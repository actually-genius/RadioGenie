import os
import time
import json
import requests
import base64

CONFIG_PATH = "config.json"
MUSIC_DIR = "music"

def load_config():
    if not os.path.exists(CONFIG_PATH):
        return None
    with open(CONFIG_PATH, "r", encoding="utf-8") as f:
        return json.load(f)

def stream_mp3_to_icecast(host, port, mount, password, mp3_path):
    url = f"http://{host}:{port}{mount}"

    # Basic auth for Icecast source (username is 'source')
    auth_str = f"source:{password}"
    encoded_auth = base64.b64encode(auth_str.encode('ascii')).decode('ascii')

    headers = {
        "Authorization": f"Basic {encoded_auth}",
        "Content-Type": "audio/mpeg",
        "User-Agent": "TelegramRadioAutoDJ/1.0"
    }

    print(f"Streaming: {os.path.basename(mp3_path)} -> {url}")

    try:
        with open(mp3_path, "rb") as f:
            def generate():
                while True:
                    chunk = f.read(4096)
                    if not chunk:
                        break
                    yield chunk
                    time.sleep(0.03) # Pacing for streaming bitrate

            response = requests.put(url, data=generate(), headers=headers, stream=True, timeout=30)
            if response.status_code in (200, 204):
                print(f"Successfully streamed: {os.path.basename(mp3_path)}")
                return True
            else:
                print(f"Icecast error {response.status_code}: {response.text}")
                return False
    except Exception as e:
        print(f"Error streaming {mp3_path}: {e}")
        return False

def main():
    config = load_config()
    if not config or "caster_fm" not in config:
        print("Error: caster_fm credentials not found in config.json")
        return

    caster = config["caster_fm"]
    host = caster.get("host")
    port = caster.get("port", 80)
    mount = caster.get("mount", "/stream")
    password = caster.get("password")

    stations = config.get("stations", {})

    print("AutoDJ started. Broadcasting Telegram tracks to Caster.fm...")

    while True:
        played_any = False
        for station_id, info in stations.items():
            station_folder = os.path.join(MUSIC_DIR, station_id)
            if not os.path.exists(station_folder):
                continue

            tracks = [os.path.join(station_folder, f) for f in sorted(os.listdir(station_folder)) if f.lower().endswith('.mp3')]

            for track in tracks:
                # Update current track metadata file for frontend
                current_meta = {"title": os.path.splitext(os.path.basename(track))[0].replace("_", " ")}
                with open(os.path.join(MUSIC_DIR, f"{station_id}_current.json"), "w", encoding="utf-8") as mf:
                    json.dump(current_meta, mf)

                success = stream_mp3_to_icecast(host, port, mount, password, track)
                played_any = True
                if not success:
                    time.sleep(5)

        if not played_any:
            print("No tracks found in music folders. Waiting for Telegram downloader...")
            time.sleep(10)

if __name__ == "__main__":
    main()

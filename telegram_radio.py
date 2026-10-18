import os
import sys
import json
import requests
import time

CONFIG_PATH = "config.json"
SECRETS_PATH = "secrets.json"
MUSIC_DIR = "music"
VALID_EXTS = ('.mp3', '.m4a', '.ogg', '.wav', '.flac')

def load_bot_token():
    # 1. Environment Variable (Primary for GitHub Actions / Production)
    env_token = os.environ.get("TELEGRAM_BOT_TOKEN")
    if env_token:
        return env_token

    # 2. Local secrets.json
    if os.path.exists(SECRETS_PATH):
        try:
            with open(SECRETS_PATH, "r", encoding="utf-8") as f:
                sec = json.load(f)
                token = sec.get("bot_token")
                if token and token != "YOUR_TELEGRAM_BOT_TOKEN_HERE":
                    return token
        except Exception as e:
            print(f"Warning reading {SECRETS_PATH}: {e}")

    # 3. Fallback to config.json
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                cfg = json.load(f)
                return cfg.get("bot_token")
        except Exception as e:
            print(f"Warning reading {CONFIG_PATH}: {e}")

    return None

def load_config():
    config = {}
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                config = json.load(f)
        except Exception as e:
            print(f"Warning reading {CONFIG_PATH}: {e}")
    return config

def download_file(file_url, save_path):
    response = requests.get(file_url, stream=True)
    if response.status_code == 200:
        with open(save_path, "wb") as f:
            for chunk in response.iter_content(chunk_size=8192):
                f.write(chunk)
        return True
    return False

def apply_retention_policy(station_folder, retention_config):
    if not retention_config or not os.path.exists(station_folder):
        return

    retention_type = retention_config.get("type", "forever").lower()
    value = retention_config.get("value", 50)

    if retention_type == "forever":
        return

    files = []
    for fname in os.listdir(station_folder):
        if fname.lower().endswith(VALID_EXTS):
            fpath = os.path.join(station_folder, fname)
            files.append((fpath, os.path.getmtime(fpath)))

    files.sort(key=lambda x: x[1], reverse=True)

    if retention_type == "count":
        if len(files) > value:
            to_remove = files[value:]
            for fpath, _ in to_remove:
                try:
                    os.remove(fpath)
                    print(f"[Retention] Removed old track (count > {value}): {os.path.basename(fpath)}")
                except Exception as e:
                    print(f"Error removing {fpath}: {e}")

    elif retention_type == "days":
        cutoff_time = time.time() - (value * 86400)
        for fpath, mtime in files:
            if mtime < cutoff_time:
                try:
                    os.remove(fpath)
                    print(f"[Retention] Removed expired track (>{value} days old): {os.path.basename(fpath)}")
                except Exception as e:
                    print(f"Error removing {fpath}: {e}")

def sync_station_music(bot_token, station_id, station_info):
    channels = station_info.get("channels", [])
    station_folder = os.path.join(MUSIC_DIR, station_id)
    os.makedirs(station_folder, exist_ok=True)

    offset_file = os.path.join(MUSIC_DIR, f"{station_id}_offset.txt")
    offset = 0
    is_first_run = not os.path.exists(offset_file)

    if not is_first_run:
        try:
            with open(offset_file, "r") as f:
                offset = int(f.read().strip())
        except:
            pass

    url = f"https://api.telegram.org/bot{bot_token}/getUpdates?offset={offset}&timeout=10"
    try:
        resp = requests.get(url, timeout=15)
        data = resp.json()
        if not data.get("ok"):
            print(f"Telegram API error for station {station_id}: {data}")
            return

        updates = data.get("result", [])

        history_limit = station_info.get("initial_history_count", 50)
        if is_first_run and len(updates) > history_limit:
            updates = updates[-history_limit:]

        for update in updates:
            offset = update["update_id"] + 1

            channel_post = update.get("channel_post") or update.get("message")
            if not channel_post:
                continue

            chat = channel_post.get("chat", {})
            chat_username = f"@{chat.get('username', '')}"
            chat_title = chat.get("title", "")

            if channels and (chat_username not in channels and chat_title not in channels):
                pass

            audio = channel_post.get("audio") or channel_post.get("document")
            if audio:
                file_id = audio.get("file_id")
                file_name = audio.get("file_name") or f"{audio.get('file_unique_id', 'track')}.mp3"

                file_info_url = f"https://api.telegram.org/bot{bot_token}/getFile?file_id={file_id}"
                info_resp = requests.get(file_info_url).json()

                if info_resp.get("ok"):
                    file_path = info_resp["result"]["file_path"]
                    download_url = f"https://api.telegram.org/file/bot{bot_token}/{file_path}"

                    save_path = os.path.join(station_folder, file_name)
                    if not os.path.exists(save_path):
                        print(f"Downloading [{station_id}] {file_name}...")
                        if download_file(download_url, save_path):
                            print(f"Saved: {save_path}")

        with open(offset_file, "w") as f:
            f.write(str(offset))

    except Exception as e:
        print(f"Error syncing station {station_id}: {e}")

    retention_config = station_info.get("retention", {"type": "count", "value": 50})
    apply_retention_policy(station_folder, retention_config)

    generate_playlist(station_folder, station_id)

def generate_playlist(station_folder, station_id):
    tracks = []
    if os.path.exists(station_folder):
        files = [f for f in os.listdir(station_folder) if f.lower().endswith(VALID_EXTS)]
        files.sort(key=lambda f: os.path.getmtime(os.path.join(station_folder, f)), reverse=True)

        for fname in files:
            tracks.append({
                "title": os.path.splitext(fname)[0].replace("_", " "),
                "filename": fname,
                "url": f"music/{station_id}/{fname}"
            })

    playlist_path = os.path.join(MUSIC_DIR, f"{station_id}_playlist.json")
    with open(playlist_path, "w", encoding="utf-8") as f:
        json.dump(tracks, f, indent=4)
    print(f"Playlist updated for {station_id}: {len(tracks)} tracks.")

def main():
    bot_token = load_bot_token()
    if not bot_token or bot_token == "YOUR_TELEGRAM_BOT_TOKEN_HERE":
        print("Please set TELEGRAM_BOT_TOKEN environment variable or add bot_token to secrets.json.")
        return

    config = load_config()
    if not config:
        print("Warning: config.json not found or empty.")

    os.makedirs(MUSIC_DIR, exist_ok=True)
    stations = config.get("stations", {})

    run_once = "--once" in sys.argv or os.environ.get("GITHUB_ACTIONS") == "true" or os.environ.get("RUN_ONCE") == "true"

    print("Starting Telegram Radio Sync...")
    while True:
        for station_id, station_info in stations.items():
            sync_station_music(bot_token, station_id, station_info)

        print("Sync cycle complete.")
        if run_once:
            print("Finished single-pass sync (run_once enabled).")
            break

        print("Waiting 60 seconds...")
        time.sleep(60)

if __name__ == "__main__":
    main()

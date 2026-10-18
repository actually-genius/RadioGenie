# Telegram Radio Network (InfinityFree + Python + GitHub Actions)

An automated, multi-station online radio application that automatically fetches music from Telegram channels (`@actually_purple`) and streams it on an InfinityFree PHP website with full playlist playback and customizable music retention policies.

---

## Security & File Architecture

To keep secret credentials completely isolated from public website code and configuration settings:

- **`secrets.json`**: Stores your secret `bot_token`. **Ignored by Git (`.gitignore`) and excluded from FTP deployment.**
- **`config.json`**: Stores station definitions, channel usernames, initial history limits, and retention policies. Safe to commit to Git.
- **`stations.json`**: Uploaded to InfinityFree for the PHP web player frontend.

---

## Configuration (`config.json`) & Retention Policies

`config.json` controls your radio station settings:

```json
{
    "stations": {
        "station_1": {
            "name": "The Den",
            "channels": ["@actually_purple"],
            "description": "Our Purple Den",
            "initial_history_count": 50,
            "retention": {
                "type": "count",
                "value": 50
            }
        }
    }
}
```

### Secrets (`secrets.json`):
```json
{
    "bot_token": "YOUR_TELEGRAM_BOT_TOKEN_HERE"
}
```

### Retention Options (`retention`):
1. **By Song Count** (`"type": "count", "value": 50`): Keeps the **X newest songs** (e.g., 50 songs). Older tracks are automatically deleted.
2. **By Time Period** (`"type": "days", "value": 30`): Keeps tracks downloaded in the last **X days** (e.g., 30 days). Expired tracks are automatically cleaned up.
3. **Keep Forever** (`"type": "forever"`): Never deletes any downloaded songs.

---

## Step 1: Telegram Bot Setup

1. Open Telegram and search for `@BotFather`.
2. Send `/newbot` and follow the prompts to create your bot.
3. Copy the **HTTP API Bot Token**.
4. Add your bot as an **Administrator** to all the Telegram channels you want to download music from (e.g. `@actually_purple`).

---

## Step 2: Deploying to InfinityFree

### Option A: Manual Upload (Using InfinityFree File Manager)
1. Run the downloader locally:
   ```bash
   pip install requests
   python telegram_radio.py
   ```
2. Upload `index.php`, `stations.json`, and the `music/` directory to InfinityFree's **`htdocs/`** directory.
*(Never upload `secrets.json`, `config.json`, or `telegram_radio.py` to `htdocs/`)*.

### Option B: Automated via GitHub Actions
1. Push your repository to GitHub.
2. Add your repository secrets (`TELEGRAM_BOT_TOKEN`, `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`, `FTP_SERVER_DIR`).
3. GitHub Actions will automatically check for new songs every hour, clean up old tracks based on your retention rules, and deploy everything to InfinityFree!

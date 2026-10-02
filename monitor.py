import argparse
import json
import time
from datetime import datetime
from pathlib import Path

from database import get_connection
from detector import evaluate
from threshold import load_config


LOG_DIR = Path(__file__).resolve().parent / "logs"
LOG_FILE = LOG_DIR / "monitor.log"


def write_log(message):
    LOG_DIR.mkdir(exist_ok=True)
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

    with LOG_FILE.open("a", encoding="utf-8") as file:
        file.write(f"[{timestamp}] {message}\n")


def candidate_ips():
    conn = get_connection()

    try:
        config = load_config(conn)
        cursor = conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT
                ip_address,
                MAX(username) username,
                COUNT(*) total
            FROM login_attempts
            WHERE status='failed'
            AND attempt_time >= DATE_SUB(NOW(), INTERVAL %s MINUTE)
            GROUP BY ip_address
            """,
            (config["window_minutes"],),
        )
        rows = cursor.fetchall()
        cursor.close()
        return rows

    finally:
        conn.close()


def run_once():
    results = []

    for row in candidate_ips():
        result = evaluate(row.get("username", ""), row["ip_address"])
        results.append(result)

        if result["status"] in ("blocked", "already_blocked"):
            write_log(json.dumps(result))

    return results


def main():
    parser = argparse.ArgumentParser(description="Monitor login_attempts dan jalankan Python detector.")
    parser.add_argument("--once", action="store_true", help="Jalankan satu kali lalu berhenti.")
    parser.add_argument("--interval", type=int, default=10, help="Interval monitor dalam detik.")
    args = parser.parse_args()

    write_log("Python monitor started.")

    while True:
        results = run_once()

        for result in results:
            print(json.dumps(result))

        if args.once:
            break

        time.sleep(args.interval)


if __name__ == "__main__":
    main()

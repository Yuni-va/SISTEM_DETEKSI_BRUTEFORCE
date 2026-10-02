import os
import sys

try:
    import mysql.connector
except ImportError:
    print(
        "Module mysql.connector belum terpasang. Jalankan: pip install -r python/requirements.txt",
        file=sys.stderr,
    )
    raise


DB_CONFIG = {
    "host": os.getenv("DB_HOST", "localhost"),
    "user": os.getenv("DB_USER", "root"),
    "password": os.getenv("DB_PASS", ""),
    "database": os.getenv("DB_NAME", "sistem_deteksi_bruteforce"),
}


def get_connection():
    return mysql.connector.connect(**DB_CONFIG)


def fetch_one(cursor):
    row = cursor.fetchone()
    return row if row is not None else {}

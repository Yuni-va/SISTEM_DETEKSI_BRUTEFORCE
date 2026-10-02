LIMIT = 5
WINDOW_MINUTES = 1
BLOCK_MINUTES = 15

DETECTION_TYPE = "Brute Force"
DESCRIPTION = "Threshold 5 login gagal dalam 1 menit."


def load_config(conn):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT setting_key, setting_value
        FROM system_settings
        WHERE setting_key IN (
            'threshold_limit',
            'detection_window_minutes',
            'block_minutes'
        )
        """
    )
    rows = cursor.fetchall()
    cursor.close()

    values = {row["setting_key"]: row["setting_value"] for row in rows}
    limit = max(1, int(values.get("threshold_limit") or LIMIT))
    window_minutes = max(1, int(values.get("detection_window_minutes") or WINDOW_MINUTES))
    block_minutes = max(1, int(values.get("block_minutes") or BLOCK_MINUTES))

    return {
        "limit": limit,
        "window_minutes": window_minutes,
        "block_minutes": block_minutes,
        "detection_type": DETECTION_TYPE,
        "description": f"Threshold {limit} login gagal dalam {window_minutes} menit.",
    }

import argparse
import json

from database import fetch_one, get_connection
from threshold import load_config


def unblock_expired(conn):
    cursor = conn.cursor()
    cursor.execute(
        """
        UPDATE blocked_ips
        SET status='unblocked'
        WHERE status='blocked'
        AND blocked_until <= NOW()
        """
    )
    conn.commit()
    cursor.close()


def is_blocked(conn, ip):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT *
        FROM blocked_ips
        WHERE ip_address=%s
        AND status='blocked'
        AND blocked_until > NOW()
        ORDER BY blocked_until DESC
        LIMIT 1
        """,
        (ip,),
    )
    blocked = cursor.fetchone()
    cursor.close()
    return blocked


def failed_attempts(conn, ip, window_minutes):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT COUNT(*) total
        FROM login_attempts
        WHERE ip_address=%s
        AND status='failed'
        AND attempt_time >= DATE_SUB(NOW(), INTERVAL %s MINUTE)
        AND attempt_time > COALESCE(
            (
                SELECT MAX(success_attempt.attempt_time)
                FROM login_attempts success_attempt
                WHERE success_attempt.ip_address=%s
                AND success_attempt.status='success'
            ),
            '1970-01-01 00:00:00'
        )
        """,
        (ip, window_minutes, ip),
    )
    row = fetch_one(cursor)
    cursor.close()
    return int(row.get("total", 0))


def latest_failed_username(conn, ip, fallback_username="unknown"):
    cursor = conn.cursor(dictionary=True)
    cursor.execute(
        """
        SELECT username
        FROM login_attempts
        WHERE ip_address=%s
        AND status='failed'
        ORDER BY attempt_time DESC
        LIMIT 1
        """,
        (ip,),
    )
    row = fetch_one(cursor)
    cursor.close()
    return row.get("username") or fallback_username


def block_ip(conn, ip, block_minutes):
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO blocked_ips
        (
            ip_address,
            status,
            blocked_at,
            blocked_until
        )
        VALUES
        (
            %s,
            'blocked',
            NOW(),
            DATE_ADD(NOW(), INTERVAL %s MINUTE)
        )
        """,
        (ip, block_minutes),
    )
    conn.commit()
    cursor.close()


def save_threshold(conn, username, ip, attempt, limit):
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO threshold_logs
        (
            ip_address,
            total_attempt,
            threshold_limit,
            action_taken,
            created_at
        )
        VALUES
        (
            %s,%s,%s,%s,NOW()
        )
        """,
        (
            ip,
            attempt,
            limit,
            f"IP diblokir karena {attempt} login gagal mencapai threshold {limit} untuk username {username}",
        ),
    )
    conn.commit()
    cursor.close()


def save_detection(conn, username, ip, detection_type, description):
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO detection_logs
        (
            username,
            ip_address,
            detection_type,
            description,
            detected_at
        )
        VALUES
        (
            %s,%s,%s,%s,NOW()
        )
        """,
        (username, ip, detection_type, description),
    )
    conn.commit()
    cursor.close()


def save_system_log(conn, message, level="INFO"):
    cursor = conn.cursor()
    cursor.execute(
        """
        INSERT INTO system_logs
        (
            level,
            message,
            created_at
        )
        VALUES
        (
            %s,%s,NOW()
        )
        """,
        (level, message),
    )
    conn.commit()
    cursor.close()


def evaluate(username, ip):
    conn = get_connection()

    try:
        config = load_config(conn)
        limit = config["limit"]
        window_minutes = config["window_minutes"]
        block_minutes = config["block_minutes"]

        unblock_expired(conn)

        failed = failed_attempts(conn, ip, window_minutes)

        if failed < limit:
            return {
                "blocked": False,
                "status": "below_threshold",
                "ip": ip,
                "username": username,
                "failed": failed,
                "threshold": limit,
            }

        existing_block = is_blocked(conn, ip)

        if existing_block:
            return {
                "blocked": True,
                "status": "already_blocked",
                "ip": ip,
                "username": username,
                "failed": failed,
                "threshold": limit,
                "blocked_until": str(existing_block["blocked_until"]),
            }

        username = username or latest_failed_username(conn, ip)

        block_ip(conn, ip, block_minutes)
        save_threshold(conn, username, ip, failed, limit)
        save_detection(
            conn,
            username,
            ip,
            config["detection_type"],
            config["description"],
        )
        save_system_log(conn, f"IP {ip} diblokir oleh Python detector karena {failed} login gagal.")

        return {
            "blocked": True,
            "status": "blocked",
            "ip": ip,
            "username": username,
            "failed": failed,
            "threshold": limit,
            "block_minutes": block_minutes,
        }

    finally:
        conn.close()


def main():
    parser = argparse.ArgumentParser(description="Brute force threshold detector.")
    parser.add_argument("--username", default="", help="Username terbaru yang dicoba.")
    parser.add_argument("--ip", required=True, help="IP address yang akan dicek.")
    args = parser.parse_args()

    result = evaluate(args.username, args.ip)
    print(json.dumps(result, indent=2))


if __name__ == "__main__":
    main()

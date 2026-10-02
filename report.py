import argparse
import json

from database import fetch_one, get_connection


def count_rows(conn, table, date_column, from_date="", to_date="", status_column="", status_value=""):
    sql = f"SELECT COUNT(*) total FROM {table} WHERE 1=1"
    params = []

    if status_column:
        sql += f" AND {status_column}=%s"
        params.append(status_value)

    if from_date and to_date:
        sql += f" AND DATE({date_column}) BETWEEN %s AND %s"
        params.extend([from_date, to_date])
    elif from_date:
        sql += f" AND DATE({date_column}) >= %s"
        params.append(from_date)
    elif to_date:
        sql += f" AND DATE({date_column}) <= %s"
        params.append(to_date)

    cursor = conn.cursor(dictionary=True)
    cursor.execute(sql, tuple(params))
    row = fetch_one(cursor)
    cursor.close()

    return int(row.get("total", 0))


def summary(from_date="", to_date=""):
    conn = get_connection()

    try:
        return {
            "success_login": count_rows(
                conn,
                "login_attempts",
                "attempt_time",
                from_date,
                to_date,
                "status",
                "success",
            ),
            "failed_login": count_rows(
                conn,
                "login_attempts",
                "attempt_time",
                from_date,
                to_date,
                "status",
                "failed",
            ),
            "blocked_ip": count_rows(conn, "blocked_ips", "blocked_at", from_date, to_date),
            "threshold": count_rows(conn, "threshold_logs", "created_at", from_date, to_date),
            "detection": count_rows(conn, "detection_logs", "detected_at", from_date, to_date),
        }

    finally:
        conn.close()


def main():
    parser = argparse.ArgumentParser(description="Report ringkas sistem deteksi brute force.")
    parser.add_argument("--from-date", default="", help="Tanggal awal format YYYY-MM-DD.")
    parser.add_argument("--to-date", default="", help="Tanggal akhir format YYYY-MM-DD.")
    args = parser.parse_args()

    print(json.dumps(summary(args.from_date, args.to_date), indent=2))


if __name__ == "__main__":
    main()

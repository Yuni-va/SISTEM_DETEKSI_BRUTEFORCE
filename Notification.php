<?php

class Notification
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->ensureTable();
    }

    private function ensureTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS notification_reads (
                    user_id INT NOT NULL PRIMARY KEY,
                    last_read_at DATETIME NOT NULL,
                    updated_at DATETIME NULL
                )";

        mysqli_query($this->conn,$sql);
    }

    public function lastReadAt($userId)
    {
        $sql = "SELECT last_read_at
                FROM notification_reads
                WHERE user_id=?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$userId);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return $row['last_read_at'] ?? "1970-01-01 00:00:00";
    }

    public function markAllRead($userId)
    {
        $sql = "INSERT INTO notification_reads
                (
                    user_id,
                    last_read_at,
                    updated_at
                )
                VALUES
                (
                    ?,NOW(),NOW()
                )
                ON DUPLICATE KEY UPDATE
                    last_read_at=NOW(),
                    updated_at=NOW()";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i",$userId);

        return $stmt->execute();
    }

    public function unreadCount($userId)
    {
        $lastReadAt = $this->lastReadAt($userId);

        $sql = "SELECT
                    (
                        SELECT COUNT(*)
                        FROM detection_logs
                        WHERE detected_at > ?
                    ) +
                    (
                        SELECT COUNT(*)
                        FROM blocked_ips
                        WHERE blocked_at > ?
                    ) total";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss",$lastReadAt,$lastReadAt);
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();

        return (int) ($row['total'] ?? 0);
    }
}

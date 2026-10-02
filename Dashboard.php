<?php

class Dashboard
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // ============================
    // Total User
    // ============================

    public function totalUser()
    {
        $sql = "SELECT COUNT(*) AS total FROM users";
        $query = mysqli_query($this->conn,$sql);
        return mysqli_fetch_assoc($query)['total'];
    }

    // ============================
    // Total Login Gagal
    // ============================

    public function totalFailedLogin()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM login_attempts
                WHERE status='failed'";

        $query = mysqli_query($this->conn,$sql);

        return mysqli_fetch_assoc($query)['total'];
    }

    // ============================
    // Total Login Success
    // ============================

    public function totalSuccessLogin()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM login_attempts
                WHERE status='success'";

        $query = mysqli_query($this->conn,$sql);

        return mysqli_fetch_assoc($query)['total'];
    }

    // ============================
    // Total Blocked IP
    // ============================

    public function totalBlockedIP()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM blocked_ips
                WHERE status='blocked'
                AND blocked_until > NOW()";

        $query = mysqli_query($this->conn,$sql);

        return mysqli_fetch_assoc($query)['total'];
    }

    // ============================
    // Total Detection
    // ============================

    public function totalDetection()
    {
        $sql = "SELECT COUNT(*) AS total
                FROM detection_logs";

        $query = mysqli_query($this->conn,$sql);

        return mysqli_fetch_assoc($query)['total'];
    }

    // ============================
    // Aktivitas Terbaru
    // ============================

    public function latestActivity()
    {
        $sql = "SELECT
                    users.fullName,
                    activity_logs.activity,
                    activity_logs.created_at
                FROM activity_logs
                LEFT JOIN users
                ON users.id = activity_logs.user_id
                ORDER BY activity_logs.created_at DESC
                LIMIT 10";

        return mysqli_query($this->conn,$sql);
    }

    // ============================
    // Grafik Login Attempt
    // ============================

    public function chartLogin()
    {
        $data = [];

        $sql = "SELECT
                    DATE(attempt_time) AS tanggal,
                    COUNT(*) AS total
                FROM login_attempts
                GROUP BY DATE(attempt_time)
                ORDER BY DATE(attempt_time) ASC";

        $query = mysqli_query($this->conn,$sql);

        while($row=mysqli_fetch_assoc($query))
        {
            $data[]=$row;
        }

        return $data;
    }

    public function chartLoginSeries()
    {
        $data = [];

        $sql = "SELECT
                    DATE(attempt_time) AS tanggal,
                    SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) AS success_total,
                    SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) AS failed_total
                FROM login_attempts
                GROUP BY DATE(attempt_time)
                ORDER BY DATE(attempt_time) ASC";

        $query = mysqli_query($this->conn,$sql);

        while($row=mysqli_fetch_assoc($query))
        {
            $data[]=$row;
        }

        return $data;
    }

    public function suspiciousIpToday()
    {
        $sql = "SELECT
                    ip_address,
                    COUNT(*) total_failed,
                    MAX(attempt_time) last_attempt
                FROM login_attempts
                WHERE status='failed'
                AND DATE(attempt_time)=CURDATE()
                GROUP BY ip_address
                ORDER BY total_failed DESC, last_attempt DESC
                LIMIT 1";

        $query = mysqli_query($this->conn,$sql);
        $row = mysqli_fetch_assoc($query);

        return $row ?: [
            "ip_address" => "-",
            "total_failed" => 0,
            "last_attempt" => null
        ];
    }

    // ============================
    // Top Attackers
    // ============================

    public function topAttackers($limit = 5)
    {
        $limit = (int) $limit;
        if($limit <= 0) $limit = 5;

        $sql = "SELECT
                    ip_address,
                    COUNT(*) AS total_failed,
                    MAX(attempt_time) AS last_attempt
                FROM login_attempts
                WHERE status='failed'
                GROUP BY ip_address
                ORDER BY total_failed DESC, last_attempt DESC
                LIMIT " . $limit;

        return mysqli_query($this->conn, $sql);
    }

    // ============================
    // Failed attempts last 24 hours (hourly)
    // ============================

    public function failedLast24Hours()
    {
        $data = array_fill(0,24,0);

        $sql = "SELECT HOUR(attempt_time) AS hr, COUNT(*) AS total
                FROM login_attempts
                WHERE status='failed'
                AND attempt_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY HOUR(attempt_time)
                ORDER BY hr ASC";

        $query = mysqli_query($this->conn, $sql);

        while($row = mysqli_fetch_assoc($query)){
            $h = (int) $row['hr'];
            $data[$h] = (int) $row['total'];
        }

        return $data;
    }

    public function recentSecurityEvents($limit = 5)
    {
        $limit = (int) $limit;
        if($limit <= 0) $limit = 5;

        $sql = "SELECT
                    'danger' AS severity,
                    'Brute force detected' AS title,
                    CONCAT('IP ', ip_address, ' melewati threshold') AS description,
                    detected_at AS event_time
                FROM detection_logs
                UNION ALL
                SELECT
                    'warning' AS severity,
                    'IP address blocked' AS title,
                    CONCAT(ip_address, ' diblokir sampai ', DATE_FORMAT(blocked_until, '%d %b %Y %H:%i')) AS description,
                    blocked_at AS event_time
                FROM blocked_ips
                UNION ALL
                SELECT
                    CASE WHEN status='failed' THEN 'danger' ELSE 'success' END AS severity,
                    CASE WHEN status='failed' THEN 'Failed login attempt' ELSE 'Successful login' END AS title,
                    CONCAT(username, ' dari IP ', ip_address) AS description,
                    attempt_time AS event_time
                FROM login_attempts
                ORDER BY event_time DESC
                LIMIT ".$limit;

        return mysqli_query($this->conn,$sql);
    }

    public function latestBlockedIps($limit = 4)
    {
        $limit = (int) $limit;
        if($limit <= 0) $limit = 4;

        $sql = "SELECT *
                FROM blocked_ips
                WHERE status='blocked'
                AND (blocked_until IS NULL OR blocked_until > NOW())
                ORDER BY blocked_at DESC
                LIMIT ".$limit;

        return mysqli_query($this->conn,$sql);
    }

    public function lastDetectionTime()
    {
        $sql = "SELECT detected_at
                FROM detection_logs
                ORDER BY detected_at DESC
                LIMIT 1";

        $query = mysqli_query($this->conn,$sql);
        $row = mysqli_fetch_assoc($query);

        return $row['detected_at'] ?? null;
    }

}

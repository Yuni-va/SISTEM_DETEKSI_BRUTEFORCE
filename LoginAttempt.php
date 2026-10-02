<?php

class LoginAttempt
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // ==========================
    // Simpan Login Attempt
    // ==========================

    public function save($username,$ip,$status)
    {
        $sql="INSERT INTO login_attempts
        (
            username,
            ip_address,
            status,
            attempt_time
        )
        VALUES
        (
            ?,?,?,NOW()
        )";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param(
            "sss",
            $username,
            $ip,
            $status
        );

        return $stmt->execute();
    }

    // ==========================
    // Semua Data
    // ==========================

    public function getAll()
    {
        return mysqli_query(
            $this->conn,
            "SELECT *
            FROM login_attempts
            ORDER BY attempt_time DESC"
        );
    }

    // ==========================
    // Search
    // ==========================

    public function search($keyword)
    {

        $keyword="%".$keyword."%";

        $sql="SELECT *
              FROM login_attempts
              WHERE username LIKE ?
              OR ip_address LIKE ?
              ORDER BY attempt_time DESC";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param(
            "ss",
            $keyword,
            $keyword
        );

        $stmt->execute();

        return $stmt->get_result();

    }

    // ==========================
    // Success
    // ==========================

    public function totalSuccess()
    {

        $sql="SELECT COUNT(*) total
              FROM login_attempts
              WHERE status='success'";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];

    }

    // ==========================
    // Failed
    // ==========================

    public function totalFailed()
    {

        $sql="SELECT COUNT(*) total
              FROM login_attempts
              WHERE status='failed'";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];

    }

    // ==========================
    // Hari Ini
    // ==========================

    public function today()
    {

        $sql="SELECT COUNT(*) total
              FROM login_attempts
              WHERE DATE(attempt_time)=CURDATE()";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];

    }

    // ==========================
    // Failed Attempts Terbaru
    // ==========================

    public function failedAttempts($ip,$windowMinutes=1)
    {
        $windowMinutes = max(1,(int) $windowMinutes);

        $sql="SELECT COUNT(*) total
              FROM login_attempts
              WHERE ip_address=?
              AND status='failed'
              AND attempt_time >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
              AND attempt_time > COALESCE(
                    (
                        SELECT MAX(success_attempt.attempt_time)
                        FROM login_attempts success_attempt
                        WHERE success_attempt.ip_address=?
                        AND success_attempt.status='success'
                    ),
                    '1970-01-01 00:00:00'
              )";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param(
            "sis",
            $ip,
            $windowMinutes,
            $ip
        );

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['total'];
    }

    // ==========================
    // Reset Counter Gagal
    // ==========================

    public function clearFailedAttempt($ip)
    {
        $sql="SELECT COUNT(*) total
              FROM login_attempts
              WHERE ip_address=?
              AND status='failed'
              AND attempt_time > COALESCE(
                    (
                        SELECT MAX(success_attempt.attempt_time)
                        FROM login_attempts success_attempt
                        WHERE success_attempt.ip_address=?
                        AND success_attempt.status='success'
                    ),
                    '1970-01-01 00:00:00'
              )";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param(
            "ss",
            $ip,
            $ip
        );

        $stmt->execute();

        return (int) $stmt->get_result()->fetch_assoc()['total'] === 0;
    }

}

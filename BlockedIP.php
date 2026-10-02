<?php

class BlockedIP
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Semua Data
    public function getAll()
    {
        $sql = "SELECT *
                FROM blocked_ips
                ORDER BY blocked_at DESC";

        return mysqli_query($this->conn,$sql);
    }

    public function latestBlocked($limit=5)
    {
        $limit = (int) $limit;

        if($limit <= 0){
            $limit = 5;
        }

        $sql = "SELECT *
                FROM blocked_ips
                WHERE status='blocked'
                ORDER BY blocked_at DESC
                LIMIT ".$limit;

        return mysqli_query($this->conn,$sql);
    }

    // Cari
    public function search($keyword)
    {
        $keyword="%".$keyword."%";

        $sql="SELECT *
              FROM blocked_ips
              WHERE ip_address LIKE ?
              ORDER BY blocked_at DESC";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param("s",$keyword);

        $stmt->execute();

        return $stmt->get_result();
    }

    // Total Blocked
    public function totalBlocked()
    {
        $sql="SELECT COUNT(*) total
              FROM blocked_ips
              WHERE status='blocked'";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    // Total Unblocked
    public function totalUnblocked()
    {
        $sql="SELECT COUNT(*) total
              FROM blocked_ips
              WHERE status='unblocked'";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    // Total Hari Ini
    public function today()
    {
        $sql="SELECT COUNT(*) total
              FROM blocked_ips
              WHERE DATE(blocked_at)=CURDATE()";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    // Unblock
    public function unblock($id)
    {
        $sql="UPDATE blocked_ips
              SET status='unblocked'
              WHERE id=?";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param("i",$id);

        return $stmt->execute();
    }

    // Cek IP yang masih aktif diblokir
    public function isBlocked($ip)
    {
        $sql="SELECT *
              FROM blocked_ips
              WHERE ip_address=?
              AND status='blocked'
              AND blocked_until > NOW()
              ORDER BY blocked_until DESC
              LIMIT 1";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param("s",$ip);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    // Blokir IP
    public function blockIP($ip,$minutes=15)
    {
        $blocked=$this->isBlocked($ip);

        if($blocked){
            return true;
        }

        $sql="INSERT INTO blocked_ips
              (
                ip_address,
                status,
                blocked_at,
                blocked_until,
                unblocked_at,
                unblocked_by
              )
              VALUES
              (
                ?,
                'blocked',
                NOW(),
                DATE_ADD(NOW(), INTERVAL ? MINUTE),
                NULL,
                NULL
              )
              ON DUPLICATE KEY UPDATE
                status='blocked',
                blocked_at=VALUES(blocked_at),
                blocked_until=VALUES(blocked_until),
                unblocked_at=NULL,
                unblocked_by=NULL";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param(
            "si",
            $ip,
            $minutes
        );

        return $stmt->execute();
    }

    // Buka blokir otomatis yang sudah habis
    public function unblockExpired()
    {
        $sql="UPDATE blocked_ips
              SET status='unblocked'
              WHERE status='blocked'
              AND blocked_until <= NOW()";

        return mysqli_query($this->conn,$sql);
    }
}

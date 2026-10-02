<?php

class ThresholdLog
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
                FROM threshold_logs
                ORDER BY created_at DESC";

        return mysqli_query($this->conn, $sql);
    }

    // Search
    public function search($keyword)
    {
        $keyword = "%".$keyword."%";

        $sql = "SELECT *
                FROM threshold_logs
                WHERE ip_address LIKE ?
                OR action_taken LIKE ?
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("ss",$keyword,$keyword);

        $stmt->execute();

        return $stmt->get_result();
    }

    public function filter($keyword="",$from="",$to="")
    {
        $keyword = "%".$keyword."%";

        $sql = "SELECT *
                FROM threshold_logs
                WHERE (
                    ip_address LIKE ?
                    OR action_taken LIKE ?
                )";

        if($from !== "" && $to !== ""){
            $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
        }elseif($from !== ""){
            $sql .= " AND DATE(created_at) >= ?";
        }elseif($to !== ""){
            $sql .= " AND DATE(created_at) <= ?";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);

        if($from !== "" && $to !== ""){
            $stmt->bind_param("ssss",$keyword,$keyword,$from,$to);
        }elseif($from !== "" || $to !== ""){
            $date = $from !== "" ? $from : $to;
            $stmt->bind_param("sss",$keyword,$keyword,$date);
        }else{
            $stmt->bind_param("ss",$keyword,$keyword);
        }

        $stmt->execute();

        return $stmt->get_result();
    }

    // Total Threshold
    public function total()
    {
        $sql = "SELECT COUNT(*) total FROM threshold_logs";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    // Hari Ini
    public function today()
    {
        $sql = "SELECT COUNT(*) total
                FROM threshold_logs
                WHERE DATE(created_at)=CURDATE()";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    // Minggu Ini
    public function week()
    {
        $sql = "SELECT COUNT(*) total
                FROM threshold_logs
                WHERE YEARWEEK(created_at)=YEARWEEK(NOW())";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    // Simpan Threshold Log
    public function save($username,$ip,$attempt,$threshold)
    {
        $sql = "INSERT INTO threshold_logs
                (
                    ip_address,
                    failed_attempts,
                    threshold_limit,
                    action_taken,
                    created_at
                )
                VALUES
                (
                    ?,?,?,?,NOW()
                )";

        $stmt = $this->conn->prepare($sql);

        $actionTaken = "IP diblokir karena ".$attempt." login gagal mencapai threshold ".$threshold;

        if($username !== ""){
            $actionTaken .= " untuk username ".$username;
        }

        $stmt->bind_param(
            "siis",
            $ip,
            $attempt,
            $threshold,
            $actionTaken
        );

        return $stmt->execute();
    }
}


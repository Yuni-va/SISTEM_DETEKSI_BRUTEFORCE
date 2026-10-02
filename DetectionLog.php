<?php

class DetectionLog
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function save($username, $ip, $description = "")
    {
        $type = "Brute Force";

        if($description === ""){
            $description = "Threshold brute force terlampaui.";
        }

        $sql = "INSERT INTO detection_logs
                (
                    username,
                    ip_address,
                    detection_type,
                    description,
                    detected_at
                )
                VALUES
                (
                    ?,?,?,?,NOW()
                )";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssss",
            $username,
            $ip,
            $type,
            $description
        );

        return $stmt->execute();
    }

    // Semua Data
    public function getAll()
    {
        $sql = "SELECT *
                FROM detection_logs
                ORDER BY detected_at DESC";

        return mysqli_query($this->conn, $sql);
    }

    public function latest($limit=5)
    {
        $limit = (int) $limit;

        if($limit <= 0){
            $limit = 5;
        }

        $sql = "SELECT *
                FROM detection_logs
                ORDER BY detected_at DESC
                LIMIT ".$limit;

        return mysqli_query($this->conn, $sql);
    }

    // Search
    public function search($keyword)
    {
        $keyword = "%".$keyword."%";

        $sql = "SELECT *
                FROM detection_logs
                WHERE username LIKE ?
                OR ip_address LIKE ?
                OR detection_type LIKE ?
                OR description LIKE ?
                ORDER BY detected_at DESC";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssss",
            $keyword,
            $keyword,
            $keyword,
            $keyword
        );

        $stmt->execute();

        return $stmt->get_result();
    }

    public function filter($keyword="",$from="",$to="")
    {
        $keyword = "%".$keyword."%";

        $sql = "SELECT *
                FROM detection_logs
                WHERE (
                    username LIKE ?
                    OR ip_address LIKE ?
                    OR detection_type LIKE ?
                    OR description LIKE ?
                )";

        if($from !== "" && $to !== ""){
            $sql .= " AND DATE(detected_at) BETWEEN ? AND ?";
        }elseif($from !== ""){
            $sql .= " AND DATE(detected_at) >= ?";
        }elseif($to !== ""){
            $sql .= " AND DATE(detected_at) <= ?";
        }

        $sql .= " ORDER BY detected_at DESC";

        $stmt = $this->conn->prepare($sql);

        if($from !== "" && $to !== ""){
            $stmt->bind_param("ssssss",$keyword,$keyword,$keyword,$keyword,$from,$to);
        }elseif($from !== "" || $to !== ""){
            $date = $from !== "" ? $from : $to;
            $stmt->bind_param("sssss",$keyword,$keyword,$keyword,$keyword,$date);
        }else{
            $stmt->bind_param("ssss",$keyword,$keyword,$keyword,$keyword);
        }

        $stmt->execute();

        return $stmt->get_result();
    }

    // Total Detection
    public function total()
    {
        $sql = "SELECT COUNT(*) total
                FROM detection_logs";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    // Hari Ini
    public function today()
    {
        $sql = "SELECT COUNT(*) total
                FROM detection_logs
                WHERE DATE(detected_at)=CURDATE()";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    // Minggu Ini
    public function week()
    {
        $sql = "SELECT COUNT(*) total
                FROM detection_logs
                WHERE YEARWEEK(detected_at)=YEARWEEK(NOW())";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }
}


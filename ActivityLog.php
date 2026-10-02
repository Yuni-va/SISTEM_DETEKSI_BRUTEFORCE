<?php

class ActivityLog
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function save($user_id,$activity)
    {

        $ip = $_SERVER['REMOTE_ADDR'] ?? "0.0.0.0";

        $sql="INSERT INTO activity_logs
              (
                user_id,
                activity,
                ip_address,
                created_at
              )
              VALUES
              (
                ?,?,?,NOW()
              )";

        $stmt=$this->conn->prepare($sql);

        $stmt->bind_param(
            "iss",
            $user_id,
            $activity,
            $ip
        );

        return $stmt->execute();

    }

    public function getAll()
    {
        $sql="SELECT
                activity_logs.*,
                users.fullName,
                users.username
              FROM activity_logs
              LEFT JOIN users
              ON users.id=activity_logs.user_id
              ORDER BY activity_logs.created_at DESC";

        return mysqli_query($this->conn,$sql);
    }

    public function search($keyword)
    {
        $keyword="%".$keyword."%";

        $sql="SELECT
                activity_logs.*,
                users.fullName,
                users.username
              FROM activity_logs
              LEFT JOIN users
              ON users.id=activity_logs.user_id
              WHERE users.fullName LIKE ?
              OR users.username LIKE ?
              OR activity_logs.activity LIKE ?
              OR activity_logs.ip_address LIKE ?
              ORDER BY activity_logs.created_at DESC";

        $stmt=$this->conn->prepare($sql);

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

    public function total()
    {
        $sql="SELECT COUNT(*) total
              FROM activity_logs";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    public function today()
    {
        $sql="SELECT COUNT(*) total
              FROM activity_logs
              WHERE DATE(created_at)=CURDATE()";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

    public function week()
    {
        $sql="SELECT COUNT(*) total
              FROM activity_logs
              WHERE YEARWEEK(created_at)=YEARWEEK(NOW())";

        return mysqli_fetch_assoc(
            mysqli_query($this->conn,$sql)
        )['total'];
    }

}

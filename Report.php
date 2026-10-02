<?php

class Report
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    private function countRows($table,$dateColumn,$from,$to,$statusColumn="",$statusValue="")
    {
        $sql = "SELECT COUNT(*) total FROM ".$table." WHERE 1=1";

        if($statusColumn!=""){
            $sql .= " AND ".$statusColumn."=?";
        }

        if($from!="" && $to!=""){
            $sql .= " AND DATE(".$dateColumn.") BETWEEN ? AND ?";
        }elseif($from!=""){
            $sql .= " AND DATE(".$dateColumn.") >= ?";
        }elseif($to!=""){
            $sql .= " AND DATE(".$dateColumn.") <= ?";
        }

        $stmt = $this->conn->prepare($sql);

        if($statusColumn!="" && $from!="" && $to!=""){
            $stmt->bind_param("sss",$statusValue,$from,$to);
        }elseif($statusColumn!="" && ($from!="" || $to!="")){
            $date = $from!="" ? $from : $to;
            $stmt->bind_param("ss",$statusValue,$date);
        }elseif($statusColumn!=""){
            $stmt->bind_param("s",$statusValue);
        }elseif($from!="" && $to!=""){
            $stmt->bind_param("ss",$from,$to);
        }elseif($from!="" || $to!=""){
            $date = $from!="" ? $from : $to;
            $stmt->bind_param("s",$date);
        }

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['total'];
    }

    public function summary($from="",$to="")
    {
        return [
            "success_login" => $this->countRows(
                "login_attempts",
                "attempt_time",
                $from,
                $to,
                "status",
                "success"
            ),
            "failed_login" => $this->countRows(
                "login_attempts",
                "attempt_time",
                $from,
                $to,
                "status",
                "failed"
            ),
            "blocked_ip" => $this->countRows(
                "blocked_ips",
                "blocked_at",
                $from,
                $to
            ),
            "threshold" => $this->countRows(
                "threshold_logs",
                "created_at",
                $from,
                $to
            ),
            "detection" => $this->countRows(
                "detection_logs",
                "detected_at",
                $from,
                $to
            )
        ];
    }

    public function latestDetections($from="",$to="")
    {
        return $this->detections($from,$to,10);
    }

    public function detections($from="",$to="",$limit=0)
    {
        $sql = "SELECT *
                FROM detection_logs
                WHERE 1=1";

        if($from!="" && $to!=""){
            $sql .= " AND DATE(detected_at) BETWEEN ? AND ?";
        }elseif($from!=""){
            $sql .= " AND DATE(detected_at) >= ?";
        }elseif($to!=""){
            $sql .= " AND DATE(detected_at) <= ?";
        }

        $sql .= " ORDER BY detected_at DESC";

        $limit = (int) $limit;

        if($limit > 0){
            $sql .= " LIMIT ".$limit;
        }

        $stmt = $this->conn->prepare($sql);

        if($from!="" && $to!=""){
            $stmt->bind_param("ss",$from,$to);
        }elseif($from!="" || $to!=""){
            $date = $from!="" ? $from : $to;
            $stmt->bind_param("s",$date);
        }

        $stmt->execute();

        return $stmt->get_result();
    }
}

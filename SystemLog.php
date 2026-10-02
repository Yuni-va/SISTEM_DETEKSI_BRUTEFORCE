<?php

class SystemLog
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function save($message, $level = "INFO")
    {
        $sql = "INSERT INTO system_logs
                (level, message, created_at)
                VALUES (?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("ss", $level, $message);

        return $stmt->execute();
    }
}
<?php

// koneksi database

$host = getenv("DB_HOST") ?: "localhost";
$user = getenv("DB_USER") ?: "root";
$pass = getenv("DB_PASS") ?: "";
$db   = getenv("DB_NAME") ?: "sistem_deteksi_bruteforce";

$conn = new mysqli($host,$user,$pass,$db);

if($conn->connect_error){
    die("Koneksi gagal : ".$conn->connect_error);
}

date_default_timezone_set("Asia/Jakarta");

?>
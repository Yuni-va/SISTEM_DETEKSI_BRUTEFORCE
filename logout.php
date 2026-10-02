<?php

session_start();
require "app/helpers/Security.php";

require_post();
require_csrf();

// Hapus semua session
$_SESSION = [];

// Hapus session
session_destroy();

// Kembali ke login
header("Location: login.php");
exit;

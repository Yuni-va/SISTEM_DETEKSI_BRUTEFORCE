<?php
session_start();
require "app/helpers/Security.php";
?>

<!doctype html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Register User</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="container">

<div class="row justify-content-center align-items-center vh-100">

<div class="col-lg-6">

<div class="card shadow-lg border-0 rounded-4">

<div class="card-body p-5">

<h2 class="text-center text-primary mb-4">

<i class="bi bi-person-plus-fill"></i>

Register User

</h2>

<?php

if(isset($_SESSION['success'])){

echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';

unset($_SESSION['success']);

}

if(isset($_SESSION['error'])){

echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';

unset($_SESSION['error']);

}

?>

<form action="app/controllers/RegisterController.php" method="POST">

<?= csrf_field(); ?>

<div class="mb-3">

<label>Nama Lengkap</label>

<input
type="text"
name="fullname"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Username</label>

<input
type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Konfirmasi Password</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

<button class="btn btn-primary w-100">

Daftar User

</button>

</form>

<hr>

<div class="text-center">

<a href="login.php">

Sudah punya akun? Login

</a>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>

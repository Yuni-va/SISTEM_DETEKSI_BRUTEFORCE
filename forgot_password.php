<?php
session_start();
?>

<!doctype html>

<html>

<head>

<meta charset="UTF-8">

<title>Lupa Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="container">

<div class="row justify-content-center align-items-center vh-100">

<div class="col-lg-5">

<div class="card login-card">

<div class="card-body p-5">

<h3 class="text-center">

Reset Password

</h3>

<p class="text-center text-muted">

Masukkan email akun Anda

</p>

<form>

<input
type="email"
class="form-control mb-3"
placeholder="Email">

<button
class="btn btn-primary w-100">

Kirim Link Reset

</button>

<a
href="login.php"
class="btn btn-outline-secondary w-100 mt-3">

Kembali Login

</a>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>
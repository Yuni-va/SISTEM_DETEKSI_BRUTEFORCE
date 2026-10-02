<?php
session_start();

require "app/middleware/Auth.php";
?>

<?php include "app/views/layouts/header.php"; ?>

<div class="wrapper">

    <!-- Sidebar -->
    <?php include "app/views/layouts/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Navbar -->
        <?php include "app/views/layouts/navbar.php"; ?>

        <!-- Isi Dashboard -->
        <div class="container-fluid p-4">

            <?php include "app/views/dashboard/index.php"; ?>

        </div>

    </div>

</div>

<?php include "app/views/layouts/footer.php"; ?>

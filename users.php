<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require "app/middleware/Admin.php";

include "app/views/layouts/header.php";
?>

<div class="wrapper">

    <?php include "app/views/layouts/sidebar.php"; ?>

    <div class="main-content">

        <?php include "app/views/layouts/navbar.php"; ?>

        <div class="container-fluid p-4">

            <?php include "app/views/users/index.php"; ?>

        </div>

    </div>

</div>

<?php include "app/views/layouts/footer.php"; ?>

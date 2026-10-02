<?php

require_once "app/helpers/Security.php";
require_once "config/database.php";
require_once "app/models/DetectionLog.php";
require_once "app/models/BlockedIP.php";
require_once "app/models/Notification.php";

$notificationDetectionModel = new DetectionLog($conn);
$notificationBlockedModel = new BlockedIP($conn);
$notificationModel = new Notification($conn);

$notificationDetections = $notificationDetectionModel->latest(4);
$notificationBlockedIps = $notificationBlockedModel->latestBlocked(3);
$notificationCount = $notificationModel->unreadCount((int) ($_SESSION['id'] ?? 0));
$notificationBadge = $notificationCount > 99 ? "99+" : $notificationCount;

$navbarPhoto = $_SESSION['profile_photo'] ?? "";
$navbarPhotoPath = "assets/uploads/profile/".$navbarPhoto;
$navbarHasPhoto = $navbarPhoto !== "" && file_exists($navbarPhotoPath);
$navbarInitial = strtoupper(substr($_SESSION['fullname'] ?? $_SESSION['username'] ?? "A",0,1));

?>

<nav class="navbar navbar-expand-lg soc-navbar">

    <div class="container-fluid">

        <button class="btn btn-outline-primary d-lg-none soc-icon-btn" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>

        <div class="navbar-title">

            <h4>Dashboard</h4>
            <small>Sistem Deteksi Serangan Brute Force</small>

        </div>

        <?php
            $todayDetections = $notificationDetectionModel->today();
            $todayBlocked = $notificationBlockedModel->today();
            $systemStatus = ($todayDetections > 0 || $todayBlocked > 0) ? 'ALERT' : 'ACTIVE';
            $statusClass = $systemStatus === 'ACTIVE' ? 'status-active' : 'status-alert';
            $now = date('d M Y H:i:s');
        ?>

        <ul class="navbar-nav ms-auto align-items-center soc-navbar-actions">

            <li class="nav-item d-none d-xl-flex soc-time">
                <i class="bi bi-calendar3"></i>
                <span><?= $now ?> WIB</span>
            </li>

            <li class="nav-item d-none d-md-flex">
                <span class="soc-status-badge <?= $statusClass ?>">
                    <i class="bi <?= $systemStatus === 'ACTIVE' ? 'bi-shield-check' : 'bi-exclamation-triangle' ?>"></i>
                    System Status: <?= $systemStatus ?>
                </span>
            </li>

            <li class="nav-item dropdown">

                <a class="nav-link position-relative"
                    href="#"
                    role="button"
                    data-bs-toggle="dropdown">

                    <i class="bi bi-bell fs-5"></i>

                    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:<?= $notificationCount>0? 'inline-block':'none' ?>">
                        <?= htmlspecialchars($notificationBadge) ?>
                    </span>

                </a>

                <ul id="notifDropdownMenu" class="dropdown-menu dropdown-menu-end shadow" style="min-width:360px;">

                    <li>

                        <h6 class="dropdown-header">

                            Notifikasi

                            <?php if($notificationCount > 0){ ?>
                                <span class="badge bg-danger ms-1">
                                    <?= htmlspecialchars($notificationBadge) ?> baru
                                </span>
                            <?php } ?>

                        </h6>

                    </li>

                    <!-- dropdown content is populated via server-side initially and updated by JS -->
                    <?php if(
                        mysqli_num_rows($notificationDetections) === 0 &&
                        mysqli_num_rows($notificationBlockedIps) === 0
                    ){ ?>

                        <span class="dropdown-item">

                            Belum ada notifikasi.

                        </span>

                    <?php } ?>

                    <?php while($notification = mysqli_fetch_assoc($notificationDetections)){ ?>

                        <li>
                            <a class="dropdown-item py-2" href="detection_logs.php">
                                <div class="d-flex gap-2">
                                    <i class="bi bi-shield-exclamation text-danger"></i>
                                    <div>
                                        <div class="fw-semibold">
                                            Brute force terdeteksi
                                        </div>
                                        <small class="text-muted">
                                            IP <?= htmlspecialchars($notification['ip_address']) ?>
                                            pada <?= date('d-m-Y H:i',strtotime($notification['detected_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </li>

                    <?php } ?>

                    <?php while($blockedIp = mysqli_fetch_assoc($notificationBlockedIps)){ ?>

                        <li>
                            <a class="dropdown-item py-2" href="blocked_ips.php">
                                <div class="d-flex gap-2">
                                    <i class="bi bi-ban text-warning"></i>
                                    <div>
                                        <div class="fw-semibold">
                                            IP diblokir
                                        </div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($blockedIp['ip_address']) ?>
                                            sampai <?= date('d-m-Y H:i',strtotime($blockedIp['blocked_until'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        </li>

                    <?php } ?>

                    <?php if($notificationCount > 0){ ?>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <div class="d-flex gap-2 px-3 pb-2">
                                <a class="btn btn-primary btn-sm flex-fill" href="detection_logs.php">
                                    Lihat Log
                                </a>

                                <form method="POST" action="app/controllers/NotificationController.php" class="flex-fill">
                                    <?= csrf_field(); ?>
                                    <button class="btn btn-outline-secondary btn-sm w-100">
                                        Tandai Dibaca
                                    </button>
                                </form>
                            </div>
                        </li>

                    <?php } ?>

                </ul>

            </li>

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle d-flex align-items-center profile-toggle"
                    href="#"
                    data-bs-toggle="dropdown">

                    <?php if($navbarHasPhoto){ ?>

                        <img
                            src="<?= htmlspecialchars($navbarPhotoPath) ?>"
                            class="rounded-circle profile-photo"
                            width="45"
                            height="45"
                            style="object-fit:cover;"
                            alt="Foto profil">

                    <?php }else{ ?>

                        <span
                            class="profile-initial">
                            <?= htmlspecialchars($navbarInitial) ?>
                        </span>

                    <?php } ?>

                    <div class="ms-3 profile-copy">

                        <div class="fw-bold">

                            <?= htmlspecialchars($_SESSION['fullname']) ?>

                        </div>

                        <small>

                            <?= htmlspecialchars($_SESSION['role']) ?>

                        </small>

                    </div>

                </a>

                <ul class="dropdown-menu dropdown-menu-end shadow">

                    <li>

                        <a class="dropdown-item" href="profile.php">

                            <i class="bi bi-person-circle me-2"></i>

                            Profil

                        </a>

                    </li>

                    <li>

                        <a class="dropdown-item" href="settings.php">

                            <i class="bi bi-gear me-2"></i>

                            Pengaturan

                        </a>

                    </li>

                    <li>

                        <hr class="dropdown-divider">

                    </li>

                    <li>

                        <form method="POST" action="logout.php">

                            <?= csrf_field(); ?>

                            <button type="submit" class="dropdown-item text-danger">

                                <i class="bi bi-box-arrow-right me-2"></i>

                                Logout

                            </button>

                        </form>

                    </li>

                </ul>

            </li>

        </ul>

    </div>

</nav>

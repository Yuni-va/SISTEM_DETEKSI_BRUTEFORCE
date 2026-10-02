<?php

require_once "config/database.php";
require_once "app/helpers/Security.php";
require_once "app/models/Setting.php";

$settingModel = new Setting($conn);
$sidebarMenus = $settingModel->getActiveMenus();

?>

<div class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <div class="logo">
            <i class="bi bi-shield-fill-check"></i>
        </div>

        <div class="system-name">
            <h5>Sistem Deteksi</h5>
            <small>Brute Force</small>
        </div>
    </div>

    <ul class="sidebar-menu">

        <?php while($menu=mysqli_fetch_assoc($sidebarMenus)){ 
            $isActive = basename($_SERVER['PHP_SELF'])==$menu['url'];
        ?>

            <li class="<?= $isActive? 'active' : '' ?>">
                <a href="<?= htmlspecialchars($menu['url']) ?>" class="menu-link <?= $isActive? 'menu-active' : '' ?>" title="<?= htmlspecialchars($menu['label']) ?>">
                    <i class="<?= htmlspecialchars($menu['icon']) ?>"></i>
                    <span class="menu-label"><?= htmlspecialchars($menu['label']) ?></span>
                    <?php if(!empty($menu['badge'])): ?>
                        <span class="menu-badge"><?= htmlspecialchars($menu['badge']) ?></span>
                    <?php endif; ?>
                </a>
            </li>

        <?php } ?>

        <li class="logout-menu">
            <form method="POST" action="logout.php">
                <?= csrf_field(); ?>
                <button type="submit" class="btn w-100 text-start">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </button>
            </form>
        </li>

    </ul>

    <div class="sidebar-status">
        <div class="sidebar-status-title">System Status</div>
        <div class="sidebar-status-main">
            <span class="status-shield"><i class="bi bi-shield-check"></i></span>
            <div>
                <strong>Protected</strong>
                <small>Detection engine aktif</small>
            </div>
        </div>
        <div class="sidebar-status-row">
            <span>Mode</span>
            <strong>Threshold</strong>
        </div>
        <div class="sidebar-status-row">
            <span>Last Update</span>
            <strong><?= date('H:i:s') ?></strong>
        </div>
    </div>

</div>

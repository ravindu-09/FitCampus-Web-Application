<?php
// views/common/notifications.php

// Include Page Controller ONLY (No Direct Database Queries)
require_once '../../controllers/common/notifications_page_controller.php';

// 1. Load Appropriate Header
if ($role === 'admin') {
    require_once '../../includes/headers/header_admin.php';
} else {
    require_once '../../includes/headers/header_member.php';
}
?>

<!-- Load specific Notification CSS -->
<link rel="stylesheet" href="../../assets/css/common/notifications.css">

<?php if ($role === 'admin'): ?>
    <!-- ADMIN LAYOUT STRUCTURE                     -->
    <div class="admin-viewport-wrapper">
        <?php require_once '../../includes/sidebars/sidebar_admin.php'; ?>
        <main class="admin-main-canvas">

<?php else: ?>
    <!-- MEMBER / CAPTAIN LAYOUT STRUCTURE          -->
    <?php 
        if ($is_captain) {
            require_once '../../includes/sidebars/sidebar_captain.php';
        } else {
            require_once '../../includes/sidebars/sidebar_member.php';
        }
    ?>
    <div class="member-content-wrapper">
        <main class="dashboard-main-container">
<?php endif; ?>

            <!-- Notification Content Start -->
            <div class="nt-mobile-header">
                <h1 class="nt-title" style="font-size: 24px;">Notifications</h1>
            </div>

            <div class="nt-container">
                <!-- Category: System Announcements (Common) -->
                <div class="nt-category-group">
                    <h3 class="nt-category-title">System Announcements</h3>
                    
                    <?php if (empty($announcements)): ?>
                        <p class="text-muted" style="font-size: 13px;">No recent system announcements.</p>
                    <?php else: ?>
                        <?php foreach ($announcements as $ann): ?>
                            <div class="nt-card nt-border-tertiary">
                                <div class="nt-icon-box bg-tertiary-dim border-tertiary-dim text-tertiary">
                                    <span class="material-symbols-outlined">campaign</span>
                                </div>
                                <div class="nt-content">
                                    <div class="nt-head">
                                        <h4 class="nt-title"><?php echo htmlspecialchars($ann['Title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <span class="nt-time"><?php echo date('M d, g:i A', strtotime($ann['Publish_Date'])); ?></span>
                                    </div>
                                    <p class="nt-desc"><?php echo nl2br(htmlspecialchars($ann['Description'], ENT_QUOTES, 'UTF-8')); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Category: Personal Alerts (Specific to User) -->
                <div class="nt-category-group mt-4">
                    <h3 class="nt-category-title">Personal Alerts</h3>
                    
                    <?php if (empty($personal_notifications)): ?>
                        <p class="text-muted" style="font-size: 13px;">You have no personal alerts.</p>
                    <?php else: ?>
                        <?php foreach ($personal_notifications as $notif): ?>
                            <div class="nt-card nt-border-primary" id="notif-card-<?php echo $notif['Notification_ID']; ?>">
                                <div class="nt-icon-box bg-primary-dim border-primary-dim text-primary">
                                    <span class="material-symbols-outlined">notifications_active</span>
                                </div>
                                <div class="nt-content">
                                    <div class="nt-head">
                                        <h4 class="nt-title"><?php echo htmlspecialchars($notif['Title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <span class="nt-time"><?php echo date('M d, g:i A', strtotime($notif['Created_At'])); ?></span>
                                    </div>
                                    <p class="nt-desc"><?php echo nl2br(htmlspecialchars($notif['Message'], ENT_QUOTES, 'UTF-8')); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
            <!-- Notification Content End -->

        </main>
    </div> <!-- Proper Wrapper Closure for both roles -->

<?php 
// Load Dynamic Bottombars
if ($role === 'admin') {
    require_once '../../includes/bottombar/bottombar_admin.php';
} else {
    if ($is_captain) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
}

// Load Footer
require_once '../../includes/footers/footer_common.php'; 
?>
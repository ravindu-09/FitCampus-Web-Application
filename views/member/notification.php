<?php
// views/member/notifications.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Notifications | FitCampus";

// JS specific to dashboard layout
$extra_js = [
    "member/dashboard.js"
];

require_once '../../includes/db_connection.php';
require_once '../../includes/headers/header_member.php';

$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;

if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        
        <div class="nt-mobile-header">
            <h1 class="settings-main-title">Notifications</h1>
        </div>

        <div class="nt-container">
            
            <!-- Category: Gym Alerts -->
            <div class="nt-category-group">
                <h3 class="nt-category-title">Gym Alerts</h3>
                
                <div class="glass-card nt-card nt-border-tertiary">
                    <div class="nt-icon-box bg-tertiary-dim border-tertiary-dim text-tertiary">
                        <span class="material-symbols-outlined">warning</span>
                    </div>
                    <div class="nt-content">
                        <div class="nt-head">
                            <h4 class="nt-title">Gym 01 Near Capacity</h4>
                            <span class="nt-time">10m ago</span>
                        </div>
                        <p class="nt-desc">Main weights area is currently at 90% capacity. Consider visiting during off-peak hours for a better experience.</p>
                        <div class="nt-actions">
                            <button class="btn btn-outline btn-sm">Check Live Stats</button>
                        </div>
                    </div>
                </div>

                <div class="glass-card nt-card nt-card-muted">
                    <div class="nt-icon-box bg-surface-dim border-surface-dim text-muted">
                        <span class="material-symbols-outlined">info</span>
                    </div>
                    <div class="nt-content">
                        <div class="nt-head">
                            <h4 class="nt-title">Maintenance Complete</h4>
                            <span class="nt-time">Yesterday</span>
                        </div>
                        <p class="nt-desc">Treadmill #4 in Cardio Zone B has been repaired and is now available for use.</p>
                    </div>
                </div>
            </div>

            <!-- Category: Personal Achievements -->
            <div class="nt-category-group">
                <h3 class="nt-category-title">Personal Achievements</h3>
                
                <div class="glass-card nt-card nt-border-primary nt-glow-primary">
                    <div class="nt-icon-box bg-primary-dim border-primary-dim text-primary">
                        <span class="material-symbols-outlined">local_fire_department</span>
                    </div>
                    <div class="nt-content">
                        <div class="nt-head">
                            <h4 class="nt-title">Daily Goal Crushed!</h4>
                            <span class="nt-time">2h ago</span>
                        </div>
                        <p class="nt-desc">You've reached your daily goal of 2500 active calories. Keep up the phenomenal work!</p>
                        <div class="nt-actions">
                            <button class="btn btn-primary btn-sm">View Details</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category: System Updates -->
            <div class="nt-category-group">
                <h3 class="nt-category-title">System Updates</h3>
                
                <div class="glass-card nt-card nt-border-secondary nt-card-muted">
                    <div class="nt-icon-box bg-secondary-dim border-secondary-dim text-secondary">
                        <span class="material-symbols-outlined">update</span>
                    </div>
                    <div class="nt-content">
                        <div class="nt-head">
                            <h4 class="nt-title">New Feature: Trainer Ratings</h4>
                            <span class="nt-time">Oct 12</span>
                        </div>
                        <p class="nt-desc">You can now rate and review trainers after your sessions. Help the community by sharing your feedback.</p>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <?php 
    if ($is_captain) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    ?>

    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>
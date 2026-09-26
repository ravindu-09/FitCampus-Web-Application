<?php
// includes/headers/header_member.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is a captain
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;

// Dynamic path prefixing based on current directory[cite: 43]
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$member_prefix = ($current_dir === 'captain' || $current_dir === 'common') ? '../member/' : '';
$captain_prefix = ($current_dir === 'member' || $current_dir === 'common') ? '../captain/' : '';

// Dynamic Page Title
$default_title = $is_captain ? 'FitCampus - Captain Portal' : 'FitCampus - Member Portal';
$page_title = isset($page_title) ? $page_title : $default_title;

$display_first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Member';
$display_life = isset($_SESSION['life_percentage']) ? (int)$_SESSION['life_percentage'] : 100;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>

    <!-- Base Token Stylesheets -->
    <link rel="stylesheet" href="../../assets/css/base/main.css">
    <link rel="stylesheet" href="../../assets/css/base/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/base/components.css">

    <!-- Role Stylesheet -->
    <link rel="stylesheet" href="../../assets/css/roles/member.css">
    <!-- Captain Specific Stylesheet (Loads only for Captains) -->
    <?php if ($is_captain): ?>
        <link rel="stylesheet" href="../../assets/css/roles/captain.css">
    <?php endif; ?>
</head>
<body class="member-layout-body">

    <!-- Ambient Glowing Effects -->
    <div class="purple-glow top-left"></div>
    <div class="purple-glow bottom-right"></div>

    <!-- Top Navigation Header -->
    <header class="member-top-header">
        <div class="header-left">
            <button id="sidebarToggleBtn" class="menu-toggle-btn" type="button" aria-label="Toggle Sidebar">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h1 class="header-brand-title">FitCampus</h1>

            <!-- Vertical Divider -->
            <div class="divider-vertical desktop-only"></div>

            <div class="user-greeting desktop-only">
                <span class="greeting-heading">Hello, <?php echo htmlspecialchars($display_first_name); ?></span>
                <span class="greeting-sub">Ready to crush your goals today?</span>
            </div>
        </div>

        <div class="header-right">
            <!-- Notification link updated to common folder[cite: 43] -->
            <a href="../../views/common/notifications.php" class="notif-btn" aria-label="Notifications" style="text-decoration: none;">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notif-dot"></span>
            </a>
            
            <div class="life-status-pill">
                <div class="life-status-labels">
                    <span class="life-tag">Life</span>
                    <span class="life-value"><?php echo $display_life; ?>%</span>
                </div>
                <div class="life-progress-bar">
                    <div class="life-progress-fill" style="width: <?php echo $display_life; ?>%;"></div>
                </div>
            </div>
        </div>
    </header>
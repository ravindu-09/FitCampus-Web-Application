<?php
// includes/headers/header_admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin Security & Route Protection Guard
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    $_SESSION['error'] = "Unauthorized access. Please login as an administrator.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$admin_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin User';
// Extract first name for the greeting
$display_first_name = explode(' ', $admin_name)[0];

// Dynamic path prefixing based on current directory 
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$common_prefix = ($current_dir === 'common') ? '' : '../common/';

// Dynamic Page Title
$default_title = 'FitCampus - Admin Console';
$page_title = isset($page_title) ? $page_title : $default_title;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></title>
    
    <!-- Base & Role CSS Stack -->
    <link rel="stylesheet" href="../../assets/css/base/main.css">
    <link rel="stylesheet" href="../../assets/css/base/components.css">
    <link rel="stylesheet" href="../../assets/css/base/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/roles/admin.css">
</head>
<body class="admin-layout-body">

    <!-- Top Appbar -->
    <header class="admin-topbar">
        <div class="topbar-left">
            <!-- Sidebar Toggle Button -->
            <button id="sidebarToggleBtn" class="menu-toggle-btn" type="button" aria-label="Toggle Sidebar">
                <span class="material-symbols-outlined">menu</span>
            </button>

            <h1 class="topbar-brand-title">FitCampus <span>Admin</span></h1>

            <!-- Vertical Divider -->
            <div class="divider-vertical desktop-only"></div>

            <!-- Dynamic Admin Greeting -->
            <div class="user-greeting desktop-only">
                <span class="greeting-heading">Hello, <?php echo htmlspecialchars($display_first_name, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="greeting-sub">Ready to manage the system today?</span>
            </div>
        </div>

        <div class="topbar-right">
            <!-- Notification link dynamically routed to common folder -->
            <a href="<?php echo $common_prefix; ?>notifications.php" class="topbar-icon-btn" title="View Notifications">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge-dot"></span>
            </a>

            <div class="topbar-profile-pill">
                
                <div class="topbar-avatar-wrapper">
                    <span class="material-symbols-outlined" style="color: var(--primary);">admin_panel_settings</span>
                </div>
                
                <div class="topbar-admin-meta">
                    <span class="admin-name"><?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="admin-role">System Manager</span>
                </div>
            </div>
        </div>
    </header>
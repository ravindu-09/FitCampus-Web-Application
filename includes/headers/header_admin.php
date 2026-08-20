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

$admin_name = htmlspecialchars($_SESSION['full_name'] ?? 'Admin User', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'FitCampus Admin Console'; ?></title>
    
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
            <span class="material-symbols-outlined topbar-logo-icon fill">fitness_center</span>
            <h1 class="topbar-brand-title">FitCampus <span>Admin</span></h1>
        </div>

        <div class="topbar-right">
            <a href="../../views/admin/alerts.php" class="topbar-icon-btn" title="View Alerts">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge-dot"></span>
            </a>

            <div class="topbar-profile-pill">
                <div class="topbar-avatar-wrapper">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <div class="topbar-admin-meta">
                    <span class="admin-name"><?php echo $admin_name; ?></span>
                    <span class="admin-role">System Manager</span>
                </div>
            </div>
        </div>
    </header>
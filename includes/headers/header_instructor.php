<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Security Guard: Check if the user is logged in and has the 'instructor' role
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'instructor') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Fetch the instructor's full name from the session, with a fallback default
$instructor_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Instructor';

// Extract only the first name for the personalized greeting
$display_first_name = explode(' ', $instructor_name)[0];

// Dynamic path prefixing based on the current directory for correct routing
$common_prefix = (basename(dirname($_SERVER['PHP_SELF'])) === 'common') ? '' : '../common/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitCampus - Instructor Console</title>
    
    <!-- Base CSS Stylesheets -->
    <link rel="stylesheet" href="../../assets/css/base/main.css">
    <link rel="stylesheet" href="../../assets/css/base/components.css">
    <link rel="stylesheet" href="../../assets/css/base/glassmorphism.css">
    
    <!-- Role Specific Stylesheet (Versioning added to prevent browser cache issues) -->
    <link rel="stylesheet" href="../../assets/css/roles/instructor.css?v=<?php echo time(); ?>">
</head>
<body class="instructor-layout-body">

    <!-- Top Navigation Header -->
    <header class="admin-topbar">
        
        <!-- Left Side: Sidebar Toggle, Brand Identity & Greeting -->
        <div class="topbar-left" style="display: flex; align-items: center; gap: 16px;">
            <!-- Menu Toggle Button -->
            <button id="sidebarToggleBtn" class="menu-toggle-btn" type="button" aria-label="Toggle Sidebar" style="background: none; border: none; color: white; cursor: pointer;">
                <span class="material-symbols-outlined">menu</span>
            </button>
            
            <!-- Brand Title -->
            <h1 class="topbar-brand-title" style="margin: 0;">FitCampus <span>Instructor</span></h1>
            
            <!-- Vertical Divider (Hidden on mobile) -->
            <div class="divider-vertical desktop-only" style="width: 1px; height: 24px; background: rgba(255,255,255,0.1); margin: 0 16px;"></div>
            
            <!-- Personalized Greeting (Hidden on mobile) -->
            <div class="user-greeting desktop-only" style="display: flex; flex-direction: column;">
                <span style="font-weight: bold; font-size: 15px;">Hello, <?php echo htmlspecialchars($display_first_name); ?></span>
                <span style="font-size: 11px; color: var(--on-surface-variant);">Manage facility routines & athletes.</span>
            </div>
        </div>

        <!-- Right Side: Notifications & User Profile -->
        <div class="topbar-right" style="display: flex; align-items: center; gap: 16px;">
            
            <!-- Notifications Button -->
            <a href="<?php echo $common_prefix; ?>notifications.php" class="topbar-icon-btn" aria-label="Notifications">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge-dot"></span>
            </a>
            
            <!-- Profile Wrapper -->
            <div class="topbar-profile-pill" style="display: flex; align-items: center; gap: 10px;">
                
                <!-- Profile Avatar/Icon (Always visible on both Desktop and Mobile) -->
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(74,225,118,0.1); display: flex; align-items: center; justify-content: center; border: 1px solid rgba(74,225,118,0.3);">
                    <span class="material-symbols-outlined" style="color: var(--secondary);">sports</span>
                </div>
                
                <!-- Profile Text details (Visible only on Desktop) -->
                <div class="desktop-only" style="display: flex; flex-direction: column;">
                    <span style="font-weight: bold; font-size: 14px;"><?php echo htmlspecialchars($instructor_name); ?></span>
                    <span style="font-size: 11px; color: gray;">Gym Instructor</span>
                </div>
                
            </div>
        </div>
    </header>
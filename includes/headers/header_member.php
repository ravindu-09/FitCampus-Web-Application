<?php
// includes/headers/header_member.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Guard: Member role check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') {
    $_SESSION['error'] = "Please log in to access your portal.";
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_name  = htmlspecialchars($_SESSION['full_name'] ?? 'Member', ENT_QUOTES, 'UTF-8');
$is_captain = !empty($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'FitCampus Member Portal'; ?></title>
    
    <link rel="stylesheet" href="../../assets/css/base/main.css">
    <link rel="stylesheet" href="../../assets/css/base/components.css">
    <link rel="stylesheet" href="../../assets/css/base/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/roles/member.css">
</head>
<body class="member-layout-body">

    <header class="member-topbar">
        <div class="topbar-left">
            <span class="material-symbols-outlined topbar-logo-icon fill">fitness_center</span>
            <h1 class="topbar-brand-title">FitCampus <span>Portal</span></h1>
        </div>

        <div class="topbar-right">
            <div class="topbar-profile-pill">
                <div class="topbar-avatar-wrapper">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <div class="topbar-user-meta">
                    <span class="user-name"><?php echo $user_name; ?></span>
                    <span class="user-role <?php echo $is_captain ? 'badge-captain-gold' : ''; ?>">
                        <?php echo $is_captain ? '★ Team Captain' : 'Undergraduate'; ?>
                    </span>
                </div>
            </div>
        </div>
    </header>
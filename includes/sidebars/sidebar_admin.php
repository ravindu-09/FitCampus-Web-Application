<?php
// includes/sidebars/sidebar_admin.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Desktop Sidebar -->
<aside class="admin-sidebar">
    <div class="sidebar-header-card">
        <div class="sidebar-avatar">
            <span class="material-symbols-outlined" style="font-size: 24px; color: var(--primary);">admin_panel_settings</span>
        </div>
        <div class="sidebar-meta">
            <span class="meta-title">FitCampus Admin</span>
            <span class="meta-sub">Univ. of Colombo</span>
        </div>
    </div>

    <nav class="sidebar-nav-list">
        <a href="analytics.php" class="sidebar-nav-item <?php echo ($current_page == 'analytics.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">analytics</span>
            <span>Analytics</span>
        </a>
        <a href="bookings.php" class="sidebar-nav-item <?php echo ($current_page == 'bookings.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">calendar_month</span>
            <span>Bookings</span>
        </a>
        <a href="verification.php" class="sidebar-nav-item <?php echo ($current_page == 'verification.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">verified_user</span>
            <span>Verification</span>
        </a>
        <a href="users.php" class="sidebar-nav-item <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">group</span>
            <span>Users</span>
        </a>
        <a href="alerts.php" class="sidebar-nav-item <?php echo ($current_page == 'alerts.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">campaign</span>
            <span>Alerts</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="../../backend/auth/logout.php" class="btn-sidebar-logout">
            <span class="material-symbols-outlined">logout</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<!-- Mobile Bottom Navigation Bar -->
<nav class="admin-mobile-nav">
    <a href="analytics.php" class="mobile-nav-item <?php echo ($current_page == 'analytics.php') ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">analytics</span>
        <span>Analytics</span>
    </a>
    <a href="bookings.php" class="mobile-nav-item <?php echo ($current_page == 'bookings.php') ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">calendar_month</span>
        <span>Bookings</span>
    </a>
    <a href="verification.php" class="mobile-nav-item <?php echo ($current_page == 'verification.php') ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">verified_user</span>
        <span>Verification</span>
    </a>
    <a href="users.php" class="mobile-nav-item <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">group</span>
        <span>Users</span>
    </a>
    <a href="alerts.php" class="mobile-nav-item <?php echo ($current_page == 'alerts.php') ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">campaign</span>
        <span>Alerts</span>
    </a>
</nav>
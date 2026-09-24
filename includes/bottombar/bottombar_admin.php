<?php
// includes/bottombar/bottombar_admin.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Bottom Navigation Bar (Modular Component) -->
<nav class="mobile-bottom-nav">
    <a class="mob-nav-item <?php echo ($current_page === 'analytics.php') ? 'active' : ''; ?>" href="analytics.php">
        <span class="material-symbols-outlined">analytics</span>
        <span>Analytics</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'bookings.php') ? 'active' : ''; ?>" href="bookings.php">
        <span class="material-symbols-outlined">calendar_month</span>
        <span>Bookings</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'verification.php') ? 'active' : ''; ?>" href="verification.php">
        <span class="material-symbols-outlined">verified_user</span>
        <span>Verify</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'users.php') ? 'active' : ''; ?>" href="users.php">
        <span class="material-symbols-outlined">group</span>
        <span>Users</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'alerts.php') ? 'active' : ''; ?>" href="alerts.php">
        <span class="material-symbols-outlined">campaign</span>
        <span>Alerts</span>
    </a>
</nav>
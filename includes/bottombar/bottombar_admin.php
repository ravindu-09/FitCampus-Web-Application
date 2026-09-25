<?php
// includes/bottombar/bottombar_admin.php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$admin_prefix = ($current_dir === 'common') ? '../admin/' : '';
?>
<!-- Mobile Bottom Navigation Bar (Modular Component) -->
<nav class="mobile-bottom-nav">
    <a class="mob-nav-item <?php echo ($current_page === 'analytics.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>analytics.php">
        <span class="material-symbols-outlined">analytics</span>
        <span>Analytics</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'bookings.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>bookings.php">
        <span class="material-symbols-outlined">calendar_month</span>
        <span>Bookings</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'verification.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>verification.php">
        <span class="material-symbols-outlined">verified_user</span>
        <span>Verify</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'users.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>users.php">
        <span class="material-symbols-outlined">group</span>
        <span>Users</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'alerts.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>alerts.php">
        <span class="material-symbols-outlined">campaign</span>
        <span>Alerts</span>
    </a>
</nav>
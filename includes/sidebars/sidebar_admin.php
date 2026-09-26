<?php
// includes/sidebars/sidebar_admin.php
$current_script = basename($_SERVER['PHP_SELF']);
$admin_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin User';
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$admin_prefix = ($current_dir === 'common') ? '../admin/' : '';
?>
<!-- Sidebar Drawer Navigation -->
<aside id="memberSidebar" class="member-sidebar">
    <div class="sidebar-brand-header">
        <h1 class="brand-heading">FitCampus</h1>
        <button id="closeSidebarBtn" class="close-sidebar-btn" type="button" aria-label="Close Sidebar">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <nav class="sidebar-nav-list">
        <a class="side-nav-item <?php echo ($current_script === 'analytics.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>analytics.php">
            <span class="material-symbols-outlined">analytics</span>
            <span>Analytics</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'bookings.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>bookings.php">
            <span class="material-symbols-outlined">calendar_month</span>
            <span>Bookings</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'verification.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>verification.php">
            <span class="material-symbols-outlined">verified_user</span>
            <span>Verification</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'users.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>users.php">
            <span class="material-symbols-outlined">group</span>
            <span>Users Hub</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'alerts.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>alerts.php">
            <span class="material-symbols-outlined">campaign</span>
            <span>Alerts</span>
        </a>
    </nav>

    <!-- Sidebar Bottom Profile & Settings -->
    <div class="sidebar-footer">
        <div class="sidebar-profile-card">
            <div class="sidebar-profile-content">
                <div class="profile-icon-circle">
                    <span class="material-symbols-outlined" style="color: var(--primary);">admin_panel_settings</span>
                </div>
                <div class="profile-text-wrap">
                    <p class="profile-name"><?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="profile-role">System Manager</p>
                </div>
            </div>
        </div>

        <!-- Path updated from backend to controllers -->
        <a class="sidebar-settings-link" href="../../controllers/auth/logout.php">
            <span class="material-symbols-outlined">logout</span>
            <span>Sign Out</span>
        </a>

        <a class="sidebar-settings-link <?php echo ($current_script === 'settings.php') ? 'active' : ''; ?>" href="<?php echo $admin_prefix; ?>settings.php">
            <span class="material-symbols-outlined">settings</span>
            <span>Settings</span>
        </a>
    </div>
</aside>

<!-- Independent Global Backdrop Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>
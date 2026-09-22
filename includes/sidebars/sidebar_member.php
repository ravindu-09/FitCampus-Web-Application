<?php
// includes/sidebars/sidebar_member.php
$current_script = basename($_SERVER['PHP_SELF']);
$user_display_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Student Member';
$user_reg_no = isset($_SESSION['reg_no']) ? $_SESSION['reg_no'] : 'Student';

// Uploaded Avatar path check
$profile_img_name = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'default_avatar.png';
$has_custom_avatar = !empty($profile_img_name) && $profile_img_name !== 'default_avatar.png' && file_exists('../../assets/images/uploads/' . $profile_img_name);
$avatar_url = '../../assets/images/uploads/' . htmlspecialchars($profile_img_name);
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
        <a class="side-nav-item <?php echo ($current_script === 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'workouts.php') ? 'active' : ''; ?>" href="workouts.php">
            <span class="material-symbols-outlined">fitness_center</span>
            <span>Workout Plan</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'calories.php') ? 'active' : ''; ?>" href="calories.php">
            <span class="material-symbols-outlined">local_fire_department</span>
            <span>Calorie Track</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'goals.php') ? 'active' : ''; ?>" href="goals.php">
            <span class="material-symbols-outlined">flag</span>
            <span>My Goals</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'leaderboard.php') ? 'active' : ''; ?>" href="leaderboard.php">
            <span class="material-symbols-outlined">leaderboard</span>
            <span>Leaderboard</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'teams.php') ? 'active' : ''; ?>" href="teams.php">
            <span class="material-symbols-outlined">groups</span>
            <span>Team</span>
        </a>
    </nav>

    <!-- Sidebar Bottom Profile & Settings -->
    <div class="sidebar-footer">
        <div class="sidebar-profile-card">
            <div class="sidebar-profile-content">
                <div class="profile-icon-circle">
                    <?php if ($has_custom_avatar): ?>
                        <img src="<?php echo $avatar_url; ?>" alt="Profile Photo" class="sidebar-avatar-img">
                    <?php else: ?>
                        <span class="material-symbols-outlined">person</span>
                    <?php endif; ?>
                </div>
                <div class="profile-text-wrap">
                    <p class="profile-name"><?php echo htmlspecialchars($user_display_name); ?></p>
                    <p class="profile-role"><?php echo htmlspecialchars($user_reg_no); ?></p>
                </div>
            </div>
        </div>

        <a class="sidebar-settings-link <?php echo ($current_script === 'settings.php') ? 'active' : ''; ?>" href="settings.php">
            <span class="material-symbols-outlined">settings</span>
            <span>Settings</span>
        </a>
    </div>
</aside>

<!-- Independent Global Backdrop Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>
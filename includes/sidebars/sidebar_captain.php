<?php
// includes/sidebars/sidebar_captain.php
$current_script = basename($_SERVER['PHP_SELF']);
$user_display_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Alex Johnson';
$user_reg_no = isset($_SESSION['reg_no']) ? $_SESSION['reg_no'] : 'Captain';

// Dynamic path prefixing based on current directory (member vs captain)
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$member_prefix = ($current_dir === 'captain') ? '../member/' : '';
$captain_prefix = ($current_dir === 'captain') ? '' : '../captain/';

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

    <nav class="sidebar-nav-list custom-scrollbar">
        <!-- Standard Member Links -->
        <a class="side-nav-item <?php echo ($current_script === 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>dashboard.php">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'workouts.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>workouts.php">
            <span class="material-symbols-outlined">fitness_center</span>
            <span>Workout Plan</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'calories.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>calories.php">
            <span class="material-symbols-outlined">local_fire_department</span>
            <span>Calorie Track</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'goals.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>goals.php">
            <span class="material-symbols-outlined">flag</span>
            <span>My Goals</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'leaderboard.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>leaderboard.php">
            <span class="material-symbols-outlined">leaderboard</span>
            <span>Leaderboard</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'teams.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>teams.php">
            <span class="material-symbols-outlined">groups</span>
            <span>Team</span>
        </a>

        <!-- Horizontal Divider -->
        <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 8px 0;"></div>

        <!-- Captain Specific Links -->
        <a class="side-nav-item <?php echo ($current_script === 'booking.php' || $current_script === 'facilities.php') ? 'active' : ''; ?>" href="<?php echo $captain_prefix; ?>booking.php">
            <span class="material-symbols-outlined">apartment</span>
            <span>Facilities</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'roster.php') ? 'active' : ''; ?>" href="<?php echo $captain_prefix; ?>roster.php">
            <span class="material-symbols-outlined">groups</span>
            <span>Team Roster</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'planner.php') ? 'active' : ''; ?>" href="<?php echo $captain_prefix; ?>planner.php">
            <span class="material-symbols-outlined">event_available</span>
            <span>Team Workout Planner</span>
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

        <a class="sidebar-settings-link" href="../../backend/auth/logout.php">
            <span class="material-symbols-outlined">logout</span>
            <span>Sign Out</span>
        </a>

        <a class="sidebar-settings-link <?php echo ($current_script === 'settings.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>settings.php">
            <span class="material-symbols-outlined">settings</span>
            <span>Settings</span>
        </a>
    </div>
</aside>

<!-- Independent Global Backdrop Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>
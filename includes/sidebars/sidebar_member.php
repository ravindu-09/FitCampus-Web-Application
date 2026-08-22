<?php
// includes/sidebars/sidebar_member.php
$current_page = basename($_SERVER['PHP_SELF']);
$is_captain   = !empty($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
?>

<!-- Desktop Sidebar -->
<aside class="member-sidebar">
    <div class="sidebar-header-card">
        <div class="sidebar-avatar">
            <span class="material-symbols-outlined" style="font-size: 24px; color: var(--primary);">
                <?php echo $is_captain ? 'military_tech' : 'person'; ?>
            </span>
        </div>
        <div class="sidebar-meta">
            <span class="meta-title"><?php echo $is_captain ? 'Varsity Captain' : 'Student Member'; ?></span>
            <span class="meta-sub">Univ. of Colombo</span>
        </div>
    </div>

    <nav class="sidebar-nav-list">
        <!-- 1. Common Member Links (views/member/) -->
        <a href="../member/dashboard.php" class="sidebar-nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>
        <a href="../member/goals.php" class="sidebar-nav-item <?php echo ($current_page == 'goals.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">track_changes</span>
            <span>Goals</span>
        </a>
        <a href="../member/leaderboard.php" class="sidebar-nav-item <?php echo ($current_page == 'leaderboard.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">leaderboard</span>
            <span>Leaderboard</span>
        </a>
        <a href="../member/workouts.php" class="sidebar-nav-item <?php echo ($current_page == 'workouts.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">fitness_center</span>
            <span>Workouts</span>
        </a>
        <a href="../member/calories.php" class="sidebar-nav-item <?php echo ($current_page == 'calories.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">local_fire_department</span>
            <span>Calories</span>
        </a>

        <!-- 2. Captain Special Features (views/captain/) -->
        <?php if ($is_captain): ?>
            <div class="sidebar-section-divider">Captain Portal</div>
            
            <a href="../captain/roaster.php" class="sidebar-nav-item captain-feature <?php echo ($current_page == 'roaster.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">groups</span>
                <span>Team Roster</span>
            </a>
            <a href="../captain/booking.php" class="sidebar-nav-item captain-feature <?php echo ($current_page == 'booking.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">event_seat</span>
                <span>Team Bookings</span>
            </a>
            <a href="../captain/planner.php" class="sidebar-nav-item captain-feature <?php echo ($current_page == 'planner.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">event_note</span>
                <span>Workout Planner</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="../../backend/auth/logout.php" class="btn-sidebar-logout">
            <span class="material-symbols-outlined">logout</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<!-- Mobile Bottom Navigation Bar -->
<nav class="member-mobile-nav">
    <!-- Common Mobile Items -->
    <a href="../member/dashboard.php" class="mobile-nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">dashboard</span>
        <span>Home</span>
    </a>
    <a href="../member/goals.php" class="mobile-nav-item <?php echo ($current_page == 'goals.php') ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">track_changes</span>
        <span>Goals</span>
    </a>
    <a href="../member/leaderboard.php" class="mobile-nav-item <?php echo ($current_page == 'leaderboard.php') ? 'active' : ''; ?>">
        <span class="material-symbols-outlined">leaderboard</span>
        <span>Board</span>
    </a>

    <!-- Captain Exclusive Mobile Items -->
    <?php if ($is_captain): ?>
        <a href="../captain/roaster.php" class="mobile-nav-item <?php echo ($current_page == 'roaster.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">groups</span>
            <span>Roster</span>
        </a>
        <a href="../captain/booking.php" class="mobile-nav-item <?php echo ($current_page == 'booking.php') ? 'active' : ''; ?>">
            <span class="material-symbols-outlined">event_seat</span>
            <span>Booking</span>
        </a>
    <?php endif; ?>
</nav>
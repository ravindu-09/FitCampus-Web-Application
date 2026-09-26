<?php
// includes/bottombar/bottombar_member.php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$member_prefix = ($current_dir === 'common') ? '../member/' : '';
?>
<!-- Mobile Bottom Navigation Bar (Modular Component) -->
<nav class="mobile-bottom-nav">
    <a class="mob-nav-item <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>dashboard.php">
        <span class="material-symbols-outlined">dashboard</span>
        <span>Dashboard</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'workouts.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>workouts.php">
        <span class="material-symbols-outlined">fitness_center</span>
        <span>Workouts</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'calories.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>calories.php">
        <span class="material-symbols-outlined">local_fire_department</span>
        <span>Calories</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'goals.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>goals.php">
        <span class="material-symbols-outlined">flag</span>
        <span>Goals</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'leaderboard.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>leaderboard.php">
        <span class="material-symbols-outlined">leaderboard</span>
        <span>Ranks</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'teams.php') ? 'active' : ''; ?>" href="<?php echo $member_prefix; ?>teams.php">
        <span class="material-symbols-outlined">groups</span>
        <span>Team</span>
    </a>
</nav>
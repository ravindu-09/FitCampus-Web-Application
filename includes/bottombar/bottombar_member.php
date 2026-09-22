<?php
// includes/bottombar/bottombar_member.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Bottom Navigation Bar (Modular Component) -->
<nav class="mobile-bottom-nav">
    <a class="mob-nav-item <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
        <span class="material-symbols-outlined">dashboard</span>
        <span>Dashboard</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'workouts.php') ? 'active' : ''; ?>" href="workouts.php">
        <span class="material-symbols-outlined">fitness_center</span>
        <span>Workouts</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'calories.php') ? 'active' : ''; ?>" href="calories.php">
        <span class="material-symbols-outlined">local_fire_department</span>
        <span>Calories</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'goals.php') ? 'active' : ''; ?>" href="goals.php">
        <span class="material-symbols-outlined">flag</span>
        <span>Goals</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'leaderboard.php') ? 'active' : ''; ?>" href="leaderboard.php">
        <span class="material-symbols-outlined">leaderboard</span>
        <span>Ranks</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'teams.php') ? 'active' : ''; ?>" href="teams.php">
        <span class="material-symbols-outlined">groups</span>
        <span>Team</span>
    </a>
</nav>
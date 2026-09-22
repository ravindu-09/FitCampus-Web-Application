<?php
// includes/bottombar/bottombar_captain.php
$current_page = basename($_SERVER['PHP_SELF']);
$team_pages = ['facilities.php', 'booking.php', 'roster.php', 'planner.php', 'teams.php'];
$is_team_active = in_array($current_page, $team_pages);

$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$member_prefix = ($current_dir === 'captain') ? '../member/' : '';
$captain_prefix = ($current_dir === 'captain') ? '' : '../captain/';
?>
<!-- Mobile Bottom Navigation Bar (Captain Component) -->
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

    <!-- Team / Captain Actions Dropdown -->
    <div class="mob-nav-item-group" id="captainTeamGroup">
        <button class="mob-nav-item <?php echo $is_team_active ? 'active' : ''; ?>" id="captainTeamBtn" style="border: none; background: transparent; width: 100%; padding: 6px 0;" type="button">
            <span class="material-symbols-outlined" <?php echo $is_team_active ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>groups</span>
            <span>Team</span>
        </button>

        <!-- Popup Menu -->
        <div class="captain-dropdown-menu" id="captainTeamMenu">
            <a href="<?php echo $member_prefix; ?>teams.php" class="capt-drop-item <?php echo ($current_page === 'teams.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">groups</span> Team
            </a>
            <a href="<?php echo $captain_prefix; ?>booking.php" class="capt-drop-item <?php echo ($current_page === 'booking.php' || $current_page === 'facilities.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">apartment</span> Facilities
            </a>
            <a href="<?php echo $captain_prefix; ?>roster.php" class="capt-drop-item <?php echo ($current_page === 'roster.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">groups</span> Team Roster
            </a>
            <a href="<?php echo $captain_prefix; ?>planner.php" class="capt-drop-item <?php echo ($current_page === 'planner.php') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">event_available</span> Planner
            </a>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const teamBtn = document.getElementById('captainTeamBtn');
        const teamMenu = document.getElementById('captainTeamMenu');

        if(teamBtn && teamMenu) {
            teamBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                teamMenu.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!teamMenu.contains(e.target) && !teamBtn.contains(e.target)) {
                    teamMenu.classList.remove('show');
                }
            });
        }
    });
</script>
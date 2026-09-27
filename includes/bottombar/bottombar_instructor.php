<?php
$current_page = basename($_SERVER['PHP_SELF']);
$instructor_prefix = (basename(dirname($_SERVER['PHP_SELF'])) === 'common') ? '../instructor/' : '';
?>
<nav class="mobile-bottom-nav">
    <a class="mob-nav-item <?php echo ($current_page === 'kiosk.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>kiosk.php">
        <span class="material-symbols-outlined">qr_code_scanner</span> <span>Kiosk</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'inventory.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>inventory.php">
        <span class="material-symbols-outlined">inventory_2</span> <span>Inventory</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'workouts.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>workouts.php">
        <span class="material-symbols-outlined">fitness_center</span> <span>Workouts</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'rules.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>rules.php">
        <span class="material-symbols-outlined">gavel</span> <span>Rules</span>
    </a>
    <a class="mob-nav-item <?php echo ($current_page === 'updates.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>updates.php">
        <span class="material-symbols-outlined">campaign</span> <span>Broadcasts</span>
    </a>
</nav>
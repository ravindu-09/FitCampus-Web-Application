<?php
$current_script = basename($_SERVER['PHP_SELF']);
$instructor_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Instructor';
$instructor_prefix = (basename(dirname($_SERVER['PHP_SELF'])) === 'common') ? '../instructor/' : '';
?>
<aside id="memberSidebar" class="member-sidebar">
    <div class="sidebar-brand-header" style="height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <h1 class="brand-heading" style="margin: 0; font-size: 20px; color: white;">FitCampus</h1>
        <button id="closeSidebarBtn" class="close-sidebar-btn" type="button" aria-label="Close Sidebar">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    
    <nav class="sidebar-nav-list custom-scrollbar" style="padding: 24px 16px; display: flex; flex-direction: column; gap: 8px;">
        <a class="side-nav-item <?php echo ($current_script === 'kiosk.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>kiosk.php">
            <span class="material-symbols-outlined">qr_code_scanner</span> <span>Check-in Kiosk</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'inventory.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>inventory.php">
            <span class="material-symbols-outlined">inventory_2</span> <span>Equipment Inventory</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'workouts.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>workouts.php">
            <span class="material-symbols-outlined">fitness_center</span> <span>Workout Plans</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'rules.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>rules.php">
            <span class="material-symbols-outlined">gavel</span> <span>Facility Rules</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'updates.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>updates.php">
            <span class="material-symbols-outlined">campaign</span> <span>Staff Broadcasts</span>
        </a>
    </nav>
    
    <div class="sidebar-footer" style="margin-top: auto; padding: 24px 16px; border-top: 1px solid rgba(255,255,255,0.05);display: flex; flex-direction: column; gap: 4px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
            <span class="material-symbols-outlined" style="color: var(--secondary);">sports</span>
            <div>
                <p style="margin: 0; font-weight: bold;"><?php echo htmlspecialchars($instructor_name); ?></p>
                <p style="margin: 0; font-size: 11px; color: gray;">Instructor</p>
            </div>
        </div>
        <a class="side-nav-item" href="../../controllers/auth/logout.php" style="color: var(--error);">
            <span class="material-symbols-outlined">logout</span> <span>Sign Out</span>
        </a>
        <a class="side-nav-item <?php echo ($current_script === 'settings.php') ? 'active' : ''; ?>" href="<?php echo $instructor_prefix; ?>settings.php">
            <span class="material-symbols-outlined">settings</span> <span>Settings</span>
        </a>
    </div>
</aside>

<div id="sidebarOverlay" class="sidebar-overlay"></div>
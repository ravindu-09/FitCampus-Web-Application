<?php
// views/member/team_roster.php
require_once '../../includes/db_connection.php';

$page_title = "Varsity Team Roster - FitCampus";
require_once '../../includes/headers/header_member.php';

// Route Protection: Normal Members cannot access this view
if (empty($_SESSION['is_captain']) || $_SESSION['is_captain'] != 1) {
    $_SESSION['error'] = "Access denied. Only Team Captains can access this page.";
    header("Location: dashboard.php");
    exit();
}
?>

<div class="member-viewport-wrapper">
    <!-- Dynamic Sidebar (Desktop & Mobile) -->
    <?php include_once '../../includes/sidebars/sidebar_member.php'; ?>

    <!-- Main Workspace Canvas (Ready for your content) -->
    <main class="member-main-canvas">
        
        <!-- Captain Content-->

    </main>
</div>

<?php require_once '../../includes/footers/footer_common.php'; ?>
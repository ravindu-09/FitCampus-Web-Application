<?php
// views/member/dashboard.php
require_once '../../includes/db_connection.php';

$page_title = "Member Dashboard - FitCampus";
require_once '../../includes/headers/header_member.php';
?>

<div class="member-viewport-wrapper">
    <!-- Dynamic Sidebar (Desktop & Mobile) -->
    <?php include_once '../../includes/sidebars/sidebar_member.php'; ?>

    <!-- Main Workspace Canvas (Ready for your content) -->
    <main class="member-main-canvas">
        
        <!-- Content-->

    </main>
</div>

<?php require_once '../../includes/footers/footer_common.php'; ?>
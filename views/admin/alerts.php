<?php
// views/admin/alerts.php
require_once '../../includes/db_connection.php';
require_once '../../includes/headers/header_admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Alerts &amp; Broadcasts - FitCampus</title>
    
    <link rel="stylesheet" href="../../assets/css/base/main.css">
    <link rel="stylesheet" href="../../assets/css/base/components.css">
    <link rel="stylesheet" href="../../assets/css/base/glassmorphism.css">
    <link rel="stylesheet" href="../../assets/css/roles/admin.css">
</head>
<body class="admin-layout-body">

    <div class="admin-viewport-wrapper">
        <?php include_once '../../includes/sidebars/sidebar_admin.php'; ?>

        <main class="admin-main-canvas">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Emergency Broadcasts &amp; Facility Alerts</h2>
                    <p class="section-subtitle">Deploy operational notices, maintenance downtimes, and team announcements</p>
                </div>
            </div>

            <div class="alerts-grid-layout">
                <div class="glass-card p-6">
                    <h3 class="card-inner-title">Compose Alert</h3>
                    <form method="POST" action="../../backend/admin/broadcast_alert.php" class="flex-col-gap-14">
                        <div class="form-group">
                            <label class="form-label">Target Audience</label>
                            <select name="audience" class="form-control pl-14">
                                <option value="all">All Members &amp; Instructors</option>
                                <option value="instructors">Instructors Only</option>
                                <option value="captains">Team Captains Only</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alert Header</label>
                            <input type="text" name="title" class="form-control pl-14" placeholder="e.g. Free Weights Area Maintenance" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Broadcast Details</label>
                            <textarea name="message" class="form-control pl-14 h-100" placeholder="Enter specific times and instructions..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary mt-8">
                            <span class="material-symbols-outlined">send</span>
                            Deploy Broadcast
                        </button>
                    </form>
                </div>

                <div class="glass-card p-6">
                    <h3 class="card-inner-title">Active Notices</h3>
                    <div class="flex-col-gap-12">
                        <div class="alert-notice-card">
                            <div class="font-bold text-white">Cardio Zone Open Access</div>
                            <p class="notice-desc">Smart rowers calibration is complete. All slots available.</p>
                            <span class="notice-expiry">Expires in 2 days</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php 
    include_once '../../includes/footers/footer_common.php'; 
    ?>
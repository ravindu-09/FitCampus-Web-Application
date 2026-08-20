<?php
// views/admin/analytics.php
require_once '../../includes/db_connection.php';
require_once '../../includes/headers/header_admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Analytics Dashboard - FitCampus</title>
    
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
                    <h2 class="section-title">Institutional Facility Telemetry</h2>
                    <p class="section-subtitle">Real-time gym capacity, attendance velocity, and throughput analytics</p>
                </div>
            </div>

            <div class="kpi-grid">
                <div class="kpi-card glass-card">
                    <div class="kpi-title-row">
                        <span class="kpi-title">Daily Attendance</span>
                        <span class="material-symbols-outlined text-primary">groups</span>
                    </div>
                    <div class="kpi-value" id="kpi-attendance-val">450</div>
                    <span class="kpi-sub text-secondary" id="kpi-attendance-sub">↑ 12% Peak capacity</span>
                </div>

                <div class="kpi-card glass-card">
                    <div class="kpi-title-row">
                        <span class="kpi-title">Prime Usage Band</span>
                        <span class="material-symbols-outlined text-primary">schedule</span>
                    </div>
                    <div class="kpi-value kpi-value-sm" id="kpi-peak-val">4PM - 6PM</div>
                    <span class="kpi-sub">7-Day average cycle</span>
                </div>

                <div class="kpi-card glass-card">
                    <div class="kpi-title-row">
                        <span class="kpi-title">Active Members</span>
                        <span class="material-symbols-outlined text-primary">school</span>
                    </div>
                    <div class="kpi-value" id="kpi-students-val">1.2K</div>
                    <span class="kpi-sub text-secondary" id="kpi-students-pct">Enrolled undergraduates</span>
                </div>

                <div class="kpi-card glass-card border-left-warning">
                    <div class="kpi-title-row">
                        <span class="kpi-title">Pending Approvals</span>
                        <span class="material-symbols-outlined text-warning">pending_actions</span>
                    </div>
                    <div class="kpi-value text-warning">14</div>
                    <span class="kpi-sub">Awaiting verification</span>
                </div>
            </div>

            <div class="glass-card p-6">
                <h3 class="card-inner-title">System Audit Logs</h3>
                <div class="admin-table-container">
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>Action Event</th>
                                <th>Operator</th>
                                <th>Timestamp</th>
                                <th class="text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>User Verification Approved</td>
                                <td>System Administrator</td>
                                <td class="date-tag"><?php echo date("M d, H:i"); ?></td>
                                <td class="text-right"><span class="badge-status text-secondary">Success</span></td>
                            </tr>
                            <tr>
                                <td>Facility Slot Allocation Override</td>
                                <td>Admin_Desk</td>
                                <td class="date-tag"><?php echo date("M d, H:i", strtotime("-1 hour")); ?></td>
                                <td class="text-right"><span class="badge-status text-primary">Processed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php 
    $extra_js = "admin/analytics-charts.js";
    include_once '../../includes/footers/footer_common.php'; 
    ?>
<?php
// views/admin/bookings.php
require_once '../../includes/db_connection.php';
require_once '../../includes/headers/header_admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Booking Manager - FitCampus</title>
    
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
                    <h2 class="section-title">Facility Slot &amp; Team Bookings</h2>
                    <p class="section-subtitle">Manage university varsity teams and manual booking overrides</p>
                </div>
            </div>

            <div class="glass-card p-6">
                <h3 class="card-inner-title">Special Requests (Manual Intervention)</h3>
                <div class="admin-table-container">
                    <table class="admin-data-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Facility Zone</th>
                                <th>Athletic Team</th>
                                <th>Scheduled Time</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-mono text-primary">#BK-105</td>
                                <td>Gym 01 (Weights)</td>
                                <td>
                                    <div class="font-bold">Badminton Varsity</div>
                                    <span class="meta-subtext">Squad: 25 Players</span>
                                </td>
                                <td class="date-tag">Today | 14:00 - 16:00</td>
                                <td class="text-right">
                                    <button class="btn btn-primary btn-sm">Approve</button>
                                    <button class="btn btn-glass btn-sm text-error">Decline</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <?php 
    include_once '../../includes/footers/footer_common.php'; 
    ?>
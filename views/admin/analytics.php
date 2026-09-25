<?php
// views/admin/analytics.php

// Include database connection and define the dynamic page title
require_once '../../includes/db_connection.php';
$page_title = 'Executive Analytics Dashboard - FitCampus';

// 1. Load Admin Header, Topbar, and Session Security Guard
require_once '../../includes/headers/header_admin.php';
?>

<div class="admin-viewport-wrapper">
    
    <!-- 2. Load the main Sidebar drawer navigation-->
    <?php include_once '../../includes/sidebars/sidebar_admin.php'; ?>

    <main class="admin-main-canvas">
        
        <!-- Top Controls: Facility Filter & Download Button -->
        <div class="analytics-controls bk-mb-md">
            <div class="input-wrapper">
                <select id="facilityFilter" class="form-control select-custom analytics-select">
                    <option value="all">🟢 All Facilities</option>
                    <option value="gym1">Gym 01</option>
                    <option value="gym2">Gym 02</option>
                </select>
            </div>
            <button id="downloadAnalysisBtn" class="btn btn-glass analytics-btn">
                <span class="material-symbols-outlined" style="font-size: 18px;">download</span> Download Analysis
            </button>
        </div>

        <!-- KPI Grid Cards: Key Performance Indicators Overview -->
        <div class="verification-stats-grid analytics-kpi-grid bk-mb-md">
            
            <!-- KPI Card 1: Total Daily Attendance -->
            <div class="glass-card stat-summary-box analytics-card">
                <div class="stat-top-row">
                    <span class="stat-label stat-label-text">TOTAL DAILY ATTENDANCE</span>
                    <span class="material-symbols-outlined text-primary" style="font-size: 20px;">groups</span>
                </div>
                <div class="stat-value-row">
                    <div class="stat-value stat-value-text">450</div>
                    <span class="stat-change text-secondary">↑12%</span>
                </div>
                <div class="stat-note-text">Gym 01 & Gym 02</div>
            </div>

            <!-- KPI Card 2: Peak Usage Time -->
            <div class="glass-card stat-summary-box analytics-card">
                <div class="stat-top-row">
                    <span class="stat-label stat-label-text">PEAK USAGE TIME</span>
                    <span class="material-symbols-outlined text-primary" style="font-size: 20px;">schedule</span>
                </div>
                <div class="stat-value stat-value-text-md">4PM - 6PM</div>
                <div class="stat-note-text">Based on 7-day average</div>
            </div>

            <!-- KPI Card 3: Active Students -->
            <div class="glass-card stat-summary-box analytics-card">
                <div class="stat-top-row">
                    <span class="stat-label stat-label-text">ACTIVE STUDENTS</span>
                    <span class="material-symbols-outlined text-primary" style="font-size: 20px;">school</span>
                </div>
                <div class="stat-value-row">
                    <div class="stat-value stat-value-text">1.2K</div>
                    <span class="stat-change text-secondary">↑4%</span>
                </div>
                <div class="stat-note-text">Currently enrolled</div>
            </div>

            <!-- KPI Card 4: Pending Requests (Tertiary Action Card) -->
            <div class="glass-card stat-summary-box analytics-card analytics-card-tertiary">
                <div class="stat-top-row tertiary-padding">
                    <span class="stat-label stat-label-text">PENDING REQUESTS</span>
                    <span class="material-symbols-outlined text-tertiary" style="font-size: 20px;">assignment_late</span>
                </div>
                <div class="stat-value stat-value-text tertiary-padding">12</div>
                <div class="stat-note-text tertiary-padding">Requires action</div>
                <button class="btn btn-sm w-full mt-3 btn-review">Review</button>
            </div>

        </div>

        <!-- Facility Utilization Section: Bar Chart Visualization -->
        <div class="glass-card p-4 analytics-card bk-mb-md">
            <div class="flex-between bk-mb-md">
                <h3 class="section-title">Facility Utilization</h3>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-dot dot-gym1"></span> Gym 01</span>
                    <span class="legend-item"><span class="legend-dot dot-gym2"></span> Gym 02</span>
                </div>
            </div>
            
            <div class="chart-container">
                <div class="chart-column"><div class="bar-gym1" style="height: 40%;"></div><div class="bar-gym2" style="height: 25%;"></div></div>
                <div class="chart-column"><div class="bar-gym1" style="height: 60%;"></div><div class="bar-gym2" style="height: 35%;"></div></div>
                <div class="chart-column"><div class="bar-gym1" style="height: 80%;"></div><div class="bar-gym2" style="height: 55%;"></div></div>
                <div class="chart-column"><div class="bar-gym1" style="height: 95%;"></div><div class="bar-gym2" style="height: 70%;"></div></div>
                <div class="chart-column"><div class="bar-gym1" style="height: 70%;"></div><div class="bar-gym2" style="height: 50%;"></div></div>
                <div class="chart-column"><div class="bar-gym1" style="height: 40%;"></div><div class="bar-gym2" style="height: 20%;"></div></div>
                <div class="chart-column"><div class="bar-gym1" style="height: 25%;"></div><div class="bar-gym2" style="height: 15%;"></div></div>
            </div>
            <div class="chart-x-axis">
                <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
            </div>
        </div>

        <!-- System Audit Logs Section: Recent Administrator Actions -->
        <div class="glass-card p-4 analytics-card">
            <div class="flex-between bk-mb-md">
                <h3 class="section-title">Recent System Audit Logs</h3>
                <button id="downloadSystemReportBtn" class="btn btn-glass btn-sm analytics-btn" style="font-size: 11px; padding: 6px 12px;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">download</span> Download Report
                </button>
            </div>
            <div class="admin-table-container">
                <table class="admin-data-table">
                    <thead>
                        <tr class="table-header-row">
                            <th>ACTION</th>
                            <th>ADMIN</th>
                            <th class="text-right">TIME</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="action-cell">
                                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">check_circle</span>
                                    <span class="action-text">Booking Approved</span>
                                </div>
                            </td>
                            <td class="admin-name">Admin_01</td>
                            <td class="text-right log-time">10:23 AM</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php 
// 3. Load the Mobile Bottom Navigation Bar for smaller screens
include_once '../../includes/bottombar/bottombar_admin.php';

// 4. Register the page-specific JavaScript file to be injected by the footer
$extra_js = "admin/analytics-charts.js";

// 5. Load the Universal Footer and Script Drivers
include_once '../../includes/footers/footer_common.php'; 
?>
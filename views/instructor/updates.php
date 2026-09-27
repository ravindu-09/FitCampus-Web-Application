<?php
// views/instructor/updates.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../controllers/instructor/updates_page_controller.php';
require_once '../../includes/headers/header_instructor.php';
require_once '../../includes/sidebars/sidebar_instructor.php';
?>

<!-- Ambient Glow Effects -->
<div class="purple-glow top-left"></div>
<div class="purple-glow bottom-right"></div>

<div class="instructor-viewport-wrapper">
    <main class="main-content">
        <div class="dashboard-main-container bk-main-container updates-main-wrapper">
            
            <!-- Page Header -->
            <div class="content-header-row mb-4 updates-header-row">
                <h2 class="page-main-heading m-0">Updates Console</h2>
                <p class="page-sub-heading m-0">Broadcast motivational messages to the student feed or issue critical facility alerts across the campus network.</p>
            </div>

            <!-- Two-Column Grid Layout -->
            <div class="updates-grid-layout">
                
                <!-- Left Column: Motivational Messaging & Active Feed -->
                <div class="updates-col-flex">
                    
                    <!-- Composer Card -->
                    <div class="glass-card bk-card-pad updates-card">
                        <div class="updates-card-header">
                            <span class="material-symbols-outlined text-primary updates-icon-box">edit_note</span>
                            <h3 class="settings-main-title text-base m-0">Motivational Message Composer</h3>
                        </div>
                        <form class="updates-form">
                            <div class="form-group-cal">
                                <label class="gl-lbl-accent">BROADCAST MESSAGE</label>
                                <textarea class="cal-input-field updates-textarea" rows="4" placeholder="Draft your daily motivation for the student body..."></textarea>
                            </div>
                            <button type="button" class="btn btn-primary w-full updates-broadcast-btn">
                                <span class="material-symbols-outlined updates-icon-sm">send</span> Broadcast Motivation
                            </button>
                        </form>
                    </div>

                    <!-- Active Motivations List -->
                    <div class="glass-card bk-card-pad updates-card">
                        <div class="flex-between updates-active-top">
                            <h3 class="settings-main-title text-base m-0">Active Motivations</h3>
                            <span class="gl-badge updates-badge-pill">3 Active</span>
                        </div>
                        <div class="updates-list-wrapper">
                            <div class="updates-item-box">
                                <div class="updates-item-top">
                                    <span class="updates-dot-indicator"></span>
                                    <span class="updates-mono-lbl">Pinned</span>
                                    <span class="updates-time-text">Posted 2h ago</span>
                                </div>
                                <p class="updates-item-desc">"Discipline is choosing between what you want now and what you want most. Crush your goals today!"</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: System Notifications & Alert History -->
                <div class="updates-col-flex">
                    
                    <!-- Alerts Form -->
                    <div class="glass-card bk-card-pad updates-card updates-alert-card">
                        <div class="updates-card-header">
                            <span class="material-symbols-outlined text-error updates-icon-box-error">warning</span>
                            <div>
                                <h3 class="settings-main-title text-base m-0">System Notifications</h3>
                                <p class="text-regular-sub text-xs m-0 updates-sub-text">Emergency or Facility Alerts</p>
                            </div>
                        </div>
                        <form class="updates-form">
                            <div class="form-group-cal">
                                <label class="gl-lbl-accent">ALERT TITLE</label>
                                <input class="cal-input-field" type="text" placeholder="e.g. Gym 02 Maintenance">
                            </div>
                            <div class="form-group-cal">
                                <label class="gl-lbl-accent">MESSAGE DETAILS</label>
                                <textarea class="cal-input-field updates-textarea" rows="3" placeholder="Provide details about the alert..."></textarea>
                            </div>
                            <button type="button" class="btn updates-btn-alert">
                                <span class="material-symbols-outlined updates-icon-sm">notifications_active</span> Alert Students
                            </button>
                        </form>
                    </div>

                    <!-- Recent Notifications History -->
                    <div class="glass-card bk-card-pad updates-card updates-history-card">
                        <h3 class="settings-main-title text-base m-0 updates-history-title">Recent Notifications</h3>
                        <div class="updates-timeline">
                            <div class="updates-timeline-item">
                                <div class="updates-timeline-dot"></div>
                                <span class="updates-mono-lbl">Today, 09:30 AM</span>
                                <h4 class="updates-timeline-heading">Pool Closure</h4>
                                <p class="updates-timeline-desc">Pool area closed for unscheduled cleaning until 2PM.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </main>
</div>

<?php 
require_once '../../includes/bottombar/bottombar_instructor.php'; 
require_once '../../includes/footers/footer_common.php'; 
?>
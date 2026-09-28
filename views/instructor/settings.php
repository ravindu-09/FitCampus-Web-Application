<?php
// views/instructor/settings.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../controllers/instructor/setting_page_controller.php';
require_once '../../includes/headers/header_instructor.php';
require_once '../../includes/sidebars/sidebar_instructor.php';
?>

<!-- Ambient Glow Effects -->
<div class="purple-glow top-left"></div>
<div class="purple-glow bottom-right"></div>

<div class="instructor-viewport-wrapper">
    <main class="main-content">
        <div class="dashboard-main-container bk-main-container settings-main-wrapper">
            
            <!-- Page Header -->
            <div class="content-header-row mb-4 settings-header-row">
                <div>
                    <h2 class="page-main-heading m-0">Instructor Control Settings</h2>
                    <p class="page-sub-heading m-0">Manage facility operations, live headcounts, and member discipline securely.</p>
                </div>
            </div>

            <!-- Simulated Alerts -->
            <?php if (isset($_SESSION['settings_success'])): ?>
                <div class="settings-alert-success">
                    <?= htmlspecialchars($_SESSION['settings_success']); unset($_SESSION['settings_success']); ?>
                </div>
            <?php endif; ?>

            <div class="settings-sections-wrapper">

                <!-- SECTION 1: Facility Open / Close Controls -->
                <div class="glass-card bk-card-pad settings-section-card">
                    <h3 class="settings-main-title text-base settings-title-primary">Facility Operational Status</h3>
                    <form action="" method="POST" onsubmit="event.preventDefault(); alert('Facility status updated (Simulation Mode)!');" class="settings-form-layout">
                        <div class="settings-form-row">
                            <div class="form-group-cal settings-form-group">
                                <label class="gl-lbl-accent">SELECT ACTION</label>
                                <select class="cal-input-field gl-select">
                                    <option value="open">Open Facility Now</option>
                                    <option value="close">Close Facility Immediately</option>
                                </select>
                            </div>
                            <div class="form-group-cal settings-form-group">
                                <label class="gl-lbl-accent">CONFIRM INSTRUCTOR PASSWORD</label>
                                <input type="password" class="cal-input-field" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-primary settings-btn-submit">Update Status</button>
                        </div>
                    </form>
                </div>

                <!-- SECTION 2: Manual Live Count Adjustments (+ / -) -->
                <div class="glass-card bk-card-pad settings-section-card secondary-border">
                    <h3 class="settings-main-title text-base settings-title-secondary">Manual Live Count Override</h3>
                    <form action="" method="POST" onsubmit="event.preventDefault(); alert('Live count adjusted (Simulation Mode)!');" class="settings-form-layout">
                        <div class="settings-form-row">
                            <div class="form-group-cal settings-form-group">
                                <label class="gl-lbl-accent">ADJUSTMENT ACTION</label>
                                <select class="cal-input-field gl-select">
                                    <option value="1">+1 (Add Student Manually)</option>
                                    <option value="-1">-1 (Deduct Student Manually)</option>
                                </select>
                            </div>
                            <div class="form-group-cal settings-form-group">
                                <label class="gl-lbl-accent">CONFIRM INSTRUCTOR PASSWORD</label>
                                <input type="password" class="cal-input-field" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-glass settings-btn-secondary">Apply Count</button>
                        </div>
                    </form>
                </div>

                <!-- SECTION 3: Issue Penalty Feature -->
                <div class="glass-card bk-card-pad settings-section-card warning-border">
                    <h3 class="settings-main-title text-base settings-title-error">Issue Member Penalty</h3>
                    <form action="" method="POST" onsubmit="event.preventDefault(); alert('Penalty successfully issued (Simulation Mode)!');" class="settings-form-layout">
                        <div class="settings-grid-2">
                            <div class="form-group-cal m-0">
                                <label class="gl-lbl-accent">STUDENT REGISTRATION NUMBER</label>
                                <input type="text" class="cal-input-field" placeholder="e.g. STU-8921" required>
                            </div>
                            <div class="form-group-cal m-0">
                                <label class="gl-lbl-accent">PENALTY VALUE / AMOUNT</label>
                                <input type="text" class="cal-input-field" placeholder="e.g. 500 LKR or 10 Points" required>
                            </div>
                        </div>

                        <div class="form-group-cal m-0">
                            <label class="gl-lbl-accent">REASON / DESCRIPTION</label>
                            <textarea class="cal-input-field settings-textarea" rows="2" placeholder="Explain rule violation reason..." required></textarea>
                        </div>

                        <div class="settings-form-row settings-mt-sm">
                            <div class="form-group-cal settings-form-group-wide">
                                <label class="gl-lbl-accent">CONFIRM INSTRUCTOR PASSWORD (REQUIRED)</label>
                                <input type="password" class="cal-input-field" placeholder="Enter your password to authorize penalty" required>
                            </div>
                            <button type="submit" class="btn settings-btn-error">Issue Penalty</button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </main>
</div>

<?php 
require_once '../../includes/bottombar/bottombar_instructor.php'; 
require_once '../../includes/footers/footer_common.php'; 
?>
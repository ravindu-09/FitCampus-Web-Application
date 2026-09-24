<?php
// views/captain/booking.php

// Check session status and initiate if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect unauthorized users to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Facilities Booking | FitCampus";

// Include page-specific JS script
$extra_js = [
    "captain/booking.js"
];

require_once '../../includes/db_connection.php';
require_once '../../includes/headers/header_member.php';
require_once '../../includes/sidebars/sidebar_captain.php';

// Fetch all teams where the current user holds the 'Captain' role
$captain_teams = [];
try {
    $stmt = $pdo->prepare("
        SELECT t.Team_ID, t.Team_Name, t.Sport, 
               (SELECT COUNT(*) FROM team_member WHERE Team_ID = t.Team_ID) as Member_Count 
        FROM team t 
        JOIN team_member tm ON t.Team_ID = tm.Team_ID 
        WHERE tm.User_ID = ? AND tm.Role_In_Team = 'Captain'
    ");
    $stmt->execute([$user_id]);
    $captain_teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Fetch all facilities to generate toggle buttons
$facilities = [];
try {
    $stmtFac = $pdo->query("SELECT Facility_ID, Facility_Name FROM facility ORDER BY Facility_ID ASC");
    $facilities = $stmtFac->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Fetch the booking history specific to the logged-in captain
$booking_history = [];
try {
    $stmtHist = $pdo->prepare("
        SELECT b.*, f.Facility_Name, t.Team_Name 
        FROM booking b
        JOIN facility f ON b.Facility_ID = f.Facility_ID
        JOIN team t ON b.Team_ID = t.Team_ID
        WHERE b.Requested_By = ?
        ORDER BY b.Reserve_Date DESC, b.Start_Time DESC
    ");
    $stmtHist->execute([$user_id]);
    $booking_history = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>

<!-- Main layout wrapper -->
<div class="member-content-wrapper">
    <main class="dashboard-main-container bk-main-container">
        
        <!-- Two-column grid layout for calendar and booking form -->
        <div class="grid-layout-booking">
            
            <!-- Left Column: Calendar Grid Section -->
            <section class="booking-calendar-section">
                <div class="glass-card inner-glow bk-card-pad overflow-hidden">
                    <!-- Calendar Header and Filters -->
                    <div class="cal-header-flex bk-mb-lg">
                        <div>
                            <h2 class="settings-main-title m-0">Weekly Availability</h2>
                            <p class="txt-muted text-sm bk-mt-xs m-0">Select an open slot to begin your request</p>
                        </div>
                        
                        <!-- Facility and Shift Toggles -->
                        <div class="booking-toggles">
                            <div class="toggle-group" id="gym-toggle-container">
                                <?php if (!empty($facilities)): ?>
                                    <?php foreach ($facilities as $index => $fac): ?>
                                        <button class="toggle-btn <?= $index === 0 ? 'active' : '' ?>" id="btn-gym-0<?= $fac['Facility_ID'] ?>" data-facility-id="<?= $fac['Facility_ID'] ?>" type="button">
                                            <?= htmlspecialchars($fac['Facility_Name']) ?>
                                        </button>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <button class="toggle-btn active" type="button">No Facilities Found</button>
                                <?php endif; ?>
                            </div>
                            <div class="toggle-group" id="time-toggle-container">
                                <button class="toggle-btn active" id="btn-morning" type="button">Morning (6AM-12PM)</button>
                                <button class="toggle-btn" id="btn-evening" type="button">Evening (12PM-6PM)</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Week Navigator -->
                    <div class="date-navigator bk-mb-md">
                        <button class="nav-arrow" id="btn-prev-week" type="button"><span class="material-symbols-outlined">chevron_left</span></button>
                        <span class="mono font-bold text-sm" id="week-range-display">Loading Dates...</span>
                        <button class="nav-arrow" id="btn-next-week" type="button"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>

                    <!-- Swipe hint for mobile users -->
                    <div class="mobile-swipe-hint">
                        <span class="material-symbols-outlined" style="font-size: 14px;">swipe</span> Swipe horizontally to see more
                    </div>

                    <!-- Scrollable Calendar Grid -->
                    <div class="grid-table-wrapper custom-scrollbar">
                        <div class="grid-table-inner">
                            <div class="calendar-grid-header border-b-dim bk-pb-sm bk-mb-sm" id="calendar-grid-header"></div>
                            <div class="grid-body-rows" id="schedule-grid-body"></div>
                        </div>
                    </div>

                    <!-- Slot Status Legend -->
                    <div class="status-legend">
                        <div class="legend-item"><span class="dot bg-secondary"></span>Available (0%)</div>
                        <div class="legend-item"><span class="dot bg-tertiary"></span>Moderate (1-90%)</div>
                        <div class="legend-item"><span class="dot bg-error"></span>High (&gt; 90%)</div>
                        <div class="legend-item"><span class="dot outline-dot"></span>Full (100%)</div>
                        <div class="legend-item"><span class="dot pending-dot"></span>Pending (Approval)</div>
                        <div class="legend-item"><span class="dot bg-primary"></span>Selected</div>
                    </div>
                </div>
            </section>

            <!-- Right Column: Booking Request Form Sidebar -->
            <aside class="booking-form-section">
                <div class="glass-card bk-card-pad shadow-xl sticky-form">
                    
                    <!-- Form Header -->
                    <div class="form-header bk-mb-lg">
                        <div class="flex-between bk-mb-sm">
                            <h3 class="settings-main-title text-lg m-0">Booking Request</h3>
                            <span class="gl-badge badge-bg-primary txt-primary mono uppercase font-bold font-xs">Limit For Per Week : 3 Sessions</span>
                        </div>
                    </div>

                    <!-- Dynamic Capacity Info (Hidden until slot selected) -->
                    <div class="bk-mb-md hidden" id="capacity-info-container">
                        <div class="flex-between mono font-xs txt-white uppercase font-bold">
                            <span>Remaining Capacity</span>
                            <span class="txt-green font-bold text-sm" id="remaining-capacity-val">0</span>
                        </div>
                    </div>

                    <form id="facility-booking-form" class="booking-form space-y-md">
                        <!-- Date Display -->
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent">SELECTED DATE</label>
                            <div class="cal-input-field flex-items-center gap-3">
                                <span class="material-symbols-outlined txt-primary" style="font-size: 20px;">calendar_today</span>
                                <span class="font-medium text-sm" id="selected-date-display">Select a slot</span>
                            </div>
                        </div>

                        <!-- Time & Duration Inputs -->
                        <div class="cal-grid-2 gap-3">
                            <div class="form-group-cal space-y-xs">
                                <label class="gl-lbl-accent">START TIME</label>
                                <select class="cal-input-field gl-select" id="start-time-select" disabled>
                                    <option value="">--:--</option>
                                </select>
                            </div>
                            <div class="form-group-cal space-y-xs">
                                <label class="gl-lbl-accent">DURATION</label>
                                <select class="cal-input-field gl-select" id="duration-select">
                                    <option value="1">1.0 Hour</option>
                                    <option value="2">2.0 Hours</option>
                                </select>
                            </div>
                        </div>

                        <!-- Team Selection Dropdown -->
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent">SELECT TEAM</label>
                            <select class="cal-input-field gl-select" id="team-select">
                                <option disabled selected value="">Select Your Team</option>
                                <?php if (!empty($captain_teams)): ?>
                                    <?php foreach ($captain_teams as $team): ?>
                                        <option value="<?= $team['Team_ID'] ?>" data-size="<?= $team['Member_Count'] ?>">
                                            <?= htmlspecialchars($team['Team_Name']) ?> (<?= htmlspecialchars($team['Sport']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option disabled value="">No teams assigned</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Custom Team Size Input -->
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent">TEAM SIZE</label>
                            <input class="cal-input-field" id="team-size-input" type="number" min="1" placeholder="Enter team size">
                        </div>

                        <!-- Read-only Captain ID -->
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent">CAPTAIN ID</label>
                            <input class="cal-input-field txt-primary mono tracking-wide font-bold" type="text" readonly value="<?= $user_id ?>">
                        </div>

                        <!-- Special Request Toggle Checkbox -->
                        <div class="checkbox-wrapper bk-mt-md">
                            <input type="checkbox" id="limit-toggle" class="custom-checkbox">
                            <label for="limit-toggle" class="text-sm cursor-pointer select-none">Simulate Limit Exceeded / Special Request</label>
                        </div>

                        <!-- Hidden Exception Reason Textarea -->
                        <div class="form-group-cal space-y-xs hidden" id="special-request-field">
                            <label class="gl-lbl-accent">REASON FOR REQUEST <span class="txt-error">*</span></label>
                            <textarea class="cal-input-field" id="reason-input" placeholder="Explain why you need this extra session..." style="min-height: 80px;"></textarea>
                        </div>

                        <!-- Error Message & Form Submit Button -->
                        <div class="form-group-cal space-y-xs bk-mt-md">
                            <p class="txt-error font-xs hidden font-medium m-0 bk-mb-xs text-center" id="capacity-error-msg"></p>
                            <button class="gl-btn-gradient w-full bk-py-md font-bold text-sm flex-items-center gap-2 justify-center" id="submit-booking-btn" type="button" disabled>
                                <span class="material-symbols-outlined text-[20px]" id="btn-icon">check_circle</span>
                                <span id="btn-text">Confirm Selection</span>
                            </button>
                            <p class="text-center font-xs bk-mt-xs txt-muted m-0" id="booking-note">Standard bookings are instantly reserved.</p>
                        </div>
                    </form>
                </div>
            </aside>
        </div>

        <!-- Full Width History Section -->
        <section class="glass-card bk-card-pad bk-mt-lg">
            <div class="cal-header-flex bk-mb-md">
                <h3 class="settings-main-title text-lg m-0">My Booking History</h3>
                <span class="txt-muted text-sm">Track your past and active reservations</span>
            </div>
            
            <div class="admin-table-container custom-scrollbar">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>Team</th>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop through booking records fetched from DB -->
                        <?php if (!empty($booking_history)): ?>
                            <?php foreach ($booking_history as $bk): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($bk['Facility_Name']) ?></strong></td>
                                    <td><?= htmlspecialchars($bk['Team_Name']) ?></td>
                                    <td><span class="mono"><?= htmlspecialchars($bk['Reserve_Date']) ?></span></td>
                                    <td><span class="mono"><?= date('H:i', strtotime($bk['Start_Time'])) ?> - <?= date('H:i', strtotime($bk['End_Time'])) ?></span></td>
                                    <td>
                                        <!-- Apply specific color badges based on status -->
                                        <?php if ($bk['Status'] === 'Approved'): ?>
                                            <span class="badge-status" style="background: rgba(74,225,118,0.1); border-color: rgba(74,225,118,0.3); color: var(--secondary);">Approved</span>
                                        <?php elseif ($bk['Status'] === 'Pending'): ?>
                                            <span class="badge-status" style="background: rgba(247,190,29,0.1); border-color: rgba(247,190,29,0.3); color: var(--tertiary);">Pending</span>
                                        <?php else: ?>
                                            <span class="badge-status" style="background: rgba(255,180,171,0.1); border-color: rgba(255,180,171,0.3); color: var(--error);"><?= htmlspecialchars($bk['Status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallback for empty history -->
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 24px; color: var(--on-surface-variant);">No bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- Include template layouts -->
    <?php require_once '../../includes/bottombar/bottombar_captain.php'; ?>
    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>

<!-- Modal Overlay for Post-Booking Notifications -->
<div class="wk-modal-overlay flex-items-center justify-center p-4 transition-opacity hidden" id="confirmation-modal" style="z-index: 100;">
    <div class="glass-card shadow-2xl p-8 border-dim max-w-sm w-full transform transition-transform text-center scale-95" id="modal-box-inner">
        <div id="modal-icon-bg" class="w-16 h-16 rounded-full flex-items-center justify-center mx-auto mb-6 bg-secondary-dim txt-green">
            <span class="material-symbols-outlined text-4xl" id="modal-icon">task_alt</span>
        </div>
        <h3 class="settings-main-title text-xl mb-3 m-0" id="modal-title">Reservation Confirmed</h3>
        <p class="txt-muted text-sm mb-8 m-0" id="modal-desc">Your standard session has been instantly reserved.</p>
        <button class="w-full py-3.5 bg-surface-high border-dim hover-bg-dim txt-white font-bold rounded-xl transition-colors mb-2 close-modal-action" type="button">Confirm & Done</button>
    </div>
</div>
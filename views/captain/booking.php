<?php
// views/captain/booking.php

require_once '../../controllers/captain/booking_page_controller.php';
require_once '../../includes/headers/header_member.php';
require_once '../../includes/sidebars/sidebar_captain.php';
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container bk-main-container">
        
        <div class="grid-layout-booking">
            
            <section class="booking-calendar-section">
                <div class="glass-card inner-glow bk-card-pad overflow-hidden">
                    <div class="cal-header-flex bk-mb-lg">
                        <div>
                            <h2 class="settings-main-title m-0">Weekly Availability</h2>
                            <p class="txt-muted text-sm bk-mt-xs m-0">Select an open slot to begin your request</p>
                        </div>
                        
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
                    
                    <div class="date-navigator bk-mb-md">
                        <button class="nav-arrow" id="btn-prev-week" type="button"><span class="material-symbols-outlined">chevron_left</span></button>
                        <span class="mono font-bold text-sm" id="week-range-display">Loading Dates...</span>
                        <button class="nav-arrow" id="btn-next-week" type="button"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>

                    <div class="mobile-swipe-hint">
                        <span class="material-symbols-outlined" style="font-size: 14px;">swipe</span> Swipe horizontally to see more
                    </div>

                    <div class="grid-table-wrapper custom-scrollbar">
                        <div class="grid-table-inner">
                            <div class="calendar-grid-header border-b-dim bk-pb-sm bk-mb-sm" id="calendar-grid-header"></div>
                            <div class="grid-body-rows" id="schedule-grid-body"></div>
                        </div>
                    </div>

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

            <aside class="booking-form-section">
                <div class="glass-card bk-card-pad shadow-xl sticky-form">
                    
                    <div class="form-header bk-mb-lg">
                        <div class="flex-between bk-mb-sm">
                            <h3 class="settings-main-title text-lg m-0">Booking Request</h3>
                            <span class="gl-badge badge-bg-primary txt-primary mono uppercase font-bold font-xs">Limit For Per Week : 3 Sessions</span>
                        </div>
                    </div>

                    <div class="bk-mb-md hidden" id="capacity-info-container">
                        <div class="flex-between mono font-xs txt-white uppercase font-bold">
                            <span>Remaining Capacity</span>
                            <span class="txt-green font-bold text-sm" id="remaining-capacity-val">0</span>
                        </div>
                    </div>

                    <form id="facility-booking-form" class="booking-form space-y-md">
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent">SELECTED DATE</label>
                            <div class="cal-input-field flex-items-center gap-3">
                                <span class="material-symbols-outlined txt-primary" style="font-size: 20px;">calendar_today</span>
                                <span class="font-medium text-sm" id="selected-date-display">Select a slot</span>
                            </div>
                        </div>

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

                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent">TEAM SIZE</label>
                            <input class="cal-input-field" id="team-size-input" type="number" min="1" placeholder="Enter team size">
                        </div>

                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent">CAPTAIN ID</label>
                            <input class="cal-input-field txt-primary mono tracking-wide font-bold" type="text" readonly value="<?= $user_id ?>">
                        </div>

                        <div class="checkbox-wrapper bk-mt-md">
                            <input type="checkbox" id="limit-toggle" class="custom-checkbox">
                            <label for="limit-toggle" class="text-sm cursor-pointer select-none">Special Request</label>
                        </div>

                        <div class="form-group-cal space-y-xs hidden" id="special-request-field">
                            <label class="gl-lbl-accent">REASON FOR REQUEST <span class="txt-error">*</span></label>
                            <textarea class="cal-input-field" id="reason-input" placeholder="Explain why you need this extra session..." style="min-height: 80px;"></textarea>
                        </div>

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

        <section class="glass-card bk-card-pad bk-mt-lg">
            <div class="cal-header-flex bk-mb-md">
                <h3 class="settings-main-title text-lg m-0">Team Booking History</h3>
                <span class="txt-muted text-sm">Track reservations for teams you captain</span>
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
                            <th>Assigned Workouts</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($booking_history)): ?>
                            <?php foreach ($booking_history as $bk): ?>
                                <tr id="booking-row-<?= $bk['Booking_ID'] ?>">
                                    <td><strong><?= htmlspecialchars($bk['Facility_Name']) ?></strong></td>
                                    <td><?= htmlspecialchars($bk['Team_Name']) ?></td>
                                    <td><span class="mono"><?= htmlspecialchars($bk['Reserve_Date']) ?></span></td>
                                    <td><span class="mono"><?= date('H:i', strtotime($bk['Start_Time'])) ?> - <?= date('H:i', strtotime($bk['End_Time'])) ?></span></td>
                                    <td>
                                        <?php if ($bk['Status'] === 'Approved'): ?>
                                            <span class="badge-status" style="background: rgba(74,225,118,0.1); border-color: rgba(74,225,118,0.3); color: var(--secondary);">Approved</span>
                                        <?php elseif ($bk['Status'] === 'Pending'): ?>
                                            <span class="badge-status" style="background: rgba(247,190,29,0.1); border-color: rgba(247,190,29,0.3); color: var(--tertiary);">Pending</span>
                                        <?php else: ?>
                                            <span class="badge-status" style="background: rgba(255,180,171,0.1); border-color: rgba(255,180,171,0.3); color: var(--error);"><?= htmlspecialchars($bk['Status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($bk['can_assign_workout']): ?>
                                            <ul class="booking-assigned-workouts" data-booking-assigned-list>
                                                <?php if (empty($bk['assigned_workouts'])): ?>
                                                    <li class="no-assigned-workouts txt-muted">No workouts assigned</li>
                                                <?php else: ?>
                                                    <?php foreach ($bk['assigned_workouts'] as $assigned_workout): ?>
                                                        <li data-workout-id="<?= (int)$assigned_workout['Workout_ID'] ?>"><?= htmlspecialchars($assigned_workout['Title'], ENT_QUOTES, 'UTF-8') ?></li>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="txt-muted">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <?php if ($bk['can_assign_workout']): ?>
                                            <?php $available_workouts_json = json_encode($bk['available_workouts'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
                                            <button type="button" class="toggle-btn assign-workout-trigger" data-booking-id="<?= (int)$bk['Booking_ID'] ?>" data-available-workouts="<?= htmlspecialchars($available_workouts_json, ENT_QUOTES, 'UTF-8') ?>" style="padding: 4px 8px; font-size: 11px;">Assign Workout</button>
                                        <?php endif; ?>
                                        <?php if (!empty($bk['Can_Delete']) && empty($bk['Exception_Reason']) && $bk['Status'] !== 'Cancelled' && $bk['Status'] !== 'Rejected'): ?>
                                            <button type="button" class="toggle-btn txt-error delete-booking-btn" data-id="<?= $bk['Booking_ID'] ?>" style="padding: 4px 8px; font-size: 11px;">Delete</button>
                                        <?php else: ?>
                                            <span class="text-xs txt-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                    <td colspan="7" style="text-align: center; padding: 24px; color: var(--on-surface-variant);">No bookings found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <?php require_once '../../includes/bottombar/bottombar_captain.php'; ?>
    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>

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

<div class="wk-modal-overlay" id="assign-workout-modal" role="dialog" aria-modal="true" aria-labelledby="assign-workout-modal-title">
    <div class="wk-modal-box glass-card shadow-2xl">
        <div class="wk-modal-header">
            <div>
                <h3 class="wk-modal-title" id="assign-workout-modal-title">Assign Workout</h3>
                <p class="wk-modal-sub" id="assign-workout-booking-label"></p>
            </div>
            <button type="button" class="wk-modal-close" id="assign-workout-close" aria-label="Close assignment dialog">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="assign-workout-form">
            <div class="wk-modal-body">
                <h4 class="font-bold text-sm txt-white bk-mb-sm">Assigned Workouts</h4>
                <ul id="assign-workout-current-list" class="wk-exercise-list"></ul>
                <p id="assign-workout-current-empty" class="txt-muted text-sm hidden">No workouts assigned.</p>

                <div class="form-group-cal space-y-xs bk-mt-md">
                    <label class="gl-lbl-accent" for="assign-workout-select">ADD WORKOUT</label>
                    <select class="cal-input-field gl-select" id="assign-workout-select" name="workout_id" required>
                        <option value="">Select a reusable team workout</option>
                    </select>
                    <p id="assign-workout-no-options" class="txt-muted text-xs hidden">No unassigned workouts are available for this team.</p>
                </div>
                <p id="assign-workout-message" class="text-sm hidden" role="status" aria-live="polite"></p>
            </div>
            <div class="wk-modal-footer">
                <input type="hidden" name="booking_id" id="assign-workout-booking-id" value="">
                <button type="button" class="btn btn-glass" id="assign-workout-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="assign-workout-submit">Assign</button>
            </div>
        </form>
    </div>
</div>
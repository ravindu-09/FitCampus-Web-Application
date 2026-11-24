<?php
/**
 * views/instructor/kiosk.php
 * Kiosk attendance management view for instructors.
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../../controllers/instructor/kiosk_page_controller.php';
require_once '../../includes/headers/header_instructor.php';

$recent_checkin = $attendance_logs[0] ?? null;
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>

<!-- Ambient glow background effects -->
<div class="purple-glow top-left"></div>
<div class="purple-glow bottom-right"></div>

<div class="instructor-viewport-wrapper">
    <?php require_once '../../includes/sidebars/sidebar_instructor.php'; ?>
    
    <main class="main-content kiosk-main-layout">
        <div class="kiosk-container">
            
            <!-- Row 1: Scan Entry and Recent Check-In -->
            <div class="kiosk-hero-grid">
                <div class="glass-card kiosk-card inner-glow-primary">
                    <h3 class="card-header-title">
                        <span class="material-symbols-outlined text-color-primary">qr_code_scanner</span> Scan Entry
                    </h3>
                    <div class="scanner-wrap">
                        <div class="scanner-backdrop"></div>
                        <div class="scanner-frame">
                            <div class="scan-laser"></div>
                        </div>
                        <span class="scanner-text">ALIGN QR CODE</span>
                    </div>
                    <button class="btn btn-glass w-100" onclick="openKioskModal('manual-entry-modal')">
                        <span class="material-symbols-outlined">keyboard_return</span> Manual Entry
                    </button>
                </div>

                <div class="glass-card kiosk-card">
                    <h3 class="card-header-title border-bottom-sub">
                        <span class="material-symbols-outlined text-color-secondary">verified_user</span> Recent Check-In
                    </h3>
                    <?php if ($recent_checkin): ?>
                        <?php $recentImage = basename((string)($recent_checkin['Profile_Image'] ?? '')); ?>
                        <div class="recent-profile">
                            <div class="avatar-lg">
                                <?php if ($recentImage !== '' && $recentImage !== 'default_avatar.png'): ?>
                                    <img src="../../assets/images/uploads/<?= $escape($recentImage) ?>" alt="">
                                <?php else: ?>
                                    <span class="material-symbols-outlined">person</span>
                                <?php endif; ?>
                                <div class="status-tick"><span class="material-symbols-outlined">check</span></div>
                            </div>
                            <h2><?= $escape($recent_checkin['First_Name'] . ' ' . $recent_checkin['Last_Name']) ?></h2>
                            <h4 class="text-color-primary font-mono"><?= $escape($recent_checkin['Registration_Number']) ?></h4>
                            <div class="profile-tags">
                                <span class="badge-tag"><?= $escape($recent_checkin['Faculty'] ?: 'Faculty not specified') ?></span>
                                <span class="badge-tag"><?= $escape($recent_checkin['Attendance_Status']) ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-regular-sub">No attendance records have been recorded yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Row 2: Team Booking -->
            <div class="glass-card kiosk-card mt-4">
                <div class="flex-between mb-3">
                    <h3 class="card-header-title m-0">
                        <span class="material-symbols-outlined text-color-primary">groups</span> Current Team Booking
                    </h3>
                    <span class="badge-pill"><span class="dot-pulse primary"></span> LIVE SESSION</span>
                </div>
                <div class="team-bar" onclick="openKioskModal('team-checkin-modal')">
                    <div class="flex-items gap-3">
                        <div class="icon-box-primary">
                            <span class="material-symbols-outlined">sports_basketball</span>
                        </div>
                        <div>
                            <h4>UOC Basketball Team</h4>
                            <p class="text-regular-sub font-sm">Men's Varsity • 14:00 - 16:00</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-dimmed-sub">chevron_right</span>
                </div>
            </div>

            <!-- Row 3: Real-Time Occupancy -->
            <div class="glass-card kiosk-card mt-4">
                <div class="flex-between mb-4">
                    <h3 class="card-header-title m-0">
                        <span class="material-symbols-outlined text-color-primary">analytics</span> Real-Time Occupancy
                    </h3>
                </div>
                <div class="grid-2-col">
                    <?php foreach ($occupancy_data as $index => $fac): ?>
                    <?php
                    $capacity = max(1, (int)$fac['Capacity']);
                    $current = (int)$fac['live_count'];
                    $percentage = min(100, (int)round($current / $capacity * 100));
                    $color = $index % 2 === 0 ? 'var(--primary)' : 'var(--secondary)';
                    ?>
                    <div class="occ-box">
                        <div class="flex-between align-end mb-2">
                            <div>
                                <h4><?= $escape($fac['Facility_Name']) ?></h4>
                                <p class="form-label m-0">Current gym members</p>
                            </div>
                            <div class="occ-count"><span style="color: <?= $color ?>;"><?= $current ?></span> / <?= $capacity ?></div>
                        </div>
                        <div class="progress-track mb-3">
                            <div class="progress-fill" style="width: <?= $percentage ?>%; background-color: <?= $color ?>;"></div>
                        </div>
                        <div class="flex-between align-center">
                            <span class="text-regular-sub"><?= $percentage ?>% capacity</span>
                            <span class="badge-status" style="border-color: <?= $color ?>; color: <?= $color ?>;">
                                <?= $current >= $capacity ? 'At capacity' : 'Available' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Row 4: Daily Attendance Log Table -->
            <div class="glass-card kiosk-card p-0 mt-4 overflow-hidden">
                <div class="flex-between p-4 border-bottom-sub">
                    <h3 class="card-header-title m-0">
                        <span class="material-symbols-outlined text-color-primary">history</span> Daily Attendance Log
                    </h3>
                    <div class="flex-items gap-2">
                        <button class="btn btn-glass btn-sm" onclick="openKioskModal('calendar-modal')">
                            <span class="material-symbols-outlined">calendar_month</span>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="kiosk-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>ID</th>
                                <th>Time</th>
                                <th>Life Bar</th>
                                <th class="text-right">Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_logs as $log): ?>
                            <?php
                            $logImage = basename((string)($log['Profile_Image'] ?? ''));
                            $lifePercentage = max(0, min(100, (int)$log['Life_Percentage']));
                            $lifeColor = $lifePercentage > 50 ? 'var(--secondary)' : ($lifePercentage > 20 ? 'var(--tertiary)' : 'var(--error)');
                            ?>
                            <tr>
                                <td>
                                    <div class="flex-items gap-2">
                                        <?php if ($logImage !== '' && $logImage !== 'default_avatar.png'): ?>
                                            <img src="../../assets/images/uploads/<?= $escape($logImage) ?>" class="table-avatar" alt="">
                                        <?php else: ?>
                                            <div class="table-avatar no-img"><span class="material-symbols-outlined">person</span></div> 
                                        <?php endif; ?>
                                        <span><?= $escape($log['First_Name'] . ' ' . $log['Last_Name']) ?></span>
                                    </div>
                                </td>
                                <td class="text-regular-sub font-mono"><?= $escape($log['Registration_Number']) ?></td>
                                <td><?= $escape(date('H:i', strtotime($log['Check_In_Time']))) ?></td>
                                <td>
                                    <div class="flex-items gap-2" style="width:120px;">
                                        <div class="progress-track flex-1">
                                            <div class="progress-fill" style="width: <?= $lifePercentage ?>%; background-color: <?= $lifeColor ?>;"></div>
                                        </div>
                                        <span class="font-mono font-xs" style="color: <?= $lifeColor ?>;"><?= $lifePercentage ?>%</span>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <?php if ((int)$log['Active_Status'] === 1): ?>
                                        <span class="badge-status"><div class="status-dot"></div> Active</span>
                                    <?php else: ?>
                                        <span class="badge-pill font-xs">Complete</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-glass btn-sm js-open-penalty"
                                            data-user-id="<?= (int)$log['User_ID'] ?>"
                                            data-member-name="<?= $escape($log['First_Name'] . ' ' . $log['Last_Name']) ?>">
                                        Penalty
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$attendance_logs): ?>
                                <tr><td colspan="6">No attendance records have been recorded yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Popups / Modals -->
<div class="wk-modal-overlay hidden" id="manual-entry-modal">
    <div class="glass-card modal-box">
        <div class="modal-header">
            <h3>Manual Attendance</h3>
            <button class="close-btn" onclick="closeKioskModal('manual-entry-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="modal-body">
            <form id="manual-attendance-form">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrf_token) ?>">
                <div class="form-group mb-3">
                    <label class="form-label" for="manual-reg-no">REGISTRATION NUMBER</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">badge</span>
                        <input id="manual-reg-no" name="reg_no" type="text" class="form-control" placeholder="e.g. STU-0000" maxlength="50" required>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label" for="manual-attendance-action">ACTION</label>
                    <select id="manual-attendance-action" name="action" class="form-control" required>
                        <option value="manual_checkin">Check in</option>
                        <option value="manual_checkout">Check out</option>
                    </select>
                </div>
                <div class="form-group mb-3" id="manual-facility-group">
                    <label class="form-label" for="manual-facility">FACILITY</label>
                    <select id="manual-facility" name="facility_id" class="form-control" required>
                        <?php foreach ($facility_options as $facility): ?>
                            <option value="<?= (int)$facility['Facility_ID'] ?>"><?= $escape($facility['Facility_Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="kiosk-feedback" id="manual-attendance-feedback" role="status" aria-live="polite"></p>
                <button class="btn btn-primary w-100 mt-4" type="submit">Record attendance</button>
            </form>
        </div>
    </div>
</div>

<div class="wk-modal-overlay hidden" id="penalty-modal">
    <div class="glass-card modal-box">
        <div class="modal-header">
            <div>
                <h3>Apply Life-Bar Penalty</h3>
                <p class="text-regular-sub font-sm" id="penalty-member-label"></p>
            </div>
            <button class="close-btn" type="button" onclick="closeKioskModal('penalty-modal')" aria-label="Close penalty form"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="modal-body">
            <form id="penalty-form">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrf_token) ?>">
                <input type="hidden" name="user_id" id="penalty-user-id">
                <input type="hidden" name="request_key" id="penalty-request-key">
                <div class="form-group mb-3">
                    <label class="form-label" for="penalty-reason">REASON</label>
                    <textarea id="penalty-reason" name="reason" class="form-control" maxlength="2000" required></textarea>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label" for="penalty-severity">SEVERITY</label>
                    <input id="penalty-severity" name="severity" type="text" class="form-control" maxlength="30" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label" for="penalty-deduction">REQUESTED LIFE DEDUCTION (1–100)</label>
                    <input id="penalty-deduction" name="requested_life_deduction" type="number" class="form-control" min="1" max="100" required>
                </div>
                <p class="kiosk-feedback" id="penalty-feedback" role="status" aria-live="polite"></p>
                <button class="btn btn-primary w-100 mt-4" type="submit">Apply penalty</button>
            </form>
        </div>
    </div>
 </div>

<div class="wk-modal-overlay hidden" id="team-checkin-modal">
    <div class="glass-card modal-box" style="max-width: 600px;">
        <div class="modal-header align-start">
            <div>
                <h3>UOC Basketball Team</h3>
                <p class="text-regular-sub font-sm mt-1">12/15 Members Checked In</p>
            </div>
            <button class="close-btn" onclick="closeKioskModal('team-checkin-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="modal-body">
            <div class="dummy-placeholder p-5 text-center">
                <span class="material-symbols-outlined text-color-primary" style="font-size: 48px; margin-bottom: 12px;">qr_code_scanner</span>
                <p>Team Scanner Interface Placeholder</p>
            </div>
            <button class="btn btn-primary w-100 mt-4" onclick="closeKioskModal('team-checkin-modal')">Finish Check-in</button>
        </div>
    </div>
</div>

<div class="wk-modal-overlay hidden" id="calendar-modal">
    <div class="glass-card modal-box">
        <div class="modal-header">
            <h3>Select Attendance Date</h3>
            <button class="close-btn" onclick="closeKioskModal('calendar-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="modal-body">
            <div class="dummy-placeholder p-4 text-center">Calendar Component Placeholder</div>
            <button class="btn btn-primary w-100 mt-4" onclick="closeKioskModal('calendar-modal')">Select Date</button>
        </div>
    </div>
</div>

<div class="wk-modal-overlay hidden" id="admin-auth-modal">
    <div class="glass-card modal-box">
        <div class="modal-header">
            <h3>Admin Verification</h3>
            <button class="close-btn" onclick="closeKioskModal('admin-auth-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="modal-body text-center">
            <p class="text-regular-sub font-sm mb-3">Please enter your Instructor Profile Password to proceed.</p>
            <input type="password" class="form-control text-center mb-3" placeholder="••••••••">
            <button class="btn btn-primary w-100 mt-4" onclick="closeKioskModal('admin-auth-modal')">Confirm Action</button>
        </div>
    </div>
</div>

<?php 
require_once '../../includes/bottombar/bottombar_instructor.php'; 
require_once '../../includes/footers/footer_common.php'; 
?>
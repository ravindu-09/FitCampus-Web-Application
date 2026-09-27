<?php
/**
 * views/instructor/kiosk.php
 * Kiosk attendance management view for instructors.
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once '../../controllers/instructor/kiosk_page_controller.php';
require_once '../../includes/headers/header_instructor.php';

// Mock data for UI layout rendering
$recent_checkin = [
    'name' => 'Alex Chen', 'id' => 'STU-8921', 'faculty' => 'Computer Science', 'year' => 'Junior',
    'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCFYIIWG-DJrrw7nBUg6H28xRxVkc5rsJ0enrUl8dnG1m3puxT5avRDeqFzUfyykr3JqDXj9EEHNR6xzTlqmeYvSXj4rmfJDdWdUtOhOhTgePSBCnAgULr8kSfKictzpXu7vn9OcdamwLC5uBGTnEA2tACDMIoTxM33SNNxgL9VKzbfxZ-h1chlwR0IXdbylifvTyUVm3ebvtoYXSaw0vde3rfMkvpWO78MUkxgVLiigYWXEj8X6kuf0g'
];

$occupancy_data = [
    ['name' => 'Gym 01 - Main', 'sub' => 'STRENGTH & FREE WEIGHTS', 'current' => 32, 'max' => 50, 'pct' => 64, 'male' => 60, 'female' => 40, 'status' => 'OPTIMAL', 'color' => 'var(--primary)'],
    ['name' => 'Gym 02 - Cardio', 'sub' => 'TREADMILLS & CYCLES', 'current' => 18, 'max' => 40, 'pct' => 45, 'male' => 45, 'female' => 55, 'status' => 'LOW TRAFFIC', 'color' => 'var(--secondary)']
];

$attendance_logs = [
    ['name' => 'Alex Chen', 'id' => 'STU-8921', 'time' => '14:22', 'life' => 85, 'color' => 'var(--secondary)', 'img' => $recent_checkin['image'], 'status' => 'Active'],
    ['name' => 'Jordan Smith', 'id' => 'STU-4410', 'time' => '14:15', 'life' => 45, 'color' => 'var(--tertiary)', 'img' => '', 'status' => 'Active'],
    ['name' => 'Taylor Brooks', 'id' => 'STU-1102', 'time' => '13:05', 'life' => 15, 'color' => 'var(--error)', 'img' => '', 'status' => 'Complete']
];
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
                    <div class="recent-profile">
                        <div class="avatar-lg">
                            <img src="<?= $recent_checkin['image'] ?>" alt="Profile">
                            <div class="status-tick"><span class="material-symbols-outlined">check</span></div>
                        </div>
                        <h2><?= $recent_checkin['name'] ?></h2>
                        <h4 class="text-color-primary font-mono"><?= $recent_checkin['id'] ?></h4>
                        <div class="profile-tags">
                            <span class="badge-tag"><?= $recent_checkin['faculty'] ?></span>
                            <span class="badge-tag"><?= $recent_checkin['year'] ?></span>
                        </div>
                    </div>
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
                    <?php foreach($occupancy_data as $fac): ?>
                    <div class="occ-box">
                        <div class="flex-between align-end mb-2">
                            <div>
                                <h4><?= $fac['name'] ?></h4>
                                <p class="form-label m-0"><?= $fac['sub'] ?></p>
                            </div>
                            <div class="occ-count"><span style="color: <?= $fac['color'] ?>;"><?= $fac['current'] ?></span> / <?= $fac['max'] ?></div>
                        </div>
                        <div class="progress-track mb-3">
                            <div class="progress-fill" style="width: <?= $fac['pct'] ?>%; background-color: <?= $fac['color'] ?>;"></div>
                        </div>
                        <div class="flex-between align-center">
                            <div class="flex-items gap-3">
                                <span class="gender-stat text-regular-sub"><span class="material-symbols-outlined text-color-primary">male</span> <?= $fac['male'] ?>%</span>
                                <span class="gender-stat text-regular-sub"><span class="material-symbols-outlined text-color-secondary">female</span> <?= $fac['female'] ?>%</span>
                            </div>
                            <span class="badge-status" style="border-color: <?= $fac['color'] ?>; color: <?= $fac['color'] ?>;"><?= $fac['status'] ?></span>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($attendance_logs as $log): ?>
                            <tr>
                                <td>
                                    <div class="flex-items gap-2">
                                        <?php if($log['img']): ?> 
                                            <img src="<?= $log['img'] ?>" class="table-avatar">
                                        <?php else: ?> 
                                            <div class="table-avatar no-img"><span class="material-symbols-outlined">person</span></div> 
                                        <?php endif; ?>
                                        <span><?= $log['name'] ?></span>
                                    </div>
                                </td>
                                <td class="text-regular-sub font-mono"><?= $log['id'] ?></td>
                                <td><?= $log['time'] ?></td>
                                <td>
                                    <div class="flex-items gap-2" style="width:120px;">
                                        <div class="progress-track flex-1">
                                            <div class="progress-fill" style="width: <?= $log['life'] ?>%; background-color: <?= $log['color'] ?>;"></div>
                                        </div>
                                        <span class="font-mono font-xs" style="color: <?= $log['color'] ?>;"><?= $log['life'] ?>%</span>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <?php if($log['status'] == 'Active'): ?>
                                        <span class="badge-status"><div class="status-dot"></div> Active</span>
                                    <?php else: ?>
                                        <span class="badge-pill font-xs">Complete</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
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
            <h3>Manual Authentication</h3>
            <button class="close-btn" onclick="closeKioskModal('manual-entry-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="modal-body">
            <div class="form-group mb-3">
                <label class="form-label">REGISTRATION NUMBER</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">badge</span>
                    <input type="text" class="form-control" placeholder="e.g. STU-0000">
                </div>
            </div>
            <button class="btn btn-primary w-100 mt-4" onclick="closeKioskModal('manual-entry-modal')">Authenticate</button>
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
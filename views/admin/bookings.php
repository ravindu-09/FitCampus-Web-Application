<?php
// views/admin/bookings.php
require_once '../../includes/db_connection.php';

$page_title = 'Facility Bookings | Admin';
$extra_js = ["admin/bookings.js"];

// Fetch facilities dynamically
$facilities = [];
try {
    $stmtFac = $pdo->query("SELECT Facility_ID, Facility_Name FROM facility ORDER BY Facility_ID ASC");
    $facilities = $stmtFac->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

require_once '../../includes/headers/header_admin.php';
?>

<!-- Sidebar -->
<?php include_once '../../includes/sidebars/sidebar_admin.php'; ?>

<!-- Main Viewport -->
<div class="admin-viewport-wrapper">
    <main class="admin-main-canvas">
        
        <!-- Alert Banner Container -->
        <div id="alert-banner-container" class="hidden mb-4">
            <div class="alert-banner-danger">
                <span class="material-symbols-outlined icon">warning</span>
                <div class="alert-text" id="alert-banner-text"></div>
                <button class="close-alert-btn" onclick="document.getElementById('alert-banner-container').classList.add('hidden')">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
        </div>

        <!-- Page Header -->
        <div class="content-header-row mb-4">
            <div>
                <h2 class="page-main-heading m-0">Facility Slot & Team Bookings</h2>
                <p class="page-sub-heading m-0">Manage university varsity teams and manual booking overrides</p>
            </div>
        </div>

        <!-- Main Grid Layout -->
        <div class="bookings-grid-layout">
            
            <!-- LEFT COLUMN -->
            <div class="bookings-left-col">
                
                <!-- Weekly Availability -->
                <div class="glass-card bk-card-pad mb-4 overflow-hidden">
                    <div class="cal-header-flex bk-mb-md">
                        <div>
                            <h3 class="settings-main-title text-lg m-0">Weekly Availability</h3>
                            <p class="txt-muted text-sm m-0">Real-time Facility Availability</p>
                        </div>
                        <div class="booking-toggles">
                            <div class="toggle-group" id="admin-gym-toggle">
                                <?php if (!empty($facilities)): ?>
                                    <?php foreach ($facilities as $index => $fac): ?>
                                        <button class="toggle-btn <?= $index === 0 ? 'active' : '' ?>" data-id="<?= $fac['Facility_ID'] ?>">
                                            <?= htmlspecialchars($fac['Facility_Name']) ?>
                                        </button>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <button class="toggle-btn active" data-id="1">Main Gym</button>
                                <?php endif; ?>
                            </div>
                            <div class="toggle-group" id="admin-shift-toggle">
                                <button class="toggle-btn active" data-shift="morning">Morning (6AM-12PM)</button>
                                <button class="toggle-btn" data-shift="evening">Evening (12PM-6PM)</button>
                            </div>
                        </div>
                    </div>

                    <div class="date-navigator bk-mb-md">
                        <button class="nav-arrow" id="btn-prev-week"><span class="material-symbols-outlined">chevron_left</span></button>
                        <span class="mono font-bold text-sm" id="week-range-display">Loading...</span>
                        <button class="nav-arrow" id="btn-next-week"><span class="material-symbols-outlined">chevron_right</span></button>
                    </div>

                    <div class="grid-table-wrapper custom-scrollbar">
                        <div class="grid-table-inner" style="min-width: 600px;">
                            <div class="calendar-grid-header border-b-dim bk-pb-sm bk-mb-sm" id="calendar-grid-header"></div>
                            <div class="grid-body-rows" id="schedule-grid-body"></div>
                        </div>
                    </div>

                    <div class="status-legend bk-mt-md">
                        <div class="legend-item"><span class="dot bg-secondary"></span>Available (0%)</div>
                        <div class="legend-item"><span class="dot bg-tertiary"></span>Moderate (1-90%)</div>
                        <div class="legend-item"><span class="dot outline-dot"></span>Full (100%)</div>
                    </div>
                </div>

                <!-- Special Requests Table -->
                <div class="glass-card bk-card-pad overflow-hidden">
                    <div class="flex-items-center gap-2 bk-mb-md">
                        <span class="material-symbols-outlined text-primary">assignment_late</span>
                        <h3 class="settings-main-title text-lg m-0">Special Requests (Manual Intervention)</h3>
                    </div>
                    
                    <div class="admin-table-container custom-scrollbar">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Facility</th>
                                    <th>Team Details</th>
                                    <th>Reason</th>
                                    <th>Time Slot</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pending-requests-tbody">
                                <tr><td colspan="6" class="text-center txt-muted py-8">Loading requests...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="bookings-right-col">
                
                <!-- Slot Details Section -->
                <div class="glass-card bk-card-pad h-full">
                    <div class="flex-between bk-mb-md">
                        <div class="flex-items-center gap-2">
                            <span class="material-symbols-outlined text-primary">info</span>
                            <h3 class="settings-main-title text-lg m-0">Slot Details<br><span class="text-sm txt-muted font-normal" id="selected-slot-lbl">Select a slot to view</span></h3>
                        </div>
                    </div>

                    <div class="schedule-timeline" id="slot-details-container">
                        <div class="text-center txt-muted py-8">Please select a time slot from the calendar.</div>
                    </div>
                </div>

            </div>

        </div>

    </main>
</div>

<!-- Mobile Bottom Navigation -->
<?php include_once '../../includes/bottombar/bottombar_admin.php'; ?>

<!-- CONFLICT RESOLUTION MODAL -->
<div class="wk-modal-overlay flex-items-center justify-center p-4 transition-opacity hidden" id="conflict-modal" style="z-index: 100;">
    <div class="glass-card shadow-2xl p-6 border-dim w-full max-w-md transform transition-transform scale-95" id="conflict-modal-inner" style="background: #1e1e1e;">
        
        <div class="flex-between border-b-dim bk-pb-sm bk-mb-md">
            <h3 class="settings-main-title text-xl m-0 text-error flex-items-center gap-2">
                <span class="material-symbols-outlined">warning</span> Capacity Conflict
            </h3>
            <button class="close-sidebar-btn" onclick="closeModal('conflict-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>

        <p class="text-sm txt-muted m-0 bk-mb-sm">This special request exceeds facility capacity. To approve it, you must cancel existing bookings to make room.</p>
        
        <div class="bg-dim p-4 rounded-lg bk-mb-md">
            <div class="flex-between text-sm bk-mb-xs"><span class="txt-muted">Max Capacity:</span> <strong id="conf-max">0</strong></div>
            <div class="flex-between text-sm bk-mb-xs"><span class="txt-muted">Currently Booked:</span> <strong id="conf-booked">0</strong></div>
            <div class="flex-between text-sm bk-mb-xs"><span class="txt-muted">New Request Size:</span> <strong class="text-primary" id="conf-new">0</strong></div>
            <div class="border-t-dim my-2"></div>
            <div class="flex-between text-sm"><span class="text-error font-bold">Overflow to clear:</span> <strong class="text-error" id="conf-overflow">0</strong></div>
        </div>

        <h4 class="font-bold text-sm txt-white bk-mb-sm m-0">Select Teams to Cancel:</h4>
        <div class="space-y-xs max-h-40 overflow-y-auto custom-scrollbar pr-2 bk-mb-md" id="conflict-teams-list">
            <!-- Dynamic checkboxes generated here -->
        </div>

        <div class="form-group-cal space-y-xs bk-mb-lg">
            <label class="gl-lbl-accent">CANCELLATION REASON (Sent to Captains) <span class="text-error">*</span></label>
            <textarea class="cal-input-field" id="cancel-reason" placeholder="e.g., Slot overridden for university varsity finals." style="min-height: 60px; background: rgba(0,0,0,0.2);"></textarea>
        </div>

        <div class="flex gap-2">
            <button class="btn-decline-action flex-1 rounded-lg font-bold" onclick="closeModal('conflict-modal')">Cancel</button>
            <button class="btn-approve-action flex-1 rounded-lg border-none cursor-pointer" id="btn-confirm-override" disabled>Force Approve</button>
        </div>
    </div>
</div>

<?php include_once '../../includes/footers/footer_common.php'; ?>
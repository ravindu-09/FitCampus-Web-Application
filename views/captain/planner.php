<?php
// views/captain/planner.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check (Keep this)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Workout Planner | FitCampus";

$extra_js = [
    "member/dashboard.js",
    "captain/planner.js" // UI logic and future AJAX calls
];

// ---------------------------------------------------------
// MOCK DATA:
// ---------------------------------------------------------
$mock_teams = [
    ['Team_ID' => 1, 'Team_Name' => 'UOC Track & Field'],
    ['Team_ID' => 2, 'Team_Name' => 'UOC Swimming Team']
];

$mock_published = [
    ['id' => 101, 'title' => 'Explosive Power Training', 'target' => 'Oct 24, 2026', 'location' => 'Main Gym', 'icon' => 'fitness_center'],
    ['id' => 102, 'title' => 'Endurance Conditioning', 'target' => 'Oct 26, 2026', 'location' => 'Arena-A', 'icon' => 'directions_run']
];

$mock_drafts = [
    ['id' => 201, 'title' => 'Recovery Flow Session', 'target' => 'Nov 01, 2026', 'location' => 'Pool-Side', 'icon' => 'edit_note']
];
// ---------------------------------------------------------

require_once '../../includes/headers/header_member.php';

$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;
if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container bk-main-container" style="max-width: 850px; margin: 0 auto;">
        
        <!-- ========================================== -->
        <!-- 1. NEW EXERCISE SESSION FORM -->
        <!-- ========================================== -->
        <div class="glass-card inner-glow overflow-hidden border-dim bk-mb-lg" style="animation: slideUpFade 0.5s ease-out;">
            <div class="bk-p-md border-b-dim flex-between items-center bg-dim">
                <h3 class="settings-main-title text-lg m-0">New Exercise Session</h3>
            </div>
            
            <div class="bk-card-pad space-y-md">
                <form id="workout-planner-form" class="space-y-md">
                    
                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent">SELECT TEAM</label>
                        <select class="cal-input-field gl-select" name="team_id" required>
                            <option value="" disabled selected>Select your team</option>
                            <?php foreach ($mock_teams as $t): ?>
                                <option value="<?= $t['Team_ID'] ?>"><?= htmlspecialchars($t['Team_Name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent">ROUTINE NAME</label>
                        <input class="cal-input-field" type="text" name="title" placeholder="e.g., Full Body Blast" required>
                    </div>
                    
                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent">DESCRIPTION & PROTOCOL</label>
                        <textarea class="cal-input-field" name="description" rows="3" placeholder="Define sets objectives and safety protocols..."></textarea>
                    </div>

                    <!-- Dynamic Exercises Container -->
                    <div class="bg-dim border-dim bk-p-md space-y-md" style="border-radius: 12px;" id="exercises-container">
                        <div class="exercise-block">
                            <div class="form-group-cal space-y-xs bk-mb-md">
                                <label class="gl-lbl-accent">EXERCISE NAME</label>
                                <input class="cal-input-field ex-name" type="text" name="ex_name[]" placeholder="e.g., Bench Press" required>
                            </div>
                            <div class="cal-grid-2 gap-3">
                                <div class="form-group-cal space-y-xs">
                                    <label class="gl-lbl-accent">SETS</label>
                                    <input class="cal-input-field ex-sets" type="number" name="ex_sets[]" placeholder="0" required>
                                </div>
                                <div class="form-group-cal space-y-xs">
                                    <label class="gl-lbl-accent">MAX DURATION (MINS)</label>
                                    <input class="cal-input-field ex-duration" type="number" name="ex_duration[]" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" id="add-exercise-btn" class="w-full bk-py-md bg-dim border-dim txt-muted font-semibold text-sm hover-bg-dim transition-colors" style="border-radius: 8px; cursor: pointer; border-style: dashed;">
                        + Add Another Exercise
                    </button>

                    <!-- Status Message Box (Hidden by default) -->
                    <div id="form-status-msg" class="hidden bk-p-sm font-semibold text-sm text-center" style="border-radius: 8px;"></div>

                    <!-- Form Actions -->
                    <div class="flex-between gap-3 bk-mt-md bg-dim bk-p-sm" style="flex-wrap: wrap; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center" type="button" id="btn-save-draft" style="background: transparent; border: 1px solid rgba(255,255,255,0.1); color: var(--on-surface);">Save as Draft</button>
                        <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center" type="submit" id="btn-publish">Publish Schedule</button>
                        <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center" type="reset" style="background: transparent; border: 1px solid rgba(255,255,255,0.1); color: var(--on-surface-variant);">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 2. PUBLISHED SCHEDULES LIST -->
        <!-- ========================================== -->
        <div class="bk-mb-lg space-y-md">
            <div class="flex-between border-b-dim bk-pb-sm">
                <h4 class="settings-main-title text-base txt-primary flex-items-center gap-2 m-0 uppercase tracking-wide">
                    <span class="material-symbols-outlined">verified</span> Published Schedules
                </h4>
                <span class="gl-badge badge-bg-primary txt-primary font-xs uppercase font-bold tracking-wide"><?= count($mock_published) ?> ACTIVE</span>
            </div>
            
            <div class="space-y-md">
                <?php foreach ($mock_published as $pub): ?>
                <div class="glass-card bk-p-md flex-between items-center transition-colors border-dim hover-bg-dim cursor-pointer">
                    <div class="flex-items-center gap-3">
                        <div class="bg-primary-dim txt-primary flex-items-center justify-center" style="width: 48px; height: 48px; border-radius: 12px;">
                            <span class="material-symbols-outlined"><?= $pub['icon'] ?></span>
                        </div>
                        <div>
                            <p class="font-bold text-base m-0 txt-white"><?= htmlspecialchars($pub['title']) ?></p>
                            <p class="font-xs txt-muted m-0 bk-mt-xs">Target: <?= $pub['target'] ?> • <?= $pub['location'] ?></p>
                        </div>
                    </div>
                    <!-- Delete Button -> Opens Modal -->
                    <button class="toggle-btn txt-error open-delete-modal" data-id="<?= $pub['id'] ?>" style="padding: 8px;"><span class="material-symbols-outlined">delete</span></button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. DRAFT SCHEDULES LIST -->
        <!-- ========================================== -->
        <div class="bk-mb-lg space-y-md">
            <div class="flex-between border-b-dim bk-pb-sm">
                <h4 class="settings-main-title text-base txt-muted flex-items-center gap-2 m-0 uppercase tracking-wide">
                    <span class="material-symbols-outlined">draft</span> Draft Schedules
                </h4>
                <span class="gl-badge bg-dim txt-muted font-xs uppercase font-bold tracking-wide"><?= count($mock_drafts) ?> DRAFT</span>
            </div>
            
            <div class="space-y-md">
                <?php foreach ($mock_drafts as $draft): ?>
                <div class="glass-card bk-p-md flex-between items-center transition-colors border-dim hover-bg-dim cursor-pointer open-edit-modal" style="border-style: dashed;" data-id="<?= $draft['id'] ?>">
                    <div class="flex-items-center gap-3">
                        <div class="bg-dim txt-muted flex-items-center justify-center" style="width: 48px; height: 48px; border-radius: 12px;">
                            <span class="material-symbols-outlined"><?= $draft['icon'] ?></span>
                        </div>
                        <div>
                            <p class="font-bold text-base m-0 txt-white"><?= htmlspecialchars($draft['title']) ?></p>
                            <p class="font-xs txt-muted m-0 bk-mt-xs">Target: <?= $draft['target'] ?> • <?= $draft['location'] ?></p>
                        </div>
                    </div>
                    <button class="toggle-btn txt-error open-delete-modal" data-id="<?= $draft['id'] ?>" style="padding: 8px;"><span class="material-symbols-outlined">delete</span></button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>

    <!-- ========================================== -->
    <!-- MODALS (Hidden by default) -->
    <!-- ========================================== -->
    
    <!-- Delete Confirmation Modal -->
    <div class="wk-modal-overlay" id="delete-modal">
        <div class="wk-modal-box max-w-sm">
            <div class="wk-modal-body text-center space-y-md">
                <h3 class="settings-main-title text-xl m-0">Delete Schedule?</h3>
                <p class="txt-muted text-sm m-0">This action cannot be undone. The schedule will be permanently removed.</p>
                <div class="flex gap-3 bk-mt-md">
                    <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center close-modal-btn" type="button" style="background: transparent; border: 1px solid rgba(255,255,255,0.1); color: var(--on-surface);">Cancel</button>
                    <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center" type="button" id="confirm-delete-btn" style="background: rgba(239, 68, 68, 0.2); color: var(--error);">Confirm Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Draft Modal -->
    <div class="wk-modal-overlay" id="edit-draft-modal">
        <div class="wk-modal-box" style="max-width: 600px;">
            <div class="wk-modal-header">
                <h3 class="wk-modal-title">Edit Draft Schedule</h3>
                <button class="wk-modal-close close-modal-btn"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="wk-modal-body space-y-md">
                <div class="form-group-cal space-y-xs">
                    <label class="gl-lbl-accent">ROUTINE NAME</label>
                    <input class="cal-input-field" type="text" value="Recovery Flow Session">
                </div>
                <div class="cal-grid-2 gap-3">
                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent">DATE</label>
                        <input class="cal-input-field" type="text" value="Nov 01, 2026">
                    </div>
                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent">LOCATION</label>
                        <input class="cal-input-field" type="text" value="Pool-Side">
                    </div>
                </div>
            </div>
            <div class="wk-modal-footer">
                <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center close-modal-btn" type="button" style="background: transparent; border: 1px solid rgba(255,255,255,0.1); color: var(--on-surface-variant);">Cancel</button>
                <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center" type="button" style="background: transparent; border: 1px solid var(--primary); color: var(--primary);">Update Draft</button>
                <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center" type="button">Publish Schedule</button>
            </div>
        </div>
    </div>

    <?php 
    
    require_once '../../includes/bottombar/bottombar_captain.php';
    
    require_once '../../includes/footers/footer_common.php'; 
    ?>
</div>
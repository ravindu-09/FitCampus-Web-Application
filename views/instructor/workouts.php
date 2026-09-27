<?php
// views/instructor/workouts.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../controllers/instructor/workouts_page_controller.php';
require_once '../../includes/headers/header_instructor.php';
require_once '../../includes/sidebars/sidebar_instructor.php';
?>

<!-- Ambient Glow Effects -->
<div class="purple-glow top-left"></div>
<div class="purple-glow bottom-right"></div>

<div class="instructor-viewport-wrapper">
    <main class="main-content">
        <div class="dashboard-main-container bk-main-container workouts-main-wrapper">
            
            <!-- Page Header -->
            <div class="content-header-row mb-4 flex-between items-center workouts-header-row">
                <div>
                    <h2 class="page-main-heading m-0">Common Workouts Manager</h2>
                    <p class="page-sub-heading m-0">Manage standardized routines for student members.</p>
                </div>
                <button class="btn btn-primary btn-sm workouts-create-btn" onclick="openModal('modal-create')">
                    <span class="material-symbols-outlined">add</span> Create New Routine
                </button>
            </div>

            <!-- Workout Routines Grid -->
            <div class="workouts-grid">
                
                <!-- Routine Card 1 -->
                <div class="glass-card bk-card-pad workouts-card workout-border-secondary">
                    <div class="workouts-card-top">
                        <span class="gl-badge workouts-badge-beginner">Beginner</span>
                        <div class="workouts-action-group">
                            <button class="toggle-btn workouts-action-btn" onclick="openModal('modal-edit')"><span class="material-symbols-outlined workouts-icon-sm">edit</span></button>
                            <button class="toggle-btn txt-error workouts-action-btn" onclick=""><span class="material-symbols-outlined workouts-icon-sm">delete</span></button>
                        </div>
                    </div>
                    <h3 class="workouts-card-title">Foundation Full Body</h3>
                    <div class="workouts-card-footer-grid">
                        <div>
                            <span class="workouts-meta-label">Exercises</span>
                            <span class="workouts-meta-val">6</span>
                        </div>
                        <div>
                            <span class="workouts-meta-label">Duration</span>
                            <span class="workouts-meta-val">45 min</span>
                        </div>
                    </div>
                </div>

                <!-- Routine Card 2 -->
                <div class="glass-card bk-card-pad workouts-card workout-border-tertiary">
                    <div class="workouts-card-top">
                        <span class="gl-badge workouts-badge-intermediate">Intermediate</span>
                        <div class="workouts-action-group">
                            <button class="toggle-btn workouts-action-btn" onclick="openModal('modal-edit')"><span class="material-symbols-outlined workouts-icon-sm">edit</span></button>
                            <button class="toggle-btn txt-error workouts-action-btn" onclick=""><span class="material-symbols-outlined workouts-icon-sm">delete</span></button>
                        </div>
                    </div>
                    <h3 class="workouts-card-title">Hypertrophy Push/Pull</h3>
                    <div class="workouts-card-footer-grid">
                        <div>
                            <span class="workouts-meta-label">Exercises</span>
                            <span class="workouts-meta-val">8</span>
                        </div>
                        <div>
                            <span class="workouts-meta-label">Duration</span>
                            <span class="workouts-meta-val">60 min</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<!-- ================= MODALS ================= -->

<!-- Create New Routine Modal -->
<div class="wk-modal-overlay hidden" id="modal-create">
    <div class="wk-modal-box glass-card shadow-2xl workouts-modal-box">
        <div class="wk-modal-header workouts-modal-header">
            <h3 class="wk-modal-title">Create New Routine</h3>
            <button class="wk-modal-close workouts-close-btn" onclick="closeModal('modal-create')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body custom-scrollbar workouts-modal-body">
            <div class="form-group-cal">
                <label class="gl-lbl-accent">ROUTINE NAME</label>
                <input class="cal-input-field" type="text" placeholder="e.g., Full Body Blast">
            </div>
            
            <div class="form-group-cal">
                <label class="gl-lbl-accent">ADD EXERCISES</label>
                <div class="workouts-exercise-box">
                    <div>
                        <label class="workouts-sub-lbl">Exercise Name</label>
                        <input class="cal-input-field" type="text" placeholder="e.g., Bench Press">
                    </div>
                    <div class="workouts-grid-3">
                        <div>
                            <label class="workouts-sub-lbl">Sets</label>
                            <input class="cal-input-field" type="number" placeholder="0">
                        </div>
                        <div>
                            <label class="workouts-sub-lbl">Reps</label>
                            <input class="cal-input-field" type="number" placeholder="0">
                        </div>
                        <div>
                            <label class="workouts-sub-lbl">Duration (min)</label>
                            <input class="cal-input-field" type="number" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-glass w-full workouts-dashed-btn">
                <span class="material-symbols-outlined workouts-add-icon">add</span> + Add Another Exercise
            </button>
        </div>
        <div class="wk-modal-footer workouts-modal-footer">
            <button type="button" class="btn btn-glass flex-1" onclick="closeModal('modal-create')">Cancel</button>
            <button type="button" class="btn btn-primary flex-1" onclick="closeModal('modal-create')">Publish Routine</button>
        </div>
    </div>
</div>

<!-- Edit Routine Modal -->
<div class="wk-modal-overlay hidden" id="modal-edit">
    <div class="wk-modal-box glass-card shadow-2xl workouts-modal-box">
        <div class="wk-modal-header workouts-modal-header">
            <h3 class="wk-modal-title workouts-title-primary">Edit Routine</h3>
            <button class="wk-modal-close workouts-close-btn" onclick="closeModal('modal-edit')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body custom-scrollbar workouts-modal-body">
            <div class="form-group-cal">
                <label class="gl-lbl-accent">ROUTINE NAME</label>
                <input class="cal-input-field" type="text" value="Foundation Full Body">
            </div>
            <div class="workouts-edit-box">
                <div class="workouts-edit-bar"></div>
                <div>
                    <label class="workouts-sub-lbl">Exercise Name</label>
                    <input class="cal-input-field" type="text" value="Goblet Squat">
                </div>
                <div class="workouts-grid-3">
                    <div>
                        <label class="workouts-sub-lbl">Sets</label>
                        <input class="cal-input-field" type="number" value="3">
                    </div>
                    <div>
                        <label class="workouts-sub-lbl">Reps</label>
                        <input class="cal-input-field" type="number" value="12">
                    </div>
                    <div>
                        <label class="workouts-sub-lbl">Duration (min)</label>
                        <input class="cal-input-field" type="number" value="10">
                    </div>
                </div>
                <div class="workouts-remove-wrap">
                    <button class="btn-text-primary txt-error workouts-remove-btn"><span class="material-symbols-outlined workouts-icon-xs">delete</span> Remove</button>
                </div>
            </div>
        </div>
        <div class="wk-modal-footer workouts-modal-footer">
            <button type="button" class="btn btn-primary flex-1" onclick="closeModal('modal-edit')">Publish Changes</button>
            <button type="button" class="btn btn-glass flex-1" onclick="closeModal('modal-edit')">Cancel</button>
        </div>
    </div>
</div>

<?php 
require_once '../../includes/bottombar/bottombar_instructor.php'; 
require_once '../../includes/footers/footer_common.php'; 
?>
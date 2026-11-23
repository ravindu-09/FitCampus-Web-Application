<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../controllers/instructor/workouts_page_controller.php';
require_once '../../includes/headers/header_instructor.php';
require_once '../../includes/sidebars/sidebar_instructor.php';
?>

<div class="purple-glow top-left"></div>
<div class="purple-glow bottom-right"></div>

<div class="instructor-viewport-wrapper">
    <main class="main-content">
        <div class="dashboard-main-container bk-main-container workouts-main-wrapper">
            <div class="content-header-row workouts-header-row">
                <div>
                    <h2 class="page-main-heading m-0">Instructor Recommendations</h2>
                    <p class="page-sub-heading m-0">Create and manage workout routines for student members.</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm workouts-create-btn" id="open-create-workout">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                    Create New Routine
                </button>
            </div>

            <div id="instructor-workout-status" class="workouts-feedback" role="status" aria-live="polite" hidden></div>
            <?php if (empty($workouts_list)): ?>
                <div class="glass-card workouts-empty-state">You have not created any recommendations. New routines are published immediately.</div>
            <?php else: ?>
                <div class="workouts-grid">
                    <?php foreach ($workouts_list as $workout):
                        $workoutJson = htmlspecialchars(
                            json_encode($workout, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                    ?>
                        <article class="glass-card workouts-card workout-border-secondary" data-workout="<?= $workoutJson ?>">
                            <div class="workouts-card-top">
                                <span class="gl-badge workouts-level-badge"><?= htmlspecialchars($workout['Workout_Level'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="workouts-action-group">
                                    <button type="button" class="toggle-btn workouts-action-btn workouts-view-btn" aria-label="View routine details">
                                        <span class="material-symbols-outlined workouts-icon-sm" aria-hidden="true">visibility</span>
                                    </button>
                                    <button type="button" class="toggle-btn workouts-action-btn workouts-edit-btn" aria-label="Edit routine">
                                        <span class="material-symbols-outlined workouts-icon-sm" aria-hidden="true">edit</span>
                                    </button>
                                    <button type="button" class="toggle-btn txt-error workouts-action-btn workouts-delete-btn" aria-label="Delete routine">
                                        <span class="material-symbols-outlined workouts-icon-sm" aria-hidden="true">delete</span>
                                    </button>
                                </div>
                            </div>
                            <h3 class="workouts-card-title"><?= htmlspecialchars($workout['Title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="workouts-card-description"><?= htmlspecialchars($workout['Description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="workouts-card-footer-grid">
                                <div>
                                    <span class="workouts-meta-label">Exercises</span>
                                    <span class="workouts-meta-val"><?= count($workout['Exercises']) ?></span>
                                </div>
                                <div>
                                    <span class="workouts-meta-label">Duration</span>
                                    <span class="workouts-meta-val"><?= $workout['Duration'] !== null ? (int)$workout['Duration'] . ' min' : 'Not set' ?></span>
                                </div>
                            </div>
                            <span class="workouts-published-note">Published · visible to members</span>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<div class="workouts-modal-overlay" id="workout-form-modal" aria-hidden="true">
    <div class="glass-card workouts-dialog" role="dialog" aria-modal="true" aria-labelledby="workout-form-title">
        <div class="workouts-dialog-header">
            <div>
                <h3 id="workout-form-title">Create recommendation</h3>
                <p>New and updated recommendations are published immediately.</p>
            </div>
            <button type="button" class="workouts-dialog-close" data-close-workouts-modal aria-label="Close">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>
        <form id="instructor-workout-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="workout_id" value="">
            <div class="workouts-dialog-body custom-scrollbar">
                <div id="instructor-form-status" class="workouts-feedback" role="status" aria-live="polite" hidden></div>
                <label class="workouts-field">
                    <span>ROUTINE NAME</span>
                    <input class="cal-input-field" name="title" type="text" maxlength="150" required>
                </label>
                <div class="workouts-field-grid">
                    <label class="workouts-field">
                        <span>DIFFICULTY</span>
                        <select class="cal-input-field" name="level" required>
                            <option value="">Select difficulty</option>
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </label>
                    <label class="workouts-field">
                        <span>DURATION (MINUTES)</span>
                        <input class="cal-input-field" name="duration" type="number" min="1" max="1440" step="1" required>
                    </label>
                </div>
                <label class="workouts-field">
                    <span>INSTRUCTIONS</span>
                    <textarea class="cal-input-field" name="description" rows="4" maxlength="10000" placeholder="Add guidance, rest periods, or safety notes."></textarea>
                </label>
                <div class="workouts-dialog-section-heading">
                    <h4>Exercises</h4>
                    <span>Sets and reps are required</span>
                </div>
                <div id="instructor-exercises" class="workouts-exercise-builder"></div>
                <button type="button" class="btn btn-glass workouts-add-exercise" id="add-instructor-exercise">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                    Add exercise
                </button>
            </div>
            <div class="workouts-dialog-footer">
                <button type="button" class="btn btn-glass" data-close-workouts-modal>Cancel</button>
                <button type="submit" class="btn btn-primary" id="instructor-workout-submit">Publish recommendation</button>
            </div>
        </form>
    </div>
</div>

<div class="workouts-modal-overlay" id="workout-details-modal" aria-hidden="true">
    <div class="glass-card workouts-dialog workouts-details-dialog" role="dialog" aria-modal="true" aria-labelledby="workout-details-title">
        <div class="workouts-dialog-header">
            <div>
                <h3 id="workout-details-title"></h3>
                <p id="workout-details-meta"></p>
            </div>
            <button type="button" class="workouts-dialog-close" data-close-workouts-modal aria-label="Close">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="workouts-dialog-body">
            <p class="workouts-details-description" id="workout-details-description"></p>
            <ul class="wk-modal-ex-list" id="workout-details-exercises"></ul>
        </div>
    </div>
</div>

<?php
require_once '../../includes/bottombar/bottombar_instructor.php';
require_once '../../includes/footers/footer_common.php';
?>

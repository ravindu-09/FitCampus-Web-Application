<?php
require_once '../../controllers/member/workouts_page_controller.php';
require_once '../../includes/headers/header_member.php';

if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        <section class="wk-hero-banner wk-margin-b-lg">
            <div class="wk-hero-content">
                <h2 class="wk-hero-title">Elevate Your Performance</h2>
                <p class="wk-hero-sub">Build your own training routines or follow recommendations from FitCampus instructors.</p>
            </div>
        </section>

        <div id="workout-status" class="wk-feedback" role="status" aria-live="polite" hidden></div>

        <section class="wk-workout-section wk-margin-b-xl">
            <div class="wk-section-head wk-margin-b-md">
                <h3 class="monitor-title">Instructor Recommendations</h3>
                <span class="wk-badge-verified">Published Plans</span>
            </div>
            <?php if (empty($recommendations)): ?>
                <div class="glass-card wk-empty-state">There are no instructor recommendations yet. Check back soon.</div>
            <?php else: ?>
                <div class="wk-workout-grid">
                    <?php foreach ($recommendations as $workout):
                        $workoutJson = htmlspecialchars(
                            json_encode($workout, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                    ?>
                        <article class="glass-card wk-card wk-workout-card" data-workout="<?= $workoutJson ?>">
                            <div class="wk-card-icon-header bg-icon-primary">
                                <span class="material-symbols-outlined" aria-hidden="true">fitness_center</span>
                            </div>
                            <div class="wk-card-body">
                                <div class="wk-card-topline">
                                    <span class="wk-tag tag-purple"><?= htmlspecialchars($workout['Workout_Level'] ?? 'General', ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="wk-card-meta"><?= $workout['Duration'] !== null ? (int)$workout['Duration'] . ' min' : 'Duration not set' ?></span>
                                </div>
                                <h4 class="wk-card-title"><?= htmlspecialchars($workout['Title'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p class="wk-card-meta">By <?= htmlspecialchars($workout['Instructor_Name'], ENT_QUOTES, 'UTF-8') ?> · <?= count($workout['Exercises']) ?> exercises</p>
                                <div class="wk-card-actions wk-card-actions-stack">
                                    <button type="button" class="btn btn-glass btn-sm wk-view-details">View details</button>
                                    <div class="wk-card-actions">
                                        <button type="button" class="btn btn-glass btn-sm wk-save-toggle" data-saved="<?= $workout['Is_Saved'] ? '1' : '0' ?>">
                                            <?= $workout['Is_Saved'] ? 'Unsave' : 'Save' ?>
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm wk-copy-workout">Copy to my workouts</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="wk-workout-section wk-margin-b-lg">
            <div class="wk-section-head wk-margin-b-md">
                <h3 class="monitor-title">My Personal Workouts</h3>
                <button type="button" class="btn btn-primary wk-create-workout" id="btn-open-create-modal">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                    Create routine
                </button>
            </div>
            <?php if (empty($personal_workouts)): ?>
                <div class="glass-card wk-empty-state">You have no personal routines yet. Create one or copy an instructor recommendation.</div>
            <?php else: ?>
                <div class="wk-workout-grid">
                    <?php foreach ($personal_workouts as $workout):
                        $workoutJson = htmlspecialchars(
                            json_encode($workout, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP),
                            ENT_QUOTES,
                            'UTF-8'
                        );
                    ?>
                        <article class="glass-card wk-card wk-workout-card wk-personal-card" data-workout="<?= $workoutJson ?>">
                            <div class="wk-card-icon-header bg-icon-secondary">
                                <span class="material-symbols-outlined" aria-hidden="true">exercise</span>
                            </div>
                            <div class="wk-card-body">
                                <div class="wk-card-topline">
                                    <span class="wk-tag tag-green">Personal routine</span>
                                    <span class="wk-card-meta"><?= $workout['Duration'] !== null ? (int)$workout['Duration'] . ' min' : 'Duration not set' ?></span>
                                </div>
                                <h4 class="wk-card-title"><?= htmlspecialchars($workout['Title'], ENT_QUOTES, 'UTF-8') ?></h4>
                                <p class="wk-card-meta"><?= count($workout['Exercises']) ?> exercises</p>
                                <ul class="wk-exercise-list">
                                    <?php foreach (array_slice($workout['Exercises'], 0, 3) as $exercise): ?>
                                        <li>
                                            <span class="ex-name-plain"><?= htmlspecialchars($exercise['Exercise_Name'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="ex-reps-plain"><?= (int)$exercise['Sets'] ?> × <?= (int)$exercise['Reps'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="wk-card-actions">
                                    <button type="button" class="btn btn-glass btn-sm wk-view-details">Details</button>
                                    <button type="button" class="action-btn-icon edit-btn wk-edit-workout" aria-label="Edit <?= htmlspecialchars($workout['Title'], ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="material-symbols-outlined" aria-hidden="true">edit</span>
                                    </button>
                                    <button type="button" class="action-btn-icon delete-btn wk-delete-workout" aria-label="Delete <?= htmlspecialchars($workout['Title'], ENT_QUOTES, 'UTF-8') ?>">
                                        <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                    </button>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php
    if ($is_captain) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    ?>
</div>

<div class="wk-modal-overlay" id="routine-modal" aria-hidden="true">
    <div class="wk-modal-box glass-card shadow-2xl wk-routine-modal" role="dialog" aria-modal="true" aria-labelledby="routine-modal-title">
        <div class="wk-modal-header">
            <div>
                <h3 class="wk-modal-title" id="routine-modal-title">Create personal routine</h3>
                <p class="wk-modal-sub">Configure a routine and its exercises.</p>
            </div>
            <button type="button" class="wk-modal-close" data-close-modal="routine-modal" aria-label="Close">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>
        <form id="routine-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="workout_id" value="">
            <div class="wk-modal-body custom-scrollbar">
                <div id="routine-form-status" class="wk-feedback" role="status" aria-live="polite" hidden></div>
                <div class="form-group-cal">
                    <label for="routine-title">ROUTINE NAME</label>
                    <input class="cal-input-field" id="routine-title" name="title" type="text" maxlength="150" required>
                </div>
                <div class="form-group-cal">
                    <label for="routine-duration">TOTAL DURATION (MINUTES)</label>
                    <input class="cal-input-field" id="routine-duration" name="duration" type="number" min="1" max="1440" step="1" required>
                </div>
                <div class="wk-form-section-head">
                    <h4>Exercises</h4>
                    <span class="wk-card-meta">Sets and reps are required</span>
                </div>
                <div id="routine-exercises" class="wk-exercise-builder"></div>
                <button type="button" class="wk-add-ex-row-btn" id="add-routine-exercise">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                    Add exercise
                </button>
            </div>
            <div class="wk-modal-footer">
                <button type="button" class="btn btn-glass" data-close-modal="routine-modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="routine-submit">Save routine</button>
            </div>
        </form>
    </div>
</div>

<div class="wk-modal-overlay" id="details-modal" aria-hidden="true">
    <div class="wk-modal-box glass-card shadow-2xl wk-routine-modal" role="dialog" aria-modal="true" aria-labelledby="details-title">
        <div class="wk-modal-header">
            <div>
                <h3 class="wk-modal-title" id="details-title"></h3>
                <p class="wk-modal-sub" id="details-meta"></p>
            </div>
            <button type="button" class="wk-modal-close" data-close-modal="details-modal" aria-label="Close">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>
        <div class="wk-modal-body custom-scrollbar">
            <p class="wk-details-description" id="details-description"></p>
            <ul class="wk-modal-ex-list" id="details-exercises"></ul>
        </div>
        <div class="wk-modal-footer">
            <button type="button" class="btn btn-glass" data-close-modal="details-modal">Close</button>
        </div>
    </div>
</div>

<?php require_once '../../includes/footers/footer_common.php'; ?>

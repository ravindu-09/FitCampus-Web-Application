<?php
// views/captain/planner.php
require_once '../../controllers/captain/planner_page_controller.php';
require_once '../../includes/headers/header_member.php';
require_once '../../includes/sidebars/sidebar_captain.php';
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container bk-main-container" style="max-width: 850px; margin: 0 auto;">
        <div class="glass-card inner-glow overflow-hidden border-dim bk-mb-lg" style="animation: slideUpFade 0.5s ease-out;">
            <div class="bk-p-md border-b-dim flex-between items-center bg-dim">
                <h3 class="settings-main-title text-lg m-0" id="workout-form-title">Create Reusable Team Workout</h3>
            </div>

            <div class="bk-card-pad space-y-md">
                <form id="workout-planner-form" class="space-y-md" method="POST" action="../../controllers/captain/workout_action.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="workout_id" id="workout-id" value="">
                    <input type="hidden" name="action" id="workout-action" value="create">

                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent" for="workout-team">SELECT TEAM</label>
                        <select class="cal-input-field gl-select" name="team_id" id="workout-team" required>
                            <option value="" disabled selected>Select a team you captain</option>
                            <?php foreach ($captain_teams as $team): ?>
                                <option value="<?= (int)$team['Team_ID'] ?>"><?= htmlspecialchars($team['Team_Name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent" for="workout-title">WORKOUT NAME</label>
                        <input class="cal-input-field" id="workout-title" type="text" name="title" maxlength="150" placeholder="e.g., Full Body Blast" required>
                    </div>

                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent" for="workout-description">DESCRIPTION &amp; PROTOCOL</label>
                        <textarea class="cal-input-field" id="workout-description" name="description" rows="3" maxlength="5000" placeholder="Define objectives and safety protocols..."></textarea>
                    </div>

                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent" for="workout-duration">TOTAL DURATION (MINUTES)</label>
                        <input class="cal-input-field" id="workout-duration" type="number" name="duration" min="1" max="1440" placeholder="e.g., 45" required>
                    </div>

                    <div class="bg-dim border-dim bk-p-md space-y-md" style="border-radius: 12px;" id="exercises-container">
                        <div class="exercise-block">
                            <div class="form-group-cal space-y-xs bk-mb-md">
                                <label class="gl-lbl-accent">EXERCISE NAME</label>
                                <select class="cal-input-field gl-select exercise-select" required>
                                    <option value="" disabled selected>Select an exercise</option>
                                    <optgroup label="Strength">
                                        <option value="Squat">Squat</option>
                                        <option value="Bench Press">Bench Press</option>
                                        <option value="Deadlift">Deadlift</option>
                                        <option value="Overhead Press">Overhead Press</option>
                                        <option value="Barbell Row">Barbell Row</option>
                                        <option value="Pull-Up">Pull-Up</option>
                                        <option value="Push-Up">Push-Up</option>
                                        <option value="Lunges">Lunges</option>
                                    </optgroup>
                                    <optgroup label="Lower Body">
                                        <option value="Leg Press">Leg Press</option>
                                        <option value="Leg Curl">Leg Curl</option>
                                        <option value="Leg Extension">Leg Extension</option>
                                        <option value="Calf Raise">Calf Raise</option>
                                    </optgroup>
                                    <optgroup label="Core">
                                        <option value="Plank">Plank</option>
                                        <option value="Sit-Up">Sit-Up</option>
                                        <option value="Crunch">Crunch</option>
                                        <option value="Russian Twist">Russian Twist</option>
                                    </optgroup>
                                    <optgroup label="Cardio">
                                        <option value="Running">Running</option>
                                        <option value="Cycling">Cycling</option>
                                        <option value="Jump Rope">Jump Rope</option>
                                    </optgroup>
                                    <option value="__custom__">Custom Exercise...</option>
                                </select>
                                <div class="custom-exercise-field" hidden>
                                    <input class="cal-input-field exercise-custom" type="text" maxlength="100" placeholder="Enter exercise name">
                                </div>
                                <input type="hidden" name="exercise_name[]" class="exercise-name-value" required>
                            </div>
                            <div class="cal-grid-2 gap-3">
                                <div class="form-group-cal space-y-xs">
                                    <label class="gl-lbl-accent">SETS</label>
                                    <input class="cal-input-field ex-sets" type="number" name="exercise_sets[]" min="1" max="1000" placeholder="3" required>
                                </div>
                                <div class="form-group-cal space-y-xs">
                                    <label class="gl-lbl-accent">REPS</label>
                                    <input class="cal-input-field ex-reps" type="number" name="exercise_reps[]" min="1" max="1000" placeholder="10" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-exercise-btn" class="w-full bk-py-md bg-dim border-dim txt-muted font-semibold text-sm hover-bg-dim transition-colors" style="border-radius: 8px; cursor: pointer; border-style: dashed;">
                        + Add Another Exercise
                    </button>
                    <div id="form-status-msg" class="hidden bk-p-sm font-semibold text-sm text-center" role="status" style="border-radius: 8px;"></div>

                    <div class="flex-between gap-3 bk-mt-md bg-dim bk-p-sm" style="flex-wrap: wrap; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center" type="submit" id="btn-save-workout">Save Workout</button>
                        <button class="gl-btn-gradient flex-1 bk-py-md font-bold text-sm justify-center" type="reset" id="btn-reset-workout" style="background: transparent; border: 1px solid rgba(255,255,255,0.1); color: var(--on-surface-variant);">Clear Form</button>
                    </div>
                </form>
            </div>
        </div>

        <section class="bk-mb-lg space-y-md">
            <div class="flex-between border-b-dim bk-pb-sm">
                <h4 class="settings-main-title text-base txt-primary flex-items-center gap-2 m-0 uppercase tracking-wide">
                    <span class="material-symbols-outlined">fitness_center</span> Reusable Team Workouts
                </h4>
                <span class="gl-badge badge-bg-primary txt-primary font-xs uppercase font-bold tracking-wide"><?= count($workouts) ?> WORKOUTS</span>
            </div>

            <?php if (empty($workouts)): ?>
                <div class="glass-card bk-p-md txt-muted">No reusable workouts have been created for your teams yet.</div>
            <?php else: ?>
                <div class="space-y-md">
                    <?php foreach ($workouts as $workout): ?>
                        <?php $workoutJson = json_encode($workout, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
                        <article class="glass-card bk-p-md border-dim">
                            <div class="flex-between items-center gap-3">
                                <div>
                                    <p class="font-bold text-base m-0 txt-white"><?= htmlspecialchars($workout['Title'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="font-xs txt-muted m-0 bk-mt-xs">
                                        <?= htmlspecialchars($workout['Team_Name'], ENT_QUOTES, 'UTF-8') ?>
                                        &middot; <?= (int)$workout['Duration'] ?> min
                                    </p>
                                </div>
                                <div class="flex-items-center gap-2">
                                    <button type="button" class="toggle-btn edit-workout-btn" data-workout="<?= htmlspecialchars($workoutJson, ENT_QUOTES, 'UTF-8') ?>" aria-label="Edit workout" title="Edit workout" style="padding: 8px;">
                                        <span class="material-symbols-outlined">edit</span>
                                    </button>
                                    <button type="button" class="toggle-btn txt-error delete-workout-btn" data-workout-id="<?= (int)$workout['Workout_ID'] ?>" data-team-id="<?= (int)$workout['Team_ID'] ?>" aria-label="Delete workout" title="Delete workout" style="padding: 8px;">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </div>
                            </div>
                            <?php if (!empty($workout['Description'])): ?>
                                <p class="txt-muted text-sm bk-mt-sm"><?= nl2br(htmlspecialchars($workout['Description'], ENT_QUOTES, 'UTF-8')) ?></p>
                            <?php endif; ?>
                            <ul class="wk-exercise-list bk-mt-sm">
                                <?php foreach ($workout['Exercises'] as $exercise): ?>
                                    <li>
                                        <div class="ex-name"><span class="ex-dot dot-primary"></span><?= htmlspecialchars($exercise['Exercise_Name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="ex-reps"><?= (int)$exercise['Sets'] ?> &times; <?= (int)$exercise['Reps'] ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php
    require_once '../../includes/bottombar/bottombar_captain.php';
    require_once '../../includes/footers/footer_common.php';
    ?>
</div>
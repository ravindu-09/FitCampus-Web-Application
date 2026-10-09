<?php
// views/member/workout_details.php
require_once '../../controllers/member/workout_details_page_controller.php';
require_once '../../includes/headers/header_member.php';

if (!empty($_SESSION['is_captain'])) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        <?php if ($workout_details === null): ?>
            <section class="glass-card tm-section-pad" role="alert">
                <h2 class="settings-main-title">Workout unavailable</h2>
                <p class="text-color-on-surface-variant">This workout is not assigned to an approved booking you can access.</p>
                <a class="btn btn-secondary-action btn-sm" href="teams.php">Return to your teams</a>
            </section>
        <?php else: ?>
            <a class="btn btn-glass btn-sm tm-back-btn" href="teams.php?team_id=<?= (int)$workout_details['Team_ID'] ?>">
    <span class="material-symbols-outlined">arrow_back</span> Back to team
</a>

            <section class="wk-view-container tm-section-pad" aria-labelledby="assigned-workout-title">
                <div class="wk-section-head tm-section-head">
                    <div>
                        <span class="wk-badge-verified">Assigned Team Workout</span>
                        <h2 class="monitor-title" id="assigned-workout-title"><?= htmlspecialchars($workout_details['Title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                </div>

                <article class="glass-card wk-card">
                    <div class="wk-card-icon-header bg-icon-secondary">
                        <span class="material-symbols-outlined">fitness_center</span>
                    </div>
                    <div class="wk-card-body">
                        <p class="wk-card-meta">
                            <?= $workout_details['Duration'] !== null ? (int)$workout_details['Duration'] . ' mins' : 'Duration not specified' ?>
                        </p>
                        <?php if (!empty($workout_details['Description'])): ?>
                            <p class="wk-card-meta"><?= nl2br(htmlspecialchars($workout_details['Description'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php endif; ?>

                        <h3 class="monitor-title tm-section-head">Exercises</h3>
                        <?php if (empty($workout_details['Exercises'])): ?>
                            <p class="wk-card-meta">No exercises listed.</p>
                        <?php else: ?>
                            <ul class="wk-exercise-list">
                                <?php foreach ($workout_details['Exercises'] as $exercise): ?>
                                    <li>
                                        <div class="ex-name"><span class="ex-dot dot-secondary"></span><?= htmlspecialchars($exercise['Exercise_Name'], ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="ex-reps"><?= (int)$exercise['Sets'] ?> &times; <?= (int)$exercise['Reps'] ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </article>
            </section>
        <?php endif; ?>
    </main>

    <?php
    if (!empty($_SESSION['is_captain'])) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    require_once '../../includes/footers/footer_common.php';
    ?>
</div>
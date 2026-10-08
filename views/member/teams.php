<?php
// views/member/teams.php
require_once '../../controllers/member/teams_page_controller.php';
require_once '../../includes/headers/header_member.php';

if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        <?php if ($team_access_denied): ?>
            <div class="glass-card tm-section-pad" role="alert">
                <h2 class="settings-main-title">Team unavailable</h2>
                <p class="text-color-on-surface-variant">You do not have access to that team.</p>
                <a class="btn btn-secondary-action btn-sm" href="teams.php">View your teams</a>
            </div>
        <?php elseif (empty($teams)): ?>
            <div class="glass-card tm-section-pad">
                <h2 class="settings-main-title">Your Teams</h2>
                <p class="text-color-on-surface-variant">You are not currently listed as a member of a team.</p>
            </div>
        <?php else: ?>
            <div class="tm-header-bar" id="team-toggle-container">
                <div class="tm-header-title">
                    <span class="material-symbols-outlined text-color-secondary gl-btn-icon-pad" style="font-size: 32px;">groups</span>
                    <h2 class="settings-main-title m-0"><?= htmlspecialchars($selected_team['Team_Name'], ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <?php if (count($teams) > 1): ?>
                    <button type="button" class="btn btn-secondary-action btn-sm m-0" id="team-toggle-btn" aria-expanded="false" aria-controls="team-selection-modal">
                        <span class="material-symbols-outlined">swap_horiz</span>
                        <span>Change Team</span>
                    </button>
                <?php endif; ?>
            </div>

            <section class="wk-view-container tm-section-pad" aria-labelledby="team-overview-heading">
                <div class="wk-section-head tm-section-head">
                    <h3 class="monitor-title m-0" id="team-overview-heading">Team Overview</h3>
                </div>
                <p class="text-color-on-surface-variant">
                    <?= htmlspecialchars($selected_team['Sport'], ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($selected_team['Sport_Gender'])): ?>
                        &middot; <?= htmlspecialchars($selected_team['Sport_Gender'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </p>
            </section>

            <section id="scheduled-training-sessions" class="wk-view-container tm-section-pad" aria-labelledby="scheduled-sessions-heading">
                <div class="wk-section-head tm-section-head">
                    <h3 class="monitor-title m-0" id="scheduled-sessions-heading">Scheduled Training Sessions</h3>
                    <span class="wk-badge-verified">Next 2 Weeks</span>
                </div>

                <?php if (empty($scheduled_sessions)): ?>
                    <div class="glass-card tm-empty-state">
                        <span class="material-symbols-outlined tm-empty-icon">event_busy</span>
                        <p class="m-0 text-color-on-surface-variant">No approved training sessions scheduled in the next two weeks.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-md">
                        <?php $last_session_date = null; ?>
                        <?php foreach ($scheduled_sessions as $session): ?>
                            <?php if ($last_session_date !== $session['Reserve_Date']): ?>
                                <?php $last_session_date = $session['Reserve_Date']; ?>
                                <h4 class="settings-main-title text-base txt-primary bk-mt-md">
                                    <?= htmlspecialchars(date('l, F j', strtotime($session['Reserve_Date'])), ENT_QUOTES, 'UTF-8') ?>
                                </h4>
                            <?php endif; ?>
                            <article class="glass-card bk-p-md border-dim">
                                <div class="flex-between items-center gap-3">
                                    <div class="flex-items-center gap-3">
                                        <span class="material-symbols-outlined text-color-secondary">schedule</span>
                                        <div>
                                            <p class="font-bold text-base m-0 txt-white">
                                                <?= htmlspecialchars(date('g:i A', strtotime($session['Start_Time'])) . ' - ' . date('g:i A', strtotime($session['End_Time'])), ENT_QUOTES, 'UTF-8') ?>
                                            </p>
                                            <p class="font-xs txt-muted m-0 bk-mt-xs">
                                                <?= htmlspecialchars($session['Facility_Name'], ENT_QUOTES, 'UTF-8') ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="badge-status" style="background: rgba(74,225,118,0.1); border-color: rgba(74,225,118,0.3); color: var(--secondary);">Approved</span>
                                </div>
                                <p class="font-xs txt-muted bk-mt-sm mb-0">No workout assigned</p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section id="team-goals-section" class="wk-view-container tm-section-pad" aria-labelledby="team-goals-heading">
                <div class="wk-section-head tm-section-head">
                    <h3 class="monitor-title m-0" id="team-goals-heading">Team Goals</h3>
                </div>
                <div class="glass-card tm-empty-state tm-empty-full">
                    <span class="material-symbols-outlined tm-empty-icon">flag</span>
                    <p class="m-0 text-color-on-surface-variant">Coming soon</p>
                </div>
            </section>

            <section id="team-workouts" class="wk-view-container tm-section-pad" aria-labelledby="team-workouts-heading">
                <div class="wk-section-head tm-section-head">
                    <h3 class="monitor-title m-0" id="team-workouts-heading">Team Workouts</h3>
                    <span class="wk-badge-captain">Reusable Plans</span>
                </div>

                <?php if (empty($team_workouts)): ?>
                    <div class="glass-card tm-empty-state">
                        <span class="material-symbols-outlined tm-empty-icon">fitness_center</span>
                        <p class="m-0 text-color-on-surface-variant">No team workouts have been created yet.</p>
                    </div>
                <?php else: ?>
                    <div class="wk-scroll-track custom-scrollbar">
                        <?php foreach ($team_workouts as $workout): ?>
                            <article class="glass-card wk-card">
                                <div class="wk-card-icon-header bg-icon-secondary">
                                    <span class="material-symbols-outlined">fitness_center</span>
                                </div>
                                <div class="wk-card-body">
                                    <h4 class="wk-card-title"><?= htmlspecialchars($workout['Title'], ENT_QUOTES, 'UTF-8') ?></h4>
                                    <p class="wk-card-meta">
                                        <?= $workout['Duration'] !== null ? (int)$workout['Duration'] . ' mins' : 'Duration not specified' ?>
                                    </p>
                                    <?php if (!empty($workout['Description'])): ?>
                                        <p class="wk-card-meta"><?= nl2br(htmlspecialchars($workout['Description'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    <?php endif; ?>
                                    <?php if (empty($workout['Exercises'])): ?>
                                        <p class="wk-card-meta">No exercises listed.</p>
                                    <?php else: ?>
                                        <ul class="wk-exercise-list">
                                            <?php foreach ($workout['Exercises'] as $exercise): ?>
                                                <li>
                                                    <div class="ex-name"><span class="ex-dot dot-secondary"></span><?= htmlspecialchars($exercise['Exercise_Name'], ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div class="ex-reps"><?= (int)$exercise['Sets'] ?> &times; <?= (int)$exercise['Reps'] ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <?php if (!$team_access_denied && count($teams) > 1): ?>
    <div class="wk-modal-overlay" id="team-selection-modal" aria-label="Choose a team" role="dialog" aria-modal="true">
        <div class="wk-modal-box glass-card shadow-2xl tm-modal-box">
            <div class="wk-modal-header">
                <h3 class="wk-modal-title text-color-on-surface">Your Teams</h3>
                <button type="button" class="wk-modal-close" id="btn-close-team-modal" aria-label="Close team selection">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="wk-modal-body">
                <div class="wk-team-list">
                    <?php foreach ($teams as $team): ?>
                        <a class="wk-team-select-btn" style="color: inherit; text-decoration: none;" href="teams.php?team_id=<?= (int)$team['Team_ID'] ?>" <?= (int)$team['Team_ID'] === (int)$selected_team['Team_ID'] ? 'aria-current="page"' : '' ?>>
                            <div class="wk-team-icon bg-icon-secondary"><span class="material-symbols-outlined">groups</span></div>
                            <div class="text-left">
                                <p class="wk-team-name m-0"><?= htmlspecialchars($team['Team_Name'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p class="wk-team-meta m-0"><?= htmlspecialchars($team['Sport'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php
    if ($is_captain) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    require_once '../../includes/footers/footer_common.php';
    ?>
</div>
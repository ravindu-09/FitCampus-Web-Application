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
                    <span class="material-symbols-outlined text-color-secondary gl-btn-icon-pad team-header-icon">groups</span>
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
                                    <span class="badge-status booking-status-approved">Approved</span>
                                </div>
                                <div class="bk-mt-sm">
                                    <p class="font-xs txt-muted m-0">Assigned Workouts</p>
                                    <?php if (empty($session['Assigned_Workouts'])): ?>
                                        <p class="font-xs txt-muted bk-mt-xs mb-0">No workout assigned</p>
                                    <?php else: ?>
                                        <ul class="wk-exercise-list">
                                            <?php foreach ($session['Assigned_Workouts'] as $assigned_workout): ?>
                                                <li>
                                                    <a class="tm-workout-link" href="workout_details.php?booking_id=<?= (int)$session['Booking_ID'] ?>&amp;workout_id=<?= (int)$assigned_workout['Workout_ID'] ?>">
                                                        <?= htmlspecialchars($assigned_workout['Title'], ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
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

            <section id="team-goals-section" class="wk-view-container tm-section-pad" aria-labelledby="team-goals-heading">
                <div class="wk-section-head tm-section-head">
                    <h3 class="monitor-title m-0" id="team-goals-heading">Team Goals</h3>
                    <span class="wk-badge-captain">Shared targets</span>
                </div>
                <?php if ($team_goal_success !== ''): ?>
                    <div class="team-goal-success-toast" role="status" aria-live="polite" aria-atomic="true">
                        <span class="material-symbols-outlined team-goal-success-icon" aria-hidden="true">check_circle</span>
                        <span class="team-goal-success-message"><?= htmlspecialchars($team_goal_success, ENT_QUOTES, 'UTF-8') ?></span>
                        <button class="team-goal-success-dismiss" type="button" aria-label="Dismiss success notification">
                            <span class="material-symbols-outlined" aria-hidden="true">close</span>
                        </button>
                    </div>
                <?php endif; ?>
                <?php if ($team_goal_error !== ''): ?>
                    <div class="captain-feedback captain-feedback-error" role="alert"><?= htmlspecialchars($team_goal_error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if (empty($team_goals)): ?>
                    <div class="glass-card tm-empty-state tm-empty-full">
                        <span class="material-symbols-outlined tm-empty-icon">flag</span>
                        <p class="m-0 text-color-on-surface-variant">Your team has no goals yet.</p>
                    </div>
                <?php else: ?>
                    <div class="captain-goals-grid member-team-goals-grid">
                        <?php foreach ($team_goals as $goal): ?>
                            <?php
                            $target = (float)$goal['Target_Value'];
                            $progress = (float)$goal['Total_Progress'];
                            $percentage = (int)$goal['Progress_Percentage'];
                            $today = new DateTimeImmutable('today');
                            $startDate = new DateTimeImmutable($goal['Start_Date']);
                            $endDate = new DateTimeImmutable($goal['End_Date']);
                            $daysToStart = (int)$today->diff($startDate)->format('%r%a');
                            $daysRemaining = (int)$today->diff($endDate)->format('%r%a');
                            $displayStatus = $goal['Effective_Status'] === 'Active' && $daysToStart > 0
                                ? 'Upcoming'
                                : $goal['Effective_Status'];
                            $topContributors = array_slice($goal['Leaderboard'], 0, 5);
                            $remainingContributors = array_slice($goal['Leaderboard'], 5);
                            $memberRank = null;
                            foreach ($goal['Leaderboard'] as $rankIndex => $contributor) {
                                if ((int)$contributor['User_ID'] === $user_id) {
                                    $memberRank = $rankIndex + 1;
                                    break;
                                }
                            }
                            ?>
                            <article class="glass-card captain-goal-card member-team-goal-card">
                                <div class="captain-goal-card-head">
                                    <div>
                                        <span class="captain-status-badge status-<?= htmlspecialchars(strtolower($displayStatus), ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($displayStatus, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <h4><?= htmlspecialchars($goal['Goal_Name'], ENT_QUOTES, 'UTF-8') ?></h4>
                                    </div>
                                    <span class="captain-goal-date">
                                        <span class="material-symbols-outlined" aria-hidden="true">event</span>
                                        <span>Due <?= htmlspecialchars(date('M j, Y', strtotime($goal['End_Date'])), ENT_QUOTES, 'UTF-8') ?></span>
                                    </span>
                                </div>
                                <div class="captain-goal-timeline">
                                    <span>
                                        <span class="material-symbols-outlined" aria-hidden="true">play_arrow</span>
                                        Starts <?= htmlspecialchars(date('M j', strtotime($goal['Start_Date'])), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <?php if ($goal['Effective_Status'] === 'Active'): ?>
                                        <span class="captain-goal-countdown">
                                            <?php if ($daysToStart > 0): ?>
                                                Starts in <?= $daysToStart ?> <?= $daysToStart === 1 ? 'day' : 'days' ?>
                                            <?php elseif ($daysRemaining === 0): ?>
                                                Ends today
                                            <?php elseif ($daysRemaining > 0): ?>
                                                <?= $daysRemaining ?> <?= $daysRemaining === 1 ? 'day' : 'days' ?> left
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($goal['Description'])): ?>
                                    <p class="captain-goal-copy"><?= nl2br(htmlspecialchars($goal['Description'], ENT_QUOTES, 'UTF-8')) ?></p>
                                <?php endif; ?>
                                <div class="member-team-goal-progress-summary">
                                    <div class="member-team-goal-team-progress">
                                        <span class="captain-goal-progress-label">TEAM PROGRESS</span>
                                        <strong><?= htmlspecialchars(number_format($progress, 2), ENT_QUOTES, 'UTF-8') ?> <span><?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></span></strong>
                                        <span class="member-team-goal-target">of <?= htmlspecialchars(number_format($target, 2), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div class="member-team-goal-personal">
                                        <span>YOUR CONTRIBUTION</span>
                                        <strong><?= htmlspecialchars(number_format((float)$goal['My_Contribution'], 2), ENT_QUOTES, 'UTF-8') ?> <small><?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></small></strong>
                                    </div>
                                </div>
                                <div class="captain-progress-track" role="progressbar" aria-label="<?= htmlspecialchars($goal['Goal_Name'], ENT_QUOTES, 'UTF-8') ?> progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $percentage ?>">
                                    <span style="width: <?= $percentage ?>%"></span>
                                </div>
                                <div class="captain-goal-meta">
                                    <strong><?= $percentage ?>%</strong>
                                    <span>Complete</span>
                                    <span class="captain-goal-contribution-count"><?= count($goal['Leaderboard']) ?> contributors</span>
                                </div>
                                <?php if ($goal['Can_Contribute']): ?>
                                    <details class="captain-goal-edit captain-contribution-form member-team-goal-contribution">
                                        <summary><span class="material-symbols-outlined" aria-hidden="true">add_circle</span>Log contribution</summary>
                                        <form method="POST" action="../../controllers/member/team_goal_contribution_action.php">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="team_id" value="<?= (int)$selected_team['Team_ID'] ?>">
                                            <input type="hidden" name="team_goal_id" value="<?= (int)$goal['Team_Goal_ID'] ?>">
                                            <label class="gl-lbl-accent">AMOUNT (<?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?>)
                                                <input class="cal-input-field" name="contribution_value" type="number" min="0.01" max="9999999999.99" step="0.01" required>
                                            </label>
                                            <label class="gl-lbl-accent">DATE
                                                <input class="cal-input-field" name="progress_date" type="date" min="<?= htmlspecialchars($goal['Start_Date'], ENT_QUOTES, 'UTF-8') ?>" max="<?= date('Y-m-d') ?>" required>
                                            </label>
                                            <button class="gl-btn-gradient" type="submit">Record contribution</button>
                                        </form>
                                    </details>
                                <?php endif; ?>
                                <section class="team-goal-leaderboard" aria-label="Team goal contributor rankings">
                                    <div class="team-goal-leaderboard-heading">
                                        <h4>Top contributors</h4>
                                        <span><?= count($goal['Leaderboard']) ?> <?= count($goal['Leaderboard']) === 1 ? 'member' : 'members' ?></span>
                                    </div>
                                    <?php if (empty($goal['Leaderboard'])): ?>
                                        <p class="team-goal-leaderboard-empty">Be the first to contribute to this team challenge!</p>
                                    <?php else: ?>
                                        <ol class="team-goal-leaderboard-list">
                                            <?php foreach ($topContributors as $rankIndex => $contributor): ?>
                                                <?php $isCurrentUser = (int)$contributor['User_ID'] === $user_id; ?>
                                                <li class="<?= $isCurrentUser ? 'is-current-user' : '' ?>">
                                                    <span class="team-goal-rank rank-<?= $rankIndex + 1 ?>"><?= $rankIndex + 1 ?></span>
                                                    <span class="team-goal-contributor-name">
                                                        <?= htmlspecialchars($contributor['Contributor_Name'], ENT_QUOTES, 'UTF-8') ?>
                                                        <?php if ($isCurrentUser): ?><small>You</small><?php endif; ?>
                                                    </span>
                                                    <strong><?= htmlspecialchars(number_format((float)$contributor['Total_Contribution'], 2), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                        <?php if ($memberRank !== null && $memberRank > 5): ?>
                                            <?php $ownRanking = $goal['Leaderboard'][$memberRank - 1]; ?>
                                            <div class="team-goal-own-ranking">
                                                <span>Your rank</span>
                                                <span class="team-goal-rank"><?= $memberRank ?></span>
                                                <strong><?= htmlspecialchars(number_format((float)$ownRanking['Total_Contribution'], 2), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($remainingContributors)): ?>
                                            <details class="team-goal-leaderboard-more">
                                                <summary>View all <?= count($goal['Leaderboard']) ?> contributors</summary>
                                                <ol class="team-goal-leaderboard-list" start="6">
                                                    <?php foreach ($remainingContributors as $rankOffset => $contributor): ?>
                                                        <?php $rank = $rankOffset + 6; ?>
                                                        <?php $isCurrentUser = (int)$contributor['User_ID'] === $user_id; ?>
                                                        <li class="<?= $isCurrentUser ? 'is-current-user' : '' ?>">
                                                            <span class="team-goal-rank"><?= $rank ?></span>
                                                            <span class="team-goal-contributor-name">
                                                                <?= htmlspecialchars($contributor['Contributor_Name'], ENT_QUOTES, 'UTF-8') ?>
                                                                <?php if ($isCurrentUser): ?><small>You</small><?php endif; ?>
                                                            </span>
                                                            <strong><?= htmlspecialchars(number_format((float)$contributor['Total_Contribution'], 2), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            </details>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </section>
                                <details class="captain-contribution-history">
                                    <summary>Team contribution history <span>(<?= (int)$goal['Contribution_Count'] ?>)</span></summary>
                                    <?php if (empty($goal['Contributions'])): ?>
                                        <p class="txt-muted">No contributions have been recorded.</p>
                                    <?php else: ?>
                                        <ul>
                                            <?php foreach ($goal['Contributions'] as $contribution): ?>
                                                <li>
                                                    <span>
                                                        <?= htmlspecialchars($contribution['Contributor_Name'], ENT_QUOTES, 'UTF-8') ?>
                                                        <?php if ((int)$contribution['User_ID'] === $user_id): ?><strong>(You)</strong><?php endif; ?>
                                                        · <?= htmlspecialchars(date('M j, Y', strtotime($contribution['Progress_Date'])), ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                    <strong>+<?= htmlspecialchars(number_format((float)$contribution['Contribution_Value'], 2), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </details>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
                        <a class="wk-team-select-btn team-selection-link" href="teams.php?team_id=<?= (int)$team['Team_ID'] ?>" <?= (int)$team['Team_ID'] === (int)$selected_team['Team_ID'] ? 'aria-current="page"' : '' ?>>
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
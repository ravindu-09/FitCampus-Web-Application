<?php
require_once '../../controllers/captain/team_goals_page_controller.php';
require_once '../../includes/headers/header_member.php';
require_once '../../includes/sidebars/sidebar_captain.php';
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container bk-main-container captain-goals-page">
        <header class="glass-card inner-glow-primary bk-card-pad captain-team-goals-heading">
            <div class="cal-header-flex">
                <div>
                    <h1 class="settings-main-title m-0">Team Goals</h1>
                    <p class="txt-muted captain-team-goal-subtitle">Set shared targets and follow every team contribution.</p>
                </div>
                <?php if (count($captain_teams) > 1): ?>
                    <form method="GET" class="captain-team-goal-filter">
                        <label class="gl-lbl-accent" for="goal-team-filter">SELECT TEAM</label>
                        <select class="cal-input-field gl-select" id="goal-team-filter" name="team_id">
                            <?php foreach ($captain_teams as $team): ?>
                                <option value="<?= (int)$team['Team_ID'] ?>" <?= (int)$team['Team_ID'] === $selected_team_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team['Team_Name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($success_message !== ''): ?>
            <div class="captain-feedback captain-feedback-success captain-goal-feedback" role="status">
                <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
                <?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message !== ''): ?>
            <div class="captain-feedback captain-feedback-error captain-goal-feedback" role="alert">
                <span class="material-symbols-outlined" aria-hidden="true">error</span>
                <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($selected_team === null): ?>
            <section class="glass-card inner-glow-primary captain-empty-state captain-team-goal-empty" role="alert">
                <span class="material-symbols-outlined">lock</span>
                <h2 class="settings-main-title">Team unavailable</h2>
                <p class="txt-muted">You are not authorized to manage goals for that team.</p>
                <a class="gl-btn-outline" href="team_goals.php">Return to your teams</a>
            </section>
        <?php else: ?>
            <section class="captain-goal-summary captain-team-goal-summary" aria-label="Team goal summary">
                <article class="glass-card captain-summary-card captain-team-goal-stat">
                    <span class="captain-summary-label">ACTIVE GOALS</span>
                    <strong class="txt-primary"><?= $goal_stats['active'] ?></strong>
                </article>
                <article class="glass-card captain-summary-card captain-team-goal-stat">
                    <span class="captain-summary-label">COMPLETED GOALS</span>
                    <strong class="txt-green"><?= $goal_stats['completed'] ?></strong>
                </article>
                <article class="glass-card captain-summary-card captain-team-goal-stat captain-team-contribution-stat">
                    <span class="captain-summary-label">TOTAL TEAM CONTRIBUTION</span>
                    <?php if (empty($goal_stats['contributions_by_unit'])): ?>
                        <strong>0</strong>
                    <?php else: ?>
                        <div class="captain-team-contribution-totals">
                            <?php foreach ($goal_stats['contributions_by_unit'] as $contributionTotal): ?>
                                <span>
                                    <strong><?= htmlspecialchars(number_format($contributionTotal['value'], 2), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small><?= htmlspecialchars($contributionTotal['unit'], ENT_QUOTES, 'UTF-8') ?></small>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </section>

            <section class="glass-card inner-glow-primary captain-team-goal-create" aria-labelledby="create-team-goal-title">
                <div class="bk-p-md border-b-dim flex-between bg-dim captain-team-goal-section-head">
                    <div>
                        <h2 class="settings-main-title captain-team-goal-title" id="create-team-goal-title">Create Team Goal</h2>
                        <p class="txt-muted captain-team-goal-subtitle"><?= htmlspecialchars($selected_team['Team_Name'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <span class="gl-badge badge-bg-primary txt-primary mono uppercase font-bold font-xs captain-shared-goal-badge">Shared target</span>
                </div>
                <form method="POST" action="../../controllers/captain/team_goal_action.php" class="captain-goal-form bk-card-pad">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="team_id" value="<?= $selected_team_id ?>">
                    <input type="hidden" name="action" value="create">
                    <div class="captain-goal-form-grid captain-team-goal-fields">
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent" for="new-goal-name">GOAL NAME</label>
                            <input class="cal-input-field" id="new-goal-name" name="goal_name" maxlength="150" required placeholder="e.g. Run 500 km together">
                        </div>
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent" for="new-goal-target">TARGET VALUE</label>
                            <input class="cal-input-field" id="new-goal-target" name="target_value" type="number" min="0.01" max="9999999999.99" step="0.01" required>
                        </div>
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent" for="new-goal-unit">UNIT</label>
                            <input class="cal-input-field" id="new-goal-unit" name="unit" maxlength="50" required placeholder="km, reps, hours">
                        </div>
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent" for="new-goal-start">START DATE</label>
                            <input class="cal-input-field" id="new-goal-start" name="start_date" type="date" required>
                        </div>
                        <div class="form-group-cal space-y-xs">
                            <label class="gl-lbl-accent" for="new-goal-end">DEADLINE</label>
                            <input class="cal-input-field" id="new-goal-end" name="end_date" type="date" required>
                        </div>
                    </div>
                    <div class="form-group-cal space-y-xs captain-team-goal-description-field">
                        <label class="gl-lbl-accent" for="new-goal-description">DESCRIPTION <span class="txt-muted">(OPTIONAL)</span></label>
                        <textarea class="cal-input-field captain-goal-description" id="new-goal-description" name="description" rows="3" maxlength="5000" placeholder="Add context for your team"></textarea>
                    </div>
                    <button class="gl-btn-gradient captain-goal-submit captain-team-goal-submit" type="submit">
                        <span class="material-symbols-outlined" aria-hidden="true">add</span>
                        Create team goal
                    </button>
                </form>
            </section>

            <section class="captain-goals-list" aria-labelledby="team-goals-list-title">
                <div class="flex-between border-b-dim bk-pb-sm captain-team-goal-list-heading">
                    <div>
                        <h2 class="settings-main-title captain-team-goal-title txt-primary" id="team-goals-list-title">Team Goals</h2>
                        <p class="txt-muted captain-team-goal-subtitle">Progress and member contributions</p>
                    </div>
                    <span class="gl-badge badge-bg-primary txt-primary mono uppercase font-bold font-xs"><?= (int)$goal_stats['total'] ?> GOALS</span>
                </div>

                <?php if (empty($goals)): ?>
                    <div class="glass-card inner-glow-primary captain-empty-state captain-team-goal-empty">
                        <span class="material-symbols-outlined" aria-hidden="true">flag</span>
                        <h3>No team goals yet</h3>
                        <p class="txt-muted">Create a shared target to get your team moving together.</p>
                    </div>
                <?php else: ?>
                    <div class="captain-goals-grid captain-team-goal-grid">
                        <?php foreach ($goals as $goal): ?>
                            <?php
                            $progress = (float)$goal['Total_Progress'];
                            $target = (float)$goal['Target_Value'];
                            $percentage = (int)$goal['Progress_Percentage'];
                            $today = new DateTimeImmutable('today');
                            $startDate = new DateTimeImmutable($goal['Start_Date']);
                            $endDate = new DateTimeImmutable($goal['End_Date']);
                            $daysToStart = (int)$today->diff($startDate)->format('%r%a');
                            $daysRemaining = (int)$today->diff($endDate)->format('%r%a');
                            $displayStatus = $goal['Effective_Status'] === 'Active' && $daysToStart > 0
                                ? 'Upcoming'
                                : $goal['Effective_Status'];
                            $statusClass = strtolower($displayStatus);
                            $goalId = (int)$goal['Team_Goal_ID'];
                            $topContributors = array_slice($goal['Leaderboard'], 0, 5);
                            $remainingContributors = array_slice($goal['Leaderboard'], 5);
                            ?>
                            <article class="glass-card inner-glow-primary captain-goal-card captain-team-goal-card">
                                <div class="captain-goal-card-head">
                                    <div>
                                        <span class="captain-status-badge status-<?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($displayStatus, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <h3><?= htmlspecialchars($goal['Goal_Name'], ENT_QUOTES, 'UTF-8') ?></h3>
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
                                <?php if ($goal['Description'] !== null && $goal['Description'] !== ''): ?>
                                    <p class="captain-goal-copy"><?= nl2br(htmlspecialchars($goal['Description'], ENT_QUOTES, 'UTF-8')) ?></p>
                                <?php endif; ?>
                                <div class="captain-goal-numbers">
                                    <div>
                                        <span class="captain-goal-progress-label">TEAM PROGRESS</span>
                                        <strong><?= htmlspecialchars(number_format($progress, 2), ENT_QUOTES, 'UTF-8') ?> <span><?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></span></strong>
                                    </div>
                                    <span class="captain-goal-target">of <?= htmlspecialchars(number_format($target, 2), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <div class="captain-progress-track" role="progressbar" aria-label="<?= htmlspecialchars($goal['Goal_Name'], ENT_QUOTES, 'UTF-8') ?> progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $percentage ?>">
                                    <span style="width: <?= $percentage ?>%"></span>
                                </div>
                                <div class="captain-goal-meta">
                                    <strong><?= $percentage ?>%</strong>
                                    <span>Complete</span>
                                    <span class="captain-goal-contribution-count"><?= count($goal['Leaderboard']) ?> contributors</span>
                                </div>

                                <?php if ($goal['Effective_Status'] === 'Active'): ?>
                                    <details class="captain-goal-edit captain-team-goal-edit">
                                        <summary><span class="material-symbols-outlined" aria-hidden="true">edit</span>Edit goal details</summary>
                                        <form method="POST" action="../../controllers/captain/team_goal_action.php" class="captain-goal-form captain-goal-edit-form">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="team_id" value="<?= $selected_team_id ?>">
                                            <input type="hidden" name="team_goal_id" value="<?= $goalId ?>">
                                            <input type="hidden" name="action" value="edit">
                                            <label class="gl-lbl-accent">GOAL NAME<input class="cal-input-field" name="goal_name" maxlength="150" required value="<?= htmlspecialchars($goal['Goal_Name'], ENT_QUOTES, 'UTF-8') ?>"></label>
                                            <label class="gl-lbl-accent">TARGET<input class="cal-input-field" name="target_value" type="number" min="0.01" max="9999999999.99" step="0.01" required value="<?= htmlspecialchars($goal['Target_Value'], ENT_QUOTES, 'UTF-8') ?>"></label>
                                            <label class="gl-lbl-accent">UNIT<input class="cal-input-field" name="unit" maxlength="50" required value="<?= htmlspecialchars($goal['Unit'], ENT_QUOTES, 'UTF-8') ?>"></label>
                                            <label class="gl-lbl-accent">START DATE<input class="cal-input-field" name="start_date" type="date" required value="<?= htmlspecialchars($goal['Start_Date'], ENT_QUOTES, 'UTF-8') ?>"></label>
                                            <label class="gl-lbl-accent">DEADLINE<input class="cal-input-field" name="end_date" type="date" required value="<?= htmlspecialchars($goal['End_Date'], ENT_QUOTES, 'UTF-8') ?>"></label>
                                            <label class="gl-lbl-accent captain-edit-description">DESCRIPTION<textarea class="cal-input-field" name="description" rows="3" maxlength="5000"><?= htmlspecialchars($goal['Description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
                                            <button class="gl-btn-gradient captain-team-goal-save" type="submit">Save changes</button>
                                        </form>
                                    </details>
                                    <div class="captain-goal-status-actions captain-team-goal-status-actions">
                                        <?php foreach (['Completed', 'Cancelled'] as $nextStatus): ?>
                                            <form method="POST" action="../../controllers/captain/team_goal_action.php" class="captain-goal-status-form" data-confirm-status="<?= strtolower($nextStatus) ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="team_id" value="<?= $selected_team_id ?>">
                                                <input type="hidden" name="team_goal_id" value="<?= $goalId ?>">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="status" value="<?= $nextStatus ?>">
                                                <button class="toggle-btn captain-status-action captain-status-<?= strtolower($nextStatus) ?>" type="submit"><?= $nextStatus ?></button>
                                            </form>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <section class="team-goal-leaderboard" aria-label="Top contributors">
                                    <div class="team-goal-leaderboard-heading">
                                        <h4>Top contributors</h4>
                                        <span><?= count($goal['Leaderboard']) ?> <?= count($goal['Leaderboard']) === 1 ? 'member' : 'members' ?></span>
                                    </div>
                                    <?php if (empty($goal['Leaderboard'])): ?>
                                        <p class="team-goal-leaderboard-empty">Be the first to contribute to this team challenge!</p>
                                    <?php else: ?>
                                        <ol class="team-goal-leaderboard-list">
                                            <?php foreach ($topContributors as $rankIndex => $contributor): ?>
                                                <?php $isCurrentUser = (int)$contributor['User_ID'] === $userId; ?>
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
                                        <?php if (!empty($remainingContributors)): ?>
                                            <details class="team-goal-leaderboard-more">
                                                <summary>View all <?= count($goal['Leaderboard']) ?> contributors</summary>
                                                <ol class="team-goal-leaderboard-list" start="6">
                                                    <?php foreach ($remainingContributors as $rankOffset => $contributor): ?>
                                                        <?php $rank = $rankOffset + 6; ?>
                                                        <?php $isCurrentUser = (int)$contributor['User_ID'] === $userId; ?>
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

                                <details class="captain-contribution-history captain-team-goal-history">
                                    <summary>
                                        <span class="material-symbols-outlined" aria-hidden="true">groups</span>
                                        <span>Contribution history</span>
                                        <span class="gl-badge badge-bg-primary txt-primary mono"><?= (int)$goal['Contribution_Count'] ?></span>
                                    </summary>
                                    <?php if (empty($goal['Contributions'])): ?>
                                        <p class="txt-muted captain-no-contributions">No contributions have been recorded.</p>
                                    <?php else: ?>
                                        <ul>
                                            <?php foreach ($goal['Contributions'] as $contribution): ?>
                                                <li>
                                                    <span class="captain-contributor-detail">
                                                        <strong><?= htmlspecialchars($contribution['Contributor_Name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                        <time datetime="<?= htmlspecialchars($contribution['Progress_Date'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('M j, Y', strtotime($contribution['Progress_Date'])), ENT_QUOTES, 'UTF-8') ?></time>
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
        <?php endif; ?>
    </main>
    <?php require_once '../../includes/bottombar/bottombar_captain.php'; ?>
    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>

<?php
// views/member/goals.php
require_once '../../controllers/member/goals_page_controller.php';
require_once '../../includes/headers/header_member.php';

if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatNumber = static function ($value) {
    if ($value === null || $value === '') {
        return '—';
    }
    return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
};
$metricValue = static function (array $values, $key) {
    return array_key_exists($key, $values) ? $values[$key] : null;
};
$formatPerformance = static function (array $goal, array $values) use ($formatNumber) {
    $parts = [];
    foreach ($goal['Metrics'] as $metric) {
        $key = $metric['Metric_Key'];
        if (!array_key_exists($key, $values)) {
            continue;
        }
        $parts[] = $metric['Metric_Label'] . ': '
            . $formatNumber($values[$key]) . ' ' . $metric['Unit'];
    }
    return $parts ? implode(' · ', $parts) : 'No performance recorded';
};

$activeGoals = 0;
$completedGoals = 0;
$overdueGoals = 0;
foreach ($goals as $goal) {
    if ($goal['Display_Status'] === 'Completed') {
        $completedGoals++;
    } elseif ($goal['Display_Status'] === 'Overdue') {
        $overdueGoals++;
    } else {
        $activeGoals++;
    }
}
$totalGoals = count($goals);
$successRate = $totalGoals > 0 ? round(($completedGoals / $totalGoals) * 100) : 0;
$goalColors = [
    'Weight Loss' => ['badge-bg-green txt-green', 'bg-green'],
    'Strength' => ['badge-bg-purple txt-purple', 'bg-purple'],
    'Endurance' => ['badge-bg-yellow txt-yellow', 'bg-yellow']
];
?>
<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        <section class="gl-page-header">
            <div class="gl-super-title txt-purple">PERSONAL PERFORMANCE</div>
            <h1 class="gl-main-title">Track Your Discipline.</h1>
            <p class="gl-intro-copy">Set a target, record each performance, and review how your training changes over time.</p>

            <?php if (is_string($_GET['success'] ?? null) && $_GET['success'] !== ''): ?>
                <div class="gl-message gl-message-success" role="status">
                    <?= $escape($_GET['success']) ?>
                </div>
            <?php endif; ?>
            <?php if (is_string($_GET['error'] ?? null) && $_GET['error'] !== ''): ?>
                <div class="gl-message gl-message-error" role="alert">
                    <?= $escape($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <div class="gl-stats-grid">
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">Active Goals</div>
                    <div class="gl-stat-val txt-purple"><?= $activeGoals ?></div>
                </div>
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">Goals Completed</div>
                    <div class="gl-stat-val txt-green"><?= $completedGoals ?></div>
                </div>
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">Success Rate</div>
                    <div class="gl-stat-val txt-yellow"><?= $successRate ?>%</div>
                </div>
            </div>
            <?php if ($overdueGoals > 0): ?>
                <p class="gl-overdue-summary"><?= $overdueGoals ?> overdue <?= $overdueGoals === 1 ? 'goal' : 'goals' ?></p>
            <?php endif; ?>
        </section>

        <section class="gl-cards-grid" aria-label="Personal goals">
            <?php foreach ($goals as $goal): ?>
                <?php
                $goalId = (int) $goal['Goal_ID'];
                $type = (string) $goal['Goal_Type'];
                [$badgeClass, $progressClass] = $goalColors[$type] ?? $goalColors['Strength'];
                $latest = $goal['Latest_Performance']['Values'] ?? [];
                $status = $goal['Display_Status'];
                $statusClass = strtolower($status);
                $endDate = !empty($goal['End_Date'])
                    ? date('d M Y', strtotime($goal['End_Date']))
                    : 'No deadline';
                $today = new DateTime('today');
                $deadline = !empty($goal['End_Date']) ? new DateTime($goal['End_Date']) : null;
                $deadlineText = $endDate;
                if ($deadline && $status !== 'Completed' && $deadline >= $today) {
                    $deadlineText .= ' · ' . $today->diff($deadline)->days . ' days remaining';
                }
                $detailPayload = [
                    'id' => $goalId,
                    'title' => $goal['Goal_Name'],
                    'activity' => $goal['Activity_Name'] ?? '',
                    'category' => $type,
                    'status' => $status,
                    'startDate' => $goal['Start_Date'] ?? '',
                    'endDate' => $goal['End_Date'] ?? '',
                    'metrics' => $goal['Metrics'],
                    'latest' => $goal['Latest_Performance'],
                    'history' => $goal['History'],
                    'best' => $goal['Best_Performance']
                ];
                $detailData = htmlspecialchars(
                    json_encode($detailPayload, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_INVALID_UTF8_SUBSTITUTE),
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
                <article class="gl-goal-card">
                    <div class="gl-card-top">
                        <span class="gl-badge <?= $escape($badgeClass) ?>"><?= $escape($type) ?></span>
                        <div class="gl-actions">
                            <button
                                type="button"
                                class="gl-edit-goal-btn"
                                aria-label="Edit <?= $escape($goal['Goal_Name']) ?>"
                                data-goal-name="<?= $escape($goal['Goal_Name']) ?>">
                                <span class="material-symbols-outlined" aria-hidden="true">edit_note</span>
                            </button>
                            <form
                                method="POST"
                                action="../../controllers/member/goal_action.php"
                                onsubmit="return confirm('Are you sure you want to delete this goal?');">
                                <input type="hidden" name="action" value="delete_goal">
                                <input type="hidden" name="csrf_token" value="<?= $escape($csrf_token) ?>">
                                <input type="hidden" name="goal_id" value="<?= $goalId ?>">
                                <button type="submit" class="delete-icon" aria-label="Delete <?= $escape($goal['Goal_Name']) ?>">
                                    <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <h2 class="gl-card-title"><?= $escape($goal['Goal_Name']) ?></h2>
                    <?php if (!empty($goal['Activity_Name'])): ?>
                        <p class="gl-activity-name"><?= $escape($goal['Activity_Name']) ?></p>
                    <?php endif; ?>

                    <div class="gl-performance-pair">
                        <div class="gl-performance-block">
                            <div class="gl-m-lbl">TARGET PERFORMANCE</div>
                            <div class="gl-performance-value">
                                <?= $escape($formatPerformance($goal, array_column($goal['Metrics'], 'Target_Value', 'Metric_Key'))) ?>
                            </div>
                        </div>
                        <div class="gl-performance-block">
                            <div class="gl-m-lbl">LATEST PERFORMANCE</div>
                            <div class="gl-performance-value">
                                <?= $escape($formatPerformance($goal, $latest)) ?>
                            </div>
                            <?php if (!empty($goal['Latest_Performance']['Performance_Date'])): ?>
                                <small class="gl-latest-date"><?= $escape(date('d M Y', strtotime($goal['Latest_Performance']['Performance_Date']))) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($goal['Metrics']): ?>
                        <div class="gl-metric-attainment" aria-label="Per-metric target status">
                            <?php foreach ($goal['Metrics'] as $metric): ?>
                                <?php
                                $key = $metric['Metric_Key'];
                                $currentMetric = $metricValue($latest, $key);
                                $targetMetric = $metric['Target_Value'];
                                $metricMet = $currentMetric !== null
                                    && $targetMetric !== null
                                    && (
                                        ($metric['Comparison_Direction'] === 'at_least' && $currentMetric >= $targetMetric)
                                        || ($metric['Comparison_Direction'] === 'at_most' && $currentMetric <= $targetMetric)
                                    );
                                ?>
                                <div class="gl-metric-attainment-row">
                                    <span><?= $escape($metric['Metric_Label']) ?></span>
                                    <span class="<?= $metricMet ? 'txt-green' : 'gl-label-muted' ?>">
                                        <?= $currentMetric === null ? 'Awaiting entry' : ($metricMet ? 'Target met' : 'Target pending') ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($type === 'Endurance' && isset($latest['distance'], $latest['duration']) && (float) $latest['distance'] > 0): ?>
                        <p class="gl-pace-line">
                            Pace: <?= $escape($formatNumber((float) $latest['duration'] / (float) $latest['distance'])) ?> min/km
                        </p>
                    <?php endif; ?>
                    <?php if ($type === 'Strength' && $goal['Best_Performance']): ?>
                        <p class="gl-record-line">
                            Personal best: <?= $escape($formatPerformance($goal, $goal['Best_Performance']['Values'])) ?>
                        </p>
                    <?php endif; ?>

                    <div class="gl-goal-status-row">
                        <span class="gl-status-pill gl-status-<?= $escape($statusClass) ?>"><?= $escape($status) ?></span>
                        <span class="gl-deadline"><?= $escape($deadlineText) ?></span>
                    </div>
                    <div class="gl-card-footer">
                        <button
                            type="button"
                            class="gl-view-btn txt-purple"
                            data-goal-action="update-progress"
                            data-goal-id="<?= $goalId ?>"
                            data-goal-name="<?= $escape($goal['Goal_Name']) ?>"
                            data-goal-metrics="<?= $detailData ?>">
                            Update Progress
                        </button>
                        <button
                            type="button"
                            class="gl-view-btn txt-purple"
                            data-goal-action="details"
                            data-goal-details="<?= $detailData ?>">
                            View Details
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
            <button type="button" class="gl-add-card" onclick="openCreateGoalModal()">
                <span class="gl-add-icon">
                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                </span>
                <strong class="txt-white">New Goal</strong>
                <span>Set your next milestone</span>
            </button>
        </section>
    </main>
    <?php
    if ($is_captain) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    require_once '../../includes/footers/footer_common.php';
    ?>
</div>

<div class="wk-modal-overlay" id="edit-goal-modal">
    <div class="wk-modal-box gl-edit-modal shadow-2xl">
        <div class="gl-modal-top">
            <h2 class="txt-white font-bold text-xl">Edit Goal</h2>
            <button type="button" class="gl-close-btn" onclick="closeModals()" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="wk-modal-body gl-modal-pad">
            <div class="gl-form-group">
                <label for="edit-goal-name">GOAL NAME</label>
                <input class="gl-input-dark" id="edit-goal-name" type="text">
            </div>
            <p class="gl-label-muted">Goal editing is not available yet.</p>
            <button type="button" class="gl-btn-outline gl-button-full" onclick="closeModals()">Close</button>
        </div>
    </div>
</div>

<div class="wk-modal-overlay" id="create-goal-modal" role="dialog" aria-modal="true" aria-labelledby="create-goal-title">
    <div class="wk-modal-box gl-edit-modal shadow-2xl">
        <div class="gl-modal-top">
            <div class="gl-modal-title-flex">
                <div class="gl-icon-square bg-purple-dim">
                    <span class="material-symbols-outlined txt-purple">add_task</span>
                </div>
                <h2 id="create-goal-title" class="txt-white font-bold text-xl">Create Goal</h2>
            </div>
            <button type="button" class="gl-close-btn" onclick="closeModals()" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="../../controllers/member/goal_action.php" id="create-goal-form">
            <input type="hidden" name="action" value="create_goal">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrf_token) ?>">
            <div class="wk-modal-body custom-scrollbar gl-modal-pad">
                <div class="gl-form-group">
                    <label for="create-goal-name">GOAL NAME</label>
                    <input class="gl-input-dark" id="create-goal-name" type="text" name="goal_name" maxlength="150" placeholder="e.g. Stronger bench press" required>
                </div>
                <div class="gl-form-group">
                    <label for="create-goal-type">CATEGORY</label>
                    <div class="gl-select-wrapper">
                        <select class="gl-input-dark gl-select" id="create-goal-type" name="goal_type" required>
                            <option value="">Select category</option>
                            <option value="Weight Loss">Weight</option>
                            <option value="Strength">Strength</option>
                            <option value="Endurance">Endurance</option>
                        </select>
                        <span class="material-symbols-outlined gl-select-arrow">expand_more</span>
                    </div>
                </div>
                <div class="gl-form-group gl-category-fields" data-category="Strength" hidden>
                    <label for="create-strength-activity">EXERCISE</label>
                    <input class="gl-input-dark" id="create-strength-activity" type="text" name="activity_name" data-category-input maxlength="150" placeholder="e.g. Bench Press" disabled>
                </div>
                <div class="gl-form-group gl-category-fields" data-category="Endurance" hidden>
                    <label for="create-endurance-activity">ACTIVITY</label>
                    <input class="gl-input-dark" id="create-endurance-activity" type="text" name="activity_name" data-category-input maxlength="150" placeholder="e.g. Run" disabled>
                </div>

                <div class="gl-category-fields" data-category="Weight Loss" hidden>
                    <div class="gl-form-group">
                        <label for="create-start-value">STARTING WEIGHT (KG)</label>
                        <div class="gl-number-unit">
                            <input class="gl-input-dark" id="create-start-value" type="number" name="start_value" min="0.01" max="99999999.99" step="0.01" placeholder="e.g. 85" data-category-input disabled>
                            <span aria-hidden="true">kg</span>
                        </div>
                    </div>
                    <div class="gl-form-group">
                        <label for="create-weight-target">TARGET WEIGHT (KG)</label>
                        <div class="gl-number-unit">
                            <input class="gl-input-dark" id="create-weight-target" type="number" name="weight_target_weight" min="0.01" max="99999999.99" step="0.01" placeholder="e.g. 78" data-category-input disabled>
                            <span aria-hidden="true">kg</span>
                        </div>
                    </div>
                </div>

                <div class="gl-category-fields" data-category="Strength" hidden>
                    <div class="gl-form-group">
                        <label for="create-strength-weight">TARGET WEIGHT</label>
                        <div class="gl-number-unit">
                            <input class="gl-input-dark" id="create-strength-weight" type="number" name="strength_target_weight" min="0.01" max="99999999.99" step="0.01" placeholder="e.g. 100" data-category-input disabled>
                            <span aria-hidden="true">kg</span>
                        </div>
                    </div>
                    <div class="gl-form-group">
                        <label for="create-strength-repetitions">TARGET REPETITIONS</label>
                        <div class="gl-number-unit">
                            <input class="gl-input-dark" id="create-strength-repetitions" type="number" name="target_repetitions" min="1" max="99999999" step="1" placeholder="e.g. 8" data-category-input disabled>
                            <span aria-hidden="true">reps</span>
                        </div>
                    </div>
                </div>

                <div class="gl-category-fields" data-category="Endurance" hidden>
                    <div class="gl-form-group">
                        <label for="create-endurance-distance">TARGET DISTANCE</label>
                        <div class="gl-number-unit">
                            <input class="gl-input-dark" id="create-endurance-distance" type="number" name="target_distance" min="0.01" max="99999999.99" step="0.01" placeholder="e.g. 5" data-category-input disabled>
                            <span aria-hidden="true">km</span>
                        </div>
                    </div>
                    <div class="gl-form-group">
                        <label for="create-endurance-duration">TARGET DURATION</label>
                        <div class="gl-number-unit">
                            <input class="gl-input-dark" id="create-endurance-duration" type="number" name="target_duration" min="0.01" max="99999999.99" step="0.01" placeholder="e.g. 25" data-category-input disabled>
                            <span aria-hidden="true">min</span>
                        </div>
                    </div>
                </div>

                <div class="gl-form-group">
                    <label for="create-target-date">TARGET DATE</label>
                    <input class="gl-input-dark" type="date" id="create-target-date" name="end_date" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="gl-form-actions">
                    <button type="submit" class="gl-btn-gradient gl-button-full">
                        <span class="material-symbols-outlined gl-btn-icon-pad">add_circle</span>
                        Create Goal
                    </button>
                    <button type="button" class="gl-btn-outline gl-button-full" onclick="closeModals()">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="wk-modal-overlay" id="update-progress-modal" role="dialog" aria-modal="true" aria-labelledby="progress-modal-title">
    <div class="wk-modal-box gl-edit-modal shadow-2xl">
        <div class="gl-modal-top">
            <div class="gl-modal-title-flex">
                <div class="gl-icon-square bg-purple-dim">
                    <span class="material-symbols-outlined txt-purple">monitoring</span>
                </div>
                <h2 id="progress-modal-title" class="txt-white font-bold text-xl">Update Progress</h2>
            </div>
            <button type="button" class="gl-close-btn" onclick="closeModals()" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="../../controllers/member/goal_action.php" id="update-progress-form">
            <input type="hidden" name="action" value="update_progress">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrf_token) ?>">
            <input type="hidden" name="goal_id" id="progress-goal-id">
            <div class="wk-modal-body custom-scrollbar gl-modal-pad">
                <div class="gl-form-group">
                    <label for="progress-goal-name">GOAL</label>
                    <input class="gl-input-dark" type="text" id="progress-goal-name" readonly>
                </div>
                <div id="progress-metric-fields"></div>
                <div class="gl-form-group">
                    <label for="progress-update-date">PERFORMANCE DATE</label>
                    <input class="gl-input-dark" type="date" id="progress-update-date" name="update_date" max="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="gl-form-actions">
                    <button type="submit" class="gl-btn-gradient gl-button-full">
                        <span class="material-symbols-outlined gl-btn-icon-pad">save</span>
                        Save Progress
                    </button>
                    <button type="button" class="gl-btn-outline gl-button-full" onclick="closeModals()">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="wk-modal-overlay" id="goal-details-modal" role="dialog" aria-modal="true" aria-labelledby="details-modal-title">
    <div class="wk-modal-box gl-details-modal shadow-2xl">
        <div class="gl-modal-top gl-modal-top-sm">
            <div class="gl-modal-title-flex">
                <span class="material-symbols-outlined txt-purple" aria-hidden="true">flag</span>
                <h2 id="details-modal-title" class="txt-white font-bold text-base">Goal Details</h2>
            </div>
            <button type="button" class="gl-close-btn" onclick="closeModals()" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="wk-modal-body custom-scrollbar gl-modal-pad-sm">
            <div class="gl-details-heading">
                <span class="gl-badge badge-bg-purple txt-purple gl-badge-sm" id="detail-category-badge">GOAL</span>
                <h3 class="txt-white font-bold text-xl" id="detail-title">Goal</h3>
                <p class="gl-label-muted" id="detail-activity"></p>
                <span class="gl-status-pill" id="detail-status"></span>
            </div>
            <div class="gl-details-performance-grid">
                <section class="gl-bento-card gl-bento-pad">
                    <h3 class="gl-progress-label">TARGET PERFORMANCE</h3>
                    <div id="detail-target-performance" class="gl-performance-value"></div>
                    <small id="detail-target-pace" class="gl-latest-date"></small>
                </section>
                <section class="gl-bento-card gl-bento-pad">
                    <h3 class="gl-progress-label">LATEST PERFORMANCE</h3>
                    <div id="detail-latest-performance" class="gl-performance-value"></div>
                    <small id="detail-latest-date" class="gl-latest-date"></small>
                    <small id="detail-latest-pace" class="gl-latest-date"></small>
                </section>
            </div>
            <p id="detail-baseline" class="gl-label-muted"></p>
            <section id="detail-best-section" class="gl-bento-card gl-bento-pad" hidden>
                <h3 class="gl-progress-label">PERSONAL BEST</h3>
                <div id="detail-best-performance" class="gl-performance-value"></div>
                <small id="detail-best-date" class="gl-latest-date"></small>
            </section>
            <section class="gl-history-section">
                <h3 class="txt-white font-bold">Performance History</h3>
                <div id="detail-chart-list" class="gl-chart-list"></div>
                <div class="gl-history-table-wrap" id="detail-history-wrap">
                    <table class="gl-history-table">
                        <thead id="detail-history-head"></thead>
                        <tbody id="detail-history-body"></tbody>
                    </table>
                </div>
                <p id="detail-history-empty" class="gl-history-empty" hidden>No performance history has been recorded yet.</p>
            </section>
        </div>
    </div>
</div>

<?php
// views/member/goals.php
// Include Page Controller ONLY
require_once '../../controllers/member/goals_page_controller.php';
require_once '../../includes/headers/header_member.php';
// Load sidebar dynamically
if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
// ---------------------------------------------------------
// Calculate statistics
// ---------------------------------------------------------
$activeGoals = 0;
$completedGoals = 0;
foreach ($goals as $goal) {
    $status = strtolower(trim($goal['Status'] ?? ''));
    if ($status === 'completed') {
        $completedGoals++;
    } else {
        $activeGoals++;
    }
}
$totalGoals = count($goals);
$successRate = $totalGoals > 0
    ? round(($completedGoals / $totalGoals) * 100)
    : 0;
?>
<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        <!-- =================================================
             HEADER
             ================================================= -->
        <section class="gl-page-header">
            <div class="gl-super-title txt-purple">
                PERSONAL PERFORMANCE
            </div>
            <h3 class="gl-main-title">
                Track Your Discipline.
            </h3>
            <!-- Success Message -->
            <?php if (!empty($_GET['success'])): ?>
                <div class="gl-bento-card mb-3">
                    <span class="txt-green">
                        <?= htmlspecialchars($_GET['success']) ?>
                    </span>
                </div>
            <?php endif; ?>
            <!-- Error Message -->
            <?php if (!empty($_GET['error'])): ?>
                <div class="gl-bento-card mb-3">
                    <span class="txt-yellow">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </span>
                </div>
            <?php endif; ?>
            <!-- Stats -->
            <div class="gl-stats-grid">
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">
                        Active Goals
                    </div>
                    <div class="gl-stat-val txt-purple">
                        <?= $activeGoals ?>
                    </div>
                </div>
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">
                        Goals Completed
                    </div>
                    <div class="gl-stat-val txt-green">
                        <?= $completedGoals ?>
                    </div>
                </div>
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">
                        Success Rate
                    </div>
                    <div class="gl-stat-val txt-yellow">
                        <?= $successRate ?>%
                    </div>
                </div>
            </div>
        </section>
        <!-- =================================================
             GOAL CARDS
             ================================================= -->
        <div class="gl-cards-grid">
            <?php foreach ($goals as $goal): ?>
                <?php
                // ---------------------------------------------
                // Basic values
                // ---------------------------------------------
                $goalId =
                    (int) $goal['Goal_ID'];
                $goalName =
                    $goal['Goal_Name'] ?? 'Goal';
                $goalType =
                    $goal['Goal_Type'] ?? 'Goal';
                $status =
                    $goal['Status'] ?? 'Active';
                $unit =
                    $goal['Unit'] ?? '';
                $target =
                    $goal['Target'] !== null
                    ? (float) $goal['Target']
                    : 0;
                $current =
                    $goal['Current_Value'] !== null
                    ? (float) $goal['Current_Value']
                    : 0;
                $startValue = isset($goal['Start_Value']) && $goal['Start_Value'] !== null ? (float) $goal['Start_Value'] : 0;
                // ---------------------------------------------
                // Badge colour
                // ---------------------------------------------
                $goalTypeLower =
                    strtolower($goalType);
                $badgeClass =
                    'badge-bg-purple txt-purple';
                $progressClass =
                    'bg-purple';
                if (
                    strpos(
                        $goalTypeLower,
                        'weight'
                    ) !== false
                ) {
                    $badgeClass =
                        'badge-bg-green txt-green';
                    $progressClass =
                        'bg-green';
                } elseif (
                    strpos(
                        $goalTypeLower,
                        'endurance'
                    ) !== false
                ) {
                    $badgeClass =
                        'badge-bg-yellow txt-yellow';
                    $progressClass =
                        'bg-yellow';
                }
                // ---------------------------------------------
                // Progress
                // ---------------------------------------------
                $progress = 0;
                if (strpos($goalTypeLower, 'weight') !== false) {
                    if ($startValue > $target) {
                        $progress = (($startValue - $current) / ($startValue - $target)) * 100;
                    }
                } elseif ($target > 0) {
                    $progress = ($current / $target) * 100;
                }
                $progress = round(min(max($progress, 0), 100));
                // ---------------------------------------------
                // Dates
                // ---------------------------------------------
                $startDate =
                    !empty($goal['Start_Date'])
                    ? date(
                        'd M Y',
                        strtotime($goal['Start_Date'])
                    )
                    : '-';
                $endDate =
                    !empty($goal['End_Date'])
                    ? date(
                        'd M Y',
                        strtotime($goal['End_Date'])
                    )
                    : '-';
                // ---------------------------------------------
                // Remaining time
                // ---------------------------------------------
                $timeText =
                    'No Target Date';
                if (!empty($goal['End_Date'])) {
                    $today =
                        new DateTime('today');
                    $end =
                        new DateTime(
                            $goal['End_Date']
                        );
                    if ($end >= $today) {
                        $daysRemaining =
                            $today->diff($end)->days;
                        $timeText =
                            $daysRemaining .
                            ' Days Remaining';
                    } else {
                        $timeText =
                            'Target Date Passed';
                    }
                }
                // Display values
                $targetDisplay =
                    number_format($target, 2);
                $currentDisplay =
                    number_format($current, 2);
                ?>
                <!-- =================================================
                     DYNAMIC GOAL CARD
                     ================================================= -->
                <div class="gl-goal-card">
                    <div class="gl-card-top">
                        <span
                            class="gl-badge <?= htmlspecialchars($badgeClass) ?>">
                            <?= htmlspecialchars($goalType) ?>
                        </span>
                        <div class="gl-actions">
                            <!-- Edit will be connected next -->
                            <button
                                type="button"
                                onclick="openEditGoalModal(
                                    '<?= htmlspecialchars(
                                        $goalName,
                                        ENT_QUOTES
                                    ) ?>'
                                )">
                                <span class="material-symbols-outlined">
                                    edit_note
                                </span>
                            </button>
                            <!-- Delete will be connected later -->
                            <form
                                method="POST"
                                action="../../controllers/member/goal_action.php"
                                onsubmit="return confirm('Are you sure you want to delete this goal?');"
                                style="display: inline;">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="delete_goal">

                                <input
                                    type="hidden"
                                    name="goal_id"
                                    value="<?= $goalId ?>">

                                <button
                                    type="submit"
                                    class="delete-icon">

                                    <span class="material-symbols-outlined">
                                        delete
                                    </span>

                                </button>

                            </form>
                        </div>
                    </div>
                    <!-- Goal Name -->
                    <h4 class="gl-card-title">
                        <?= htmlspecialchars($goalName) ?>
                    </h4>
                    <!-- Metrics -->
                    <div class="gl-metrics">
                        <div>
                            <div class="gl-m-lbl">
                                Target Metric
                            </div>
                            <div class="gl-m-val">
                                <?= htmlspecialchars($targetDisplay) ?>
                                <?= htmlspecialchars($unit) ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="gl-m-lbl">
                                Current
                            </div>
                            <div class="gl-m-val">
                                <?= htmlspecialchars($currentDisplay) ?>
                                <?= htmlspecialchars($unit) ?>
                            </div>
                        </div>
                    </div>
                    <!-- Progress -->
                    <div class="gl-progress-bg">
                        <div
                            class="gl-progress-fill <?= $progressClass ?>"
                            style="width: <?= $progress ?>%;">
                        </div>
                    </div>
                    <div class="gl-progress-lbls">
                        <span class="txt-white">
                            <?= $progress ?>% Progress
                        </span>
                        <span>
                            <?= htmlspecialchars($endDate) ?>
                        </span>
                    </div>
                    <!-- Footer -->
                    <div class="gl-card-footer">
                        <div class="gl-plan">
                            <span class="material-symbols-outlined">
                                flag
                            </span>
                            <div class="gl-plan-text txt-white">
                                <?= htmlspecialchars($status) ?>
                                <br>
                                Since <?= htmlspecialchars($startDate) ?>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="gl-view-btn txt-purple"
                            onclick="openUpdateProgressModal(
                                <?= $goalId ?>,
                                '<?= htmlspecialchars($goalName, ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($currentDisplay, ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($unit, ENT_QUOTES) ?>'
                            )">
                            Update
                            <br>
                            Progress
                        </button>
                        <button
                            type="button"
                            class="gl-view-btn txt-purple"
                            onclick="openGoalDetailsModal(
                                '<?= htmlspecialchars(
                                    $goalName,
                                    ENT_QUOTES
                                ) ?>',
                                <?= $progress ?>,
                                '<?= htmlspecialchars(
                                    $targetDisplay,
                                    ENT_QUOTES
                                ) ?>',
                                '<?= htmlspecialchars(
                                    $currentDisplay,
                                    ENT_QUOTES
                                ) ?>',
                                '<?= htmlspecialchars(
                                    $unit,
                                    ENT_QUOTES
                                ) ?>',
                                '<?= htmlspecialchars(
                                    $badgeClass,
                                    ENT_QUOTES
                                ) ?>',
                                '<?= htmlspecialchars(
                                    strtoupper($goalType),
                                    ENT_QUOTES
                                ) ?>',
                                '<?= htmlspecialchars(
                                    $timeText,
                                    ENT_QUOTES
                                ) ?>'
                            )">
                            View
                            <br>
                            Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
            <!-- =================================================
                 ADD NEW GOAL CARD
                 ================================================= -->
            <button
                type="button"
                class="gl-add-card"
                onclick="openCreateGoalModal()">
                <div class="gl-add-icon">
                    <span class="material-symbols-outlined">
                        add
                    </span>
                </div>
                <h4 class="txt-white">
                    New Goal
                </h4>
                <p>
                    Set your next milestone
                </p>
            </button>
        </div>
    </main>
    <?php
    if ($is_captain) {
        require_once
            '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once
            '../../includes/bottombar/bottombar_member.php';
    }
    ?>
    <?php
    require_once
        '../../includes/footers/footer_common.php';
    ?>
</div>
<!-- =========================================================
     EDIT GOAL MODAL
     ========================================================= -->
<div
    class="wk-modal-overlay"
    id="edit-goal-modal">
    <div
        class="wk-modal-box gl-edit-modal shadow-2xl">
        <div class="gl-modal-top">
            <div class="gl-modal-title-flex">
                <div
                    class="gl-icon-square bg-purple-dim">
                    <span
                        class="material-symbols-outlined txt-purple gl-icon-fill">
                        edit_square
                    </span>
                </div>
                <h3
                    class="txt-white font-bold text-xl">
                    Edit Goal
                </h3>
            </div>
            <button
                type="button"
                class="gl-close-btn"
                onclick="closeModals()">
                <span class="material-symbols-outlined">
                    close
                </span>
            </button>
        </div>
        <div
            class="wk-modal-body custom-scrollbar gl-modal-pad">
            <div class="gl-form-group">
                <label class="txt-purple">
                    GOAL NAME
                </label>
                <input
                    class="gl-input-dark"
                    id="edit-goal-name"
                    type="text">
            </div>
            <div class="gl-form-group">
                <label class="txt-green">
                    CATEGORY
                </label>
                <div class="gl-select-wrapper">
                    <select
                        class="gl-input-dark gl-select">
                        <option>
                            Weight Loss
                        </option>
                        <option>
                            Strength
                        </option>
                        <option>
                            Endurance
                        </option>
                    </select>
                    <span
                        class="material-symbols-outlined gl-select-arrow">
                        expand_more
                    </span>
                </div>
            </div>
            <div class="gl-form-group">
                <label class="txt-yellow">
                    TARGET DATE
                </label>
                <input
                    class="gl-input-dark"
                    type="date"
                    id="edit-target-date">
            </div>
            <div class="gl-form-group mb-6">
                <label class="gl-label-muted">
                    TARGET METRIC
                </label>
                <input
                    class="gl-input-dark"
                    type="number">
            </div>
            <!-- Edit DB connection comes next -->
            <button
                type="button"
                class="gl-btn-gradient w-full mb-3">
                <span
                    class="material-symbols-outlined gl-btn-icon-pad">
                    save
                </span>
                Save Changes
            </button>
            <button
                type="button"
                class="gl-btn-outline w-full"
                onclick="closeModals()">
                Cancel
            </button>
        </div>
    </div>
</div>
<!-- =========================================================
     GOAL DETAILS MODAL
     ========================================================= -->
<div
    class="wk-modal-overlay"
    id="goal-details-modal">
    <div
        class="wk-modal-box gl-details-modal shadow-2xl">
        <div
            class="gl-modal-top gl-modal-top-sm">
            <div class="gl-modal-title-flex">
                <span
                    class="material-symbols-outlined txt-purple">
                    flag
                </span>
                <h3
                    class="txt-white font-bold text-base">
                    Goal Details
                </h3>
            </div>
            <button
                type="button"
                class="gl-close-btn"
                onclick="closeModals()">
                <span class="material-symbols-outlined">
                    close
                </span>
            </button>
        </div>
        <div
            class="wk-modal-body custom-scrollbar gl-modal-pad-sm">
            <div class="mb-4">
                <span
                    class="gl-badge badge-bg-purple txt-purple uppercase gl-badge-sm"
                    id="detail-category-badge">
                    GOAL
                </span>
                <h2
                    class="txt-white font-bold text-xl mt-2 mb-2"
                    id="detail-title">
                    Goal
                </h2>
                <div
                    class="gl-time-badge gl-time-badge-sm">
                    <span
                        class="material-symbols-outlined"
                        style="font-size: 14px;">
                        schedule
                    </span>
                    <span id="detail-time">
                        Target Date
                    </span>
                </div>
            </div>
            <!-- Progress -->
            <div
                class="gl-bento-card gl-bento-pad">
                <div
                    class="flex justify-between items-center mb-2">
                    <span
                        class="uppercase gl-progress-label">
                        PROGRESS
                    </span>
                    <span
                        class="font-bold txt-purple text-base mono"
                        id="detail-percentage">
                        0%
                    </span>
                </div>
                <div
                    class="gl-progress-bg gl-progress-bar-custom">
                    <div
                        id="bar-progress-fill"
                        class="gl-progress-fill bg-purple gl-progress-bar-glow">
                    </div>
                </div>
            </div>
            <!-- Current -->
            <div
                class="gl-bento-card mb-3 relative gl-bento-pad-sm">
                <div
                    class="flex justify-between items-center mb-1">
                    <span
                        class="uppercase gl-progress-label">
                        CURRENT
                    </span>
                    <div
                        class="gl-icon-sm bg-purple-dim">
                        <span
                            class="material-symbols-outlined txt-purple"
                            style="font-size: 14px;">
                            radio_button_checked
                        </span>
                    </div>
                </div>
                <div
                    class="flex items-baseline gap-1">
                    <span
                        class="font-bold txt-white text-2xl mono"
                        id="detail-current-val">
                        0
                    </span>
                    <span
                        class="txt-white text-sm font-normal"
                        id="detail-current-unit">
                    </span>
                </div>
            </div>
            <!-- Target -->
            <div
                class="gl-bento-card relative gl-bento-pad-sm">
                <div
                    class="flex justify-between items-center mb-1">
                    <span
                        class="uppercase gl-progress-label">
                        TARGET GOAL
                    </span>
                    <div
                        class="gl-icon-sm bg-green-dim">
                        <span
                            class="material-symbols-outlined txt-green"
                            style="font-variation-settings: 'FILL' 1; font-size: 14px;">
                            stars
                        </span>
                    </div>
                </div>
                <div
                    class="flex items-baseline gap-1">
                    <span
                        class="font-bold txt-green text-2xl mono"
                        id="detail-target-val">
                        0
                    </span>
                    <span
                        class="txt-white text-sm font-normal"
                        id="detail-target-unit">
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- =========================================================
     UPDATE PROGRESS MODAL
     ========================================================= -->
<div class="wk-modal-overlay" id="update-progress-modal">
    <div class="wk-modal-box gl-edit-modal shadow-2xl">
        <div class="gl-modal-top">
            <div class="gl-modal-title-flex">
                <div class="gl-icon-square bg-purple-dim">
                    <span class="material-symbols-outlined txt-purple">monitoring</span>
                </div>
                <h3 class="txt-white font-bold text-xl">Update Progress</h3>
            </div>
            <button type="button" class="gl-close-btn" onclick="closeModals()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="../../controllers/member/goal_action.php">
            <input type="hidden" name="action" value="update_progress">
            <input type="hidden" name="goal_id" id="progress-goal-id">
            <div class="wk-modal-body custom-scrollbar gl-modal-pad">
                <div class="gl-form-group">
                    <label class="txt-purple">GOAL</label>
                    <input class="gl-input-dark" type="text" id="progress-goal-name" readonly>
                </div>
                <div class="gl-form-group">
                    <label class="gl-label-muted">CURRENT VALUE</label>
                    <div class="gl-select-wrapper">
                        <input class="gl-input-dark" type="number" id="progress-current-value" name="current_value" min="0" step="0.01" required>
                        <span id="progress-unit" class="gl-select-arrow"></span>
                    </div>
                </div>
                <div class="gl-form-group">
                    <label class="txt-yellow">UPDATE DATE</label>
                    <input class="gl-input-dark" type="date" id="progress-update-date" name="update_date" max="<?= date('Y-m-d') ?>" required>
                </div>
                <button type="submit" class="gl-btn-gradient w-full mb-3">
                    <span class="material-symbols-outlined gl-btn-icon-pad">save</span>
                    Save Progress
                </button>
                <button type="button" class="gl-btn-outline w-full" onclick="closeModals()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================
     CREATE GOAL MODAL
     ========================================================= -->
<div
    class="wk-modal-overlay"
    id="create-goal-modal">
    <div
        class="wk-modal-box gl-edit-modal shadow-2xl">
        <div class="gl-modal-top">
            <div class="gl-modal-title-flex">
                <div
                    class="gl-icon-square bg-purple-dim">
                    <span
                        class="material-symbols-outlined txt-purple">
                        add_task
                    </span>
                </div>
                <h3
                    class="txt-white font-bold text-xl">
                    Create Goal
                </h3>
            </div>
            <button
                type="button"
                class="gl-close-btn"
                onclick="closeModals()">
                <span class="material-symbols-outlined">
                    close
                </span>
            </button>
        </div>
        <!-- CREATE FORM -->
        <form
            method="POST"
            action="../../controllers/member/goal_action.php">
            <input
                type="hidden"
                name="action"
                value="create_goal">
            <div
                class="wk-modal-body custom-scrollbar gl-modal-pad">
                <!-- Goal Name -->
                <div class="gl-form-group">
                    <label class="txt-purple">
                        GOAL NAME
                    </label>
                    <input
                        class="gl-input-dark"
                        type="text"
                        name="goal_name"
                        placeholder="e.g. Bench Press PR"
                        maxlength="150"
                        required>
                </div>
                <!-- Category -->
                <div class="gl-form-group">
                    <label class="txt-green">
                        CATEGORY
                    </label>
                    <div class="gl-select-wrapper">
                        <select
                            class="gl-input-dark gl-select"
                            id="create-goal-type"
                            name="goal_type"
                            required>
                            <option value="">
                                Select Category
                            </option>
                            <option value="Weight Loss">
                                Weight Loss
                            </option>
                            <option value="Strength">
                                Strength
                            </option>
                            <option value="Endurance">
                                Endurance
                            </option>
                        </select>
                        <span
                            class="material-symbols-outlined gl-select-arrow">
                            expand_more
                        </span>
                    </div>
                </div>
                <!-- Starting Weight: shown only for Weight Loss -->
                <div class="gl-form-group" id="starting-weight-group" style="display: none;">
                    <label class="gl-label-muted">
                        STARTING WEIGHT
                    </label>
                    <input
                        class="gl-input-dark"
                        id="create-start-value"
                        type="number"
                        name="start_value"
                        min="0.01"
                        step="0.01"
                        placeholder="e.g. 77"
                        disabled>
                </div>

                <!-- Target Value -->
                <div class="gl-form-group">
                    <label class="gl-label-muted">
                        TARGET VALUE
                    </label>
                    <input
                        class="gl-input-dark"
                        type="number"
                        name="target"
                        min="0.01"
                        step="0.01"
                        placeholder="e.g. 70"
                        required>
                </div>
                <!-- Unit -->
                <div class="gl-form-group">
                    <label class="txt-green">
                        UNIT
                    </label>
                    <div class="gl-select-wrapper">
                        <select
                            class="gl-input-dark gl-select"
                            name="unit"
                            required>
                            <option value="">
                                Select Unit
                            </option>
                            <option value="kg">
                                kg
                            </option>
                            <option value="km">
                                km
                            </option>
                            <option value="min">
                                min
                            </option>
                            <option value="reps">
                                reps
                            </option>
                        </select>
                        <span
                            class="material-symbols-outlined gl-select-arrow">
                            expand_more
                        </span>
                    </div>
                </div>
                <!-- Target Date -->
                <div class="gl-form-group">
                    <label class="txt-yellow">
                        TARGET DATE
                    </label>

                    <input
                        class="gl-input-dark"
                        type="date"
                        id="create-target-date"
                        name="end_date"
                        min="<?= date('Y-m-d') ?>"
                        required>
                </div>
                <!-- Submit -->
                <button
                    type="submit"
                    class="gl-btn-gradient w-full mb-3">
                    <span
                        class="material-symbols-outlined gl-btn-icon-pad">
                        add_circle
                    </span>
                    Create Goal
                </button>
                <button
                    type="button"
                    class="gl-btn-outline w-full"
                    onclick="closeModals()">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

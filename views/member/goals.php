<?php
// views/member/goals.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Personal Fitness Goals | FitCampus";

$extra_js = [
    "member/dashboard.js",
    "member/goals.js"
];

require_once '../../includes/db_connection.php';

require_once '../../includes/headers/header_member.php';

// Check if the logged-in member is a captain based on login_process.php session
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;

// Load specific Headers and Sidebars dynamically
if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        
        <!-- Header & Stats Section -->
        <section class="gl-page-header">
            <div class="gl-super-title txt-purple">PERSONAL PERFORMANCE</div>
            <h3 class="gl-main-title">Track Your Discipline.</h3>
            
            <div class="gl-stats-grid">
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">Active Goals</div>
                    <div class="gl-stat-val txt-purple">04</div>
                </div>
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">Goals Completed</div>
                    <div class="gl-stat-val txt-green">12</div>
                </div>
                <div class="gl-stat-box">
                    <div class="gl-stat-lbl">Success Rate</div>
                    <div class="gl-stat-val txt-yellow">88%</div>
                </div>
            </div>
        </section>

        <!-- Goal Cards Grid -->
        <div class="gl-cards-grid">
            
            <!-- Goal Card 1 -->
            <div class="gl-goal-card">
                <div class="gl-card-top">
                    <span class="gl-badge badge-bg-green txt-green">Weight Loss</span>
                    <div class="gl-actions">
                        <button onclick="openEditGoalModal('Summer Cut 2024')"><span class="material-symbols-outlined">edit_note</span></button>
                        <button class="delete-icon"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
                <h4 class="gl-card-title">Summer Cut 2024</h4>
                
                <div class="gl-metrics">
                    <div>
                        <div class="gl-m-lbl">Target Metric</div>
                        <div class="gl-m-val">70.0 kg</div>
                    </div>
                    <div class="text-right">
                        <div class="gl-m-lbl">Current</div>
                        <div class="gl-m-val">74.5 kg</div>
                    </div>
                </div>
                
                <div class="gl-progress-bg">
                    <div class="gl-progress-fill bg-purple" style="width: 65%;"></div>
                </div>
                
                <div class="gl-progress-lbls">
                    <span class="txt-white">65% Progress</span>
                    <span>15 Aug 2024</span>
                </div>

                <div class="gl-card-footer">
                    <div class="gl-plan">
                        <span class="material-symbols-outlined">article</span>
                        <div class="gl-plan-text txt-white">Hypertrophy<br>Plan B</div>
                    </div>
                    <button class="gl-view-btn txt-purple" onclick="openGoalDetailsModal('Summer Cut 2024', 65, '70.0 kg', '74.5 kg', 'badge-bg-purple txt-purple', 'WEIGHT LOSS', '42 Days Remaining')">View<br>Details</button>
                </div>
            </div>

            <!-- Goal Card 2 -->
            <div class="gl-goal-card">
                <div class="gl-card-top">
                    <span class="gl-badge badge-bg-purple txt-purple">Strength</span>
                    <div class="gl-actions">
                        <button onclick="openEditGoalModal('Bench Press PR')"><span class="material-symbols-outlined">edit_note</span></button>
                        <button class="delete-icon"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
                <h4 class="gl-card-title">Bench Press PR</h4>
                
                <div class="gl-metrics">
                    <div>
                        <div class="gl-m-lbl">Target Metric</div>
                        <div class="gl-m-val">100.0 kg</div>
                    </div>
                    <div class="text-right">
                        <div class="gl-m-lbl">Current</div>
                        <div class="gl-m-val">92.5 kg</div>
                    </div>
                </div>
                
                <div class="gl-progress-bg">
                    <div class="gl-progress-fill bg-green" style="width: 85%;"></div>
                </div>
                
                <div class="gl-progress-lbls">
                    <span class="txt-white">85% Progress</span>
                    <span>01 Oct 2024</span>
                </div>

                <div class="gl-card-footer">
                    <div class="gl-plan">
                        <span class="material-symbols-outlined">article</span>
                        <div class="gl-plan-text txt-white">Powerlifting<br>101</div>
                    </div>
                    <button class="gl-view-btn txt-purple" onclick="openGoalDetailsModal('Bench Press PR', 85, '100.0 kg', '92.5 kg', 'badge-bg-purple txt-purple', 'STRENGTH', '20 Days Remaining')">View<br>Details</button>
                </div>
            </div>

            <!-- Goal Card 3 -->
            <div class="gl-goal-card">
                <div class="gl-card-top">
                    <span class="gl-badge badge-bg-yellow txt-yellow">Endurance</span>
                    <div class="gl-actions">
                        <button onclick="openEditGoalModal('5K Personal Best')"><span class="material-symbols-outlined">edit_note</span></button>
                        <button class="delete-icon"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
                <h4 class="gl-card-title">5K Personal Best</h4>
                
                <div class="gl-metrics">
                    <div>
                        <div class="gl-m-lbl">Target Metric</div>
                        <div class="gl-m-val">22:00 min</div>
                    </div>
                    <div class="text-right">
                        <div class="gl-m-lbl">Current</div>
                        <div class="gl-m-val">24:15 min</div>
                    </div>
                </div>
                
                <div class="gl-progress-bg">
                    <div class="gl-progress-fill bg-yellow" style="width: 40%;"></div>
                </div>
                
                <div class="gl-progress-lbls">
                    <span class="txt-white">40% Progress</span>
                    <span>20 Nov 2024</span>
                </div>

                <div class="gl-card-footer">
                    <div class="gl-plan">
                        <span class="material-symbols-outlined">article</span>
                        <div class="gl-plan-text txt-white">V02 Max<br>Booster</div>
                    </div>
                    <button class="gl-view-btn txt-purple" onclick="openGoalDetailsModal('5K Personal Best', 40, '22:00 min', '24:15 min', 'badge-bg-yellow txt-yellow', 'ENDURANCE', '60 Days Remaining')">View<br>Details</button>
                </div>
            </div>

            <!-- Empty State / Add New Card -->
            <button class="gl-add-card" onclick="openCreateGoalModal()">
                <div class="gl-add-icon">
                    <span class="material-symbols-outlined">add</span>
                </div>
                <h4 class="txt-white">New Goal</h4>
                <p>Set your next milestone</p>
            </button>

        </div>
    </main>

    <?php 
    if (isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    ?>

    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>

<!-- ==========================================
     MODALS
     ========================================== -->

<!-- 1. Edit Goal Modal -->
<div class="wk-modal-overlay" id="edit-goal-modal">
    <div class="wk-modal-box gl-edit-modal shadow-2xl">
        <div class="gl-modal-top">
            <div class="gl-modal-title-flex">
                <div class="gl-icon-square bg-purple-dim">
                    <span class="material-symbols-outlined txt-purple gl-icon-fill">edit_square</span>
                </div>
                <h3 class="txt-white font-bold text-xl">Edit Goal</h3>
            </div>
            <button class="gl-close-btn" onclick="closeModals()"><span class="material-symbols-outlined">close</span></button>
        </div>
        
        <div class="wk-modal-body custom-scrollbar gl-modal-pad">
            <div class="gl-form-group">
                <label class="txt-purple">GOAL NAME</label>
                <input class="gl-input-dark" id="edit-goal-name" type="text" value="Push Day Personal Best">
            </div>

            <div class="gl-form-group">
                <label class="txt-green">CATEGORY</label>
                <div class="gl-select-wrapper">
                    <select class="gl-input-dark gl-select">
                        <option>Weight Loss</option>
                        <option selected>Strength</option>
                        <option>Endurance</option>
                    </select>
                    <span class="material-symbols-outlined gl-select-arrow">expand_more</span>
                </div>
            </div>

            <div class="gl-form-group">
                <label class="txt-yellow">TARGET DATE</label>
                <div class="relative-input">
                    <input class="gl-input-dark" type="date" value="2023-12-31">
                </div>
            </div>

            <div class="gl-form-group mb-6">
                <label class="gl-label-muted">TARGET METRIC</label>
                <div class="gl-metric-input-group">
                    <input class="gl-input-dark flex-grow gl-input-split-left" type="number" value="120">
                    <div class="gl-unit-box">KG</div>
                </div>
                <div class="gl-progress-text mt-2">Current progress: 105 KG (87.5%)</div>
                <div class="gl-progress-bg mt-2">
                    <div class="gl-progress-fill bg-green" style="width: 87.5%;"></div>
                </div>
            </div>

            <button class="gl-btn-gradient w-full mb-3"><span class="material-symbols-outlined gl-btn-icon-pad gl-icon-fill">save</span> Save Changes</button>
            <button class="gl-btn-outline w-full" onclick="closeModals()">Cancel</button>
        </div>
    </div>
</div>

<!-- 2. Goal Details Modal -->
<div class="wk-modal-overlay" id="goal-details-modal">
    <div class="wk-modal-box gl-details-modal shadow-2xl">
        <div class="gl-modal-top gl-modal-top-sm">
            <div class="gl-modal-title-flex">
                <span class="material-symbols-outlined txt-purple">flag</span>
                <h3 class="txt-white font-bold text-base">Goal Details</h3>
            </div>
            <button class="gl-close-btn" onclick="closeModals()"><span class="material-symbols-outlined">close</span></button>
        </div>
        
        <div class="wk-modal-body custom-scrollbar gl-modal-pad-sm">
            
            <div class="mb-4">
                <span class="gl-badge badge-bg-purple txt-purple uppercase gl-badge-sm" id="detail-category-badge">WEIGHT LOSS</span>
                <h2 class="txt-white font-bold text-xl mt-2 mb-2" id="detail-title">Summer Cut 2024</h2>
                <div class="gl-time-badge gl-time-badge-sm">
                    <span class="material-symbols-outlined" style="font-size: 14px;">schedule</span>
                    <span id="detail-time">42 Days Remaining</span>
                </div>
            </div>

            <!-- Bar Chart Progress Bento Card -->
            <div class="gl-bento-card gl-bento-pad">
                <div class="flex justify-between items-center mb-2">
                    <span class="uppercase gl-progress-label">PROGRESS</span>
                    <span class="font-bold txt-purple text-base mono" id="detail-percentage">65%</span>
                </div>
                <!-- Linear Progress Bar -->
                <div class="gl-progress-bg gl-progress-bar-custom">
                    <div id="bar-progress-fill" class="gl-progress-fill bg-purple gl-progress-bar-glow"></div>
                </div>
            </div>

            <!-- Current Weight / Metric Bento Card -->
            <div class="gl-bento-card mb-3 relative gl-bento-pad-sm">
                <div class="flex justify-between items-center mb-1">
                    <span class="uppercase gl-progress-label">CURRENT WEIGHT</span>
                    <div class="gl-icon-sm bg-purple-dim"><span class="material-symbols-outlined txt-purple" style="font-size: 14px;">radio_button_checked</span></div>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-bold txt-white text-2xl mono" id="detail-current-val">74.5</span>
                    <span class="txt-white text-sm font-normal">kg</span>
                </div>
            </div>
            
            <!-- Target Goal Bento Card -->
            <div class="gl-bento-card relative gl-bento-pad-sm">
                <div class="flex justify-between items-center mb-1">
                    <span class="uppercase gl-progress-label">TARGET GOAL</span>
                    <div class="gl-icon-sm bg-green-dim"><span class="material-symbols-outlined txt-green" style="font-variation-settings: 'FILL' 1; font-size: 14px;">stars</span></div>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="font-bold txt-green text-2xl mono" id="detail-target-val">70.0</span>
                    <span class="txt-white text-sm font-normal">kg</span>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- 3. Create Goal Modal -->
<div class="wk-modal-overlay" id="create-goal-modal">
    <div class="wk-modal-box gl-edit-modal shadow-2xl">
        <div class="gl-modal-top">
            <div class="gl-modal-title-flex">
                <div class="gl-icon-square bg-purple-dim">
                    <span class="material-symbols-outlined txt-purple">add_task</span>
                </div>
                <h3 class="txt-white font-bold text-xl">Create Goal</h3>
            </div>
            <button class="gl-close-btn" onclick="closeModals()"><span class="material-symbols-outlined">close</span></button>
        </div>
        
        <div class="wk-modal-body custom-scrollbar gl-modal-pad">
            <div class="gl-form-group">
                <label class="txt-purple">GOAL NAME</label>
                <input class="gl-input-dark" type="text" placeholder="e.g. Run 10km">
            </div>

            <div class="gl-form-group">
                <label class="txt-green">CATEGORY</label>
                <div class="gl-select-wrapper">
                    <select class="gl-input-dark gl-select">
                        <option>Weight Loss</option>
                        <option>Strength</option>
                        <option>Endurance</option>
                    </select>
                    <span class="material-symbols-outlined gl-select-arrow">expand_more</span>
                </div>
            </div>

            <div class="gl-form-group">
                <label class="txt-yellow">TARGET DATE</label>
                <div class="relative-input">
                    <input class="gl-input-dark" type="date">
                </div>
            </div>

            <div class="gl-form-group mb-6">
                <label class="gl-label-muted">TARGET METRIC</label>
                <div class="gl-metric-input-group">
                    <input class="gl-input-dark flex-grow gl-input-split-left" type="number" placeholder="0">
                    <div class="gl-unit-box">UNITS</div>
                </div>
            </div>

            <button class="gl-btn-gradient w-full mb-3"><span class="material-symbols-outlined gl-btn-icon-pad">add_circle</span> Create Goal</button>
            <button class="gl-btn-outline w-full" onclick="closeModals()">Cancel</button>
        </div>
    </div>
</div>
<?php
// views/member/calories.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Nutrition & Activity Tracker | FitCampus";

$extra_js = [
    "member/dashboard.js",
    "member/calories.js"
];

require_once '../../includes/db_connection.php';
require_once '../../includes/headers/header_member.php';

$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;

if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        
        <section class="cal-card-box cal-spacing-lg">
            <div class="cal-header-flex">
                <div class="cal-title-wrap">
                    <span class="material-symbols-outlined">calendar_month</span>
                    <h2 class="cal-main-heading">History</h2>
                </div>
                <div class="cal-nav-group">
                    <button id="btnPrevMonth" type="button"><span class="material-symbols-outlined">chevron_left</span></button>
                    <span id="month-display" class="month-txt">October 2026</span>
                    <button id="btnNextMonth" type="button"><span class="material-symbols-outlined">chevron_right</span></button>
                </div>
            </div>

            <div class="cal-grid-labels">
                <span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span><span>SUN</span>
            </div>
            <div class="cal-grid-days" id="calendar-days-container"></div>

            <div class="cal-summary-strip">
                <div class="summary-left">
                    <div class="summary-icon"><span class="material-symbols-outlined">insert_chart</span></div>
                    <div class="summary-text-box">
                        <span class="sm-label">SELECTED DAY SUMMARY</span>
                        <span class="sm-date" id="summary-date">Today</span>
                    </div>
                </div>
                <div class="summary-right">
                    <div class="stat-block cal-align-right">
                        <div class="stat-val" id="summary-intake-val">0 <span class="unit">kcal</span></div>
                        <div class="stat-lbl lbl-green">Intake</div>
                    </div>
                    <div class="stat-block cal-align-right" style="margin-left: 24px;">
                        <div class="stat-val text-yellow" id="summary-burned-val">0 <span class="unit">kcal</span></div>
                        <div class="stat-lbl lbl-yellow">Burned</div>
                    </div>
                    <button type="button" id="btn-reset-date" class="reset-day-btn" title="Reset this day's logs">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
            </div>
        </section>

        <div class="cal-forms-grid cal-spacing-lg">
            
            <!-- Intake Form -->
            <section class="cal-card-box">
                <h3 class="form-title txt-purple cal-spacing-md">
                    <span class="material-symbols-outlined">add_circle</span> Log Nutrition (Intake)
                </h3>
                <form method="POST" action="../../backend/member/calorie_action.php?action=add_intake">
                    <input type="hidden" name="log_date" id="intake-hidden-date" value="<?php echo date('Y-m-d'); ?>">
                    
                    <div class="form-group-cal cal-spacing-md">
                        <label>Meal Category</label>
                        <select name="meal_category" class="cal-input-field" required>
                            <option value="Breakfast">Breakfast</option>
                            <option value="Lunch" selected>Lunch</option>
                            <option value="Dinner">Dinner</option>
                            <option value="Snack">Snack</option>
                        </select>
                    </div>

                    <div class="form-group-cal cal-spacing-md">
                        <label>Search Food Item</label>
                        <div class="relative-input">
                            <span class="material-symbols-outlined absolute-icon">search</span>
                            <input type="text" name="food_item" class="cal-input-field with-icon" placeholder="e.g. Grilled Chicken" required>
                        </div>
                    </div>

                    <div class="cal-grid-2 cal-spacing-md">
                        <div class="form-group-cal">
                            <label>Portion (g / ml)</label>
                            <input type="number" name="portion" class="cal-input-field" value="150" required>
                        </div>
                        <div class="form-group-cal">
                            <label>Calories (kcal)</label>
                            <input type="number" name="calories" class="cal-input-field" value="300" required>
                        </div>
                    </div>

                    <div class="cal-grid-3 cal-spacing-lg">
                        <div class="form-group-cal">
                            <label>Carbs (g)</label>
                            <input type="number" step="0.1" name="carbs" class="cal-input-field cal-align-center" value="30" required>
                        </div>
                        <div class="form-group-cal">
                            <label>Protein (g)</label>
                            <input type="number" step="0.1" name="protein" class="cal-input-field cal-align-center" value="15" required>
                        </div>
                        <div class="form-group-cal">
                            <label>Fat (g)</label>
                            <input type="number" step="0.1" name="fat" class="cal-input-field cal-align-center" value="5" required>
                        </div>
                    </div>

                    <button type="submit" class="cal-submit-btn btn-purple">
                        <span class="material-symbols-outlined">restaurant</span> Add to Log
                    </button>
                </form>
            </section>

            <!-- Burned Form -->
            <section class="cal-card-box">
                <h3 class="form-title txt-yellow cal-spacing-md">
                    <span class="material-symbols-outlined">directions_run</span> Log Activity (Burned)
                </h3>
                <form method="POST" action="../../backend/member/calorie_action.php?action=add_burned">
                    <input type="hidden" name="log_date" id="burned-hidden-date" value="<?php echo date('Y-m-d'); ?>">

                    <div class="form-group-cal cal-spacing-md">
                        <label>Activity Type</label>
                        <select name="activity_type" class="cal-input-field" required>
                            <option value="Running">Running</option>
                            <option value="Cycling">Cycling</option>
                            <option value="Weightlifting">Weightlifting</option>
                            <option value="Walking" selected>Walking</option>
                            <option value="Swimming">Swimming</option>
                        </select>
                    </div>

                    <div class="form-group-cal cal-spacing-md">
                        <label>Duration (minutes)</label>
                        <input type="number" name="duration" class="cal-input-field" value="45" required>
                    </div>

                    <div class="form-group-cal cal-spacing-lg" style="padding-bottom: 74px;">
                        <label>Calories Burned (Manual Override)</label>
                        <input type="number" name="calories_burned" class="cal-input-field" placeholder="Optional">
                    </div>

                    <button type="submit" class="cal-submit-btn btn-yellow">
                        <span class="material-symbols-outlined">local_fire_department</span> Add Activity
                    </button>
                </form>
            </section>

        </div>

        <section class="cal-card-box cal-spacing-lg cal-no-pad" id="consumption-section">
            <div class="cal-table-header">
                <h3>Today's Consumption</h3>
                <span class="material-symbols-outlined">filter_list</span>
            </div>
            <div class="cal-table-wrapper">
                <table class="cal-data-table">
                    <thead>
                        <tr>
                            <th>ITEM</th>
                            <th>TYPE</th>
                            <th>CALORIES</th>
                            <th>MACROS (C/P/F)</th>
                            <th class="cal-align-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="intake-table-body"></tbody>
                    <tfoot id="intake-table-foot"></tfoot>
                </table>
            </div>
        </section>

        <section class="cal-card-box cal-spacing-lg cal-no-pad" id="activity-section">
            <div class="cal-table-header">
                <h3>Today's Activity</h3>
            </div>
            <div class="cal-table-wrapper">
                <table class="cal-data-table">
                    <thead>
                        <tr>
                            <th>ACTIVITY</th>
                            <th>DURATION</th>
                            <th>CALORIES BURNED</th>
                            <th class="cal-align-right">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="burned-table-body"></tbody>
                    <tfoot id="burned-table-foot"></tfoot>
                </table>
            </div>
        </section>

    </main>

    <?php 
    if ($is_captain) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    ?>
    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>
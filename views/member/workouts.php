<?php
// views/member/workouts.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Workout Builder | FitCampus";

$extra_js = [
    "member/workouts.js"
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
        
        <section class="wk-hero-banner wk-margin-b-lg">
            <div class="wk-hero-content">
                <h2 class="wk-hero-title">Elevate Your Performance</h2>
                <p class="wk-hero-sub">Build custom routines and track every rep. Data-driven fitness for the academic athlete at University of Colombo.</p>
            </div>
        </section>

        <div id="personal-view-container" class="wk-view-container">
            
            <section class="wk-margin-b-xl">
                <div class="wk-section-head wk-margin-b-md">
                    <h3 class="monitor-title">Instructor Recommendations</h3>
                    <span class="wk-badge-verified">Verified Plans</span>
                </div>
                
                <div class="wk-scroll-track custom-scrollbar">
                    <div class="glass-card wk-card">
                        <div class="wk-card-icon-header bg-icon-primary">
                            <span class="material-symbols-outlined">fitness_center</span>
                        </div>
                        <div class="wk-card-body">
                            <h4 class="wk-card-title">Full Body Strength</h4>
                            <p class="wk-card-meta">3 exercises • 45 mins</p>
                            <button class="btn btn-glass btn-sm wk-push-bottom wk-btn-auto" onclick="showWorkoutDetails('Full Body Strength', 'Bench Press 3x10, Deadlift 3x8, Pull Ups 3x12', 'High-intensity metabolic conditioning designed by Coach Silva.')">
                                View
                            </button>
                        </div>
                    </div>

                    <div class="glass-card wk-card">
                        <div class="wk-card-icon-header bg-icon-secondary">
                            <span class="material-symbols-outlined">bolt</span>
                        </div>
                        <div class="wk-card-body">
                            <h4 class="wk-card-title">HIIT Cardio Blast</h4>
                            <p class="wk-card-meta">5 exercises • 20 mins</p>
                            <button class="btn btn-glass btn-sm wk-push-bottom wk-btn-auto" onclick="showWorkoutDetails('HIIT Cardio Blast', 'Burpees 4x15, Mountain Climbers 4x30', 'Cardio burst for stamina building.')">
                                View
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="wk-margin-b-lg">
                <div class="wk-section-head wk-margin-b-md">
                    <h3 class="monitor-title">My Customized Workout Plans</h3>
                    <button type="button" class="wk-add-btn" id="btn-open-create-modal" title="Create New">
                        <span class="material-symbols-outlined">add_box</span>
                    </button>
                </div>

                <div class="wk-scroll-track custom-scrollbar">
                    
                    <div class="glass-card wk-card">
                        <div class="wk-card-image-header" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDZhY69mjAh8bJxhlFfPSJXiHkQqFgqMhD2xB-LtFgS3PA_8PcX4rjEWNTfjFYsfjUs_WtfGKHs0X-wqm6n5ZyTiDs6jjLgO2VKZqroboGYOZMVx3x4VSgo3u7jBxrGXoJWwlUB3i1rlvxMP29VPd255kxDPpGXGWctmd5epJtaXeiOZLNvvRJGm9XziuHEGj7uJFfhX1btl9pHMBdv4wwl3cRGjHmGEuPXwAn7Y-VFK36waTyQIJCeFA');">
                            <div class="wk-image-overlay">
                                <span class="wk-tag tag-red">High Intensity</span>
                                <h4 class="wk-card-img-title">Leg Day Heavy</h4>
                            </div>
                        </div>
                        <div class="wk-card-body">
                            <ul class="wk-exercise-list">
                                <li>
                                    <div class="ex-name"><span class="ex-dot dot-primary"></span> Back Squats</div>
                                    <div class="ex-reps">5 x 5</div>
                                </li>
                                <li>
                                    <div class="ex-name"><span class="ex-dot dot-primary"></span> Romanian Deadlift</div>
                                    <div class="ex-reps">3 x 10</div>
                                </li>
                                <li>
                                    <div class="ex-name"><span class="ex-dot dot-primary"></span> Leg Press</div>
                                    <div class="ex-reps">4 x 12</div>
                                </li>
                            </ul>
                            <div class="wk-card-footer">
                                <span class="wk-footer-note">Last completed: 2 days ago</span>
                                <div class="wk-action-group">
                                    <button class="action-btn-icon edit-btn" onclick="openEditModal('Leg Day Heavy')"><span class="material-symbols-outlined">edit</span></button>
                                    <button class="action-btn-icon delete-btn"><span class="material-symbols-outlined">delete</span></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card wk-card">
                        <div class="wk-card-image-header" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCweaU0a1kO_bvuOP0HH2vavJnMPWXJ5yzgJrf_7_kCjLoSuOrFxrMLJLegap3T5Jo8AuRkSmpoEqgRp3wf9C_aP9t2YJqkVyC458Ley3yRbWeq-7w7cOVK0yCfY2Ftk_UWD7c7QC-q0sJJJrl2i1eIPNtqtIkrxkvfqVLuRrnyuo9zX4SUV0vni8DhXkWiu6A-PoLWNI-5b_CDebr_xUscaqcnKPv1iheSJTG5cjLUjTh0gQAdTVSK5w');">
                            <div class="wk-image-overlay">
                                <span class="wk-tag tag-green">Moderate</span>
                                <h4 class="wk-card-img-title">Push Day (A)</h4>
                            </div>
                        </div>
                        <div class="wk-card-body">
                            <ul class="wk-exercise-list">
                                <li>
                                    <div class="ex-name"><span class="ex-dot dot-secondary"></span> Flat Bench Press</div>
                                    <div class="ex-reps">4 x 8</div>
                                </li>
                                <li>
                                    <div class="ex-name"><span class="ex-dot dot-secondary"></span> DB Incline Press</div>
                                    <div class="ex-reps">3 x 12</div>
                                </li>
                                <li>
                                    <div class="ex-name"><span class="ex-dot dot-secondary"></span> Lateral Raises</div>
                                    <div class="ex-reps">4 x 15</div>
                                </li>
                            </ul>
                            <div class="wk-card-footer">
                                <span class="wk-footer-note">Last completed: 4 days ago</span>
                                <div class="wk-action-group">
                                    <button class="action-btn-icon edit-btn" onclick="openEditModal('Push Day (A)')"><span class="material-symbols-outlined">edit</span></button>
                                    <button class="action-btn-icon delete-btn"><span class="material-symbols-outlined">delete</span></button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

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

<!-- ==========================================
     MODALS
     ========================================== -->

<div class="wk-modal-overlay" id="workout-modal">
    <div class="wk-modal-box glass-card shadow-2xl">
        <div class="wk-modal-header">
            <div>
                <h3 class="wk-modal-title" id="modal-title">Workout Title</h3>
                <p class="wk-modal-sub" id="modal-desc">Description goes here.</p>
            </div>
            <button class="wk-modal-close" onclick="closeWorkoutModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body custom-scrollbar">
            <ul class="wk-modal-ex-list" id="modal-exercise-list"></ul>
        </div>
    </div>
</div>

<div class="wk-modal-overlay" id="create-routine-modal">
    <div class="wk-modal-box glass-card shadow-2xl" style="max-width: 600px;">
        <div class="wk-modal-header">
            <h3 class="wk-modal-title text-color-on-surface">Create New Routine</h3>
            <button class="wk-modal-close" id="btn-close-create-modal"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body custom-scrollbar">
            <div class="form-group-cal wk-margin-b-lg">
                <label>ROUTINE NAME</label>
                <input class="cal-input-field" placeholder="e.g., Full Body Blast" type="text">
            </div>
            
            <label class="wk-lbl-accent">ADD EXERCISES</label>
            <div class="wk-exercise-builder-box wk-margin-b-md">
                <div class="form-group-cal wk-margin-b-md">
                    <label>Exercise Name</label>
                    <input class="cal-input-field" placeholder="e.g., Bench Press" type="text">
                </div>
                <div class="cal-grid-3">
                    <div class="form-group-cal">
                        <label>Sets</label>
                        <input class="cal-input-field" placeholder="0" type="number">
                    </div>
                    <div class="form-group-cal">
                        <label>Reps</label>
                        <input class="cal-input-field" placeholder="0" type="number">
                    </div>
                    <div class="form-group-cal">
                        <label>Max Duration (min)</label>
                        <input class="cal-input-field" placeholder="0" type="number">
                    </div>
                </div>
            </div>
            <button type="button" class="wk-add-ex-row-btn">
                <span class="material-symbols-outlined">add</span> + Add Another Exercise
            </button>
        </div>
        <div class="wk-modal-footer">
            <button class="btn btn-glass" id="btn-cancel-create-modal">Cancel</button>
            <button class="btn btn-primary"><span class="material-symbols-outlined">save</span> Save Routine</button>
        </div>
    </div>
</div>

<div class="wk-modal-overlay" id="edit-routine-modal">
    <div class="wk-modal-box glass-card shadow-2xl" style="max-width: 600px;">
        <div class="wk-modal-header">
            <h3 class="wk-modal-title text-color-primary">Edit Routine</h3>
            <button class="wk-modal-close" id="btn-close-edit-modal"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body custom-scrollbar">
            <div class="form-group-cal wk-margin-b-lg">
                <label>ROUTINE NAME</label>
                <input class="cal-input-field" type="text" id="edit-routine-name" value="Upper Body Power">
            </div>
            
            <label class="wk-lbl-accent">EXERCISES</label>
            <div class="wk-exercise-builder-box wk-margin-b-md relative-box border-left-primary">
                <div class="form-group-cal wk-margin-b-md relative-input">
                    <label>Exercise Name</label>
                    <input class="cal-input-field" type="text" value="Barbell Bench Press">
                </div>
                <div class="cal-grid-3">
                    <div class="form-group-cal">
                        <label>Sets</label>
                        <input class="cal-input-field" type="number" value="4">
                    </div>
                    <div class="form-group-cal">
                        <label>Reps</label>
                        <input class="cal-input-field" type="number" value="12">
                    </div>
                    <div class="form-group-cal">
                        <label>Duration (min)</label>
                        <input class="cal-input-field" type="number" value="10">
                    </div>
                </div>
                <div class="wk-txt-right wk-margin-t-sm">
                    <button class="btn-text-danger"><span class="material-symbols-outlined">delete</span> Remove</button>
                </div>
            </div>
            <button type="button" class="wk-add-ex-row-btn">
                <span class="material-symbols-outlined">add</span> + Add Another Exercise
            </button>
        </div>
        <div class="wk-modal-footer">
            <button class="btn btn-glass" id="btn-cancel-edit-modal">Cancel</button>
            <button class="btn btn-primary"><span class="material-symbols-outlined">save</span> Update Routine</button>
        </div>
    </div>
</div>
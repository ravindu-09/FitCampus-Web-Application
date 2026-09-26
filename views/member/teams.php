<?php
// views/member/teams.php

// Include Page Controller ONLY (No Direct Database Connections)
require_once '../../controllers/member/teams_page_controller.php';

require_once '../../includes/headers/header_member.php';

// Load specific Headers and Sidebars dynamically[cite: 18]
if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        
        <!-- Team Header & Toggle -->
        <div class="tm-header-bar" id="team-toggle-container">
            <div class="tm-header-title">
                <span class="material-symbols-outlined text-color-secondary gl-btn-icon-pad" style="font-size: 32px;">groups</span>
                <h2 class="settings-main-title m-0" id="active-team-name">UOC Cricket</h2>
            </div>
            <button type="button" class="btn btn-secondary-action btn-sm m-0" id="team-toggle-btn">
                <span class="material-symbols-outlined">swap_horiz</span>
                <span>Change Team</span>
            </button>
        </div>

        <!-- TEAM WORKOUT FEED -->
        <div id="team-workout-feed" class="wk-view-container tm-section-pad">
            <div class="wk-section-head tm-section-head">
                <h3 class="monitor-title m-0">Team Workout Feed</h3>
                <span class="wk-badge-captain">Captain's Uploads</span>
            </div>

            <!-- JS will populate workouts here -->
            <div class="wk-scroll-track custom-scrollbar" id="team-workouts-container"></div>
        </div>

        <!-- TEAM GOALS -->
        <div id="team-goals-section" class="wk-view-container tm-section-pad">
            <div class="wk-section-head tm-section-head">
                <h3 class="monitor-title m-0">Team Goals</h3>
                <span class="gl-badge badge-bg-purple txt-purple mono uppercase gl-badge-sm">Active Milestone</span>
            </div>

            <!-- JS will populate goals here -->
            <div class="gl-cards-grid" id="team-goals-container"></div>
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

<!-- TEAM SELECTION MODAL -->
<div class="wk-modal-overlay" id="team-selection-modal">
    <div class="wk-modal-box glass-card shadow-2xl tm-modal-box">
        <div class="wk-modal-header">
            <h3 class="wk-modal-title text-color-on-surface">Your Teams</h3>
            <button class="wk-modal-close" id="btn-close-team-modal"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body">
            <div class="wk-team-list">
                <button class="wk-team-select-btn" onclick="switchToTeam('UOC Cricket')">
                    <div class="wk-team-icon bg-icon-secondary"><span class="material-symbols-outlined">sports_cricket</span></div>
                    <div class="text-left">
                        <p class="wk-team-name m-0">UOC Cricket</p>
                        <p class="wk-team-meta m-0">12 Active Members</p>
                    </div>
                </button>
                <button class="wk-team-select-btn" onclick="switchToTeam('UOC Athletics')">
                    <div class="wk-team-icon bg-icon-secondary"><span class="material-symbols-outlined">directions_run</span></div>
                    <div class="text-left">
                        <p class="wk-team-name m-0">UOC Athletics</p>
                        <p class="wk-team-meta m-0">8 Active Members</p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- TEAM GOAL DETAILS MODAL -->
<div class="wk-modal-overlay" id="team-goal-details-modal">
    <div class="wk-modal-box gl-details-modal shadow-2xl">
        <div class="gl-modal-top gl-modal-top-sm">
            <div class="gl-modal-title-flex">
                <span class="material-symbols-outlined txt-purple">flag</span>
                <h3 class="txt-white font-bold text-base m-0">Goal Details</h3>
            </div>
            <button class="gl-close-btn" onclick="closeTeamGoalModal()"><span class="material-symbols-outlined">close</span></button>
        </div>
        
        <div class="wk-modal-body custom-scrollbar gl-modal-pad-sm">
            
            <div class="tm-modal-head-pad">
                <span class="gl-badge badge-bg-purple txt-purple uppercase gl-badge-sm" id="team-detail-category-badge">TOURNAMENT PREP</span>
                <h2 class="txt-white font-bold text-xl mt-2 mb-2" id="team-detail-title">Inter-University Championship</h2>
                <div class="gl-time-badge gl-time-badge-sm">
                    <span class="material-symbols-outlined gl-btn-icon-pad">schedule</span>
                    <span id="team-detail-time">10 Days Remaining</span>
                </div>
            </div>

            <!-- Bar Chart Progress Bento Card -->
            <div class="gl-bento-card gl-bento-pad mb-3">
                <div class="tm-bento-flex mb-2">
                    <span class="uppercase gl-progress-label">PROGRESS</span>
                    <span class="font-bold txt-purple text-base mono" id="team-detail-percentage">85%</span>
                </div>
                <div class="gl-progress-bg gl-progress-bar-custom m-0">
                    <div id="team-bar-progress-fill" class="gl-progress-fill bg-purple gl-progress-bar-glow" style="width: 0%;"></div>
                </div>
            </div>

            <!-- Current Metric Bento Card -->
            <div class="gl-bento-card relative gl-bento-pad-sm mb-3">
                <div class="tm-bento-flex mb-1">
                    <span class="uppercase gl-progress-label">CURRENT VALUE</span>
                    <div class="gl-icon-sm bg-purple-dim"><span class="material-symbols-outlined txt-purple gl-btn-icon-pad">radio_button_checked</span></div>
                </div>
                <div class="tm-bento-baseline">
                    <span class="font-bold txt-white text-2xl mono" id="team-detail-current-val">85%</span>
                </div>
            </div>
            
            <!-- Target Goal Bento Card -->
            <div class="gl-bento-card relative gl-bento-pad-sm">
                <div class="tm-bento-flex mb-1">
                    <span class="uppercase gl-progress-label">TARGET GOAL</span>
                    <div class="gl-icon-sm bg-green-dim"><span class="material-symbols-outlined txt-green gl-icon-fill gl-btn-icon-pad">stars</span></div>
                </div>
                <div class="tm-bento-baseline">
                    <span class="font-bold txt-green text-2xl mono" id="team-detail-target-val">100% Attendance</span>
                </div>
            </div>

        </div>
    </div>
</div>
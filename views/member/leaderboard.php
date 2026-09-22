<?php
// views/member/leaderboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$page_title = "Leaderboard | FitCampus";

// JS file linked directly to leaderboard.js
$extra_js = [
    "member/dashboard.js",
    "member/leaderboard.js"
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

        <!-- Podium Section -->
        <section class="mb-12">
            <div class="ld-podium-wrap">
                
                <!-- 2nd Place -->
                <div class="ld-podium-item rank-2">
                    <div class="ld-avatar-container">
                        <img alt="Silver medalist" class="ld-avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD8w5bp4Z2GnX8wj81e6PeW24Tn-Llqssp5va8jvJ34zttEUgTdEXcn_b1y1GhjOVcwGLjpMhV0WV0cTyLYSBOOf3ZSG6r6jfKcnSIMBo4MTiNkPPqSXsC_QD6XknkgS9lGBud73nznyLvOgEVBYZeEqkAsoUIzJUi6jKdEP03x7Ygghh3e5E2QcM0Qt1g0BAV7ePLMzYjGss3pJb62AqlCVUHVKyDayDzWL_edG984IC6mXzFB4Owvng">
                        <div class="ld-badge">2</div>
                    </div>
                    <div class="ld-pillar">
                        <span class="ld-points">1,840</span>
                        <span class="ld-points-lbl">Points</span>
                    </div>
                    <p class="ld-name">Amara S.</p>
                </div>

                <!-- 1st Place -->
                <div class="ld-podium-item rank-1">
                    <div class="ld-avatar-container">
                        <span class="material-symbols-outlined ld-crown" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
                        <img alt="Gold medalist" class="ld-avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCt1C5lBZsCM9xQlC9LpU3kgQXt5FC8WgnIcYdUyVv2nct308oKQ3KhFboMGXmIe6ORqJHsV_0U77XbuqDdrgk2QwpPIINFuhNY7-C3n4z6kTIWL-TRgOi4iF8LNuUsVqzjvw7eBSty58iADF8GAgD7NBLdJXURoBHiM3R_fCIuYckRzwAdeQpkM1GXxxMWzVLhvJRthsHsR9C2db25ypsFQRaYD_bVNIKn2Yl8ETEwQmgSMGy1RQo0sA">
                        <div class="ld-badge">1</div>
                    </div>
                    <div class="ld-pillar">
                        <span class="ld-points">2,150</span>
                        <span class="ld-points-lbl">Points</span>
                    </div>
                    <p class="ld-name">Kasun Perera</p>
                </div>

                <!-- 3rd Place -->
                <div class="ld-podium-item rank-3">
                    <div class="ld-avatar-container">
                        <img alt="Bronze medalist" class="ld-avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD_bdXj_aWIe0Nr3giIggBtUG69X2J0BEB9DWcL7Edx35swIQjQYqnO3I6swB6jiGSc4f0ZiWFprDkxkQtnTu1CxPEKC7yLxtij3geeTkDItse9JZ-dm28kVSb0B5-p4tO6g-O-Dk1aXIjJv46_J-tweLseuhJyVf07AAq_ZYRu77DdqqYOWyB8BbPsNbn7-zNqs0BpuGAzfaMIs3L2i4vqbi8UrjyU0hA47jYT5CK9cDQEIHU7zr0lyQ">
                        <div class="ld-badge">3</div>
                    </div>
                    <div class="ld-pillar">
                        <span class="ld-points">1,620</span>
                        <span class="ld-points-lbl">Points</span>
                    </div>
                    <p class="ld-name">Nimasha K.</p>
                </div>

            </div>
        </section>

        <!-- Leaderboard Table & Tabs -->
        <div class="mb-12">
            <div class="ld-tabs" id="leaderboard-tabs">
                <button class="ld-tab active">This Month</button>
                <button class="ld-tab">All Time</button>
                <button class="ld-tab">Faculty Wise</button>
            </div>

            <div class="glass-card ld-table-wrapper custom-scrollbar">
                <table class="ld-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Athlete</th>
                            <th class="hide-mobile">Faculty</th>
                            <th>Points</th>
                            <th style="text-align: right;">Badges</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- User Row (Highlighted) -->
                        <tr class="highlight">
                            <td style="font-weight: bold;">12</td>
                            <td>
                                <div class="ld-user-cell">
                                    <div class="ld-user-avatar" style="background: rgba(223, 183, 255, 0.2);">
                                        <img alt="User profile" class="ld-avatar-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBLJWru2Er9vB6yY5RSfM3zB6KIYis5rVUbtEtKrz7pnsU2GSTsr5tsIoxIGayugVbDDI4nVCvIMIh27pwqEjvOgMk-2UcCrRsGg-ufX_bJmMyX65SOvi_MDXMYf3YvPQ1Etk3NJRkhkBpJyIq1MqwgopI6wZWg58XcbO7yeHws3Lv0OCO3NoDU_CIAlpAg6MUls7JJ2rD4A-s9CpHbx3jqQsUoQK8RRF2zpS-DvPXA6ZhzOryE_9M_tQ" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
                                    </div>
                                    <span style="font-weight: bold;">You (Academic Athlete)</span>
                                </div>
                            </td>
                            <td class="hide-mobile" style="color: var(--on-surface-variant);">Computing</td>
                            <td style="font-weight: bold; color: var(--primary);">1,245</td>
                            <td>
                                <div class="ld-badge-group">
                                    <span class="ld-mini-badge mb-sec">5D</span>
                                    <span class="ld-mini-badge mb-ter">EB</span>
                                </div>
                            </td>
                        </tr>

                        <!-- Sample Row -->
                        <tr>
                            <td style="color: var(--on-surface-variant);">4</td>
                            <td>
                                <div class="ld-user-cell">
                                    <div class="ld-user-avatar">SJ</div>
                                    <span>Sahan Jayaweera</span>
                                </div>
                            </td>
                            <td class="hide-mobile" style="color: var(--on-surface-variant);">Engineering</td>
                            <td style="font-weight: bold;">1,580</td>
                            <td>
                                <div class="ld-badge-group">
                                    <span class="ld-mini-badge mb-pri">M1</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="ld-table-footer">
                    <button class="ld-view-all-btn">View All Rankings</button>
                </div>
            </div>
        </div>

        <!-- Sidebar Widgets (Horizontal Layout) -->
        <div class="ld-widgets-grid">
            
            <!-- Achievement Rank Widget -->
            <div class="glass-card tier-track-card">
                <div class="tier-header">
                    <h3 class="settings-main-title m-0" style="font-size: 20px;">Achievement Rank</h3>
                    <span class="gl-badge badge-bg-green txt-green uppercase" style="font-size: 10px;">Silver Tier</span>
                </div>
                
                <div class="tier-current">
                    <div class="tier-icon">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">military_tech</span>
                    </div>
                    <div>
                        <p class="font-bold txt-green" style="font-size: 16px;">Silver Achiever</p>
                        <p class="mono text-xs" style="color: var(--on-surface-variant);">Current Status: 1,245 Points</p>
                    </div>
                </div>

                <div class="tier-steps">
                    <!-- Bronze (Past) -->
                    <div class="tier-step">
                        <div class="ts-icon ts-past"><span class="material-symbols-outlined">workspace_premium</span></div>
                        <span class="ts-label">Bronze</span>
                    </div>
                    <!-- Silver (Active) -->
                    <div class="tier-step">
                        <div class="ts-icon ts-active relative-box">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">workspace_premium</span>
                            <div class="ts-check"><span class="material-symbols-outlined" style="font-size: 10px;">check</span></div>
                        </div>
                        <span class="ts-label txt-green">Silver</span>
                    </div>
                    <!-- Gold (Future) -->
                    <div class="tier-step" style="opacity: 0.4;">
                        <div class="ts-icon ts-future"><span class="material-symbols-outlined">workspace_premium</span></div>
                        <span class="ts-label">Gold</span>
                    </div>
                    <!-- Legend (Future) -->
                    <div class="tier-step" style="opacity: 0.4;">
                        <div class="ts-icon ts-future"><span class="material-symbols-outlined">stars</span></div>
                        <span class="ts-label">Legend</span>
                    </div>
                </div>

                <div class="tier-progress-area mt-4 pt-4 border-t" style="border-color: rgba(255,255,255,0.05);">
                    <div class="flex justify-between mb-2 mono text-xs">
                        <span style="color: var(--on-surface-variant);">Next Tier: Gold</span>
                        <span class="txt-purple">255 pts to go</span>
                    </div>
                    <div class="gl-progress-bg" style="height: 6px;">
                        <div class="gl-progress-fill bg-purple" style="width: 82%;"></div>
                    </div>
                </div>
            </div>

            <!-- Current Standing Widget -->
            <div class="glass-card ld-standing-card">
                <div class="relative z-10">
                    <p class="mono text-xs uppercase mb-2" style="color: var(--on-surface-variant);">Current Standing</p>
                    <h4 class="ld-big-rank">#12</h4>
                    <div class="flex items-center gap-2 mt-4 txt-green font-bold text-sm">
                        <span class="material-symbols-outlined">trending_up</span>
                        <span>Up 3 spots since yesterday</span>
                    </div>
                </div>
                <span class="material-symbols-outlined ld-watermark">emoji_events</span>
            </div>

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
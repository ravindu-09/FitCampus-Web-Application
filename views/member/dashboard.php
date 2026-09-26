<?php
// views/member/dashboard.php
$page_title = "Member Dashboard | FitCampus";

// Script Driver via footer_common.php
$extra_js = "member/dashboard.js";

// 1. Include Page Controller ONLY (No Direct DB Connection)
require_once '../../controllers/member/dashboard_page_controller.php';

require_once '../../includes/headers/header_member.php';

// Check if the logged-in member is a captain based on session
$is_captain = isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1;

// Load specific Headers and Sidebars dynamically
if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<!-- Content Wrapper -->
<div class="member-content-wrapper">

    <!-- Main Content Area -->
    <main class="dashboard-main-container">
        
        <!-- Live Check-in Status Card -->
        <section class="glass-card checkin-status-card inner-glow-primary">
            <div class="checkin-flex">
                <div class="checkin-left-group">
                    <div class="status-column">
                        <span class="section-label-micro">Current Status</span>
                        <div class="live-indicator-wrapper">
                            <div class="pulse-circle">
                                <span class="pulse-bubble" style="<?php echo !$is_checked_in ? 'background-color: var(--outline); animation: none;' : ''; ?>"></span>
                                <span class="pulse-dot" style="<?php echo !$is_checked_in ? 'background-color: var(--outline);' : ''; ?>"></span>
                            </div>
                            <span class="status-value-text"><?php echo $is_checked_in ? 'Checked In' : 'Checked Out'; ?></span>
                        </div>
                    </div>
                    
                    <div class="divider-vertical"></div>
                    
                    <div class="status-column">
                        <span class="section-label-micro">Location</span>
                        <div class="location-value-text">
                            <span class="material-symbols-outlined text-color-secondary">location_on</span>
                            <span><?php echo htmlspecialchars($location_name); ?></span>
                        </div>
                    </div>
                </div>

                <div class="divider-vertical"></div>

                <div class="checkin-time-column">
                    <span class="section-label-micro">Check-in Time</span>
                    <div class="time-row">
                        <span class="material-symbols-outlined text-color-primary">schedule</span>
                        <span><?php echo htmlspecialchars($checkin_time); ?></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Live Facility Capacity Monitors (Dynamic from Controller) -->
        <section class="grid-monitors">
            <?php if (!empty($facilities)): ?>
                <?php foreach ($facilities as $fac): ?>
                    <div class="glass-card monitor-card <?php echo $fac['is_glow'] ? 'inner-glow-primary' : ''; ?>">
                        <div class="monitor-header">
                            <div>
                                <div class="monitor-live-tag">
                                    <span class="dot-pulse <?php echo $fac['color_class']; ?>"></span>
                                    <span class="section-label-micro text-color-<?php echo $fac['color_class']; ?>">Live Facility</span>
                                </div>
                                <h3 class="monitor-title"><?php echo htmlspecialchars($fac['name']); ?></h3>
                            </div>
                            <div class="monitor-capacity">
                                <div class="monitor-count text-color-<?php echo $fac['color_class']; ?>">
                                    <?php echo $fac['current']; ?><span class="monitor-count-sub">/<?php echo $fac['capacity']; ?></span>
                                </div>
                                <p class="section-label-micro">Capacity Reach</p>
                            </div>
                        </div>
                        <div>
                            <div class="ratio-row">
                                <span class="text-regular-sub">Load Ratio</span>
                                <div class="ratio-val-group">
                                    <span class="text-color-<?php echo $fac['color_class']; ?>"><?php echo $fac['percentage']; ?>% Filled</span>
                                </div>
                            </div>
                            <div class="gender-split-bar">
                                <div style="width: <?php echo $fac['percentage']; ?>%; background-color: var(--<?php echo $fac['color_class']; ?>); height: 100%;"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="glass-card monitor-card">
                    <p class="text-dimmed-sub">No facility capacity data configured in system yet.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Instructor Motivation & Announcements -->
        <section>
            <div class="section-head">
                <h3>Latest System Updates & Motivation</h3>
            </div>
            
            <div class="feed-cards-wrap">
                <?php if (!empty($announcements)): ?>
                    <?php foreach ($announcements as $announce): ?>
                        <article class="glass-card feed-article-card border-accent-primary">
                            <div class="article-body">
                                <span class="badge-tag">Announcement</span>
                                <h4 class="monitor-title" style="margin-top: 8px;"><?php echo htmlspecialchars($announce['Title']); ?></h4>
                                <p class="quote-box">
                                    "<?php echo nl2br(htmlspecialchars($announce['Description'])); ?>"
                                </p>
                                <div class="article-actions-bar">
                                    <span class="action-time-right"><?php echo date('M d, Y - h:i A', strtotime($announce['Publish_Date'])); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="glass-card feed-article-card border-accent-primary">
                        <div class="article-body">
                            <span class="badge-tag">Daily Motivation</span>
                            <h4 class="monitor-title" style="margin-top: 8px;">Consistency is Key</h4>
                            <p class="quote-box">
                                "It's not about being the best, it's about being better than you were yesterday. Focus on showing up, even on the days you don't feel like it."
                            </p>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </section>

        <!-- Rules & Regulations Carousel -->
        <section>
            <div class="section-head">
                <h3>Rules &amp; Regulations</h3>
                <div class="ratio-val-group">
                    <button id="rulesScrollLeft" class="carousel-btn glass-card" type="button" aria-label="Scroll left">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>
                    <button id="rulesScrollRight" class="carousel-btn glass-card" type="button" aria-label="Scroll right">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>
            </div>

            <div id="rulesCarousel" class="rules-carousel-wrapper scrollbar-hide">
                <?php if (!empty($rules)): ?>
                    <?php 
                    $borders = ['border-tertiary', 'border-secondary', 'border-primary'];
                    $text_colors = ['text-color-tertiary', 'text-color-secondary', 'text-color-primary'];
                    foreach ($rules as $i => $rule): 
                        $b_class = $borders[$i % 3];
                        $c_class = $text_colors[$i % 3];
                    ?>
                        <div class="glass-card rule-card <?php echo $b_class; ?>">
                            <div class="rule-card-header">
                                <span class="material-symbols-outlined <?php echo $c_class; ?>">policy</span>
                                <span class="section-label-micro <?php echo $c_class; ?>">Rule #<?php echo $rule['Rule_No']; ?></span>
                            </div>
                            <h4 class="monitor-title">Gym Policy</h4>
                            <p class="rule-desc"><?php echo htmlspecialchars($rule['Rule']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="glass-card rule-card border-tertiary">
                        <div class="rule-card-header">
                            <span class="material-symbols-outlined text-color-tertiary">checkroom</span>
                            <span class="section-label-micro text-color-tertiary">Dress Code</span>
                        </div>
                        <h4 class="monitor-title">Proper Attire</h4>
                        <p class="rule-desc">Always wear athletic clothing and clean indoor shoes to maintain hygiene and safety.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Modular Bottom Navigation Bar -->
    <?php 
    if (isset($_SESSION['is_captain']) && $_SESSION['is_captain'] == 1) {
        require_once '../../includes/bottombar/bottombar_captain.php';
    } else {
        require_once '../../includes/bottombar/bottombar_member.php';
    }
    ?>

    <!-- Modular Footer with Auto Script Loader -->
    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>
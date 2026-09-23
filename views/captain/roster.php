<?php
// views/captain/roster.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$page_title = "Team Roster | FitCampus";

$extra_js = [
    "member/dashboard.js", // Required for sidebar functionality
    "captain/roster.js"
];

require_once '../../backend/captain/roster_backend.php';
require_once '../../includes/headers/header_member.php';
require_once '../../includes/sidebars/sidebar_captain.php';
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container bk-main-container">
        
        <!-- Grid Layout matching booking.php -->
        <div class="grid-layout-booking">
            <!-- Left: Roster List & Banner Section -->
            <section class="booking-calendar-section">
                
                <!-- Team Switcher Header -->
                <div class="glass-card inner-glow bk-card-pad overflow-hidden bk-mb-lg">
                    <div class="cal-header-flex">
                        <div>
                            <h2 class="settings-main-title m-0">Team Roster Management</h2>
                            <p class="txt-muted text-sm bk-mt-xs m-0">View and manage members of your varsity sports team.</p>
                        </div>
                        <?php if (count($captain_teams) > 1): ?>
                        <div class="booking-toggles">
                            <div class="form-group-cal m-0">
                                <!-- Redirects immediately on change -->
                                <select class="cal-input-field gl-select" id="team-switcher-select" style="min-width: 250px;" onchange="window.location.href=this.value;">
                                    <?php foreach ($captain_teams as $t): ?>
                                        <option value="roster.php?team_id=<?= $t['Team_ID'] ?>" <?= ((int)$t['Team_ID'] === (int)$selected_team_id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['Team_Name']) ?> (<?= htmlspecialchars($t['Sport']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Auto-Dismissing Alerts -->
                <?php if (!empty($success_message)): ?>
                    <div class="glass-card bk-card-pad bk-mb-md alert-success" style="background: rgba(74,225,118,0.1); border-color: rgba(74,225,118,0.3);">
                        <p class="txt-green text-sm m-0 font-semibold"><?= htmlspecialchars($success_message) ?></p>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error_message)): ?>
                    <div class="glass-card bk-card-pad bk-mb-md alert-error" style="background: rgba(255,180,171,0.1); border-color: rgba(255,180,171,0.3);">
                        <p class="txt-error text-sm m-0 font-semibold"><?= htmlspecialchars($error_message) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($selected_team_name)): ?>
                    <!-- Team Banner -->
                    <div class="glass-card bk-card-pad bk-mb-lg team-banner-card">
                        <div class="badge-tag bk-mb-xs"><?= htmlspecialchars($selected_team_gender ?: 'Mixed') ?> DIVISION</div>
                        <h2 class="settings-main-title text-xl m-0"><?= htmlspecialchars($selected_team_name) ?></h2>
                    </div>

                    <!-- Team Roster Table -->
                    <div class="glass-card inner-glow overflow-hidden border-dim">
                        <div class="bk-p-md border-b-dim flex-between items-center bg-dim">
                            <h3 class="settings-main-title text-base m-0">Team Roster</h3>
                            <span class="gl-badge badge-bg-primary txt-primary mono uppercase font-bold font-xs"><?= count($team_members) ?> MEMBERS TOTAL</span>
                        </div>
                        
                        <div class="custom-scrollbar roster-scroll-box">
                            <table class="cal-data-table w-full text-left">
                                <thead>
                                    <tr class="bg-dim">
                                        <th class="bk-px-lg bk-py-md font-xs uppercase tracking-wide txt-muted">Member Name</th>
                                        <th class="bk-px-lg bk-py-md font-xs uppercase tracking-wide txt-muted">Student ID</th>
                                        <th class="bk-px-lg bk-py-md font-xs uppercase tracking-wide txt-muted">Role</th>
                                        <th class="bk-px-lg bk-py-md font-xs uppercase tracking-wide txt-muted">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-dim">
                                    <?php if (!empty($team_members)): ?>
                                        <?php foreach ($team_members as $member): ?>
                                        <tr class="hover-bg-dim transition-colors">
                                            <td class="bk-px-lg bk-py-md">
                                                <p class="font-semibold text-sm m-0 txt-white"><?= htmlspecialchars($member['First_Name'] . ' ' . $member['Last_Name']) ?></p>
                                            </td>
                                            <td class="bk-px-lg bk-py-md mono text-xs txt-muted">
                                                <?= htmlspecialchars($member['Registration_Number'] ?? 'N/A') ?>
                                            </td>
                                            <td class="bk-px-lg bk-py-md">
                                                <?php if ($member['Role_In_Team'] === 'Captain'): ?>
                                                    <span class="dark-badge font-bold tracking-wider font-xs border-dim txt-primary captain-badge-highlight">CAPTAIN</span>
                                                <?php else: ?>
                                                    <span class="dark-badge font-bold tracking-wider font-xs border-dim txt-muted">MEMBER</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="bk-px-lg bk-py-md">
                                                <?php if ($member['Role_In_Team'] !== 'Captain'): ?>
                                                    <form action="roster.php?team_id=<?= $selected_team_id ?>" method="POST" onsubmit="return confirm('Are you sure you want to remove this member?');" style="margin:0;">
                                                        <input type="hidden" name="action" value="remove_member">
                                                        <input type="hidden" name="team_id" value="<?= $selected_team_id ?>">
                                                        <input type="hidden" name="remove_user_id" value="<?= $member['User_ID'] ?>">
                                                        <button type="submit" class="toggle-btn" style="color: var(--error); padding: 4px 8px; font-size: 11px;">Remove</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-xs txt-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center bk-py-lg txt-muted text-sm">No members found for this team.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="glass-card bk-card-pad text-center">
                        <p class="txt-muted text-sm m-0">You are currently not assigned as a captain to any active teams.</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Right: Add Member Sticky Form Section -->
            <aside class="booking-form-section">
                <div class="glass-card bk-card-pad shadow-xl sticky-form">
                    <div class="form-header bk-mb-lg">
                        <h3 class="settings-main-title text-lg m-0">Add Team Member</h3>
                    </div>

                    <?php if (!empty($selected_team_name)): ?>
                        <form action="roster.php?team_id=<?= $selected_team_id ?>" method="POST" class="booking-form space-y-md">
                            <input type="hidden" name="action" value="add_member">
                            <input type="hidden" name="team_id" value="<?= $selected_team_id ?>">
                            
                            <div class="form-group-cal space-y-xs">
                                <label class="gl-lbl-accent">STUDENT ID / REG NO</label>
                                <input type="text" name="reg_number" class="cal-input-field" placeholder="e.g. 2022CS001" required>
                            </div>

                            <div class="form-group-cal space-y-xs">
                                <label class="gl-lbl-accent">CAPTAIN PASSWORD</label>
                                <input type="password" name="captain_password" class="cal-input-field" placeholder="Enter your password" required>
                            </div>

                            <button type="submit" class="gl-btn-gradient w-full bk-py-md bk-mt-md font-bold text-sm flex-items-center gap-2 justify-center">
                                <span class="material-symbols-outlined" style="font-size: 20px;">person_add</span>
                                <span>Add Member</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="txt-muted text-sm m-0 text-center">Select a team first.</p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>

    </main>

    <?php require_once '../../includes/bottombar/bottombar_captain.php'; ?>
    <?php require_once '../../includes/footers/footer_common.php'; ?>
</div>
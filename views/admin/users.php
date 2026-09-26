<?php
// views/admin/users.php

// 1. Include the Page Controller (No Direct DB Connection Here)
require_once '../../controllers/admin/users_page_controller.php';

$page_title = 'User & Roster Management - FitCampus';
$extra_js = ["admin/users.js?v=" . time()]; 
require_once '../../includes/headers/header_admin.php';
?>

<!-- Pass Teams List and CSRF Token globally to JS -->
<script>
    const globalTeamsList = <?php echo json_encode($teamsList); ?>;
    const csrfToken = "<?php echo $_SESSION['csrf_token']; ?>";
</script>

<div class="admin-viewport-wrapper">
    <?php include_once '../../includes/sidebars/sidebar_admin.php'; ?>

    <main class="admin-main-canvas">
        <div class="section-header bk-mb-md">
            <div>
                <h2 class="section-title">Institutional User Directory</h2>
                <p class="section-subtitle">Manage memberships, assign instructor status, and designate team captains</p>
            </div>
        </div>

        <div class="glass-card bk-card-pad bk-mb-md flex-between" style="flex-wrap: wrap; gap: 16px;">
            <div class="input-wrapper search-box-wrapper" style="flex: 1; min-width: 250px;">
                <span class="material-symbols-outlined input-icon">search</span>
                <input type="text" id="filterSearch" class="form-control" placeholder="Search Email, Name or Reg No...">
            </div>
            
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <select id="filterRole" class="form-control select-custom" style="width: auto;">
                    <option value="all">All Roles</option>
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                    <option value="admin">Admin</option>
                </select>

                <select id="filterTeam" class="form-control select-custom" style="width: auto;">
                    <option value="all">All Teams (Entire Directory)</option>
                    <?php foreach($teamsList as $t): ?>
                        <option value="<?= $t['Team_ID'] ?>"><?= htmlspecialchars($t['Team_Name']) ?> Roster</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="glass-card p-0 overflow-hidden">
            <div class="admin-table-container custom-scrollbar">
                <table class="admin-data-table">
                    <thead>
                        <tr>
                            <th>Member Identity</th>
                            <th>Faculty / ID</th>
                            <th>System Role</th>
                            <th class="text-right">Privileges & Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php foreach ($users as $row): 
                            $roleLower = strtolower($row['Role']); 
                            $searchString = strtolower($row['First_Name'] . ' ' . $row['Last_Name'] . ' ' . $row['Email'] . ' ' . ($row['Registration_Number'] ?? ''));
                            $profileImg = $row['Profile_Image'] ?? 'default_avatar.png';
                            
                            $jsData = [
                                'User_ID' => $row['User_ID'],
                                'First_Name' => $row['First_Name'],
                                'Last_Name' => $row['Last_Name'],
                                'Email' => $row['Email'],
                                'Profile_Image' => $row['Profile_Image'],
                                'Registration_Number' => $row['Registration_Number'],
                                'Faculty' => $row['Faculty'],
                                'NIC' => $row['NIC'],
                                'Emergency_Contact' => $row['Emergency_Contact'],
                                'Role' => $row['Role'],
                                'Life_Percentage' => $row['Life_Percentage'],
                                'Date_of_Final_Exam' => $row['Date_of_Final_Exam'],
                                'parsed_teams' => $row['parsed_teams']
                            ];
                            $userJson = htmlspecialchars(json_encode($jsData), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr class="user-data-row" 
                                data-search="<?= htmlspecialchars($searchString) ?>" 
                                data-role="<?= $roleLower ?>" 
                                data-teams="<?= htmlspecialchars($row['team_ids']) ?>">
                                
                                <td>
                                    <div class="user-meta-cell">
                                        <div class="user-avatar-circle">
                                            <img src="../../assets/images/uploads/<?= htmlspecialchars($profileImg) ?>" onerror="this.src='../../assets/images/uoc-logo.png';">
                                        </div>
                                        <div>
                                            <div class="user-cell-name"><?= htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']) ?></div>
                                            <div class="user-cell-email"><?= htmlspecialchars($row['Email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($roleLower === 'student'): ?>
                                        <div class="faculty-tag"><?= htmlspecialchars($row['Faculty'] ?? 'N/A') ?></div>
                                        <div class="reg-no-tag"><?= htmlspecialchars($row['Registration_Number'] ?? 'N/A') ?></div>
                                    <?php else: ?>
                                        <span class="txt-muted text-xs">Staff / Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-tag badge-<?= $roleLower ?>"><?= strtoupper($row['Role']) ?></span>
                                    <?php if ($row['is_captain']): ?>
                                        <span class="badge-status badge-captain ml-2" style="background: rgba(247, 190, 29, 0.2); color: var(--tertiary); border: 1px solid var(--tertiary); padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;">CAPTAIN</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <div class="action-btn-group">
                                        <button class="btn btn-glass btn-sm btn-view-user" data-user='<?= $userJson ?>' style="border: 1px solid rgba(255,255,255,0.1);">
                                            <span class="material-symbols-outlined" style="font-size: 16px;">visibility</span> View
                                        </button>

                                        <?php if ($roleLower === 'student'): ?>
                                            <button class="btn btn-primary btn-sm btn-role-user" data-user='<?= $userJson ?>'>
                                                <span class="material-symbols-outlined" style="font-size: 16px;">manage_accounts</span> Edit Role
                                            </button>
                                        <?php else: ?>
                                            <span class="txt-muted text-xs mx-2">N/A</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- (Modals HTML remains exactly the same below this point) -->
<div id="viewUserModal" class="wk-modal-overlay hidden" style="z-index: 1000;">
    <div class="modal-dialog-inspector" style="width: 100%; max-width: 600px; background: #1e1e1e; border-radius: 12px; margin: auto; padding: 24px;">
        <div class="flex-between border-b-dim bk-pb-sm bk-mb-md">
            <h3 class="settings-main-title text-xl m-0 flex-items-center gap-2 text-primary">
                <span class="material-symbols-outlined">person</span> Member Details
            </h3>
            <button class="close-sidebar-btn" onclick="closeModal('viewUserModal')"><span class="material-symbols-outlined">close</span></button>
        </div>

        <div class="flex-items-center gap-3 bk-mb-md p-3 rounded-lg" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
            <img id="v-avatar" src="" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            <div>
                <h4 id="v-name" class="m-0 text-lg txt-white"></h4>
                <div id="v-email" class="text-sm txt-muted mono"></div>
            </div>
        </div>

        <div class="settings-grid-layout" style="gap: 16px; margin-bottom: 20px;">
            <div class="bg-dim p-3 rounded-lg"><label class="gl-lbl-accent">Reg No</label><div id="v-reg" class="txt-white font-bold"></div></div>
            <div class="bg-dim p-3 rounded-lg"><label class="gl-lbl-accent">Faculty</label><div id="v-fac" class="txt-white font-bold"></div></div>
            <div class="bg-dim p-3 rounded-lg"><label class="gl-lbl-accent">NIC</label><div id="v-nic" class="txt-white font-bold"></div></div>
            <div class="bg-dim p-3 rounded-lg"><label class="gl-lbl-accent">Emergency Contact</label><div id="v-phone" class="txt-white font-bold"></div></div>
        </div>

        <div class="bk-mb-md">
            <label class="gl-lbl-accent">TEAM ROSTERS</label>
            <div id="v-teams" class="p-3 rounded-lg text-sm txt-white" style="background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1);"></div>
        </div>

        <div id="student-update-section">
            <h4 class="border-b-dim bk-pb-sm bk-mb-sm txt-white m-0">Update Member Meta</h4>
            <input type="hidden" id="v-userid">
            <div class="flex gap-3 bk-mb-md">
                <div class="flex-1">
                    <label class="gl-lbl-accent">Life Percentage (%)</label>
                    <input type="number" id="v-life" class="cal-input-field" min="0" max="100">
                </div>
                <div class="flex-1">
                    <label class="gl-lbl-accent">Final Exam Date</label>
                    <input type="date" id="v-exam" class="cal-input-field">
                </div>
            </div>
            
            <div class="flex gap-2">
                <button class="btn btn-glass flex-1" onclick="closeModal('viewUserModal')">Close</button>
                <button class="btn btn-primary flex-1" onclick="updateStudentMeta()">Update Details</button>
            </div>
        </div>
    </div>
</div>

<div id="editRoleModal" class="wk-modal-overlay hidden" style="z-index: 1000;">
    <div class="modal-dialog-inspector" style="width: 100%; max-width: 500px; background: #1e1e1e; border-radius: 12px; margin: auto; padding: 24px;">
        <div class="flex-between border-b-dim bk-pb-sm bk-mb-md">
            <h3 class="settings-main-title text-xl m-0 flex-items-center gap-2 text-tertiary">
                <span class="material-symbols-outlined">shield_person</span> Manage Captain Role
            </h3>
            <button class="close-sidebar-btn" onclick="closeModal('editRoleModal')"><span class="material-symbols-outlined">close</span></button>
        </div>

        <p class="text-sm txt-muted bk-mb-md">Assign or revoke team captaincy for <strong id="r-name" class="txt-white"></strong>.</p>
        <input type="hidden" id="r-userid">

        <div id="demote-section" class="bk-mb-lg hidden">
            <label class="gl-lbl-accent">CURRENT CAPTAIN ASSIGNMENTS</label>
            <div id="current-captain-teams" class="space-y-sm mt-2"></div>
        </div>

        <div class="form-group-cal bk-mb-lg">
            <label class="gl-lbl-accent border-t-dim pt-4 mt-2">PROMOTE TO CAPTAIN</label>
            <select id="r-team" class="cal-input-field gl-select mt-2">
                <option value="">-- Select Team --</option>
            </select>
            <button type="button" class="btn w-full glowing-secondary mt-3" style="background: var(--secondary); color: #000; font-weight: bold;" onclick="submitRoleChange('promote')">Promote to Captain</button>
        </div>

    </div>
</div>

<?php 
include_once '../../includes/bottombar/bottombar_admin.php';
include_once '../../includes/footers/footer_common.php'; 
?>
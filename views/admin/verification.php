<?php
// views/admin/verification.php
require_once '../../includes/db_connection.php';
require_once '../../bll/admin/VerificationBLL.php';

$verificationBLL = new VerificationBLL($pdo);

// Fetch data via BLL instead of direct database queries
$data = $verificationBLL->getVerificationData();
$pending_users = $data['pending_users'];
$count_pending = $data['count_pending'];
$count_approved = $data['count_approved'];
$count_total_members = $data['count_total_members'];

$page_title = "User Verification Console - FitCampus";
require_once '../../includes/headers/header_admin.php';
?>

<div class="admin-viewport-wrapper">
    <?php include_once '../../includes/sidebars/sidebar_admin.php'; ?>

    <main class="admin-main-canvas">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert-success">
                <span class="material-symbols-outlined">check_circle</span>
                <span><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-error">
                <span class="material-symbols-outlined">error</span>
                <span><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <div class="content-header-row">
            <div>
                <h2 class="page-main-heading">User Verification Console</h2>
                <p class="page-sub-heading">Review and approve new institutional member registrations</p>
            </div>
        </div>

        <div class="verification-stats-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="glass-card stat-summary-box">
                <div class="stat-top-row">
                    <span class="stat-label">Under Review</span>
                    <span class="material-symbols-outlined text-tertiary">hourglass_empty</span>
                </div>
                <span class="stat-value text-tertiary"><?php echo $count_pending; ?></span>
            </div>

            <div class="glass-card stat-summary-box">
                <div class="stat-top-row">
                    <span class="stat-label">Approved Members</span>
                    <span class="material-symbols-outlined text-secondary">check_circle</span>
                </div>
                <span class="stat-value text-secondary"><?php echo $count_approved; ?></span>
            </div>

            <div class="glass-card stat-summary-box">
                <div class="stat-top-row">
                    <span class="stat-label">Total Members</span>
                    <span class="material-symbols-outlined text-primary">group</span>
                </div>
                <span class="stat-value text-white"><?php echo $count_total_members; ?></span>
            </div>
        </div>

        <div class="glass-card p-0 overflow-hidden">
            <?php if (empty($pending_users)): ?>
                <div class="empty-state-box" style="padding: 40px; text-align: center;">
                    <span class="material-symbols-outlined empty-state-icon" style="font-size: 48px; color: var(--secondary); margin-bottom: 16px;">task_alt</span>
                    <p class="empty-state-title" style="font-size: 18px; font-weight: bold; margin-bottom: 8px;">No pending verification requests.</p>
                    <span class="empty-state-desc" style="color: var(--on-surface-variant);">All student registration records have been processed and approved.</span>
                </div>
            <?php else: ?>
                <div class="admin-table-container">
                    <table class="admin-data-table" id="verificationTable">
                        <thead>
                            <tr>
                                <th>Student Info</th>
                                <th>Faculty / ID</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_users as $user): ?>
                                <tr class="verification-table-row">
                                    <td>
                                        <div class="user-meta-cell">
                                            <div class="user-avatar-circle">
                                                <img src="../../assets/images/uploads/<?php echo htmlspecialchars($user['profile_image'] ?? 'default_avatar.png', ENT_QUOTES, 'UTF-8'); ?>" 
                                                     alt="Student Profile" 
                                                     onerror="this.src='../../assets/images/uoc-logo.png';">
                                            </div>
                                            <div>
                                                <div class="user-cell-name"><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                <div class="user-cell-email"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="faculty-tag"><?php echo htmlspecialchars($user['faculty'] ?? 'Undergraduate', ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="reg-no-tag"><?php echo htmlspecialchars($user['reg_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge-status-pending">
                                            <span class="pulsing-dot"></span>
                                            Pending Review
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-btn-group">
                                            <button type="button" class="btn btn-glass btn-sm btn-inspect-trigger" data-user='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>' style="padding: 6px 12px; font-size: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; cursor: pointer; color: white;">
                                                View Details
                                            </button>
                                            <button type="button" class="quick-action-btn quick-approve" title="Quick Approve" onclick="quickDecision(<?php echo $user['user_id']; ?>, 'approve')">
                                                <span class="material-symbols-outlined">check</span>
                                            </button>
                                            <button type="button" class="quick-action-btn quick-decline" title="Quick Decline" onclick="quickDecision(<?php echo $user['user_id']; ?>, 'reject')">
                                                <span class="material-symbols-outlined">close</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Inspector Modal -->
<div id="verificationModal" class="wk-modal-overlay hidden" style="z-index: 1000; overflow-y: auto;">
    <div class="modal-dialog-inspector" style="width: 100%; max-width: 800px; background: #1e1e1e; border-radius: 12px; margin: auto; display: flex; flex-direction: column; position: relative;">
        
        <div class="modal-header-banner" style="padding: 20px; background: #2a2a2a; border-radius: 12px 12px 0 0; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
            <div class="modal-user-identity-card" style="display: flex; gap: 15px; align-items: center;">
                <div class="modal-avatar-lg" style="width: 60px; height: 60px; border-radius: 50%; overflow: hidden; border: 2px solid var(--primary);">
                    <img id="modalAvatar" src="" alt="Profile" onerror="this.src='../../assets/images/uoc-logo.png';" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div>
                    <h3 id="modalUserName" class="modal-title-name" style="margin: 0; font-size: 20px; color: white;">Student Name</h3>
                    <p id="modalUserSubtitle" class="modal-sub-designation" style="margin: 5px 0 0; color: #aaa; font-size: 14px;">Undergraduate</p>
                </div>
            </div>
            <button type="button" class="modal-close-trigger" id="modalCloseBtn" style="background: transparent; border: none; color: white; cursor: pointer;">
                <span class="material-symbols-outlined" style="font-size: 28px;">close</span>
            </button>
        </div>

        <div class="modal-content-body custom-scrollbar" style="padding: 24px; overflow-y: auto; flex-grow: 1;">
            
            <h4 style="color: white; border-bottom: 1px solid #444; padding-bottom: 8px; margin-bottom: 16px; margin-top: 0;">Personal & Academic Details</h4>
            
            <div class="modal-credentials-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div class="cred-info-card" style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    <label style="font-size: 11px; color: #888; text-transform: uppercase; display: block; margin-bottom: 4px;">Student Reg. No</label>
                    <div id="modalRegNo" style="font-weight: bold; color: white;">N/A</div>
                </div>
                <div class="cred-info-card" style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    <label style="font-size: 11px; color: #888; text-transform: uppercase; display: block; margin-bottom: 4px;">Email Address</label>
                    <div id="modalEmail" style="font-weight: bold; color: white; word-break: break-all;">N/A</div>
                </div>
                <div class="cred-info-card" style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    <label style="font-size: 11px; color: #888; text-transform: uppercase; display: block; margin-bottom: 4px;">NIC Number</label>
                    <div id="modalNic" style="font-weight: bold; color: white;">N/A</div>
                </div>
                <div class="cred-info-card" style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    <label style="font-size: 11px; color: #888; text-transform: uppercase; display: block; margin-bottom: 4px;">Date of Birth</label>
                    <div id="modalDob" style="font-weight: bold; color: white;">N/A</div>
                </div>
                <div class="cred-info-card" style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    <label style="font-size: 11px; color: #888; text-transform: uppercase; display: block; margin-bottom: 4px;">Gender</label>
                    <div id="modalGender" style="font-weight: bold; color: white;">N/A</div>
                </div>
                <div class="cred-info-card" style="background: rgba(255,255,255,0.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                    <label style="font-size: 11px; color: #888; text-transform: uppercase; display: block; margin-bottom: 4px;">Emergency Contact</label>
                    <div id="modalPhone" style="font-weight: bold; color: white;">N/A</div>
                </div>
            </div>

            <div class="modal-id-section">
                <h4 style="color: white; border-bottom: 1px solid #444; padding-bottom: 8px; margin-bottom: 16px;">Institutional Identity Verification</h4>
                <div class="id-previews-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div class="id-preview-container">
                        <span style="font-size: 12px; color: #aaa; margin-bottom: 8px; display: block;">Uploaded University ID (Front)</span>
                        <div class="id-image-wrapper group" onclick="zoomImage(this)" style="position: relative; cursor: zoom-in; border-radius: 8px; overflow: hidden; border: 1px solid #444;">
                            <img id="modalIdFront" src="" alt="ID Front Card" style="width: 100%; height: auto; display: block;">
                            <div class="id-zoom-overlay" style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;">
                                <span class="material-symbols-outlined" style="color: white;">zoom_in</span>
                            </div>
                        </div>
                    </div>
                    <div class="id-preview-container">
                        <span style="font-size: 12px; color: #aaa; margin-bottom: 8px; display: block;">Uploaded University ID (Back)</span>
                        <div class="id-image-wrapper group" onclick="zoomImage(this)" style="position: relative; cursor: zoom-in; border-radius: 8px; overflow: hidden; border: 1px solid #444;">
                            <img id="modalIdBack" src="" alt="ID Back Card" style="width: 100%; height: auto; display: block;">
                            <div class="id-zoom-overlay" style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;">
                                <span class="material-symbols-outlined" style="color: white;">zoom_in</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Updated form action pointing to the new controller -->
            <form id="verifyForm" method="POST" action="../../controllers/admin/VerificationController.php" class="modal-action-form" style="margin-top: 24px;">
                <input type="hidden" name="user_id" id="formUserId" value="">
                <input type="hidden" name="action" id="formAction" value="approve">

                <div class="form-group hidden" id="rejectionGroup" style="background: rgba(255,0,0,0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,0,0,0.2);">
                    <label class="form-label text-error" style="display:block; margin-bottom: 8px; font-weight: bold; color: #ff6b6b;">Reason for Declining Verification *</label>
                    <textarea name="rejection_reason" id="rejectionReasonInput" style="width: 100%; padding: 12px; border-radius: 6px; background: rgba(255,255,255,0.05); color: white; border: 1px solid #555; resize: vertical; min-height: 80px;" placeholder="e.g. Identity card is blurred, or registration number mismatches."></textarea>
                </div>
            </form>
        </div>

        <div class="modal-actions-footer" style="padding: 20px; background: #2a2a2a; border-radius: 0 0 12px 12px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; flex-shrink: 0;">
            <p style="margin: 0; font-size: 12px; color: #aaa; flex: 1; min-width: 200px;">Please verify that document details match the student information before approving.</p>
            <div style="display: flex; gap: 10px;">
                <button type="button" id="btnRejectToggle" style="padding: 10px 20px; background: transparent; border: 1px solid #ff6b6b; color: #ff6b6b; border-radius: 6px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">block</span> Decline
                </button>
                <button type="button" id="btnApproveAction" style="padding: 10px 20px; background: var(--secondary); color: #000; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span> Approve User
                </button>
            </div>
        </div>

    </div>
</div>

<style>
    .id-image-wrapper:hover .id-zoom-overlay { opacity: 1 !important; }
</style>

<!-- Hidden Quick Form for Row Actions pointing to the new controller -->
<form id="quickDecisionForm" method="POST" action="../../controllers/admin/VerificationController.php" style="display: none;">
    <input type="hidden" name="user_id" id="quickUserId" value="">
    <input type="hidden" name="action" id="quickAction" value="">
    <input type="hidden" name="rejection_reason" id="quickReason" value="">
</form>

<?php 
$extra_js = "admin/user-approval.js";
include_once '../../includes/bottombar/bottombar_admin.php';
require_once '../../includes/footers/footer_common.php'; 
?>
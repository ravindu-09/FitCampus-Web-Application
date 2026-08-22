<?php
// views/admin/verification.php
require_once '../../includes/db_connection.php';

$page_title = "User Verification Console - FitCampus";
require_once '../../includes/headers/header_admin.php';

try {
    // 1. Fetch Pending Applicants
    $stmt = $pdo->query("
        SELECT 
            u.User_ID AS user_id, 
            CONCAT(u.First_Name, ' ', u.Last_Name) AS full_name, 
            u.Email AS email, 
            s.Registration_Number AS reg_no, 
            s.Faculty AS faculty, 
            s.Emergency_Contact AS emergency_contact, 
            s.Profile_Image AS profile_image, 
            s.Student_ID_Front AS id_front_image, 
            s.Student_ID_Back AS id_back_image, 
            s.Created_At AS created_at 
        FROM `USER` u
        INNER JOIN `UNIVERSITY_STUDENT` s ON u.User_ID = s.User_ID 
        WHERE s.Status = 'pending' 
        ORDER BY s.Created_At ASC
    ");
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Telemetry Counts
    $count_pending = count($pending_users);
    $count_approved = $pdo->query("SELECT COUNT(*) FROM `UNIVERSITY_STUDENT` WHERE `Status` = 'active'")->fetchColumn() ?: 0;
    $count_declined = $pdo->query("SELECT COUNT(*) FROM `UNIVERSITY_STUDENT` WHERE `Status` = 'suspended'")->fetchColumn() ?: 0;
    $count_total_members = $pdo->query("SELECT COUNT(*) FROM `UNIVERSITY_STUDENT`")->fetchColumn() ?: 0;

} catch (\PDOException $e) {
    error_log("Verification Fetch Error: " . $e->getMessage());
    $pending_users = [];
    $count_pending = 0;
    $count_approved = 0;
    $count_declined = 0;
    $count_total_members = 0;
}
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

                <div class="verification-filter-controls">
                    <div class="input-wrapper search-box-wrapper">
                        <span class="material-symbols-outlined input-icon">search</span>
                        <input type="text" id="userSearchInput" class="form-control" placeholder="Search ID or Name...">
                    </div>

                    <div class="select-wrapper">
                        <select id="dateFilterSelect" class="form-control select-custom">
                            <option value="all">All Dates</option>
                            <option value="today">Today</option>
                            <option value="7days">Past 7 Days</option>
                            <option value="30days">Past 30 Days</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="verification-stats-grid">
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
                        <span class="stat-label">Suspended</span>
                        <span class="material-symbols-outlined text-error">cancel</span>
                    </div>
                    <span class="stat-value text-error"><?php echo $count_declined; ?></span>
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
                    <div class="empty-state-box">
                        <span class="material-symbols-outlined empty-state-icon">task_alt</span>
                        <p class="empty-state-title">No pending verification requests.</p>
                        <span class="empty-state-desc">All student registration records have been processed and approved.</span>
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
                                    <tr class="verification-table-row" 
                                        data-search="<?php echo htmlspecialchars(strtolower($user['full_name'] . ' ' . ($user['reg_no'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-date="<?php echo date('Y-m-d', strtotime($user['created_at'])); ?>">
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
                                                <button type="button" class="btn btn-glass btn-sm btn-inspect-trigger" data-user='<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8'); ?>'>
                                                    <span class="material-symbols-outlined" style="font-size: 18px;">visibility</span>
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
    <div id="verificationModal" class="modal-overlay hidden">
        <div class="modal-dialog-inspector">
            
            <div class="modal-header-banner">
                <div class="modal-user-identity-card">
                    <div class="modal-avatar-lg">
                        <img id="modalAvatar" src="" alt="Profile" onerror="this.src='../../assets/images/uoc-logo.png';">
                    </div>
                    <div>
                        <h3 id="modalUserName" class="modal-title-name">Student Name</h3>
                        <p id="modalUserSubtitle" class="modal-sub-designation">Undergraduate</p>
                    </div>
                </div>
                <button type="button" class="modal-close-trigger" id="modalCloseBtn">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="modal-content-body custom-scrollbar">
                <div class="modal-credentials-grid">
                    <div class="cred-info-card">
                        <label class="cred-label">Student ID</label>
                        <div id="modalRegNo" class="cred-value">N/A</div>
                    </div>
                    <div class="cred-info-card">
                        <label class="cred-label">Email Address</label>
                        <div id="modalEmail" class="cred-value">N/A</div>
                    </div>
                    <div class="cred-info-card">
                        <label class="cred-label">Emergency Contact</label>
                        <div id="modalPhone" class="cred-value">N/A</div>
                    </div>
                    <div class="cred-info-card">
                        <label class="cred-label">Registration Date</label>
                        <div id="modalDate" class="cred-value">N/A</div>
                    </div>
                </div>

                <div class="modal-id-section">
                    <h4 class="modal-section-heading">Institutional Identity Verification</h4>
                    <div class="id-previews-grid">
                        <div class="id-preview-container">
                            <span class="id-type-title">Uploaded University ID (Front)</span>
                            <div class="id-image-wrapper group" onclick="zoomImage(this)">
                                <img id="modalIdFront" src="" alt="ID Front Card">
                                <div class="id-zoom-overlay">
                                    <span class="material-symbols-outlined">zoom_in</span>
                                </div>
                            </div>
                        </div>
                        <div class="id-preview-container">
                            <span class="id-type-title">Uploaded University ID (Back)</span>
                            <div class="id-image-wrapper group" onclick="zoomImage(this)">
                                <img id="modalIdBack" src="" alt="ID Back Card">
                                <div class="id-zoom-overlay">
                                    <span class="material-symbols-outlined">zoom_in</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="verifyForm" method="POST" action="../../backend/admin/approve_user.php" class="modal-action-form">
                    <input type="hidden" name="user_id" id="formUserId" value="">
                    <input type="hidden" name="action" id="formAction" value="approve">

                    <div class="form-group hidden" id="rejectionGroup">
                        <label class="form-label text-error">Reason for Declining Verification</label>
                        <textarea name="rejection_reason" id="rejectionReasonInput" class="form-control rejection-textarea" placeholder="e.g. Identity card is expired, blurred, or registration number mismatches institutional records."></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-actions-footer">
                <p class="modal-verify-guideline">Please verify that document details match the student information before approving.</p>
                <div class="modal-btn-cluster">
                    <button type="button" class="btn btn-decline-action" id="btnRejectToggle">
                        <span class="material-symbols-outlined">block</span>
                        Decline
                    </button>
                    <button type="button" class="btn btn-approve-action glowing-secondary" id="btnApproveAction">
                        <span class="material-symbols-outlined">check_circle</span>
                        Approve User
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Hidden Quick Form for Row Actions -->
    <form id="quickDecisionForm" method="POST" action="../../backend/admin/approve_user.php" style="display: none;">
        <input type="hidden" name="user_id" id="quickUserId" value="">
        <input type="hidden" name="action" id="quickAction" value="">
        <input type="hidden" name="rejection_reason" id="quickReason" value="">
    </form>

<?php 
$extra_js = "admin/user-approval.js";
require_once '../../includes/footers/footer_common.php'; 
?>
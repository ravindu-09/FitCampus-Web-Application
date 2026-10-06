<?php
// views/admin/support_requests.php
require_once '../../controllers/admin/support_requests_page_controller.php';
require_once '../../includes/headers/header_admin.php';
?>
<link rel="stylesheet" href="../../assets/css/common/support.css">

<div class="admin-viewport-wrapper">
    <?php require_once '../../includes/sidebars/sidebar_admin.php'; ?>
    <main class="admin-main-canvas">
        <section class="support-page support-admin-page">
            <header class="support-heading">
                <div>
                    <span class="support-eyebrow">ADMINISTRATION / INBOX</span>
                    <h2>Support requests</h2>
                    <p>Review member requests and send a status update or response.</p>
                </div>
                <a class="support-admin-link" href="../common/support.php"><span class="material-symbols-outlined">open_in_new</span> User support page</a>
            </header>

            <?php if (!empty($_SESSION['support_admin_success'])): ?>
                <div class="support-alert support-alert-success" role="status"><?php echo htmlspecialchars($_SESSION['support_admin_success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['support_admin_success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['support_admin_error'])): ?>
                <div class="support-alert support-alert-error" role="alert"><?php echo htmlspecialchars($_SESSION['support_admin_error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['support_admin_error']); ?></div>
            <?php endif; ?>
            <?php if ($support_error): ?>
                <div class="support-alert support-alert-error" role="alert"><?php echo htmlspecialchars($support_error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php elseif (empty($admin_requests)): ?>
                <div class="support-empty-state support-admin-empty"><span class="material-symbols-outlined">task_alt</span><p>No support requests have been submitted.</p></div>
            <?php else: ?>
                <div class="support-admin-list">
                    <?php
                    $categoryLabels = [
                        'account_extension' => 'Account extension',
                        'life_bar_replenishment' => 'Life bar replenishment',
                        'complaint_feedback' => 'Complaint or feedback'
                    ];
                    foreach ($admin_requests as $request):
                        $statusClass = strtolower(str_replace(' ', '-', $request['Status']));
                    ?>
                    <article class="support-admin-card">
                        <div class="support-admin-request">
                            <div class="support-request-topline">
                                <span class="support-request-category"><?php echo htmlspecialchars($categoryLabels[$request['Category']] ?? $request['Category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="support-status support-status-<?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($request['Status'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($request['Full_Name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="support-admin-identity">
                                <?php echo htmlspecialchars($request['Email'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php if (!empty($request['Registration_Number'])): ?> · <?php echo htmlspecialchars($request['Registration_Number'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                                <?php if (!empty($request['Faculty'])): ?> · <?php echo htmlspecialchars($request['Faculty'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                            </p>
                            <p class="support-request-date">Request #<?php echo (int)$request['Feedback_ID']; ?> · <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($request['Submitted_Date'])), ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php if (!empty($request['Requested_End_Date'])): ?>
                                <p class="support-extension-date">Requested account end date: <strong><?php echo htmlspecialchars(date('M j, Y', strtotime($request['Requested_End_Date'])), ENT_QUOTES, 'UTF-8'); ?></strong></p>
                            <?php endif; ?>
                            <div class="support-admin-description"><?php echo nl2br(htmlspecialchars($request['Description'], ENT_QUOTES, 'UTF-8')); ?></div>
                            <?php if (!empty($request['Attachment_Path'])): ?>
                                <a class="support-attachment-link" href="../../controllers/common/support_attachment.php?id=<?php echo (int)$request['Feedback_ID']; ?>" target="_blank" rel="noopener"><span class="material-symbols-outlined">image</span> View attached image</a>
                            <?php endif; ?>
                            <?php if (!empty($request['Response'])): ?>
                                <div class="support-response"><strong>Previous response · <?php echo htmlspecialchars($request['Response_Date'] ? date('M j, Y g:i A', strtotime($request['Response_Date'])) : '', ENT_QUOTES, 'UTF-8'); ?></strong><p><?php echo nl2br(htmlspecialchars($request['Response'], ENT_QUOTES, 'UTF-8')); ?></p></div>
                            <?php endif; ?>
                        </div>
                        <form class="support-response-form" method="POST" action="../../controllers/admin/support_request_action.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="request_id" value="<?php echo (int)$request['Feedback_ID']; ?>">
                            <label for="status-<?php echo (int)$request['Feedback_ID']; ?>">Update status</label>
                            <select id="status-<?php echo (int)$request['Feedback_ID']; ?>" name="status" required>
                                <option value="In Progress" <?php echo $request['Status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Resolved" <?php echo $request['Status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="Rejected" <?php echo $request['Status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                            <label for="response-<?php echo (int)$request['Feedback_ID']; ?>">Response to user</label>
                            <textarea id="response-<?php echo (int)$request['Feedback_ID']; ?>" name="response" rows="4" maxlength="5000" required placeholder="Write a response visible to the requester."><?php echo htmlspecialchars($request['Response'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <button type="submit" class="support-submit-button"><span>Save response</span><span class="material-symbols-outlined">send</span></button>
                        </form>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<?php
include_once '../../includes/bottombar/bottombar_admin.php';
include_once '../../includes/footers/footer_common.php';
?>
<?php
// views/common/support.php
require_once '../../controllers/common/support_page_controller.php';

if ($role === 'admin') {
    require_once '../../includes/headers/header_admin.php';
} elseif ($role === 'instructor') {
    require_once '../../includes/headers/header_instructor.php';
} else {
    require_once '../../includes/headers/header_member.php';
}
?>
<link rel="stylesheet" href="../../assets/css/common/support.css">

<?php if ($role === 'admin'): ?>
<div class="admin-viewport-wrapper">
    <?php require_once '../../includes/sidebars/sidebar_admin.php'; ?>
    <main class="admin-main-canvas">
<?php elseif ($role === 'instructor'): ?>
<div class="admin-viewport-wrapper">
    <?php require_once '../../includes/sidebars/sidebar_instructor.php'; ?>
    <main class="admin-main-canvas">
<?php else: ?>
    <?php if ($is_captain): ?>
        <?php require_once '../../includes/sidebars/sidebar_captain.php'; ?>
    <?php else: ?>
        <?php require_once '../../includes/sidebars/sidebar_member.php'; ?>
    <?php endif; ?>
<div class="member-content-wrapper">
    <main class="dashboard-main-container">
<?php endif; ?>

        <section class="support-page">
            <header class="support-heading">
                <div>
                    <span class="support-eyebrow">FITCAMPUS / SUPPORT</span>
                    <h2>How can we help?</h2>
                    <p>Send a request to the administration team and follow its progress here.</p>
                </div>
                <?php if ($role === 'admin'): ?>
                    <a class="support-admin-link" href="../admin/support_requests.php">
                        <span class="material-symbols-outlined">inbox</span> Admin inbox
                    </a>
                <?php endif; ?>
            </header>

            <?php if (!empty($_SESSION['support_success'])): ?>
                <div class="support-alert support-alert-success" role="status">
                    <?php echo htmlspecialchars($_SESSION['support_success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['support_success']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['support_error'])): ?>
                <div class="support-alert support-alert-error" role="alert">
                    <?php echo htmlspecialchars($_SESSION['support_error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['support_error']); ?>
                </div>
            <?php endif; ?>
            <?php if ($support_error): ?>
                <div class="support-alert support-alert-error" role="alert"><?php echo htmlspecialchars($support_error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="support-layout">
                <section class="support-form-panel">
                    <h3>New request</h3>
                    <p class="support-panel-intro">Select the closest category. Your account information will be included automatically.</p>

                    <form action="../../controllers/common/support_request_action.php" method="POST" enctype="multipart/form-data" id="supportRequestForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                        <fieldset class="support-types">
                            <legend>Request type</legend>
                            <?php if (($support_profile['Role'] ?? '') === 'Student'): ?>
                            <label class="support-type-option">
                                <input type="radio" name="category" value="account_extension" required>
                                <span class="material-symbols-outlined">event_repeat</span>
                                <span><strong>Account extension</strong><small>Continuing studies or repeating a year</small></span>
                            </label>
                            <?php endif; ?>
                            <label class="support-type-option">
                                <input type="radio" name="category" value="life_bar_replenishment" required>
                                <span class="material-symbols-outlined">favorite</span>
                                <span><strong>Life bar replenishment</strong><small>Explain why your life bar needs review</small></span>
                            </label>
                            <label class="support-type-option">
                                <input type="radio" name="category" value="complaint_feedback" required>
                                <span class="material-symbols-outlined">chat</span>
                                <span><strong>Complaint or feedback</strong><small>Share feedback or another support request</small></span>
                            </label>
                        </fieldset>

                        <div class="support-account-summary">
                            <span class="material-symbols-outlined">account_circle</span>
                            <div>
                                <strong><?php echo htmlspecialchars(trim(($support_profile['First_Name'] ?? '') . ' ' . ($support_profile['Last_Name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span><?php echo htmlspecialchars($support_profile['Email'] ?? '', ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($support_profile['Registration_Number'])): ?> · <?php echo htmlspecialchars($support_profile['Registration_Number'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?><?php if (!empty($support_profile['Faculty'])): ?> · <?php echo htmlspecialchars($support_profile['Faculty'], ENT_QUOTES, 'UTF-8'); ?><?php endif; ?></span>
                            </div>
                        </div>

                        <div class="support-extension-fields" id="extensionFields" hidden>
                            <label for="requestedEndDate">Requested account end date <span>*</span></label>
                            <input type="date" id="requestedEndDate" name="requested_end_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            <p>Attach a clear photo of your new university ID. Administration will review the request before making any account changes.</p>
                        </div>

                        <div class="support-field">
                            <label for="supportDescription" id="descriptionLabel">Describe your request <span>*</span></label>
                            <textarea id="supportDescription" name="description" rows="6" maxlength="5000" required placeholder="Include the details the administration team needs to review your request."></textarea>
                        </div>

                        <div class="support-field">
                            <label for="supportAttachment">Photo attachment <span id="attachmentRequirement">Optional</span></label>
                            <input type="file" id="supportAttachment" name="attachment" accept="image/jpeg,image/png,image/webp">
                            <small id="attachmentHelp">JPEG, PNG or WEBP, up to 5 MB. Attachments are visible only to you and administrators.</small>
                        </div>

                        <button type="submit" class="support-submit-button">
                            <span>Send request</span><span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </form>
                </section>

                <section class="support-history-panel">
                    <div class="support-history-heading">
                        <div><h3>Your requests</h3><p>Replies from administrators appear here.</p></div>
                        <span class="support-count"><?php echo count($support_requests); ?></span>
                    </div>
                    <?php if (empty($support_requests)): ?>
                        <div class="support-empty-state"><span class="material-symbols-outlined">inbox</span><p>No requests yet.</p></div>
                    <?php else: ?>
                        <div class="support-request-list">
                            <?php foreach ($support_requests as $request): ?>
                                <?php
                                $categoryLabels = [
                                    'account_extension' => 'Account extension',
                                    'life_bar_replenishment' => 'Life bar replenishment',
                                    'complaint_feedback' => 'Complaint or feedback'
                                ];
                                $statusClass = strtolower(str_replace(' ', '-', $request['Status']));
                                ?>
                                <article class="support-request-card">
                                    <div class="support-request-topline">
                                        <span class="support-request-category"><?php echo htmlspecialchars($categoryLabels[$request['Category']] ?? $request['Category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="support-status support-status-<?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($request['Status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <p class="support-request-date">#<?php echo (int)$request['Feedback_ID']; ?> · <?php echo htmlspecialchars(date('M j, Y', strtotime($request['Submitted_Date'])), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php if (!empty($request['Requested_End_Date'])): ?>
                                        <p class="support-request-date">Requested end date: <?php echo htmlspecialchars(date('M j, Y', strtotime($request['Requested_End_Date'])), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                    <p class="support-request-description"><?php echo nl2br(htmlspecialchars($request['Description'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    <?php if (!empty($request['Attachment_Path'])): ?>
                                        <a class="support-attachment-link" href="../../controllers/common/support_attachment.php?id=<?php echo (int)$request['Feedback_ID']; ?>" target="_blank" rel="noopener">
                                            <span class="material-symbols-outlined">image</span> View attachment
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($request['Response'])): ?>
                                        <div class="support-response"><strong>Administrator response</strong><p><?php echo nl2br(htmlspecialchars($request['Response'], ENT_QUOTES, 'UTF-8')); ?></p></div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </section>
    </main>
</div>

<?php
if ($role === 'admin') {
    require_once '../../includes/bottombar/bottombar_admin.php';
} elseif ($role === 'instructor') {
    require_once '../../includes/bottombar/bottombar_instructor.php';
} elseif ($is_captain) {
    require_once '../../includes/bottombar/bottombar_captain.php';
} else {
    require_once '../../includes/bottombar/bottombar_member.php';
}
$extra_js = 'common/support.js';
require_once '../../includes/footers/footer_common.php';
?>
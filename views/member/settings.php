<?php
// views/member/settings.php

// Include Page Controller ONLY (No Direct DB Connections)
require_once '../../controllers/member/settings_page_controller.php';

require_once '../../includes/headers/header_member.php';

if ($is_captain) {
    require_once '../../includes/sidebars/sidebar_captain.php';
} else {
    require_once '../../includes/sidebars/sidebar_member.php';
}
?>

<div class="member-content-wrapper">
    <main class="dashboard-main-container">
        
        <div class="settings-header-wrap">
            <div>
                <h2 class="settings-main-title">Account Settings</h2>
                <p class="text-regular-sub">Manage your personal credentials and view institutional identity</p>
            </div>
            <a href="../../backend/auth/logout.php" class="btn btn-danger-action">
                <span class="material-symbols-outlined">logout</span>
                <span>Sign Out</span>
            </a>
        </div>

        <?php if (isset($_SESSION['settings_success'])): ?>
            <div class="alert-success">
                <span class="material-symbols-outlined fill">check_circle</span>
                <span><?php echo htmlspecialchars($_SESSION['settings_success']); unset($_SESSION['settings_success']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['settings_error'])): ?>
            <div class="alert-error">
                <span class="material-symbols-outlined">error</span>
                <span><?php echo htmlspecialchars($_SESSION['settings_error']); unset($_SESSION['settings_error']); ?></span>
            </div>
        <?php endif; ?>

        <div class="settings-grid-layout">
            
            <section class="glass-card settings-card">
                <div class="section-title-wrap">
                    <h3>Profile Photo</h3>
                </div>

                <div class="avatar-preview-container">
                    <div class="large-profile-avatar">
                        <?php if ($has_custom_avatar): ?>
                            <img src="<?php echo $avatar_path; ?>" alt="Profile Picture" class="avatar-img-fit">
                        <?php else: ?>
                            <span class="material-symbols-outlined avatar-fallback-icon">person</span>
                        <?php endif; ?>
                    </div>

                    <div class="avatar-action-info">
                        <div class="avatar-buttons-wrap">
                            <!-- Action path updated to controllers -->
                            <form action="../../controllers/member/update_avatar.php" method="POST" enctype="multipart/form-data" class="st-inline-block">
                                <input type="hidden" name="action" value="upload">
                                <label for="profile_image" class="btn btn-glass btn-sm upload-btn-label">
                                    <span class="material-symbols-outlined">photo_camera</span>
                                    <span><?php echo $has_custom_avatar ? 'Change Photo' : 'Upload Photo'; ?></span>
                                </label>
                                <input type="file" name="profile_image" id="profile_image" accept="image/jpeg,image/png,image/webp" class="file-hidden-input" onchange="this.form.submit()">
                            </form>

                            <?php if ($has_custom_avatar): ?>
                                <!-- Action path updated to controllers -->
                                <form action="../../controllers/member/update_avatar.php" method="POST" class="st-inline-block" onsubmit="return confirm('Are you sure you want to remove your profile photo?');">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="btn btn-danger-action btn-sm">
                                        <span class="material-symbols-outlined">delete</span>
                                        <span>Remove</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <span class="upload-sub">Allowed formats: JPG, PNG, WEBP (Max 2MB)</span>
                    </div>
                </div>
            </section>

            <section class="glass-card settings-card">
                <div class="section-title-wrap">
                    <h3>Security &amp; Password</h3>
                </div>
                <!-- Action path updated to controllers -->
                <form action="../../controllers/member/update_password.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">lock_clock</span>
                            <input type="password" name="current_password" id="current_password" class="form-control" required placeholder="••••••••••••" autocomplete="current-password">
                            <button type="button" class="btn-icon-right material-symbols-outlined password-toggle-btn" data-target="current_password" aria-label="Toggle current password visibility">visibility</button>
                        </div>
                    </div>

                    <div class="grid-2-col">
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-icon">lock_reset</span>
                                <input type="password" name="new_password" id="new_password" class="form-control" minlength="8" required placeholder="Min 8 characters" autocomplete="new-password">
                                <button type="button" class="btn-icon-right material-symbols-outlined password-toggle-btn" data-target="new_password" aria-label="Toggle new password visibility">visibility</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-icon">check</span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="8" required placeholder="Confirm new password" autocomplete="new-password">
                                <button type="button" class="btn-icon-right material-symbols-outlined password-toggle-btn" data-target="confirm_password" aria-label="Toggle confirm password visibility">visibility</button>
                            </div>
                        </div>
                    </div>

                    <div class="actions-group-row">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <span class="material-symbols-outlined">save</span>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </section>

            <section class="glass-card settings-card full-span-card">
                <div class="section-title-wrap">
                    <h3>Institutional Identity (Fixed)</h3>
                </div>
                <p class="text-dimmed-sub fixed-info-note">
                    * The following academic and identity details are synchronized with the university registry and cannot be altered directly.
                </p>

                <div class="grid-2-col">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">badge</span>
                            <input type="text" class="form-control read-only-input" value="<?php echo htmlspecialchars($member['First_Name'] . ' ' . $member['Last_Name']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Registration Number</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">pin</span>
                            <input type="text" class="form-control read-only-input" value="<?php echo htmlspecialchars($member['Registration_Number'] ?? 'N/A'); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Faculty</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">school</span>
                            <input type="text" class="form-control read-only-input" value="<?php echo htmlspecialchars($member['Faculty'] ?? 'N/A'); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Institutional Email</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">mail</span>
                            <input type="email" class="form-control read-only-input" value="<?php echo htmlspecialchars($member['Email']); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">National Identity Card (NIC)</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">fingerprint</span>
                            <input type="text" class="form-control read-only-input" value="<?php echo htmlspecialchars($member['NIC'] ?? 'N/A'); ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Membership Status</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">verified_user</span>
                            <input type="text" class="form-control read-only-input text-color-secondary" value="<?php echo ucfirst(htmlspecialchars($member['student_status'] ?? 'Active')); ?>" readonly>
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
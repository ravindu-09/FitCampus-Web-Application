<?php
// views/admin/settings.php
require_once '../../includes/db_connection.php';
require_once '../../bll/admin/SettingBLL.php';

$settingBLL = new SettingBLL($pdo);

$page_title = "Admin Settings | FitCampus";
$extra_js = "admin/settings.js";

// Fetch facilities dynamically via BLL instead of direct database queries
$facilities = $settingBLL->getFacilities();

require_once '../../includes/headers/header_admin.php';
?>

<div class="admin-viewport-wrapper">
    <?php include_once '../../includes/sidebars/sidebar_admin.php'; ?>

    <main class="admin-main-canvas">
        <div class="settings-header-wrap">
            <div>
                <h2 class="settings-main-title">System Settings</h2>
                <p class="text-regular-sub">Manage security, register staff, and manage facilities</p>
            </div>
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

        <script>const facilityData = <?php echo json_encode($facilities); ?>;</script>

        <div class="settings-grid-layout">
            
            <!-- SECTION 1: Password Change -->
            <section class="glass-card settings-card">
                <div class="section-title-wrap mb-4">
                    <h3>Security &amp; Password</h3>
                </div>
                <form action="../../controllers/admin/SettingController.php" method="POST">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group mb-4">
                        <label class="form-label">Current Password</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">lock_clock</span>
                            <input type="password" name="current_password" id="current_password" class="form-control" required placeholder="••••••••••••">
                            <button type="button" class="btn-icon-right material-symbols-outlined password-toggle-btn" data-target="current_password">visibility</button>
                        </div>
                    </div>

                    <div class="grid-2-col mb-4">
                        <div class="form-group">
                            <label class="form-label">New Password</label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-icon">lock_reset</span>
                                <input type="password" name="new_password" id="new_password" class="form-control" minlength="8" required>
                                <button type="button" class="btn-icon-right material-symbols-outlined password-toggle-btn" data-target="new_password">visibility</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-icon">check</span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="8" required>
                                <button type="button" class="btn-icon-right material-symbols-outlined password-toggle-btn" data-target="confirm_password">visibility</button>
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

            <!-- SECTION 2: Register Instructor -->
            <section class="glass-card settings-card border-left-secondary">
                <div class="section-title-wrap mb-4">
                    <h3 class="text-secondary">Register New Instructor</h3>
                </div>
                <form action="../../controllers/admin/SettingController.php" method="POST">
                    <input type="hidden" name="action" value="register_instructor">
                    
                    <div class="grid-2-col mb-4">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="grid-2-col mb-4">
                        <div class="form-group">
                            <label class="form-label">Instructor Email</label>
                            <div class="input-wrapper">
                                <span class="material-symbols-outlined input-icon">mail</span>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Assign Facility</label>
                            <select name="facility_id" class="form-control select-custom" required>
                                <option value="">-- Select Facility --</option>
                                <?php foreach($facilities as $fac): ?>
                                    <option value="<?php echo $fac['Facility_ID']; ?>"><?php echo htmlspecialchars($fac['Facility_Name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Default Password</label>
                        <div class="input-wrapper">
                            <span class="material-symbols-outlined input-icon">key</span>
                            <input type="password" name="default_password" id="inst_password" class="form-control" minlength="8" required>
                            <button type="button" class="btn-icon-right material-symbols-outlined password-toggle-btn" data-target="inst_password">visibility</button>
                        </div>
                    </div>

                    <div class="form-group mb-4 auth-box p-4">
                        <label class="form-label text-error">Admin Authorization Required</label>
                        <input type="password" name="admin_auth_password" class="form-control" placeholder="Enter YOUR admin password to confirm" required>
                    </div>

                    <div class="actions-group-row">
                        <button type="submit" class="btn btn-primary btn-sm" style="background: var(--secondary); color: var(--on-secondary);">
                            <span class="material-symbols-outlined">person_add</span>
                            <span>Register Instructor</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- SECTION 3: Add New Facility -->
            <section class="glass-card settings-card border-left-tertiary">
                <div class="section-title-wrap mb-4">
                    <h3 class="text-tertiary">Add New Facility</h3>
                </div>
                <form action="../../controllers/admin/SettingController.php" method="POST">
                    <input type="hidden" name="action" value="insert_facility">
                    
                    <div class="grid-2-col mb-4">
                        <div class="form-group">
                            <label class="form-label">Facility Name</label>
                            <input type="text" name="facility_name" class="form-control" placeholder="e.g. Indoor Badminton Court" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Location Details</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Main Sports Complex" required>
                        </div>
                    </div>

                    <div class="facility-grid-3 mb-4">
                        <div class="form-group">
                            <label class="form-label">Max Capacity</label>
                            <input type="number" name="capacity" class="form-control" placeholder="50" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Open Time</label>
                            <input type="time" name="open_time" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Close Time</label>
                            <input type="time" name="close_time" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group mb-4 auth-box p-4">
                        <label class="form-label text-error">Admin Authorization Required</label>
                        <input type="password" name="admin_auth_password" class="form-control" placeholder="Enter YOUR admin password to add facility" required>
                    </div>

                    <div class="actions-group-row">
                        <button type="submit" class="btn btn-primary btn-sm" style="background: var(--tertiary); color: var(--on-primary);">
                            <span class="material-symbols-outlined">add_business</span>
                            <span>Add Facility</span>
                        </button>
                    </div>
                </form>
            </section>

            <!-- SECTION 4: Update Existing Facility -->
            <section class="glass-card settings-card border-left-primary">
                <div class="section-title-wrap mb-4">
                    <h3 class="text-primary">Update Existing Facility</h3>
                </div>
                <form action="../../controllers/admin/SettingController.php" method="POST">
                    <input type="hidden" name="action" value="update_facility">
                    
                    <div class="form-group mb-4">
                        <label class="form-label">Select Facility to Edit</label>
                        <select name="facility_id" id="facilitySelect" class="form-control select-custom" required>
                            <option value="">-- Choose a Facility --</option>
                            <?php foreach($facilities as $fac): ?>
                                <option value="<?php echo $fac['Facility_ID']; ?>"><?php echo htmlspecialchars($fac['Facility_Name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid-2-col mb-4">
                        <div class="form-group">
                            <label class="form-label">Facility Name</label>
                            <input type="text" name="facility_name" id="fac_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Location Details</label>
                            <input type="text" name="location" id="fac_location" class="form-control" required>
                        </div>
                    </div>

                    <div class="facility-grid-3 mb-4">
                        <div class="form-group">
                            <label class="form-label">Max Capacity</label>
                            <input type="number" name="capacity" id="fac_capacity" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Open Time</label>
                            <input type="time" name="open_time" id="fac_open" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Close Time</label>
                            <input type="time" name="close_time" id="fac_close" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group mb-4 auth-box p-4">
                        <label class="form-label text-error">Admin Authorization Required</label>
                        <input type="password" name="admin_auth_password" class="form-control" placeholder="Enter YOUR admin password to confirm update" required>
                    </div>

                    <div class="actions-group-row">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <span class="material-symbols-outlined">domain_verification</span>
                            <span>Update Facility</span>
                        </button>
                    </div>
                </form>
            </section>

        </div>
    </main>
</div>

<?php 
include_once '../../includes/bottombar/bottombar_admin.php';
include_once '../../includes/footers/footer_common.php'; 
?>
<?php
session_start();

if (!isset($_SESSION['reg_step1']) || !isset($_SESSION['reg_step2'])) {
    header("Location: register_step1.php");
    exit();
}

$page_title = "FitCampus - Step 3: Identity Verification";
$extra_js = "auth/register-wizard.js";
require_once '../../includes/headers/header_auth.php';
?>

<!-- Wizard Header Bar -->
<header class="wizard-header">
    <div class="wizard-brand">
        <span class="material-symbols-outlined fill text-primary" style="font-size: 26px;">fitness_center</span>
        <h1>FitCampus</h1>
    </div>
    <div class="wizard-step-info">
        <span class="wizard-step-badge">Step 3 of 3</span>
        <span class="wizard-step-name">Verification</span>
    </div>
</header>

<main class="wizard-main">
    <div class="progress-track">
        <div class="progress-fill" style="width: 100%;"></div>
    </div>

    <div class="step-heading">
        <h2>Final Verification</h2>
        <p>Confirm your identity to unlock university athletic facilities and personalized training programs.</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-error">
            <span class="material-symbols-outlined">error</span>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <form action="../../backend/auth/register_process.php?step=3" method="POST" enctype="multipart/form-data" class="auth-form" id="step3Form">
        
        <!-- ID Verification Section -->
        <section class="form-section-card">
            <div class="section-title-wrap">
                <span class="material-symbols-outlined">badge</span>
                <h3>Student ID Verification</h3>
            </div>
            <p class="form-label" style="margin-bottom: 12px; text-transform: uppercase;">Please upload clear photos of your valid University of Colombo ID card.</p>

            <div class="upload-card-box" onclick="document.getElementById('id_front').click()">
                <input type="file" id="id_front" name="id_front" accept="image/*" class="file-hidden-input" required>
                <div class="upload-dashed-area" id="box_front">
                    <span class="material-symbols-outlined upload-icon">add_a_photo</span>
                    <div>
                        <p class="upload-title" id="txt_front">Front View</p>
                        <p class="upload-sub">PNG, JPG up to 5MB</p>
                    </div>
                </div>
            </div>

            <div class="upload-card-box" onclick="document.getElementById('id_back').click()">
                <input type="file" id="id_back" name="id_back" accept="image/*" class="file-hidden-input" required>
                <div class="upload-dashed-area" id="box_back">
                    <span class="material-symbols-outlined upload-icon">flip_camera_ios</span>
                    <div>
                        <p class="upload-title" id="txt_back">Back View</p>
                        <p class="upload-sub">Clear barcode visible</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Personal Profile Photo -->
        <section class="form-section-card">
            <div class="section-title-wrap">
                <span class="material-symbols-outlined">account_circle</span>
                <h3>Student Profile Photo</h3>
            </div>
            <p class="form-label" style="margin-bottom: 12px; text-transform: uppercase;">Please upload a clear photo where your face is clearly visible.</p>

            <div class="upload-card-box" onclick="document.getElementById('profile_image').click()">
                <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-hidden-input" required>
                <div class="upload-dashed-area" id="box_profile">
                    <span class="material-symbols-outlined upload-icon">face</span>
                    <div>
                        <p class="upload-title" id="txt_profile">Profile Photo</p>
                        <p class="upload-sub">PNG, JPG up to 5MB</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Personal Identity (NIC) -->
        <section class="form-section-card">
            <div class="section-title-wrap">
                <span class="material-symbols-outlined">fingerprint</span>
                <h3>Personal Identity</h3>
            </div>
            <div class="form-group">
                <label for="nic" class="form-label">NIC NUMBER</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">pin</span>
                    <input type="text" id="nic" name="nic" class="form-control" placeholder="e.g. 200012345678" required>
                </div>
            </div>
        </section>

        <div class="wizard-actions">
            <button id="submitRegBtn" type="submit" class="btn-primary-action animate-pulse-cta">
                <span>Complete Registration</span>
                <span class="material-symbols-outlined font-bold fill">check_circle</span>
            </button>
            <a href="register_step2.php" class="btn-tertiary-back">
                <span class="material-symbols-outlined">chevron_left</span>
                <span>Back to Previous Page</span>
            </a>
            <p style="text-align: center; font-size: 11px; font-family: var(--font-mono); color: rgba(207, 194, 212, 0.6); margin-top: 8px;">
                By completing registration, you agree to the University Sports Council facility regulations and code of conduct.
            </p>
        </div>
    </form>
</main>

<?php require_once '../../includes/footers/footer_common.php'; ?>
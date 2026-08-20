<?php
session_start();

if (!isset($_SESSION['reg_step1'])) {
    header("Location: register_step1.php");
    exit();
}

$page_title = "FitCampus - Step 2: Profile & Security";
$extra_js = "auth/register-wizard.js";
require_once '../../includes/headers/header_auth.php';

$step2_data = $_SESSION['reg_step2'] ?? [];
?>

<!-- Wizard Header Bar -->
<header class="wizard-header">
    <div class="wizard-brand">
        <span class="material-symbols-outlined fill text-primary" style="font-size: 26px;">fitness_center</span>
        <h1>FitCampus</h1>
    </div>
    <div class="wizard-step-info">
        <span class="wizard-step-badge">Step 2 of 3</span>
        <span class="wizard-step-name">Profile & Security</span>
    </div>
</header>

<main class="wizard-main">
    <div class="progress-track">
        <div class="progress-fill" style="width: 66.66%;"></div>
    </div>

    <div class="step-heading">
        <h2>Profile & Security</h2>
        <p>Complete your personal profile and secure your account to finish registration.</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-error">
            <span class="material-symbols-outlined">error</span>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <form action="../../backend/auth/register_process.php?step=2" method="POST" class="auth-form" id="step2Form">
        
        <!-- Personal Details -->
        <section class="form-section-card">
            <div class="section-title-wrap">
                <span class="material-symbols-outlined">person</span>
                <h3>Personal Details</h3>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="dob" class="form-label">DATE OF BIRTH</label>
                <div class="input-wrapper">
                    <input 
                        type="date" 
                        id="dob" 
                        name="dob" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($step2_data['dob'] ?? ''); ?>" 
                        required
                    >
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">GENDER</label>
                <div class="radio-group-wrap">
                    <label class="radio-option">
                        <input type="radio" name="gender" value="male" class="radio-custom" <?php echo ($step2_data['gender'] ?? '') === 'male' ? 'checked' : ''; ?> required>
                        <span>Male</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="gender" value="female" class="radio-custom" <?php echo ($step2_data['gender'] ?? '') === 'female' ? 'checked' : ''; ?>>
                        <span>Female</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="gender" value="other" class="radio-custom" <?php echo ($step2_data['gender'] ?? '') === 'other' ? 'checked' : ''; ?>>
                        <span>Other</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="emergency_contact" class="form-label">EMERGENCY CONTACT (NAME & PHONE)</label>
                <div class="input-wrapper">
                    <span class="material-symbols-outlined input-icon">emergency</span>
                    <input 
                        type="text" 
                        id="emergency_contact" 
                        name="emergency_contact" 
                        class="form-control" 
                        placeholder="e.g. John Doe - +94 77 123 4567" 
                        value="<?php echo htmlspecialchars($step2_data['emergency_contact'] ?? ''); ?>" 
                        required
                    >
                </div>
            </div>
        </section>

        <!-- Security -->
        <section class="form-section-card">
            <div class="section-title-wrap">
                <span class="material-symbols-outlined">lock</span>
                <h3>Security</h3>
            </div>

            <div class="grid-2-col">
                <div class="form-group">
                    <label for="password" class="form-label">PASSWORD</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">password</span>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Min. 8 characters" required>
                        <button 
                            type="button" 
                            id="togglePassword" 
                            class="btn-icon-right material-symbols-outlined"
                            aria-label="Toggle password visibility"
                        >visibility</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">CONFIRM PASSWORD</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">verified_user</span>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                        <button 
                            type="button" 
                            id="toggleConfirmPassword" 
                            class="btn-icon-right material-symbols-outlined"
                            aria-label="Toggle password visibility"
                        >visibility</button>
                    </div>
                </div>
            </div>

            <div class="info-banner">
                <span class="material-symbols-outlined" style="font-size: 18px;">info</span>
                <span>Ensure your password contains at least one capital letter, one number, and one special character.</span>
            </div>
        </section>

        <!-- Terms -->
        <label class="checkbox-label" style="align-items: flex-start; gap: 10px;">
            <input type="checkbox" name="terms" class="checkbox-custom" style="margin-top: 3px;" required>
            <span class="checkbox-text" style="line-height: 1.4;">
                I agree to the FitCampus <a href="#" class="forgot-link">Terms of Service</a> and <a href="#" class="forgot-link">Facility Rules</a> of the University of Colombo.
            </span>
        </label>

        <div class="wizard-actions">
            <button type="submit" class="btn-primary-action animate-pulse-cta">
                <span>Next Step</span>
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
            <a href="register_step1.php" class="btn-tertiary-back">
                <span class="material-symbols-outlined">chevron_left</span>
                <span>Back to Previous Page</span>
            </a>
        </div>
    </form>
</main>

<?php require_once '../../includes/footers/footer_common.php'; ?>
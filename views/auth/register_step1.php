<?php
session_start();

$page_title = "FitCampus - Step 1: Academic Credentials";
$extra_js = "auth/register-wizard.js";
require_once '../../includes/headers/header_auth.php';

$step1_data = $_SESSION['reg_step1'] ?? [];
?>

<!-- Wizard Header Bar -->
<header class="wizard-header">
    <div class="wizard-brand">
        <span class="material-symbols-outlined fill text-primary" style="font-size: 26px;">fitness_center</span>
        <h1>FitCampus</h1>
    </div>
    <div class="wizard-step-info">
        <span class="wizard-step-badge">Step 1 of 3</span>
        <span class="wizard-step-name">Academic Info</span>
    </div>
</header>

<main class="wizard-main">
    <div class="progress-track">
        <div class="progress-fill" style="width: 33.33%;"></div>
    </div>

    <div class="step-heading">
        <h2>Academic Credentials</h2>
        <p>Verify your student or faculty status to access the campus gym network.</p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-error">
            <span class="material-symbols-outlined">error</span>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
    <?php endif; ?>

    <form action="../../backend/auth/register_process.php?step=1" method="POST" class="auth-form" id="step1Form">
        <div class="form-group">
            <label for="full_name" class="form-label">Full Name</label>
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    class="form-control" 
                    placeholder="Enter your full legal name" 
                    value="<?php echo htmlspecialchars($step1_data['full_name'] ?? ''); ?>" 
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="reg_no" class="form-label">Registration Number</label>
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="reg_no" 
                    name="reg_no" 
                    class="form-control" 
                    placeholder="e.g. 2021/CS/045" 
                    value="<?php echo htmlspecialchars($step1_data['reg_no'] ?? ''); ?>" 
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">University Email</label>
            <div class="input-wrapper">
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="name@stu.cmb.ac.lk" 
                    value="<?php echo htmlspecialchars($step1_data['email'] ?? ''); ?>" 
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="faculty" class="form-label">Faculty</label>
            <div class="input-wrapper">
                <select id="faculty" name="faculty" class="form-control form-select" required>
                    <option disabled <?php echo empty($step1_data['faculty']) ? 'selected' : ''; ?> value="">Select your faculty</option>
                    <option value="Faculty of Science" <?php echo ($step1_data['faculty'] ?? '') === 'Faculty of Science' ? 'selected' : ''; ?>>Faculty of Science</option>
                    <option value="Faculty of Management" <?php echo ($step1_data['faculty'] ?? '') === 'Faculty of Management' ? 'selected' : ''; ?>>Faculty of Management</option>
                    <option value="Faculty of Arts" <?php echo ($step1_data['faculty'] ?? '') === 'Faculty of Arts' ? 'selected' : ''; ?>>Faculty of Arts</option>
                    <option value="School of Computing" <?php echo ($step1_data['faculty'] ?? '') === 'School of Computing' ? 'selected' : ''; ?>>School of Computing</option>
                    <option value="Faculty of Law" <?php echo ($step1_data['faculty'] ?? '') === 'Faculty of Law' ? 'selected' : ''; ?>>Faculty of Law</option>
                </select>
            </div>
        </div>

        <div class="wizard-actions">
            <button type="submit" class="btn-primary-action animate-pulse-cta">
                <span>Next Step</span>
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
            <a href="login.php" class="btn-tertiary-back">
                <span class="material-symbols-outlined">chevron_left</span>
                <span>Back to Login</span>
            </a>
        </div>
    </form>
</main>

<?php require_once '../../includes/footers/footer_common.php'; ?>
<?php

session_start();

// Active session check (Role-specific redirection)
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        header("Location: ../admin/analytics.php");
        exit();
    } elseif ($role === 'member') {
        header("Location: ../member/dashboard.php");
        exit();
    } elseif ($role === 'instructor') {
        header("Location: ../instructor/dashboard.php");
        exit();
    }
}

$page_title = "FitCampus - Institutional Portal Login";
$extra_js = "auth/auth.js";
require_once '../../includes/headers/header_auth.php';
?>

    <!-- Atmospheric Glows -->
    <div class="purple-glow top-left"></div>
    <div class="purple-glow bottom-right"></div>

    <!-- Main Content Canvas -->
    <main class="auth-main">
        
        <!-- Branding Header with Local Image Logo -->
        <header class="auth-header">
            <div class="brand-icon-box">
                <img src="../../assets/images/uoc-logo.png" alt="University of Colombo Crest">
            </div>
            <h1 class="auth-title">
                FitCampus <span>Institutional Portal</span>
            </h1>
            <p class="auth-subtitle">University of Colombo</p>
        </header>

        <!-- Glassmorphic Login Card -->
        <div class="glass-card auth-card">
            
            <!-- Registration Success Notification -->
            <?php if (isset($_SESSION['registration_success'])): ?>
                <div class="alert-success">
                    <span class="material-symbols-outlined fill">check_circle</span>
                    <span><?php echo htmlspecialchars($_SESSION['registration_success']); unset($_SESSION['registration_success']); ?></span>
                </div>
            <?php endif; ?>

            <!-- Error Notification -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-error">
                    <span class="material-symbols-outlined">error</span>
                    <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>

            <form action="../../backend/auth/login_process.php" method="POST" id="loginForm" class="auth-form">
                
                <div class="form-group">
                    <label for="identifier" class="form-label">Institutional Email / Reg No</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">mail</span>
                        <input 
                            type="text" 
                            id="identifier" 
                            name="identifier" 
                            class="form-control" 
                            placeholder="username@stu.cmb.ac.lk / 2022cs001" 
                            required 
                            autocomplete="username"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Portal Password</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control" 
                            placeholder="••••••••••••" 
                            required 
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            id="togglePassword" 
                            class="btn-icon-right material-symbols-outlined"
                            aria-label="Toggle password visibility"
                        >visibility</button>
                    </div>
                </div>

                <div class="form-meta">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me" class="checkbox-custom">
                        <span class="checkbox-text">Remember Session</span>
                    </label>
                    <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                </div>

                <div class="actions-group">
                    <button id="submitBtn" type="submit" class="btn-primary-action animate-pulse-cta">
                        <span>Sign In / Login</span>
                        <span class="material-symbols-outlined font-bold">login</span>
                    </button>

                    <a href="register_step1.php" class="btn-secondary-action">
                        <span>Sign Up</span>
                        <span class="material-symbols-outlined font-bold">person_add</span>
                    </a>

                    <a href="../../index.php" class="btn-tertiary-back">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <span>Back to Home</span>
                    </a>
                </div>
            </form>

        </div>
    </main>

<?php require_once '../../includes/footers/footer_common.php'; ?>
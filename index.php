<?php
// index.php - Public Landing Page & Route Dispatcher
session_start();

// For Active session, auto-redirect to Dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'instructor':
            header("Location: views/instructor/kiosk.php");
            exit();
        case 'member':
            header("Location: views/member/dashboard.php");
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitCampus - Department of Physical Education | University of Colombo</title>
    
    <!-- Modular Vanilla CSS Links -->
     <link rel="stylesheet" href="assets/css/base/main.css">
    <link rel="stylesheet" href="assets/css/base/components.css">
    <link rel="stylesheet" href="assets/css/base/glassmorphism.css">
    <link rel="stylesheet" href="assets/css/roles/index.css">
</head>
<body>

    <!-- Background Ambient Glows -->
    <div class="purple-glow top-left"></div>
    <div class="purple-glow bottom-right"></div>

    <!-- Top App Navigation Header -->
    <header class="landing-header">
        <div class="nav-container">
            <div class="brand-container">
                <span class="material-symbols-outlined brand-icon fill">fitness_center</span>
                <span class="brand-title">FitCampus</span>
            </div>

            <nav class="desktop-nav">
                <a href="#hero" class="nav-link active">Home</a>
                <a href="#facilities" class="nav-link">Facilities</a>
                <a href="#info" class="nav-link">Info</a>
            </nav>

            <div class="header-actions">
                <a href="views/auth/login.php" class="btn btn-primary btn-sm">Portal Login</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Hero Section -->
        <section id="hero" class="hero-section">
            <div class="hero-content">
                <div class="badge-pill">
                    <!-- <span class="status-dot"></span> -->
                    <span class="badge-text">University of Colombo</span>
                </div>
                
                <h1 class="hero-title">
                    Elevate Your <br>
                    <span class="gradient-text">Athletic Journey</span>
                </h1>
                
                <p class="hero-subtitle">
                    Experience elite training facilities designed for peak university performance. Join a community driven by discipline, sports science, and dedicated coaching.
                </p>
                
                <div class="hero-cta-group">
                    <a href="views/auth/register_step1.php" class="btn btn-primary btn-lg">
                        <span>Join Now</span>
                        <span class="material-symbols-outlined btn-arrow">arrow_forward</span>
                    </a>
                    <a href="views/auth/login.php" class="btn btn-glass btn-lg">
                        Member Sign In
                    </a>
                </div>
            </div>
        </section>

        <!-- Facilities Section -->
        <section id="facilities" class="section-container">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Elite Facilities</h2>
                    <p class="section-subtitle">Engineered for every athletic discipline.</p>
                </div>
            </div>

            <div class="facilities-grid">
                <!-- Gym 01 Card -->
                <div class="facility-card glass-card">
                    <div class="facility-body">
                        <div class="facility-badge-row">
                            <span class="badge-status open">
                                <span class="status-dot"></span> Open
                            </span>
                            <span class="badge-tag">Gym 01</span>
                        </div>
                        
                        <h3 class="facility-card-title">Strength &amp; Conditioning</h3>
                        <p class="facility-desc">
                            The home of varsity powerlifters and strength athletes. Equipped with heavy-duty power racks, Olympic lifting platforms, and free weights to build raw power.
                        </p>
                        
                        <div class="facility-features">
                            <span class="feature-icon material-symbols-outlined">fitness_center</span>
                            <span class="feature-icon material-symbols-outlined">timer</span>
                        </div>
                    </div>
                </div>

                <!-- Gym 02 Card -->
                <div class="facility-card glass-card">
                    <div class="facility-body">
                        <div class="facility-badge-row">
                            <span class="badge-status open">
                                <span class="status-dot"></span> Open
                            </span>
                            <span class="badge-tag">Gym 02</span>
                        </div>
                        
                        <h3 class="facility-card-title">Cardio &amp; Performance</h3>
                        <p class="facility-desc">
                            A high-energy zone engineered for cardiovascular endurance. Featuring commercial treadmills, smart rowers, and indoor cycles with real-time tracking metrics.
                        </p>
                        
                        <div class="facility-features">
                            <span class="feature-icon material-symbols-outlined">monitor_heart</span>
                            <span class="feature-icon material-symbols-outlined">directions_run</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Info & Operational Benefits -->
        <section id="info" class="info-section">
            <div class="info-grid">
                <div class="info-card glass-card">
                    <div class="info-icon-box purple">
                        <span class="material-symbols-outlined">schedule</span>
                    </div>
                    <div>
                        <h4 class="info-card-title">Operating Hours</h4>
                        <div class="info-mono-tag">Mon - Sun: 06:00 - 20:00</div>
                    </div>
                </div>

                <div class="info-card glass-card">
                    <div class="info-icon-box green">
                        <span class="material-symbols-outlined">sports</span>
                    </div>
                    <div>
                        <h4 class="info-card-title">Professional Coaching</h4>
                        <p class="info-desc">Access to national-level trainers and customized academic conditioning plans.</p>
                    </div>
                </div>

                <div class="info-card glass-card">
                    <div class="info-icon-box purple">
                        <span class="material-symbols-outlined">monitoring</span>
                    </div>
                    <div>
                        <h4 class="info-card-title">Performance Tracking</h4>
                        <p class="info-desc">Data-driven tracking to log progressive overload, calorie burn, and team capacity.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

<!-- Global Footer -->
<?php require_once 'includes/footers/footer_common.php'; ?>
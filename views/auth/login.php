<?php 
$pageTitle = "FitCampus - Institutional Portal Login";
include '../../includes/headers/header_auth.php'; 
?>

<!-- Atmospheric Glows -->
<div class="purple-glow top-[-100px] left-[-50px]"></div>
<div class="purple-glow bottom-[-100px] right-[-50px]"></div>

<!-- Main Content Canvas -->
<main class="flex-grow flex flex-col items-center justify-center px-margin-mobile py-10 relative z-10">
    
    <!-- Branding Header -->
    <header class="mb-8 text-center">
        <div class="w-20 h-20 mx-auto mb-4 bg-primary-container/20 rounded-full flex items-center justify-center border border-primary/20">
            <span class="material-symbols-outlined text-primary text-5xl" style="font-variation-settings: 'FILL' 1;">fitness_center</span>
        </div>
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile text-on-background tracking-tight">
            FitCampus <span class="text-primary/80">Institutional Portal</span>
        </h1>
        <p class="font-label-sm text-label-sm text-on-surface-variant/60 mt-1 uppercase">University of Colombo</p>
    </header>

    <!-- Glassmorphic Login Card -->
    <div class="glass-card w-full max-w-md rounded-xl p-8 border border-white/10">
        <form class="space-y-6" action="../../backend/auth/login_process.php" method="POST">
            
            <div class="space-y-1.5">
                <label class="block font-label-sm text-label-sm text-on-surface-variant/80 ml-1">INSTITUTIONAL EMAIL</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/40">mail</span>
                    <input name="email" class="w-full bg-surface-container-low border border-outline-variant/40 rounded-lg py-3.5 pl-12 pr-4 text-on-surface placeholder-on-surface-variant/30 focus:border-primary/50 transition-all" placeholder="username@stu.cmb.ac.lk" type="email" required>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block font-label-sm text-label-sm text-on-surface-variant/80 ml-1">PORTAL PASSWORD</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/40">lock</span>
                    <input name="password" class="w-full bg-surface-container-low border border-outline-variant/40 rounded-lg py-3.5 pl-12 pr-4 text-on-surface placeholder-on-surface-variant/30 focus:border-primary/50 transition-all" placeholder="••••••••••••" type="password" required>
                </div>
            </div>

            <div class="flex items-center justify-between py-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input name="remember" class="w-4 h-4 rounded border-outline-variant/60 bg-surface-container text-primary focus:ring-primary/20" type="checkbox">
                    <span class="font-label-sm text-label-sm text-on-surface-variant/60">Remember Session</span>
                </label>
                <a class="font-label-sm text-label-sm text-primary hover:text-primary-fixed-dim transition-colors" href="#">Forgot Password?</a>
            </div>

            <div class="flex flex-col gap-4 mt-2">
                <button id="loginSubmitBtn" class="animate-pulse-cta w-full bg-primary-container text-white font-title-md py-5 rounded-2xl flex items-center justify-center gap-3 active:scale-[0.97] transition-all shadow-lg shadow-primary-container/20 group relative overflow-hidden" type="submit">
                    <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span>Sign In / Login</span>
                    <span class="material-symbols-outlined font-bold">login</span>
                </button>
                
                <a href="register_step1.php" class="w-full bg-primary-container text-white font-title-md py-5 rounded-2xl flex items-center justify-center gap-3 active:scale-[0.97] transition-all shadow-lg shadow-primary-container/20 group relative overflow-hidden text-center">
                    <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span>Sign Up</span>
                    <span class="material-symbols-outlined font-bold">person_add</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Footer Component -->
    <?php include '../../includes/footers/footer_common.php'; ?>
</main>

<script src="../../assets/js/auth/auth.js"></script>
</body>
</html>
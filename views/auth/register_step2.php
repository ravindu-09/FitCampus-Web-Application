<?php 
$pageTitle = "FitCampus - Registration (Step 2 of 3)";
include '../../includes/headers/header_auth.php'; 
?>

<!-- Top Navigation -->
<header class="fixed top-0 w-full h-[70px] z-50 bg-background/80 backdrop-blur-md flex justify-between items-center px-4 md:px-gutter border-b border-outline-variant/10">
    <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[28px]" style="font-variation-settings: 'FILL' 1;">fitness_center</span>
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile font-bold text-on-surface">FitCampus</h1>
    </div>
    <div class="flex flex-col items-end">
        <span class="font-label-sm text-label-sm text-primary uppercase tracking-widest">Step 2 of 3</span>
        <span class="font-body-md text-[14px] text-on-surface-variant">Profile & Security</span>
    </div>
</header>

<!-- Main Content -->
<main class="w-full max-w-lg mx-auto mt-[100px] mb-24 px-4 md:px-0 text-on-background">
    <!-- Progress Bar -->
    <div class="w-full h-1 bg-surface-container-highest rounded-full mb-10 overflow-hidden">
        <div class="h-full bg-primary transition-all duration-700 ease-out" style="width: 66.66%;"></div>
    </div>

    <form class="space-y-6" action="register_step3.php" method="GET">
        <div class="mb-8">
            <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Profile & Security</h2>
            <p class="font-body-md text-on-surface-variant">Complete your personal profile and secure your account to finish registration.</p>
        </div>

        <!-- Section 2: Personal Details -->
        <section class="glass-card p-6 rounded-xl space-y-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-primary">person</span>
                <h2 class="font-title-md text-title-md text-on-surface">Personal Details</h2>
            </div>
            
            <div class="space-y-6">
                <!-- Date of Birth -->
                <div class="space-y-2">
                    <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">DATE OF BIRTH</label>
                    <input name="dob" class="w-full bg-surface-container-lowest border-outline-variant/20 rounded-lg py-3 px-4 text-on-surface focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all outline-none" type="date" required>
                </div>

                <!-- Gender Selection -->
                <div class="space-y-2">
                    <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">GENDER</label>
                    <div class="flex items-center gap-6 py-2">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input class="custom-radio w-5 h-5 bg-surface-container border-outline-variant text-primary focus:ring-primary" name="gender" type="radio" value="male" checked>
                            <span class="text-on-surface group-hover:text-primary transition-colors">Male</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input class="custom-radio w-5 h-5 bg-surface-container border-outline-variant text-primary focus:ring-primary" name="gender" type="radio" value="female">
                            <span class="text-on-surface group-hover:text-primary transition-colors">Female</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input class="custom-radio w-5 h-5 bg-surface-container border-outline-variant text-primary focus:ring-primary" name="gender" type="radio" value="other">
                            <span class="text-on-surface group-hover:text-primary transition-colors">Other</span>
                        </label>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="space-y-2">
                    <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">EMERGENCY CONTACT (NAME & PHONE)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-outline material-symbols-outlined">emergency</span>
                        <input name="emergency_contact" class="w-full bg-surface-container-lowest border-outline-variant/20 rounded-lg py-3 pl-12 pr-4 text-on-surface focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all outline-none" placeholder="e.g. John Doe - +94 77 123 4567" type="text" required>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Security -->
        <section class="glass-card p-6 rounded-xl space-y-6">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-primary">lock</span>
                <h2 class="font-title-md text-title-md text-on-surface">Security</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">PASSWORD</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-outline material-symbols-outlined">password</span>
                        <input name="password" class="w-full bg-surface-container-lowest border-outline-variant/20 rounded-lg py-3 pl-12 pr-4 text-on-surface focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all outline-none" placeholder="Min. 8 characters" type="password" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="font-label-sm text-label-sm text-on-surface-variant ml-1">CONFIRM PASSWORD</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-outline material-symbols-outlined">verified_user</span>
                        <input name="confirm_password" class="w-full bg-surface-container-lowest border-outline-variant/20 rounded-lg py-3 pl-12 pr-4 text-on-surface focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all outline-none" placeholder="Repeat password" type="password" required>
                    </div>
                </div>
            </div>
            
            <div class="p-4 bg-primary-container/10 border-l-2 border-primary rounded-r-lg mt-4">
                <p class="text-on-primary-container font-body-md text-sm flex gap-3">
                    <span class="material-symbols-outlined text-sm shrink-0">info</span>
                    <span>Ensure your password contains at least one capital letter, one number, and one special character.</span>
                </p>
            </div>
        </section>

        <!-- Terms & Navigation Buttons -->
        <div class="flex flex-col items-center gap-4 pt-4">
            <label class="flex items-start gap-3 cursor-pointer max-w-lg px-2 mb-4">
                <div class="mt-1">
                    <input class="w-5 h-5 rounded border-outline-variant/30 bg-surface-container-lowest text-primary focus:ring-primary cursor-pointer" type="checkbox" required>
                </div>
                <span class="text-on-surface-variant font-body-md text-sm leading-relaxed">
                    I agree to the FitCampus <a class="text-primary hover:underline" href="#">Terms of Service</a> and <a class="text-primary hover:underline" href="#">Facility Rules</a> of the University of Colombo.
                </span>
            </label>

            <button class="w-full bg-primary text-on-primary font-bold py-4 rounded-lg shadow-lg active:scale-[0.98] transition-transform flex items-center justify-center gap-2 hover:bg-opacity-90 selection:text-white" type="submit">
                Next Step <span class="material-symbols-outlined">arrow_forward</span>
            </button>
            
            <a href="register_step1.php" class="w-full border border-tertiary text-tertiary font-bold py-4 rounded-lg active:scale-[0.98] transition-transform flex items-center justify-center gap-2 hover:bg-tertiary/10 text-center">
                <span class="material-symbols-outlined">chevron_left</span>
                Back to Previous Page
            </a>
        </div>
    </form>
</main>

<?php include '../../includes/footers/footer_common.php'; ?>
<script src="../../assets/js/auth/auth.js"></script>
</body>
</html>
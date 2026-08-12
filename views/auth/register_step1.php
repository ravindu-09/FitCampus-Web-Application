<?php 
$pageTitle = "FitCampus - Registration (Step 1 of 3)";
include '../../includes/headers/header_auth.php'; 
?>

<!-- Header Bar -->
<header class="fixed top-0 w-full h-[70px] z-50 bg-background/80 backdrop-blur-md border-b border-outline-variant/10 flex items-center justify-between px-margin-mobile md:px-margin-desktop">
    <div class="flex items-center gap-2">
        <span class="material-symbols-outlined text-primary text-[28px]" style="font-variation-settings: 'FILL' 1;">fitness_center</span>
        <h1 class="font-headline-lg-mobile text-headline-lg-mobile font-bold text-on-surface">FitCampus</h1>
    </div>
    <div class="flex flex-col items-end">
        <span class="font-label-sm text-label-sm text-primary uppercase tracking-widest">Step 1 of 3</span>
        <span class="font-body-md text-[14px] text-on-surface-variant">Academic Info</span>
    </div>
</header>

<!-- Main Content -->
<main class="relative z-10 pt-[100px] pb-[120px] px-margin-mobile md:px-margin-desktop flex flex-col items-center w-full">
    <div class="w-full max-w-md mx-auto">
        <!-- Progress Bar -->
        <div class="w-full h-1 bg-surface-container rounded-full mb-10 overflow-hidden">
            <div class="h-full bg-primary transition-all duration-700 ease-out" style="width: 33.33%;"></div>
        </div>

        <form class="space-y-6" id="registration-form" action="register_step2.php" method="GET">
            <section class="step-transition opacity-100" id="section-1">
                <div class="mb-8">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Academic Credentials</h2>
                    <p class="font-body-md text-on-surface-variant">Verify your student or faculty status to access the campus gym network.</p>
                </div>

                <div class="space-y-5">
                    <div class="group">
                        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2 ml-1">Full Name</label>
                        <input name="fullname" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-lg py-3.5 px-4 text-on-surface placeholder:text-on-surface-variant/40 focus:border-primary-container focus:ring-1 focus:ring-primary-container outline-none transition-all duration-200" placeholder="Enter your full legal name" type="text" required>
                    </div>

                    <div class="group">
                        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2 ml-1">Registration Number</label>
                        <input name="reg_no" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-lg py-3.5 px-4 text-on-surface placeholder:text-on-surface-variant/40 focus:border-primary-container focus:ring-1 focus:ring-primary-container outline-none transition-all duration-200" placeholder="e.g. 2021/CS/045" type="text" required>
                    </div>

                    <div class="group">
                        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2 ml-1">University Email</label>
                        <input name="email" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-lg py-3.5 px-4 text-on-surface placeholder:text-on-surface-variant/40 focus:border-primary-container focus:ring-1 focus:ring-primary-container outline-none transition-all duration-200" placeholder="name@stu.cmb.ac.lk" type="email" required>
                    </div>

                    <div class="group">
                        <label class="block font-label-sm text-label-sm text-on-surface-variant mb-2 ml-1">Faculty</label>
                        <div class="relative">
                            <select name="faculty" class="w-full bg-surface-container-low border border-outline-variant/30 rounded-lg py-3.5 px-4 text-on-surface appearance-none focus:border-primary-container focus:ring-1 focus:ring-primary-container outline-none transition-all duration-200" required>
                                <option disabled selected value="">Select your faculty</option>
                                <option value="science">Faculty of Science</option>
                                <option value="management">Faculty of Management</option>
                                <option value="arts">Faculty of Arts</option>
                                <option value="ucsc">School of Computing</option>
                                <option value="law">Faculty of Law</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">expand_more</span>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex flex-col gap-4">
                    <button class="w-full bg-primary text-on-primary font-bold py-4 rounded-lg shadow-lg active:scale-[0.98] transition-transform flex items-center justify-center gap-2 hover:bg-opacity-90 selection:text-white" type="submit">
                        Next Step
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                    
                    <a href="login.php" class="w-full border border-tertiary text-tertiary font-bold py-4 rounded-lg active:scale-[0.98] transition-transform flex items-center justify-center gap-2 hover:bg-tertiary/10 text-center">
                        <span class="material-symbols-outlined">chevron_left</span>
                        Back to Login
                    </a>
                </div>
            </section>
        </form>
    </div>
</main>

<?php include '../../includes/footers/footer_common.php'; ?>
</body>
</html>
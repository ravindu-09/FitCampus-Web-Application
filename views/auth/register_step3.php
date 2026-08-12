<?php 
$pageTitle = "FitCampus - Registration (Step 3 of 3)";
include '../../includes/headers/header_auth.php'; 
?>

<!-- Header -->
<header class="fixed top-0 w-full h-[70px] z-50 bg-background/80 backdrop-blur-md flex justify-between items-center px-4 md:px-gutter border-b border-outline-variant/10">
    <div class="max-w-2xl w-full mx-auto flex justify-between items-center px-4">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[28px]" style="font-variation-settings: 'FILL' 1;">fitness_center</span>
            <h1 class="font-headline-lg-mobile text-headline-lg-mobile font-bold text-on-surface">FitCampus</h1>
        </div>
        <div class="flex flex-col items-end">
            <span class="font-label-sm text-label-sm text-primary uppercase tracking-widest">Step 3 of 3</span>
            <span class="font-body-md text-[14px] text-on-surface-variant">Verification</span>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="w-full max-w-2xl mt-[100px] mb-24 px-4 md:px-gutter text-on-background mx-auto">
    <div class="w-full h-1 bg-surface-container-highest rounded-full mb-10 overflow-hidden">
        <div class="h-full bg-primary transition-all duration-700 ease-out" style="width: 100%;"></div>
    </div>

    <section class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Final Verification</h2>
        <p class="font-body-md text-on-surface-variant">Confirm your identity to unlock university athletic facilities and personalized training programs.</p>
    </section>

    <form id="step3Form" action="../../backend/auth/register_process.php" method="POST" enctype="multipart/form-data" class="flex flex-col gap-10">
        
        <!-- Student ID Upload -->
        <section class="space-y-6">
            <div class="flex items-center gap-3 border-l-4 border-primary-container pl-4">
                <span class="material-symbols-outlined text-primary">badge</span>
                <h3 class="font-title-md text-title-md text-on-surface">Student ID Verification</h3>
            </div>
            <p class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider mb-4">Please upload clear photos of your valid University of Colombo ID card.</p>
            
            <div class="space-y-4">
                <div class="glass-card rounded-xl p-1 group cursor-pointer active:scale-[0.99] transition-transform relative">
                    <input type="file" name="id_front" class="absolute inset-0 opacity-0 z-10 cursor-pointer" required>
                    <div class="relative w-full aspect-[1.586/1] rounded-lg border-2 border-dashed border-outline-variant/40 flex flex-col items-center justify-center gap-3 group-hover:border-primary/50 group-hover:bg-primary/5 transition-all">
                        <span class="material-symbols-outlined text-4xl text-outline group-hover:text-primary transition-colors">add_a_photo</span>
                        <div class="text-center">
                            <p class="font-title-md text-sm text-on-surface">Front View</p>
                            <p class="font-label-sm text-[10px] text-on-surface-variant">PNG, JPG up to 5MB</p>
                        </div>
                    </div>
                </div>

                <div class="glass-card rounded-xl p-1 group cursor-pointer active:scale-[0.99] transition-transform relative">
                    <input type="file" name="id_back" class="absolute inset-0 opacity-0 z-10 cursor-pointer" required>
                    <div class="relative w-full aspect-[1.586/1] rounded-lg border-2 border-dashed border-outline-variant/40 flex flex-col items-center justify-center gap-3 group-hover:border-primary/50 group-hover:bg-primary/5 transition-all">
                        <span class="material-symbols-outlined text-4xl text-outline group-hover:text-primary transition-colors">flip_camera_ios</span>
                        <div class="text-center">
                            <p class="font-title-md text-sm text-on-surface">Back View</p>
                            <p class="font-label-sm text-[10px] text-on-surface-variant">Clear barcode visible</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Profile Photo Section -->
        <section class="space-y-6">
            <div class="flex items-center gap-3 border-l-4 border-primary-container pl-4">
                <span class="material-symbols-outlined text-primary">account_circle</span>
                <h3 class="font-title-md text-title-md text-on-surface">Student Profile Photo</h3>
            </div>
            <p class="text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider mb-4">Please upload a clear photo where your face is clearly visible.</p>
            
            <div class="glass-card rounded-xl p-1 group cursor-pointer active:scale-[0.99] transition-transform relative">
                <input type="file" name="profile_photo" class="absolute inset-0 opacity-0 z-10 cursor-pointer" required>
                <div class="relative w-full aspect-[1.586/1] rounded-lg border-2 border-dashed border-outline-variant/40 flex flex-col items-center justify-center gap-3 group-hover:border-primary/50 group-hover:bg-primary/5 transition-all">
                    <span class="material-symbols-outlined text-4xl text-outline group-hover:text-primary transition-colors">face</span>
                    <div class="text-center">
                        <p class="font-title-md text-sm text-on-surface">Profile Photo</p>
                        <p class="font-label-sm text-[10px] text-on-surface-variant">PNG, JPG up to 5MB</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- NIC Number -->
        <section class="space-y-4">
            <div class="flex items-center gap-3 border-l-4 border-primary-container pl-4">
                <span class="material-symbols-outlined text-primary">fingerprint</span>
                <h3 class="font-title-md text-title-md text-on-surface">Personal Identity</h3>
            </div>
            <div class="flex flex-col gap-2">
                <label class="font-label-sm text-label-sm text-on-surface-variant ml-1 uppercase tracking-widest" for="nic_number">NIC Number</label>
                <div class="relative group">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-outline group-focus-within:text-primary">pin</span>
                    <input name="nic_number" class="w-full bg-surface-container-lowest border border-outline-variant/30 rounded-xl py-4 pl-12 pr-4 text-on-surface placeholder:text-outline-variant/50 focus:border-primary/50 focus:bg-surface-container transition-all font-body-md" id="nic_number" placeholder="e.g. 200012345678" type="text" required>
                </div>
            </div>
        </section>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col gap-4">
            <button id="completeBtn" class="w-full bg-primary-container text-on-primary-container font-headline-lg text-title-md py-5 rounded-2xl flex items-center justify-center gap-3 active:scale-[0.97] transition-all shadow-lg shadow-primary-container/20 group relative overflow-hidden order-1" type="submit">
                <div class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <span>Complete Registration</span>
                <span class="material-symbols-outlined font-bold" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            </button>
            
            <a href="register_step2.php" class="w-full border border-tertiary/60 text-tertiary font-title-md py-4 rounded-2xl flex items-center justify-center gap-2 hover:bg-tertiary/10 active:scale-[0.97] transition-all group order-2 text-center">
                <span class="material-symbols-outlined text-tertiary group-hover:-translate-x-1 transition-transform">chevron_left</span>
                Back to Previous Page
            </a>
            
            <p class="text-center text-label-sm text-on-surface-variant/60 mt-2 px-6 italic leading-relaxed order-3">
                By completing registration, you agree to the University Sports Council facility regulations and code of conduct.
            </p>
        </div>
    </form>
</main>

<?php include '../../includes/footers/footer_common.php'; ?>
<script src="../../assets/js/auth/register-wizard.js"></script>
</body>
</html>
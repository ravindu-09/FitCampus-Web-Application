<?php
/**
 * views/instructor/inventory.php
 * Inventory equipment management view matching the design specifications.
 */

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../controllers/instructor/inventory_page_controller.php';
require_once '../../includes/headers/header_instructor.php';
?>

<!-- Ambient glow background effects -->
<div class="purple-glow top-left"></div>
<div class="purple-glow bottom-right"></div>

<div class="instructor-viewport-wrapper">
    <?php require_once '../../includes/sidebars/sidebar_instructor.php';?>
    
    <main class="main-content">
        <div class="inventory-container">
            
            <!-- Page Header -->
            <div class="inventory-header">
                <div>
                    <h2 class="settings-main-title m-0">Equipment Status</h2>
                    <p class="text-regular-sub">Manage and track maintenance for all gym assets.</p>
                </div>
                <div class="inventory-actions flex items-center gap-4">
                    <div class="search-box-wrap desktop-only">
                        <span class="material-symbols-outlined search-icon">search</span>
                        <input type="text" class="form-control search-input" placeholder="Search equipment...">
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="openEquipmentModal('addModal')">
                        <span class="material-symbols-outlined">add</span> Add New Equipment
                    </button>
                </div>
            </div>

            <!-- Stats Overview Bento Grid -->
            <div class="bento-grid-inventory">
                <div class="glass-card bento-card">
                    <div class="bento-top-row">
                        <span class="section-label-micro">Total Assets</span>
                        <div class="bento-icon-box">
                            <span class="material-symbols-outlined">inventory</span>
                        </div>
                    </div>
                    <span class="bento-val">142</span>
                </div>

                <div class="glass-card bento-card border-accent-secondary">
                    <div class="bento-top-row">
                        <span class="section-label-micro text-color-secondary">Available</span>
                        <div class="bento-icon-box bg-secondary-dim">
                            <span class="material-symbols-outlined text-color-secondary">check_circle</span>
                        </div>
                    </div>
                    <span class="bento-val">128</span>
                </div>

                <div class="glass-card bento-card border-accent-tertiary">
                    <div class="bento-top-row">
                        <span class="section-label-micro text-color-tertiary">Under Maintenance</span>
                        <div class="bento-icon-box bg-tertiary-dim">
                            <span class="material-symbols-outlined text-color-tertiary">build</span>
                        </div>
                    </div>
                    <span class="bento-val">14</span>
                </div>
            </div>

            <!-- Equipment Grid -->
            <div class="equipment-grid-layout">
                
                <!-- Card 1: Available -->
                <div class="glass-card equipment-card glow-effect">
                    <div class="equipment-img-header">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEndk1fkTomJk53anqMvLXzGWQaBVzVJVMUQTX2Z9Zupnd5jDBLu5RIcBA2NLFsqtxKjg2SBifRjbmnI2puPpac2a_wgw8D12x_VFZPBjQIyEmHoZ5Jdo7evEm84QHwDMN_prtQS5ufT_pGacUmLCN0KPZE1hbfzHxV1wJGvSh3PlL-8OawNZjzylN5SNeles2y8-Vh9m-Xnytc01Bqk0IRNqovAl_1_SOB0UkQ0wEP18aWv2McuZUIQ" alt="Treadmill">
                        <div class="equipment-badge-wrap">
                            <span class="badge-status open">
                                <span class="status-dot"></span> Available
                            </span>
                        </div>
                    </div>
                    <div class="equipment-body">
                        <div class="flex-between mb-2">
                            <h3 class="monitor-title m-0">Treadmill Pro X</h3>
                            <span class="mono text-xs text-dimmed-sub">#TR-04</span>
                        </div>
                        <p class="text-regular-sub text-sm mb-4">Cardio Zone A</p>
                        <div class="equipment-card-footer">
                            <span class="mono text-xs text-regular-sub">Total: 1 | Damaged: 0</span>
                            <button class="btn-text-primary" onclick="openEquipmentModal('updateModal', 'Treadmill Pro X')">Update Status</button>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Under Maintenance -->
                <div class="glass-card equipment-card glow-effect">
                    <div class="equipment-img-header">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuAD22Xd7RdaafEhDGUzRHyMeanvYW7xMEO1WEFMRdGV5OoSUR6tNFnSCi0LubYC3NbHm-IjSV7QNdWjCukQGZ9FPnd34Z4ALI8mdRed1HKqjM4MgJEzQVtFfwlJor-d25EIa7JV7hpKdRynVLter9sKw-Ad-pry_XMd8GSuWKvDzwflS0b1mKIWv4WwxT0XwzThuqBjqHxRDVfJbN2H0_5-2dHRKdwar5ybWZhxK_niRIfUtyO1IhBaeQ" alt="Cable Crossover">
                        <div class="equipment-badge-wrap">
                            <span class="badge-status-maint">
                                <span class="material-symbols-outlined text-xs">build</span> Maint.
                            </span>
                        </div>
                    </div>
                    <div class="equipment-body">
                        <div class="flex-between mb-2">
                            <h3 class="monitor-title m-0">Cable Crossover</h3>
                            <span class="mono text-xs text-dimmed-sub">#CB-02</span>
                        </div>
                        <p class="text-regular-sub text-sm mb-4">Strength Floor 1</p>
                        <div class="equipment-card-footer">
                            <span class="mono text-xs text-regular-sub">Total: 1 | Maint: 1</span>
                            <button class="btn-text-primary" onclick="openEquipmentModal('updateModal', 'Cable Crossover')">Update Status</button>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Damaged -->
                <div class="glass-card equipment-card glow-effect border-error-sub">
                    <div class="equipment-img-header">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDu5V50MiVq6GGzRJXhQKgzdzYEmfioSDF9LoKokp2p3IKyVTtguCf6F_LEgxPPcuMAmFsOJSJHNhrMXn786WEif5JIjf1DdLWiJpU0bKmjqgymknWNM5shc2_KEJnVD8wSniOqjtlPSYIsR4b2ZyX6nWspe6RgaHiy6sjTeAVbtWLSz2GITwmzLO7gLJ6r6zoQ3lDpY40z-2a2NCNeW8L2WAmgyQliGOM847A6YNhyT9FKLafHrqwB3Q" alt="Dumbbell">
                        <div class="equipment-badge-wrap">
                            <span class="badge-status-danger">
                                <span class="material-symbols-outlined text-xs">warning</span> Damaged
                            </span>
                        </div>
                    </div>
                    <div class="equipment-body">
                        <div class="flex-between mb-2">
                            <h3 class="monitor-title m-0">Dumbbell 20kg</h3>
                            <span class="mono text-xs text-dimmed-sub">#FW-20</span>
                        </div>
                        <p class="text-regular-sub text-sm mb-4">Free Weights Area</p>
                        <div class="equipment-card-footer">
                            <span class="mono text-xs text-regular-sub">Total: 4 | Damaged: 2</span>
                            <button class="btn-text-primary" onclick="openEquipmentModal('updateModal', 'Dumbbell 20kg')">Update Status</button>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Available -->
                <div class="glass-card equipment-card glow-effect">
                    <div class="equipment-img-header">
                        <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuA8oSF6yNQ1mGv3zAEwwXHY1B4bWk3D81epw6BHPxLJEkYKFCh8H2yQThpJqjpmwNzfExWGdIJWYBolZOhe3gDZccuqVqO_Q5nb9ERj5gFAfsBkhVux3D97H5jMn1tN1dl0bWsu2Xznw3yj9RRVvyZgPsCtzdh0ysfOeBvFBMwzeN-3Y-G2g8c1cpthgQmP6pmrbeJmsn22cfjf-9eVtKEI0QtXsx9BtXPaYn25SRm2F42Eb-ZgvybPyA" alt="Spin Bike">
                        <div class="equipment-badge-wrap">
                            <span class="badge-status open">
                                <span class="status-dot"></span> Available
                            </span>
                        </div>
                    </div>
                    <div class="equipment-body">
                        <div class="flex-between mb-2">
                            <h3 class="monitor-title m-0">Spin Bike Elite</h3>
                            <span class="mono text-xs text-dimmed-sub">#SP-12</span>
                        </div>
                        <p class="text-regular-sub text-sm mb-4">Studio 2</p>
                        <div class="equipment-card-footer">
                            <span class="mono text-xs text-regular-sub">Total: 24 | Damaged: 0</span>
                            <button class="btn-text-primary" onclick="openEquipmentModal('updateModal', 'Spin Bike Elite')">Update Status</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<!-- ================= MODALS ================= -->
<div class="wk-modal-overlay hidden" id="addModal">
    <div class="wk-modal-box glass-card shadow-2xl">
        <div class="wk-modal-header">
            <h3 class="wk-modal-title text-color-on-surface">Add New Equipment</h3>
            <button class="wk-modal-close" onclick="closeEquipmentModal('addModal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <form class="wk-modal-body space-y-md">
            <div class="form-group-cal">
                <label class="gl-lbl-accent">ASSET NAME</label>
                <input type="text" placeholder="e.g. Treadmill Pro X" class="cal-input-field">
            </div>
            <div class="form-group-cal">
                <label class="gl-lbl-accent">CATEGORY</label>
                <select class="cal-input-field gl-select">
                    <option>Cardio</option>
                    <option>Strength</option>
                    <option>Free Weights</option>
                    <option>Accessories</option>
                </select>
            </div>
            <div class="form-group-cal">
                <label class="gl-lbl-accent">LOCATION</label>
                <input type="text" placeholder="e.g. Cardio Zone A" class="cal-input-field">
            </div>
            <div class="form-row-2">
                <div class="form-group-cal">
                    <label class="gl-lbl-accent">TOTAL QUANTITY</label>
                    <input type="number" value="1" min="1" class="cal-input-field">
                </div>
                <div class="form-group-cal">
                    <label class="gl-lbl-accent">INITIAL CONDITION</label>
                    <select class="cal-input-field gl-select">
                        <option>Excellent</option>
                        <option>Good</option>
                        <option>Fair</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer-actions">
                <button type="button" class="btn btn-glass" onclick="closeEquipmentModal('addModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="closeEquipmentModal('addModal')">Add Asset</button>
            </div>
        </form>
    </div>
</div>

<div class="wk-modal-overlay hidden" id="updateModal">
    <div class="wk-modal-box glass-card shadow-2xl" style="max-width: 400px;">
        <div class="wk-modal-header">
            <h3 class="wk-modal-title text-color-on-surface">Update Status</h3>
            <button class="wk-modal-close" onclick="closeEquipmentModal('updateModal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body">
            <p id="updateAssetName" class="text-color-primary font-bold mb-4">Asset Name</p>
            <div class="update-box-bg mb-4">
                <div class="flex-between">
                    <span class="text-regular-sub text-sm">Total Quantity:</span>
                    <span class="font-bold">24</span>
                </div>
                <hr class="custom-divider">
                <div class="flex-between align-center">
                    <span class="text-error text-sm">Damaged / Maint.:</span>
                    <input type="number" value="0" min="0" class="cal-input-field text-right" style="width: 80px; padding: 4px 8px;">
                </div>
            </div>
            <div class="modal-footer-actions">
                <button type="button" class="btn btn-glass" onclick="closeEquipmentModal('updateModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="closeEquipmentModal('updateModal')">Save Status</button>
            </div>
        </div>
    </div>
</div>

<?php 
require_once '../../includes/bottombar/bottombar_instructor.php'; 
require_once '../../includes/footers/footer_common.php'; 
?>
<?php
// views/instructor/rules.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../../controllers/instructor/rules_page_controller.php';
require_once '../../includes/headers/header_instructor.php';
require_once '../../includes/sidebars/sidebar_instructor.php';
?>

<div class="instructor-viewport-wrapper">
    <main class="main-content">
        <div class="dashboard-main-container bk-main-container rules-main-wrapper">
            
            <!-- Page Header -->
            <div class="content-header-row mb-4 flex-between items-center rules-header-row">
                <div>
                    <h2 class="page-main-heading m-0">Gym Rules Manager</h2>
                    <p class="page-sub-heading m-0">Define and enforce facility guidelines for all members.</p>
                </div>
                <button class="btn btn-glass btn-sm rules-filter-btn" onclick="openModal('filter-modal')">
                    <span class="material-symbols-outlined rules-filter-icon">filter_list</span> Filter
                </button>
            </div>

            <!-- Rules Grid Layout -->
            <div class="rules-grid">
                
                <!-- Rule 1 -->
                <div class="glass-card bk-card-pad rules-card rule-border-error">
                    <div class="rules-card-top">
                        <div class="rules-badge-group">
                            <span class="badge-tag mono rules-tag-muted">Rule 01</span>
                            <span class="gl-badge rules-badge-strict">Strict</span>
                        </div>
                        <div class="rules-action-group">
                            <button class="toggle-btn rules-action-btn" onclick="openModal('edit-rule-modal')"><span class="material-symbols-outlined rules-icon-sm">edit</span></button>
                            <button class="toggle-btn txt-error rules-action-btn" onclick=""><span class="material-symbols-outlined rules-icon-sm">delete</span></button>
                        </div>
                    </div>
                    <h3 class="rules-card-title">Re-rack weights after use</h3>
                    <p class="rules-card-desc">All dumbbells, plates, and barbells must be returned to their designated storage racks immediately following a set.</p>
                    <div class="rules-card-footer">
                        <div class="flex-items gap-2">
                            <span class="material-symbols-outlined text-error rules-icon-xs">warning</span>
                            <span class="rules-penalty-text">Penalty: <span class="text-error font-bold">Account Strike</span></span>
                        </div>
                    </div>
                </div>

                <!-- Rule 2 -->
                <div class="glass-card bk-card-pad rules-card rule-border-tertiary">
                    <div class="rules-card-top">
                        <div class="rules-badge-group">
                            <span class="badge-tag mono rules-tag-muted">Rule 02</span>
                            <span class="gl-badge rules-badge-standard">Standard</span>
                        </div>
                        <div class="rules-action-group">
                            <button class="toggle-btn rules-action-btn" onclick="openModal('edit-rule-modal')"><span class="material-symbols-outlined rules-icon-sm">edit</span></button>
                            <button class="toggle-btn txt-error rules-action-btn" onclick=""><span class="material-symbols-outlined rules-icon-sm">delete</span></button>
                        </div>
                    </div>
                    <h3 class="rules-card-title">Wipe down machines</h3>
                    <p class="rules-card-desc">Users must sanitize equipment padding and handles using provided wipes after completing their exercises.</p>
                    <div class="rules-card-footer">
                        <div class="flex-items gap-2">
                            <span class="material-symbols-outlined text-tertiary rules-icon-xs">campaign</span>
                            <span class="rules-penalty-text">Penalty: <span class="text-tertiary font-bold">Verbal Warning</span></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="rules-footer-actions">
                <button class="btn rules-btn-outline" onclick="openModal('add-rule-modal')">
                    <span class="material-symbols-outlined">add_circle</span> Add New Rule
                </button>
                <button class="btn btn-primary rules-btn-publish">
                    <span class="material-symbols-outlined">publish</span> Publish Updated Rules
                </button>
            </div>

        </div>
    </main>
</div>

<!-- ================= MODALS ================= -->
<div class="wk-modal-overlay hidden" id="add-rule-modal">
    <div class="wk-modal-box glass-card shadow-2xl rules-modal-box">
        <div class="wk-modal-header rules-modal-header">
            <h3 class="wk-modal-title text-color-on-surface">Add New Rule</h3>
            <button class="wk-modal-close rules-close-btn" onclick="closeModal('add-rule-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body space-y-md rules-modal-body">
            <div class="form-group-cal">
                <label class="gl-lbl-accent rules-label">RULE TITLE</label>
                <input class="cal-input-field rules-input" type="text" placeholder="e.g. Re-rack weights">
            </div>
            <div class="form-group-cal">
                <label class="gl-lbl-accent rules-label">RULE DESCRIPTION</label>
                <textarea class="cal-input-field rules-textarea" rows="3" placeholder="Describe the rule and its importance..."></textarea>
            </div>
            <button type="button" class="btn btn-primary w-full rules-btn-submit">Publish Rule</button>
        </div>
    </div>
</div>

<div class="wk-modal-overlay hidden" id="edit-rule-modal">
    <div class="wk-modal-box glass-card shadow-2xl rules-modal-box">
        <div class="wk-modal-header rules-modal-header">
            <h3 class="wk-modal-title text-color-on-surface">Edit Rule</h3>
            <button class="wk-modal-close rules-close-btn" onclick="closeModal('edit-rule-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body space-y-md rules-modal-body">
            <div class="form-group-cal">
                <label class="gl-lbl-accent rules-label">RULE TITLE</label>
                <input class="cal-input-field rules-input" type="text" value="Re-rack weights after use">
            </div>
            <button type="button" class="btn btn-primary w-full rules-btn-submit">Publish Changes</button>
        </div>
    </div>
</div>

<div class="wk-modal-overlay hidden" id="filter-modal">
    <div class="wk-modal-box glass-card shadow-2xl rules-filter-box">
        <div class="wk-modal-header rules-modal-header">
            <h3 class="wk-modal-title text-color-on-surface">Filter Rules</h3>
            <button class="wk-modal-close rules-close-btn" onclick="closeModal('filter-modal')"><span class="material-symbols-outlined">close</span></button>
        </div>
        <div class="wk-modal-body space-y-md">
            <button type="button" class="btn btn-primary w-full rules-btn-submit" onclick="closeModal('filter-modal')">Apply Filters</button>
        </div>
    </div>
</div>

<?php 
require_once '../../includes/bottombar/bottombar_instructor.php'; 
require_once '../../includes/footers/footer_common.php'; 
?>
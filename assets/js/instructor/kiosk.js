/**
 * assets/js/instructor/kiosk.js
 * Handles modal popups, manual override toggle, and headcount counter.
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. Modal Open Function
    window.openKioskModal = (modalId) => {
        document.querySelectorAll('.wk-modal-overlay').forEach(modal => {
            modal.classList.add('hidden');
        });
        const targetModal = document.getElementById(modalId);
        if (targetModal) {
            targetModal.classList.remove('hidden');
        } else {
            console.warn(`Modal with ID "${modalId}" was not found.`);
        }
    };

    // 2. Modal Close Function
    window.closeKioskModal = (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    // 3. Close modal when clicking outside backdrop
    document.querySelectorAll('.wk-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.add('hidden');
            }
        });
    });

    // 4. Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.wk-modal-overlay').forEach(modal => {
                modal.classList.add('hidden');
            });
        }
    });

    // 5. Manual Override Toggle Logic
    const overrideToggle = document.getElementById('override-toggle');
    const overrideControls = document.getElementById('override-controls');

    if (overrideToggle && overrideControls) {
        overrideToggle.addEventListener('change', function() {
            if (this.checked) {
                overrideControls.classList.remove('disabled');
            } else {
                overrideControls.classList.add('disabled');
            }
        });
    }

    const feedback = (element, message, isError = false) => {
        if (!element) return;
        element.textContent = message;
        element.classList.toggle('is-error', isError);
    };

    const manualAction = document.getElementById('manual-attendance-action');
    const facilityGroup = document.getElementById('manual-facility-group');
    const facilitySelect = document.getElementById('manual-facility');
    manualAction?.addEventListener('change', () => {
        const checkingIn = manualAction.value === 'manual_checkin';
        facilityGroup?.classList.toggle('hidden', !checkingIn);
        if (facilitySelect) facilitySelect.required = checkingIn;
    });

    document.getElementById('manual-attendance-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const status = document.getElementById('manual-attendance-feedback');
        const submit = form.querySelector('[type="submit"]');
        submit.disabled = true;
        feedback(status, 'Recording attendance…');
        try {
            const response = await fetch('../../controllers/instructor/kiosk_action.php', {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Attendance could not be recorded.');
            feedback(status, result.message);
            window.setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            feedback(status, error.message || 'Attendance could not be recorded. Please try again.', true);
        } finally {
            submit.disabled = false;
        }
    });

    const newRequestKey = () => {
        if (window.crypto?.randomUUID) return window.crypto.randomUUID().replace(/-/g, '');
        const bytes = new Uint8Array(16);
        window.crypto.getRandomValues(bytes);
        return Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
    };

    document.querySelectorAll('.js-open-penalty').forEach(button => {
        button.addEventListener('click', () => {
            document.getElementById('penalty-user-id').value = button.dataset.userId || '';
            document.getElementById('penalty-member-label').textContent =
                `For ${button.dataset.memberName || 'selected member'}`;
            document.getElementById('penalty-request-key').value = newRequestKey();
            feedback(document.getElementById('penalty-feedback'), '');
            openKioskModal('penalty-modal');
        });
    });

    document.getElementById('penalty-form')?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const status = document.getElementById('penalty-feedback');
        const submit = form.querySelector('[type="submit"]');
        submit.disabled = true;
        feedback(status, 'Applying penalty…');
        try {
            const response = await fetch('../../controllers/common/penalty_action.php', {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Penalty could not be recorded.');
            feedback(status, `${result.message} Remaining Life Bar: ${result.life_balance}%.`);
            window.setTimeout(() => window.location.reload(), 1100);
        } catch (error) {
            feedback(status, error.message || 'Penalty could not be recorded. Please try again.', true);
        } finally {
            submit.disabled = false;
        }
    });

    // 6. Headcount Plus / Minus Counter Logic
    const btnPlus = document.getElementById('btn-plus');
    const btnMinus = document.getElementById('btn-minus');
    const headcountVal = document.getElementById('headcount-val');

    if (btnPlus && btnMinus && headcountVal) {
        btnPlus.addEventListener('click', () => {
            let current = parseInt(headcountVal.innerText, 10);
            if (current < 50) {
                headcountVal.innerText = current + 1;
            }
        });

        btnMinus.addEventListener('click', () => {
            let current = parseInt(headcountVal.innerText, 10);
            if (current > 0) {
                headcountVal.innerText = current - 1;
            }
        });
    }

});
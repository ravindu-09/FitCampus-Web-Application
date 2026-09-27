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
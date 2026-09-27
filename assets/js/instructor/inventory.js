/**
 * assets/js/instructor/inventory.js
 * Handles inventory equipment modal toggles and maintenance updates.
 */

function openEquipmentModal(modalId, assetName = '') {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        if (assetName) {
            const nameEl = document.getElementById('updateAssetName');
            if (nameEl) {
                nameEl.innerText = assetName;
            }
        }
    }
}

function closeEquipmentModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Close modal when clicking outside background overlay
    const overlays = document.querySelectorAll('.wk-modal-overlay');
    overlays.forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.add('hidden');
            }
        });
    });

    // Close modal on Escape key press
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            overlays.forEach(overlay => {
                overlay.classList.add('hidden');
            });
        }
    });
});
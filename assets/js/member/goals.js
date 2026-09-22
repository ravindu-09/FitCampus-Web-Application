// assets/js/member/goals.js
document.addEventListener('DOMContentLoaded', () => {

    const editModal = document.getElementById('edit-goal-modal');
    const detailsModal = document.getElementById('goal-details-modal');
    const createModal = document.getElementById('create-goal-modal');

    // Global Close Function
    window.closeModals = function() {
        if(editModal) editModal.classList.remove('active');
        if(detailsModal) detailsModal.classList.remove('active');
        if(createModal) createModal.classList.remove('active');
    };

    // Open Edit Modal
    window.openEditGoalModal = function(goalName) {
        if(editModal) {
            document.getElementById('edit-goal-name').value = goalName;
            editModal.classList.add('active');
        }
    };

    // Open Create Modal
    window.openCreateGoalModal = function() {
        if(createModal) {
            createModal.classList.add('active');
        }
    };

    // Open Goal Details Modal & Animate Bar Progress
    window.openGoalDetailsModal = function(title, percentage, target, current, badgeClass, badgeText, timeText) {
        if(detailsModal) {
            document.getElementById('detail-title').innerText = title;
            
            let targetParts = target.split(' ');
            let currentParts = current.split(' ');

            document.getElementById('detail-target-val').innerText = targetParts[0];
            document.getElementById('detail-current-val').innerText = currentParts[0];
            
            document.getElementById('detail-percentage').innerText = percentage + '%';
            if(document.getElementById('detail-time')) {
                document.getElementById('detail-time').innerText = timeText;
            }
            
            const badge = document.getElementById('detail-category-badge');
            if (badge) {
                badge.className = `gl-badge ${badgeClass} uppercase`;
                badge.innerText = badgeText;
            }
            
            // Animate Linear Progress Bar Width
            const barFill = document.getElementById('bar-progress-fill');
            if (barFill) {
                const cleanPercentage = Math.min(Math.max(parseFloat(percentage), 0), 100);
                
                barFill.style.width = '0%';
                setTimeout(() => {
                    barFill.style.width = cleanPercentage + '%';
                }, 50);
            }

            detailsModal.classList.add('active');
        }
    };

});
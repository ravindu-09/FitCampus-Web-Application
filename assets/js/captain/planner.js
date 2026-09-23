// assets/js/captain/planner.js

document.addEventListener('DOMContentLoaded', () => {
    
    // =========================================================
    // 1. ADD NEW EXERCISE ROW LOGIC
    // =========================================================
    const addExerciseBtn = document.getElementById('add-exercise-btn');
    const exercisesContainer = document.getElementById('exercises-container');

    if (addExerciseBtn && exercisesContainer) {
        addExerciseBtn.addEventListener('click', () => {
            const exerciseBlock = document.createElement('div');
            exerciseBlock.className = 'exercise-block bk-mt-md pt-4 border-t-dim'; // Pure CSS separator
            
            exerciseBlock.innerHTML = `
                <div class="flex-between items-center bk-mb-sm">
                    <label class="gl-lbl-accent m-0">NEXT EXERCISE</label>
                    <button type="button" class="remove-ex-btn txt-error bg-transparent" style="border:none; cursor:pointer; display:flex; align-items:center;"><span class="material-symbols-outlined" style="font-size:18px;">close</span></button>
                </div>
                <div class="form-group-cal space-y-xs bk-mb-md">
                    <input class="cal-input-field ex-name" type="text" name="ex_name[]" placeholder="e.g., Squats" required>
                </div>
                <div class="cal-grid-2 gap-3">
                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent">SETS</label>
                        <input class="cal-input-field ex-sets" type="number" name="ex_sets[]" placeholder="0" required>
                    </div>
                    <div class="form-group-cal space-y-xs">
                        <label class="gl-lbl-accent">MAX DURATION (MINS)</label>
                        <input class="cal-input-field ex-duration" type="number" name="ex_duration[]" placeholder="0" required>
                    </div>
                </div>
            `;
            exercisesContainer.appendChild(exerciseBlock);

            // Add event listener to the newly created remove button
            exerciseBlock.querySelector('.remove-ex-btn').addEventListener('click', function() {
                exerciseBlock.remove();
            });
        });
    }

    // =========================================================
    // 2. FORM SUBMISSION (Ready for Backend Integration)
    // =========================================================
    const plannerForm = document.getElementById('workout-planner-form');
    const statusMsg = document.getElementById('form-status-msg');

    if (plannerForm) {
        plannerForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default page reload
            
            // Show processing message to the UI
            statusMsg.classList.remove('hidden');
            statusMsg.innerHTML = `<span class="txt-primary">Processing your schedule...</span>`;
            statusMsg.style.background = 'rgba(223, 183, 255, 0.1)';

            const formData = new FormData(plannerForm);
            formData.append('action', 'save_workout');
            formData.append('status', 'Published'); // Indicate the Published status

            // -------------------------------------------------------------
            // TODO: Uncomment the fetch block below once the backend is ready.
            // -------------------------------------------------------------
            /*
            fetch('planner_backend.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusMsg.innerHTML = `<span class="txt-green">Schedule Published Successfully!</span>`;
                    statusMsg.style.background = 'rgba(74,225,118,0.1)';
                    plannerForm.reset();
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    statusMsg.innerHTML = `<span class="txt-error">${data.error}</span>`;
                    statusMsg.style.background = 'rgba(255,180,171,0.1)';
                }
            })
            .catch(error => {
                statusMsg.innerHTML = `<span class="txt-error">Network error occurred.</span>`;
                statusMsg.style.background = 'rgba(255,180,171,0.1)';
            });
            */

            // For demonstration purposes only (Mock Success):
            setTimeout(() => {
                statusMsg.innerHTML = `<span class="txt-green">Mock: Schedule Published Successfully!</span>`;
                statusMsg.style.background = 'rgba(74,225,118,0.1)';
                plannerForm.reset();
            }, 1000);
        });
        
        // Save as Draft Button Logic (Mock)
        document.getElementById('btn-save-draft').addEventListener('click', function() {
            if(!plannerForm.checkValidity()) {
                plannerForm.reportValidity();
                return;
            }
            statusMsg.classList.remove('hidden');
            statusMsg.innerHTML = `<span class="txt-muted">Mock: Saved as Draft</span>`;
            statusMsg.style.background = 'rgba(255,255,255,0.05)';
        });
    }

    // =========================================================
    // 3. MODALS LOGIC (Delete & Edit)
    // =========================================================
    const deleteModal = document.getElementById('delete-modal');
    const editDraftModal = document.getElementById('edit-draft-modal');

    // Open Delete Modal
    document.querySelectorAll('.open-delete-modal').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent parent card click event from triggering
            const scheduleId = btn.getAttribute('data-id');
            // TODO: Pass scheduleId to the confirm button for backend use later
            deleteModal.classList.add('active');
        });
    });

    // Open Edit Draft Modal
    document.querySelectorAll('.open-edit-modal').forEach(card => {
        card.addEventListener('click', () => {
            const scheduleId = card.getAttribute('data-id');
            // TODO: Fetch data for this ID from backend later
            editDraftModal.classList.add('active');
        });
    });

    // Close Modals functionality
    document.querySelectorAll('.close-modal-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            deleteModal.classList.remove('active');
            editDraftModal.classList.remove('active');
        });
    });

    // Confirm Delete Action (Mock)
    document.getElementById('confirm-delete-btn')?.addEventListener('click', () => {
        // TODO: Include Backend delete logic here
        deleteModal.classList.remove('active');
        alert("Mock: Schedule deleted successfully.");
    });
});
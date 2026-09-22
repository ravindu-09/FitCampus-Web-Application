// assets/js/member/workouts.js
document.addEventListener('DOMContentLoaded', () => {

    // --- Modals Logic (Create & Edit) ---
    const createModal = document.getElementById('create-routine-modal');
    const editModal = document.getElementById('edit-routine-modal');

    // Open Create Modal
    const btnOpenCreate = document.getElementById('btn-open-create-modal');
    if (btnOpenCreate) {
        btnOpenCreate.addEventListener('click', () => createModal.classList.add('active'));
    }

    // Close Create Modal
    document.getElementById('btn-close-create-modal')?.addEventListener('click', () => createModal.classList.remove('active'));
    document.getElementById('btn-cancel-create-modal')?.addEventListener('click', () => createModal.classList.remove('active'));

    // Close Edit Modal
    document.getElementById('btn-close-edit-modal')?.addEventListener('click', () => editModal.classList.remove('active'));
    document.getElementById('btn-cancel-edit-modal')?.addEventListener('click', () => editModal.classList.remove('active'));

    // Global Function to Open Edit Modal (callable from inline)
    window.openEditModal = function(routineName) {
        document.getElementById('edit-routine-name').value = routineName;
        editModal.classList.add('active');
    };

});

// --- Instructor Workout Details Modal (Global Function) ---
function showWorkoutDetails(title, exercisesString, desc) {
    const modal = document.getElementById('workout-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalDesc = document.getElementById('modal-desc');
    const list = document.getElementById('modal-exercise-list');
    
    modalTitle.innerText = title;
    if (modalDesc) modalDesc.innerText = desc || '';
    list.innerHTML = '';
    
    exercisesString.split(', ').forEach(ex => {
        const parts = ex.split(' ');
        const repData = parts.pop();
        const exName = parts.join(' ');

        const li = document.createElement('li');
        li.className = 'wk-modal-list-item';
        li.innerHTML = `<span class="wk-modal-ex-name">${exName}</span><span class="wk-modal-ex-reps">${repData}</span>`;
        list.appendChild(li);
    });
    
    modal.classList.add('active');
}

function closeWorkoutModal() {
    const modal = document.getElementById('workout-modal');
    modal.classList.remove('active');
}
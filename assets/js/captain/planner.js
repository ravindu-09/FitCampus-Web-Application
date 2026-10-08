// assets/js/captain/planner.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('workout-planner-form');
    const exercisesContainer = document.getElementById('exercises-container');
    const statusMessage = document.getElementById('form-status-msg');
    const saveButton = document.getElementById('btn-save-workout');
    const formTitle = document.getElementById('workout-form-title');
    const workoutIdInput = document.getElementById('workout-id');
    const actionInput = document.getElementById('workout-action');
    const exerciseOptions = exercisesContainer.querySelector('.exercise-select').innerHTML;

    if (!form || !exercisesContainer) return;

    function addRemoveButton(block) {
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-ex-btn txt-error bg-transparent';
        removeButton.setAttribute('aria-label', 'Remove exercise');
        removeButton.style.cssText = 'border: none; cursor: pointer; display: flex; align-items: center; margin-left: auto;';
        removeButton.innerHTML = '<span class="material-symbols-outlined">close</span>';
        block.appendChild(removeButton);
        removeButton.addEventListener('click', () => {
            if (exercisesContainer.children.length === 1) {
                block.querySelector('.exercise-select').value = '';
                block.querySelector('.exercise-custom').value = '';
                block.querySelector('.exercise-name-value').value = '';
                block.querySelector('.custom-exercise-field').hidden = true;
                block.querySelector('.exercise-custom').required = false;
                block.querySelector('.ex-sets').value = '';
                block.querySelector('.ex-reps').value = '';
            } else {
                block.remove();
            }
        });
    }

    function syncExerciseName(block) {
        const select = block.querySelector('.exercise-select');
        const customField = block.querySelector('.custom-exercise-field');
        const customInput = block.querySelector('.exercise-custom');
        const exerciseName = block.querySelector('.exercise-name-value');
        const isCustom = select.value === '__custom__';

        customField.hidden = !isCustom;
        customInput.required = isCustom;
        exerciseName.value = isCustom ? customInput.value.trim() : select.value;
    }

    function makeExerciseRow(exercise = {}) {
        const block = document.createElement('div');
        block.className = 'exercise-block bk-mt-md pt-4 border-t-dim';
        block.innerHTML = `
            <div class="form-group-cal space-y-xs bk-mb-md">
                <label class="gl-lbl-accent">EXERCISE NAME</label>
                <select class="cal-input-field gl-select exercise-select" required>${exerciseOptions}</select>
                <div class="custom-exercise-field" hidden>
                    <input class="cal-input-field exercise-custom" type="text" maxlength="100" placeholder="Enter exercise name">
                </div>
                <input type="hidden" name="exercise_name[]" class="exercise-name-value" required>
            </div>
            <div class="cal-grid-2 gap-3">
                <div class="form-group-cal space-y-xs">
                    <label class="gl-lbl-accent">SETS</label>
                    <input class="cal-input-field ex-sets" type="number" name="exercise_sets[]" min="1" max="1000" placeholder="3" required>
                </div>
                <div class="form-group-cal space-y-xs">
                    <label class="gl-lbl-accent">REPS</label>
                    <input class="cal-input-field ex-reps" type="number" name="exercise_reps[]" min="1" max="1000" placeholder="10" required>
                </div>
            </div>
        `;
        block.querySelector('[name="exercise_sets[]"]').value = exercise.Sets || '';
        block.querySelector('[name="exercise_reps[]"]').value = exercise.Reps || '';
        syncExerciseName(block);
        if (exercise.Exercise_Name) {
            const select = block.querySelector('.exercise-select');
            const normalizedName = exercise.Exercise_Name.trim().replace(/\s+/g, ' ');
            const matchingOption = Array.from(select.options).find((option) =>
                option.value && option.value !== '__custom__'
                && option.value.toLocaleLowerCase() === normalizedName.toLocaleLowerCase()
            );

            if (matchingOption) {
                select.value = matchingOption.value;
            } else {
                select.value = '__custom__';
                block.querySelector('.exercise-custom').value = exercise.Exercise_Name;
            }
            syncExerciseName(block);
        }
        addRemoveButton(block);
        return block;
    }

    function setStatus(message, isError = false) {
        statusMessage.textContent = message;
        statusMessage.classList.remove('hidden');
        statusMessage.style.color = isError ? 'var(--error)' : 'var(--secondary)';
        statusMessage.style.background = isError ? 'rgba(255,180,171,0.1)' : 'rgba(74,225,118,0.1)';
    }

    function resetForm() {
        form.reset();
        workoutIdInput.value = '';
        actionInput.value = 'create';
        saveButton.textContent = 'Save Workout';
        formTitle.textContent = 'Create Reusable Team Workout';
        exercisesContainer.replaceChildren(makeExerciseRow());
        statusMessage.classList.add('hidden');
    }

    exercisesContainer.querySelectorAll('.exercise-block').forEach(addRemoveButton);

    exercisesContainer.addEventListener('change', (event) => {
        if (event.target.matches('.exercise-select')) {
            syncExerciseName(event.target.closest('.exercise-block'));
        }
    });
    exercisesContainer.addEventListener('input', (event) => {
        if (event.target.matches('.exercise-custom')) {
            syncExerciseName(event.target.closest('.exercise-block'));
        }
    });

    document.getElementById('add-exercise-btn').addEventListener('click', () => {
        exercisesContainer.appendChild(makeExerciseRow());
    });

    document.getElementById('btn-reset-workout').addEventListener('click', (event) => {
        event.preventDefault();
        resetForm();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        saveButton.disabled = true;
        setStatus('Saving workout...');

        try {
            const response = await fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form) });
            const result = await response.json();
            if (!response.ok || !result.success) {
                setStatus(result.message || 'The workout could not be saved.', true);
                return;
            }
            setStatus(result.message);
            window.setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            setStatus('A network error prevented saving the workout.', true);
        } finally {
            saveButton.disabled = false;
        }
    });

    document.querySelectorAll('.edit-workout-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const workout = JSON.parse(button.dataset.workout);
            form.elements.team_id.value = workout.Team_ID;
            form.elements.title.value = workout.Title;
            form.elements.description.value = workout.Description || '';
            form.elements.duration.value = workout.Duration || '';
            workoutIdInput.value = workout.Workout_ID;
            actionInput.value = 'edit';
            saveButton.textContent = 'Update Workout';
            formTitle.textContent = 'Edit Reusable Team Workout';
            const rows = workout.Exercises.length
                ? workout.Exercises.map(makeExerciseRow)
                : [makeExerciseRow()];
            exercisesContainer.replaceChildren(...rows);
            setStatus('Editing workout for ' + workout.Team_Name + '.');
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.querySelectorAll('.delete-workout-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            if (!window.confirm('Delete this reusable workout? Its exercise links will also be removed.')) return;

            const formData = new FormData();
            formData.append('csrf_token', form.elements.csrf_token.value);
            formData.append('action', 'delete');
            formData.append('workout_id', button.dataset.workoutId);
            formData.append('team_id', button.dataset.teamId);
            button.disabled = true;

            try {
                const response = await fetch(form.getAttribute('action'), { method: 'POST', body: formData });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    setStatus(result.message || 'The workout could not be deleted.', true);
                    button.disabled = false;
                    return;
                }
                window.location.reload();
            } catch (error) {
                setStatus('A network error prevented deleting the workout.', true);
                button.disabled = false;
            }
        });
    });
});
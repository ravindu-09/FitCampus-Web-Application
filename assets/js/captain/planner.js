// assets/js/captain/planner.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('workout-planner-form');
    const exercisesContainer = document.getElementById('exercises-container');
    if (!form || !exercisesContainer) return;

    const statusMessage = document.getElementById('form-status-msg');
    const saveButton = document.getElementById('btn-save-workout');
    const formTitle = document.getElementById('workout-form-title');
    const workoutIdInput = document.getElementById('workout-id');
    const actionInput = document.getElementById('workout-action');

    function addRemoveButton(block) {
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-ex-btn txt-error bg-transparent';
        removeButton.setAttribute('aria-label', 'Remove exercise');
        removeButton.classList.add('captain-exercise-remove');
        removeButton.title = 'Remove exercise';
        removeButton.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">delete</span>';
        block.appendChild(removeButton);
        removeButton.addEventListener('click', () => {
            if (exercisesContainer.children.length === 1) {
                block.querySelector('.exercise-search').value = '';
                block.querySelector('[data-exercise-id]').value = '';
                block.querySelector('[data-exercise-message]').hidden = true;
                block.querySelector('.ex-sets').value = '';
                block.querySelector('.ex-reps').value = '';
            } else {
                block.remove();
            }
        });
    }

    function makeExerciseRow(exercise = {}) {
        const block = document.createElement('div');
        block.className = 'exercise-block captain-exercise-card';
        block.innerHTML = `
            <div class="form-group-cal space-y-xs bk-mb-md">
                <label class="gl-lbl-accent">EXERCISE NAME</label>
                <div class="exercise-autocomplete">
                    <input class="cal-input-field exercise-search" type="text" name="exercise_name[]" maxlength="100" placeholder="Search or enter an exercise name" autocomplete="off" required data-exercise-search>
                    <input type="hidden" name="exercise_id[]" data-exercise-id>
                    <div class="exercise-autocomplete-list" hidden data-exercise-suggestions></div>
                    <div class="exercise-autocomplete-message" hidden data-exercise-message></div>
                </div>
            </div>
            <div class="captain-exercise-fields">
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
        if (exercise.Exercise_Name) {
            block.querySelector('.exercise-search').value = exercise.Exercise_Name;
            block.querySelector('[data-exercise-id]').value = exercise.Exercise_ID || '';
        }
        addRemoveButton(block);
        window.FitCampusExerciseAutocomplete.initializeWithin(block);
        return block;
    }

    function setStatus(message, isError = false) {
        statusMessage.textContent = message;
        statusMessage.classList.remove('hidden');
        statusMessage.classList.toggle('captain-feedback-error', isError);
        statusMessage.classList.toggle('captain-feedback-success', !isError);
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

    function hasWorkoutFormData() {
        const hasMainFields = [
            form.elements.team_id.value,
            form.elements.title.value.trim(),
            form.elements.description.value.trim(),
            form.elements.duration.value,
            workoutIdInput.value
        ].some(Boolean);

        if (hasMainFields) return true;

        return Array.from(exercisesContainer.querySelectorAll('.exercise-block')).some((block) => (
            block.querySelector('.exercise-search').value.trim()
            || block.querySelector('[data-exercise-id]').value
            || block.querySelector('.ex-sets').value
            || block.querySelector('.ex-reps').value
        ));
    }

    exercisesContainer.querySelectorAll('.exercise-block').forEach((block) => {
    addRemoveButton(block);
    window.FitCampusExerciseAutocomplete.initializeWithin(block);
    });

    document.getElementById('add-exercise-btn').addEventListener('click', () => {
        exercisesContainer.appendChild(makeExerciseRow());
    });

    document.getElementById('btn-reset-workout').addEventListener('click', (event) => {
        event.preventDefault();
        if (
            hasWorkoutFormData()
            && !window.confirm('Clear the workout form? Your unsaved workout details will be lost.')
        ) {
            return;
        }
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
            window.setTimeout(() => {
                form.elements.title.focus({ preventScroll: true });
            }, 350);
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
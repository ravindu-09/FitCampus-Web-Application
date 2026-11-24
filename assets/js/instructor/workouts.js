document.addEventListener('DOMContentLoaded', () => {
    const actionUrl = '../../controllers/instructor/workout_action.php';
    const status = document.getElementById('instructor-workout-status');
    const formModal = document.getElementById('workout-form-modal');
    const detailsModal = document.getElementById('workout-details-modal');
    const form = document.getElementById('instructor-workout-form');
    const exerciseContainer = document.getElementById('instructor-exercises');

    function showStatus(message, success) {
        status.textContent = message;
        status.classList.toggle('is-success', success);
        status.classList.toggle('is-error', !success);
        status.hidden = !message;
    }

    function showFormStatus(message, success = false) {
        const formStatus = document.getElementById('instructor-form-status');
        formStatus.textContent = message;
        formStatus.classList.toggle('is-success', success);
        formStatus.classList.toggle('is-error', !success);
        formStatus.hidden = !message;
    }

    function reloadWithNotice(message) {
        try {
            sessionStorage.setItem('instructor_workout_notice', message);
        } catch {
            // Continue with the reload when session storage is unavailable.
        }
        window.location.reload();
    }

    function setModalVisible(modal, visible) {
        modal.classList.toggle('active', visible);
        modal.setAttribute('aria-hidden', visible ? 'false' : 'true');
    }

    function makeNumberField(labelText, name, min, max) {
        const wrapper = document.createElement('label');
        wrapper.className = 'workouts-field';
        const label = document.createElement('span');
        label.textContent = labelText.toUpperCase();
        const input = document.createElement('input');
        input.className = 'cal-input-field';
        input.type = 'number';
        input.name = name;
        input.min = String(min);
        input.max = String(max);
        input.step = '1';
        input.required = true;
        wrapper.append(label, input);
        return wrapper;
    }

    function makeExerciseRow(exercise = null) {
        const row = document.createElement('div');
        row.className = 'workouts-exercise-row';
        const top = document.createElement('div');
        top.className = 'workouts-exercise-row-top';
        const label = document.createElement('span');
        label.textContent = 'EXERCISE';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'workouts-remove-exercise';
        remove.setAttribute('aria-label', 'Remove exercise');
        remove.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">delete</span><span>Remove</span>';
        top.append(label, remove);

        const autocomplete = document.createElement('div');
        autocomplete.className = 'exercise-autocomplete';
        const search = document.createElement('input');
        search.className = 'cal-input-field';
        search.name = 'exercise_name[]';
        search.type = 'text';
        search.maxLength = 100;
        search.placeholder = 'Search or enter an exercise name';
        search.autocomplete = 'off';
        search.required = true;
        search.dataset.exerciseSearch = '1';
        const exerciseId = document.createElement('input');
        exerciseId.type = 'hidden';
        exerciseId.name = 'exercise_id[]';
        exerciseId.dataset.exerciseId = '1';
        const suggestions = document.createElement('div');
        suggestions.className = 'exercise-autocomplete-list';
        suggestions.hidden = true;
        suggestions.dataset.exerciseSuggestions = '1';
        const message = document.createElement('div');
        message.className = 'exercise-autocomplete-message';
        message.hidden = true;
        message.dataset.exerciseMessage = '1';
        autocomplete.append(search, exerciseId, suggestions, message);

        const measurements = document.createElement('div');
        measurements.className = 'workouts-exercise-measurements';
        const sets = makeNumberField('Sets', 'exercise_sets[]', 1, 1000);
        const reps = makeNumberField('Reps', 'exercise_reps[]', 1, 1000);
        measurements.append(sets, reps);

        remove.addEventListener('click', () => {
            if (exerciseContainer.children.length > 1) row.remove();
            else showStatus('Each recommendation needs at least one exercise.', false);
        });
        row.append(top, autocomplete, measurements);
        if (exercise) {
            search.value = exercise.Exercise_Name || '';
            exerciseId.value = exercise.Exercise_ID || '';
            sets.querySelector('input').value = exercise.Sets;
            reps.querySelector('input').value = exercise.Reps;
        }
        window.FitCampusExerciseAutocomplete.initializeWithin(autocomplete);
        return row;
    }

    function resetForm() {
        form.reset();
        showFormStatus('', true);
        form.elements.action.value = 'create';
        form.elements.workout_id.value = '';
        document.getElementById('workout-form-title').textContent = 'Create recommendation';
        document.getElementById('instructor-workout-submit').textContent = 'Publish recommendation';
        exerciseContainer.replaceChildren(makeExerciseRow());
    }

    async function sendAction(data) {
        const response = await fetch(actionUrl, { method: 'POST', body: data, credentials: 'same-origin' });
        let result;
        try {
            result = await response.json();
        } catch {
            throw new Error('The server returned an unexpected response. Please try again.');
        }
        if (!response.ok || !result.success) throw new Error(result.message || 'The request could not be completed.');
        return result;
    }

    function showDetails(workout) {
        document.getElementById('workout-details-title').textContent = workout.Title;
        document.getElementById('workout-details-meta').textContent =
            `${workout.Workout_Level || 'General'} · ${workout.Duration ? `${workout.Duration} minutes` : 'Duration not set'} · ${workout.Exercises.length} exercises · Published`;
        document.getElementById('workout-details-description').textContent = workout.Description || 'No instructions provided.';
        const list = document.getElementById('workout-details-exercises');
        list.replaceChildren();
        workout.Exercises.forEach(exercise => {
            const item = document.createElement('li');
            item.className = 'wk-modal-list-item';
            const name = document.createElement('span');
            name.className = 'wk-modal-ex-name';
            name.textContent = exercise.Exercise_Name;
            const details = document.createElement('span');
            details.className = 'wk-modal-ex-reps';
            details.textContent = `${exercise.Sets} sets × ${exercise.Reps} reps`;
            item.append(name, details);
            list.append(item);
        });
        setModalVisible(detailsModal, true);
    }

    document.getElementById('open-create-workout').addEventListener('click', () => {
        showStatus('', true);
        resetForm();
        setModalVisible(formModal, true);
        form.elements.title.focus();
    });

    try {
        const notice = sessionStorage.getItem('instructor_workout_notice');
        if (notice) {
            sessionStorage.removeItem('instructor_workout_notice');
            showStatus(notice, true);
        }
    } catch {
        // The page remains usable when session storage is unavailable.
    }

    document.getElementById('add-instructor-exercise').addEventListener('click', () => {
        if (exerciseContainer.children.length >= 40) {
            showStatus('A recommendation can contain up to 40 exercises.', false);
            return;
        }
        exerciseContainer.append(makeExerciseRow());
    });

    document.querySelectorAll('[data-close-workouts-modal]').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.workouts-modal-overlay');
            if (modal) setModalVisible(modal, false);
        });
    });
    document.querySelectorAll('.workouts-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', event => {
            if (event.target === overlay) setModalVisible(overlay, false);
        });
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.workouts-modal-overlay.active').forEach(modal => setModalVisible(modal, false));
        }
    });

    document.querySelectorAll('.workouts-card').forEach(card => {
        const workout = JSON.parse(card.dataset.workout);
        card.querySelector('.workouts-view-btn').addEventListener('click', () => showDetails(workout));
        card.querySelector('.workouts-edit-btn').addEventListener('click', () => {
            resetForm();
            form.elements.action.value = 'update';
            form.elements.workout_id.value = workout.Common_Workout_ID;
            form.elements.title.value = workout.Title;
            form.elements.level.value = workout.Workout_Level || '';
            form.elements.duration.value = workout.Duration || '';
            form.elements.description.value = workout.Description || '';
            document.getElementById('workout-form-title').textContent = 'Edit recommendation';
            document.getElementById('instructor-workout-submit').textContent = 'Save and publish changes';
            exerciseContainer.replaceChildren(
                ...(workout.Exercises.length ? workout.Exercises.map(makeExerciseRow) : [makeExerciseRow()])
            );
            setModalVisible(formModal, true);
            form.elements.title.focus();
        });
        card.querySelector('.workouts-delete-btn').addEventListener('click', async () => {
            if (!window.confirm(`Delete "${workout.Title}"? Members' saved bookmark will be removed; copied personal routines remain unchanged.`)) return;
            const data = new FormData();
            data.append('csrf_token', form.elements.csrf_token.value);
            data.append('action', 'delete');
            data.append('workout_id', workout.Common_Workout_ID);
            try {
                await sendAction(data);
                reloadWithNotice('Recommendation deleted.');
            } catch (error) {
                showStatus(error.message, false);
            }
        });
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const submit = document.getElementById('instructor-workout-submit');
        const originalLabel = submit.textContent;
        submit.disabled = true;
        submit.textContent = 'Saving…';
        showFormStatus('Saving and publishing your recommendation…', true);
        try {
            await sendAction(new FormData(form));
            setModalVisible(formModal, false);
            reloadWithNotice(form.elements.action.value === 'create'
                ? 'Recommendation published and visible to members.'
                : 'Recommendation updated and republished.');
        } catch (error) {
            showFormStatus(error.message);
        } finally {
            submit.disabled = false;
            submit.textContent = originalLabel;
        }
    });

    resetForm();
});

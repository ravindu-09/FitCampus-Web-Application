document.addEventListener('DOMContentLoaded', () => {
    const actionUrl = '../../controllers/member/workout_action.php';
    const status = document.getElementById('workout-status');
    const routineModal = document.getElementById('routine-modal');
    const detailsModal = document.getElementById('details-modal');
    const form = document.getElementById('routine-form');
    const exerciseContainer = document.getElementById('routine-exercises');

    function showStatus(message, success) {
        status.textContent = message;
        status.classList.toggle('is-success', success);
        status.classList.toggle('is-error', !success);
        status.hidden = !message;
    }

    function showFormStatus(message, success = false) {
        const formStatus = document.getElementById('routine-form-status');
        formStatus.textContent = message;
        formStatus.classList.toggle('is-success', success);
        formStatus.classList.toggle('is-error', !success);
        formStatus.hidden = !message;
    }

    function reloadWithNotice(message) {
        try {
            sessionStorage.setItem('member_workout_notice', message);
        } catch {
            // Continue with the reload when session storage is unavailable.
        }
        window.location.reload();
    }

    function setModalVisible(modal, visible) {
        modal.classList.toggle('active', visible);
        modal.setAttribute('aria-hidden', visible ? 'false' : 'true');
    }

    function makeExerciseRow(exercise = null) {
        const row = document.createElement('div');
        row.className = 'wk-exercise-row';

        const top = document.createElement('div');
        top.className = 'wk-exercise-row-top';
        const label = document.createElement('label');
        label.textContent = 'EXERCISE';
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'wk-remove-exercise';
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

        const measurement = document.createElement('div');
        measurement.className = 'wk-exercise-measurements';
        const setsField = makeNumberField('Sets', 'exercise_sets[]', 1, 1000);
        const repsField = makeNumberField('Reps', 'exercise_reps[]', 1, 1000);
        measurement.append(setsField, repsField);

        remove.addEventListener('click', () => {
            if (exerciseContainer.children.length > 1) row.remove();
            else showStatus('Each workout needs at least one exercise.', false);
        });

        row.append(top, autocomplete, measurement);
        if (exercise) {
            search.value = exercise.Exercise_Name || '';
            exerciseId.value = exercise.Exercise_ID || '';
            setsField.querySelector('input').value = exercise.Sets;
            repsField.querySelector('input').value = exercise.Reps;
        }
        window.FitCampusExerciseAutocomplete.initializeWithin(autocomplete);
        return row;
    }

    function makeNumberField(labelText, name, min, max) {
        const wrapper = document.createElement('div');
        const label = document.createElement('label');
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

    function resetRoutineForm() {
        form.reset();
        showFormStatus('', true);
        form.elements.action.value = 'create';
        form.elements.workout_id.value = '';
        document.getElementById('routine-modal-title').textContent = 'Create personal routine';
        document.getElementById('routine-submit').textContent = 'Save routine';
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

    function openDetails(workout) {
        document.getElementById('details-title').textContent = workout.Title;
        const duration = workout.Duration ? `${workout.Duration} minutes` : '';
        const level = workout.Workout_Level ? ` · ${workout.Workout_Level}` : '';
        const instructor = workout.Instructor_Name ? ` · By ${workout.Instructor_Name}` : '';
        document.getElementById('details-meta').textContent =
            `${workout.Exercises.length} exercises${duration ? ` · ${duration}` : ''}${level}${instructor}`;
        document.getElementById('details-description').textContent = workout.Description || '';
        const list = document.getElementById('details-exercises');
        list.replaceChildren();
        workout.Exercises.forEach(exercise => {
            const item = document.createElement('li');
            item.className = 'wk-modal-list-item';
            const name = document.createElement('span');
            name.className = 'wk-modal-ex-name';
            name.textContent = exercise.Exercise_Name;
            const reps = document.createElement('span');
            reps.className = 'wk-modal-ex-reps';
            reps.textContent = `${exercise.Sets} sets × ${exercise.Reps} reps`;
            item.append(name, reps);
            list.append(item);
        });
        setModalVisible(detailsModal, true);
    }

    document.getElementById('btn-open-create-modal').addEventListener('click', () => {
        showStatus('', true);
        resetRoutineForm();
        setModalVisible(routineModal, true);
        document.getElementById('routine-title').focus();
    });

    try {
        const notice = sessionStorage.getItem('member_workout_notice');
        if (notice) {
            sessionStorage.removeItem('member_workout_notice');
            showStatus(notice, true);
        }
    } catch {
        // The page remains usable when session storage is unavailable.
    }

    document.getElementById('add-routine-exercise').addEventListener('click', () => {
        if (exerciseContainer.children.length >= 40) {
            showStatus('A workout can contain up to 40 exercises.', false);
            return;
        }
        exerciseContainer.append(makeExerciseRow());
    });

    document.querySelectorAll('[data-close-modal]').forEach(button => {
        button.addEventListener('click', () => setModalVisible(document.getElementById(button.dataset.closeModal), false));
    });

    document.querySelectorAll('.wk-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', event => {
            if (event.target === overlay) setModalVisible(overlay, false);
        });
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.wk-modal-overlay.active').forEach(modal => setModalVisible(modal, false));
        }
    });

    document.querySelectorAll('.wk-workout-card').forEach(card => {
        const workout = JSON.parse(card.dataset.workout);
        card.querySelector('.wk-view-details').addEventListener('click', () => openDetails(workout));

        const editButton = card.querySelector('.wk-edit-workout');
        if (editButton) {
            editButton.addEventListener('click', () => {
                resetRoutineForm();
                form.elements.action.value = 'update';
                form.elements.workout_id.value = workout.Personal_Workout_ID;
                form.elements.title.value = workout.Title;
                form.elements.duration.value = workout.Duration || '';
                document.getElementById('routine-modal-title').textContent = 'Edit personal routine';
                document.getElementById('routine-submit').textContent = 'Save changes';
                exerciseContainer.replaceChildren(
                    ...(workout.Exercises.length ? workout.Exercises.map(makeExerciseRow) : [makeExerciseRow()])
                );
                setModalVisible(routineModal, true);
                document.getElementById('routine-title').focus();
            });
        }

        const deleteButton = card.querySelector('.wk-delete-workout');
        if (deleteButton) {
            deleteButton.addEventListener('click', async () => {
                if (!window.confirm(`Delete "${workout.Title}"? This cannot be undone.`)) return;
                const data = new FormData();
                data.append('csrf_token', form.elements.csrf_token.value);
                data.append('action', 'delete');
                data.append('workout_id', workout.Personal_Workout_ID);
                try {
                    await sendAction(data);
                    reloadWithNotice('Personal workout deleted.');
                } catch (error) {
                    showStatus(error.message, false);
                }
            });
        }

        const saveButton = card.querySelector('.wk-save-toggle');
        if (saveButton) {
            saveButton.addEventListener('click', async () => {
                const saved = saveButton.dataset.saved === '1';
                saveButton.disabled = true;
                const data = new FormData();
                data.append('csrf_token', form.elements.csrf_token.value);
                data.append('action', saved ? 'unsave' : 'save');
                data.append('workout_id', workout.Common_Workout_ID);
                try {
                    await sendAction(data);
                    saveButton.dataset.saved = saved ? '0' : '1';
                    saveButton.textContent = saved ? 'Save' : 'Unsave';
                    showStatus(saved ? 'Recommendation removed from saved workouts.' : 'Recommendation saved.', true);
                } catch (error) {
                    showStatus(error.message, false);
                } finally {
                    saveButton.disabled = false;
                }
            });
        }

        const copyButton = card.querySelector('.wk-copy-workout');
        if (copyButton) {
            copyButton.addEventListener('click', async () => {
                if (!window.confirm(`Copy "${workout.Title}" to your personal workouts?`)) return;
                copyButton.disabled = true;
                const data = new FormData();
                data.append('csrf_token', form.elements.csrf_token.value);
                data.append('action', 'copy');
                data.append('workout_id', workout.Common_Workout_ID);
                try {
                    const result = await sendAction(data);
                    showStatus(result.message, true);
                    window.location.reload();
                } catch (error) {
                    showStatus(error.message, false);
                    copyButton.disabled = false;
                }
            });
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const submit = document.getElementById('routine-submit');
        const originalLabel = submit.textContent;
        submit.disabled = true;
        submit.textContent = 'Saving…';
        showFormStatus('Saving your personal workout…', true);
        try {
            await sendAction(new FormData(form));
            setModalVisible(routineModal, false);
            reloadWithNotice(form.elements.action.value === 'create'
                ? 'Personal workout created.'
                : 'Personal workout updated.');
        } catch (error) {
            showFormStatus(error.message);
        } finally {
            submit.disabled = false;
            submit.textContent = originalLabel;
        }
    });

    resetRoutineForm();
});

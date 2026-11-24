(function () {
    const endpoint = '../../controllers/common/exercise_action.php';
    let instanceCounter = 0;

    function normalizeForComparison(value) {
        return value.trim().replace(/[\s\u00a0]+/g, ' ').toLocaleUpperCase();
    }

    function initialize(root) {
        const search = root.querySelector('[data-exercise-search]');
        const exerciseId = root.querySelector('[data-exercise-id]');
        const list = root.querySelector('[data-exercise-suggestions]');
        const message = root.querySelector('[data-exercise-message]');
        if (!search || !exerciseId || !list || root.dataset.autocompleteReady === '1') return;

        root.dataset.autocompleteReady = '1';
        const listId = `exercise-suggestions-${++instanceCounter}`;
        list.id = listId;
        search.setAttribute('role', 'combobox');
        search.setAttribute('aria-autocomplete', 'list');
        search.setAttribute('aria-controls', listId);
        search.setAttribute('aria-expanded', 'false');
        if (!search.hasAttribute('aria-label')) {
            search.setAttribute('aria-label', 'Exercise name');
        }

        let suggestions = [];
        let activeIndex = -1;
        let debounceTimer;
        let requestController;

        function closeList() {
            list.hidden = true;
            list.replaceChildren();
            search.setAttribute('aria-expanded', 'false');
            search.removeAttribute('aria-activedescendant');
            activeIndex = -1;
        }

        function setMessage(text, isError = false) {
            if (!message) return;
            message.textContent = text;
            message.hidden = !text;
            message.classList.toggle('is-error', isError);
        }

        function markSelected(item) {
            exerciseId.value = String(item.Exercise_ID);
            search.value = item.Exercise_Name;
            search.dataset.exerciseSelected = '1';
            setMessage('');
            closeList();
        }

        function renderList(items, canCreate) {
            list.replaceChildren();
            suggestions = items;
            activeIndex = -1;

            items.forEach((item, index) => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'exercise-autocomplete-option';
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                option.textContent = item.Exercise_Name;
                option.addEventListener('mousedown', event => event.preventDefault());
                option.addEventListener('click', () => markSelected(item));
                list.append(option);
            });

            if (canCreate) {
                const action = document.createElement('button');
                action.type = 'button';
                action.className = 'exercise-autocomplete-create';
                action.setAttribute('role', 'option');
                action.setAttribute('aria-selected', 'false');
                action.textContent = `Create “${normalizeForComparison(search.value)}” in shared catalogue`;
                action.addEventListener('mousedown', event => event.preventDefault());
                action.addEventListener('click', createExercise);
                list.append(action);
            }

            list.hidden = list.childElementCount === 0;
            search.setAttribute('aria-expanded', list.hidden ? 'false' : 'true');
        }

        function setActive(index) {
            const options = Array.from(list.querySelectorAll('[role="option"]'));
            if (!options.length) return;
            activeIndex = (index + options.length) % options.length;
            options.forEach((option, optionIndex) => {
                const active = optionIndex === activeIndex;
                option.setAttribute('aria-selected', active ? 'true' : 'false');
                option.classList.toggle('is-active', active);
            });
            search.setAttribute('aria-activedescendant', options[activeIndex].id || '');
            if (!options[activeIndex].id) {
                options[activeIndex].id = `${listId}-option-${activeIndex}`;
                search.setAttribute('aria-activedescendant', options[activeIndex].id);
            }
        }

        async function searchExercises(term) {
            requestController?.abort();
            requestController = new AbortController();
            const url = new URL(endpoint, window.location.href);
            url.searchParams.set('term', term);
            const response = await fetch(url, {
                credentials: 'same-origin',
                signal: requestController.signal,
                headers: { Accept: 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Exercise search failed.');
            return result;
        }

        async function createExercise() {
            const form = root.closest('form');
            const token = form?.querySelector('[name="csrf_token"]')?.value;
            if (!token) {
                setMessage('Reload the page before adding an exercise.', true);
                return;
            }

            const name = search.value;
            list.querySelector('.exercise-autocomplete-create')?.setAttribute('disabled', 'disabled');
            try {
                const data = new FormData();
                data.append('csrf_token', token);
                data.append('name', name);
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: data,
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' }
                });
                const result = await response.json();
                if (!response.ok || !result.success) throw new Error(result.message || 'Exercise creation failed.');
                markSelected({
                    Exercise_ID: result.exercise_id,
                    Exercise_Name: result.exercise_name
                });
                setMessage(result.created ? 'Added to the shared exercise catalogue.' : 'Matched and reused an existing exercise.');
            } catch (error) {
                setMessage(error.message || 'Exercise creation failed.', true);
            }
        }

        search.addEventListener('input', () => {
            exerciseId.value = '';
            search.dataset.exerciseSelected = '0';
            setMessage('');
            clearTimeout(debounceTimer);
            requestController?.abort();
            const term = search.value.trim();
            if (term.length < 2) {
                closeList();
                return;
            }
            debounceTimer = window.setTimeout(async () => {
                try {
                    const result = await searchExercises(term);
                    if (search.value.trim() !== term) return;
                    const items = result.exact
                        ? [result.exact, ...result.suggestions.filter(item => Number(item.Exercise_ID) !== Number(result.exact.Exercise_ID))]
                        : result.suggestions;
                    const canCreate = !result.exact
                        && !items.some(item => normalizeForComparison(item.Exercise_Name) === normalizeForComparison(term));
                    renderList(items, canCreate);
                } catch (error) {
                    if (error.name !== 'AbortError') setMessage(error.message || 'Exercise search failed.', true);
                }
            }, 220);
        });

        search.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown' && !list.hidden) {
                event.preventDefault();
                setActive(activeIndex + 1);
            } else if (event.key === 'ArrowUp' && !list.hidden) {
                event.preventDefault();
                setActive(activeIndex < 0 ? list.children.length - 1 : activeIndex - 1);
            } else if (event.key === 'Enter' && !list.hidden && activeIndex >= 0) {
                event.preventDefault();
                list.children[activeIndex].click();
            } else if (event.key === 'Escape') {
                closeList();
            }
        });

        search.addEventListener('blur', () => {
            window.setTimeout(closeList, 120);
        });

        if (exerciseId.value) search.dataset.exerciseSelected = '1';
    }

    window.FitCampusExerciseAutocomplete = {
        initializeWithin(container) {
            if (container.matches?.('.exercise-autocomplete')) initialize(container);
            container.querySelectorAll?.('.exercise-autocomplete').forEach(initialize);
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.FitCampusExerciseAutocomplete.initializeWithin(document);
    });
})();

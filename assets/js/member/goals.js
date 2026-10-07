// assets/js/member/goals.js

document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('edit-goal-modal');
    const detailsModal = document.getElementById('goal-details-modal');
    const createModal = document.getElementById('create-goal-modal');
    const progressModal = document.getElementById('update-progress-modal');

    const createGoalType = document.getElementById('create-goal-type');
    const startingWeightGroup = document.getElementById('starting-weight-group');
    const startingWeightInput = document.getElementById('create-start-value');

    const progressGoalId = document.getElementById('progress-goal-id');
    const progressGoalName = document.getElementById('progress-goal-name');
    const progressCurrentValue = document.getElementById('progress-current-value');
    const progressUpdateDate = document.getElementById('progress-update-date');
    const progressUnit = document.getElementById('progress-unit');

    const createTargetDate = document.getElementById('create-target-date');
    const editTargetDate = document.getElementById('edit-target-date');

    // ---------------------------------------------------------
    // NATIVE CALENDAR PICKERS
    // ---------------------------------------------------------

    function enableCalendarPicker(dateInput) {
        if (!dateInput) {
            return;
        }

        // Prevent manually typing dates
        dateInput.addEventListener('keydown', event => {
            event.preventDefault();
        });

        dateInput.addEventListener('paste', event => {
            event.preventDefault();
        });

        // Open browser calendar when field is clicked
        dateInput.addEventListener('click', () => {
            if (typeof dateInput.showPicker === 'function') {
                try {
                    dateInput.showPicker();
                } catch (error) {
                    // Native browser date control remains available.
                }
            }
        });
    }

    enableCalendarPicker(createTargetDate);
    enableCalendarPicker(editTargetDate);
    enableCalendarPicker(progressUpdateDate);

    // ---------------------------------------------------------
    // CLOSE ALL MODALS
    // ---------------------------------------------------------

    window.closeModals = function () {
        if (editModal) {
            editModal.classList.remove('active');
        }

        if (detailsModal) {
            detailsModal.classList.remove('active');
        }

        if (createModal) {
            createModal.classList.remove('active');
        }

        if (progressModal) {
            progressModal.classList.remove('active');
        }
    };

    // ---------------------------------------------------------
    // EDIT GOAL MODAL
    // ---------------------------------------------------------

    window.openEditGoalModal = function (goalName) {
        if (!editModal) {
            return;
        }

        const nameInput = document.getElementById('edit-goal-name');

        if (nameInput) {
            nameInput.value = goalName;
        }

        editModal.classList.add('active');
    };

    // ---------------------------------------------------------
    // CREATE GOAL MODAL
    // ---------------------------------------------------------

    function resetCreateGoalForm() {
        if (createGoalType) {
            createGoalType.value = '';
        }

        if (startingWeightGroup) {
            startingWeightGroup.style.display = 'none';
        }

        if (startingWeightInput) {
            startingWeightInput.value = '';
            startingWeightInput.required = false;
            startingWeightInput.disabled = true;
        }
    }

    window.openCreateGoalModal = function () {
        if (!createModal) {
            return;
        }

        resetCreateGoalForm();

        createModal.classList.add('active');
    };

    // ---------------------------------------------------------
    // SHOW STARTING WEIGHT ONLY FOR WEIGHT LOSS
    // ---------------------------------------------------------

    function handleGoalTypeChange() {
        if (
            !createGoalType ||
            !startingWeightGroup ||
            !startingWeightInput
        ) {
            return;
        }

        if (createGoalType.value === 'Weight Loss') {
            startingWeightGroup.style.display = '';
            startingWeightInput.disabled = false;
            startingWeightInput.required = true;
        } else {
            startingWeightGroup.style.display = 'none';
            startingWeightInput.value = '';
            startingWeightInput.required = false;
            startingWeightInput.disabled = true;
        }
    }

    if (createGoalType) {
        createGoalType.addEventListener(
            'change',
            handleGoalTypeChange
        );
    }

    // ---------------------------------------------------------
    // GOAL DETAILS MODAL
    // ---------------------------------------------------------

    window.openGoalDetailsModal = function (
        title,
        percentage,
        target,
        current,
        unit,
        badgeClass,
        badgeText,
        timeText
    ) {
        if (!detailsModal) {
            return;
        }

        const titleElement =
            document.getElementById('detail-title');

        const targetElement =
            document.getElementById('detail-target-val');

        const currentElement =
            document.getElementById('detail-current-val');

        const targetUnitElement =
            document.getElementById('detail-target-unit');

        const currentUnitElement =
            document.getElementById('detail-current-unit');

        const percentageElement =
            document.getElementById('detail-percentage');

        const timeElement =
            document.getElementById('detail-time');

        const badge =
            document.getElementById('detail-category-badge');

        if (titleElement) {
            titleElement.innerText = title;
        }

        if (targetElement) {
            targetElement.innerText = target;
        }

        if (currentElement) {
            currentElement.innerText = current;
        }

        if (targetUnitElement) {
            targetUnitElement.innerText = unit;
        }

        if (currentUnitElement) {
            currentUnitElement.innerText = unit;
        }

        if (percentageElement) {
            percentageElement.innerText =
                percentage + '%';
        }

        if (timeElement) {
            timeElement.innerText = timeText;
        }

        if (badge) {
            badge.className =
                `gl-badge ${badgeClass} uppercase gl-badge-sm`;

            badge.innerText = badgeText;
        }

        const barFill =
            document.getElementById('bar-progress-fill');

        if (barFill) {
            const cleanPercentage = Math.min(
                Math.max(
                    parseFloat(percentage) || 0,
                    0
                ),
                100
            );

            barFill.style.width = '0%';

            setTimeout(() => {
                barFill.style.width =
                    cleanPercentage + '%';
            }, 50);
        }

        detailsModal.classList.add('active');
    };

    // ---------------------------------------------------------
    // UPDATE PROGRESS MODAL
    // ---------------------------------------------------------

    window.openUpdateProgressModal = function (
        goalId,
        goalName,
        currentValue,
        unit
    ) {
        if (!progressModal) {
            return;
        }

        if (progressGoalId) {
            progressGoalId.value = goalId;
        }

        if (progressGoalName) {
            if (
                progressGoalName.tagName === 'INPUT' ||
                progressGoalName.tagName === 'TEXTAREA'
            ) {
                progressGoalName.value = goalName;
            } else {
                progressGoalName.innerText = goalName;
            }
        }

        if (progressCurrentValue) {
            progressCurrentValue.value = currentValue;
        }

        if (progressUnit) {
            progressUnit.innerText = unit;
        }

        if (progressUpdateDate) {
            const today = new Date();

            const year = today.getFullYear();

            const month = String(
                today.getMonth() + 1
            ).padStart(2, '0');

            const day = String(
                today.getDate()
            ).padStart(2, '0');

            const todayString =
                `${year}-${month}-${day}`;

            progressUpdateDate.value =
                todayString;

            progressUpdateDate.max =
                todayString;
        }

        progressModal.classList.add('active');
    };

    // ---------------------------------------------------------
    // CLOSE MODALS WHEN CLICKING OUTSIDE
    // ---------------------------------------------------------

    [
        editModal,
        detailsModal,
        createModal,
        progressModal
    ].forEach(modal => {

        if (!modal) {
            return;
        }

        modal.addEventListener(
            'click',
            event => {

                if (event.target === modal) {
                    window.closeModals();
                }
            }
        );
    });
});
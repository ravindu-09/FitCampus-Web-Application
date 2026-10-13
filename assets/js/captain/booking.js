// assets/js/captain/booking.js
document.addEventListener('DOMContentLoaded', () => {
    
    let currentGymId = 1; 
    let currentShift = 'morning';
    let selectedSlot = null;
    let facilityCapacity = 50;
    
    let currentDate = new Date();
    let currentWeekStart = new Date(currentDate);
    let dayOfWeek = currentWeekStart.getDay();
    let diffToMonday = currentWeekStart.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
    currentWeekStart.setDate(diffToMonday);
    
    let currentDayNames = []; 

    const assignmentModal = document.getElementById('assign-workout-modal');
    const assignmentForm = document.getElementById('assign-workout-form');
    const assignmentSelect = document.getElementById('assign-workout-select');
    const assignmentBookingId = document.getElementById('assign-workout-booking-id');
    const assignmentBookingLabel = document.getElementById('assign-workout-booking-label');
    const assignmentCurrentList = document.getElementById('assign-workout-current-list');
    const assignmentCurrentEmpty = document.getElementById('assign-workout-current-empty');
    const assignmentNoOptions = document.getElementById('assign-workout-no-options');
    const assignmentMessage = document.getElementById('assign-workout-message');
    const assignmentSubmit = document.getElementById('assign-workout-submit');
    let activeAssignmentRow = null;
    let activeAssignmentButton = null;

    function getAvailableWorkouts(button) {
        try {
            const workouts = JSON.parse(button.dataset.availableWorkouts || '[]');
            return Array.isArray(workouts) ? workouts : [];
        } catch (error) {
            return [];
        }
    }

    function renderAssignedWorkouts(row) {
        const rowList = row?.querySelector('[data-booking-assigned-list]');
        if (!rowList) return;

        const assignedItems = Array.from(rowList.querySelectorAll('[data-workout-id]'));
        assignmentCurrentList.replaceChildren();
        assignedItems.forEach((item) => {
            const listItem = document.createElement('li');
            listItem.textContent = item.textContent;
            assignmentCurrentList.appendChild(listItem);
        });
        assignmentCurrentEmpty.classList.toggle('hidden', assignedItems.length > 0);
    }

    function populateAvailableWorkouts(button) {
        const workouts = getAvailableWorkouts(button);
        assignmentSelect.replaceChildren(new Option('Select a reusable team workout', ''));
        workouts.forEach((workout) => {
            const duration = workout.Duration ? ` (${workout.Duration} min)` : '';
            const option = new Option(`${workout.Title}${duration}`, workout.Workout_ID);
            option.dataset.workoutTitle = workout.Title;
            assignmentSelect.add(option);
        });
        assignmentSelect.disabled = workouts.length === 0;
        assignmentSubmit.disabled = workouts.length === 0;
        assignmentNoOptions.classList.toggle('hidden', workouts.length > 0);
    }

    function closeAssignmentModal() {
        assignmentModal?.classList.remove('active');
        activeAssignmentRow = null;
        activeAssignmentButton = null;
    }

    document.addEventListener('click', (event) => {
        const assignButton = event.target.closest('.assign-workout-trigger');
        if (assignButton && assignmentModal) {
            activeAssignmentButton = assignButton;
            activeAssignmentRow = assignButton.closest('tr');
            assignmentBookingId.value = assignButton.dataset.bookingId;
            assignmentBookingLabel.textContent = `Booking #${assignButton.dataset.bookingId} · ${activeAssignmentRow.cells[1].textContent} · ${activeAssignmentRow.cells[2].textContent} · ${activeAssignmentRow.cells[3].textContent}`;
            assignmentMessage.textContent = '';
            assignmentMessage.classList.add('hidden');
            renderAssignedWorkouts(activeAssignmentRow);
            populateAvailableWorkouts(assignButton);
            assignmentModal.classList.add('active');
            return;
        }

        if (event.target.closest('#assign-workout-close, #assign-workout-cancel')
            || event.target === assignmentModal) {
            closeAssignmentModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeAssignmentModal();
    });

    assignmentForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!activeAssignmentRow || !assignmentSelect.value) return;

        const selectedWorkout = assignmentSelect.selectedOptions[0];
        const workoutId = assignmentSelect.value;
        const formData = new FormData();
        formData.append('action', 'assign_workout');
        formData.append('booking_id', assignmentBookingId.value);
        formData.append('workout_id', workoutId);
        assignmentSubmit.disabled = true;
        assignmentSubmit.textContent = 'Assigning...';

        try {
            const response = await fetch('../../controllers/captain/booking_action.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                assignmentMessage.textContent = result.error || 'Could not assign workout.';
                assignmentMessage.classList.remove('hidden');
                assignmentMessage.style.color = 'var(--error)';
                return;
            }

            const rowList = activeAssignmentRow.querySelector('[data-booking-assigned-list]');
            if (rowList && !rowList.querySelector(`[data-workout-id="${CSS.escape(workoutId)}"]`)) {
                rowList.querySelector('.no-assigned-workouts')?.remove();
                const listItem = document.createElement('li');
                listItem.dataset.workoutId = workoutId;
                listItem.textContent = selectedWorkout.dataset.workoutTitle || selectedWorkout.textContent;
                rowList.appendChild(listItem);
            }

            const remainingWorkouts = getAvailableWorkouts(activeAssignmentButton)
                .filter((workout) => String(workout.Workout_ID) !== String(workoutId));
            activeAssignmentButton.dataset.availableWorkouts = JSON.stringify(remainingWorkouts);
            renderAssignedWorkouts(activeAssignmentRow);
            populateAvailableWorkouts(activeAssignmentButton);
            assignmentMessage.textContent = result.message || 'Workout assigned.';
            assignmentMessage.classList.remove('hidden');
            assignmentMessage.style.color = 'var(--secondary)';
        } catch (error) {
            assignmentMessage.textContent = 'Network error. Could not assign workout.';
            assignmentMessage.classList.remove('hidden');
            assignmentMessage.style.color = 'var(--error)';
        } finally {
            assignmentSubmit.textContent = 'Assign';
            assignmentSubmit.disabled = assignmentSelect.disabled;
        }
    });

    const gymToggleContainer = document.getElementById('gym-toggle-container');
    if (gymToggleContainer) {
        const firstBtn = gymToggleContainer.querySelector('.toggle-btn');
        if (firstBtn && firstBtn.dataset.facilityId) {
            currentGymId = parseInt(firstBtn.dataset.facilityId);
        }
    }

    function updateCalendarHeaders() {
        const headerContainer = document.getElementById('calendar-grid-header');
        const weekRangeDisplay = document.getElementById('week-range-display');
        if (!headerContainer || !weekRangeDisplay) return;

        const days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        currentDayNames = [];
        let headerHTML = `<div class="sticky-col txt-muted font-xs uppercase tracking-wide">Time</div>`;

        let weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(weekEnd.getDate() + 6);

        const options = { month: 'short', day: 'numeric' };
        weekRangeDisplay.textContent = `${currentWeekStart.toLocaleDateString('en-US', options)} - ${weekEnd.toLocaleDateString('en-US', options)}, ${currentWeekStart.getFullYear()}`;

        for (let i = 0; i < 7; i++) {
            let d = new Date(currentWeekStart);
            d.setDate(d.getDate() + i);
            let dateNum = d.getDate();
            let monthShort = d.toLocaleString('default', { month: 'short' });
            
            const fullDateStr = `${monthShort} ${dateNum}`;
            currentDayNames.push(fullDateStr);

            const isToday = (d.toDateString() === new Date().toDateString()) ? 'txt-green' : 'txt-primary';

            headerHTML += `
                <div class="grid-day-head">
                    <p>${days[i]}</p>
                    <p class="date font-bold ${isToday}">${dateNum}</p>
                </div>`;
        }
        headerContainer.innerHTML = headerHTML;
    }

    document.getElementById('btn-prev-week')?.addEventListener('click', () => {
        currentWeekStart.setDate(currentWeekStart.getDate() - 7);
        updateCalendarHeaders();
        selectedSlot = null;
        fetchAndResetForm();
    });

    document.getElementById('btn-next-week')?.addEventListener('click', () => {
        currentWeekStart.setDate(currentWeekStart.getDate() + 7);
        updateCalendarHeaders();
        selectedSlot = null;
        fetchAndResetForm();
    });

    function fetchAndResetForm() {
        selectedSlot = null;
        document.getElementById('capacity-info-container').classList.add('hidden');
        document.getElementById('selected-date-display').textContent = 'Select a slot';
        const timeSelect = document.getElementById('start-time-select');
        timeSelect.innerHTML = `<option value="">--:--</option>`;
        timeSelect.disabled = true;
        document.getElementById('submit-booking-btn').disabled = true;
        fetchAndRenderGrid();
    }

    function fetchAndRenderGrid() {
        const gridBody = document.getElementById('schedule-grid-body');
        if (!gridBody) return;

        gridBody.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--on-surface-variant);">Loading schedule...</div>';
        
        const dateKey = `${currentWeekStart.getFullYear()}-${String(currentWeekStart.getMonth() + 1).padStart(2, '0')}-${String(currentWeekStart.getDate()).padStart(2, '0')}`;

        fetch(`../../controllers/captain/booking_action.php?action=get_schedule&facility_id=${currentGymId}&shift=${currentShift}&start_date=${dateKey}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    facilityCapacity = data.capacity;
                    renderGrid(data.gridData);
                } else {
                    gridBody.innerHTML = `<div style="padding: 24px; text-align: center; color: var(--error);">${data.error || 'Error loading data.'}</div>`;
                }
            })
            .catch(err => {
                console.error('Fetch Error:', err);
                gridBody.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--error);">Network Error. Backend running?</div>';
            });
    }

    function renderGrid(gridData) {
        const gridBody = document.getElementById('schedule-grid-body');
        let html = '';
        
        const nowPlus24h = new Date().getTime() + (24 * 3600 * 1000);
        
        gridData.forEach(row => {
            const time = row[0]; 
            html += `<div class="calendar-grid-row">`;
            html += `<div class="sticky-col txt-muted font-xs tracking-wide">${time}</div>`;
            
            for (let i = 1; i <= 7; i++) {
                const cellData = String(row[i]);
                let isPending = cellData.startsWith('pending');
                let occVal = isPending ? parseInt(cellData.split('_')[1] || 0) : parseInt(cellData);
                
                const availableSpaces = Math.floor(facilityCapacity * (1 - (occVal / 100)));
                
                let styleClass = '';
                let icon = '';
                let isSelectable = false;
                let textDisplay = '';

                let tempD = new Date(currentWeekStart);
                tempD.setDate(tempD.getDate() + (i - 1));
                const exactDbDate = `${tempD.getFullYear()}-${String(tempD.getMonth() + 1).padStart(2, '0')}-${String(tempD.getDate()).padStart(2, '0')}`;

                const [year, month, day] = exactDbDate.split('-').map(Number);
                const [hours, minutes] = time.split(':').map(Number);
                const slotDateTime = new Date(year, month - 1, day, hours, minutes, 0).getTime();

                const isWithin24Hours = slotDateTime < nowPlus24h;

                if (isWithin24Hours) {
                    styleClass = 'slot-full';
                    icon = 'lock';
                    textDisplay = 'Locked';
                    isSelectable = false;
                } else if (isPending) {
                    styleClass = 'slot-pending-state';
                    icon = 'hourglass_empty';
                    textDisplay = 'Pending';
                    isSelectable = true;
                } else if (occVal >= 100) {
                    styleClass = 'slot-full';
                    icon = 'block';
                    textDisplay = 'Full';
                    isSelectable = false; 
                } else {
                    isSelectable = true;
                    textDisplay = occVal + '%';
                    if (occVal === 0) {
                        styleClass = 'slot-available';
                        icon = 'add_circle';
                    } else if (occVal <= 90) {
                        styleClass = 'slot-moderate';
                        icon = 'add_circle';
                    } else {
                        styleClass = 'slot-high';
                        icon = 'add_circle';
                    }
                }

                let dataAttrs = `data-time="${time}:00" data-day="${currentDayNames[i-1]}" data-dbdate="${exactDbDate}" data-available="${availableSpaces}" data-occupancy="${occVal}"`;
                if(isSelectable) dataAttrs += ` data-selectable="true"`;

                html += `
                    <div class="slot-cell">
                        <div class="slot-box ${styleClass}" ${dataAttrs}>
                            <span class="material-symbols-outlined">${icon}</span>
                            <span class="txt mono">${textDisplay}</span>
                        </div>
                    </div>`;
            }
            html += `</div>`;
        });
        
        gridBody.innerHTML = html;
        attachSlotListeners();
        
        if (selectedSlot) highlightSelectedSlots();
    }

    function attachSlotListeners() {
        document.querySelectorAll('.calendar-grid-row [data-selectable="true"]').forEach(slot => {
            slot.addEventListener('click', function() {
                selectedSlot = {
                    time: this.getAttribute('data-time'),
                    day: this.getAttribute('data-day'),
                    dbdate: this.getAttribute('data-dbdate'),
                    available: parseInt(this.getAttribute('data-available')),
                    occupancy: parseInt(this.getAttribute('data-occupancy'))
                };
                updateFormUI();
            });
        });
    }

    function addOneHour(timeStr) {
        let [hours, minutes, seconds] = timeStr.split(':').map(Number);
        hours = (hours + 1) % 24;
        return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }

    function highlightSelectedSlots() {
        document.querySelectorAll('.slot-box').forEach(s => {
            s.classList.remove('slot-selected');
            const iconSpan = s.querySelector('.material-symbols-outlined');
            if (iconSpan) {
                if (s.classList.contains('slot-full')) iconSpan.textContent = s.querySelector('.txt').textContent === 'Locked' ? 'lock' : 'block';
                else if (s.classList.contains('slot-pending-state')) iconSpan.textContent = 'hourglass_empty';
                else iconSpan.textContent = 'add_circle';
            }
        });

        if (!selectedSlot) return;

        const duration = parseInt(document.getElementById('duration-select').value) || 1;
        let currentTime = selectedSlot.time;
        let minAvailable = selectedSlot.available;

        for (let d = 0; d < duration; d++) {
            const targetCell = document.querySelector(`.slot-box[data-dbdate="${selectedSlot.dbdate}"][data-time="${currentTime}"]`);
            if (targetCell) {
                if (targetCell.getAttribute('data-selectable') === 'true') {
                    targetCell.classList.add('slot-selected');
                    const iconSpan = targetCell.querySelector('.material-symbols-outlined');
                    if (iconSpan) iconSpan.textContent = 'check_circle';
                    
                    const slotAvail = parseInt(targetCell.getAttribute('data-available')) || 0;
                    if (slotAvail < minAvailable) {
                        minAvailable = slotAvail;
                    }
                } else {
                    break;
                }
            }
            currentTime = addOneHour(currentTime);
        }

        selectedSlot.effectiveAvailable = minAvailable;
    }

    document.getElementById('duration-select')?.addEventListener('change', () => {
        if (selectedSlot) {
            updateFormUI();
        }
    });

    function updateFormUI() {
        if (selectedSlot) {
            document.getElementById('selected-date-display').textContent = selectedSlot.day + `, ${currentWeekStart.getFullYear()}`;
            
            const timeSelect = document.getElementById('start-time-select');
            timeSelect.innerHTML = `<option value="${selectedSlot.time}">${selectedSlot.time.substring(0,5)}</option>`;
            timeSelect.disabled = false;
            
            highlightSelectedSlots();

            document.getElementById('capacity-info-container').classList.remove('hidden');
            const remainingVal = document.getElementById('remaining-capacity-val');
            remainingVal.textContent = selectedSlot.effectiveAvailable;
            
            if (selectedSlot.effectiveAvailable > Math.floor(facilityCapacity * 0.3)) {
                remainingVal.className = 'txt-green font-bold text-sm';
            } else if (selectedSlot.effectiveAvailable > Math.floor(facilityCapacity * 0.1)) {
                remainingVal.className = 'txt-tertiary font-bold text-sm';
            } else {
                remainingVal.className = 'txt-error font-bold text-sm';
            }
            
            validateFrontendLimits();
        }
    }

    const teamSelect = document.getElementById('team-select');
    const teamSizeInput = document.getElementById('team-size-input');
    let maxAllowedSize = 0;

    if (teamSelect) {
        teamSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            maxAllowedSize = parseInt(selectedOption.getAttribute('data-size')) || 0;
            teamSizeInput.value = maxAllowedSize; 
            validateFrontendLimits();
        });
    }

    teamSizeInput?.addEventListener('input', () => {
        validateFrontendLimits();
    });

    function validateFrontendLimits() {
        const errorMsg = document.getElementById('capacity-error-msg');
        const submitBtn = document.getElementById('submit-booking-btn');
        const limitToggle = document.getElementById('limit-toggle');
        
        if (!selectedSlot || !teamSelect.value) {
            submitBtn.disabled = true;
            return;
        }

        const teamSize = parseInt(teamSizeInput.value) || 0;
        errorMsg.classList.add('hidden');
        errorMsg.textContent = '';
        teamSizeInput.style.borderColor = '';

        if (teamSize > maxAllowedSize) {
            errorMsg.textContent = `Team size cannot exceed your assigned team member count (${maxAllowedSize}).`;
            errorMsg.classList.remove('hidden');
            submitBtn.disabled = true;
            teamSizeInput.style.borderColor = 'var(--error)';
            return;
        }

        if (teamSize > 0) {
            submitBtn.disabled = false;
            if (teamSize > selectedSlot.effectiveAvailable && !limitToggle.checked) {
                errorMsg.textContent = `Capacity Exceeded in selected duration slots! Tick "Special Request" to proceed.`;
                errorMsg.classList.remove('hidden');
                submitBtn.disabled = true;
                teamSizeInput.style.borderColor = 'var(--error)';
            }
        } else {
            submitBtn.disabled = true;
        }
    }

    document.querySelectorAll('#gym-toggle-container .toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#gym-toggle-container .toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentGymId = parseInt(this.getAttribute('data-facility-id'));
            fetchAndResetForm();
        });
    });

    document.querySelectorAll('#time-toggle-container .toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#time-toggle-container .toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentShift = this.id === 'btn-morning' ? 'morning' : 'evening';
            fetchAndResetForm();
        });
    });

    updateCalendarHeaders();
    fetchAndRenderGrid();

    const limitToggle = document.getElementById('limit-toggle');
    const specialRequestField = document.getElementById('special-request-field');
    const submitBtn = document.getElementById('submit-booking-btn');
    const modal = document.getElementById('confirmation-modal');
    
    if (limitToggle) {
        limitToggle.addEventListener('change', (e) => {
            const btnText = document.getElementById('btn-text');
            const btnIcon = document.getElementById('btn-icon');
            const bookingNote = document.getElementById('booking-note');

            if (e.target.checked) {
                specialRequestField.classList.remove('hidden');
                btnText.textContent = 'Submit Special Request';
                btnIcon.textContent = 'send';
                submitBtn.style.background = 'rgba(247, 190, 29, 0.2)';
                submitBtn.style.color = 'var(--tertiary)';
                bookingNote.textContent = 'Special requests require department approval.';
            } else {
                specialRequestField.classList.add('hidden');
                btnText.textContent = 'Confirm Selection';
                btnIcon.textContent = 'check_circle';
                submitBtn.style.background = '';
                submitBtn.style.color = '';
                bookingNote.textContent = 'Standard bookings are instantly reserved.';
            }
            validateFrontendLimits();
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            if (submitBtn.disabled) return;
            const errorMsg = document.getElementById('capacity-error-msg');
            
            const formData = new FormData();
            formData.append('action', 'create_booking');
            formData.append('facility_id', currentGymId);
            formData.append('date', selectedSlot.dbdate); 
            formData.append('time', selectedSlot.time);
            formData.append('duration', document.getElementById('duration-select').value);
            formData.append('team_id', teamSelect.value); 
            formData.append('team_size', teamSizeInput.value);
            formData.append('is_special', limitToggle.checked);
            
            if(limitToggle.checked) {
                formData.append('reason', document.getElementById('reason-input').value);
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-[20px] animate-spin">sync</span> Processing...';

            fetch('../../controllers/captain/booking_action.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal(data.status);
                    
                    document.getElementById('facility-booking-form').reset();
                    if(limitToggle.checked) limitToggle.click();
                    
                    fetchAndResetForm(); 

                    const tbody = document.querySelector('.admin-data-table tbody');
                    if (tbody) {
                        const emptyRow = tbody.querySelector('td[colspan="7"]');
                        if (emptyRow) emptyRow.parentElement.remove();

                        const b = data.new_booking;
                        if (!b.Booking_ID) return;
                        let badgeStyle = '';
                        if (b.Status === 'Approved') {
                            badgeStyle = 'background: rgba(74,225,118,0.1); border-color: rgba(74,225,118,0.3); color: var(--secondary);';
                        } else {
                            badgeStyle = 'background: rgba(247,190,29,0.1); border-color: rgba(247,190,29,0.3); color: var(--tertiary);';
                        }

                        const tr = document.createElement('tr');
                        tr.id = `booking-row-${b.Booking_ID}`;

                        const facilityCell = document.createElement('td');
                        const facilityName = document.createElement('strong');
                        facilityName.textContent = b.Facility_Name;
                        facilityCell.appendChild(facilityName);
                        tr.appendChild(facilityCell);

                        [b.Team_Name, b.Reserve_Date, b.Time_Slot].forEach((value, index) => {
                            const cell = document.createElement('td');
                            const valueElement = document.createElement('span');
                            valueElement.className = 'mono';
                            valueElement.textContent = value;
                            if (index === 0) cell.textContent = value;
                            else cell.appendChild(valueElement);
                            tr.appendChild(cell);
                        });

                        const statusCell = document.createElement('td');
                        const statusBadge = document.createElement('span');
                        statusBadge.className = 'badge-status';
                        statusBadge.setAttribute('style', badgeStyle);
                        statusBadge.textContent = b.Status;
                        statusCell.appendChild(statusBadge);
                        tr.appendChild(statusCell);

                        const assignedCell = document.createElement('td');
                        if (b.Status === 'Approved') {
                            const assignedList = document.createElement('ul');
                            assignedList.className = 'booking-assigned-workouts';
                            assignedList.dataset.bookingAssignedList = '';
                            const emptyMessage = document.createElement('li');
                            emptyMessage.className = 'no-assigned-workouts txt-muted';
                            emptyMessage.textContent = 'No workouts assigned';
                            assignedList.appendChild(emptyMessage);
                            assignedCell.appendChild(assignedList);
                        } else {
                            assignedCell.textContent = '—';
                            assignedCell.className = 'txt-muted';
                        }
                        tr.appendChild(assignedCell);

                        const actionsCell = document.createElement('td');
                        actionsCell.className = 'text-right';
                        if (b.Status === 'Approved') {
                            const assignButton = document.createElement('button');
                            assignButton.type = 'button';
                            assignButton.className = 'toggle-btn assign-workout-trigger';
                            assignButton.dataset.bookingId = b.Booking_ID;
                            assignButton.dataset.availableWorkouts = JSON.stringify(b.Available_Workouts || []);
                            assignButton.style.cssText = 'padding: 4px 8px; font-size: 11px;';
                            assignButton.textContent = 'Assign Workout';
                            actionsCell.appendChild(assignButton);
                        }

                        const deleteButton = document.createElement('button');
                        deleteButton.type = 'button';
                        deleteButton.className = 'toggle-btn txt-error delete-booking-btn';
                        deleteButton.dataset.id = b.Booking_ID;
                        deleteButton.style.cssText = 'padding: 4px 8px; font-size: 11px;';
                        deleteButton.textContent = 'Delete';
                        actionsCell.appendChild(deleteButton);
                        tr.appendChild(actionsCell);
                        tbody.prepend(tr);
                    }

                } else {
                    errorMsg.textContent = data.error;
                    errorMsg.classList.remove('hidden');
                }
            })
            .catch(err => {
                errorMsg.textContent = "Network Error. Could not submit.";
                errorMsg.classList.remove('hidden');
            })
            .finally(() => {
                submitBtn.disabled = false;
                const isSpec = limitToggle.checked;
                submitBtn.innerHTML = `<span class="material-symbols-outlined text-[20px]" id="btn-icon">${isSpec ? 'send' : 'check_circle'}</span> <span id="btn-text">${isSpec ? 'Submit Special Request' : 'Confirm Selection'}</span>`;
            });
        });
    }

    // Delete Booking Event Listener
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-booking-btn')) {
            const btn = e.target.closest('.delete-booking-btn');
            const bookingId = btn.getAttribute('data-id');

            if (confirm('Are you sure you want to delete this booking?')) {
                const formData = new FormData();
                formData.append('action', 'delete_booking');
                formData.append('booking_id', bookingId);

                fetch('../../controllers/captain/booking_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const row = document.getElementById(`booking-row-${bookingId}`);
                        if (row) row.remove();
                        fetchAndResetForm();
                    } else {
                        alert(data.error || 'Failed to delete booking.');
                    }
                })
                .catch(err => {
                    console.error('Delete Error:', err);
                    alert('Network error. Could not delete booking.');
                });
            }
        }
    });

    function showSuccessModal(status) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            document.getElementById('modal-box-inner').classList.remove('scale-95');
        }, 10);

        const modalTitle = document.getElementById('modal-title');
        const modalDesc = document.getElementById('modal-desc');
        const modalIconBox = document.getElementById('modal-icon-bg');
        const modalIcon = document.getElementById('modal-icon');

        if (status === 'Pending') {
            modalTitle.textContent = 'Request Submitted';
            modalDesc.textContent = 'Your special request has been sent for department review. You will be notified soon.';
            modalIconBox.className = 'w-16 h-16 rounded-full flex-items-center justify-center mx-auto mb-6 bg-tertiary-dim txt-tertiary';
            modalIcon.textContent = 'schedule_send';
        } else {
            modalTitle.textContent = 'Reservation Confirmed';
            modalDesc.textContent = 'Your standard session has been instantly reserved and added to your team schedule.';
            modalIconBox.className = 'w-16 h-16 rounded-full flex-items-center justify-center mx-auto mb-6 bg-secondary-dim txt-green';
            modalIcon.textContent = 'task_alt';
        }
    }

    document.querySelectorAll('.close-modal-action').forEach(btn => {
        btn.addEventListener('click', () => {
            modal.classList.add('opacity-0');
            document.getElementById('modal-box-inner').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        });
    });
});
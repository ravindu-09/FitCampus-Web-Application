// assets/js/captain/booking.js
document.addEventListener('DOMContentLoaded', () => {
    
    // --- State Variables ---
    let currentGymId = 1; 
    let currentShift = 'morning';
    let selectedSlot = null;
    let facilityCapacity = 50;
    
    // --- Date Initialization (Set to Monday of the current week) ---
    let currentDate = new Date();
    let currentWeekStart = new Date(currentDate);
    let dayOfWeek = currentWeekStart.getDay();
    let diffToMonday = currentWeekStart.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
    currentWeekStart.setDate(diffToMonday);
    
    let currentDayNames = []; 

    // --- Initialize Facility Selection ---
    const gymToggleContainer = document.getElementById('gym-toggle-container');
    if (gymToggleContainer) {
        const firstBtn = gymToggleContainer.querySelector('.toggle-btn');
        if (firstBtn && firstBtn.dataset.facilityId) {
            currentGymId = parseInt(firstBtn.dataset.facilityId);
        }
    }

    // --- Calendar Header Generation ---
    // Generates the days of the week and dates for the top row of the grid
    function updateCalendarHeaders() {
        const headerContainer = document.getElementById('calendar-grid-header');
        const weekRangeDisplay = document.getElementById('week-range-display');
        if (!headerContainer || !weekRangeDisplay) return;

        const days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        currentDayNames = [];
        let headerHTML = `<div class="sticky-col txt-muted font-xs uppercase tracking-wide">Time</div>`;

        let weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(weekEnd.getDate() + 6);

        // Update the date range display at the top
        const options = { month: 'short', day: 'numeric' };
        weekRangeDisplay.textContent = `${currentWeekStart.toLocaleDateString('en-US', options)} - ${weekEnd.toLocaleDateString('en-US', options)}, ${currentWeekStart.getFullYear()}`;

        for (let i = 0; i < 7; i++) {
            let d = new Date(currentWeekStart);
            d.setDate(d.getDate() + i);
            let dateNum = d.getDate();
            let monthShort = d.toLocaleString('default', { month: 'short' });
            
            const fullDateStr = `${monthShort} ${dateNum}`;
            currentDayNames.push(fullDateStr);

            // Highlight today's date in green
            const isToday = (d.toDateString() === new Date().toDateString()) ? 'txt-green' : 'txt-primary';

            headerHTML += `
                <div class="grid-day-head">
                    <p>${days[i]}</p>
                    <p class="date font-bold ${isToday}">${dateNum}</p>
                </div>`;
        }
        headerContainer.innerHTML = headerHTML;
    }

    // --- Week Navigation Listeners ---
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

    // --- Form & Grid Reset ---
    // Clears the selected slot and disables the booking form until a new slot is chosen
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

    // --- Fetch Schedule Data ---
    // Fetches the weekly schedule from the backend using AJAX
    function fetchAndRenderGrid() {
        const gridBody = document.getElementById('schedule-grid-body');
        if (!gridBody) return;

        gridBody.innerHTML = '<div style="padding: 24px; text-align: center; color: var(--on-surface-variant);">Loading schedule...</div>';
        
        const dateKey = `${currentWeekStart.getFullYear()}-${String(currentWeekStart.getMonth() + 1).padStart(2, '0')}-${String(currentWeekStart.getDate()).padStart(2, '0')}`;

        fetch(`../../backend/captain/booking_action.php?action=get_schedule&facility_id=${currentGymId}&shift=${currentShift}&start_date=${dateKey}`)
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

    // --- Render Grid ---
    // Builds the HTML for the slots based on fetched occupancy data
    function renderGrid(gridData) {
        const gridBody = document.getElementById('schedule-grid-body');
        let html = '';
        
        gridData.forEach(row => {
            const time = row[0]; 
            html += `<div class="calendar-grid-row">`;
            html += `<div class="sticky-col txt-muted font-xs tracking-wide">${time}</div>`;
            
            for (let i = 1; i <= 7; i++) {
                // Parse format (e.g. "pending_45" or "45")
                const cellData = String(row[i]);
                let isPending = cellData.startsWith('pending');
                let occVal = isPending ? parseInt(cellData.split('_')[1] || 0) : parseInt(cellData);
                
                // Calculate remaining spaces
                const availableSpaces = Math.floor(facilityCapacity * (1 - (occVal / 100)));
                
                let styleClass = '';
                let icon = '';
                let isSelectable = false;
                let textDisplay = '';
                
                // Determine styling and selectability based on occupancy/status
                if (isPending) {
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
                
                // Generate precise date string for the database
                let tempD = new Date(currentWeekStart);
                tempD.setDate(tempD.getDate() + (i - 1));
                const exactDbDate = `${tempD.getFullYear()}-${String(tempD.getMonth() + 1).padStart(2, '0')}-${String(tempD.getDate()).padStart(2, '0')}`;

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
        
        // Re-highlight if the user already had a slot selected before reloading the grid
        if (selectedSlot) highlightSelectedSlots();
    }

    // --- Slot Selection Logic ---
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

    // Utility function to calculate the next hour for multi-hour durations
    function addOneHour(timeStr) {
        let [hours, minutes, seconds] = timeStr.split(':').map(Number);
        hours = (hours + 1) % 24;
        return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }

    // Visually highlights the selected slot and any subsequent slots based on duration
    function highlightSelectedSlots() {
        // Remove previous highlights
        document.querySelectorAll('.slot-box').forEach(s => {
            s.classList.remove('slot-selected');
            const iconSpan = s.querySelector('.material-symbols-outlined');
            if (iconSpan) {
                if (s.classList.contains('slot-full')) iconSpan.textContent = 'block';
                else if (s.classList.contains('slot-pending-state')) iconSpan.textContent = 'hourglass_empty';
                else iconSpan.textContent = 'add_circle';
            }
        });

        if (!selectedSlot) return;

        const duration = parseInt(document.getElementById('duration-select').value) || 1;
        let currentTime = selectedSlot.time;
        let minAvailable = selectedSlot.available;

        // Apply highlights for the given duration
        for (let d = 0; d < duration; d++) {
            const targetCell = document.querySelector(`.slot-box[data-dbdate="${selectedSlot.dbdate}"][data-time="${currentTime}"]`);
            if (targetCell) {
                if (targetCell.getAttribute('data-selectable') === 'true') {
                    targetCell.classList.add('slot-selected');
                    const iconSpan = targetCell.querySelector('.material-symbols-outlined');
                    if (iconSpan) iconSpan.textContent = 'check_circle';
                    
                    // Track the bottleneck (lowest available capacity) across the duration
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

    // Update slots if user changes the duration dropdown
    document.getElementById('duration-select')?.addEventListener('change', () => {
        if (selectedSlot) {
            updateFormUI();
        }
    });

    // --- Update Sidebar Form ---
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
            
            // Adjust remaining capacity text color
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

    // --- Capacity & Limit Validation ---
    const teamSelect = document.getElementById('team-select');
    const teamSizeInput = document.getElementById('team-size-input');
    let maxAllowedSize = 0;

    // Detect team changes to set max allowable size
    if (teamSelect) {
        teamSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            maxAllowedSize = parseInt(selectedOption.getAttribute('data-size')) || 0;
            teamSizeInput.value = maxAllowedSize; 
            validateFrontendLimits();
        });
    }

    // Validate if user manually types in the team size input
    teamSizeInput?.addEventListener('input', () => {
        validateFrontendLimits();
    });

    // Main validation logic (Checks limits, enables/disables submit button)
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

        // Block submission if entered size exceeds assigned team count
        if (teamSize > maxAllowedSize) {
            errorMsg.textContent = `Team size cannot exceed your assigned team member count (${maxAllowedSize}).`;
            errorMsg.classList.remove('hidden');
            submitBtn.disabled = true;
            teamSizeInput.style.borderColor = 'var(--error)';
            return;
        }

        if (teamSize > 0) {
            submitBtn.disabled = false;
            // Warn if booking exceeds facility capacity (unless special request is checked)
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

    // --- UI Toggles (Facilities and Shifts) ---
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

    // Initialization calls
    updateCalendarHeaders();
    fetchAndRenderGrid();

    // --- Form Submission & Modals ---
    const limitToggle = document.getElementById('limit-toggle');
    const specialRequestField = document.getElementById('special-request-field');
    const submitBtn = document.getElementById('submit-booking-btn');
    const modal = document.getElementById('confirmation-modal');
    
    // Toggle UI state when "Special Request" checkbox is clicked
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

    // Submit booking request via AJAX
    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            if (submitBtn.disabled) return;
            const errorMsg = document.getElementById('capacity-error-msg');
            
            // Build the data payload
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

            // Set button to loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-[20px] animate-spin">sync</span> Processing...';

            fetch('../../backend/captain/booking_action.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSuccessModal(data.status);
                    
                    // Reset form fields
                    document.getElementById('facility-booking-form').reset();
                    if(limitToggle.checked) limitToggle.click();
                    
                    // Refresh the grid slots dynamically
                    fetchAndResetForm(); 

                    // Dynamically prepend the new booking to the history table without reloading
                    const tbody = document.querySelector('.admin-data-table tbody');
                    if (tbody) {
                        const emptyRow = tbody.querySelector('td[colspan="5"]');
                        if (emptyRow) emptyRow.parentElement.remove();

                        const b = data.new_booking;
                        let badgeStyle = '';
                        if (b.Status === 'Approved') {
                            badgeStyle = 'background: rgba(74,225,118,0.1); border-color: rgba(74,225,118,0.3); color: var(--secondary);';
                        } else {
                            badgeStyle = 'background: rgba(247,190,29,0.1); border-color: rgba(247,190,29,0.3); color: var(--tertiary);';
                        }

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>${b.Facility_Name}</strong></td>
                            <td>${b.Team_Name}</td>
                            <td><span class="mono">${b.Reserve_Date}</span></td>
                            <td><span class="mono">${b.Time_Slot}</span></td>
                            <td><span class="badge-status" style="${badgeStyle}">${b.Status}</span></td>
                        `;
                        tbody.prepend(tr);
                    }

                } else {
                    // Show backend error (e.g. duplicate booking)
                    errorMsg.textContent = data.error;
                    errorMsg.classList.remove('hidden');
                }
            })
            .catch(err => {
                errorMsg.textContent = "Network Error. Could not submit.";
                errorMsg.classList.remove('hidden');
            })
            .finally(() => {
                // Restore button state
                submitBtn.disabled = false;
                const isSpec = limitToggle.checked;
                submitBtn.innerHTML = `<span class="material-symbols-outlined text-[20px]" id="btn-icon">${isSpec ? 'send' : 'check_circle'}</span> <span id="btn-text">${isSpec ? 'Submit Special Request' : 'Confirm Selection'}</span>`;
            });
        });
    }

    // Configure and trigger the appropriate modal UI based on status
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

    // Modal dismissal logic
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
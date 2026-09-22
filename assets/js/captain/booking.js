// assets/js/captain/booking.js
document.addEventListener('DOMContentLoaded', () => {
    
    // Default Facility IDs (Make sure these match your Database Facility_ID)
    let currentGymId = 1; // 1 = Main Gym
    let currentShift = 'morning';
    let selectedSlot = null;
    let facilityCapacity = 50;
    
    let currentDate = new Date();
    let currentWeekStart = new Date(currentDate);
    let dayOfWeek = currentWeekStart.getDay();
    let diffToMonday = currentWeekStart.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
    currentWeekStart.setDate(diffToMonday);
    
    let currentDayNames = []; 

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
        fetchAndRenderGrid();
    });

    document.getElementById('btn-next-week')?.addEventListener('click', () => {
        currentWeekStart.setDate(currentWeekStart.getDate() + 7);
        updateCalendarHeaders();
        selectedSlot = null;
        fetchAndRenderGrid();
    });

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

    function renderGrid(gridData) {
        const gridBody = document.getElementById('schedule-grid-body');
        let html = '';
        
        gridData.forEach(row => {
            const time = row[0];
            html += `<div class="calendar-grid-row">`;
            html += `<div class="sticky-col txt-muted font-xs tracking-wide">${time}</div>`;
            
            for (let i = 1; i <= 7; i++) {
                const occupancy = row[i];
                let isPending = occupancy === 'pending';
                let occVal = isPending ? 0 : parseInt(occupancy);
                const availableSpaces = Math.floor(facilityCapacity * (1 - (occVal / 100)));
                
                let styleClass = '';
                let icon = '';
                let isSelectable = false;
                let textDisplay = '';
                
                if (isPending) {
                    styleClass = 'slot-pending-state';
                    icon = 'hourglass_empty';
                    textDisplay = 'Pending';
                    isSelectable = true;
                } else if (occVal >= 100) {
                    styleClass = 'slot-full';
                    icon = 'block';
                    textDisplay = 'Full';
                    isSelectable = true; 
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
                
                let tempD = new Date(currentWeekStart);
                tempD.setDate(tempD.getDate() + (i - 1));
                const exactDbDate = `${tempD.getFullYear()}-${String(tempD.getMonth() + 1).padStart(2, '0')}-${String(tempD.getDate()).padStart(2, '0')}`;

                let dataAttrs = `data-time="${time}" data-day="${currentDayNames[i-1]}" data-dbdate="${exactDbDate}" data-available="${availableSpaces}" data-occupancy="${occVal}"`;
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
    }

    function attachSlotListeners() {
        document.querySelectorAll('.calendar-grid-row [data-selectable="true"]').forEach(slot => {
            slot.addEventListener('click', function() {
                document.querySelectorAll('.calendar-grid-row [data-selectable="true"]').forEach(s => {
                    if (s.getAttribute('data-selected') === 'true') {
                        s.removeAttribute('data-selected');
                        s.classList.remove('slot-selected');
                        s.querySelector('.material-symbols-outlined').textContent = s.classList.contains('slot-full') ? 'block' : (s.classList.contains('slot-pending-state') ? 'hourglass_empty' : 'add_circle');
                    }
                });

                this.setAttribute('data-selected', 'true');
                this.classList.add('slot-selected');
                this.querySelector('.material-symbols-outlined').textContent = 'check_circle';
                
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

    function updateFormUI() {
        if (selectedSlot) {
            document.getElementById('selected-date-display').textContent = selectedSlot.day + `, ${currentWeekStart.getFullYear()}`;
            
            const timeSelect = document.getElementById('start-time-select');
            timeSelect.innerHTML = `<option value="${selectedSlot.time}">${selectedSlot.time}</option>`;
            timeSelect.disabled = false;
            
            document.getElementById('capacity-info-container').classList.remove('hidden');
            const remainingVal = document.getElementById('remaining-capacity-val');
            remainingVal.textContent = selectedSlot.available;
            
            if (selectedSlot.available > Math.floor(facilityCapacity * 0.3)) {
                remainingVal.className = 'txt-green font-bold text-sm';
            } else if (selectedSlot.available > Math.floor(facilityCapacity * 0.1)) {
                remainingVal.className = 'txt-tertiary font-bold text-sm';
            } else {
                remainingVal.className = 'txt-error font-bold text-sm';
            }
            
            validateFrontendLimits();
        }
    }

    // --- Dynamic Team Size Fill & Validation ---
    const teamSelect = document.getElementById('team-select');
    const teamSizeInput = document.getElementById('team-size-input');

    if (teamSelect) {
        teamSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const size = selectedOption.getAttribute('data-size');
            teamSizeInput.value = size; // Auto fill the real member count from DB
            validateFrontendLimits();
        });
    }

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

        if (teamSize > 0) {
            submitBtn.disabled = false;
            
            if (teamSize > selectedSlot.available && !limitToggle.checked) {
                errorMsg.textContent = `Capacity Exceeded! Tick "Special Request" to proceed.`;
                errorMsg.classList.remove('hidden');
                submitBtn.disabled = true;
                teamSizeInput.style.borderColor = 'var(--error)';
            }
        } else {
            submitBtn.disabled = true;
        }
    }

    function setupToggle(btnId, type, idVal) {
        const btn = document.getElementById(btnId);
        if(btn) {
            btn.addEventListener('click', function() {
                if(type === 'gym') {
                    currentGymId = idVal;
                    document.getElementById('btn-gym-01').classList.remove('active');
                    document.getElementById('btn-gym-02').classList.remove('active');
                } else {
                    currentShift = idVal;
                    document.getElementById('btn-morning').classList.remove('active');
                    document.getElementById('btn-evening').classList.remove('active');
                }
                this.classList.add('active');
                selectedSlot = null;
                fetchAndRenderGrid();
            });
        }
    }
    
    // IMPORTANT: Facility ID should match `facility` table in your DB
    setupToggle('btn-gym-01', 'gym', 1);
    setupToggle('btn-gym-02', 'gym', 2);
    setupToggle('btn-morning', 'shift', 'morning');
    setupToggle('btn-evening', 'shift', 'evening');

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
            formData.append('team_id', teamSelect.value); // Sending Team_ID for the DB
            formData.append('team_size', teamSizeInput.value);
            formData.append('is_special', limitToggle.checked);
            
            if(limitToggle.checked) {
                formData.append('reason', document.getElementById('reason-input').value);
            }

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
                    
                    document.getElementById('facility-booking-form').reset();
                    if(limitToggle.checked) limitToggle.click();
                    document.getElementById('selected-date-display').textContent = 'Select a slot';
                    document.getElementById('capacity-info-container').classList.add('hidden');
                    errorMsg.classList.add('hidden');
                    
                    fetchAndRenderGrid(); 
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
            setTimeout(() => modal.classList.add('hidden'), 300);
        });
    });
});
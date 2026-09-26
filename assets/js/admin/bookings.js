// assets/js/admin/bookings.js
document.addEventListener('DOMContentLoaded', () => {

    // --- CALENDAR STATE ---
    let currentGymId = 1; 
    let currentShift = 'morning';
    let facilityCapacity = 50;
    
    let currentDate = new Date();
    let currentWeekStart = new Date(currentDate);
    let dayOfWeek = currentWeekStart.getDay();
    let diffToMonday = currentWeekStart.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1);
    currentWeekStart.setDate(diffToMonday);
    
    // Dynamically set initial gym ID
    const adminGymContainer = document.getElementById('admin-gym-toggle');
    if (adminGymContainer) {
        const firstBtn = adminGymContainer.querySelector('.toggle-btn');
        if (firstBtn && firstBtn.dataset.id) {
            currentGymId = parseInt(firstBtn.dataset.id);
        }
    }

    // --- INITIALIZATION ---
    updateCalendarHeaders();
    fetchAndRenderGrid();
    loadPendingRequests();

    // --- CALENDAR LOGIC (READ-ONLY) ---
    function updateCalendarHeaders() {
        const headerContainer = document.getElementById('calendar-grid-header');
        const weekRangeDisplay = document.getElementById('week-range-display');
        if (!headerContainer || !weekRangeDisplay) return;

        const days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
        let headerHTML = `<div class="sticky-col txt-muted font-xs uppercase tracking-wide">Time</div>`;

        let weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(weekEnd.getDate() + 6);

        const options = { month: 'short', day: 'numeric' };
        weekRangeDisplay.textContent = `${currentWeekStart.toLocaleDateString('en-US', options)} - ${weekEnd.toLocaleDateString('en-US', options)}, ${currentWeekStart.getFullYear()}`;

        for (let i = 0; i < 7; i++) {
            let d = new Date(currentWeekStart);
            d.setDate(d.getDate() + i);
            let dateNum = d.getDate();
            
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
        fetchAndRenderGrid();
        clearSlotDetails();
    });

    document.getElementById('btn-next-week')?.addEventListener('click', () => {
        currentWeekStart.setDate(currentWeekStart.getDate() + 7);
        updateCalendarHeaders();
        fetchAndRenderGrid();
        clearSlotDetails();
    });

    function fetchAndRenderGrid() {
        const gridBody = document.getElementById('schedule-grid-body');
        if (!gridBody) return;

        gridBody.innerHTML = '<div class="py-8 text-center txt-muted">Loading schedule...</div>';
        const dateKey = `${currentWeekStart.getFullYear()}-${String(currentWeekStart.getMonth() + 1).padStart(2, '0')}-${String(currentWeekStart.getDate()).padStart(2, '0')}`;

        // Updated Controller endpoint path
        fetch(`../../controllers/admin/BookingController.php?action=get_schedule&facility_id=${currentGymId}&shift=${currentShift}&start_date=${dateKey}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    facilityCapacity = data.capacity;
                    renderGrid(data.gridData);
                } else {
                    gridBody.innerHTML = `<div class="py-8 text-center text-error">${data.error}</div>`;
                }
            }).catch(() => gridBody.innerHTML = '<div class="py-8 text-center text-error">Network Error</div>');
    }

    function renderGrid(gridData) {
        const gridBody = document.getElementById('schedule-grid-body');
        let html = '';
        
        gridData.forEach(row => {
            const time = row[0]; 
            html += `<div class="calendar-grid-row">`;
            html += `<div class="sticky-col txt-muted font-xs tracking-wide">${time}</div>`;
            
            for (let i = 1; i <= 7; i++) {
                const cellData = String(row[i]);
                let occVal = parseInt(cellData);
                const availableSpaces = Math.floor(facilityCapacity * (1 - (occVal / 100)));
                
                let styleClass = '';
                let icon = '';
                
                if (occVal >= 100) {
                    styleClass = 'slot-full';
                    icon = 'block';
                } else {
                    if (occVal === 0) { styleClass = 'slot-available'; icon = 'add_circle'; } 
                    else if (occVal <= 90) { styleClass = 'slot-moderate'; icon = 'add_circle'; } 
                    else { styleClass = 'slot-high'; icon = 'add_circle'; }
                }

                let tempD = new Date(currentWeekStart);
                tempD.setDate(tempD.getDate() + (i - 1));
                const exactDbDate = `${tempD.getFullYear()}-${String(tempD.getMonth() + 1).padStart(2, '0')}-${String(tempD.getDate()).padStart(2, '0')}`;
                
                const days = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
                const dayName = days[i-1];

                html += `
                    <div class="slot-cell">
                        <div class="slot-box cursor-pointer ${styleClass}" data-time="${time}:00" data-dbdate="${exactDbDate}" data-day="${dayName}">
                            <span class="material-symbols-outlined">${icon}</span>
                            <span class="txt mono">${occVal}%</span>
                            <span style="font-size: 9px; color: rgba(255,255,255,0.6); margin-top: 2px;">${availableSpaces} Left</span>
                        </div>
                    </div>`;
            }
            html += `</div>`;
        });
        gridBody.innerHTML = html;
        attachSlotClickListeners();
    }

    function attachSlotClickListeners() {
        document.querySelectorAll('.slot-box').forEach(slot => {
            slot.addEventListener('click', function() {
                document.querySelectorAll('.slot-box').forEach(s => s.classList.remove('slot-selected'));
                this.classList.add('slot-selected');
                
                const time = this.getAttribute('data-time');
                const date = this.getAttribute('data-dbdate');
                const dayName = this.getAttribute('data-day');
                
                loadSlotDetails(date, time, dayName);
            });
        });
    }

    function clearSlotDetails() {
        document.getElementById('selected-slot-lbl').textContent = 'Select a slot to view';
        document.getElementById('slot-details-container').innerHTML = '<div class="text-center txt-muted py-8">Please select a time slot from the calendar.</div>';
    }

    // Toggle Listeners
    if(adminGymContainer) {
        adminGymContainer.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                adminGymContainer.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentGymId = parseInt(this.getAttribute('data-id'));
                fetchAndRenderGrid();
                clearSlotDetails();
            });
        });
    }

    document.querySelectorAll('#admin-shift-toggle .toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#admin-shift-toggle .toggle-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentShift = this.getAttribute('data-shift');
            fetchAndRenderGrid();
            clearSlotDetails();
        });
    });


    // --- DATA LOADING (REQUESTS & SLOT DETAILS) ---
    function loadPendingRequests() {
        fetch('../../controllers/admin/BookingController.php?action=get_pending')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('pending-requests-tbody');
                if (!data.success || data.requests.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center txt-muted py-8">No pending requests found.</td></tr>`;
                    return;
                }

                tbody.innerHTML = data.requests.map(req => {
                    const timeStr = `${req.Start_Time.substring(0,5)} - ${req.End_Time.substring(0,5)}`;
                    return `
                        <tr>
                            <td class="font-mono text-primary font-bold">#BK-${req.Booking_ID}</td>
                            <td>${req.Facility_Name}</td>
                            <td>
                                <div class="font-bold flex-items-center">${req.Team_Name} <span class="b-tag b-tag-error">Special Req</span></div>
                                <span class="text-xs txt-muted">Capt: ${req.First_Name} ${req.Last_Name} | Size: ${req.Team_Size}</span>
                            </td>
                            <td style="max-width: 250px; white-space: normal; line-height: 1.4;" class="text-sm txt-muted">
                                ${req.Exception_Reason || 'No reason provided'}
                            </td>
                            <td class="font-mono text-sm">${req.Reserve_Date}<br><span class="txt-muted">${timeStr}</span></td>
                            <td class="text-right action-btn-group">
                                <button class="quick-action-btn quick-approve" onclick="handleApprove(${req.Booking_ID}, '${req.Team_Name}')" title="Approve">
                                    <span class="material-symbols-outlined">check</span>
                                </button>
                                <button class="quick-action-btn quick-decline" onclick="handleDecline(${req.Booking_ID})" title="Decline">
                                    <span class="material-symbols-outlined">close</span>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
            });
    }

    function loadSlotDetails(date, time, dayName) {
        const container = document.getElementById('slot-details-container');
        document.getElementById('selected-slot-lbl').textContent = `${dayName} | ${date} | ${time.substring(0,5)}`;
        container.innerHTML = `<div class="text-center txt-muted py-8">Loading details...</div>`;

        fetch(`../../controllers/admin/BookingController.php?action=get_slot_details&facility_id=${currentGymId}&date=${date}&time=${time}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    container.innerHTML = `<div class="text-center text-error py-8">Error loading details.</div>`;
                    return;
                }
                if (data.bookings.length === 0) {
                    container.innerHTML = `<div class="text-center txt-muted py-8"><span class="material-symbols-outlined text-4xl mb-2" style="opacity: 0.5;">check_circle</span><br>No one has booked this time slot.<br>Capacity is 100% free.</div>`;
                    return;
                }

                container.innerHTML = data.bookings.map(bk => {
                    const timeStr = `${bk.Start_Time.substring(0,5)} - ${bk.End_Time.substring(0,5)}`;
                    const isPending = bk.Status === 'Pending';
                    const statusClass = isPending ? 'status-pending' : 'status-approved';
                    const badge = isPending ? '<span class="text-error text-xs ml-2">(Pending Approval)</span>' : '<span class="txt-green text-xs ml-2">(Approved)</span>';
                    const regNo = bk.Registration_Number || 'N/A';
                    
                    return `
                        <div class="timeline-item ${statusClass}">
                            <div class="tl-time">${timeStr} ${badge}</div>
                            <h4 class="tl-title">${bk.Team_Name} Practice</h4>
                            <div class="tl-meta mt-1">
                                <span class="material-symbols-outlined">person</span> Capt: ${bk.First_Name} (${regNo})
                                <span class="material-symbols-outlined ml-2">group</span> Size: ${bk.Team_Size}
                            </div>
                        </div>
                    `;
                }).join('');
            });
    }


    // --- APPROVE & CONFLICT LOGIC ---
    let activeConflictData = null;

    window.handleApprove = function(bookingId, teamName) {
        const formData = new FormData();
        formData.append('action', 'check_conflict');
        formData.append('booking_id', bookingId);

        fetch('../../controllers/admin/BookingController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (!data.success) { alert(data.error); return; }

                if (data.has_conflict) {
                    activeConflictData = { bookingId: bookingId, reqTeam: teamName, ...data };
                    openConflictModal(activeConflictData);
                } else {
                    if (confirm(`No conflicts detected. Approve request for ${teamName}?`)) {
                        submitApproval(bookingId, [], '');
                    }
                }
            });
    };

    function openConflictModal(data) {
        document.getElementById('conflict-modal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('conflict-modal').classList.remove('opacity-0');
            document.getElementById('conflict-modal-inner').classList.remove('scale-95');
        }, 10);

        document.getElementById('conf-max').textContent = data.max_capacity;
        document.getElementById('conf-booked').textContent = data.current_booked;
        document.getElementById('conf-new').textContent = data.pending_size;
        
        updateConflictCounters();

        const listDiv = document.getElementById('conflict-teams-list');
        listDiv.innerHTML = data.occupants.map(occ => `
            <div class="flex-items-center gap-3 p-3 bg-dim rounded-lg border-dim">
                <input type="checkbox" class="cancel-checkbox custom-checkbox" data-id="${occ.Booking_ID}" data-size="${occ.Team_Size}" onchange="updateConflictCounters()">
                <div>
                    <div class="text-sm font-bold txt-white">${occ.Team_Name}</div>
                    <div class="text-xs txt-muted">Size: ${occ.Team_Size} | Time: ${occ.Start_Time.substring(0,5)}</div>
                </div>
            </div>
        `).join('');

        document.getElementById('cancel-reason').value = '';
    }

    window.updateConflictCounters = function() {
        if (!activeConflictData) return;

        let selectedCancelSize = 0;
        document.querySelectorAll('.cancel-checkbox:checked').forEach(cb => {
            selectedCancelSize += parseInt(cb.dataset.size);
        });

        const newOverflow = activeConflictData.overflow - selectedCancelSize;
        const overflowEl = document.getElementById('conf-overflow');
        const btnConfirm = document.getElementById('btn-confirm-override');

        if (newOverflow > 0) {
            overflowEl.textContent = newOverflow + ' spots remaining to clear';
            overflowEl.className = 'text-error font-bold';
            btnConfirm.disabled = true;
        } else {
            overflowEl.textContent = '0 (Cleared)';
            overflowEl.className = 'txt-green font-bold';
            btnConfirm.disabled = false;
        }
    };

    document.getElementById('btn-confirm-override').addEventListener('click', () => {
        const reason = document.getElementById('cancel-reason').value.trim();
        if (reason === '') { alert("Please provide a cancellation reason."); return; }

        let cancelIds = [];
        document.querySelectorAll('.cancel-checkbox:checked').forEach(cb => {
            cancelIds.push(parseInt(cb.dataset.id));
        });

        submitApproval(activeConflictData.bookingId, cancelIds, reason);

        const bannerContainer = document.getElementById('alert-banner-container');
        const bannerText = document.getElementById('alert-banner-text');
        bannerText.textContent = `Special Request Override: ${activeConflictData.reqTeam} approved. Cancelled ${cancelIds.length} conflicting booking(s).`;
        bannerContainer.classList.remove('hidden');

        closeModal('conflict-modal');
    });

    function submitApproval(bookingId, cancelIds, reason) {
        const formData = new FormData();
        formData.append('action', 'approve_booking');
        formData.append('booking_id', bookingId);
        formData.append('cancel_ids', JSON.stringify(cancelIds));
        formData.append('cancel_reason', reason);

        fetch('../../controllers/admin/BookingController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    refreshAllData();
                } else {
                    alert('Error: ' + data.error);
                }
            });
    }

    // --- DECLINE LOGIC ---
    window.handleDecline = function(bookingId) {
        const reason = prompt("Please enter the reason for rejecting this request:\n(This will be sent to all team members)");
        
        if (reason === null) return; 
        if (reason.trim() === '') {
            alert("A reason is required to reject a request.");
            return;
        }

        const formData = new FormData();
        formData.append('action', 'decline_booking');
        formData.append('booking_id', bookingId);
        formData.append('reject_reason', reason.trim());

        fetch('../../controllers/admin/BookingController.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    refreshAllData();
                } else {
                    alert('Error: ' + data.error);
                }
            });
    };

    function refreshAllData() {
        fetchAndRenderGrid();
        loadPendingRequests();
        clearSlotDetails(); 
    }

    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        modal.classList.add('opacity-0');
        document.getElementById(id + '-inner').classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
        activeConflictData = null;
    };
});
// assets/js/member/calories.js
document.addEventListener('DOMContentLoaded', () => {
    // State variables for tracking the viewed month and the currently selected date
    let currentDate = new Date();
    let selectedDate = new Date();

    // DOM Element References for calendar navigation and display
    const monthDisplay = document.getElementById('month-display');
    const calendarDays = document.getElementById('calendar-days-container');
    const btnPrevMonth = document.getElementById('btnPrevMonth');
    const btnNextMonth = document.getElementById('btnNextMonth');
    const btnResetDate = document.getElementById('btn-reset-date');

    // DOM Element References for summary dashboard
    const summaryDate = document.getElementById('summary-date');
    const summaryIntake = document.getElementById('summary-intake-val');
    const summaryBurned = document.getElementById('summary-burned-val');
    const consumptionTitle = document.querySelector('#consumption-section .cal-table-header h3');
    const activityTitle = document.querySelector('#activity-section .cal-table-header h3');

    // DOM Element References for data tables
    const intakeTableBody = document.getElementById('intake-table-body');
    const intakeTableFoot = document.getElementById('intake-table-foot');
    const burnedTableBody = document.getElementById('burned-table-body');
    const burnedTableFoot = document.getElementById('burned-table-foot');

    // Hidden input fields for tracking selected date in forms
    const intakeHiddenDate = document.getElementById('intake-hidden-date');
    const burnedHiddenDate = document.getElementById('burned-hidden-date');

    // Array mapping month indices to month names
    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    // Helper function to format a Date object into a 'YYYY-MM-DD' string
    function formatDateKey(dateObj) {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    // Function to generate and render the calendar UI for the current month
    function renderCalendar() {
        if (!calendarDays || !monthDisplay) return;

        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        // Update the month and year header
        monthDisplay.innerText = `${monthNames[month]} ${year}`;
        calendarDays.innerHTML = '';

        // Calculate days to determine calendar layout
        const firstDayIndex = new Date(year, month, 1).getDay();
        const adjustedFirstDay = (firstDayIndex === 0) ? 6 : firstDayIndex - 1;
        const totalDays = new Date(year, month + 1, 0).getDate();
        const prevMonthLastDate = new Date(year, month, 0).getDate();

        // Render padding days from the previous month
        for (let i = adjustedFirstDay; i > 0; i--) {
            const padCell = document.createElement('div');
            padCell.className = 'cal-day-box inactive-day';
            padCell.innerText = prevMonthLastDate - i + 1;
            calendarDays.appendChild(padCell);
        }

        // Render the actual days of the active month
        for (let day = 1; day <= totalDays; day++) {
            const thisDate = new Date(year, month, day);
            const dateStr = formatDateKey(thisDate);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cal-day-box';
            btn.dataset.date = dateStr;

            // Highlight the currently selected date
            if (formatDateKey(selectedDate) === dateStr) {
                btn.classList.add('active');
            }

            // Create inner HTML for the day button including indicator dots
            btn.innerHTML = `
                <span class="day-num">${day}</span>
                <div class="day-dots">
                    <span class="dot d-green"></span>
                    <span class="dot d-purple"></span>
                </div>
            `;

            // Event listener for selecting a specific date
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                // Remove active class from all days and apply to the clicked one
                document.querySelectorAll('.cal-day-box').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                selectedDate = thisDate;
                // Fetch new data for the selected date
                loadDateData(dateStr);
            });

            calendarDays.appendChild(btn);
        }
    }

    // Helper function to map food/activity categories to UI icons and color classes
    function getIconDetails(category) {
        const cat = category.toLowerCase();
        if (cat === 'breakfast') return { icon: 'egg', colorClass: 'bg-orange' };
        if (cat === 'lunch' || cat === 'dinner') return { icon: 'restaurant', colorClass: 'bg-red' };
        if (cat === 'snack') return { icon: 'local_drink', colorClass: 'bg-blue' };
        if (cat === 'cardio' || cat === 'running') return { icon: 'directions_run', colorClass: 'bg-yellow' };
        return { icon: 'directions_walk', colorClass: 'bg-green' };
    }

    // Function to fetch and display calorie data (intake and burned) for a specific date
    function loadDateData(dateStr) {
        const dObj = new Date(dateStr + 'T00:00:00');
        const formattedDateText = dObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

        // Update UI headers with the formatted date
        if (summaryDate) summaryDate.innerText = formattedDateText;
        if (consumptionTitle) consumptionTitle.innerText = `${formattedDateText} Consumption`;
        if (activityTitle) activityTitle.innerText = `${formattedDateText} Activity`;

        // Update hidden date fields for form submission
        if (intakeHiddenDate) intakeHiddenDate.value = dateStr;
        if (burnedHiddenDate) burnedHiddenDate.value = dateStr;

        // Fetch data from the controllers via AJAX (Path updated to controllers)
        fetch(`../../controllers/member/calorie_action.php?action=get_date_data&date=${dateStr}`)
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) return;

                const inCals = Math.round(data.totals.intake_cals);
                const outCals = Math.round(data.totals.burned_cals);

                // Update summary dashboard values
                if (summaryIntake) summaryIntake.innerHTML = `${inCals.toLocaleString()} <span class="unit">kcal</span>`;
                if (summaryBurned) summaryBurned.innerHTML = `${outCals.toLocaleString()} <span class="unit">kcal</span>`;

                // Render Intake Table body
                if (intakeTableBody) {
                    if (data.intake.length === 0) {
                        intakeTableBody.innerHTML = `<tr><td colspan="5" class="empty-row">No food items logged.</td></tr>`;
                    } else {
                        intakeTableBody.innerHTML = data.intake.map(item => {
                            const iconData = getIconDetails(item.Category);
                            return `
                            <tr>
                                <td>
                                    <div class="row-flex">
                                        <div class="icon-square ${iconData.colorClass}">
                                            <span class="material-symbols-outlined">${iconData.icon}</span>
                                        </div>
                                        <div class="row-text">
                                            <div class="r-title">${escapeHtml(item.Item_Name)}</div>
                                            <div class="r-sub">${escapeHtml(item.Category)}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="dark-badge">Nutrition</span></td>
                                <td>${Math.round(item.Calories)} kcal</td>
                                <td>
                                    <span class="mac-c">${item.Carbs}g</span> 
                                    <span class="mac-p">${item.Protein}g</span> 
                                    <span class="mac-f">${item.Fat}g</span>
                                </td>
                                <td class="text-right">
                                    <span class="material-symbols-outlined action-trash">delete</span>
                                </td>
                            </tr>
                        `}).join('');
                    }
                }

                // Render Intake Table footer with macro totals
                if (intakeTableFoot) {
                    intakeTableFoot.innerHTML = `
                        <tr>
                            <td colspan="2" class="foot-title">Daily Intake</td>
                            <td class="foot-val">${inCals.toLocaleString()} kcal</td>
                            <td class="foot-macros">
                                <span class="mac-c">${Math.round(data.totals.carbs)}g (C)</span> 
                                <span class="mac-p">${Math.round(data.totals.protein)}g (P)</span> 
                                <span class="mac-f">${Math.round(data.totals.fat)}g (F)</span>
                            </td>
                            <td></td>
                        </tr>
                    `;
                }

                // Render Burned Table body
                if (burnedTableBody) {
                    if (data.burned.length === 0) {
                        burnedTableBody.innerHTML = `<tr><td colspan="4" class="empty-row">No activities recorded.</td></tr>`;
                    } else {
                        burnedTableBody.innerHTML = data.burned.map(item => {
                            const iconData = getIconDetails(item.Category);
                            return `
                            <tr>
                                <td>
                                    <div class="row-flex">
                                        <div class="icon-square ${iconData.colorClass}">
                                            <span class="material-symbols-outlined">${iconData.icon}</span>
                                        </div>
                                        <div class="row-text">
                                            <div class="r-title">${escapeHtml(item.Item_Name)}</div>
                                            <div class="r-sub">${escapeHtml(item.Category)}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>${escapeHtml(item.Portion_Or_Duration)}</td>
                                <td class="txt-yellow">${Math.round(item.Calories)} kcal</td>
                                <td class="text-right">
                                    <span class="material-symbols-outlined action-trash">delete</span>
                                </td>
                            </tr>
                        `}).join('');
                    }
                }

                // Render Burned Table footer
                if (burnedTableFoot) {
                    burnedTableFoot.innerHTML = `
                        <tr>
                            <td colspan="2" class="foot-title txt-yellow">Daily Burned</td>
                            <td class="foot-val txt-yellow" colspan="2">${outCals.toLocaleString()} kcal</td>
                        </tr>
                    `;
                }
            })
            .catch(err => console.error('Error:', err));
    }

    // Helper function to sanitize strings to prevent XSS attacks
    function escapeHtml(str) {
        return (str || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

    // Event listeners for navigating between previous and next months
    if (btnPrevMonth) btnPrevMonth.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
    if (btnNextMonth) btnNextMonth.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });

    // Event listener for the reset button to clear all logged data for the selected date
    if (btnResetDate) {
        btnResetDate.addEventListener('click', () => {
            const dateStr = formatDateKey(selectedDate);
            if (confirm(`Are you sure you want to completely clear all logs for ${dateStr}?`)) {
                const formData = new FormData();
                formData.append('action', 'reset_date');
                formData.append('date', dateStr);

                // Path updated to controllers
                fetch('../../controllers/member/calorie_action.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    // Reload data if reset was successful
                    if (data && data.success) loadDateData(dateStr);
                    else alert('Failed to reset data.');
                })
                .catch(err => console.error('Reset Error:', err));
            }
        });
    }

    // Initial execution calls to render the calendar and load data for today on page load
    renderCalendar();
    loadDateData(formatDateKey(selectedDate));
});
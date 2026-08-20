/**
 * FitCampus - Analytics Dashboard Interactive Controller
 * Path: assets/js/admin/analytics-charts.js
 */

document.addEventListener('DOMContentLoaded', () => {
    const facilityData = {
        'All Facilities': {
            attendance: { val: '450', pct: '12%', sub: 'Gym 01 & Gym 02' },
            peak: '4PM - 6PM',
            students: { val: '1.2K', pct: '4%' },
            gym1Vis: true,
            gym2Vis: true
        },
        'Gym 01': {
            attendance: { val: '280', pct: '8%', sub: 'Gym 01 (Strength)' },
            peak: '5PM - 7PM',
            students: { val: '850', pct: '2%' },
            gym1Vis: true,
            gym2Vis: false
        },
        'Gym 02': {
            attendance: { val: '170', pct: '5%', sub: 'Gym 02 (Cardio)' },
            peak: '6AM - 9AM',
            students: { val: '420', pct: '3%' },
            gym1Vis: false,
            gym2Vis: true
        }
    };

    const dropdownBtn = document.getElementById('facility-dropdown-btn');
    const dropdownMenu = document.getElementById('facility-dropdown-menu');
    const selectedLabel = document.getElementById('selected-facility');
    const facilityOptions = document.querySelectorAll('.facility-option');

    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', () => {
            dropdownMenu.classList.add('hidden');
        });
    }

    facilityOptions.forEach(option => {
        option.addEventListener('click', () => {
            const selectedVal = option.getAttribute('data-value');
            if (selectedLabel) selectedLabel.textContent = selectedVal;
            
            const data = facilityData[selectedVal];
            if (!data) return;

            const attVal = document.getElementById('kpi-attendance-val');
            const attSub = document.getElementById('kpi-attendance-sub');
            const peakVal = document.getElementById('kpi-peak-val');
            const stuVal = document.getElementById('kpi-students-val');
            const stuPct = document.getElementById('kpi-students-pct');

            if (attVal) attVal.textContent = data.attendance.val;
            if (attSub) attSub.textContent = data.attendance.sub;
            if (peakVal) peakVal.textContent = data.peak;
            if (stuVal) stuVal.textContent = data.students.val;
            if (stuPct) stuPct.textContent = data.students.pct;
        });
    });
});
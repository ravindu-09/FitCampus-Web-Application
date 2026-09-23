// assets/js/captain/roster.js
document.addEventListener('DOMContentLoaded', () => {
    const teamSwitcherSelect = document.getElementById('team-switcher-select');
    if (teamSwitcherSelect) {
        teamSwitcherSelect.addEventListener('change', (e) => {
            window.location.href = e.target.value; 
        });
    }
});
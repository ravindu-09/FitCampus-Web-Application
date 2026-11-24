// assets/js/captain/roster.js
document.addEventListener('DOMContentLoaded', () => {
    const teamSwitcherSelect = document.getElementById('team-switcher-select');
    if (teamSwitcherSelect) {
        teamSwitcherSelect.addEventListener('change', event => {
            window.location.href = event.target.value;
        });
    }

    document.querySelectorAll('.roster-remove-form').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm('Are you sure you want to remove this member?')) {
                event.preventDefault();
            }
        });
    });
});
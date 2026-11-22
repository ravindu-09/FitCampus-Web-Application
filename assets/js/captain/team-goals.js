document.addEventListener('DOMContentLoaded', () => {
    const teamFilter = document.getElementById('goal-team-filter');
    teamFilter?.addEventListener('change', () => teamFilter.form?.requestSubmit());

    document.querySelectorAll('.captain-goal-status-form').forEach(form => {
        form.addEventListener('submit', event => {
            const status = form.dataset.confirmStatus;
            if (!window.confirm(`Mark this goal ${status}?`)) {
                event.preventDefault();
            }
        });
    });
});

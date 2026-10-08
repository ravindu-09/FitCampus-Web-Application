// assets/js/member/teams.js
document.addEventListener('DOMContentLoaded', () => {
    const teamModal = document.getElementById('team-selection-modal');
    const openButton = document.getElementById('team-toggle-btn');
    const closeButton = document.getElementById('btn-close-team-modal');

    function closeTeamModal() {
        if (!teamModal) return;
        teamModal.classList.remove('active');
        openButton?.setAttribute('aria-expanded', 'false');
    }

    openButton?.addEventListener('click', () => {
        teamModal?.classList.add('active');
        openButton.setAttribute('aria-expanded', 'true');
    });

    closeButton?.addEventListener('click', closeTeamModal);
    teamModal?.addEventListener('click', (event) => {
        if (event.target === teamModal) closeTeamModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeTeamModal();
    });
});
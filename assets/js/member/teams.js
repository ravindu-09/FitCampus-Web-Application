// assets/js/member/teams.js
function initializeTeamsPage() {
    const teamModal = document.getElementById('team-selection-modal');
    const openButton = document.getElementById('team-toggle-btn');
    const closeButton = document.getElementById('btn-close-team-modal');
    const successToast = document.querySelector('.team-goal-success-toast');

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

    if (successToast) {
        const dismissButton = successToast.querySelector('.team-goal-success-dismiss');
        let autoDismiss;

        function dismissSuccessToast() {
            window.clearTimeout(autoDismiss);
            successToast.remove();
        }

        autoDismiss = window.setTimeout(dismissSuccessToast, 4500);
        dismissButton?.addEventListener('click', dismissSuccessToast);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeTeamsPage, { once: true });
} else {
    initializeTeamsPage();
}

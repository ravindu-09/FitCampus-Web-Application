document.addEventListener('DOMContentLoaded', () => {
    const teamButton = document.getElementById('captainTeamBtn');
    const teamMenu = document.getElementById('captainTeamMenu');

    if (!teamButton || !teamMenu) {
        return;
    }

    const closeMenu = () => {
        teamMenu.classList.remove('show');
        teamButton.setAttribute('aria-expanded', 'false');
    };

    teamButton.addEventListener('click', () => {
        const isOpen = teamMenu.classList.toggle('show');
        teamButton.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', event => {
        if (!teamMenu.contains(event.target) && !teamButton.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && teamMenu.classList.contains('show')) {
            closeMenu();
            teamButton.focus();
        }
    });
});

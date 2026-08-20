/**
 * FitCampus - Universal DOM Utility Handlers
 */

document.addEventListener('DOMContentLoaded', () => {
    // Universal Alert Auto-dismiss (5 seconds)
    const alerts = document.querySelectorAll('.alert-success, .alert-error');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    }
});

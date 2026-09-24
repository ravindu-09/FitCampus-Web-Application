document.addEventListener('DOMContentLoaded', () => {
    // 1. Alerts Auto-dismiss
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

    // 2. Global Sidebar Toggle Logic (Reusable across roles)
    const sidebar = document.getElementById('memberSidebar') || document.querySelector('.admin-sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const closeBtn = document.getElementById('closeSidebarBtn');
    const body = document.body;

    function isDesktop() {
        return window.innerWidth > 1024;
    }

    function toggleSidebar() {
        if (!sidebar) return;
        
        const isOpen = sidebar.classList.contains('active');
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('active');
        body.classList.add('sidebar-open');

        // Mobile screens waladi pamanak backdrop overlay saha scroll lock eka activate we
        if (!isDesktop()) {
            if (overlay) overlay.classList.add('active');
            body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('active');
        body.classList.remove('sidebar-open');

        if (overlay) overlay.classList.remove('active');
        body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleSidebar();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeSidebar();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', (e) => {
            e.stopPropagation();
            closeSidebar();
        });
    }

    // Screen resize weddi mobile scroll-lock resets
    window.addEventListener('resize', () => {
        if (isDesktop()) {
            if (overlay) overlay.classList.remove('active');
            body.style.overflow = '';
        } else if (sidebar && sidebar.classList.contains('active')) {
            if (overlay) overlay.classList.add('active');
            body.style.overflow = 'hidden';
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('memberSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const closeBtn = document.getElementById('closeSidebarBtn');

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
        document.body.classList.add('sidebar-open');

        // Mobile screens waladi pamanak backdrop overlay saha scroll lock eka activate we
        if (!isDesktop()) {
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('active');
        document.body.classList.remove('sidebar-open');

        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = '';
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
            document.body.style.overflow = '';
        } else if (sidebar && sidebar.classList.contains('active')) {
            if (overlay) overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });

    // Rules Carousel Controls
    const rulesCarousel = document.getElementById('rulesCarousel');
    const btnLeft = document.getElementById('rulesScrollLeft');
    const btnRight = document.getElementById('rulesScrollRight');

    if (rulesCarousel && btnLeft && btnRight) {
        btnLeft.addEventListener('click', () => {
            rulesCarousel.scrollBy({ left: -320, behavior: 'smooth' });
        });
        btnRight.addEventListener('click', () => {
            rulesCarousel.scrollBy({ left: 320, behavior: 'smooth' });
        });
    }
});
import './bootstrap';
import 'bootstrap';

function applyTheme(theme) {
    const resolved = theme === 'system'
        ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        : theme;

    document.documentElement.setAttribute('data-bs-theme', resolved);

    const icon = document.querySelector('[data-theme-icon]');
    if (icon) {
        icon.className = resolved === 'dark' ? 'bi bi-moon-stars' : 'bi bi-sun';
    }
}

function initAdminUI() {
    const layout = document.getElementById('adminLayout');
    if (!layout) return;

    const sidebarToggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    const themeToggle = document.getElementById('themeToggle');

    const syncSidebarState = () => {
        const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
        
        if (isMobile) {
            layout.classList.remove('sidebar-collapsed');
        } else {
            const sidebarSavedStatus = localStorage.getItem('lentera_sidebar_collapsed');
            if (sidebarSavedStatus === 'true') {
                layout.classList.add('sidebar-collapsed');
            } else {
                layout.classList.remove('sidebar-collapsed');
            }
            layout.classList.remove('sidebar-mobile-open');
        }
    };
    syncSidebarState();

    window.matchMedia('(max-width: 767.98px)').addEventListener('change', syncSidebarState);

    const savedTheme = localStorage.getItem('lentera_theme') || 'light';
    applyTheme(savedTheme);

    const handleSidebar = () => {
        const isNowMobile = window.matchMedia('(max-width: 767.98px)').matches;
        
        if (isNowMobile) {
            layout.classList.toggle('sidebar-mobile-open');
        } else {
            layout.classList.toggle('sidebar-collapsed');
            const isCollapsed = layout.classList.contains('sidebar-collapsed');
            localStorage.setItem('lentera_sidebar_collapsed', isCollapsed);
        }
    };

    const handleTheme = () => {
        const current = document.documentElement.getAttribute('data-bs-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem('lentera_theme', next);
        applyTheme(next);
    };

    sidebarToggle?.removeEventListener('click', handleSidebar);
    sidebarToggle?.addEventListener('click', handleSidebar);

    themeToggle?.removeEventListener('click', handleTheme);
    themeToggle?.addEventListener('click', handleTheme);

    backdrop?.removeEventListener('click', () => layout.classList.remove('sidebar-mobile-open')); 
    backdrop?.addEventListener('click', () => layout.classList.remove('sidebar-mobile-open'));
}

document.addEventListener('livewire:navigated', initAdminUI);
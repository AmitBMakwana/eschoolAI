/**
 * AI SchoolOS — Core UI Interaction Toolkit
 */

const SchoolOSUI = {
    // Toast Notification System
    toast(message, type = 'info', duration = 3500) {
        let container = document.getElementById('schoolos-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'schoolos-toast-container';
            container.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 8px;
            `;
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const bgColors = {
            success: '#10B981',
            danger: '#EF4444',
            warning: '#F59E0B',
            info: '#4F46E5',
        };

        toast.style.cssText = `
            background: ${bgColors[type] || bgColors.info};
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            font-size: 0.875rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: toastIn 200ms ease forwards;
        `;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 200ms';
            setTimeout(() => toast.remove(), 200);
        }, duration);
    },

    // Modal Manager
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
        }
    },

    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    },

    // Tab Switcher
    initTabs() {
        document.querySelectorAll('.tabs-nav').forEach(nav => {
            const buttons = nav.querySelectorAll('.tab-btn');
            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-target');
                    buttons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const container = nav.parentElement;
                    container.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.style.display = pane.id === targetId ? 'block' : 'none';
                    });
                });
            });
        });
    },

    // Sidebar Toggle for Mobile
    toggleSidebar() {
        const sidebar = document.querySelector('.app-sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    SchoolOSUI.initTabs();
});

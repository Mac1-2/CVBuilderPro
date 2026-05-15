const App = {
    baseUrl: window.location.origin,

    init() {
        this.setupDropdowns();
        this.setupModals();
        this.setupFlashDismiss();
    },

    setupDropdowns() {
        document.querySelectorAll('.user-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const menu = btn.nextElementSibling;
                menu?.classList.toggle('show');
            });
        });
        document.addEventListener('click', () => {
            document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
        });
    },

    setupModals() {
        document.querySelectorAll('[data-modal]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const modalId = trigger.dataset.modal;
                const modal = document.getElementById(modalId);
                if (modal) modal.classList.add('show');
            });
        });
        document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
            el.addEventListener('click', (e) => {
                if (e.target === el || el.classList.contains('modal-close')) {
                    el.closest('.modal-overlay')?.classList.remove('show');
                }
            });
        });
    },

    setupFlashDismiss() {
        setTimeout(() => {
            document.querySelectorAll('.flash').forEach(f => f.remove());
        }, 5000);
    },

    async fetch(url, options = {}) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        };
        const response = await fetch(url, { ...defaults, ...options });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    },

    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `flash flash-${type}`;
        toast.textContent = message;
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
};

document.addEventListener('DOMContentLoaded', () => App.init());

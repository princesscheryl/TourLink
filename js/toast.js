/**
 * Professional Toast Notification System
 * Replaces SweetAlert with elegant, non-intrusive notifications
 */

const Toast = {
    container: null,

    init() {
        if (this.container) return;

        // Create toast container
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        this.container.innerHTML = `
            <style>
                #toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 99999;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    pointer-events: none;
                }

                .toast-notification {
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    padding: 16px 20px;
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                    min-width: 320px;
                    max-width: 420px;
                    pointer-events: auto;
                    transform: translateX(120%);
                    opacity: 0;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    border-left: 4px solid #2d6a4f;
                }

                .toast-notification.show {
                    transform: translateX(0);
                    opacity: 1;
                }

                .toast-notification.hiding {
                    transform: translateX(120%);
                    opacity: 0;
                }

                .toast-notification.success {
                    border-left-color: #10b981;
                }

                .toast-notification.error {
                    border-left-color: #ef4444;
                }

                .toast-notification.warning {
                    border-left-color: #f59e0b;
                }

                .toast-notification.info {
                    border-left-color: #3b82f6;
                }

                .toast-icon {
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    font-size: 12px;
                }

                .toast-notification.success .toast-icon {
                    background: #ecfdf5;
                    color: #10b981;
                }

                .toast-notification.error .toast-icon {
                    background: #fef2f2;
                    color: #ef4444;
                }

                .toast-notification.warning .toast-icon {
                    background: #fffbeb;
                    color: #f59e0b;
                }

                .toast-notification.info .toast-icon {
                    background: #eff6ff;
                    color: #3b82f6;
                }

                .toast-content {
                    flex: 1;
                }

                .toast-title {
                    font-weight: 600;
                    font-size: 0.95rem;
                    color: #1f2937;
                    margin: 0 0 2px 0;
                }

                .toast-message {
                    font-size: 0.875rem;
                    color: #6b7280;
                    margin: 0;
                    line-height: 1.4;
                }

                .toast-close {
                    background: none;
                    border: none;
                    color: #9ca3af;
                    cursor: pointer;
                    padding: 0;
                    font-size: 18px;
                    line-height: 1;
                    transition: color 0.2s;
                    flex-shrink: 0;
                }

                .toast-close:hover {
                    color: #4b5563;
                }

                .toast-progress {
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    height: 3px;
                    background: currentColor;
                    opacity: 0.3;
                    border-radius: 0 0 0 12px;
                }

                .toast-notification.success .toast-progress { background: #10b981; }
                .toast-notification.error .toast-progress { background: #ef4444; }
                .toast-notification.warning .toast-progress { background: #f59e0b; }
                .toast-notification.info .toast-progress { background: #3b82f6; }

                /* Dark mode support */
                [data-theme="dark"] .toast-notification {
                    background: #374151;
                }

                [data-theme="dark"] .toast-title {
                    color: #f3f4f6;
                }

                [data-theme="dark"] .toast-message {
                    color: #d1d5db;
                }

                [data-theme="dark"] .toast-close {
                    color: #9ca3af;
                }

                @media (max-width: 480px) {
                    #toast-container {
                        left: 10px;
                        right: 10px;
                        top: 10px;
                    }

                    .toast-notification {
                        min-width: auto;
                        max-width: none;
                    }
                }
            </style>
        `;
        document.body.appendChild(this.container);
    },

    show(options) {
        this.init();

        const {
            type = 'info',
            title = '',
            message = '',
            duration = 4000,
            showProgress = true
        } = typeof options === 'string' ? { message: options } : options;

        const icons = {
            success: '<i class="fas fa-check"></i>',
            error: '<i class="fas fa-times"></i>',
            warning: '<i class="fas fa-exclamation"></i>',
            info: '<i class="fas fa-info"></i>'
        };

        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-content">
                ${title ? `<p class="toast-title">${title}</p>` : ''}
                <p class="toast-message">${message}</p>
            </div>
            <button class="toast-close">&times;</button>
            ${showProgress && duration > 0 ? '<div class="toast-progress"></div>' : ''}
        `;

        this.container.appendChild(toast);

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.hide(toast);
        });

        // Show animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Progress bar animation
        if (showProgress && duration > 0) {
            const progress = toast.querySelector('.toast-progress');
            if (progress) {
                progress.style.width = '100%';
                progress.style.transition = `width ${duration}ms linear`;
                requestAnimationFrame(() => {
                    progress.style.width = '0%';
                });
            }
        }

        // Auto hide
        if (duration > 0) {
            setTimeout(() => this.hide(toast), duration);
        }

        return toast;
    },

    hide(toast) {
        if (!toast || toast.classList.contains('hiding')) return;

        toast.classList.add('hiding');
        toast.classList.remove('show');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    },

    success(message, title = 'Success') {
        return this.show({ type: 'success', title, message });
    },

    error(message, title = 'Error') {
        return this.show({ type: 'error', title, message });
    },

    warning(message, title = 'Warning') {
        return this.show({ type: 'warning', title, message });
    },

    info(message, title = 'Info') {
        return this.show({ type: 'info', title, message });
    }
};

// Make it globally available
window.Toast = Toast;

// Also create a Swal-compatible wrapper for easy migration
window.showToast = Toast.show.bind(Toast);

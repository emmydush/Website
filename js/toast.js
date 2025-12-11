/**
 * Modern Toast Notification System
 * Replaces browser alerts with custom styled notifications
 */

class Toast {
    constructor(options = {}) {
        this.container = this.getOrCreateContainer();
        this.duration = options.duration || 4000;
        this.position = options.position || 'top-right';
    }

    getOrCreateContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    show(message, type = 'info', options = {}) {
        const {
            title = this.getTitle(type),
            duration = this.duration,
            icon = this.getIcon(type)
        } = options;

        const toastEl = document.createElement('div');
        toastEl.className = `toast ${type}`;
        toastEl.setAttribute('role', 'status');
        toastEl.setAttribute('aria-live', 'polite');
        toastEl.setAttribute('aria-atomic', 'true');

        const closeBtn = document.createElement('button');
        closeBtn.className = 'toast-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Close notification');
        closeBtn.type = 'button';

        const iconEl = document.createElement('span');
        iconEl.className = 'toast-icon';
        iconEl.innerHTML = icon;

        const content = document.createElement('div');
        content.className = 'toast-content';

        if (title) {
            const titleEl = document.createElement('div');
            titleEl.className = 'toast-title';
            titleEl.textContent = title;
            content.appendChild(titleEl);
        }

        const messageEl = document.createElement('div');
        messageEl.className = 'toast-message';
        messageEl.textContent = message;
        content.appendChild(messageEl);

        const progress = document.createElement('div');
        progress.className = 'toast-progress';

        toastEl.appendChild(iconEl);
        toastEl.appendChild(content);
        toastEl.appendChild(closeBtn);
        toastEl.appendChild(progress);

        this.container.appendChild(toastEl);

        // Close button handler
        closeBtn.addEventListener('click', () => {
            this.remove(toastEl);
        });

        // Auto-remove after duration
        let timeout;
        const startTimer = () => {
            timeout = setTimeout(() => {
                this.remove(toastEl);
            }, duration);
        };

        startTimer();

        // Pause timer on hover
        toastEl.addEventListener('mouseenter', () => {
            clearTimeout(timeout);
        });

        toastEl.addEventListener('mouseleave', () => {
            startTimer();
        });

        return toastEl;
    }

    remove(toastEl) {
        toastEl.classList.add('removing');
        setTimeout(() => {
            if (toastEl.parentNode) {
                toastEl.parentNode.removeChild(toastEl);
            }
        }, 300);
    }

    getTitle(type) {
        const titles = {
            success: '✓ Success',
            error: '✗ Error',
            info: 'ℹ Info',
            warning: '⚠ Warning'
        };
        return titles[type] || titles.info;
    }

    getIcon(type) {
        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-times-circle"></i>',
            info: '<i class="fas fa-info-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>'
        };
        return icons[type] || icons.info;
    }

    success(message, options = {}) {
        return this.show(message, 'success', {
            ...options,
            title: options.title || 'Success'
        });
    }

    error(message, options = {}) {
        return this.show(message, 'error', {
            ...options,
            title: options.title || 'Error',
            duration: options.duration || 5000
        });
    }

    info(message, options = {}) {
        return this.show(message, 'info', {
            ...options,
            title: options.title || 'Info'
        });
    }

    warning(message, options = {}) {
        return this.show(message, 'warning', {
            ...options,
            title: options.title || 'Warning'
        });
    }

    clear() {
        const toasts = this.container.querySelectorAll('.toast');
        toasts.forEach(toast => this.remove(toast));
    }
}

// Global instance
const toast = new Toast();

/**
 * Helper function to show success message
 * @param {string} message - Success message to display
 * @param {object} options - Additional options
 */
function showSuccess(message, options = {}) {
    return toast.success(message, options);
}

/**
 * Helper function to show error message
 * @param {string} message - Error message to display
 * @param {object} options - Additional options
 */
function showError(message, options = {}) {
    return toast.error(message, options);
}

/**
 * Helper function to show info message
 * @param {string} message - Info message to display
 * @param {object} options - Additional options
 */
function showInfo(message, options = {}) {
    return toast.info(message, options);
}

/**
 * Helper function to show warning message
 * @param {string} message - Warning message to display
 * @param {object} options - Additional options
 */
function showWarning(message, options = {}) {
    return toast.warning(message, options);
}

/**
 * Confirmation dialog (modern replacement for window.confirm)
 * @param {string} message - Confirmation message
 * @param {function} onConfirm - Callback if user confirms
 * @param {function} onCancel - Callback if user cancels
 */
function showConfirm(message, onConfirm, onCancel) {
    const confirmed = window.confirm(message);
    if (confirmed && onConfirm) {
        onConfirm();
    } else if (!confirmed && onCancel) {
        onCancel();
    }
    return confirmed;
}

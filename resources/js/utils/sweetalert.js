/**
 * SweetAlert2 Utilities for Noteds Application
 * Provides reusable functions for various alert/dialog patterns
 */

import Swal from 'sweetalert2';

/**
 * Initialize SweetAlert utilities and make them globally available
 */
export function initializeSweetAlertUtils() {
    // Toast mixin for notifications
    const toastMixin = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3200,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    /**
     * Show toast notification
     * @param {string} icon - Icon type (success, error, warning, info)
     * @param {string} title - Message title
     * @param {object} options - Additional SweetAlert options
     */
    window.showToast = function (icon, title, options = {}) {
        if (!title) return;

        // Skip notifications on back/forward navigation
        const navigation = (performance.getEntriesByType && performance.getEntriesByType('navigation')[0]) || null;
        const isBackForward = navigation
            ? navigation.type === 'back_forward'
            : (performance.navigation && performance.navigation.type === 2);

        if (isBackForward && options.skipBackForward !== false) {
            return;
        }

        const { skipBackForward, ...restOptions } = options || {};

        const fireOptions = {
            icon: icon || 'info',
            title,
            ...restOptions
        };

        toastMixin.fire(fireOptions);
    };

    // Alias for backward compatibility
    window.NotedsToast = window.showToast;

    /**
     * Show success toast
     * @param {string} message - Success message
     * @param {object} options - Additional options
     */
    window.showSuccess = function (message, options = {}) {
        showToast('success', message, options);
    };

    /**
     * Show error toast
     * @param {string} message - Error message
     * @param {object} options - Additional options
     */
    window.showError = function (message, options = {}) {
        showToast('error', message, options);
    };

    /**
     * Show warning toast
     * @param {string} message - Warning message
     * @param {object} options - Additional options
     */
    window.showWarning = function (message, options = {}) {
        showToast('warning', message, options);
    };

    /**
     * Show info toast
     * @param {string} message - Info message
     * @param {object} options - Additional options
     */
    window.showInfo = function (message, options = {}) {
        showToast('info', message, options);
    };

    /**
     * Intercept Swal.fire() calls to auto-apply toast mixin for simple notifications
     * This ensures all simple toast-like alerts use the correct top-end positioning
     */
    const originalFire = Swal.fire.bind(Swal);
    Swal.fire = function (config) {
        // Check if this looks like a simple toast notification
        // (has title/icon but no confirm button, html, or input)
        if (typeof config === 'object' && config !== null) {
            const hasDialogFeatures = config.showConfirmButton || config.html || config.input || config.showCancelButton;
            const isAlreadyToast = config.toast === true;

            // If looks like a simple notification without dialog features and not already a toast
            if (!hasDialogFeatures && !isAlreadyToast && (config.title || config.text)) {
                return toastMixin.fire(config);
            }
        }
        return originalFire(config);
    };

    /**
     * Show confirmation dialog
     * @param {object} config - Configuration object
     * @returns {Promise} Result of user action
     */
    window.showConfirm = function (config = {}) {
        const defaultConfig = {
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
            reverseButtons: false,
            allowOutsideClick: false,
            allowEscapeKey: true,
            ...config
        };

        return Swal.fire(defaultConfig);
    };

    /**
     * Show delete confirmation dialog
     * @param {object} config - Configuration object
     * @returns {Promise} Result of user action
     */
    window.showDeleteConfirm = function (config = {}) {
        const defaultConfig = {
            title: 'Delete item?',
            html: 'This action cannot be undone.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            reverseButtons: false,
            allowOutsideClick: false,
            allowEscapeKey: true,
            ...config
        };

        return Swal.fire(defaultConfig);
    };

    /**
     * Show loading dialog
     * @param {string} message - Loading message
     * @param {object} options - Additional options
     */
    window.showLoading = function (message = 'Please wait...', options = {}) {
        return Swal.fire({
            title: message,
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
            ...options
        });
    };

    /**
     * Hide loading dialog
     */
    window.hideLoading = function () {
        Swal.hideLoading();
    };

    /**
     * Close SweetAlert dialog
     */
    window.closeAlert = function () {
        Swal.close();
    };

    /**
     * Show custom alert with multiple options
     * @param {object} config - Complete SweetAlert configuration
     * @returns {Promise} Result of user action
     */
    window.showAlert = function (config = {}) {
        const defaultConfig = {
            allowOutsideClick: false,
            allowEscapeKey: true,
            ...config
        };

        return Swal.fire(defaultConfig);
    };
}

/**
 * Initialize event listeners for data-attribute based alerts
 * Usage: <button data-confirm-delete data-delete-url="/path/to/delete">Delete</button>
 */
export function initializeDataAttributeListeners() {
    // Delete confirmation
    document.addEventListener('click', function (e) {
        const deleteBtn = e.target.closest('[data-confirm-delete]');
        if (!deleteBtn) return;

        e.preventDefault();

        const deleteUrl = deleteBtn.getAttribute('data-delete-url');
        const deleteMessage = deleteBtn.getAttribute('data-delete-message') || 'This action cannot be undone.';
        const itemName = deleteBtn.getAttribute('data-item-name') || 'item';

        showDeleteConfirm({
            title: `Delete ${itemName}?`,
            html: deleteMessage,
            confirmButtonText: `Yes, delete ${itemName}`
        }).then((result) => {
            if (result.isConfirmed) {
                // Create and submit a hidden form for DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                form.innerHTML = `
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Generic form submission confirmation
    document.addEventListener('click', function (e) {
        const submitBtn = e.target.closest('[data-confirm-submit]');
        if (!submitBtn) return;

        e.preventDefault();

        const form = submitBtn.closest('form');
        const message = submitBtn.getAttribute('data-confirm-message') || 'Are you sure?';

        showConfirm({
            title: message,
            confirmButtonText: 'Yes, submit'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
}

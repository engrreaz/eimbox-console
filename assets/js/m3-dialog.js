/**
 * EIMBox Material Design 3 (M3) Dialog & Notification Engine
 * Replaces SweetAlert2 with Android Material Design 3 Specification
 * Provides: M3 Snackbar/Toast, M3 Material Dialog, M3 Bottom Sheet & SweetAlert2 Polyfill Bridge
 */

(function (window, document) {
  'use strict';

  // =========================================================
  // 1. Icon Mapping (Bootstrap Icons strictly)
  // =========================================================
  const ICON_MAP = {
    success: 'bi-check-circle-fill',
    error: 'bi-x-circle-fill',
    danger: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill',
    question: 'bi-question-circle-fill',
    default: 'bi-bell-fill'
  };

  const CLASS_MAP = {
    success: 'm3-icon-success',
    error: 'm3-icon-error',
    danger: 'm3-icon-danger',
    warning: 'm3-icon-warning',
    info: 'm3-icon-info',
    question: 'm3-icon-question',
    default: 'm3-icon-info'
  };

  function sanitizeType(type) {
    if (!type) return 'info';
    type = String(type).toLowerCase();
    if (type === 'danger') return 'error';
    return ICON_MAP[type] ? type : 'info';
  }

  // =========================================================
  // 2. M3 Snackbar / Toast Engine
  // =========================================================
  let snackbarContainer = null;

  function ensureSnackbarContainer() {
    if (!snackbarContainer || !document.body.contains(snackbarContainer)) {
      snackbarContainer = document.getElementById('m3SnackbarContainer');
      if (!snackbarContainer) {
        snackbarContainer = document.createElement('div');
        snackbarContainer.id = 'm3SnackbarContainer';
        snackbarContainer.className = 'm3-snackbar-container';
        document.body.appendChild(snackbarContainer);
      }
    }
    return snackbarContainer;
  }

  /**
   * Display modern M3 bottom floating snackbar
   * @param {string} message Message text
   * @param {string} type 'success' | 'error' | 'warning' | 'info'
   * @param {number|object} options Duration (ms) or config object
   */
  function showM3Snackbar(message, type = 'info', options = {}) {
    if (typeof options === 'number') {
      options = { duration: options };
    }

    const duration = options.duration !== undefined ? options.duration : 3400;
    const actionText = options.actionText || 'DISMISS';
    const onAction = typeof options.onAction === 'function' ? options.onAction : null;

    const normType = sanitizeType(type);
    const iconClass = ICON_MAP[normType] || ICON_MAP.info;

    const container = ensureSnackbarContainer();

    const snackbar = document.createElement('div');
    snackbar.className = `m3-snackbar m3-snackbar-${normType}`;

    snackbar.innerHTML = `
      <div class="m3-sb-content">
        <i class="bi ${iconClass} m3-sb-icon"></i>
        <div class="m3-sb-text">${message}</div>
      </div>
      <button type="button" class="m3-sb-action-btn">${actionText}</button>
    `;

    const btn = snackbar.querySelector('.m3-sb-action-btn');

    let dismissed = false;
    const dismiss = () => {
      if (dismissed) return;
      dismissed = true;
      snackbar.classList.remove('m3-snackbar-show');
      setTimeout(() => {
        if (snackbar.parentNode) snackbar.parentNode.removeChild(snackbar);
      }, 300);
    };

    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (onAction) onAction();
      dismiss();
    });

    container.appendChild(snackbar);

    // Trigger reflow for transition
    requestAnimationFrame(() => {
      snackbar.classList.add('m3-snackbar-show');
    });

    if (duration > 0) {
      setTimeout(dismiss, duration);
    }

    return { dismiss };
  }

  // =========================================================
  // 3. M3 Material Dialog Engine (Modal & Confirmations)
  // =========================================================
  let dialogBackdrop = null;
  let activeDialogResolver = null;

  function ensureDialogBackdrop() {
    if (!dialogBackdrop || !document.body.contains(dialogBackdrop)) {
      dialogBackdrop = document.getElementById('m3DialogBackdrop');
      if (!dialogBackdrop) {
        dialogBackdrop = document.createElement('div');
        dialogBackdrop.id = 'm3DialogBackdrop';
        dialogBackdrop.className = 'm3-dialog-backdrop';
        document.body.appendChild(dialogBackdrop);
      }
    }
    return dialogBackdrop;
  }

  /**
   * Display M3 Material Dialog (Replaces SweetAlert modal)
   * Returns a Promise resolving { isConfirmed: boolean, isDismissed: boolean, value: any }
   */
  function showM3Dialog(options = {}) {
    return new Promise((resolve) => {
      // Normalize arguments
      if (typeof options === 'string') {
        const title = arguments[0];
        const text = arguments[1] || '';
        const icon = arguments[2] || 'info';
        options = { title, text, icon };
      }

      const title = options.title || '';
      const text = options.text || options.message || options.html || '';
      const icon = sanitizeType(options.icon || options.type || 'info');
      const showCancelButton = !!options.showCancelButton;
      const confirmButtonText = options.confirmButtonText || 'OK';
      const cancelButtonText = options.cancelButtonText || 'Cancel';
      const isDestructive = !!(options.isDestructive || options.confirmButtonColor === '#d33' || options.confirmButtonColor === 'red' || options.icon === 'error');
      const allowOutsideClick = options.allowOutsideClick !== undefined ? !!options.allowOutsideClick : true;

      const backdrop = ensureDialogBackdrop();

      const iconClass = ICON_MAP[icon] || ICON_MAP.info;
      const iconColorClass = CLASS_MAP[icon] || CLASS_MAP.info;

      backdrop.innerHTML = `
        <div class="m3-dialog-card" role="dialog" aria-modal="true">
          <div class="m3-dialog-header">
            <div class="m3-dialog-icon-circle ${iconColorClass}">
              <i class="bi ${iconClass}"></i>
            </div>
            ${title ? `<h3 class="m3-dialog-title">${title}</h3>` : ''}
          </div>
          <div class="m3-dialog-body">${text}</div>
          <div class="m3-dialog-actions">
            ${showCancelButton ? `<button type="button" class="m3-btn-tonal" id="m3DialogCancelBtn">${cancelButtonText}</button>` : ''}
            <button type="button" class="m3-btn-filled ${isDestructive ? 'm3-btn-destructive' : ''}" id="m3DialogConfirmBtn">${confirmButtonText}</button>
          </div>
        </div>
      `;

      activeDialogResolver = resolve;

      const closeDialog = (result) => {
        backdrop.classList.remove('m3-show');
        document.removeEventListener('keydown', handleKeydown);
        setTimeout(() => {
          backdrop.innerHTML = '';
          if (activeDialogResolver) {
            activeDialogResolver(result);
            activeDialogResolver = null;
          }
        }, 260);
      };

      const handleKeydown = (e) => {
        if (e.key === 'Escape' && allowOutsideClick) {
          closeDialog({ isConfirmed: false, isDismissed: true, dismiss: 'escape' });
        }
      };

      document.addEventListener('keydown', handleKeydown);

      const confirmBtn = document.getElementById('m3DialogConfirmBtn');
      const cancelBtn = document.getElementById('m3DialogCancelBtn');

      if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
          if (typeof options.preConfirm === 'function') {
            const val = options.preConfirm();
            closeDialog({ isConfirmed: true, isDismissed: false, value: val });
          } else {
            closeDialog({ isConfirmed: true, isDismissed: false });
          }
        });
      }

      if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
          closeDialog({ isConfirmed: false, isDismissed: true, dismiss: 'cancel' });
        });
      }

      backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop && allowOutsideClick) {
          closeDialog({ isConfirmed: false, isDismissed: true, dismiss: 'backdrop' });
        }
      });

      requestAnimationFrame(() => {
        backdrop.classList.add('m3-show');
        if (confirmBtn) confirmBtn.focus();
      });
    });
  }

  // =========================================================
  // 4. M3 Bottom Sheet Engine
  // =========================================================
  let bottomSheetBackdrop = null;

  function showM3BottomSheet(options = {}) {
    return new Promise((resolve) => {
      const title = options.title || '';
      const content = options.content || options.html || '';

      if (!bottomSheetBackdrop || !document.body.contains(bottomSheetBackdrop)) {
        bottomSheetBackdrop = document.createElement('div');
        bottomSheetBackdrop.className = 'm3-bottom-sheet-backdrop';
        document.body.appendChild(bottomSheetBackdrop);
      }

      bottomSheetBackdrop.innerHTML = `
        <div class="m3-bottom-sheet">
          <div class="m3-sheet-handle"></div>
          ${title ? `<div class="m3-dialog-title" style="margin-bottom:14px;">${title}</div>` : ''}
          <div class="m3-sheet-body">${content}</div>
        </div>
      `;

      const closeSheet = () => {
        bottomSheetBackdrop.classList.remove('m3-show');
        setTimeout(() => {
          if (bottomSheetBackdrop.parentNode) bottomSheetBackdrop.parentNode.removeChild(bottomSheetBackdrop);
          resolve();
        }, 280);
      };

      bottomSheetBackdrop.addEventListener('click', (e) => {
        if (e.target === bottomSheetBackdrop) closeSheet();
      });

      requestAnimationFrame(() => {
        bottomSheetBackdrop.classList.add('m3-show');
      });
    });
  }

  // =========================================================
  // 5. SweetAlert2 (Swal) Drop-in Polyfill Bridge
  // Intercepts existing Swal.fire() calls seamlessly
  // =========================================================
  const M3SwalPolyfill = {
    fire: function (...args) {
      if (args.length === 0) return Promise.resolve({ isDismissed: true });

      // Case: Toast option in SweetAlert
      if (typeof args[0] === 'object' && args[0].toast) {
        const opts = args[0];
        const msg = (opts.title ? `<strong>${opts.title}</strong> ` : '') + (opts.text || '');
        showM3Snackbar(msg, opts.icon || 'info', { duration: opts.timer || 3000 });
        return Promise.resolve({ isConfirmed: true });
      }

      // Case: Swal.fire('Title', 'Text', 'success')
      if (typeof args[0] === 'string') {
        const title = args[0];
        const text = args[1] || '';
        const icon = args[2] || 'info';
        return showM3Dialog({ title, text, icon });
      }

      // Case: Swal.fire({ ... })
      return showM3Dialog(args[0]);
    },

    close: function () {
      if (dialogBackdrop && dialogBackdrop.classList.contains('m3-show')) {
        dialogBackdrop.classList.remove('m3-show');
      }
    },

    showLoading: function () {
      showM3Snackbar('Processing request, please wait...', 'info', { duration: 0 });
    },

    hideLoading: function () {
      const container = document.getElementById('m3SnackbarContainer');
      if (container) container.innerHTML = '';
    },

    mixin: function (defaultConfig = {}) {
      return {
        fire: function (...callArgs) {
          if (typeof callArgs[0] === 'object') {
            return M3SwalPolyfill.fire(Object.assign({}, defaultConfig, callArgs[0]));
          }
          return M3SwalPolyfill.fire(...callArgs);
        }
      };
    }
  };

  // Replace or mount window.Swal safely
  window.Swal = M3SwalPolyfill;
  window.swal = M3SwalPolyfill;

  // =========================================================
  // 6. Global Exposing
  // =========================================================
  window.showM3Snackbar = showM3Snackbar;
  window.showSnackbar = showM3Snackbar; // Direct alias for existing Play/Materio code
  window.showToast = function (type, message, title = '') {
    const fullMsg = title ? `${title}: ${message}` : message;
    showM3Snackbar(fullMsg, type);
  };
  window.showM3Dialog = showM3Dialog;
  window.showM3BottomSheet = showM3BottomSheet;

  window.EIMBoxUI = {
    snackbar: showM3Snackbar,
    dialog: showM3Dialog,
    bottomSheet: showM3BottomSheet,
    alert: (title, text, type = 'info') => showM3Dialog({ title, text, icon: type }),
    confirm: (title, text, onConfirm) => showM3Dialog({
      title,
      text,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Confirm',
      cancelButtonText: 'Cancel'
    }).then(res => {
      if (res.isConfirmed && typeof onConfirm === 'function') onConfirm();
      return res;
    })
  };

  console.log('[EIMBox] Material Design 3 (M3) Alert & Dialog System Active.');

})(window, document);

function showToast(type, message, title = '') {
    const types = {
        success: 'bg-success text-white',
        info: 'bg-info text-dark',
        primary: 'bg-primary text-white',
        warning: 'bg-warning text-dark',
        danger: 'bg-danger text-white',
        error: 'bg-danger text-white'
    };

    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center border-0 mb-2 ${types[type] || 'bg-secondary text-white'}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${title ? `<strong>${title}</strong><br>` : ''}
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    const container = document.getElementById('toastContainer');
    container.insertAdjacentHTML('beforeend', toastHTML);

    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}


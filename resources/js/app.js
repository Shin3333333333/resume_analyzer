import 'bootstrap/dist/js/bootstrap.bundle.min.js';


window.loadingModal = null;

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('loadingModal');

    if (modalEl) {
        window.loadingModal = new bootstrap.Modal(modalEl);
    }
});

window.showLoading = function (
    title = 'Processing',
    message = 'Please wait while we process your request…'
) {
    if (!window.loadingModal) return;

    const titleEl = document.getElementById('loadingTitle');
    const messageEl = document.getElementById('loadingMessage');

    if (titleEl) titleEl.textContent = title;
    if (messageEl) messageEl.textContent = message;

    window.loadingModal.show();
};

window.hideLoading = function () {
    if (!window.loadingModal) return;
    window.loadingModal.hide();
};

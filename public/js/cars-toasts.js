(function () {
    const VISIBLE_MS = 5200;
    const EXIT_MS = 280;

    function dismissToast(toast) {
        if (toast.dataset.carsToastClosing === '1') {
            return;
        }
        toast.dataset.carsToastClosing = '1';
        toast.classList.remove('is-visible');
        toast.classList.add('is-hiding');
        window.setTimeout(() => toast.remove(), EXIT_MS);
    }

    function initCarsToasts(root) {
        const scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('[data-cars-toast]:not([data-cars-toast-ready])').forEach((toast, index) => {
            toast.dataset.carsToastReady = '1';

            window.requestAnimationFrame(() => {
                window.setTimeout(() => toast.classList.add('is-visible'), 30 + index * 70);
            });

            const close = toast.querySelector('[data-cars-toast-close]');
            close?.addEventListener('click', () => dismissToast(toast));

            window.setTimeout(() => dismissToast(toast), VISIBLE_MS + index * 180);
        });
    }

    document.addEventListener('DOMContentLoaded', () => initCarsToasts(document));
    document.addEventListener('turbo:load', () => initCarsToasts(document));
    document.addEventListener('turbo:render', () => initCarsToasts(document));
})();

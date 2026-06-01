let initialized = false;

const SHOW_DELAY_MS = 0;
const MAX_VISIBLE_MS = 12000;

const initPageTransitionLoader = () => {
    if (initialized) {
        return;
    }

    const loader = document.getElementById('page-transition-loader');

    if (!loader) {
        return;
    }

    initialized = true;

    let showTimer = 0;
    let hideTimer = 0;

    const clearTimers = () => {
        if (showTimer) {
            window.clearTimeout(showTimer);
            showTimer = 0;
        }

        if (hideTimer) {
            window.clearTimeout(hideTimer);
            hideTimer = 0;
        }
    };

    const hidePageLoader = () => {
        clearTimers();
        loader.classList.remove('is-visible');
        loader.setAttribute('aria-hidden', 'true');
    };

    const showPageLoader = (message = 'Preparing the next page...') => {
        if (loader.classList.contains('is-visible') || showTimer) {
            return;
        }

        showTimer = window.setTimeout(() => {
            showTimer = 0;
            const messageElement = loader.querySelector('.page-transition-message');

            if (messageElement) {
                messageElement.textContent = message;
            }

            loader.classList.add('is-visible');
            loader.setAttribute('aria-hidden', 'false');

            hideTimer = window.setTimeout(hidePageLoader, MAX_VISIBLE_MS);
        }, SHOW_DELAY_MS);
    };

    const isModifiedClick = (event) => (
        event.defaultPrevented
        || event.button !== 0
        || event.metaKey
        || event.ctrlKey
        || event.shiftKey
        || event.altKey
    );

    const hasLoaderOptOut = (element) => Boolean(element.closest('[data-no-page-loader], [data-no-loader]'));

    const shouldLoadForLink = (link, event) => {
        if (!link || isModifiedClick(event) || hasLoaderOptOut(link)) {
            return false;
        }

        if (link.target && link.target !== '_self') {
            return false;
        }

        if (link.hasAttribute('download')) {
            return false;
        }

        const href = link.getAttribute('href');

        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
            return false;
        }

        const url = new URL(link.href, window.location.href);

        if (url.origin !== window.location.origin) {
            return false;
        }

        const current = new URL(window.location.href);
        const sameDocument = url.pathname === current.pathname && url.search === current.search;

        return !(sameDocument && url.hash);
    };

    const shouldLoadForForm = (form) => {
        if (!form || hasLoaderOptOut(form)) {
            return false;
        }

        if (form.target && form.target !== '_self') {
            return false;
        }

        const method = (form.getAttribute('method') || 'GET').toUpperCase();

        return method !== 'GET';
    };

    document.addEventListener('click', (event) => {
        const link = event.target instanceof Element ? event.target.closest('a[href]') : null;

        if (shouldLoadForLink(link, event)) {
            showPageLoader('Opening the next page...');
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || !shouldLoadForForm(form) || event.defaultPrevented) {
            return;
        }

        showPageLoader('Saving your request...');
    });

    window.addEventListener('pageshow', hidePageLoader);
    window.addEventListener('pagehide', clearTimers);
    window.addEventListener('load', hidePageLoader);
    window.addEventListener('online', hidePageLoader);

    window.HarvianaPageLoader = {
        hide: hidePageLoader,
        show: showPageLoader,
    };
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageTransitionLoader, { once: true });
} else {
    initPageTransitionLoader();
}

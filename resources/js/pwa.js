const isSecureContextForServiceWorker = () => {
    const { hostname, protocol } = window.location;

    return protocol === 'https:' || ['localhost', '127.0.0.1', '::1'].includes(hostname);
};

const postToServiceWorker = (message) => {
    if (!navigator.serviceWorker?.controller) {
        return;
    }

    navigator.serviceWorker.controller.postMessage(message);
};

const clearRuntimeCachesOnAuthBoundary = () => {
    if (['/login', '/register'].includes(window.location.pathname)) {
        postToServiceWorker({ type: 'CLEAR_RUNTIME_CACHES' });
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const action = new URL(form.action || window.location.href, window.location.origin);

        if (action.origin === window.location.origin && action.pathname === '/logout') {
            postToServiceWorker({ type: 'CLEAR_RUNTIME_CACHES' });
        }
    });
};

const syncViewportHeight = () => {
    const height = window.visualViewport?.height || window.innerHeight;

    if (height > 0) {
        document.documentElement.style.setProperty('--harviana-viewport-height', `${height}px`);
    }
};

const syncThemeColor = (color) => {
    document.querySelectorAll('meta[name="theme-color"], meta[name="msapplication-TileColor"]').forEach((meta) => {
        meta.setAttribute('content', color);
    });
};

window.addEventListener('harviana-sidebar-theme', (event) => {
    syncThemeColor('#355872');
});

if ('serviceWorker' in navigator && isSecureContextForServiceWorker()) {
    let refreshing = false;

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (refreshing) {
            return;
        }

        refreshing = true;
        window.location.reload();
    });

    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');

            if (registration.waiting) {
                registration.waiting.postMessage({ type: 'SKIP_WAITING' });
            }

            registration.addEventListener('updatefound', () => {
                const nextWorker = registration.installing;

                nextWorker?.addEventListener('statechange', () => {
                    if (nextWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        nextWorker.postMessage({ type: 'SKIP_WAITING' });
                    }
                });
            });

            clearRuntimeCachesOnAuthBoundary();
        } catch (error) {
            console.info('Harviana PWA registration skipped.', error);
        }
    });
}

syncViewportHeight();
window.addEventListener('resize', syncViewportHeight, { passive: true });
window.visualViewport?.addEventListener('resize', syncViewportHeight, { passive: true });
window.visualViewport?.addEventListener('scroll', syncViewportHeight, { passive: true });

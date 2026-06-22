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
        postToServiceWorker({ type: 'CLEAR_PAGE_CACHE' });
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const action = new URL(form.action || window.location.href, window.location.origin);

        if (action.origin === window.location.origin && action.pathname === '/logout') {
            postToServiceWorker({ type: 'CLEAR_PAGE_CACHE' });
        }
    });
};

let viewportSyncFrame = 0;
let lastViewportHeight = 0;

const syncViewportHeight = () => {
    if (viewportSyncFrame) {
        return;
    }

    viewportSyncFrame = window.requestAnimationFrame(() => {
        viewportSyncFrame = 0;

        const height = Math.round(window.visualViewport?.height || window.innerHeight);

        if (height > 0 && Math.abs(height - lastViewportHeight) > 1) {
            lastViewportHeight = height;
            document.documentElement.style.setProperty('--harviana-viewport-height', `${height}px`);
        }
    });
};

const syncThemeColor = (color) => {
    document.querySelectorAll('meta[name="theme-color"], meta[name="msapplication-TileColor"]').forEach((meta) => {
        meta.setAttribute('content', color);
    });
};

let pendingServiceWorker = null;
let updateAccepted = false;
let updatePromptElement = null;

const hideUpdatePrompt = () => {
    updatePromptElement?.remove();
    updatePromptElement = null;
};

const createUpdatePrompt = () => {
    const prompt = document.createElement('div');
    prompt.setAttribute('role', 'status');
    prompt.setAttribute('aria-live', 'polite');
    prompt.style.cssText = [
        'position: fixed',
        'left: calc(1rem + env(safe-area-inset-left, 0px))',
        'right: calc(1rem + env(safe-area-inset-right, 0px))',
        'bottom: calc(1rem + env(safe-area-inset-bottom, 0px))',
        'z-index: 10000',
        'display: flex',
        'align-items: center',
        'justify-content: space-between',
        'gap: 0.75rem',
        'max-width: 32rem',
        'margin-left: auto',
        'margin-right: auto',
        'border: 1px solid rgba(21, 128, 61, 0.18)',
        'border-radius: 0.875rem',
        'background: rgba(255, 255, 255, 0.98)',
        'padding: 0.875rem',
        'box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18)',
        'color: #111827',
    ].join(';');

    prompt.innerHTML = `
        <div style="min-width: 0;">
            <p style="margin: 0; font-size: 0.875rem; font-weight: 700; line-height: 1.25;">Update Harviana?</p>
            <p style="margin: 0.125rem 0 0; font-size: 0.75rem; line-height: 1.35; color: #6b7280;">A newer version is ready. Update when you are not filling out a form.</p>
        </div>
        <div style="display: flex; flex-shrink: 0; gap: 0.5rem;">
            <button type="button" data-harviana-update-later style="border: 0; border-radius: 0.625rem; background: #f3f4f6; padding: 0.5rem 0.75rem; color: #374151; font-size: 0.8125rem; font-weight: 700;">Later</button>
            <button type="button" data-harviana-update-now style="border: 0; border-radius: 0.625rem; background: #15803d; padding: 0.5rem 0.75rem; color: #ffffff; font-size: 0.8125rem; font-weight: 700;">Update</button>
        </div>
    `;

    prompt.querySelector('[data-harviana-update-later]')?.addEventListener('click', hideUpdatePrompt);
    prompt.querySelector('[data-harviana-update-now]')?.addEventListener('click', () => {
        if (!pendingServiceWorker) {
            hideUpdatePrompt();
            return;
        }

        updateAccepted = true;
        pendingServiceWorker.postMessage({ type: 'SKIP_WAITING' });
        hideUpdatePrompt();
    });

    return prompt;
};

const showUpdatePrompt = (worker) => {
    if (!worker || updateAccepted) {
        return;
    }

    pendingServiceWorker = worker;

    if (updatePromptElement) {
        return;
    }

    updatePromptElement = createUpdatePrompt();
    document.body.appendChild(updatePromptElement);
};

window.addEventListener('harviana-sidebar-theme', (event) => {
    syncThemeColor('#355872');
});

if ('serviceWorker' in navigator && isSecureContextForServiceWorker()) {
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (!updateAccepted) {
            return;
        }

        updateAccepted = false;
        window.location.reload();
    });

    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');

            if (registration.waiting) {
                showUpdatePrompt(registration.waiting);
            }

            registration.addEventListener('updatefound', () => {
                const nextWorker = registration.installing;

                nextWorker?.addEventListener('statechange', () => {
                    if (nextWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdatePrompt(nextWorker);
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

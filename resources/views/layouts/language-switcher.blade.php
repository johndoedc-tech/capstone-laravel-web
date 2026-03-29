@php
    $showControl = $showControl ?? true;
@endphp

<div id="google_translate_element" style="display: none;"></div>

@if ($showControl)
    <div class="app-language-switcher" data-app-language-switcher>
        <label for="app-language-select" class="app-language-label">Language</label>
        <select id="app-language-select" class="app-language-select" aria-label="Select language">
            <option value="en">English</option>
            <option value="tl">Tagalog</option>
        </select>
    </div>
@endif

<style>
    html,
    body,
    body.translated-ltr,
    body.translated-rtl {
        top: 0 !important;
        margin-top: 0 !important;
    }

    .goog-te-banner-frame,
    .goog-te-banner-frame.skiptranslate,
    iframe.goog-te-banner-frame,
    iframe.goog-te-banner-frame.skiptranslate,
    iframe.VIpgJd-ZVi9od-ORHb,
    .VIpgJd-ZVi9od-ORHb,
    .VIpgJd-ZVi9od-aZ2wEe-wOHMyf,
    .goog-te-balloon-frame,
    #goog-gt-tt,
    .goog-tooltip,
    .goog-tooltip:hover {
        display: none !important;
        visibility: hidden !important;
    }

    .goog-text-highlight {
        background-color: transparent !important;
        box-shadow: none !important;
    }

    .app-language-switcher {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        z-index: 60;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 9999px;
        padding: 0.4rem 0.75rem;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .app-language-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
    }

    .app-language-select {
        border: none;
        background: transparent;
        color: #111827;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.15rem 0.25rem;
        outline: none;
        cursor: pointer;
    }

    @media (max-width: 640px) {
        .app-language-switcher {
            right: 0.75rem;
            bottom: 0.75rem;
            padding: 0.35rem 0.65rem;
        }

        body.translated-ltr button.lg\:hidden.fixed.top-3.left-3,
        body.translated-rtl button.lg\:hidden.fixed.top-3.left-3,
        body.translated-ltr button.lg\:hidden.fixed.top-4.left-4,
        body.translated-rtl button.lg\:hidden.fixed.top-4.left-4 {
            top: 3.75rem !important;
        }
    }
</style>

<script>
    (function () {
        const STORAGE_KEY = 'preferred_language';
        const LEGACY_KEY = 'dashboard_language';
        const COOKIE_NAME = 'googtrans';
        const BANNER_SELECTORS = [
            '.goog-te-banner-frame',
            '.goog-te-banner-frame.skiptranslate',
            'iframe.goog-te-banner-frame',
            'iframe.goog-te-banner-frame.skiptranslate',
            'iframe.VIpgJd-ZVi9od-ORHb',
            '.VIpgJd-ZVi9od-ORHb',
            '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf'
        ];
        const SUPPORTED_LANGS = {
            en: '/en/en',
            tl: '/en/tl',
        };

        function hideTranslateBanner() {
            if (document.documentElement) {
                document.documentElement.style.top = '0px';
                document.documentElement.style.marginTop = '0px';
            }

            if (document.body) {
                document.body.style.top = '0px';
                document.body.style.marginTop = '0px';
            }

            BANNER_SELECTORS.forEach(function (selector) {
                document.querySelectorAll(selector).forEach(function (node) {
                    node.style.display = 'none';
                    node.style.visibility = 'hidden';
                    node.style.top = '-1000px';
                });
            });
        }

        function startBannerGuard() {
            hideTranslateBanner();

            let ticks = 0;
            const intervalId = window.setInterval(function () {
                hideTranslateBanner();
                ticks += 1;

                if (ticks > 40) {
                    window.clearInterval(intervalId);
                }
            }, 250);

            if (document.body) {
                const observer = new MutationObserver(hideTranslateBanner);
                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'style']
                });
            }
        }

        function normalizeLanguage(lang) {
            return lang === 'tl' ? 'tl' : 'en';
        }

        function getPreferredLanguage() {
            const saved = localStorage.getItem(STORAGE_KEY) || localStorage.getItem(LEGACY_KEY) || 'en';
            return normalizeLanguage(saved);
        }

        function setCookie(name, value) {
            const cookieValue = `${name}=${value};path=/;max-age=31536000`;
            document.cookie = cookieValue;

            if (window.location.hostname) {
                document.cookie = `${cookieValue};domain=${window.location.hostname}`;
            }
        }

        function persistLanguage(lang) {
            const normalizedLang = normalizeLanguage(lang);
            localStorage.setItem(STORAGE_KEY, normalizedLang);
            localStorage.setItem(LEGACY_KEY, normalizedLang);
            setCookie(COOKIE_NAME, SUPPORTED_LANGS[normalizedLang]);
        }

        function applyLanguage(lang, reload = true) {
            const normalizedLang = normalizeLanguage(lang);
            persistLanguage(normalizedLang);

            if (reload) {
                window.location.reload();
            }
        }

        window.applyAppLanguagePreference = applyLanguage;

        const initialLanguage = getPreferredLanguage();
        persistLanguage(initialLanguage);

        window.addEventListener('load', hideTranslateBanner);

        window.googleTranslateElementInit = function () {
            new google.translate.TranslateElement(
                {
                    pageLanguage: 'en',
                    includedLanguages: 'en,tl',
                    autoDisplay: false,
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                },
                'google_translate_element'
            );

            startBannerGuard();
        };

        document.addEventListener('DOMContentLoaded', function () {
            startBannerGuard();

            const select = document.getElementById('app-language-select');

            if (!select) {
                return;
            }

            select.value = initialLanguage;
            select.addEventListener('change', function (event) {
                applyLanguage(event.target.value, true);
            });
        });
    })();
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

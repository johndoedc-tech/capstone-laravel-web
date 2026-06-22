@once
    <style>
        .page-transition-loader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: grid;
            place-items: center;
            padding: calc(1.5rem + env(safe-area-inset-top, 0px)) calc(1.5rem + env(safe-area-inset-right, 0px)) calc(1.5rem + env(safe-area-inset-bottom, 0px)) calc(1.5rem + env(safe-area-inset-left, 0px));
            background: rgba(247, 248, 240, 0.92);
            opacity: 0;
            pointer-events: none;
            transition: opacity 160ms ease;
        }

        .page-transition-loader.is-visible {
            opacity: 1;
            pointer-events: auto;
        }

        .page-transition-card {
            display: flex;
            min-width: min(20rem, 100%);
            align-items: center;
            gap: 0.875rem;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
            padding: 1rem 1.125rem;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.14);
        }

        .page-transition-spinner {
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 auto;
            border: 3px solid #d9e4c2;
            border-top-color: #15803d;
            border-radius: 9999px;
            animation: pageTransitionSpin 0.8s linear infinite;
        }

        .page-transition-title {
            margin: 0;
            color: #111827;
            font-size: 0.9375rem;
            font-weight: 700;
            line-height: 1.25;
        }

        .page-transition-message {
            margin: 0.125rem 0 0;
            color: #6b7280;
            font-size: 0.8125rem;
            line-height: 1.35;
        }

        @keyframes pageTransitionSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .page-transition-loader {
                transition: none;
            }

            .page-transition-spinner {
                animation: none;
            }
        }
    </style>
@endonce

<div id="page-transition-loader" class="page-transition-loader" aria-live="polite" aria-hidden="true" role="status">
    <div class="page-transition-card">
        <div class="page-transition-spinner" aria-hidden="true"></div>
        <div>
            <p class="page-transition-title">Loading</p>
            <p class="page-transition-message">Preparing the next page...</p>
        </div>
    </div>
</div>

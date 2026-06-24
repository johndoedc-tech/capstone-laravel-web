<x-app-layout>
    <style>
        /* Leaflet popup polish */
        .leaflet-popup-content-wrapper {
            border-radius: 1rem !important;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important;
            padding: 0 !important;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .leaflet-popup-content {
            margin: 0 !important;
            padding: 1rem !important;
            width: auto !important;
            min-width: 140px;
            max-width: 85vw;
        }
        .leaflet-popup-tip {
            box-shadow: none !important;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            border-left: 1px solid #e2e8f0;
        }
        .leaflet-container a.leaflet-popup-close-button {
            top: 12px;
            right: 12px;
            color: #94a3b8;
            font-weight: bold;
            padding: 4px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: rgba(241, 245, 249, 0.5);
            transition: all 0.2s;
            z-index: 10;
        }
        .leaflet-container a.leaflet-popup-close-button:hover {
            color: #0f172a;
            background: #e2e8f0;
        }
        .map-pinpoint {
            width: 1.55rem;
            height: 1.55rem;
            border-radius: 50% 50% 50% 0;
            background: #16a34a;
            border: 3px solid #ffffff;
            box-shadow: 0 8px 18px rgb(15 23 42 / 0.28);
            transform: rotate(-45deg);
            transform-origin: center;
        }
        .map-pinpoint::after {
            content: '';
            position: absolute;
            inset: 0.36rem;
            border-radius: 9999px;
            background: #ffffff;
            opacity: 0.9;
        }
        .map-pinpoint.no-data {
            background: #94a3b8;
        }
        .map-pinpoint.preferred {
            background: #0f766e;
            box-shadow: 0 8px 22px rgb(15 118 110 / 0.34);
        }
        .pwa-map-viewport {
            height: min(64vh, 34rem);
            min-height: 24rem;
        }
        .pwa-map-details-panel {
            left: 0;
            right: 0;
            bottom: 0;
            max-height: min(82vh, calc(var(--harviana-viewport-height) - 4.5rem - var(--harviana-safe-top)));
            border-radius: 1.25rem 1.25rem 0 0;
            background: #f8fafc;
            box-shadow: 0 -18px 45px rgb(15 23 42 / 0.18);
            padding-bottom: var(--harviana-safe-bottom);
            overscroll-behavior: contain;
        }
        .pwa-map-details-panel.translate-x-full {
            transform: translateY(100%) !important;
        }
        .pwa-map-details-panel:not(.translate-x-full) {
            transform: translateY(0) !important;
        }
        .pwa-panel-handle {
            width: 2.75rem;
            height: 0.25rem;
            border-radius: 9999px;
            background: #cbd5e1;
        }
        .pwa-panel-header {
            position: sticky;
            top: 0;
            z-index: 2;
            margin: -1rem -1rem 1rem;
            padding: 0.75rem 1rem 1rem;
            background: rgb(248 250 252 / 0.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e5e7eb;
        }
        .pwa-panel-count {
            border: 1px solid #bbf7d0;
            background: #ecfdf5;
            color: #166534;
            border-radius: 9999px;
            padding: 0.35rem 0.65rem;
            line-height: 1;
            white-space: nowrap;
        }
        .pwa-panel-section {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 0.875rem;
            padding: 1rem;
        }
        .pwa-panel-section-primary {
            border-color: #bbf7d0;
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.05);
        }
        .pwa-panel-section-primary.is-warning {
            border-color: #fde68a;
        }
        .pwa-section-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
            color: #475569;
        }
        .pwa-section-help {
            margin-top: 0.15rem;
            font-size: 0.76rem;
            color: #64748b;
        }
        .pwa-status-pill,
        .pwa-soft-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            font-size: 0.68rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }
        .pwa-status-pill {
            background: #ecfdf5;
            color: #166534;
            padding: 0.35rem 0.55rem;
        }
        .pwa-status-pill.is-warning {
            background: #fffbeb;
            color: #92400e;
        }
        .pwa-soft-pill {
            background: #f1f5f9;
            color: #334155;
            padding: 0.35rem 0.6rem;
        }
        .pwa-status-pill.hidden,
        .pwa-soft-pill.hidden {
            display: none !important;
        }
        .pwa-crop-row {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 0.75rem;
        }
        .pwa-crop-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.65rem;
        }
        .pwa-crop-meta span {
            border-radius: 9999px;
            background: #f8fafc;
            color: #475569;
            padding: 0.35rem 0.55rem;
            font-size: 0.68rem;
            font-weight: 650;
        }
        .pwa-crop-meta span.is-danger {
            background: #fef2f2;
            color: #b91c1c;
        }
        .pwa-forecast-strip {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 0.35rem;
            scroll-snap-type: x proximity;
            scrollbar-width: thin;
        }
        .pwa-forecast-card {
            min-width: 5.75rem;
            flex-shrink: 0;
            scroll-snap-align: start;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            border-radius: 0.7rem;
            padding: 0.6rem;
            text-align: center;
        }
        .pwa-progress-track {
            height: 0.4rem;
            overflow: hidden;
            border-radius: 9999px;
            background: #e5e7eb;
        }
        .pwa-progress-fill {
            height: 100%;
            border-radius: inherit;
            background: #16a34a;
        }
        .pwa-secondary-block {
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }
        @media (min-width: 640px) {
            .pwa-map-viewport {
                height: 650px;
            }
            .pwa-map-details-panel {
                left: auto;
                top: calc(4.5rem + var(--harviana-safe-top));
                bottom: auto;
                height: calc(var(--harviana-viewport-height) - 4.5rem - var(--harviana-safe-top));
                max-height: none;
                border-radius: 1rem 0 0 1rem;
                box-shadow: -16px 0 40px rgb(15 23 42 / 0.16);
            }
            .pwa-map-details-panel.translate-x-full {
                transform: translateX(100%) !important;
            }
            .pwa-map-details-panel:not(.translate-x-full) {
                transform: translateX(0) !important;
            }
            .pwa-panel-header {
                margin: -1.25rem -1.25rem 1rem;
                padding: 1rem 1.25rem;
            }
            .pwa-panel-handle {
                display: none;
            }
        }
        @media (min-width: 1024px) {
            .pwa-map-viewport {
                height: 800px;
            }
            .pwa-map-details-panel {
                top: 0;
                height: var(--harviana-viewport-height);
            }
            .pwa-panel-header {
                margin: -1.5rem -1.5rem 1rem;
                padding: 1rem 1.5rem;
            }
        }
        .map-weather-control {
            margin: 12px 12px 0 0 !important;
        }
        .map-weather-card {
            width: min(320px, calc(100vw - 48px));
            border: 1px solid rgb(226 232 240 / 0.95);
            border-radius: 8px;
            background: rgb(255 255 255 / 0.96);
            box-shadow: 0 18px 40px rgb(15 23 42 / 0.16);
            color: #0f172a;
            font-family: inherit;
            padding: 0.875rem;
            backdrop-filter: blur(12px);
            cursor: pointer;
        }
        .map-weather-card:focus-visible {
            outline: 3px solid rgb(34 197 94 / 0.35);
            outline-offset: 2px;
        }
        .map-weather-card.is-empty {
            cursor: default;
        }
        .map-weather-card__header,
        .map-weather-card__main,
        .map-weather-card__meta {
            display: flex;
            align-items: center;
        }
        .map-weather-card__header {
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.65rem;
        }
        .map-weather-card__label {
            color: #475569;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .map-weather-card__action {
            border: 0;
            border-radius: 9999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 0.72rem;
            font-weight: 700;
            line-height: 1;
            padding: 0.45rem 0.65rem;
            transition: background 0.16s ease, color 0.16s ease;
        }
        .map-weather-card__action:hover,
        .map-weather-card__action:focus-visible {
            background: #d1fae5;
            color: #065f46;
            outline: none;
        }
        .map-weather-card__action:disabled {
            background: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }
        .map-weather-card__main {
            justify-content: space-between;
            gap: 0.85rem;
        }
        .map-weather-card__identity {
            display: flex;
            align-items: center;
            min-width: 0;
            gap: 0.65rem;
        }
        .map-weather-card__icon {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 9999px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            overflow: hidden;
        }
        .map-weather-card__icon img {
            width: 1.85rem;
            height: 1.85rem;
            object-fit: contain;
        }
        .map-weather-card__icon-fallback {
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 9999px;
            background: #38bdf8;
            box-shadow: 0 0 0 6px rgb(56 189 248 / 0.14);
        }
        .map-weather-card__place {
            color: #0f172a;
            font-size: 0.9rem;
            font-weight: 800;
            line-height: 1.15;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .map-weather-card__condition {
            color: #64748b;
            font-size: 0.76rem;
            line-height: 1.25;
            margin: 0.18rem 0 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .map-weather-card__temp {
            color: #15803d;
            flex: 0 0 auto;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            line-height: 1;
        }
        .map-weather-card__meta {
            border-top: 1px solid #e2e8f0;
            color: #475569;
            display: grid;
            font-size: 0.74rem;
            gap: 0.35rem 0.6rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 0.75rem;
            padding-top: 0.65rem;
        }
        .map-weather-card__meta span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .map-weather-card__updated {
            color: #94a3b8;
            font-size: 0.68rem;
            margin-top: 0.55rem;
        }
        .map-weather-card__status {
            color: #64748b;
            font-size: 0.78rem;
            margin-top: 0.5rem;
        }
        .map-weather-card.is-error .map-weather-card__condition,
        .map-weather-card.is-error .map-weather-card__status {
            color: #b91c1c;
        }
        .map-weather-card.is-stale .map-weather-card__updated::after {
            content: ' - stale cache';
            color: #b45309;
            font-weight: 700;
        }
        .weather-map-marker {
            align-items: center;
            background: rgb(255 255 255 / 0.97);
            border: 1px solid #dbeafe;
            border-radius: 9999px;
            box-shadow: 0 12px 28px rgb(15 23 42 / 0.2);
            color: #0f172a;
            display: inline-flex;
            gap: 0.45rem;
            max-width: 145px;
            min-width: 112px;
            padding: 0.35rem 0.55rem 0.35rem 0.4rem;
            pointer-events: auto;
            white-space: nowrap;
        }
        .weather-map-marker__icon {
            align-items: center;
            background: #eff6ff;
            border-radius: 9999px;
            display: flex;
            height: 1.65rem;
            justify-content: center;
            overflow: hidden;
            width: 1.65rem;
            flex: 0 0 auto;
        }
        .weather-map-marker__icon img {
            height: 1.3rem;
            object-fit: contain;
            width: 1.3rem;
        }
        .weather-map-marker__dot {
            width: 0.48rem;
            height: 0.48rem;
            border-radius: 9999px;
            background: #38bdf8;
        }
        .weather-map-marker__copy {
            min-width: 0;
        }
        .weather-map-marker__temp {
            color: #15803d;
            font-size: 0.82rem;
            font-weight: 800;
            line-height: 1;
        }
        .weather-map-marker__condition {
            color: #64748b;
            font-size: 0.62rem;
            line-height: 1.15;
            margin-top: 0.1rem;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .weather-map-marker.is-loading .weather-map-marker__temp,
        .weather-map-marker.is-error .weather-map-marker__temp {
            color: #475569;
        }
        .weather-map-marker.is-error .weather-map-marker__condition {
            color: #b91c1c;
        }
        .weather-forecast-modal {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: calc(0.75rem + var(--harviana-safe-top)) 0.75rem calc(0.75rem + var(--harviana-safe-bottom));
        }
        .weather-forecast-modal[hidden] {
            display: none !important;
        }
        .weather-forecast-backdrop {
            position: absolute;
            inset: 0;
            background: rgb(15 23 42 / 0.45);
            backdrop-filter: blur(4px);
        }
        .weather-forecast-dialog {
            position: relative;
            width: min(680px, 100%);
            max-height: min(86vh, calc(var(--harviana-viewport-height) - var(--harviana-safe-top) - var(--harviana-safe-bottom) - 1.5rem));
            overflow-y: auto;
            border: 1px solid rgb(226 232 240 / 0.9);
            border-radius: 1rem 1rem 0.8rem 0.8rem;
            background: #ffffff;
            box-shadow: 0 28px 80px rgb(15 23 42 / 0.28);
        }
        .weather-forecast-header {
            position: sticky;
            top: 0;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid #e2e8f0;
            background: rgb(255 255 255 / 0.96);
            padding: 1rem;
            backdrop-filter: blur(10px);
        }
        .weather-forecast-close {
            border: 0;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 0.78rem;
            font-weight: 800;
            padding: 0.5rem 0.8rem;
        }
        .weather-forecast-close:focus-visible,
        .weather-forecast-close:hover {
            background: #e2e8f0;
            outline: none;
        }
        .weather-forecast-body {
            padding: 1rem;
        }
        .weather-forecast-current {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: center;
            border: 1px solid #bbf7d0;
            border-radius: 0.9rem;
            background: #f0fdf4;
            padding: 1rem;
        }
        .weather-forecast-current-main {
            display: flex;
            align-items: center;
            min-width: 0;
            gap: 0.75rem;
        }
        .weather-forecast-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            overflow: hidden;
        }
        .weather-forecast-icon img {
            width: 2.15rem;
            height: 2.15rem;
            object-fit: contain;
        }
        .weather-forecast-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.5rem;
        }
        .weather-forecast-meta p {
            border-radius: 0.75rem;
            background: #f8fafc;
            color: #475569;
            font-size: 0.76rem;
            padding: 0.65rem;
        }
        .weather-forecast-advice {
            border-radius: 0.8rem;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            padding: 0.85rem;
        }
        @media (max-width: 640px) {
            .map-weather-control {
                margin: 10px 10px 0 0 !important;
            }
            .map-weather-card {
                padding: 0.75rem;
                width: min(290px, calc(100vw - 74px));
            }
            .map-weather-card__meta {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .map-weather-card__temp {
                font-size: 1.35rem;
            }
            .weather-map-marker {
                max-width: 128px;
                min-width: 100px;
            }
            .weather-forecast-meta {
                grid-template-columns: 1fr;
            }
        }
        @media (min-width: 640px) {
            .weather-forecast-modal {
                align-items: center;
                padding: calc(1.25rem + var(--harviana-safe-top)) 1.25rem calc(1.25rem + var(--harviana-safe-bottom));
            }
            .weather-forecast-dialog {
                border-radius: 1rem;
            }
        }
    </style>
    <div class="py-4 lg:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-full mx-auto">

            <!-- Personalized Welcome Banner (if user has preferences) -->
            @if(isset($preferredMunicipality) && $preferredMunicipality)
                <div
                    class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4 mb-4 lg:mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="bg-green-100 p-2 rounded-full">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-green-800">Your Farm Location: <span
                                        class="font-bold">{{ ucwords(strtolower($preferredMunicipality)) }}</span></p>
                                <p class="text-xs text-green-600">Map is on your town.</p>
                            </div>
                        </div>
                        <button onclick="focusOnMyMunicipality()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Show My Area
                        </button>
                    </div>
                </div>
            @endif

            <div class="flex flex-col-reverse lg:flex-col gap-4 lg:gap-6 mb-4 lg:mb-6">
                <!-- Control Panel -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 lg:p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-3 lg:mb-4">Choose What to View</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                        <!-- Crop Filter -->
                        <div>
                            <label for="crop-filter"
                                class="block text-xs lg:text-sm font-medium text-gray-700 mb-1 lg:mb-2">
                                Crop Type
                            </label>
                            <select id="crop-filter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm lg:text-base">
                                <option value="">Loading...</option>
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div>
                            <label for="year-filter"
                                class="block text-xs lg:text-sm font-medium text-gray-700 mb-1 lg:mb-2">
                                Year
                            </label>
                            <select id="year-filter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm lg:text-base">
                                <option value="">Loading...</option>
                            </select>
                        </div>

                        <!-- View Type Filter -->
                        <div>
                            <label for="view-filter"
                                class="block text-xs lg:text-sm font-medium text-gray-700 mb-1 lg:mb-2">
                                Show
                            </label>
                            <select id="view-filter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm lg:text-base">
                                <option value="supply_forecast">Expected Supply (mt)</option>
                                <option value="production">Past Production (mt)</option>
                                <option value="area_harvested">Area Harvested (ha)</option>
                                <option value="productivity">Productivity (mt/ha)</option>
                            </select>
                        </div>

                        <!-- Farm Type Filter -->
                        <div>
                            <label for="farm-type-filter"
                                class="block text-xs lg:text-sm font-medium text-gray-700 mb-1 lg:mb-2">
                                Farm Type
                            </label>
                            <select id="farm-type-filter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm lg:text-base">
                                <option value="">All Farm Types</option>
                                <option value="Irrigated">Irrigated</option>
                                <option value="Rainfed">Rainfed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loading-indicator" class="hidden mt-4">
                        <div class="flex items-center text-green-600">
                            <svg class="animate-spin h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span>Loading map...</span>
                        </div>
                    </div>

                    <div id="map-error" class="hidden mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <div class="flex items-start justify-between gap-3">
                            <p class="min-w-0" data-map-error-message></p>
                            <button type="button" onclick="clearMapError()" class="shrink-0 font-semibold text-red-700">Dismiss</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-3 lg:p-6 relative">
                    <div id="map"
                        class="pwa-map-viewport relative z-0 rounded-lg shadow-inner"></div>

                    <!-- Municipality Details Panel -->
                    <div id="details-panel"
                        class="pwa-map-details-panel fixed right-0 z-30 transform translate-x-full transition-transform duration-300 ease-out overflow-y-auto w-full sm:w-[400px] lg:w-[440px]">
                        <div class="p-4 sm:p-5 lg:p-6">
                            <div class="pwa-panel-header">
                                <div class="mb-3 flex justify-center sm:hidden">
                                    <span class="pwa-panel-handle" aria-hidden="true"></span>
                                </div>

                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 id="panel-municipality-name"
                                            class="truncate text-xl font-bold leading-tight text-slate-900 lg:text-2xl">Municipality Name</h2>
                                        <p class="mt-1 text-xs text-slate-500 lg:text-sm">Planting and supply for this town.</p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <div class="pwa-panel-count text-right">
                                            <p id="panel-farmer-count" class="text-sm font-bold">-</p>
                                            <p id="panel-farmer-count-label" class="mt-0.5 text-[10px] font-semibold uppercase text-green-700">farmers</p>
                                        </div>
                                        <button type="button" onclick="closeDetailsPanel()"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition-colors hover:bg-slate-50 hover:text-slate-700"
                                            aria-label="Close town details">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading Indicator -->
                            <div id="panel-loading" class="hidden">
                                <div class="flex items-center justify-center py-12">
                                    <svg class="animate-spin h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            <div id="panel-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                            <!-- Panel Content -->
                            <div id="panel-content" class="space-y-3">
                                <!-- Planting Signal -->
                                <div id="planting-signal-card" class="pwa-panel-section pwa-panel-section-primary">
                                    <div class="mb-3 flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="pwa-section-label">Planting Signal</h3>
                                            <p class="pwa-section-help">Quick guide before you plant.</p>
                                        </div>
                                        <span id="planting-signal-badge" class="pwa-status-pill">Checking</span>
                                    </div>
                                    <p id="planting-signal-title" class="text-base font-semibold text-slate-950">Choose a town</p>
                                    <p id="planting-signal-message" class="mt-1 text-sm leading-relaxed text-slate-600">You will see a simple planting guide here.</p>
                                    <div id="planting-signal-alternatives" class="mt-3 flex flex-wrap gap-1.5"></div>
                                </div>

                                <!-- Real-time Production Outlook -->
                                <div class="pwa-panel-section">
                                    <div class="mb-3 flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="pwa-section-label">Top Crops Here</h3>
                                            <p class="pwa-section-help">A quick read on what may be crowded.</p>
                                        </div>
                                        <span id="production-outlook-count" class="pwa-soft-pill">-</span>
                                    </div>
                                    <div id="production-outlook-list" class="space-y-2">
                                        <p class="text-sm text-slate-500">Choose a town.</p>
                                    </div>
                                </div>

                                <!-- Contribution Per Municipality Chart -->
                                <div id="contribution-section" class="pwa-panel-section pwa-secondary-block hidden">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="pwa-section-label">Crop Share</h3>
                                        <span id="contribution-crop-badge" class="pwa-soft-pill"></span>
                                    </div>
                                    <canvas id="contribution-chart" height="250"></canvas>
                                    <div id="contribution-details" class="mt-3 space-y-1">
                                        <!-- Populated dynamically -->
                                    </div>
                                </div>

                                <!-- Crop Distribution Chart -->
                                <div class="pwa-panel-section pwa-secondary-block">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <div>
                                            <h3 class="pwa-section-label">Crops Here</h3>
                                            <p class="pwa-section-help">Simple list first, chart after.</p>
                                        </div>
                                        <span id="crop-summary-count" class="pwa-soft-pill">-</span>
                                    </div>
                                    <div id="crop-list-summary" class="space-y-2"></div>
                                    <div class="mt-3 hidden sm:block">
                                        <canvas id="crop-chart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div> <!-- End of flex wrappers -->

            <!-- Statistics Panel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-4 lg:mt-6">
                <div class="p-4 lg:p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-3 lg:mb-4">Quick Summary</h3>
                    <div id="stats-content" class="grid grid-cols-2 md:grid-cols-4 gap-3 lg:gap-4">
                        <div class="text-center">
                            <p class="text-xs lg:text-sm text-gray-600">Total Production</p>
                            <p id="stat-total" class="text-lg lg:text-2xl font-bold text-green-600">-</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs lg:text-sm text-gray-600">Average</p>
                            <p id="stat-avg" class="text-lg lg:text-2xl font-bold text-green-600">-</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs lg:text-sm text-gray-600">Highest</p>
                            <p id="stat-max" class="text-lg lg:text-2xl font-bold text-green-600">-</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs lg:text-sm text-gray-600">Lowest</p>
                            <p id="stat-min" class="text-lg lg:text-2xl font-bold text-green-600">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="weather-forecast-modal" class="weather-forecast-modal" hidden aria-hidden="true">
        <div class="weather-forecast-backdrop" data-weather-modal-close></div>
        <section class="weather-forecast-dialog" role="dialog" aria-modal="true" aria-labelledby="weather-modal-title">
            <div class="weather-forecast-header">
                <div class="min-w-0">
                    <p class="pwa-section-label">Field Weather</p>
                    <h2 id="weather-modal-title" class="mt-1 text-xl font-bold text-slate-950">Weather forecast</h2>
                    <p id="weather-modal-subtitle" class="mt-1 text-sm text-slate-500">Choose a municipality to view the forecast.</p>
                </div>
                <button type="button" class="weather-forecast-close" data-weather-modal-close>Close</button>
            </div>

            <div class="weather-forecast-body">
                <div id="weather-modal-loading" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Loading forecast...
                </div>
                <div id="weather-modal-error" class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <div id="weather-modal-content" class="hidden space-y-4">
                    <div class="weather-forecast-current">
                        <div class="weather-forecast-current-main">
                            <div id="weather-modal-icon" class="weather-forecast-icon">
                                <span class="map-weather-card__icon-fallback"></span>
                            </div>
                            <div class="min-w-0">
                                <p id="weather-modal-place" class="truncate text-base font-extrabold uppercase text-slate-950">-</p>
                                <p id="weather-modal-condition" class="mt-1 truncate text-sm text-slate-600">-</p>
                            </div>
                        </div>
                        <p id="weather-modal-temp" class="text-3xl font-extrabold text-green-700">-</p>
                    </div>

                    <div class="weather-forecast-meta">
                        <p>Rain<br><span id="weather-modal-rain" class="font-bold text-slate-900">-</span></p>
                        <p>Humidity<br><span id="weather-modal-humidity" class="font-bold text-slate-900">-</span></p>
                        <p>Wind<br><span id="weather-modal-wind" class="font-bold text-slate-900">-</span></p>
                    </div>

                    <div class="weather-forecast-advice">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p id="weather-modal-advice-title" class="text-sm font-bold text-slate-950">Checking weather</p>
                                <p id="weather-modal-advice-message" class="mt-1 text-sm leading-relaxed text-slate-600">Use this before planting, spraying, or harvesting.</p>
                            </div>
                            <span id="weather-modal-source-badge" class="pwa-status-pill is-warning hidden">Stale cache</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Updated: <span id="weather-modal-updated">-</span></p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Today</p>
                                <span id="weather-modal-hourly-count" class="text-[11px] text-slate-500"></span>
                            </div>
                            <div id="weather-modal-hourly-list" class="pwa-forecast-strip"></div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Next days</p>
                                <span id="weather-modal-daily-count" class="text-[11px] text-slate-500"></span>
                            </div>
                            <div id="weather-modal-daily-list" class="pwa-forecast-strip"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        let map;
        let markerLayer;
        let currentData = {};
        let filterOptions = {};
        let currentMunicipality = null;
        let detailsRequestToken = 0;
        let mapWeatherControlEl = null;
        let mapWeatherMarker = null;
        let mapWeatherRequestToken = 0;
        let weatherForecastRequestToken = 0;
        let selectedWeatherMunicipality = null;
        const weatherPayloadCache = new Map();

        // Base URLs using Laravel's url() helper
        const apiBase = '{{ url("/api/map") }}';

        // User preferences from server
        const userPreferredMunicipality = '{{ $preferredMunicipality ?? '' }}';
        const userFavoriteCrops = @json($favoriteCrops ?? []);

        // Municipality coordinates used for one pinpoint per municipality.
        const municipalityCoords = {
            'ATOK': [16.6274093, 120.7675527],
            'BAKUN': [16.8300411, 120.6830301],
            'BOKOD': [16.4908605, 120.8302587],
            'BUGUIAS': [16.7192014, 120.826902],
            'ITOGON': [16.3657698, 120.633172],
            'KABAYAN': [16.6239201, 120.8381884],
            'KAPANGAN': [16.5761774, 120.6030069],
            'KIBUNGAN': [16.6937271, 120.6533943],
            'LA TRINIDAD': [16.4586825, 120.5812456],
            'MANKAYAN': [16.8572602, 120.7933631],
            'SABLAN': [16.4966909, 120.4875959],
            'TUBA': [16.3926636, 120.5612911],
            'TUBLAY': [16.5145931, 120.6322972]
        };

        function normalizeMunicipalityName(name) {
            return (name || '').toString().toUpperCase().replace(/\s+/g, '');
        }

        function showMapError(message) {
            const errorEl = document.getElementById('map-error');
            const messageEl = errorEl?.querySelector('[data-map-error-message]');

            if (!errorEl || !messageEl) {
                return;
            }

            messageEl.textContent = message;
            errorEl.classList.remove('hidden');
        }

        function clearMapError() {
            const errorEl = document.getElementById('map-error');
            const messageEl = errorEl?.querySelector('[data-map-error-message]');

            if (!errorEl || !messageEl) {
                return;
            }

            messageEl.textContent = '';
            errorEl.classList.add('hidden');
        }

        function showPanelError(message) {
            const loadingEl = document.getElementById('panel-loading');
            const contentEl = document.getElementById('panel-content');
            const errorEl = document.getElementById('panel-error');

            loadingEl?.classList.add('hidden');
            contentEl?.classList.add('hidden');

            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }
        }

        function clearPanelError() {
            const errorEl = document.getElementById('panel-error');

            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }
        }

        // Initialize map
        function initMap() {
            console.log('Initializing map...');

            // If user has preferred municipality, center on it
            let initialCenter = [16.5, 120.7];
            let initialZoom = 10;

            if (userPreferredMunicipality && municipalityCoords[userPreferredMunicipality]) {
                initialCenter = municipalityCoords[userPreferredMunicipality];
            }

            map = L.map('map').setView(initialCenter, initialZoom);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            initWeatherForecastModal();
            initMapWeatherControl();

            if (userPreferredMunicipality && municipalityCoords[userPreferredMunicipality]) {
                loadMapWeatherSummary(userPreferredMunicipality);
            }

            console.log('Map initialized, loading filters...');
            // Load filters
            loadFilters();
        }

        // Focus on user's municipality
        function focusOnMyMunicipality() {
            if (userPreferredMunicipality && municipalityCoords[userPreferredMunicipality]) {
                map.setView(municipalityCoords[userPreferredMunicipality], 10);

                // Open the details panel for this municipality
                loadMunicipalityDetails(userPreferredMunicipality);
            }
        }

        function initMapWeatherControl() {
            const weatherControl = L.control({ position: 'topright' });

            weatherControl.onAdd = function () {
                mapWeatherControlEl = L.DomUtil.create('div', 'leaflet-control map-weather-control');
                mapWeatherControlEl.innerHTML = `
                    <div class="map-weather-card is-empty" data-map-weather-card role="button" tabindex="-1" aria-label="Weather outlook">
                        <div class="map-weather-card__header">
                            <span class="map-weather-card__label">Field weather</span>
                            <button type="button" class="map-weather-card__action" data-map-weather-action disabled>View forecast</button>
                        </div>
                        <div class="map-weather-card__main">
                            <div class="map-weather-card__identity">
                                <div class="map-weather-card__icon" data-map-weather-icon>
                                    <span class="map-weather-card__icon-fallback"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="map-weather-card__place" data-map-weather-place>Select a municipality</p>
                                    <p class="map-weather-card__condition" data-map-weather-condition>Weather appears here after selection.</p>
                                </div>
                            </div>
                            <div class="map-weather-card__temp" data-map-weather-temp>--</div>
                        </div>
                        <div class="map-weather-card__meta" data-map-weather-meta>
                            <span data-map-weather-rain>Rain --</span>
                            <span data-map-weather-humidity>Humidity --</span>
                            <span data-map-weather-wind>Wind --</span>
                        </div>
                        <div class="map-weather-card__updated" data-map-weather-updated>Select a map marker to load current field weather.</div>
                        <p class="map-weather-card__status" data-map-weather-status hidden></p>
                    </div>
                `;

                L.DomEvent.disableClickPropagation(mapWeatherControlEl);
                L.DomEvent.disableScrollPropagation(mapWeatherControlEl);

                const card = mapWeatherControlEl.querySelector('[data-map-weather-card]');
                const action = mapWeatherControlEl.querySelector('[data-map-weather-action]');
                const openSelectedWeather = () => {
                    if (selectedWeatherMunicipality) {
                        openWeatherForecastModal(selectedWeatherMunicipality);
                    }
                };

                card.addEventListener('click', openSelectedWeather);
                card.addEventListener('keydown', (event) => {
                    if ((event.key === 'Enter' || event.key === ' ') && selectedWeatherMunicipality) {
                        event.preventDefault();
                        openSelectedWeather();
                    }
                });
                action.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    openSelectedWeather();
                });

                return mapWeatherControlEl;
            };

            weatherControl.addTo(map);
            setMapWeatherEmpty();
        }

        function getMapWeatherCard() {
            return mapWeatherControlEl?.querySelector('[data-map-weather-card]');
        }

        function updateMapWeatherText(selector, value) {
            const element = mapWeatherControlEl?.querySelector(selector);
            if (element) {
                element.textContent = value;
            }
        }

        function updateMapWeatherAction(enabled) {
            const action = mapWeatherControlEl?.querySelector('[data-map-weather-action]');
            const card = getMapWeatherCard();

            if (action) {
                action.disabled = !enabled;
            }

            if (card) {
                card.tabIndex = enabled ? 0 : -1;
                card.setAttribute('aria-disabled', enabled ? 'false' : 'true');
            }
        }

        function setMapWeatherCardClass(state, isStale = false) {
            const card = getMapWeatherCard();
            if (!card) return;

            card.classList.remove('is-empty', 'is-loading', 'is-ready', 'is-error', 'is-stale');
            card.classList.add(`is-${state}`);

            if (isStale) {
                card.classList.add('is-stale');
            }
        }

        function setMapWeatherStatus(message) {
            const status = mapWeatherControlEl?.querySelector('[data-map-weather-status]');
            if (!status) return;

            status.textContent = message || '';
            status.hidden = !message;
        }

        function setMapWeatherIcon(icon) {
            const iconEl = mapWeatherControlEl?.querySelector('[data-map-weather-icon]');
            if (!iconEl) return;

            iconEl.innerHTML = renderWeatherIconHtml(icon, 'map-weather-card__icon-fallback');
        }

        function setMapWeatherEmpty() {
            selectedWeatherMunicipality = null;
            setMapWeatherCardClass('empty');
            updateMapWeatherAction(false);
            setMapWeatherIcon(null);
            updateMapWeatherText('[data-map-weather-place]', 'Select a municipality');
            updateMapWeatherText('[data-map-weather-condition]', 'Weather appears here after selection.');
            updateMapWeatherText('[data-map-weather-temp]', '--');
            updateMapWeatherText('[data-map-weather-rain]', 'Rain --');
            updateMapWeatherText('[data-map-weather-humidity]', 'Humidity --');
            updateMapWeatherText('[data-map-weather-wind]', 'Wind --');
            updateMapWeatherText('[data-map-weather-updated]', 'Select a map marker to load current field weather.');
            setMapWeatherStatus('');
            removeWeatherMapMarker();
        }

        function setMapWeatherLoading(municipalityName) {
            selectedWeatherMunicipality = municipalityName;
            setMapWeatherCardClass('loading');
            updateMapWeatherAction(true);
            setMapWeatherIcon(null);
            updateMapWeatherText('[data-map-weather-place]', municipalityName);
            updateMapWeatherText('[data-map-weather-condition]', 'Loading current field condition...');
            updateMapWeatherText('[data-map-weather-temp]', '--');
            updateMapWeatherText('[data-map-weather-rain]', 'Rain --');
            updateMapWeatherText('[data-map-weather-humidity]', 'Humidity --');
            updateMapWeatherText('[data-map-weather-wind]', 'Wind --');
            updateMapWeatherText('[data-map-weather-updated]', 'Updating weather from Google Weather API.');
            setMapWeatherStatus('');
            renderWeatherMapMarker(municipalityName, null, 'loading');
        }

        function setMapWeatherError(municipalityName, message) {
            selectedWeatherMunicipality = municipalityName;
            setMapWeatherCardClass('error');
            updateMapWeatherAction(true);
            setMapWeatherIcon(null);
            updateMapWeatherText('[data-map-weather-place]', municipalityName);
            updateMapWeatherText('[data-map-weather-condition]', 'Weather unavailable');
            updateMapWeatherText('[data-map-weather-temp]', '--');
            updateMapWeatherText('[data-map-weather-rain]', 'Rain --');
            updateMapWeatherText('[data-map-weather-humidity]', 'Humidity --');
            updateMapWeatherText('[data-map-weather-wind]', 'Wind --');
            updateMapWeatherText('[data-map-weather-updated]', 'Map and crop data are still available.');
            setMapWeatherStatus(message || 'Unable to load weather for this municipality.');
            renderWeatherMapMarker(municipalityName, null, 'error');
        }

        function renderMapWeatherData(municipalityName, weatherPayload, hasErrors) {
            const current = weatherPayload?.current || null;

            if (!current) {
                setMapWeatherError(municipalityName, 'Current conditions are unavailable.');
                return;
            }

            selectedWeatherMunicipality = municipalityName;
            const staleMap = weatherPayload?.metadata?.stale || {};
            const hasStaleData = Object.values(staleMap).some(Boolean);
            const segmentErrors = Object.values(weatherPayload?.errors || {}).filter(Boolean);

            setMapWeatherCardClass('ready', hasStaleData);
            updateMapWeatherAction(true);
            setMapWeatherIcon(current.icon);
            updateMapWeatherText('[data-map-weather-place]', municipalityName);
            updateMapWeatherText('[data-map-weather-condition]', current.condition_text || 'Current conditions');
            updateMapWeatherText('[data-map-weather-temp]', formatTemperature(current.temperature_c));
            updateMapWeatherText('[data-map-weather-rain]', `Rain ${formatPercent(current.precipitation_probability_percent)}`);
            updateMapWeatherText('[data-map-weather-humidity]', `Humidity ${formatPercent(current.humidity_percent)}`);
            updateMapWeatherText('[data-map-weather-wind]', `Wind ${formatWind(current.wind_speed_kph)}`);
            updateMapWeatherText('[data-map-weather-updated]', `Updated ${formatClock(current.timestamp)}`);
            setMapWeatherStatus(segmentErrors[0] || (hasErrors ? 'Some forecast data is temporarily unavailable.' : ''));
            renderWeatherMapMarker(municipalityName, current, 'ready');
        }

        function formatWeatherIconUrl(icon) {
            if (!icon) return '';

            const url = String(icon).trim();
            if (!url) return '';

            if (/\.(svg|png|jpg|jpeg|webp)(\?.*)?$/i.test(url)) {
                return url;
            }

            return `${url}.svg`;
        }

        function renderWeatherIconHtml(icon, fallbackClass) {
            const iconUrl = formatWeatherIconUrl(icon);

            if (iconUrl) {
                return `<img src="${escapeHtml(iconUrl)}" alt="" loading="lazy">`;
            }

            return `<span class="${fallbackClass}"></span>`;
        }

        function removeWeatherMapMarker() {
            if (mapWeatherMarker) {
                map.removeLayer(mapWeatherMarker);
                mapWeatherMarker = null;
            }
        }

        function renderWeatherMapMarker(municipalityName, current, state = 'ready') {
            if (!map || !municipalityCoords[municipalityName]) {
                return;
            }

            removeWeatherMapMarker();

            const isLoading = state === 'loading';
            const isError = state === 'error';
            const markerHtml = `
                <div class="weather-map-marker is-${state}">
                    <div class="weather-map-marker__icon">
                        ${renderWeatherIconHtml(current?.icon, 'weather-map-marker__dot')}
                    </div>
                    <div class="weather-map-marker__copy">
                        <div class="weather-map-marker__temp">${escapeHtml(isLoading || isError ? '--' : formatTemperature(current?.temperature_c))}</div>
                        <div class="weather-map-marker__condition">${escapeHtml(isLoading ? 'Loading' : (isError ? 'Unavailable' : (current?.condition_text || 'Weather')))}</div>
                    </div>
                </div>
            `;

            mapWeatherMarker = L.marker(municipalityCoords[municipalityName], {
                icon: L.divIcon({
                    className: '',
                    html: markerHtml,
                    iconSize: [145, 46],
                    iconAnchor: [72, 48],
                    popupAnchor: [0, -44]
                }),
                zIndexOffset: 1200
            }).addTo(map);

            mapWeatherMarker.on('click', () => loadMunicipalityDetails(municipalityName));
        }

        async function fetchMunicipalityWeatherPayload(municipalityName, hours = 24, days = 7) {
            const cacheKey = `${normalizeMunicipalityName(municipalityName)}:${hours}:${days}`;

            if (weatherPayloadCache.has(cacheKey)) {
                return weatherPayloadCache.get(cacheKey);
            }

            const requestPromise = (async () => {
                const weatherParams = new URLSearchParams({
                    hours: String(hours),
                    days: String(days)
                });
                const response = await fetch(`${apiBase}/weather/${encodeURIComponent(municipalityName)}?${weatherParams}`);
                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Weather lookup failed.');
                }

                return payload;
            })();

            weatherPayloadCache.set(cacheKey, requestPromise);

            try {
                return await requestPromise;
            } catch (error) {
                weatherPayloadCache.delete(cacheKey);
                throw error;
            }
        }

        async function loadMapWeatherSummary(municipalityName) {
            const requestToken = ++mapWeatherRequestToken;
            setMapWeatherLoading(municipalityName);

            try {
                const payload = await fetchMunicipalityWeatherPayload(municipalityName, 24, 7);

                if (requestToken !== mapWeatherRequestToken) {
                    return;
                }

                renderMapWeatherData(payload.municipality || municipalityName, payload.weather, payload.has_errors);
            } catch (error) {
                if (requestToken !== mapWeatherRequestToken) {
                    return;
                }

                setMapWeatherError(municipalityName, error.message);
            }
        }

        // Load filter options from API
        async function loadFilters() {
            try {
                clearMapError();
                console.log('Fetching filters from:', `${apiBase}/filters`);
                const response = await fetch(`${apiBase}/filters`);
                if (!response.ok) {
                    throw new Error(`Request failed (${response.status})`);
                }

                console.log('Filter response:', response);
                filterOptions = await response.json();
                console.log('Filter options loaded:', filterOptions);

                // Populate crop dropdown
                const cropSelect = document.getElementById('crop-filter');
                cropSelect.innerHTML = '<option value="">All Crops</option>';
                filterOptions.crops.forEach(crop => {
                    cropSelect.innerHTML += `<option value="${crop}">${crop}</option>`;
                });

                // Auto-select user's favorite crop if available
                if (userFavoriteCrops.length > 0) {
                    const favoriteCropUpper = userFavoriteCrops[0].toUpperCase();
                    const matchingCrop = filterOptions.crops.find(c => c.toUpperCase() === favoriteCropUpper);
                    if (matchingCrop) {
                        cropSelect.value = matchingCrop;
                    }
                }

                // Populate year dropdown
                const yearSelect = document.getElementById('year-filter');
                yearSelect.innerHTML = '<option value="">All Years</option>';
                filterOptions.years.forEach(year => {
                    yearSelect.innerHTML += `<option value="${year}">${year}</option>`;
                });

                // Real-time supply uses the current season when year is left blank.
                yearSelect.value = document.getElementById('view-filter').value === 'supply_forecast'
                    ? ''
                    : filterOptions.years[filterOptions.years.length - 1];

                // Load initial map data
                loadMapData();
            } catch (error) {
                console.error('Error loading filters:', error);
                showMapError('Could not load choices.');
                document.getElementById('crop-filter').innerHTML = '<option value="">Could not load crops</option>';
                document.getElementById('year-filter').innerHTML = '<option value="">Could not load years</option>';
            }
        }

        // Load map data from API
        async function loadMapData() {
            const crop = document.getElementById('crop-filter').value;
            const year = document.getElementById('year-filter').value;
            const view = document.getElementById('view-filter').value;
            const farmType = document.getElementById('farm-type-filter').value;

            // Show loading
            clearMapError();
            document.getElementById('loading-indicator').classList.remove('hidden');

            try {
                const params = new URLSearchParams();
                if (crop) params.append('crop', crop);
                if (year) params.append('year', year);
                if (view) params.append('view', view);
                if (farmType) params.append('farm_type', farmType);

                const response = await fetch(`${apiBase}/data?${params}`);
                if (!response.ok) {
                    throw new Error(`Request failed (${response.status})`);
                }

                const data = await response.json();

                currentData = data;
                updateMap(data);
                updateStats(data);
            } catch (error) {
                console.error('Error loading map data:', error);
                showMapError('Could not load map.');
            } finally {
                document.getElementById('loading-indicator').classList.add('hidden');
            }
        }

        // Update map with one pinpoint marker per municipality.
        function updateMap(data) {
            if (markerLayer) {
                map.removeLayer(markerLayer);
            }

            markerLayer = L.layerGroup().addTo(map);
            const markerBounds = [];

            Object.entries(municipalityCoords).forEach(([municipalityName, coords]) => {
                const municipalityData = findMunicipalityData(data, municipalityName);
                const marker = L.marker(coords, {
                    icon: createPinpointIcon(municipalityName, municipalityData)
                });

                marker.bindPopup(buildMarkerPopup(municipalityName, municipalityData));
                marker.on('click', () => loadMunicipalityDetails(municipalityName));
                marker.addTo(markerLayer);
                markerBounds.push(coords);
            });

            if (markerBounds.length > 0 && !userPreferredMunicipality) {
                map.fitBounds(markerBounds, { padding: [28, 28] });
            }
        }

        function findMunicipalityData(data, municipalityName) {
            return (data.data || []).find(d =>
                normalizeMunicipalityName(d.municipality) === normalizeMunicipalityName(municipalityName)
            );
        }

        function getFarmerCountForMunicipality(data, municipalityName) {
            const productionRow = findMunicipalityData(data, municipalityName);
            if (productionRow && productionRow.farmer_count !== undefined) {
                return Number(productionRow.farmer_count) || 0;
            }

            const countRow = (data.farmer_counts || []).find(row =>
                normalizeMunicipalityName(row.municipality) === normalizeMunicipalityName(municipalityName)
                || normalizeMunicipalityName(row.normalized_municipality) === normalizeMunicipalityName(municipalityName)
            );

            return Number(countRow?.farmer_count) || 0;
        }

        function createPinpointIcon(municipalityName, municipalityData) {
            const isPreferred = userPreferredMunicipality &&
                normalizeMunicipalityName(municipalityName) === normalizeMunicipalityName(userPreferredMunicipality);
            const classes = [
                'map-pinpoint',
                municipalityData ? '' : 'no-data',
                isPreferred ? 'preferred' : ''
            ].filter(Boolean).join(' ');

            return L.divIcon({
                className: '',
                html: `<div class="${classes}"></div>`,
                iconSize: [25, 25],
                iconAnchor: [12, 25],
                popupAnchor: [0, -24]
            });
        }

        function buildMarkerPopup(municipalityName, municipalityData) {
            const farmerCount = getFarmerCountForMunicipality(currentData, municipalityName);
            const farmerLabel = farmerCount === 1 ? 'farmer' : 'farmers';

            if (municipalityData) {
                const viewType = document.getElementById('view-filter').value;
                const unit = getUnit(viewType);
                return `
                    <div class="border-b border-gray-100 pb-1.5 sm:pb-2 mb-1.5 sm:mb-2 pr-5 sm:pr-6">
                        <h4 class="font-bold text-gray-800 text-sm sm:text-base m-0">${municipalityName}</h4>
                    </div>
                    <p class="text-[9px] sm:text-[10px] text-gray-500 mb-0.5 uppercase tracking-wider font-semibold">${getViewLabel(viewType)}</p>
                    <p class="text-lg sm:text-xl font-bold text-green-600 m-0 leading-none">${Number(municipalityData.value).toLocaleString()} <span class="text-[10px] sm:text-xs font-medium text-gray-500 ml-0.5">${unit}</span></p>
                    <p class="mt-2 text-[10px] sm:text-xs font-semibold text-emerald-700">${farmerCount.toLocaleString()} ${farmerLabel}</p>
                `;
            }

            return `
                <div class="border-b border-gray-100 pb-1.5 sm:pb-2 mb-1.5 sm:mb-2 pr-5 sm:pr-6">
                    <h4 class="font-bold text-gray-800 text-sm sm:text-base m-0">${municipalityName}</h4>
                </div>
                <p class="text-xs sm:text-sm font-medium text-gray-500 m-0">No records here yet</p>
                <p class="mt-2 text-[10px] sm:text-xs font-semibold text-emerald-700">${farmerCount.toLocaleString()} ${farmerLabel}</p>
            `;
        }

        // Update statistics
        function updateStats(data) {
            const viewType = document.getElementById('view-filter').value;
            const unit = getUnit(viewType);

            if (data.metadata) {
                const total = Number(data.metadata.total) || 0;
                const avg = Number(data.metadata.avg) || 0;  // Backend sends 'avg', not 'average'
                const max = Number(data.metadata.max) || 0;
                const min = Number(data.metadata.min) || 0;

                document.getElementById('stat-total').textContent = total.toLocaleString() + ' ' + unit;
                document.getElementById('stat-avg').textContent = avg.toLocaleString() + ' ' + unit;
                document.getElementById('stat-max').textContent = max.toLocaleString() + ' ' + unit;
                document.getElementById('stat-min').textContent = min.toLocaleString() + ' ' + unit;
            } else {
                document.getElementById('stat-total').textContent = '-';
                document.getElementById('stat-avg').textContent = '-';
                document.getElementById('stat-max').textContent = '-';
                document.getElementById('stat-min').textContent = '-';
            }
        }

        // Get unit label
        function getUnit(viewType) {
            switch (viewType) {
                case 'supply_forecast': return 'mt';
                case 'production': return 'mt';
                case 'area_harvested': return 'ha';
                case 'productivity': return 'mt/ha';
                default: return '';
            }
        }

        // Get view label
        function getViewLabel(viewType) {
            switch (viewType) {
                case 'supply_forecast': return 'Expected Supply';
                case 'production': return 'Past Production';
                case 'area_harvested': return 'Area Harvested';
                case 'productivity': return 'Productivity';
                default: return 'Value';
            }
        }

        // Contribution Chart
        let contributionChart = null;

        function updateContributionChart(municipalityName) {
            const section = document.getElementById('contribution-section');
            const crop = document.getElementById('crop-filter').value;
            const viewType = document.getElementById('view-filter').value;
            const unit = getUnit(viewType);

            // Only show when a specific crop is selected and we have map data
            if (!crop || !currentData.data || currentData.data.length === 0) {
                section.classList.add('hidden');
                return;
            }

            const allMunicipalities = currentData.data.filter(d => d.value > 0);
            if (allMunicipalities.length === 0) {
                section.classList.add('hidden');
                return;
            }

            section.classList.remove('hidden');
            document.getElementById('contribution-crop-badge').textContent = crop;

            // Find the selected municipality's value
            const selected = allMunicipalities.find(d => normalizeMunicipalityName(d.municipality) === normalizeMunicipalityName(municipalityName));
            const selectedValue = selected ? selected.value : 0;
            const total = allMunicipalities.reduce((sum, d) => sum + d.value, 0);
            const othersValue = total - selectedValue;
            const selectedPercentage = total > 0 ? ((selectedValue / total) * 100).toFixed(1) : '0.0';
            const othersPercentage = total > 0 ? ((othersValue / total) * 100).toFixed(1) : '0.0';

            // Update details text
            const detailsContainer = document.getElementById('contribution-details');
            detailsContainer.innerHTML = `
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: #16a34a"></div>
                        <span class="text-sm font-medium text-slate-700">${municipalityName}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-green-700">${selectedPercentage}%</span>
                        <span class="text-xs text-slate-500 ml-1">(${Number(selectedValue).toLocaleString()} ${unit})</span>
                    </div>
                </div>
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: #cbd5e1"></div>
                        <span class="text-sm font-medium text-slate-700">Other Municipalities</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-slate-800">${othersPercentage}%</span>
                        <span class="text-xs text-slate-500 ml-1">(${Number(othersValue).toLocaleString()} ${unit})</span>
                    </div>
                </div>
            `;

            // Destroy existing chart
            if (contributionChart) {
                contributionChart.destroy();
            }

            const ctx = document.getElementById('contribution-chart');

            contributionChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: [municipalityName, 'Other Municipalities'],
                    datasets: [{
                        data: [selectedValue, othersValue],
                        backgroundColor: ['#16a34a', '#cbd5e1'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = Number(context.parsed).toLocaleString();
                                    const pct = ((context.parsed / total) * 100).toFixed(1);
                                    return `${label}: ${value} ${unit} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Municipality Details Panel Functions
        let cropChart = null;

        function closeDetailsPanel() {
            document.getElementById('details-panel').classList.add('translate-x-full');
            currentMunicipality = null;
            detailsRequestToken += 1;
        }

        function openDetailsPanel() {
            document.getElementById('details-panel').classList.remove('translate-x-full');
        }

        function resetWeatherPanel() {
            // Weather forecast details now live in the map popup, not in the side panel.
        }

        function formatTemperature(value) {
            if (value === null || value === undefined || value === '') return '-';
            return `${Math.round(Number(value))}\u00B0C`;
        }

        function formatPercent(value) {
            if (value === null || value === undefined || value === '') return '-';
            return `${Number(value).toFixed(0)}%`;
        }

        function formatWind(value) {
            if (value === null || value === undefined || value === '') return '-';
            return `${Number(value).toFixed(1)} kph`;
        }

        function formatClock(value) {
            if (!value) return '-';
            const parsed = new Date(value);
            if (Number.isNaN(parsed.getTime())) return '-';
            return parsed.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        }

        function updateFarmerCountCard(municipalityName, farmerCount) {
            const count = Number(farmerCount) || 0;
            document.getElementById('panel-farmer-count').textContent = count.toLocaleString();
            document.getElementById('panel-farmer-count-label').textContent = count === 1 ? 'farmer' : 'farmers';
        }

        function formatMetricTons(value) {
            const amount = Number(value) || 0;
            return `${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} mt`;
        }

        function renderProductionOutlookLegacy(outlook) {
            const listEl = document.getElementById('production-outlook-list');
            const countEl = document.getElementById('production-outlook-count');
            const rows = Array.isArray(outlook) ? outlook : [];

            countEl.textContent = `${rows.length} ${rows.length === 1 ? 'crop' : 'crops'}`;

            if (rows.length === 0) {
                listEl.innerHTML = '<p class="text-sm text-gray-500">No crop plans here yet.</p>';
                return;
            }

            listEl.innerHTML = rows.map(row => `
                <div class="rounded-lg border border-orange-100 bg-white p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 break-words">${escapeHtml(row.crop || 'Crop')}</p>
                            <p class="mt-0.5 text-[11px] text-gray-500">${Number(row.plan_count || 0).toLocaleString()} ${Number(row.plan_count || 0) === 1 ? 'plan' : 'plans'} · ${Number(row.harvested_count || 0).toLocaleString()} harvested</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">${formatMetricTons(row.supply_forecast_mt ?? row.net_expected_production_mt)}</span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded bg-orange-50 px-2 py-1.5">
                            <p class="font-semibold text-orange-700">Expected</p>
                            <p class="text-gray-900">${formatMetricTons(row.predicted_production_mt)}</p>
                        </div>
                        <div class="rounded bg-green-50 px-2 py-1.5">
                            <p class="font-semibold text-green-700">Harvested</p>
                            <p class="text-gray-900">${formatMetricTons(row.harvested_production_mt)}</p>
                        </div>
                        <div class="rounded bg-red-50 px-2 py-1.5">
                            <p class="font-semibold text-red-700">Damaged</p>
                            <p class="text-gray-900">${formatMetricTons(row.damaged_production_mt)}</p>
                        </div>
                        <div class="rounded bg-emerald-50 px-2 py-1.5">
                            <p class="font-semibold text-emerald-700">Remaining</p>
                            <p class="text-gray-900">${formatMetricTons(row.net_expected_production_mt)}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function toSafeNumber(value) {
            const amount = Number(value);
            return Number.isFinite(amount) ? amount : 0;
        }

        function getOutlookSupplyValue(row) {
            return toSafeNumber(row?.supply_forecast_mt ?? row?.net_expected_production_mt ?? row?.predicted_production_mt);
        }

        function getSortedOutlookRows(outlook) {
            return (Array.isArray(outlook) ? outlook : [])
                .slice()
                .sort((a, b) => {
                    const planDiff = toSafeNumber(b.plan_count) - toSafeNumber(a.plan_count);
                    if (planDiff !== 0) return planDiff;
                    return getOutlookSupplyValue(b) - getOutlookSupplyValue(a);
                });
        }

        function getSelectedCropName() {
            return document.getElementById('crop-filter')?.value || '';
        }

        function renderPlantingSignal(outlook) {
            const cardEl = document.getElementById('planting-signal-card');
            const badgeEl = document.getElementById('planting-signal-badge');
            const titleEl = document.getElementById('planting-signal-title');
            const messageEl = document.getElementById('planting-signal-message');
            const alternativesEl = document.getElementById('planting-signal-alternatives');
            const rows = getSortedOutlookRows(outlook);
            const selectedCrop = getSelectedCropName();

            cardEl.className = 'pwa-panel-section pwa-panel-section-primary';
            badgeEl.className = 'pwa-status-pill';
            alternativesEl.innerHTML = '';

            if (rows.length === 0) {
                badgeEl.textContent = 'Open';
                titleEl.textContent = 'No crop crowding yet';
                messageEl.textContent = 'You can plan based on your field and weather.';
                return;
            }

            const selectedRow = selectedCrop
                ? rows.find(row => String(row.crop || '').toLowerCase() === selectedCrop.toLowerCase())
                : null;
            const mainRow = selectedRow || rows[0];
            const cropName = mainRow?.crop || 'this crop';
            const planCount = toSafeNumber(mainRow?.plan_count);
            const maxPlanCount = Math.max(...rows.map(row => toSafeNumber(row.plan_count)));
            const isCrowded = planCount > 1 && planCount >= maxPlanCount;
            const hasDamage = rows.some(row => toSafeNumber(row.damaged_production_mt) > 0);

            if (selectedRow) {
                if (isCrowded) {
                    cardEl.classList.add('is-warning');
                    badgeEl.classList.add('is-warning');
                    badgeEl.textContent = 'Compare';
                    titleEl.textContent = `${cropName} is popular here`;
                    messageEl.textContent = 'Check another crop before you plant the same one.';
                } else {
                    badgeEl.textContent = 'Looks okay';
                    titleEl.textContent = `${cropName} still looks okay`;
                    messageEl.textContent = 'Keep checking the supply before planting.';
                }
            } else {
                if (isCrowded) {
                    cardEl.classList.add('is-warning');
                    badgeEl.classList.add('is-warning');
                }
                badgeEl.textContent = isCrowded ? 'Watch' : 'Guide';
                titleEl.textContent = `Most farmers here plant ${cropName}`;
                messageEl.textContent = isCrowded
                    ? 'If you plant the same crop, check the supply first.'
                    : 'Use this as a guide before choosing your crop.';
            }

            const alternatives = rows
                .filter(row => String(row.crop || '') !== String(cropName))
                .slice(0, 3);

            if (alternatives.length > 0) {
                alternativesEl.innerHTML = alternatives.map(row => `
                    <span class="pwa-soft-pill">
                        Try ${escapeHtml(row.crop || 'crop')}
                    </span>
                `).join('');
            } else if (hasDamage) {
                alternativesEl.innerHTML = '<span class="pwa-status-pill is-warning">Damage reported here</span>';
            }
        }

        function renderProductionOutlook(outlook) {
            const listEl = document.getElementById('production-outlook-list');
            const countEl = document.getElementById('production-outlook-count');
            const rows = getSortedOutlookRows(outlook);

            countEl.textContent = `${rows.length} ${rows.length === 1 ? 'crop' : 'crops'}`;

            if (rows.length === 0) {
                listEl.innerHTML = '<p class="text-sm text-slate-500">No crop plans here yet.</p>';
                return;
            }

            const visibleRows = rows.slice(0, 3);
            const hiddenCount = Math.max(rows.length - visibleRows.length, 0);

            listEl.innerHTML = visibleRows.map(row => {
                const planCount = toSafeNumber(row.plan_count);
                const harvestedCount = toSafeNumber(row.harvested_count);
                const damaged = toSafeNumber(row.damaged_production_mt);
                const remaining = toSafeNumber(row.net_expected_production_mt);

                return `
                    <div class="pwa-crop-row">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-950 break-words">${escapeHtml(row.crop || 'Crop')}</p>
                                <p class="mt-0.5 text-[11px] text-slate-500">${planCount.toLocaleString()} ${planCount === 1 ? 'plan' : 'plans'} recorded here</p>
                            </div>
                            <span class="pwa-status-pill">${formatMetricTons(getOutlookSupplyValue(row))}</span>
                        </div>
                        <div class="pwa-crop-meta">
                            <span>Expected ${formatMetricTons(row.predicted_production_mt)}</span>
                            <span>Harvested ${harvestedCount.toLocaleString()}</span>
                            <span>Still coming ${formatMetricTons(remaining)}</span>
                            ${damaged > 0 ? `<span class="is-danger">Damaged ${formatMetricTons(damaged)}</span>` : ''}
                        </div>
                    </div>
                `;
            }).join('') + (hiddenCount > 0
                ? `<p class="pt-1 text-xs text-slate-500">+${hiddenCount} more ${hiddenCount === 1 ? 'crop' : 'crops'}. Use the crop filter to check one crop.</p>`
                : '');
        }

        function formatDay(value) {
            if (!value) return '--';
            const parsed = new Date(value);
            if (Number.isNaN(parsed.getTime())) return String(value);
            return parsed.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            })[char]);
        }

        function renderWeatherAdvice(current, titleId = 'weather-modal-advice-title', messageId = 'weather-modal-advice-message') {
            const rainChance = toSafeNumber(current?.precipitation_probability_percent);
            const windSpeed = toSafeNumber(current?.wind_speed_kph);
            const condition = String(current?.condition_text || '').toLowerCase();
            const titleEl = document.getElementById(titleId);
            const messageEl = document.getElementById(messageId);

            if (!titleEl || !messageEl) {
                return;
            }

            if (rainChance >= 60 || condition.includes('rain')) {
                titleEl.textContent = 'Rain is likely';
                messageEl.textContent = 'Avoid spraying today. Check drainage if you just planted.';
                return;
            }

            if (windSpeed >= 25) {
                titleEl.textContent = 'It may be windy';
                messageEl.textContent = 'Be careful with spraying and field work.';
                return;
            }

            if (!current || Object.keys(current).length === 0) {
                titleEl.textContent = 'Weather is unavailable';
                messageEl.textContent = 'Check the weather before field work.';
                return;
            }

            titleEl.textContent = 'Weather looks workable';
            messageEl.textContent = 'You can plan field work, but still check your area.';
        }

        function initWeatherForecastModal() {
            const modal = document.getElementById('weather-forecast-modal');
            if (!modal) return;

            modal.querySelectorAll('[data-weather-modal-close]').forEach((button) => {
                button.addEventListener('click', closeWeatherForecastModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) {
                    closeWeatherForecastModal();
                }
            });
        }

        function closeWeatherForecastModal() {
            const modal = document.getElementById('weather-forecast-modal');
            if (!modal) return;

            weatherForecastRequestToken += 1;
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function resetWeatherForecastModal(municipalityName) {
            const modal = document.getElementById('weather-forecast-modal');
            if (!modal) return;

            document.getElementById('weather-modal-title').textContent = `${municipalityName} forecast`;
            document.getElementById('weather-modal-subtitle').textContent = 'Loading weather for field work.';
            document.getElementById('weather-modal-loading').classList.remove('hidden');
            document.getElementById('weather-modal-error').classList.add('hidden');
            document.getElementById('weather-modal-error').textContent = '';
            document.getElementById('weather-modal-content').classList.add('hidden');
            document.getElementById('weather-modal-source-badge').classList.add('hidden');
            document.getElementById('weather-modal-icon').innerHTML = '<span class="map-weather-card__icon-fallback"></span>';
            document.getElementById('weather-modal-place').textContent = municipalityName;
            document.getElementById('weather-modal-condition').textContent = '-';
            document.getElementById('weather-modal-temp').textContent = '-';
            document.getElementById('weather-modal-rain').textContent = '-';
            document.getElementById('weather-modal-humidity').textContent = '-';
            document.getElementById('weather-modal-wind').textContent = '-';
            document.getElementById('weather-modal-updated').textContent = '-';
            document.getElementById('weather-modal-hourly-count').textContent = '';
            document.getElementById('weather-modal-daily-count').textContent = '';
            document.getElementById('weather-modal-hourly-list').innerHTML = '';
            document.getElementById('weather-modal-daily-list').innerHTML = '';
            renderWeatherAdvice(null);
        }

        function showWeatherForecastModalError(message) {
            document.getElementById('weather-modal-loading').classList.add('hidden');
            document.getElementById('weather-modal-content').classList.add('hidden');
            const errorEl = document.getElementById('weather-modal-error');
            errorEl.textContent = message || 'Weather forecast is unavailable.';
            errorEl.classList.remove('hidden');
        }

        function renderWeatherForecastModal(municipalityName, weatherPayload, hasErrors) {
            const current = weatherPayload?.current || {};
            const staleMap = weatherPayload?.metadata?.stale || {};
            const segmentErrors = Object.values(weatherPayload?.errors || {}).filter(Boolean);
            const hasStaleData = Object.values(staleMap).some(Boolean);

            document.getElementById('weather-modal-loading').classList.add('hidden');
            document.getElementById('weather-modal-title').textContent = `${municipalityName} forecast`;
            document.getElementById('weather-modal-subtitle').textContent = segmentErrors[0] || (hasErrors ? 'Some forecast details may be missing.' : 'Use this before planting, spraying, or harvesting.');
            document.getElementById('weather-modal-source-badge').classList.toggle('hidden', !hasStaleData);
            document.getElementById('weather-modal-icon').innerHTML = renderWeatherIconHtml(current.icon, 'map-weather-card__icon-fallback');
            document.getElementById('weather-modal-place').textContent = municipalityName;
            document.getElementById('weather-modal-condition').textContent = current.condition_text || 'Unavailable';
            document.getElementById('weather-modal-temp').textContent = formatTemperature(current.temperature_c);
            document.getElementById('weather-modal-rain').textContent = formatPercent(current.precipitation_probability_percent);
            document.getElementById('weather-modal-humidity').textContent = formatPercent(current.humidity_percent);
            document.getElementById('weather-modal-wind').textContent = formatWind(current.wind_speed_kph);
            document.getElementById('weather-modal-updated').textContent = formatClock(current.timestamp);
            renderWeatherAdvice(current);

            const hourlyItems = (weatherPayload?.hourly?.items || []).slice(0, 8);
            document.getElementById('weather-modal-hourly-count').textContent = `${hourlyItems.length} checks`;
            document.getElementById('weather-modal-hourly-list').innerHTML = hourlyItems.length === 0
                ? '<p class="text-xs text-slate-500">No hourly weather yet.</p>'
                : hourlyItems.map(item => `
                    <div class="pwa-forecast-card">
                        <p class="text-[11px] font-semibold text-slate-700">${escapeHtml(formatClock(item.timestamp))}</p>
                        <p class="my-1 text-xs font-bold text-green-700">${escapeHtml(formatTemperature(item.temperature_c))}</p>
                        <p class="w-full truncate text-[10px] text-slate-600" title="${escapeHtml(item.condition_text || 'N/A')}">${escapeHtml(item.condition_text || 'N/A')}</p>
                        <p class="mt-1 text-[10px] text-slate-500">Rain ${escapeHtml(formatPercent(item.precipitation_probability_percent))}</p>
                    </div>
                `).join('');

            const dailyItems = (weatherPayload?.daily?.items || []).slice(0, 5);
            document.getElementById('weather-modal-daily-count').textContent = `${dailyItems.length} days`;
            document.getElementById('weather-modal-daily-list').innerHTML = dailyItems.length === 0
                ? '<p class="text-xs text-slate-500">No daily weather yet.</p>'
                : dailyItems.map(item => `
                    <div class="pwa-forecast-card">
                        <p class="text-[11px] font-bold text-slate-800">${escapeHtml(formatDay(item.date))}</p>
                        <p class="mb-1 w-full truncate text-[10px] text-slate-500" title="${escapeHtml(item.condition_text || 'N/A')}">${escapeHtml(item.condition_text || 'N/A')}</p>
                        <div class="mb-1 w-full rounded-md border border-slate-200 bg-white px-2 py-1">
                            <p class="text-[11px] font-semibold text-slate-800">${escapeHtml(formatTemperature(item.temp_max_c))}</p>
                            <p class="text-[9px] text-slate-400">${escapeHtml(formatTemperature(item.temp_min_c))}</p>
                        </div>
                        <p class="text-[10px] text-slate-500">Rain ${escapeHtml(formatPercent(item.precipitation_probability_percent))}</p>
                    </div>
                `).join('');

            document.getElementById('weather-modal-content').classList.remove('hidden');
        }

        async function openWeatherForecastModal(municipalityName) {
            if (!municipalityName) return;

            const modal = document.getElementById('weather-forecast-modal');
            if (!modal) return;

            const requestToken = ++weatherForecastRequestToken;
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
            resetWeatherForecastModal(municipalityName);

            try {
                const payload = await fetchMunicipalityWeatherPayload(municipalityName, 24, 7);

                if (requestToken !== weatherForecastRequestToken) {
                    return;
                }

                renderWeatherForecastModal(payload.municipality || municipalityName, payload.weather, payload.has_errors);
            } catch (error) {
                if (requestToken !== weatherForecastRequestToken) {
                    return;
                }

                showWeatherForecastModalError(`Weather unavailable: ${error.message}`);
            }
        }

        async function loadMunicipalityDetails(municipalityName) {
            console.log('Loading details for:', municipalityName);

            currentMunicipality = municipalityName;
            const requestToken = ++detailsRequestToken;

            // Show panel
            openDetailsPanel();

            // Update header
            document.getElementById('panel-municipality-name').textContent = municipalityName;
            updateFarmerCountCard(municipalityName, getFarmerCountForMunicipality(currentData, municipalityName));
            renderPlantingSignal([]);
            renderProductionOutlook([]);

            // Show loading
            clearPanelError();
            document.getElementById('panel-loading').classList.remove('hidden');
            document.getElementById('panel-content').classList.add('hidden');

            const weatherPromise = loadMapWeatherSummary(municipalityName);

            try {
                const crop = document.getElementById('crop-filter').value;
                const year = document.getElementById('year-filter').value;
                const farmType = document.getElementById('farm-type-filter').value;

                const params = new URLSearchParams();
                if (crop) params.append('crop', crop);
                if (year) params.append('year', year);
                if (farmType) params.append('farm_type', farmType);

                const response = await fetch(`${apiBase}/municipality/${encodeURIComponent(municipalityName)}?${params}`);
                if (!response.ok) {
                    throw new Error(`Request failed (${response.status})`);
                }

                const data = await response.json();

                if (requestToken !== detailsRequestToken) {
                    return;
                }

                console.log('Municipality data:', data);

                updateFarmerCountCard(
                    municipalityName,
                    data.summary?.farmer_count ?? getFarmerCountForMunicipality(currentData, municipalityName)
                );
                renderPlantingSignal(data.production_outlook);
                renderProductionOutlook(data.production_outlook);

                // Update charts
                updateContributionChart(municipalityName);
                updateCropChart(data.crop_distribution);

                // Hide loading, show content
                document.getElementById('panel-loading').classList.add('hidden');
                document.getElementById('panel-content').classList.remove('hidden');

                weatherPromise.catch(() => {
                    // Weather errors stay in the compact map card and do not block crop data.
                });

            } catch (error) {
                if (requestToken !== detailsRequestToken) {
                    return;
                }

                console.error('Error loading municipality details:', error);
                showPanelError('Could not load this town.');
            }
        }

        function renderCropListSummary(cropData) {
            const listEl = document.getElementById('crop-list-summary');
            const countEl = document.getElementById('crop-summary-count');
            const rows = (Array.isArray(cropData) ? cropData : [])
                .slice()
                .sort((a, b) => toSafeNumber(b.total_production) - toSafeNumber(a.total_production));

            countEl.textContent = `${rows.length} ${rows.length === 1 ? 'crop' : 'crops'}`;

            if (rows.length === 0) {
                listEl.innerHTML = '<p class="rounded-lg bg-slate-50 p-3 text-sm text-slate-500">No crop records here yet.</p>';
                return;
            }

            const totalProduction = rows.reduce((sum, row) => sum + toSafeNumber(row.total_production), 0);
            const visibleRows = rows.slice(0, 5);

            listEl.innerHTML = visibleRows.map(row => {
                const value = toSafeNumber(row.total_production);
                const percent = totalProduction > 0 ? Math.round((value / totalProduction) * 100) : 0;

                return `
                    <div class="pwa-crop-row">
                        <div class="flex items-center justify-between gap-3">
                            <p class="min-w-0 truncate text-sm font-semibold text-slate-950">${escapeHtml(row.crop || 'Crop')}</p>
                            <span class="pwa-status-pill">${percent}%</span>
                        </div>
                        <div class="pwa-progress-track mt-2">
                            <div class="pwa-progress-fill" style="width: ${Math.max(percent, 4)}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">${formatMetricTons(value)} recorded</p>
                    </div>
                `;
            }).join('');
        }

        function updateCropChart(cropData) {
            const ctx = document.getElementById('crop-chart');
            if (!ctx) return;

            renderCropListSummary(cropData);
            const container = ctx.parentElement;
            const emptyState = container.querySelector('[data-crop-chart-empty]');

            // Destroy existing chart
            if (cropChart) {
                cropChart.destroy();
                cropChart = null;
            }

            if (!Array.isArray(cropData) || cropData.length === 0) {
                ctx.classList.add('hidden');
                if (!emptyState) {
                    container.insertAdjacentHTML('beforeend',
                        '<p data-crop-chart-empty class="text-sm text-slate-500 text-center py-8">No crop records here yet.</p>');
                }
                return;
            }

            ctx.classList.remove('hidden');
            emptyState?.remove();

            const colors = [
                '#16a34a', '#65a30d', '#0f766e', '#64748b', '#d97706',
                '#15803d', '#475569', '#84cc16', '#0d9488', '#94a3b8'
            ];

            cropChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: cropData.map(c => c.crop),
                    datasets: [{
                        data: cropData.map(c => parseFloat(c.total_production)),
                        backgroundColor: colors.slice(0, cropData.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = Number(context.parsed).toLocaleString();
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return `${label}: ${value} mt (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Handle filter change: update map + refresh side panel if open
        function onFilterChange() {
            loadMapData();
            if (currentMunicipality) {
                loadMunicipalityDetails(currentMunicipality);
            }
        }

        // Event listeners
        document.getElementById('crop-filter').addEventListener('change', onFilterChange);
        document.getElementById('year-filter').addEventListener('change', onFilterChange);
        document.getElementById('view-filter').addEventListener('change', onFilterChange);
        document.getElementById('farm-type-filter').addEventListener('change', onFilterChange);

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</x-app-layout>

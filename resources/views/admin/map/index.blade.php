<x-admin-layout>
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
        .pwa-metric-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.65rem;
        }
        .pwa-metric-cell,
        .pwa-crop-row,
        .pwa-forecast-card {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 0.75rem;
        }
        .pwa-metric-cell {
            background: #f8fafc;
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
        .pwa-weather-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.75rem;
            align-items: start;
        }
        .pwa-weather-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.45rem 0.75rem;
            margin-top: 0.75rem;
            font-size: 0.74rem;
            color: #64748b;
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
            background: #f8fafc;
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
    </style>
    <div class="py-4 lg:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-full mx-auto">
            <div class="flex flex-col-reverse lg:flex-col gap-4 lg:gap-6 mb-4 lg:mb-6">
                <!-- Control Panel -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 lg:p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-3 lg:mb-4">Map Controls</h3>

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
                                View Type
                            </label>
                            <select id="view-filter"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm lg:text-base">
                                <option value="supply_forecast">Real-time Supply Forecast (mt)</option>
                                <option value="production">Historical Production (mt)</option>
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
                            <span>Loading map data...</span>
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
                                        <p class="mt-1 text-xs text-slate-500 lg:text-sm">Municipality production and farmer signals.</p>
                                    </div>

                                    <div class="flex shrink-0 items-center gap-2">
                                        <div class="pwa-panel-count text-right">
                                            <p id="panel-farmer-count" class="text-sm font-bold">-</p>
                                            <p id="panel-farmer-count-label" class="mt-0.5 text-[10px] font-semibold uppercase text-green-700">farmers</p>
                                        </div>
                                        <button type="button" onclick="closeDetailsPanel()"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition-colors hover:bg-slate-50 hover:text-slate-700"
                                            aria-label="Close municipality details">
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
                                <!-- Real-time Production Outlook -->
                                <div class="pwa-panel-section">
                                    <div class="mb-3 flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="pwa-section-label">Current Season Crop Outlook</h3>
                                            <p class="pwa-section-help">Predicted, harvested, damaged, and remaining supply.</p>
                                        </div>
                                        <span id="production-outlook-count" class="pwa-soft-pill">-</span>
                                    </div>
                                    <div id="production-outlook-list" class="space-y-2">
                                        <p class="text-sm text-slate-500">Select a municipality to view current crop outlook.</p>
                                    </div>
                                </div>

                                <!-- Overview Stats -->
                                <div class="pwa-panel-section">
                                    <div class="mb-3">
                                        <h3 class="pwa-section-label">Overview</h3>
                                        <p class="pwa-section-help">Historical records for this municipality.</p>
                                    </div>
                                    <div class="pwa-metric-grid">
                                        <div class="pwa-metric-cell">
                                            <p class="text-xs text-slate-500">Total Production</p>
                                            <p id="detail-production" class="mt-1 text-lg font-bold text-green-700">-</p>
                                        </div>
                                        <div class="pwa-metric-cell">
                                            <p class="text-xs text-slate-500">Area Harvested</p>
                                            <p id="detail-area" class="mt-1 text-lg font-bold text-green-700">-</p>
                                        </div>
                                        <div class="pwa-metric-cell">
                                            <p class="text-xs text-slate-500">Productivity</p>
                                            <p id="detail-productivity" class="mt-1 text-lg font-bold text-green-700">-</p>
                                        </div>
                                        <div class="pwa-metric-cell">
                                            <p class="text-xs text-slate-500">Records</p>
                                            <p id="detail-records" class="mt-1 text-lg font-bold text-green-700">-</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Weather Cards -->
                                <div id="weather-section" class="pwa-panel-section">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="pwa-section-label">Weather Outlook</h3>
                                        <span id="weather-source-badge"
                                            class="pwa-status-pill is-warning hidden">Stale
                                            Cache</span>
                                    </div>

                                    <div id="weather-loading" class="hidden text-xs text-slate-500 mb-3">Loading weather data...</div>
                                    <div id="weather-error" class="hidden text-xs text-red-600 mb-3"></div>

                                    <div id="weather-content" class="hidden">
                                        <div class="pwa-weather-summary">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-950">Current field condition</p>
                                                <p id="weather-current-condition" class="mt-1 text-xs text-slate-600">-</p>
                                            </div>
                                            <p id="weather-current-temp" class="text-lg font-bold text-green-700">-</p>
                                        </div>

                                        <div class="pwa-weather-meta">
                                            <p>Humidity: <span id="weather-current-humidity" class="font-medium text-slate-800">-</span></p>
                                            <p>Wind: <span id="weather-current-wind" class="font-medium text-slate-800">-</span></p>
                                            <p>Rain: <span id="weather-current-rain" class="font-medium text-slate-800">-</span></p>
                                            <p>Updated: <span id="weather-current-time" class="font-medium text-slate-800">-</span></p>
                                        </div>

                                        <div class="mt-4 space-y-3 border-t border-slate-200 pt-3">
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <p class="text-xs font-semibold uppercase text-slate-500">Hourly</p>
                                                    <span id="weather-hourly-count" class="text-[11px] text-slate-500"></span>
                                                </div>
                                                <div id="weather-hourly-list" class="pwa-forecast-strip"></div>
                                            </div>

                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <p class="text-xs font-semibold uppercase text-slate-500">Daily</p>
                                                    <span id="weather-daily-count" class="text-[11px] text-slate-500"></span>
                                                </div>
                                                <div id="weather-daily-list" class="pwa-forecast-strip"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Farm Type Breakdown -->
                                <div class="pwa-panel-section pwa-secondary-block">
                                    <div class="mb-3">
                                        <h3 class="pwa-section-label">Farm Type Distribution</h3>
                                        <p class="pwa-section-help">Production split by farm type.</p>
                                    </div>
                                    <div id="farm-type-container" class="space-y-2">
                                        <!-- Will be populated dynamically -->
                                    </div>
                                </div>

                                <!-- Monthly Production Chart -->
                                <div class="pwa-panel-section pwa-secondary-block">
                                    <h3 class="pwa-section-label mb-3">Monthly Production</h3>
                                    <canvas id="monthly-chart" height="200"></canvas>
                                </div>

                                <!-- Contribution Per Municipality Chart -->
                                <div id="contribution-section" class="pwa-panel-section pwa-secondary-block hidden">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="pwa-section-label">Crop Contribution</h3>
                                        <span id="contribution-crop-badge" class="pwa-soft-pill"></span>
                                    </div>
                                    <canvas id="contribution-chart" height="250"></canvas>
                                    <div id="contribution-details" class="mt-3 space-y-1">
                                        <!-- Populated dynamically -->
                                    </div>
                                </div>

                                <!-- Crop Distribution Chart -->
                                <div class="pwa-panel-section pwa-secondary-block">
                                    <h3 class="pwa-section-label mb-3">Crop Distribution</h3>
                                    <canvas id="crop-chart" height="250"></canvas>
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
                    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-3 lg:mb-4">Summary Statistics</h3>
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
        let currentMunicipality = null; // Track currently open municipality panel
        let detailsRequestToken = 0;

        // Base URLs using Laravel's url() helper
        const apiBase = '{{ url("/api/map") }}';

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
            map = L.map('map').setView([16.5, 120.7], 8);

            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            console.log('Map initialized, loading filters...');
            // Load filters
            loadFilters();
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
                showMapError('Unable to load map filters. Check your connection and try again.');
                document.getElementById('crop-filter').innerHTML = '<option value="">Error loading crops</option>';
                document.getElementById('year-filter').innerHTML = '<option value="">Error loading years</option>';
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
                showMapError('Unable to load map data. Check your connection and try again.');
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

            if (markerBounds.length > 0) {
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
            const classes = ['map-pinpoint', municipalityData ? '' : 'no-data']
                .filter(Boolean)
                .join(' ');

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
                <p class="text-xs sm:text-sm font-medium text-gray-500 m-0">No data available</p>
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
                case 'supply_forecast': return 'Supply Forecast';
                case 'production': return 'Production';
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
            const selected = allMunicipalities.find(d => d.municipality.toUpperCase() === municipalityName.toUpperCase());
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
        let monthlyChart = null;
        let cropChart = null;

        function closeDetailsPanel() {
            document.getElementById('details-panel').classList.add('translate-x-full');
            currentMunicipality = null;
            detailsRequestToken += 1;
            resetWeatherPanel();
        }

        function openDetailsPanel() {
            document.getElementById('details-panel').classList.remove('translate-x-full');
        }

        function resetWeatherPanel() {
            document.getElementById('weather-loading').classList.add('hidden');
            document.getElementById('weather-error').classList.add('hidden');
            document.getElementById('weather-error').textContent = '';
            document.getElementById('weather-content').classList.add('hidden');
            document.getElementById('weather-source-badge').classList.add('hidden');

            document.getElementById('weather-current-condition').textContent = '-';
            document.getElementById('weather-current-temp').textContent = '-';
            document.getElementById('weather-current-humidity').textContent = '-';
            document.getElementById('weather-current-wind').textContent = '-';
            document.getElementById('weather-current-rain').textContent = '-';
            document.getElementById('weather-current-time').textContent = '-';

            document.getElementById('weather-hourly-count').textContent = '';
            document.getElementById('weather-daily-count').textContent = '';
            document.getElementById('weather-hourly-list').innerHTML = '';
            document.getElementById('weather-daily-list').innerHTML = '';
        }

        function formatTemperature(value) {
            if (value === null || value === undefined || value === '') return '-';
            return `${Math.round(Number(value))} C`;
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

        function toSafeNumber(value) {
            const amount = Number(value);
            return Number.isFinite(amount) ? amount : 0;
        }

        function renderProductionOutlook(outlook) {
            const listEl = document.getElementById('production-outlook-list');
            const countEl = document.getElementById('production-outlook-count');
            const rows = Array.isArray(outlook) ? outlook : [];

            countEl.textContent = `${rows.length} ${rows.length === 1 ? 'crop' : 'crops'}`;

            if (rows.length === 0) {
                listEl.innerHTML = '<p class="text-sm text-slate-500">No current-season crop plans reported for this municipality yet.</p>';
                return;
            }

            listEl.innerHTML = rows.map(row => `
                <div class="pwa-crop-row">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-950 break-words">${escapeHtml(row.crop || 'Crop')}</p>
                            <p class="mt-0.5 text-[11px] text-slate-500">${Number(row.plan_count || 0).toLocaleString()} ${Number(row.plan_count || 0) === 1 ? 'plan' : 'plans'} recorded, ${Number(row.harvested_count || 0).toLocaleString()} harvested</p>
                        </div>
                        <span class="pwa-status-pill">${formatMetricTons(row.supply_forecast_mt ?? row.net_expected_production_mt)}</span>
                    </div>
                    <div class="pwa-crop-meta">
                        <span>Predicted ${formatMetricTons(row.predicted_production_mt)}</span>
                        <span>Harvested ${formatMetricTons(row.harvested_production_mt)}</span>
                        <span>Remaining ${formatMetricTons(row.net_expected_production_mt)}</span>
                        ${toSafeNumber(row.damaged_production_mt) > 0 ? `<span class="is-danger">Damaged ${formatMetricTons(row.damaged_production_mt)}</span>` : ''}
                    </div>
                </div>
            `).join('');
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

        function renderWeatherData(weatherPayload, hasErrors) {
            const loadingEl = document.getElementById('weather-loading');
            const errorEl = document.getElementById('weather-error');
            const contentEl = document.getElementById('weather-content');
            const sourceBadgeEl = document.getElementById('weather-source-badge');

            loadingEl.classList.add('hidden');
            errorEl.classList.add('hidden');
            errorEl.textContent = '';

            const staleMap = weatherPayload?.metadata?.stale || {};
            const hasStaleData = Object.values(staleMap).some(Boolean);
            if (hasStaleData) {
                sourceBadgeEl.classList.remove('hidden');
            } else {
                sourceBadgeEl.classList.add('hidden');
            }

            const segmentErrors = Object.values(weatherPayload?.errors || {}).filter(Boolean);
            if (segmentErrors.length > 0) {
                errorEl.textContent = segmentErrors[0];
                errorEl.classList.remove('hidden');
            } else if (hasErrors) {
                errorEl.textContent = 'Some weather segments are temporarily unavailable.';
                errorEl.classList.remove('hidden');
            }

            const current = weatherPayload?.current || {};
            document.getElementById('weather-current-condition').textContent = current.condition_text || 'Unavailable';
            document.getElementById('weather-current-temp').textContent = formatTemperature(current.temperature_c);
            document.getElementById('weather-current-humidity').textContent = formatPercent(current.humidity_percent);
            document.getElementById('weather-current-wind').textContent = formatWind(current.wind_speed_kph);
            document.getElementById('weather-current-rain').textContent = formatPercent(current.precipitation_probability_percent);
            document.getElementById('weather-current-time').textContent = formatClock(current.timestamp);

            const hourlyItems = (weatherPayload?.hourly?.items || []).slice(0, 12);
            document.getElementById('weather-hourly-count').textContent = `${weatherPayload?.hourly?.items?.length || 0} points`;
            const hourlyListEl = document.getElementById('weather-hourly-list');
            if (hourlyItems.length === 0) {
                hourlyListEl.innerHTML = '<p class="text-xs text-slate-500">No hourly data available.</p>';
            } else {
                hourlyListEl.innerHTML = hourlyItems.map(item => `
                    <div class="pwa-forecast-card">
                        <p class="text-[11px] font-semibold text-slate-700">${escapeHtml(formatClock(item.timestamp))}</p>
                        <p class="text-xs font-bold text-green-700 my-1">${escapeHtml(formatTemperature(item.temperature_c))}</p>
                        <p class="text-[10px] text-slate-600 truncate w-full" title="${escapeHtml(item.condition_text || 'N/A')}">${escapeHtml(item.condition_text || 'N/A')}</p>
                        <p class="text-[10px] text-slate-500 mt-1">Rain ${escapeHtml(formatPercent(item.precipitation_probability_percent))}</p>
                    </div>
                `).join('');
            }

            const dailyItems = (weatherPayload?.daily?.items || []).slice(0, 7);
            document.getElementById('weather-daily-count').textContent = `${dailyItems.length} days`;
            const dailyListEl = document.getElementById('weather-daily-list');
            if (dailyItems.length === 0) {
                dailyListEl.innerHTML = '<p class="text-xs text-slate-500">No daily data available.</p>';
            } else {
                dailyListEl.innerHTML = dailyItems.map(item => `
                    <div class="pwa-forecast-card">
                        <p class="text-[11px] font-bold text-slate-800">${escapeHtml(formatDay(item.date))}</p>
                        <p class="text-[10px] text-slate-500 mb-1 truncate w-full" title="${escapeHtml(item.condition_text || 'N/A')}">${escapeHtml(item.condition_text || 'N/A')}</p>
                        <div class="bg-white rounded-md px-2 py-1 border border-slate-200 mb-1 w-full">
                            <p class="text-[11px] font-semibold text-slate-800">${escapeHtml(formatTemperature(item.temp_max_c))}</p>
                            <p class="text-[9px] text-slate-400">${escapeHtml(formatTemperature(item.temp_min_c))}</p>
                        </div>
                        <p class="text-[10px] text-slate-500">Rain ${escapeHtml(formatPercent(item.precipitation_probability_percent))}</p>
                    </div>
                `).join('');
            }

            contentEl.classList.remove('hidden');
        }

        async function loadMunicipalityWeather(municipalityName, requestToken) {
            const weatherLoadingEl = document.getElementById('weather-loading');
            const weatherErrorEl = document.getElementById('weather-error');

            resetWeatherPanel();
            weatherLoadingEl.classList.remove('hidden');

            try {
                const weatherParams = new URLSearchParams({ hours: '24', days: '7' });
                const response = await fetch(`${apiBase}/weather/${encodeURIComponent(municipalityName)}?${weatherParams}`);
                const payload = await response.json();

                if (requestToken !== detailsRequestToken) {
                    return;
                }

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Weather lookup failed.');
                }

                renderWeatherData(payload.weather, payload.has_errors);
            } catch (error) {
                if (requestToken !== detailsRequestToken) {
                    return;
                }

                weatherLoadingEl.classList.add('hidden');
                weatherErrorEl.textContent = `Weather data unavailable: ${error.message}`;
                weatherErrorEl.classList.remove('hidden');
            }
        }

        async function loadMunicipalityDetails(municipalityName) {
            console.log('Loading details for:', municipalityName);

            // Track current municipality for auto-refresh on filter change
            currentMunicipality = municipalityName;
            const requestToken = ++detailsRequestToken;

            // Show panel
            openDetailsPanel();

            // Update header
            document.getElementById('panel-municipality-name').textContent = municipalityName;
            updateFarmerCountCard(municipalityName, getFarmerCountForMunicipality(currentData, municipalityName));
            renderProductionOutlook([]);

            // Show loading
            clearPanelError();
            document.getElementById('panel-loading').classList.remove('hidden');
            document.getElementById('panel-content').classList.add('hidden');

            const weatherPromise = loadMunicipalityWeather(municipalityName, requestToken);

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
                renderProductionOutlook(data.production_outlook);

                // Update overview stats
                document.getElementById('detail-production').textContent = Number(data.summary.total_production).toLocaleString() + ' mt';
                document.getElementById('detail-area').textContent = Number(data.summary.total_area_harvested).toLocaleString() + ' ha';
                document.getElementById('detail-productivity').textContent = Number(data.summary.avg_productivity).toFixed(2) + ' mt/ha';
                document.getElementById('detail-records').textContent = data.monthly_data.length + ' months';

                // Update farm type breakdown
                updateFarmTypeBreakdown(data.farm_type_breakdown);

                // Update charts
                updateMonthlyChart(data.monthly_data);
                updateContributionChart(municipalityName);
                updateCropChart(data.crop_distribution);

                // Hide loading, show content
                document.getElementById('panel-loading').classList.add('hidden');
                document.getElementById('panel-content').classList.remove('hidden');

                weatherPromise.catch(() => {
                    // Weather errors are rendered in the weather section to avoid blocking crop data.
                });

            } catch (error) {
                if (requestToken !== detailsRequestToken) {
                    return;
                }

                console.error('Error loading municipality details:', error);
                showPanelError('Unable to load municipality details. Check your connection and try again.');
            }
        }

        function updateFarmTypeBreakdown(farmTypes) {
            const container = document.getElementById('farm-type-container');

            if (!farmTypes || farmTypes.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-500">No farm type data available.</p>';
                return;
            }

            const total = farmTypes.reduce((sum, ft) => sum + parseFloat(ft.total_production), 0);

            container.innerHTML = farmTypes.map(ft => {
                const production = parseFloat(ft.total_production);
                const percentage = ((production / total) * 100).toFixed(1);
                return `
                    <div class="pwa-crop-row">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-slate-800">${ft.farm_type}</span>
                            <span class="pwa-status-pill">${percentage}%</span>
                        </div>
                        <div class="pwa-progress-track">
                            <div class="pwa-progress-fill" style="width: ${percentage}%"></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">${Number(production).toLocaleString()} mt</p>
                    </div>
                `;
            }).join('');
        }

        function updateMonthlyChart(monthlyData) {
            const ctx = document.getElementById('monthly-chart');

            // Destroy existing chart
            if (monthlyChart) {
                monthlyChart.destroy();
            }

            const monthNames = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            const data = monthNames.map((monthName) => {
                const monthData = monthlyData.find(m => m.month === monthName);
                return monthData ? parseFloat(monthData.total_production) : 0;
            });

            monthlyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Production (mt)',
                        data: data,
                        backgroundColor: 'rgba(22, 163, 74, 0.75)',
                        borderColor: 'rgb(22, 163, 74)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        function updateCropChart(cropData) {
            const ctx = document.getElementById('crop-chart');
            if (!ctx) return;

            const container = ctx.parentElement;
            const emptyState = container?.querySelector('[data-crop-chart-empty]');

            // Destroy existing chart
            if (cropChart) {
                cropChart.destroy();
                cropChart = null;
            }

            if (!Array.isArray(cropData) || cropData.length === 0) {
                ctx.classList.add('hidden');
                if (container && !emptyState) {
                    container.insertAdjacentHTML('beforeend',
                        '<p data-crop-chart-empty class="text-sm text-slate-500 text-center py-8">No crop data available</p>');
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
</x-admin-layout>

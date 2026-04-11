<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
            {{ __('Interactive Crop Production Map') }}
        </h2>
    </x-slot>

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
                                <p class="text-xs text-green-600">Map is focused on your municipality</p>
                            </div>
                        </div>
                        <button onclick="focusOnMyMunicipality()"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Focus on My Location
                        </button>
                    </div>
                </div>
            @endif

            <!-- Control Panel -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 lg:mb-6">
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
                                <option value="production">Total Production (mt)</option>
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

                    <!-- Toggle Options -->
                    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-100">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="toggle-boundaries" checked class="sr-only peer">
                            <div
                                class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600">
                            </div>
                            <span class="ms-2 text-sm text-gray-600">Show Boundaries</span>
                        </label>
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
                </div>
            </div>

            <!-- Map Container -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-3 lg:p-6 relative">
                    <div id="map" style="height: 500px; width: 100%;"
                        class="relative z-0 rounded-lg shadow-inner sm:h-[650px] lg:h-[800px]"></div>

                    <!-- Legend - Collapsible on mobile -->
                    <div id="legend"
                        class="absolute bottom-4 left-4 lg:bottom-8 lg:left-8 bg-white rounded-lg shadow-lg border-2 border-gray-200 z-10 max-w-[200px] sm:max-w-[240px] lg:max-w-[280px]">
                        <!-- Toggle Button (Mobile Only) -->
                        <button id="legend-toggle" onclick="toggleLegend()"
                            class="lg:hidden w-full flex items-center justify-between p-3 font-bold text-gray-800 text-xs uppercase tracking-wide">
                            <span>Legend</span>
                            <svg id="legend-icon" class="w-4 h-4 transform transition-transform" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Legend Content -->
                        <div id="legend-wrapper" class="hidden lg:block p-3 lg:p-4">
                            <h4
                                class="hidden lg:block font-bold text-gray-800 mb-2 lg:mb-3 text-xs lg:text-sm uppercase tracking-wide">
                                Production Legend</h4>
                            <div id="legend-content">
                                <span class="text-xs text-gray-600">Select filters to view data</span>
                            </div>
                        </div>
                    </div>

                    <!-- Municipality Details Panel - Slides from right -->
                    <div id="details-panel"
                        class="fixed top-0 right-0 h-full bg-white shadow-2xl z-30 transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto w-full sm:w-[400px] lg:w-[450px]">
                        <div class="p-4 lg:p-6">
                            <!-- Close Button -->
                            <button onclick="closeDetailsPanel()"
                                class="absolute top-3 right-3 lg:top-4 lg:right-4 text-gray-500 hover:text-gray-700">
                                <svg class="w-5 h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>

                            <!-- Panel Header -->
                            <div class="mb-4 lg:mb-6 pr-8">
                                <h2 id="panel-municipality-name"
                                    class="text-xl lg:text-2xl font-bold text-gray-800 mb-2">Municipality Name</h2>
                                <p class="text-xs lg:text-sm text-gray-600">Click on municipality data below</p>
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

                            <!-- Panel Content -->
                            <div id="panel-content" class="space-y-6">
                                <!-- Overview Stats -->
                                <div
                                    class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                                    <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Overview</h3>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs text-gray-600">Total Production</p>
                                            <p id="detail-production" class="text-lg font-bold text-green-700">-</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600">Area Harvested</p>
                                            <p id="detail-area" class="text-lg font-bold text-green-700">-</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600">Productivity</p>
                                            <p id="detail-productivity" class="text-lg font-bold text-green-700">-</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-600">Records</p>
                                            <p id="detail-records" class="text-lg font-bold text-green-700">-</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Weather Cards -->
                                <div id="weather-section" class="rounded-lg border border-sky-200 bg-sky-50/50 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-sm font-semibold text-gray-700 uppercase">Weather Outlook</h3>
                                        <span id="weather-source-badge"
                                            class="hidden inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-100 text-amber-800">Stale
                                            Cache</span>
                                    </div>

                                    <div id="weather-loading" class="hidden text-xs text-sky-700 mb-3">Loading weather data...</div>
                                    <div id="weather-error" class="hidden text-xs text-red-600 mb-3"></div>

                                    <div id="weather-content" class="hidden space-y-3">
                                        <div class="bg-white rounded-md border border-sky-100 p-3">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <p class="text-xs uppercase tracking-wide text-gray-500">Current</p>
                                                    <p id="weather-current-condition" class="text-sm font-semibold text-gray-800">-</p>
                                                </div>
                                                <p id="weather-current-temp" class="text-base font-bold text-sky-700">-</p>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 mt-2 text-xs text-gray-600">
                                                <p>Humidity: <span id="weather-current-humidity" class="font-medium text-gray-700">-</span></p>
                                                <p>Wind: <span id="weather-current-wind" class="font-medium text-gray-700">-</span></p>
                                                <p>Rain Chance: <span id="weather-current-rain" class="font-medium text-gray-700">-</span></p>
                                                <p>Updated: <span id="weather-current-time" class="font-medium text-gray-700">-</span></p>
                                            </div>
                                        </div>

                                        <div class="bg-white rounded-md border border-sky-100 p-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">Hourly (24h)</p>
                                                <span id="weather-hourly-count" class="text-[11px] text-gray-500"></span>
                                            </div>
                                            <div id="weather-hourly-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                                        </div>

                                        <div class="bg-white rounded-md border border-sky-100 p-3">
                                            <div class="flex items-center justify-between mb-2">
                                                <p class="text-xs uppercase tracking-wide text-gray-500">Daily (7d)</p>
                                                <span id="weather-daily-count" class="text-[11px] text-gray-500"></span>
                                            </div>
                                            <div id="weather-daily-list" class="space-y-1"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Farm Type Breakdown -->
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Farm Type
                                        Distribution</h3>
                                    <div id="farm-type-container" class="space-y-2">
                                        <!-- Will be populated dynamically -->
                                    </div>
                                </div>

                                <!-- Monthly Production Chart -->
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Monthly Production
                                    </h3>
                                    <canvas id="monthly-chart" height="200"></canvas>
                                </div>

                                <!-- Contribution Per Municipality Chart -->
                                <div id="contribution-section" class="hidden">
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-sm font-semibold text-gray-700 uppercase">Crop Contribution</h3>
                                        <span id="contribution-crop-badge"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"></span>
                                    </div>
                                    <canvas id="contribution-chart" height="250"></canvas>
                                    <div id="contribution-details" class="mt-3 space-y-1">
                                        <!-- Populated dynamically -->
                                    </div>
                                </div>

                                <!-- Crop Distribution Chart -->
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 uppercase mb-3">Crop Distribution
                                    </h3>
                                    <canvas id="crop-chart" height="250"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
        let geojsonLayer;
        let currentData = {};
        let filterOptions = {};
        let currentMunicipality = null;
        let detailsRequestToken = 0;

        // Base URLs using Laravel's url() helper
        const apiBase = '{{ url("/api/map") }}';
        const dataPath = '{{ asset("data") }}';

        // User preferences from server
        const userPreferredMunicipality = '{{ $preferredMunicipality ?? '' }}';
        const userFavoriteCrops = @json($favoriteCrops ?? []);

        // Municipality coordinates for focusing
        const municipalityCoords = {
            'ATOK': [16.5917, 120.7083],
            'BAKUN': [16.7833, 120.6667],
            'BOKOD': [16.4833, 120.8167],
            'BUGUIAS': [16.7333, 120.8333],
            'ITOGON': [16.3667, 120.6833],
            'KABAYAN': [16.6167, 120.8500],
            'KAPANGAN': [16.5667, 120.6000],
            'KIBUNGAN': [16.6833, 120.6500],
            'LA TRINIDAD': [16.4500, 120.5833],
            'MANKAYAN': [16.8667, 120.7833],
            'SABLAN': [16.4833, 120.5000],
            'TUBA': [16.3333, 120.5500],
            'TUBLAY': [16.5000, 120.6167]
        };

        function normalizeMunicipalityName(name) {
            return (name || '').toString().toUpperCase().replace(/\s+/g, '');
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

        // Toggle legend on mobile
        function toggleLegend() {
            const wrapper = document.getElementById('legend-wrapper');
            const icon = document.getElementById('legend-icon');

            if (wrapper.classList.contains('hidden')) {
                wrapper.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                wrapper.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        // Load filter options from API
        async function loadFilters() {
            try {
                console.log('Fetching filters from:', `${apiBase}/filters`);
                const response = await fetch(`${apiBase}/filters`);
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

                // Set default year to latest
                yearSelect.value = filterOptions.years[filterOptions.years.length - 1];

                // Load initial map data
                loadMapData();
            } catch (error) {
                console.error('Error loading filters:', error);
                alert('Error loading filters. Please check console for details.');
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
            document.getElementById('loading-indicator').classList.remove('hidden');

            try {
                const params = new URLSearchParams();
                if (crop) params.append('crop', crop);
                if (year) params.append('year', year);
                if (view) params.append('view', view);
                if (farmType) params.append('farm_type', farmType);

                const response = await fetch(`${apiBase}/data?${params}`);
                const data = await response.json();

                currentData = data;
                updateMap(data);
                updateStats(data);
            } catch (error) {
                console.error('Error loading map data:', error);
                alert('Error loading map data: ' + error.message);
            } finally {
                document.getElementById('loading-indicator').classList.add('hidden');
            }
        }

        // Update map with choropleth
        function updateMap(data) {
            // Remove existing layer
            if (geojsonLayer) {
                map.removeLayer(geojsonLayer);
            }

            // Use the dataPath from Laravel's asset() helper
            const geojsonPath = `${dataPath}/benguet.geojson`;

            // Load GeoJSON
            fetch(geojsonPath)
                .then(response => response.json())
                .then(geojson => {
                    geojsonLayer = L.geoJSON(geojson, {
                        style: feature => getStyle(feature, data),
                        onEachFeature: (feature, layer) => {
                            const municipalityName = feature.properties.name;
                            const municipalityData = data.data.find(d =>
                                normalizeMunicipalityName(d.municipality) === normalizeMunicipalityName(municipalityName)
                            );

                            // Popup
                            let popupContent = `<strong>${municipalityName}</strong><br>`;
                            if (municipalityData) {
                                const viewType = document.getElementById('view-filter').value;
                                const unit = getUnit(viewType);
                                popupContent += `${getViewLabel(viewType)}: ${Number(municipalityData.value).toLocaleString()} ${unit}`;
                            } else {
                                popupContent += 'No data available';
                            }
                            layer.bindPopup(popupContent);

                            // Hover effect
                            layer.on({
                                mouseover: e => {
                                    layer.setStyle({
                                        weight: 3,
                                        color: '#666',
                                        fillOpacity: 0.9
                                    });
                                },
                                mouseout: e => {
                                    geojsonLayer.resetStyle(layer);
                                },
                                click: e => {
                                    loadMunicipalityDetails(municipalityName);
                                }
                            });
                        }
                    }).addTo(map);

                    // Update legend
                    updateLegend(data);

                    // Highlight user's preferred municipality
                    if (userPreferredMunicipality) {
                        highlightUserMunicipality();
                    }
                })
                .catch(error => {
                    console.error('Error loading GeoJSON:', error);
                    alert('Error loading map boundaries: ' + error.message + '\nPath: ' + geojsonPath);
                });
        }

        // Highlight user's preferred municipality with a special border
        function highlightUserMunicipality() {
            if (!geojsonLayer || !userPreferredMunicipality) return;

            geojsonLayer.eachLayer(layer => {
                const name = layer.feature.properties.name;
                if (normalizeMunicipalityName(name) === normalizeMunicipalityName(userPreferredMunicipality)) {
                    layer.setStyle({
                        weight: 4,
                        color: '#7c3aed', // Purple border for user's location
                        dashArray: '5, 5'
                    });
                    layer.bringToFront();
                }
            });
        }

        // Get style for municipality based on value
        function getStyle(feature, data) {
            const municipalityName = feature.properties.name;
            const municipalityData = data.data.find(d =>
                normalizeMunicipalityName(d.municipality) === normalizeMunicipalityName(municipalityName)
            );

            // Check if this is the user's preferred municipality
            const isUserMunicipality = userPreferredMunicipality &&
                normalizeMunicipalityName(municipalityName) === normalizeMunicipalityName(userPreferredMunicipality);

            let fillColor = '#d3d3d3'; // Default gray for no data

            if (municipalityData && data.metadata) {
                const value = municipalityData.value;
                const { min, max } = data.metadata;

                // Calculate color based on value (green gradient)
                const ratio = (value - min) / (max - min);
                fillColor = getColor(ratio);
            }

            return {
                fillColor: fillColor,
                weight: isUserMunicipality ? 4 : 2,
                opacity: 1,
                color: isUserMunicipality ? '#7c3aed' : 'white',
                dashArray: isUserMunicipality ? '5, 5' : null,
                fillOpacity: 0.7
            };
        }

        // Get color for value ratio (0-1)
        function getColor(ratio) {
            // Red to Green gradient - red = low, green = high
            const colors = [
                '#991b1b', // Very dark red (lowest)
                '#dc2626', // Dark red
                '#ef4444', // Red
                '#f97316', // Orange
                '#f59e0b', // Amber
                '#eab308', // Yellow
                '#84cc16', // Lime green
                '#22c55e', // Green
                '#15803d'  // Dark green (highest)
            ];

            const index = Math.floor(ratio * (colors.length - 1));
            return colors[index];
        }

        // Update legend
        function updateLegend(data) {
            const legendContent = document.getElementById('legend-content');

            if (!data.metadata) {
                legendContent.innerHTML = '<span class="text-sm text-gray-600">No data available</span>';
                return;
            }

            const { min, max } = data.metadata;
            const viewType = document.getElementById('view-filter').value;
            const unit = getUnit(viewType);

            legendContent.innerHTML = `
                <div class="space-y-2">
                    <!-- Color gradient -->
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border-2 border-gray-400" style="background-color: #15803d;"></div>
                            <span class="text-xs font-semibold text-gray-700">Highest</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border border-gray-300" style="background-color: #22c55e;"></div>
                            <span class="text-xs text-gray-600">Very High</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border border-gray-300" style="background-color: #84cc16;"></div>
                            <span class="text-xs text-gray-600">High</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border border-gray-300" style="background-color: #eab308;"></div>
                            <span class="text-xs text-gray-600">Medium-High</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border border-gray-300" style="background-color: #f59e0b;"></div>
                            <span class="text-xs text-gray-600">Medium</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border border-gray-300" style="background-color: #f97316;"></div>
                            <span class="text-xs text-gray-600">Medium-Low</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border border-gray-300" style="background-color: #ef4444;"></div>
                            <span class="text-xs text-gray-600">Low</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border border-gray-300" style="background-color: #dc2626;"></div>
                            <span class="text-xs text-gray-600">Very Low</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border-2 border-gray-400" style="background-color: #991b1b;"></div>
                            <span class="text-xs font-semibold text-gray-700">Lowest</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-6 rounded border border-gray-300" style="background-color: #ffffff;"></div>
                            <span class="text-xs text-gray-600">No data</span>
                        </div>
                    </div>
                    
                    <!-- Range values -->
                    <div class="pt-2 border-t border-gray-200">
                        <div class="text-xs text-gray-600">
                            <div><span class="font-medium">Max:</span> <span class="font-bold" style="color: #15803d;">${Number(max).toLocaleString()} ${unit}</span></div>
                            <div><span class="font-medium">Min:</span> <span class="font-bold" style="color: #991b1b;">${Number(min).toLocaleString()} ${unit}</span></div>
                        </div>
                    </div>
                </div>
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
                case 'production': return 'mt';
                case 'area_harvested': return 'ha';
                case 'productivity': return 'mt/ha';
                default: return '';
            }
        }

        // Get view label
        function getViewLabel(viewType) {
            switch (viewType) {
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
            const selected = allMunicipalities.find(d => normalizeMunicipalityName(d.municipality) === normalizeMunicipalityName(municipalityName));
            const selectedValue = selected ? selected.value : 0;
            const total = allMunicipalities.reduce((sum, d) => sum + d.value, 0);
            const othersValue = total - selectedValue;
            const selectedPercentage = total > 0 ? ((selectedValue / total) * 100).toFixed(1) : '0.0';
            const othersPercentage = total > 0 ? ((othersValue / total) * 100).toFixed(1) : '0.0';

            // Update details text
            const detailsContainer = document.getElementById('contribution-details');
            detailsContainer.innerHTML = `
                <div class="flex items-center justify-between bg-green-50 p-2 rounded border border-green-200">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: #22c55e"></div>
                        <span class="text-sm font-medium text-gray-700">${municipalityName}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-green-700">${selectedPercentage}%</span>
                        <span class="text-xs text-gray-500 ml-1">(${Number(selectedValue).toLocaleString()} ${unit})</span>
                    </div>
                </div>
                <div class="flex items-center justify-between bg-gray-50 p-2 rounded border border-gray-200">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: #d1d5db"></div>
                        <span class="text-sm font-medium text-gray-700">Other Municipalities</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-gray-800">${othersPercentage}%</span>
                        <span class="text-xs text-gray-500 ml-1">(${Number(othersValue).toLocaleString()} ${unit})</span>
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
                        backgroundColor: ['#22c55e', '#d1d5db'],
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
            return `${Number(value).toFixed(1)} C`;
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

            const hourlyItems = (weatherPayload?.hourly?.items || []).slice(0, 6);
            document.getElementById('weather-hourly-count').textContent = `${weatherPayload?.hourly?.items?.length || 0} points`;
            const hourlyListEl = document.getElementById('weather-hourly-list');
            if (hourlyItems.length === 0) {
                hourlyListEl.innerHTML = '<p class="text-xs text-gray-500">No hourly data available.</p>';
            } else {
                hourlyListEl.innerHTML = hourlyItems.map(item => `
                    <div class="rounded border border-sky-100 bg-sky-50 p-2">
                        <p class="text-xs font-semibold text-gray-700">${escapeHtml(formatClock(item.timestamp))}</p>
                        <p class="text-xs text-gray-600 truncate">${escapeHtml(item.condition_text || 'N/A')}</p>
                        <p class="text-xs text-gray-800">${escapeHtml(formatTemperature(item.temperature_c))}</p>
                        <p class="text-[11px] text-gray-500">Rain ${escapeHtml(formatPercent(item.precipitation_probability_percent))}</p>
                    </div>
                `).join('');
            }

            const dailyItems = (weatherPayload?.daily?.items || []).slice(0, 7);
            document.getElementById('weather-daily-count').textContent = `${dailyItems.length} days`;
            const dailyListEl = document.getElementById('weather-daily-list');
            if (dailyItems.length === 0) {
                dailyListEl.innerHTML = '<p class="text-xs text-gray-500">No daily data available.</p>';
            } else {
                dailyListEl.innerHTML = dailyItems.map(item => `
                    <div class="flex items-center justify-between rounded border border-sky-100 bg-sky-50 px-2 py-1.5">
                        <div>
                            <p class="text-xs font-semibold text-gray-700">${escapeHtml(formatDay(item.date))}</p>
                            <p class="text-[11px] text-gray-500 truncate">${escapeHtml(item.condition_text || 'N/A')}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-gray-700">${escapeHtml(formatTemperature(item.temp_min_c))} / ${escapeHtml(formatTemperature(item.temp_max_c))}</p>
                            <p class="text-[11px] text-gray-500">Rain ${escapeHtml(formatPercent(item.precipitation_probability_percent))}</p>
                        </div>
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

            currentMunicipality = municipalityName;
            const requestToken = ++detailsRequestToken;

            // Show panel
            openDetailsPanel();

            // Update header
            document.getElementById('panel-municipality-name').textContent = municipalityName;

            // Show loading
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
                const data = await response.json();

                if (requestToken !== detailsRequestToken) {
                    return;
                }

                console.log('Municipality data:', data);

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
                alert('Error loading details: ' + error.message);
                closeDetailsPanel();
            }
        }

        function updateFarmTypeBreakdown(farmTypes) {
            const container = document.getElementById('farm-type-container');

            if (!farmTypes || farmTypes.length === 0) {
                container.innerHTML = '<p class="text-sm text-gray-500">No farm type data available</p>';
                return;
            }

            const total = farmTypes.reduce((sum, ft) => sum + parseFloat(ft.total_production), 0);

            container.innerHTML = farmTypes.map(ft => {
                const production = parseFloat(ft.total_production);
                const percentage = ((production / total) * 100).toFixed(1);
                return `
                    <div class="bg-gray-50 p-3 rounded border border-gray-200">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">${ft.farm_type}</span>
                            <span class="text-sm font-bold text-green-600">${percentage}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">${Number(production).toLocaleString()} mt</p>
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
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgb(34, 197, 94)',
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

            // Destroy existing chart
            if (cropChart) {
                cropChart.destroy();
            }

            if (cropData.length === 0) {
                ctx.parentElement.innerHTML = '<p class="text-sm text-gray-500 text-center py-8">No crop data available</p>';
                return;
            }

            const colors = [
                '#ef4444', '#f59e0b', '#eab308', '#84cc16', '#22c55e',
                '#10b981', '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1'
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

        // Toggle boundaries visibility
        function toggleBoundaries(show) {
            if (!geojsonLayer) return;

            if (show) {
                if (!map.hasLayer(geojsonLayer)) {
                    geojsonLayer.addTo(map);
                }
            } else {
                if (map.hasLayer(geojsonLayer)) {
                    map.removeLayer(geojsonLayer);
                }
            }
        }

        // Event listeners
        document.getElementById('crop-filter').addEventListener('change', onFilterChange);
        document.getElementById('year-filter').addEventListener('change', onFilterChange);
        document.getElementById('view-filter').addEventListener('change', onFilterChange);
        document.getElementById('farm-type-filter').addEventListener('change', onFilterChange);
        document.getElementById('toggle-boundaries').addEventListener('change', function () {
            toggleBoundaries(this.checked);
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</x-app-layout>
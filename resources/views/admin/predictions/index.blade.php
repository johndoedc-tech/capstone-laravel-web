<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crop Production Prediction & Forecasting') }}
        </h2>
    </x-slot>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Tab Navigation (hidden - only showing Future Production Forecast) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <button type="button" onclick="switchTab('prediction')" id="tab-prediction"
                            class="tab-button active w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                            Historical Analysis
                            <span class="block text-xs text-gray-500 mt-1">Validate model accuracy (2015-2024)</span>
                        </button>
                        <button type="button" onclick="switchTab('forecast')" id="tab-forecast"
                            class="tab-button w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z">
                                </path>
                            </svg>
                            Future Production Forecast
                            <span class="block text-xs text-gray-500 mt-1">See year-to-year trends (2025-2030+)</span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Prediction Tab Content (Hidden) -->
            <div id="prediction-content" class="tab-content hidden">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form id="predictionForm">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Municipality -->
                                <div>
                                    <label for="municipality"
                                        class="block text-sm font-medium text-gray-700">Municipality</label>
                                    <select id="municipality" name="municipality" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Municipality</option>
                                        @foreach($options['municipalities'] ?? [] as $municipality)
                                            <option value="{{ $municipality }}">{{ $municipality }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Farm Type -->
                                <div>
                                    <label for="farm_type" class="block text-sm font-medium text-gray-700">Farm
                                        Type</label>
                                    <select id="farm_type" name="farm_type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Farm Type</option>
                                        @foreach($options['farm_types'] ?? [] as $farm_type)
                                            <option value="{{ $farm_type }}">{{ $farm_type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Year -->
                                <div>
                                    <label for="year" class="block text-sm font-medium text-gray-700">
                                        Year
                                        <span class="text-xs text-gray-500">(2015-2024 for historical analysis)</span>
                                    </label>
                                    <input type="number" id="year" name="year" min="2015" max="2024" required
                                        value="2024"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="mt-1 text-xs text-blue-600">
                                        💡 For years 2025+, use the <strong>Multi-Year Forecast</strong> tab
                                    </p>
                                </div>

                                <!-- Month -->
                                <div>
                                    <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                                    <select id="month" name="month" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <!-- Crop -->
                                <div>
                                    <label for="crop" class="block text-sm font-medium text-gray-700">Crop</label>
                                    <select id="crop" name="crop" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Select Crop</option>
                                        @foreach($options['crops'] ?? [] as $crop)
                                            <option value="{{ $crop }}">{{ $crop }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Area Planted -->
                                <div>
                                    <label for="area_planted" class="block text-sm font-medium text-gray-700">
                                        Area Planted (hectares)
                                        <span class="text-xs text-gray-500 ml-1">How much land will you plant?</span>
                                    </label>
                                    <input type="number" id="area_planted" name="area_planted" step="0.01" min="0"
                                        required placeholder="e.g., 100.5"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <p class="mt-1 text-xs text-gray-500">💡 The model predicts production based on this
                                        area</p>
                                </div>
                            </div>

                            <!-- Info Box about historical analysis -->
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-semibold text-blue-900">📊 Purpose: Historical Analysis
                                            & Model Validation</h4>
                                        <p class="mt-1 text-xs text-blue-800">
                                            This tool validates our AI model's accuracy using <strong>historical data
                                                (2015-2024)</strong>.<br>
                                            Compare predictions against actual production to see how well the model
                                            performs.<br><br>
                                            <strong>Model trained on:</strong> 10 years of Benguet crop data<br>
                                            <strong>Accuracy:</strong> 68.17% (crop-sensitive predictions)<br>
                                            <strong>For future years:</strong> Use the <a href="#"
                                                onclick="switchTab('forecast'); return false;"
                                                class="underline font-semibold">Future Production Forecast</a> tab 🔮
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="submit" id="submitBtn"
                                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                                    <span id="btnText">Predict Production</span>
                                    <svg id="spinner" class="hidden animate-spin ml-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </form>

                        <!-- Results Section -->
                        <div id="results" class="mt-8 hidden">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">Prediction Results</h3>
                            <div id="resultsContent" class="space-y-4">
                                <!-- Results will be displayed here -->
                            </div>
                        </div>

                        <!-- Error Section -->
                        <div id="errorSection" class="mt-8 hidden">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                                        <div id="errorMessage" class="mt-2 text-sm text-red-700"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forecast Tab Content -->
            <div id="forecast-content" class="tab-content">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Info Banner -->
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        <strong>Multi-Year Forecast</strong> uses time-series analysis to predict
                                        production trends for future years (2025-2030).
                                        This is more accurate for future predictions than single-year predictions.<br>
                                        <span class="text-xs">📊 Shows <strong>combined production</strong> for both
                                            IRRIGATED and RAINFED farms in the selected municipality.</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form id="forecastForm">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Municipality -->
                                <div>
                                    <label for="forecast_municipality"
                                        class="block text-sm font-medium text-gray-700">Municipality</label>
                                    <select id="forecast_municipality" name="municipality" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        <option value="">Select Municipality</option>
                                        @foreach($options['municipalities'] ?? [] as $municipality)
                                            <option value="{{ $municipality }}">{{ $municipality }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Crop -->
                                <div>
                                    <label for="forecast_crop"
                                        class="block text-sm font-medium text-gray-700">Crop</label>
                                    <select id="forecast_crop" name="crop" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        <option value="">Select Crop</option>
                                        @foreach($options['crops'] ?? [] as $crop)
                                            <option value="{{ $crop }}">{{ $crop }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Forecast Years (Info only - Python API returns all available) -->
                                <div>
                                    <label for="forecast_years" class="block text-sm font-medium text-gray-700">Forecast
                                        Period</label>
                                    <select id="forecast_years" name="forecast_years" required disabled
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 bg-gray-100">
                                        <option value="6" selected>6 Years (2025-2030)</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Shows year-over-year production trends</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="submit" id="forecastBtn"
                                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50">
                                    <span id="forecastBtnText">Generate Forecast</span>
                                    <svg id="forecastSpinner" class="hidden animate-spin ml-2 h-4 w-4 text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </form>

                        <!-- Forecast Results Section -->
                        <div id="forecastResults" class="mt-8 hidden">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">Forecast Results</h3>

                            <!-- Comparison Chart -->
                            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                                <h4 class="text-md font-semibold text-gray-800 mb-4">Historical vs Predicted Production
                                    Comparison</h4>
                                <canvas id="comparisonChart" height="80"></canvas>
                            </div>

                            <div id="forecastContent" class="space-y-4">
                                <!-- Forecast results will be displayed here -->
                            </div>
                        </div>

                        <!-- Forecast Error Section -->
                        <div id="forecastError" class="mt-8 hidden">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                                        <div id="forecastErrorMessage" class="mt-2 text-sm text-red-700"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tab-button {
            border-color: transparent;
            color: #6B7280;
            transition: all 0.2s;
        }

        .tab-button:hover {
            color: #374151;
            border-color: #D1D5DB;
        }

        .tab-button.active {
            color: #4F46E5;
            border-color: #4F46E5;
        }

        .tab-content {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        // Tab Switching Function
        function switchTab(tab) {
            const predictionTab = document.getElementById('tab-prediction');
            const forecastTab = document.getElementById('tab-forecast');
            const predictionContent = document.getElementById('prediction-content');
            const forecastContent = document.getElementById('forecast-content');

            if (tab === 'prediction') {
                predictionTab.classList.add('active');
                forecastTab.classList.remove('active');
                predictionContent.classList.remove('hidden');
                forecastContent.classList.add('hidden');
            } else {
                forecastTab.classList.add('active');
                predictionTab.classList.remove('active');
                forecastContent.classList.remove('hidden');
                predictionContent.classList.add('hidden');
            }
        }

        // Auto-detect future years and suggest forecast tab
        document.addEventListener('DOMContentLoaded', function () {
            const yearInput = document.getElementById('year');
            const warningDiv = document.createElement('div');
            warningDiv.id = 'year-warning';
            warningDiv.className = 'hidden mt-2 p-3 bg-yellow-50 border border-yellow-300 rounded-lg';
            warningDiv.innerHTML = `
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800">Future Year Detected!</p>
                        <p class="text-xs text-yellow-700 mt-1">
                            For years 2025 and beyond, please use the <strong>Future Production Forecast</strong> tab to see year-to-year trends.
                        </p>
                        <button type="button" onclick="switchTab('forecast')" 
                            class="mt-2 text-xs font-medium text-yellow-800 underline hover:text-yellow-900">
                            → Switch to Forecast Tab
                        </button>
                    </div>
                </div>
            `;
            yearInput.parentNode.appendChild(warningDiv);

            yearInput.addEventListener('input', function () {
                const year = parseInt(this.value);
                if (year >= 2025) {
                    warningDiv.classList.remove('hidden');
                    this.classList.add('border-yellow-400');
                } else {
                    warningDiv.classList.add('hidden');
                    this.classList.remove('border-yellow-400');
                }
            });
        });

        // Prediction Form Handler
        document.getElementById('predictionForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const spinner = document.getElementById('spinner');
            const resultsDiv = document.getElementById('results');
            const resultsContent = document.getElementById('resultsContent');
            const errorSection = document.getElementById('errorSection');
            const errorMessage = document.getElementById('errorMessage');

            // Hide previous results/errors
            resultsDiv.classList.add('hidden');
            errorSection.classList.add('hidden');

            // Show loading state
            submitBtn.disabled = true;
            btnText.textContent = 'Processing...';
            spinner.classList.remove('hidden');

            try {
                const formData = new FormData(this);

                // NEW MODEL: Only 6 features needed (no area_harvested or productivity)
                const requestPayload = {
                    municipality: formData.get('municipality'),
                    farm_type: formData.get('farm_type'),
                    year: parseInt(formData.get('year')),
                    month: parseInt(formData.get('month')),
                    crop: formData.get('crop'),
                    area_planted: parseFloat(formData.get('area_planted'))
                };

                console.log('Sending request (6 features):', requestPayload); // Debug log

                const response = await fetch('{{ route('admin.predictions.predict') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestPayload)
                });

                const result = await response.json();

                console.log('API Response:', result); // Debug log

                if (result.success || response.ok) {
                    // Handle the nested structure from your API
                    const prediction = result.prediction || result;

                    const productionMt = prediction.production_mt || prediction.Production_mt || 0;
                    const confidenceScore = prediction.confidence_score || prediction.Confidence_Score || 0;

                    resultsContent.innerHTML = `
                        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 p-6 rounded-lg border-2 border-indigo-200">
                            <div class="text-center mb-4">
                                <p class="text-sm text-gray-600 mb-1">Predicted Production</p>
                                <p class="text-4xl font-bold text-indigo-700">${parseFloat(productionMt).toFixed(2)} <span class="text-2xl">MT</span></p>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Model Confidence Score</span>
                                <span class="text-lg font-bold text-blue-700">${parseFloat(confidenceScore * 100).toFixed(2)}%</span>
                            </div>
                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: ${parseFloat(confidenceScore * 100).toFixed(0)}%"></div>
                            </div>
                        </div>
                        
                        <div class="bg-green-50 border border-green-200 p-3 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-green-900">Crop-Sensitive AI Model</p>
                                    <p class="text-xs text-green-800 mt-1">
                                        This prediction is based on historical patterns of <strong>${requestPayload.crop}</strong> in 
                                        <strong>${requestPayload.municipality}</strong> with <strong>${requestPayload.farm_type}</strong> farming.
                                        Different crops in different locations will produce different results! 🌾
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        ${result.saved_to_history ? `
                        <div class="bg-indigo-50 border border-indigo-200 p-3 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-medium text-indigo-900">Saved to prediction history</span>
                                </div>
                                <a href="{{ route('admin.predictions.history') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    View History →
                                </a>
                            </div>
                        </div>
                        ` : result.save_error ? `
                        <div class="bg-yellow-50 border border-yellow-300 p-3 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-xs font-semibold text-yellow-900">Warning: Not saved to history</p>
                                    <p class="text-xs text-yellow-800 mt-1">${result.save_error}</p>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    `;

                    resultsDiv.classList.remove('hidden');
                    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    throw new Error(result.error || 'Prediction failed');
                }

            } catch (error) {
                console.error('Error:', error);
                errorMessage.textContent = error.message || 'An unexpected error occurred. Please try again.';
                errorSection.classList.remove('hidden');
                errorSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                btnText.textContent = 'Predict Production';
                spinner.classList.add('hidden');
            }
        });

        // Global variable to store chart instance
        let comparisonChartInstance = null;

        // Forecast Form Handler
        document.getElementById('forecastForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const forecastBtn = document.getElementById('forecastBtn');
            const forecastBtnText = document.getElementById('forecastBtnText');
            const forecastSpinner = document.getElementById('forecastSpinner');
            const forecastResults = document.getElementById('forecastResults');
            const forecastContent = document.getElementById('forecastContent');
            const forecastError = document.getElementById('forecastError');
            const forecastErrorMessage = document.getElementById('forecastErrorMessage');

            // Hide previous results/errors
            forecastResults.classList.add('hidden');
            forecastError.classList.add('hidden');

            // Destroy previous chart if exists
            if (comparisonChartInstance) {
                comparisonChartInstance.destroy();
                comparisonChartInstance = null;
            }

            // Show loading state
            forecastBtn.disabled = true;
            forecastBtnText.textContent = 'Generating Forecast...';
            forecastSpinner.classList.remove('hidden');

            try {
                const formData = new FormData(this);

                const requestPayload = {
                    municipality: formData.get('municipality'),
                    crop: formData.get('crop'),
                    forecast_years: 6  // Request 6 years (2025-2030)
                };

                console.log('Sending forecast request:', requestPayload);

                const response = await fetch('{{ route('admin.predictions.forecast') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestPayload)
                });

                const result = await response.json();

                console.log('Forecast API Response:', result);

                if (result.success || response.ok) {
                    const forecast = result.forecast || result.data || [];
                    const metadata = result.metadata || {};
                    const historical = result.historical || {};
                    const trend = result.trend || {};

                    // Fetch historical data from database
                    let historicalData = [];
                    try {
                        const historicalResponse = await fetch('{{ route('admin.predictions.historical') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                municipality: requestPayload.municipality,
                                crop: requestPayload.crop
                            })
                        });

                        if (historicalResponse.ok) {
                            const historicalResult = await historicalResponse.json();
                            historicalData = historicalResult.data || [];
                        }
                    } catch (error) {
                        console.error('Error fetching historical data:', error);
                    }

                    // Render comparison chart
                    renderComparisonChart(historicalData, forecast);

                    // Display summary card
                    let html = `
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-lg border-2 border-green-200 mb-6">
                            <h4 class="text-lg font-bold text-gray-800 mb-2">Forecast Summary</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                                <div>
                                    <p class="text-xs text-gray-600">Municipality</p>
                                    <p class="text-sm font-semibold text-gray-800">${result.municipality || requestPayload.municipality}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Crop</p>
                                    <p class="text-sm font-semibold text-gray-800">${result.crop || requestPayload.crop}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Forecast Years</p>
                                    <p class="text-sm font-semibold text-gray-800">${forecast.length} years</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-600">Trend</p>
                                    <p class="text-sm font-semibold ${trend.direction === 'increasing' ? 'text-green-600' : trend.direction === 'decreasing' ? 'text-red-600' : 'text-gray-600'}">
                                        ${trend.direction ? '↑ ' + trend.direction : 'N/A'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;

                    // Display forecast data table
                    html += `
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Predicted Production</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">YoY Growth</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Trend</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                    `;

                    // Get 2024 production from database historical data for accurate first row comparison
                    const last2024Data = historicalData.find(d => d.year === 2024);
                    const last2024Production = last2024Data ? parseFloat(last2024Data.production) : null;

                    // Calculate actual average trend from forecast data
                    let calculatedAvgTrend = null;
                    if (forecast.length >= 2) {
                        // Calculate CAGR (Compound Annual Growth Rate) from first to last forecast year
                        const firstProduction = parseFloat(forecast[0].production);
                        const lastProduction = parseFloat(forecast[forecast.length - 1].production);
                        const years = forecast.length - 1;

                        if (firstProduction > 0 && years > 0) {
                            // CAGR formula: ((End/Start)^(1/years) - 1) * 100
                            calculatedAvgTrend = (Math.pow(lastProduction / firstProduction, 1 / years) - 1) * 100;
                        }
                    }

                    // Use calculated trend, or fallback to API trend
                    const avgTrendValue = calculatedAvgTrend !== null ? calculatedAvgTrend :
                        (trend.growth_rate_percent ? parseFloat(trend.growth_rate_percent) : null);

                    forecast.forEach((item, index) => {
                        // Calculate growth rate from previous year
                        let growthRate = null;
                        let growthClass = 'text-gray-600';
                        let growthSymbol = '';

                        if (index > 0) {
                            const prevProduction = forecast[index - 1].production;
                            growthRate = ((item.production - prevProduction) / prevProduction * 100);
                            growthClass = growthRate >= 0 ? 'text-green-600' : 'text-red-600';
                            growthSymbol = growthRate >= 0 ? '+' : '';
                        } else if (last2024Production) {
                            // Compare first forecast year (2025) with actual 2024 database data
                            growthRate = ((item.production - last2024Production) / last2024Production * 100);
                            growthClass = growthRate >= 0 ? 'text-green-600' : 'text-red-600';
                            growthSymbol = growthRate >= 0 ? '+' : '';
                        } else if (historical.last_production) {
                            // Fallback to ML API historical data
                            growthRate = ((item.production - historical.last_production) / historical.last_production * 100);
                            growthClass = growthRate >= 0 ? 'text-green-600' : 'text-red-600';
                            growthSymbol = growthRate >= 0 ? '+' : '';
                        }

                        html += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.year}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                    ${parseFloat(item.production).toFixed(2)} MT
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium ${growthClass}">
                                    ${growthRate !== null ? growthSymbol + growthRate.toFixed(2) + '%' : 'Baseline'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                    ${avgTrendValue !== null ? (avgTrendValue >= 0 ? '+' : '') + avgTrendValue.toFixed(2) + '%/year' : 'N/A'}
                                </td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    // Add historical and trend statistics
                    html += `
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">Historical Context & Trend Analysis</h4>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                ${historical.average ? `
                                    <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                                        <p class="text-xs text-gray-600 mb-1">Historical Average</p>
                                        <p class="text-lg font-bold text-blue-700">${parseFloat(historical.average).toFixed(2)} MT</p>
                                        <p class="text-xs text-gray-500 mt-1">(${historical.years_available || 10} years)</p>
                                    </div>
                                ` : ''}
                                ${historical.last_production ? `
                                    <div class="bg-purple-50 border border-purple-200 p-4 rounded-lg">
                                        <p class="text-xs text-gray-600 mb-1">Last Year (${historical.last_year || 2024})</p>
                                        <p class="text-lg font-bold text-purple-700">${parseFloat(historical.last_production).toFixed(2)} MT</p>
                                    </div>
                                ` : ''}
                                ${trend.growth_rate_percent ? `
                                    <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                                        <p class="text-xs text-gray-600 mb-1">Annual Growth Rate</p>
                                        <p class="text-lg font-bold text-green-700">${parseFloat(trend.growth_rate_percent).toFixed(2)}%</p>
                                        <p class="text-xs text-gray-500 mt-1">per year</p>
                                    </div>
                                ` : ''}
                                ${trend.slope ? `
                                    <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg">
                                        <p class="text-xs text-gray-600 mb-1">Trend Slope</p>
                                        <p class="text-lg font-bold text-amber-700">${parseFloat(trend.slope).toFixed(2)}</p>
                                        <p class="text-xs text-gray-500 mt-1">MT/year</p>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;

                    // Add save to history notification
                    if (result.saved_to_history && result.saved_count > 0) {
                        html += `
                            <div class="mt-6 bg-indigo-50 border border-indigo-200 p-4 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-medium text-indigo-900">
                                            ${result.saved_count} forecast ${result.saved_count === 1 ? 'prediction' : 'predictions'} saved to prediction history
                                        </span>
                                    </div>
                                    <a href="{{ route('admin.predictions.history') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                        View History →
                                    </a>
                                </div>
                            </div>
                        `;
                    }

                    forecastContent.innerHTML = html;
                    forecastResults.classList.remove('hidden');
                    forecastResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    throw new Error(result.error || 'Forecast generation failed');
                }

            } catch (error) {
                console.error('Forecast Error:', error);
                forecastErrorMessage.textContent = error.message || 'An unexpected error occurred. Please try again.';
                forecastError.classList.remove('hidden');
                forecastError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } finally {
                // Reset button state
                forecastBtn.disabled = false;
                forecastBtnText.textContent = 'Generate Forecast';
                forecastSpinner.classList.add('hidden');
            }
        });

        /**
         * Render comparison chart with historical and forecast data
         */
        function renderComparisonChart(historicalData, forecastData) {
            const ctx = document.getElementById('comparisonChart');
            if (!ctx) return;

            // Prepare historical data (2015-2024)
            // Data is already aggregated by year from the backend
            const historicalYears = [];
            const historicalProduction = [];

            // Create a map of year -> production
            const yearlyDataMap = {};
            historicalData.forEach(item => {
                yearlyDataMap[item.year] = parseFloat(item.production || 0);
            });

            // Fill in all years from 2015-2024
            for (let year = 2015; year <= 2024; year++) {
                historicalYears.push(year);
                historicalProduction.push(yearlyDataMap[year] || null);
            }

            // Prepare forecast data (2025-2030)
            const forecastYears = forecastData.map(item => item.year);
            const forecastProduction = forecastData.map(item => parseFloat(item.production));

            // Combine all years for x-axis
            const allYears = [...historicalYears, ...forecastYears];

            // Create datasets with proper alignment
            const historicalDataset = new Array(allYears.length).fill(null);
            const forecastDataset = new Array(allYears.length).fill(null);

            // Fill historical values
            historicalYears.forEach((year, idx) => {
                const yearIndex = allYears.indexOf(year);
                historicalDataset[yearIndex] = historicalProduction[idx];
            });

            // Fill forecast values
            forecastYears.forEach((year, idx) => {
                const yearIndex = allYears.indexOf(year);
                forecastDataset[yearIndex] = forecastProduction[idx];
            });

            // Destroy existing chart
            if (comparisonChartInstance) {
                comparisonChartInstance.destroy();
            }

            // Create new chart
            comparisonChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: allYears,
                    datasets: [
                        {
                            label: 'Historical Production (2015-2024)',
                            data: historicalDataset,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.3,
                            spanGaps: false
                        },
                        {
                            label: 'Predicted Production (2025-2030)',
                            data: forecastDataset,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            tension: 0.3,
                            spanGaps: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toFixed(2) + ' MT';
                                    } else {
                                        label += 'No data';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Year',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: true,
                                drawBorder: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Production (MT)',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            },
                            beginAtZero: true,
                            grid: {
                                display: true,
                                drawBorder: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                callback: function (value) {
                                    return value.toFixed(0) + ' MT';
                                }
                            }
                        }
                    }
                }
            });
        }


    </script>
</x-admin-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crop Production Prediction & Forecasting') }}
        </h2>
    </x-slot>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Page Header for Farmers (Single view - Forecast only) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="border-b border-gray-200">
                    <div class="py-4 px-4 text-center">
                        <div class="flex items-center justify-center">
                            <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                            </svg>
                            <span class="text-lg font-semibold text-green-700">Future Production Forecast</span>
                        </div>
                        <span class="block text-sm text-gray-500 mt-1">See year-to-year production trends (2025-2030+)</span>
                    </div>
                </div>
            </div>

            <!-- Forecast Tab Content (Main view for Farmers) -->
            <div id="forecast-content" class="tab-content">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form id="forecastForm">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Municipality -->
                                <div>
                                    <label for="forecast_municipality" class="block text-sm font-medium text-gray-700">Municipality</label>
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
                                    <label for="forecast_crop" class="block text-sm font-medium text-gray-700">Crop</label>
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
                                    <label for="forecast_years" class="block text-sm font-medium text-gray-700">Forecast Period</label>
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
                                    <svg id="forecastSpinner" class="hidden animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>

                        <!-- Forecast Results Section -->
                        <div id="forecastResults" class="mt-8 hidden">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">Forecast Results</h3>
                            
                            <!-- Improved Comparison Chart - Farmer Friendly -->
                            <div class="bg-white border border-gray-200 rounded-lg p-4 md:p-6 mb-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                                    <h4 class="text-md font-semibold text-gray-800 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                        </svg>
                                        Production Over the Years
                                    </h4>
                                    <!-- Chart Type Toggle -->
                                    <div class="flex items-center mt-2 sm:mt-0 space-x-2">
                                        <span class="text-xs text-gray-500">View:</span>
                                        <button type="button" id="btnBarChart" onclick="switchChartType('bar')" class="chart-type-btn active px-3 py-1 text-xs rounded-full bg-green-600 text-white">
                                            Bar Chart
                                        </button>
                                        <button type="button" id="btnLineChart" onclick="switchChartType('line')" class="chart-type-btn px-3 py-1 text-xs rounded-full bg-gray-200 text-gray-700">
                                            Line Chart
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Clear Legend for Farmers -->
                                <div class="flex flex-wrap gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 rounded bg-blue-500 mr-2"></div>
                                        <span class="text-sm font-medium text-gray-700">📊 Actual Production (Nakaraan)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 rounded bg-green-500 mr-2" style="background: repeating-linear-gradient(45deg, #22c55e, #22c55e 2px, #86efac 2px, #86efac 4px);"></div>
                                        <span class="text-sm font-medium text-gray-700">🔮 Predicted Production (Hinaharap)</span>
                                    </div>
                                </div>
                                
                                <!-- Responsive Chart Container -->
                                <div class="relative" style="min-height: 250px; height: 300px;">
                                    <canvas id="comparisonChart"></canvas>
                                </div>
                                
                                <!-- Chart Help Text - Simple Version -->
                                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                    <div class="flex items-center justify-center space-x-6">
                                        <span class="text-sm">💡 <strong>How to read:</strong></span>
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 rounded bg-blue-500 mr-2"></div>
                                            <span class="text-sm font-medium text-gray-700">Historical</span>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 rounded bg-green-500 mr-2"></div>
                                            <span class="text-sm font-medium text-gray-700">Predicted</span>
                                        </div>
                                    </div>
                                </div>
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
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
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
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Chart type toggle buttons */
        .chart-type-btn {
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .chart-type-btn:hover {
            transform: scale(1.05);
        }
        .chart-type-btn.active {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Mobile responsive chart */
        @media (max-width: 640px) {
            #comparisonChart {
                min-height: 220px !important;
            }
            .forecast-table-mobile {
                font-size: 0.8rem;
            }
            .forecast-table-mobile th,
            .forecast-table-mobile td {
                padding: 0.5rem 0.25rem;
            }
        }
        
        /* Farmer-friendly production value highlight */
        .production-value {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 700;
        }
        .production-value.increasing {
            color: #16a34a;
        }
        .production-value.decreasing {
            color: #dc2626;
        }
    </style>

    <script>
        // Global variable to store chart instance and data
        let comparisonChartInstance = null;
        let chartData = { historical: [], forecast: [] };
        let currentChartType = 'bar';

        // Chart type switching function
        function switchChartType(type) {
            currentChartType = type;
            
            // Update button styles
            document.getElementById('btnBarChart').classList.toggle('active', type === 'bar');
            document.getElementById('btnBarChart').classList.toggle('bg-green-600', type === 'bar');
            document.getElementById('btnBarChart').classList.toggle('text-white', type === 'bar');
            document.getElementById('btnBarChart').classList.toggle('bg-gray-200', type !== 'bar');
            document.getElementById('btnBarChart').classList.toggle('text-gray-700', type !== 'bar');
            
            document.getElementById('btnLineChart').classList.toggle('active', type === 'line');
            document.getElementById('btnLineChart').classList.toggle('bg-green-600', type === 'line');
            document.getElementById('btnLineChart').classList.toggle('text-white', type === 'line');
            document.getElementById('btnLineChart').classList.toggle('bg-gray-200', type !== 'line');
            document.getElementById('btnLineChart').classList.toggle('text-gray-700', type !== 'line');
            
            // Re-render chart with stored data
            if (chartData.historical.length > 0 || chartData.forecast.length > 0) {
                renderComparisonChart(chartData.historical, chartData.forecast);
            }
        }

        // Forecast Form Handler
        document.getElementById('forecastForm').addEventListener('submit', async function(e) {
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
                
                const response = await fetch('{{ route('predictions.forecast') }}', {
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
                        const historicalResponse = await fetch('{{ route('predictions.historical') }}', {
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
                                        ${trend.direction === 'increasing' ? '↑ ' + trend.direction : trend.direction === 'decreasing' ? '↓ ' + trend.direction : 'N/A'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Display forecast data table - FARMER FRIENDLY VERSION
                    html += `
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Mobile-friendly card view -->
                            <div class="block md:hidden">
                                <div class="p-4 bg-gray-50 border-b border-gray-200">
                                    <h5 class="text-sm font-semibold text-gray-700 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                        </svg>
                                        Yearly Forecast (Per Taon)
                                    </h5>
                                </div>
                    `;
                    
                    forecast.forEach((item, index) => {
                        let growthRate = null;
                        let growthClass = 'text-gray-600';
                        let growthSymbol = '';
                        let growthIcon = '➖';
                        
                        if (index > 0) {
                            const prevProduction = forecast[index - 1].production;
                            growthRate = ((item.production - prevProduction) / prevProduction * 100);
                            growthClass = growthRate >= 0 ? 'text-green-600' : 'text-red-600';
                            growthSymbol = growthRate >= 0 ? '+' : '';
                            growthIcon = growthRate >= 0 ? '📈' : '📉';
                        } else if (historical.last_production) {
                            growthRate = ((item.production - historical.last_production) / historical.last_production * 100);
                            growthClass = growthRate >= 0 ? 'text-green-600' : 'text-red-600';
                            growthSymbol = growthRate >= 0 ? '+' : '';
                            growthIcon = growthRate >= 0 ? '📈' : '📉';
                        }
                        
                        // Mobile card view
                        html += `
                            <div class="p-4 border-b border-gray-100 ${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="text-lg font-bold text-gray-800">${item.year}</span>
                                        <p class="text-xs text-gray-500 mt-0.5">Predicted Production</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-green-700">${parseFloat(item.production).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                        <span class="text-xs text-gray-600 block">metric tons</span>
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="text-xs text-gray-500">Growth vs Last Year:</span>
                                    <span class="text-sm font-semibold ${growthClass}">
                                        ${growthIcon} ${growthRate !== null ? growthSymbol + growthRate.toFixed(2) + '%' : 'Base Year'}
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += `</div>`;
                    
                    // Desktop table view
                    html += `
                            <table class="hidden md:table min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-green-50 to-emerald-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                            📅 Taon (Year)
                                        </th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                                            🌾 Predicted Production
                                        </th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                                            📊 YoY Growth
                                        </th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                                            📈 Avg. Trend
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                    `;
                    
                    forecast.forEach((item, index) => {
                        // Calculate growth rate from previous year
                        let growthRate = null;
                        let growthClass = 'text-gray-600';
                        let growthSymbol = '';
                        let growthBg = '';
                        
                        if (index > 0) {
                            const prevProduction = forecast[index - 1].production;
                            growthRate = ((item.production - prevProduction) / prevProduction * 100);
                            growthClass = growthRate >= 0 ? 'text-green-600 font-bold' : 'text-red-600 font-bold';
                            growthSymbol = growthRate >= 0 ? '↑ +' : '↓ ';
                            growthBg = growthRate >= 0 ? 'bg-green-50' : 'bg-red-50';
                        } else if (historical.last_production) {
                            // Compare first forecast year with last historical year
                            growthRate = ((item.production - historical.last_production) / historical.last_production * 100);
                            growthClass = growthRate >= 0 ? 'text-green-600 font-bold' : 'text-red-600 font-bold';
                            growthSymbol = growthRate >= 0 ? '↑ +' : '↓ ';
                            growthBg = growthRate >= 0 ? 'bg-green-50' : 'bg-red-50';
                        }
                        
                        html += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'} hover:bg-blue-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-lg font-bold text-gray-900">${item.year}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-lg font-bold text-green-700">${parseFloat(item.production).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                    <span class="text-xs text-gray-500 block">metric tons</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm ${growthClass} ${growthBg}">
                                        ${growthRate !== null ? growthSymbol + Math.abs(growthRate).toFixed(2) + '%' : '🔵 Baseline'}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                    ${trend.growth_rate_percent ? parseFloat(trend.growth_rate_percent).toFixed(2) + '%/year' : 'N/A'}
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
                                        <p class="text-lg font-bold text-blue-700">${parseFloat(historical.average).toFixed(2)} mt</p>
                                        <p class="text-xs text-gray-500 mt-1">(${historical.years_available || 10} years)</p>
                                    </div>
                                ` : ''}
                                ${historical.last_production ? `
                                    <div class="bg-purple-50 border border-purple-200 p-4 rounded-lg">
                                        <p class="text-xs text-gray-600 mb-1">Last Year (${historical.last_year || 2024})</p>
                                        <p class="text-lg font-bold text-purple-700">${parseFloat(historical.last_production).toFixed(2)} mt</p>
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
                                        <p class="text-xs text-gray-500 mt-1">mt/year</p>
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
                                            ${result.saved_count} forecast ${result.saved_count === 1 ? 'prediction' : 'predictions'} saved to your history
                                        </span>
                                    </div>
                                    <a href="{{ route('predictions.history') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
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
         * FARMER-FRIENDLY VERSION - Clear visuals, readable on mobile
         */
        function renderComparisonChart(historicalData, forecastData) {
            const ctx = document.getElementById('comparisonChart');
            if (!ctx) return;
            
            // Store data for chart type switching
            chartData.historical = historicalData;
            chartData.forecast = forecastData;
            
            // Prepare historical data (2015-2024)
            const yearlyDataMap = {};
            historicalData.forEach(item => {
                yearlyDataMap[item.year] = parseFloat(item.production || 0);
            });
            
            // Only include years that have data for cleaner chart
            const historicalYears = [];
            const historicalProduction = [];
            
            for (let year = 2015; year <= 2024; year++) {
                if (yearlyDataMap[year] && yearlyDataMap[year] > 0) {
                    historicalYears.push(year);
                    historicalProduction.push(yearlyDataMap[year]);
                }
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
            
            // Detect if mobile
            const isMobile = window.innerWidth < 768;
            
            // Create new chart - FARMER FRIENDLY VERSION
            comparisonChartInstance = new Chart(ctx, {
                type: currentChartType,
                data: {
                    labels: allYears.map(y => isMobile ? "'" + String(y).slice(-2) : y),
                    datasets: [
                        {
                            label: 'Actual (Nakaraan)',
                            data: historicalDataset,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: currentChartType === 'bar' ? 'rgba(59, 130, 246, 0.8)' : 'rgba(59, 130, 246, 0.1)',
                            borderWidth: currentChartType === 'bar' ? 0 : 3,
                            pointRadius: currentChartType === 'line' ? 6 : 0,
                            pointHoverRadius: 8,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            tension: 0.3,
                            spanGaps: false,
                            borderRadius: currentChartType === 'bar' ? 4 : 0,
                            barPercentage: 0.8,
                            categoryPercentage: 0.9
                        },
                        {
                            label: 'Predicted (Hinaharap)',
                            data: forecastDataset,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: currentChartType === 'bar' 
                                ? createDiagonalPattern('rgba(34, 197, 94, 0.8)')
                                : 'rgba(34, 197, 94, 0.1)',
                            borderWidth: currentChartType === 'bar' ? 2 : 3,
                            borderDash: currentChartType === 'line' ? [5, 5] : [],
                            pointRadius: currentChartType === 'line' ? 6 : 0,
                            pointHoverRadius: 8,
                            pointBackgroundColor: 'rgb(34, 197, 94)',
                            pointStyle: 'triangle',
                            tension: 0.3,
                            spanGaps: false,
                            borderRadius: currentChartType === 'bar' ? 4 : 0,
                            barPercentage: 0.8,
                            categoryPercentage: 0.9
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false, // We use custom legend above
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.85)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                title: function(context) {
                                    const yearLabel = context[0].label;
                                    const fullYear = yearLabel.startsWith("'") ? '20' + yearLabel.slice(1) : yearLabel;
                                    return 'Taon ' + fullYear;
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (context.parsed.y !== null) {
                                        const value = context.parsed.y.toLocaleString('en-PH', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                        return label + ': ' + value + ' metric tons';
                                    }
                                    return label + ': Walang data';
                                },
                                afterBody: function(context) {
                                    const items = context.filter(c => c.parsed.y !== null);
                                    if (items.length === 2) {
                                        const diff = items[1].parsed.y - items[0].parsed.y;
                                        const pct = ((diff / items[0].parsed.y) * 100).toFixed(1);
                                        const arrow = diff >= 0 ? '↑' : '↓';
                                        return '\n' + arrow + ' Pagbabago: ' + pct + '%';
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Taon (Year)',
                                font: {
                                    size: isMobile ? 12 : 14,
                                    weight: 'bold'
                                },
                                color: '#374151'
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: isMobile ? 10 : 12,
                                    weight: '500'
                                },
                                color: '#4B5563'
                            }
                        },
                        y: {
                            title: {
                                display: !isMobile,
                                text: 'Production (metric tons)',
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: '#374151'
                            },
                            beginAtZero: false,
                            grace: '10%',
                            grid: {
                                color: 'rgba(0, 0, 0, 0.06)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: isMobile ? 10 : 12
                                },
                                color: '#6B7280',
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return (value / 1000).toFixed(1) + 'K';
                                    }
                                    return value.toFixed(0);
                                },
                                maxTicksLimit: 6
                            }
                        }
                    },
                    // Add visual divider annotation (where history ends and prediction starts)
                    animation: {
                        duration: 800,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }

        // Helper function to create diagonal stripe pattern for predicted bars
        function createDiagonalPattern(color) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = 10;
            canvas.height = 10;
            
            ctx.fillStyle = color;
            ctx.fillRect(0, 0, 10, 10);
            
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(0, 10);
            ctx.lineTo(10, 0);
            ctx.stroke();
            
            return ctx.createPattern(canvas, 'repeat');
        }

        // Debounce function for resize handler
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Re-render chart on window resize for responsive labels
        window.addEventListener('resize', debounce(() => {
            if (chartData.historical.length > 0 || chartData.forecast.length > 0) {
                renderComparisonChart(chartData.historical, chartData.forecast);
            }
        }, 250));

        
    </script>
</x-app-layout>

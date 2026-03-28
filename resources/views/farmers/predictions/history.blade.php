<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
            {{ __('Prediction History') }}
        </h2>
    </x-slot>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <div class="py-4 lg:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 lg:mb-6">
                <div class="p-4 lg:p-6">
                    <form method="GET" action="{{ route('predictions.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 lg:gap-4">
                        <div>
                            <label for="crop" class="block text-xs lg:text-sm font-medium text-gray-700">Crop</label>
                            <select id="crop" name="crop" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm lg:text-base">
                                <option value="">All Crops</option>
                                @foreach($crops as $crop)
                                    <option value="{{ $crop }}" {{ request('crop') == $crop ? 'selected' : '' }}>
                                        {{ $crop }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="municipality" class="block text-xs lg:text-sm font-medium text-gray-700">Municipality</label>
                            <select id="municipality" name="municipality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm lg:text-base">
                                <option value="">All Municipalities</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality }}" {{ request('municipality') == $municipality ? 'selected' : '' }}>
                                        {{ $municipality }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-xs lg:text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm lg:text-base">
                                <option value="">All Status</option>
                                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>

                        <div>
                            <label for="prediction_type" class="block text-xs lg:text-sm font-medium text-gray-700">Type</label>
                            <select id="prediction_type" name="prediction_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm lg:text-base">
                                <option value="">All Types</option>
                                <option value="regular" {{ request('prediction_type') == 'regular' ? 'selected' : '' }}>Regular Prediction</option>
                                <option value="forecast" {{ request('prediction_type') == 'forecast' ? 'selected' : '' }}>Forecast</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-xs lg:text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" 
                                placeholder="mm/dd/yyyy"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm lg:text-base">
                        </div>

                        <div>
                            <label for="date_to" class="block text-xs lg:text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" 
                                placeholder="mm/dd/yyyy"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm lg:text-base">
                        </div>

                        <div class="sm:col-span-2 lg:col-span-6 flex flex-col sm:flex-row gap-2">
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                Filter
                            </button>
                            <button type="button" onclick="window.location.href='{{ route('predictions.history') }}'" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Clear Filters
                            </button>
                            <a href="{{ route('predictions.predict.form') }}" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Prediction
                            </a>
                            <button type="button" onclick="openClearHistoryModal()" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Clear History
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 lg:p-6">
                    @if($predictions->count() > 0)
                        <div class="overflow-x-auto -mx-4 sm:mx-0">
                            <table class="min-w-full divide-y divide-gray-200 text-sm lg:text-base">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Municipality</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Farm Type</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Predicted (MT)</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Difference</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Confidence</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-3 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($predictions as $prediction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm text-gray-900">
                                                @php
                                                    $createdAt = $prediction->created_at instanceof \Carbon\Carbon 
                                                        ? $prediction->created_at 
                                                        : \Carbon\Carbon::parse($prediction->created_at);
                                                @endphp
                                                <div class="lg:hidden">{{ $createdAt->format('M d, Y') }}</div>
                                                <div class="hidden lg:block">{{ $createdAt->format('M d, Y H:i') }}</div>
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm">
                                                @if($prediction->farm_type === 'Forecast')
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                                        </svg>
                                                        Forecast
                                                        @if(isset($prediction->is_forecast_batch) && $prediction->is_forecast_batch)
                                                            <span class="ml-1 text-purple-600">({{ $prediction->year_count }} yrs)</span>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Regular
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm font-medium text-gray-900">
                                                {{ $prediction->crop }}
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm text-gray-600 hidden sm:table-cell">
                                                {{ $prediction->municipality }}
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm text-gray-600 hidden md:table-cell">
                                                @if(isset($prediction->is_forecast_batch) && $prediction->is_forecast_batch)
                                                    {{ $prediction->min_year }}-{{ $prediction->max_year }}
                                                @else
                                                    {{ $prediction->farm_type }}
                                                @endif
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm font-semibold text-indigo-600">
                                                {{ number_format($prediction->predicted_production_mt, 2) }}
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm hidden lg:table-cell">
                                                @if(isset($prediction->is_forecast_batch) && $prediction->is_forecast_batch)
                                                    <span class="text-gray-400">-</span>
                                                @else
                                                    <span class="{{ $prediction->difference >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                                        {{ $prediction->difference >= 0 ? '+' : '' }}{{ number_format($prediction->difference ?? 0, 2) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm text-gray-600 hidden lg:table-cell">
                                                @if(isset($prediction->is_forecast_batch) && $prediction->is_forecast_batch)
                                                    <span class="text-gray-400">-</span>
                                                @else
                                                    {{ number_format($prediction->confidence_score ?? 0, 4) }}
                                                @endif
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap">
                                                @if($prediction->status === 'success')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        Success
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        Failed
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 lg:px-6 py-3 lg:py-4 whitespace-nowrap text-xs lg:text-sm">
                                                @if(isset($prediction->is_forecast_batch) && $prediction->is_forecast_batch && $prediction->batch_id)
                                                    <button type="button" 
                                                        onclick="viewForecastChart('{{ $prediction->batch_id }}')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        View
                                                    </button>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 lg:mt-6">
                            {{ $predictions->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 lg:py-12">
                            <svg class="mx-auto h-10 w-10 lg:h-12 lg:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm lg:text-base font-medium text-gray-900">No predictions found</h3>
                            <p class="mt-1 text-xs lg:text-sm text-gray-500">Get started by creating your first prediction.</p>
                            <div class="mt-4 lg:mt-6">
                                <a href="{{ route('predictions.predict.form') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                    Create Prediction
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Forecast Chart Modal -->
    <div id="forecastModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeForecastModal()"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Forecast Results
                            </h3>
                            <p class="mt-1 text-sm text-gray-500" id="modal-subtitle">
                                Loading...
                            </p>
                        </div>
                        <button type="button" onclick="closeForecastModal()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Chart Container -->
                    <div class="mt-4">
                        <div id="chartLoading" class="flex items-center justify-center py-12">
                            <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="ml-2 text-gray-600">Loading forecast data...</span>
                        </div>
                        <div id="chartContainer" class="hidden">
                            <canvas id="forecastChart" height="300"></canvas>
                        </div>
                        <div id="chartError" class="hidden text-center py-8 text-red-600">
                            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="mt-2" id="chartErrorMessage">Failed to load forecast data</p>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div id="dataTableContainer" class="mt-6 hidden">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Forecast Details</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Year</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Production (MT)</th>
                                    </tr>
                                </thead>
                                <tbody id="forecastTableBody" class="bg-white divide-y divide-gray-200">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeForecastModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let forecastChart = null;

        function viewForecastChart(batchId) {
            // Show modal
            document.getElementById('forecastModal').classList.remove('hidden');
            document.getElementById('chartLoading').classList.remove('hidden');
            document.getElementById('chartContainer').classList.add('hidden');
            document.getElementById('chartError').classList.add('hidden');
            document.getElementById('dataTableContainer').classList.add('hidden');
            document.getElementById('modal-subtitle').textContent = 'Loading...';

            // Fetch forecast data
            fetch(`{{ route('predictions.forecast-batch') }}?batch_id=${encodeURIComponent(batchId)}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('chartLoading').classList.add('hidden');
                    
                    if (data.success) {
                        document.getElementById('modal-subtitle').textContent = 
                            `${data.data.crop} in ${data.data.municipality} - Generated on ${data.data.created_at}`;
                        
                        // Show chart
                        document.getElementById('chartContainer').classList.remove('hidden');
                        document.getElementById('dataTableContainer').classList.remove('hidden');
                        
                        // Prepare chart data
                        const labels = data.data.predictions.map(p => p.year);
                        const values = data.data.predictions.map(p => p.production);
                        
                        // Destroy existing chart if any
                        if (forecastChart) {
                            forecastChart.destroy();
                        }
                        
                        // Create chart
                        const ctx = document.getElementById('forecastChart').getContext('2d');
                        forecastChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Forecasted Production (MT)',
                                    data: values,
                                    borderColor: 'rgb(147, 51, 234)',
                                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                                    fill: true,
                                    tension: 0.3,
                                    pointBackgroundColor: 'rgb(147, 51, 234)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    pointRadius: 6,
                                    pointHoverRadius: 8
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    },
                                    title: {
                                        display: true,
                                        text: `${data.data.crop} Production Forecast - ${data.data.municipality}`,
                                        font: {
                                            size: 16,
                                            weight: 'bold'
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return `Production: ${context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} MT`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        title: {
                                            display: true,
                                            text: 'Production (Metric Tons)'
                                        },
                                        ticks: {
                                            callback: function(value) {
                                                return value.toLocaleString();
                                            }
                                        }
                                    },
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Year'
                                        }
                                    }
                                }
                            }
                        });
                        
                        // Populate table
                        const tableBody = document.getElementById('forecastTableBody');
                        tableBody.innerHTML = '';
                        data.data.predictions.forEach(p => {
                            const row = document.createElement('tr');
                            row.className = 'hover:bg-gray-50';
                            row.innerHTML = `
                                <td class="px-4 py-2 text-gray-900 font-medium">${p.year}</td>
                                <td class="px-4 py-2 text-indigo-600 font-semibold">${p.production.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        document.getElementById('chartError').classList.remove('hidden');
                        document.getElementById('chartErrorMessage').textContent = data.error || 'Failed to load forecast data';
                    }
                })
                .catch(error => {
                    document.getElementById('chartLoading').classList.add('hidden');
                    document.getElementById('chartError').classList.remove('hidden');
                    document.getElementById('chartErrorMessage').textContent = 'Network error: ' + error.message;
                });
        }

        function closeForecastModal() {
            document.getElementById('forecastModal').classList.add('hidden');
            if (forecastChart) {
                forecastChart.destroy();
                forecastChart = null;
            }
        }

        // Clear History Modal Functions
        function openClearHistoryModal() {
            document.getElementById('clearHistoryModal').classList.remove('hidden');
        }

        function closeClearHistoryModal() {
            document.getElementById('clearHistoryModal').classList.add('hidden');
        }

        function clearHistory() {
            const btn = document.getElementById('confirmClearBtn');
            const btnText = document.getElementById('clearBtnText');
            const spinner = document.getElementById('clearSpinner');
            
            // Disable button and show spinner
            btn.disabled = true;
            btnText.textContent = 'Clearing...';
            spinner.classList.remove('hidden');
            
            fetch('{{ route('predictions.clear-history') }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show empty state
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to clear history'));
                    btn.disabled = false;
                    btnText.textContent = 'Yes, Clear All';
                    spinner.classList.add('hidden');
                }
            })
            .catch(error => {
                alert('Network error: ' + error.message);
                btn.disabled = false;
                btnText.textContent = 'Yes, Clear All';
                spinner.classList.add('hidden');
            });
        }

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeForecastModal();
                closeClearHistoryModal();
            }
        });
    </script>

    <!-- Clear History Confirmation Modal -->
    <div id="clearHistoryModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeClearHistoryModal()"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Clear Prediction History
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to clear all your prediction history? This action cannot be undone and will permanently delete all your predictions and forecasts.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirmClearBtn" onclick="clearHistory()" class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <svg id="clearSpinner" class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="clearBtnText">Yes, Clear All</span>
                    </button>
                    <button type="button" onclick="closeClearHistoryModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

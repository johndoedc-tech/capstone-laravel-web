<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
            {{ __('Crop Data Management') }}
        </h2>
        <p class="text-xs lg:text-sm text-gray-600 mt-1">View and manage imported crop data</p>
    </x-slot>

    <div class="py-4 lg:py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-4 lg:mb-6">
                <!-- Total Records -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs lg:text-sm text-gray-600 mb-1">Total Records</p>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-800">{{ number_format($totalRecords) }}</h3>
                        </div>
                        <div class="bg-blue-100 p-2 lg:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Municipalities -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs lg:text-sm text-gray-600 mb-1">Municipalities</p>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-800">{{ $municipalitiesCount }}</h3>
                        </div>
                        <div class="bg-green-100 p-2 lg:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Crop Types -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs lg:text-sm text-gray-600 mb-1">Crop Types</p>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-800">{{ $cropTypesCount }}</h3>
                        </div>
                        <div class="bg-yellow-100 p-2 lg:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Years Covered -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 lg:p-6">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs lg:text-sm text-gray-600 mb-1">Years Covered</p>
                            <h3 class="text-lg lg:text-2xl font-bold text-gray-800">{{ $minYear ?? 'N/A' }} - {{ $maxYear ?? 'N/A' }}</h3>
                        </div>
                        <div class="bg-purple-100 p-2 lg:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 lg:mb-6 p-4 lg:p-6">
                <div class="flex flex-col sm:flex-row flex-wrap gap-2 lg:gap-3">
                    <!-- Import Button -->
                    <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                            class="bg-primary hover:bg-primary-700 text-white px-4 lg:px-6 py-2 lg:py-2.5 rounded-lg font-medium flex items-center justify-center gap-2 transition text-sm lg:text-base">
                        <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Import Data
                    </button>

                    <!-- Add Single Data Button -->
                    <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 lg:px-6 py-2 lg:py-2.5 rounded-lg font-medium flex items-center justify-center gap-2 transition text-sm lg:text-base">
                        <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Single Data
                    </button>

                    <!-- Statistics Button -->
                    <button onclick="window.location.href='#statistics'"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 lg:px-6 py-2 lg:py-2.5 rounded-lg font-medium flex items-center justify-center gap-2 transition text-sm lg:text-base">
                        <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Statistics
                    </button>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 lg:mb-6 p-4 lg:p-6">
                <form method="GET" action="{{ route('admin.crop-data.index') }}" class="space-y-3 lg:space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
                        <!-- Search -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Enter search item..." 
                                   class="w-full px-3 lg:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm lg:text-base">
                        </div>

                        <!-- View (placeholder for future use) -->
                        <div>
                            <label class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">View</label>
                            <select class="w-full px-3 lg:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm lg:text-base">
                                <option>All Views</option>
                            </select>
                        </div>

                        <!-- Municipality Filter -->
                        <div>
                            <label class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Municipality</label>
                            <select name="municipality" class="w-full px-3 lg:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm lg:text-base">
                                <option value="">All Municipalities</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality }}" {{ request('municipality') == $municipality ? 'selected' : '' }}>
                                        {{ $municipality }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Crop Filter -->
                        <div>
                            <label class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Crop</label>
                            <select name="crop" class="w-full px-3 lg:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm lg:text-base">
                                <option value="">All Crops</option>
                                @foreach($crops as $crop)
                                    <option value="{{ $crop }}" {{ request('crop') == $crop ? 'selected' : '' }}>
                                        {{ $crop }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Filter Button -->
                    <div class="flex justify-end">
                        <button type="submit" class="bg-primary hover:bg-primary-700 text-white px-4 lg:px-6 py-2 rounded-lg font-medium flex items-center gap-2 transition text-sm lg:text-base w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-primary-100 border-l-4 border-primary-500 text-primary-700 p-4 mb-6 rounded" role="alert">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Data Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    @if($cropData->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200 text-sm lg:text-base">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Municipality</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Farm Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area Harvested (ha)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production (mt)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productivity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($cropData as $data)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data->municipality }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $data->crop }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $data->farm_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $data->year }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $data->month }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($data->area_harvested, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($data->production, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($data->productivity, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <form method="POST" action="{{ route('admin.crop-data.destroy', $data->id) }}" onsubmit="return confirm('Delete this record?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $cropData->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No crop data found</h3>
                            <p class="mt-1 text-sm text-gray-500">Import crop data →</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 px-4">
        <div class="relative top-10 lg:top-20 mx-auto p-4 lg:p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Import Crop Data</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.crop-data.import') }}" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Max file size: 10MB</p>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="clear_existing" class="rounded border-gray-300 text-primary focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-700">Clear existing data before import</span>
                    </label>
                </div>

                <!-- Progress indicator -->
                <div id="importProgress" class="hidden mb-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center gap-3">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-800">Importing data...</p>
                                <p class="text-xs text-blue-600">This may take a few minutes for large files. Please wait.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" id="importBtn" class="flex-1 bg-primary hover:bg-primary-700 text-white py-2 rounded-lg font-medium transition">
                        Import
                    </button>
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>
            </form>

            <script>
                document.getElementById('importForm').addEventListener('submit', function() {
                    document.getElementById('importProgress').classList.remove('hidden');
                    document.getElementById('importBtn').disabled = true;
                    document.getElementById('importBtn').classList.add('opacity-50', 'cursor-not-allowed');
                    document.getElementById('importBtn').textContent = 'Importing...';
                });
            </script>
        </div>
    </div>

    <!-- Add Single Data Modal -->
    <div id="addModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 px-4">
        <div class="relative top-10 mx-auto p-4 lg:p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white my-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Single Crop Data</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.crop-data.store') }}">
                @csrf
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Municipality *</label>
                        <input type="text" name="municipality" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Farm Type *</label>
                        <select name="farm_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option value="IRRIGATED">IRRIGATED</option>
                            <option value="RAINFED">RAINFED</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year *</label>
                        <input type="number" name="year" required min="2000" max="2100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Month *</label>
                        <select name="month" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                            <option>JAN</option><option>FEB</option><option>MAR</option><option>APR</option>
                            <option>MAY</option><option>JUN</option><option>JUL</option><option>AUG</option>
                            <option>SEP</option><option>OCT</option><option>NOV</option><option>DEC</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Crop *</label>
                        <input type="text" name="crop" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Area Planted (ha)</label>
                        <input type="number" step="0.01" name="area_planted" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Area Harvested (ha)</label>
                        <input type="number" step="0.01" name="area_harvested" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Production (mt)</label>
                        <input type="number" step="0.01" name="production" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Productivity (mt/ha)</label>
                        <input type="number" step="0.01" name="productivity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary-700 text-white py-2 rounded-lg font-medium transition">
                        Add Data
                    </button>
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>

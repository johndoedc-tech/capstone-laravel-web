<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
                    {{ __('Prediction Analytics Report') }}
                </h2>
                <p class="text-xs lg:text-sm text-gray-600 mt-1">Analysis of prediction performance and user activity</p>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Back to Reports
            </a>
        </div>
    </x-slot>

    <div class="py-4 lg:py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Analytics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-4 lg:mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600">Total Predictions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($analytics['total']) }}</p>
                    <p class="text-xs text-green-600">{{ $analytics['successful'] }} successful</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-xs text-gray-600">Success Rate</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($analytics['success_rate'], 1) }}%</p>
                    <p class="text-xs text-gray-500">{{ $analytics['failed'] }} failed</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                    <p class="text-xs text-gray-600">Avg Confidence</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format(($analytics['avg_confidence'] ?? 0) * 100, 1) }}%</p>
                    <p class="text-xs text-gray-500">Model accuracy</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-orange-500">
                    <p class="text-xs text-gray-600">Total Predicted</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($analytics['total_predicted_production'], 0) }}</p>
                    <p class="text-xs text-gray-500">Metric Tons</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 lg:mb-6">
                <div class="p-4 lg:p-6">
                    <form method="GET" action="{{ route('admin.reports.prediction-analytics') }}" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Municipality</label>
                                <select name="municipality" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality }}" {{ request('municipality') == $municipality ? 'selected' : '' }}>{{ $municipality }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Crop</label>
                                <select name="crop" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All</option>
                                    @foreach($crops as $crop)
                                        <option value="{{ $crop }}" {{ request('crop') == $crop ? 'selected' : '' }}>{{ $crop }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Filter
                                </button>
                            </div>
                        </div>

                        <!-- Export Buttons -->
                        <div class="flex gap-2 pt-4 border-t">
                            <button type="submit" name="format" value="pdf" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export PDF
                            </button>
                            <button type="submit" name="format" value="csv" class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-primary-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Top Statistics Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-4 lg:mb-6">
                <!-- Top Crops -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 lg:p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Top Predicted Crops</h3>
                        <div class="space-y-2">
                            @foreach($topCrops->take(5) as $crop)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">{{ $crop->crop }}</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($crop->count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Top Municipalities -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 lg:p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Top Municipalities</h3>
                        <div class="space-y-2">
                            @foreach($topMunicipalities->take(5) as $municipality)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">{{ $municipality->municipality }}</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($municipality->count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 lg:p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Most Active Users</h3>
                        <div class="space-y-2">
                            @foreach($activeUsers->take(5) as $userActivity)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">{{ $userActivity->user->name ?? 'Unknown' }}</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($userActivity->count) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Predictions Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 lg:p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-4">Recent Predictions</h3>
                    
                    @if($predictions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Municipality</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Crop</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Predicted (MT)</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Confidence</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($predictions as $prediction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-xs">{{ $prediction->created_at->format('M d, Y') }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs">{{ $prediction->user->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs">{{ $prediction->municipality }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs font-medium">{{ $prediction->crop }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-right">{{ number_format($prediction->predicted_production_mt, 2) }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <span class="text-xs">{{ number_format(($prediction->confidence_score ?? 0) * 100, 1) }}%</span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                @if($prediction->status === 'success')
                                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Success</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Failed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            {{ $predictions->links() }}
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <p>No predictions found</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>

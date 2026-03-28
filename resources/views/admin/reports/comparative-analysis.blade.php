<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
                    {{ __('Comparative Analysis Report') }}
                </h2>
                <p class="text-xs lg:text-sm text-gray-600 mt-1">Compare metrics across municipalities, crops, or years</p>
            </div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                ← Back to Reports
            </a>
        </div>
    </x-slot>

    <div class="py-4 lg:py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 lg:mb-6">
                <div class="p-4 lg:p-6">
                    <form method="GET" action="{{ route('admin.reports.comparative-analysis') }}" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Compare By</label>
                                <select name="compare_by" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="municipality" {{ request('compare_by', 'municipality') == 'municipality' ? 'selected' : '' }}>Municipality</option>
                                    <option value="crop" {{ request('compare_by') == 'crop' ? 'selected' : '' }}>Crop</option>
                                    <option value="year" {{ request('compare_by') == 'year' ? 'selected' : '' }}>Year</option>
                                </select>
                            </div>
                            
                            @if(request('compare_by') !== 'year')
                            <div>
                                <label class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Year</label>
                                <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Years</option>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            @if(request('compare_by') !== 'municipality')
                            <div>
                                <label class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Municipality</label>
                                <select name="municipality" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality }}" {{ request('municipality') == $municipality ? 'selected' : '' }}>{{ $municipality }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            @if(request('compare_by') !== 'crop')
                            <div>
                                <label class="block text-xs lg:text-sm font-medium text-gray-700 mb-1">Crop</label>
                                <select name="crop" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All</option>
                                    @foreach($crops as $crop)
                                        <option value="{{ $crop }}" {{ request('crop') == $crop ? 'selected' : '' }}>{{ $crop }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            <div class="flex items-end gap-2">
                                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Compare
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
                        </div>
                    </form>
                </div>
            </div>

            <!-- Comparison Results -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 lg:p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-4">
                        Comparison by {{ ucfirst($compareBy) }}
                    </h3>
                    
                    @if($data->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ ucfirst($compareBy) }}
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Production (MT)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Area (Ha)
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Productivity (MT/Ha)
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Records
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            % of Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @php
                                        $totalProduction = $data->sum('total_production');
                                    @endphp
                                    @foreach($data as $index => $row)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <div class="flex items-center">
                                                    <span class="mr-2 text-sm font-bold text-gray-900">
                                                        {{ $index + 1 }}.
                                                    </span>
                                                    {{ $row->$compareBy }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-semibold">
                                                {{ number_format($row->total_production, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                                {{ number_format($row->total_area, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600">
                                                {{ number_format($row->avg_productivity, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                                                {{ number_format($row->record_count) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                <div class="flex items-center justify-end">
                                                    @php
                                                        $percentage = $totalProduction > 0 ? ($row->total_production / $totalProduction * 100) : 0;
                                                    @endphp
                                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <span class="text-gray-900 font-medium">{{ number_format($percentage, 1) }}%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50 font-semibold">
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">TOTAL</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($data->sum('total_production'), 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($data->sum('total_area'), 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($data->avg('avg_productivity'), 2) }}</td>
                                        <td class="px-6 py-4 text-sm text-center text-gray-900">{{ number_format($data->sum('record_count')) }}</td>
                                        <td class="px-6 py-4 text-sm text-right text-gray-900">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">No data found</p>
                            <p class="text-sm mt-1">Try adjusting your filters</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-admin-layout>

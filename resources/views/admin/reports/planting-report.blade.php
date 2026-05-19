<x-admin-layout>
    @php
        $query = request()->except(['format', 'page']);
        $csvUrl = route('admin.reports.planting-report', array_merge($query, ['format' => 'csv']));
        $pdfUrl = route('admin.reports.planting-report', array_merge($query, ['format' => 'pdf']));
        $plantedPercent = $summary['total_records'] > 0 ? round(($summary['planted_records'] / $summary['total_records']) * 100, 1) : 0;
        $damagedPercent = $summary['total_records'] > 0 ? round(($summary['damaged_records'] / $summary['total_records']) * 100, 1) : 0;
        $statusClasses = [
            'planted' => 'bg-blue-100 text-blue-700',
            'damaged' => 'bg-orange-100 text-orange-700',
            'harvested' => 'bg-emerald-100 text-emerald-700',
        ];
    @endphp

    <div class="py-4 lg:py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-full mx-auto space-y-4 lg:space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <section class="xl:col-span-4 rounded-lg bg-white border border-gray-200 shadow-sm p-4 lg:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Record Status</p>
                            <h2 class="mt-1 text-base font-semibold text-gray-900">Planting report</h2>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">{{ number_format($summary['total_records']) }} total</span>
                    </div>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-[9rem_1fr] gap-5 items-center">
                        <div class="mx-auto flex h-32 w-32 items-center justify-center rounded-full" style="background: conic-gradient(#10b981 0 {{ $plantedPercent }}%, #fb923c {{ $plantedPercent }}% 100%);">
                            <div class="flex h-20 w-20 flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-2xl font-bold text-gray-900">{{ number_format($summary['total_records']) }}</span>
                                <span class="text-[10px] uppercase text-gray-500">records</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="rounded-lg bg-emerald-50 px-3 py-2">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="flex items-center gap-2 text-sm font-medium text-emerald-800"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Planted</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($summary['planted_records']) }}</span>
                                </div>
                                <p class="mt-1 text-right text-[11px] text-gray-500">{{ number_format($plantedPercent, 1) }}%</p>
                            </div>
                            <div class="rounded-lg bg-orange-50 px-3 py-2">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="flex items-center gap-2 text-sm font-medium text-orange-800"><span class="h-2 w-2 rounded-full bg-orange-400"></span>Damaged</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($summary['damaged_records']) }}</span>
                                </div>
                                <p class="mt-1 text-right text-[11px] text-gray-500">{{ number_format($damagedPercent, 1) }}%</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="xl:col-span-4 space-y-4">
                    <div class="rounded-lg bg-white border border-gray-200 shadow-sm p-4 lg:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Area Planted</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($summary['total_area_ha'], 2) }} <span class="text-sm font-semibold text-gray-500">ha</span></p>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">{{ number_format($summary['healthy_area_percent'], 1) }}% area healthy</span>
                        </div>
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-orange-200">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $summary['healthy_area_percent']) }}%"></div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-lg bg-emerald-50 px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase text-emerald-700">Area Healthy</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">{{ number_format($summary['healthy_area_ha'], 2) }} ha</p>
                            </div>
                            <div class="rounded-lg bg-orange-50 px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase text-orange-700">Damaged</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">{{ number_format($summary['damage_area_ha'], 2) }} ha</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white border border-gray-200 shadow-sm p-4 lg:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Production</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($summary['adjusted_production_mt'], 2) }} <span class="text-sm font-semibold text-gray-500">mt adjusted</span></p>
                            </div>
                            <span class="rounded-full bg-orange-50 px-2.5 py-1 text-[11px] font-semibold text-orange-700">{{ number_format($summary['loss_percent'], 1) }}% loss</span>
                        </div>
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-orange-200">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ max(0, 100 - min(100, $summary['loss_percent'])) }}%"></div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-lg bg-emerald-50 px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase text-emerald-700">Adjusted</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">{{ number_format($summary['adjusted_production_mt'], 2) }} mt</p>
                            </div>
                            <div class="rounded-lg bg-orange-50 px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase text-orange-700">Loss</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">{{ number_format($summary['loss_production_mt'], 2) }} mt</p>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">Original estimate: {{ number_format($summary['original_production_mt'], 2) }} mt</p>
                    </div>
                </section>

                <section class="xl:col-span-4 rounded-lg bg-white border border-gray-200 shadow-sm p-4 lg:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">Crops Planted</p>
                            <h2 class="mt-1 text-base font-semibold text-gray-900">Crop records</h2>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">{{ number_format($summary['crop_types']) }} crop types</span>
                    </div>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-[8rem_1fr] gap-5 items-center">
                        <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-full bg-emerald-500">
                            <div class="flex h-16 w-16 flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-xl font-bold text-gray-900">{{ number_format($summary['total_records']) }}</span>
                                <span class="text-[9px] uppercase text-gray-500">records</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($summary['crop_breakdown']->take(5) as $crop)
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-gray-800">{{ $crop['crop'] }}</p>
                                        <span class="text-xs font-bold text-gray-900">{{ number_format($crop['records']) }}</span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, ($crop['records'] / $summary['max_crop_records']) * 100) }}%"></div>
                                    </div>
                                    <p class="mt-1 text-[11px] text-gray-500">{{ number_format($crop['area_ha'], 2) }} ha, {{ number_format($crop['production_mt'], 2) }} mt</p>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No crop records yet.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>

            <section class="rounded-lg bg-white border border-gray-200 shadow-sm p-4 lg:p-6">
                <form method="GET" action="{{ route('admin.reports.planting-report') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                        <div class="xl:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Search</label>
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Farmer name, ID, crop, municipality" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Crop Type</label>
                            <select name="crop" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All crops</option>
                                @foreach($filters['crops'] as $crop)
                                    <option value="{{ $crop }}" @selected(request('crop') === $crop)>{{ ucwords(strtolower($crop)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Municipality</label>
                            <select name="municipality" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All municipalities</option>
                                @foreach($filters['municipalities'] as $municipality)
                                    <option value="{{ $municipality }}" @selected(request('municipality') === $municipality)>{{ ucwords(strtolower($municipality)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Farm Type</label>
                            <select name="farm_type" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All farm types</option>
                                @foreach($filters['farm_types'] as $farmType)
                                    <option value="{{ $farmType }}" @selected(request('farm_type') === $farmType)>{{ ucwords(strtolower($farmType)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">All statuses</option>
                                @foreach($filters['statuses'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Planting Month</label>
                            <select name="planting_month" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Any month</option>
                                @foreach($filters['months'] as $month => $name)
                                    <option value="{{ $month }}" @selected((string) request('planting_month') === (string) $month)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Planting Year</label>
                            <select name="planting_year" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Any year</option>
                                @foreach($filters['years'] as $year)
                                    <option value="{{ $year }}" @selected((string) request('planting_year') === (string) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Harvest Month</label>
                            <select name="harvest_month" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Any month</option>
                                @foreach($filters['months'] as $month => $name)
                                    <option value="{{ $month }}" @selected((string) request('harvest_month') === (string) $month)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Harvest Year</label>
                            <select name="harvest_year" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Any year</option>
                                @foreach($filters['years'] as $year)
                                    <option value="{{ $year }}" @selected((string) request('harvest_year') === (string) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Apply Filters</button>
                            <a href="{{ route('admin.reports.planting-report') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ $csvUrl }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">Export CSV</a>
                            <a href="{{ $pdfUrl }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Export PDF</a>
                        </div>
                    </div>
                </form>
            </section>

            <section class="rounded-lg bg-white border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 lg:px-6 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Planting Records</h2>
                        <p class="mt-1 text-xs text-gray-500">Each row comes from a crop plan submitted on the farmer calendar.</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ number_format($paginatedRecords->total()) }} records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">Farmer Details</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">Crop Plan</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">Planting</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">Harvest</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">Area & Yield</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">Farm Type</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">Recorded</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($paginatedRecords as $record)
                                <tr class="align-top hover:bg-gray-50/70">
                                    <td class="px-4 py-4 text-xs text-gray-600">
                                        <p class="font-bold uppercase text-gray-900">{{ $record['farmer_name'] }}</p>
                                        <p class="mt-1">Farmer ID: {{ $record['farmer_id'] }}</p>
                                        <p>Municipality: {{ $record['municipality'] }}</p>
                                        <p>Cooperative: {{ $record['cooperative'] ?: '-' }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-xs text-gray-600">
                                        <p class="font-bold uppercase text-gray-900">{{ $record['crop'] }}</p>
                                        <p class="mt-1">Notes: {{ $record['notes'] }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-xs font-semibold text-gray-900 whitespace-nowrap">
                                        {{ $record['planting_date']?->format('M d, Y') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-xs font-semibold text-gray-900 whitespace-nowrap">
                                        {{ $record['harvest_date']?->format('M d, Y') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-xs text-gray-600 whitespace-nowrap">
                                        <p class="font-bold text-gray-900">{{ number_format($record['area_ha'], 2) }} ha</p>
                                        <p class="mt-1">Original: {{ number_format($record['original_production_mt'], 2) }} mt</p>
                                        <p>Adjusted: {{ number_format($record['adjusted_production_mt'], 2) }} mt</p>
                                        @if($record['damage_sqm'] > 0)
                                            <p class="mt-1 font-semibold text-orange-700">Damage: {{ number_format($record['damage_ha'], 2) }} ha affected, {{ number_format($record['loss_production_mt'], 2) }} mt lost</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-xs text-gray-600">
                                        <p>{{ $record['farm_type'] }}</p>
                                        <p class="mt-1">{{ $record['seed_type'] }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-xs text-gray-600">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold uppercase {{ $statusClasses[$record['status']] ?? 'bg-gray-100 text-gray-700' }}">{{ $record['status_label'] }}</span>
                                        @if($record['status'] === 'damaged')
                                            <div class="mt-2 space-y-1 text-[11px]">
                                                <p class="font-semibold text-orange-700">{{ $record['damage_title'] ?: 'Damage report' }}</p>
                                                <p>Date damaged: {{ $record['damage_date']?->format('M d, Y') ?? '-' }}</p>
                                                <p>Reported: {{ $record['damage_reported_at']?->format('M d, Y h:i A') ?? '-' }}</p>
                                                <p>{{ $record['damage_description'] ?: 'No additional notes' }}</p>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-xs text-gray-600 whitespace-nowrap">
                                        <p>{{ $record['recorded_at']?->format('M d, Y') ?? '-' }}</p>
                                        <p class="mt-1">{{ $record['recorded_at']?->format('h:i A') ?? '' }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-500">No planting records match the current filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($paginatedRecords->hasPages())
                    <div class="border-t border-gray-100 px-4 lg:px-6 py-4">
                        {{ $paginatedRecords->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-admin-layout>

<x-app-layout>
    @php
        $dashboardDate = now();
        $hour = (int) $dashboardDate->format('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $locationLabel = $preferredMunicipality ? ucwords(strtolower($preferredMunicipality)) : null;
        $harvestItems = collect($harvestProgress['items'] ?? []);
        $cropBalanceItems = collect($cropBalancePulse['items'] ?? []);
        $cropBalanceAlternatives = collect($cropBalancePulse['alternatives'] ?? []);
        $topCropsUrl = rtrim((string) config('services.ml_api.url'), '/') . '/api/top-crops';
    @endphp

    <style>
        .farmer-dashboard-card {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 160ms ease, box-shadow 160ms ease;
        }

        .farmer-dashboard-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        }

        .farmer-action-row {
            transition: background-color 160ms ease, border-color 160ms ease;
        }

        .farmer-action-row:hover {
            background: #f8fafc;
        }

        .farmer-icon-box {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .farmer-crop-thumb {
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #f1f5f9;
        }

        @media (prefers-reduced-motion: reduce) {
            .farmer-dashboard-card,
            .farmer-action-row {
                transition: none;
            }
        }
    </style>

    <div
        x-data='farmerDashboard(@json([
            'municipality' => $preferredMunicipality ?? '',
            'predictionUrl' => route('predictions.predict.form'),
            'topCropsUrl' => $topCropsUrl,
        ]))'
        x-init="init()"
        class="px-4 py-4 sm:px-6 lg:px-8 lg:py-6"
    >
        <div class="mx-auto max-w-6xl space-y-4 lg:space-y-5">
            <header class="farmer-dashboard-card px-4 py-4 sm:px-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-primary-dark">Farmer dashboard</p>
                        <h1 class="mt-1 text-xl font-semibold leading-tight text-slate-950 sm:text-2xl">
                            {{ $greeting }}, {{ Auth::user()->name }}
                        </h1>
                        <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">
                            A quick view of what needs attention before you plan, predict, or check nearby crop activity.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 font-medium text-slate-700">
                            {{ $dashboardDate->format('M d, Y') }}
                        </span>
                        @if($locationLabel)
                            <span class="inline-flex items-center rounded-full border border-primary-100 bg-primary-50 px-3 py-1.5 font-medium text-primary-dark">
                                {{ $locationLabel }}
                            </span>
                        @else
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 font-medium text-amber-700">
                                Set farm location
                            </a>
                        @endif
                    </div>
                </div>
            </header>

            <section class="farmer-dashboard-card overflow-hidden">
                <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-slate-950">Today / This week</h2>
                            <p class="text-sm text-slate-500">The most useful next actions, kept short.</p>
                        </div>
                        @if(($harvestProgress['expected_production_mt'] ?? 0) > 0)
                            <span class="inline-flex w-fit items-center rounded-full border border-green-100 bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                                {{ number_format($harvestProgress['expected_production_mt'], 2) }} MT expected
                            </span>
                        @endif
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($todayActionQueue ?? [] as $item)
                        @php
                            $dotClass = match ($item['tone'] ?? 'primary') {
                                'amber' => 'bg-amber-500',
                                'green' => 'bg-green-600',
                                default => 'bg-primary-dark',
                            };
                        @endphp
                        <a href="{{ $item['href'] }}" class="farmer-action-row flex items-center gap-3 px-4 py-3 sm:px-5">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $dotClass }}"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-slate-950">{{ $item['label'] }}</span>
                                <span class="block truncate text-xs text-slate-500">{{ $item['description'] }}</span>
                            </span>
                            <span class="hidden shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 sm:inline-flex">
                                {{ $item['meta'] }}
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @empty
                        <a href="{{ route('farmer.calendar.page') }}" class="farmer-action-row flex items-center gap-3 px-4 py-3 sm:px-5">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-primary-dark"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-slate-950">Open your calendar</span>
                                <span class="block truncate text-xs text-slate-500">Start with a crop plan or reminder.</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endforelse
                </div>
            </section>

            <section>
                <div class="mb-3 flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-slate-950">Tools</h2>
                        <p class="text-sm text-slate-500">Main farmer workflows, one tap away.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="{{ route('farmer.calendar.page') }}" class="farmer-dashboard-card flex items-start gap-3 p-4">
                        <span class="farmer-icon-box bg-primary-50 text-primary-dark">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-950">My Calendar</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">Plans, reminders, harvests</span>
                        </span>
                    </a>

                    <a href="{{ route('predictions.predict.form') }}" class="farmer-dashboard-card flex items-start gap-3 p-4">
                        <span class="farmer-icon-box bg-primary-50 text-primary-dark">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.75h4.5m-7.5 3h10.5M6 10.5h12M7.5 21h9a1.5 1.5 0 001.5-1.5v-6A1.5 1.5 0 0016.5 12h-9A1.5 1.5 0 006 13.5v6A1.5 1.5 0 007.5 21z" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-950">Predict</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">Estimate crop production</span>
                        </span>
                    </a>

                    <a href="{{ route('map.index') }}" class="farmer-dashboard-card flex items-start gap-3 p-4">
                        <span class="farmer-icon-box bg-slate-100 text-slate-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l-6 3V6l6-3m0 15l6 3m-6-3V3m6 18l6-3V3l-6 3m0 15V6" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-950">Map</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">Check municipal signals</span>
                        </span>
                    </a>

                    <a href="{{ route('forum.index') }}" class="farmer-dashboard-card flex items-start gap-3 p-4">
                        <span class="farmer-icon-box bg-slate-100 text-slate-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8m-8 4h5m8-2a8 8 0 11-3.293-6.475L21 4.5V12z" />
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-slate-950">Forum</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">Ask and share updates</span>
                        </span>
                    </a>
                </div>
            </section>

            @if($harvestItems->isNotEmpty())
                <section class="farmer-dashboard-card overflow-hidden">
                    <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-slate-950">Harvest progress</h2>
                                <p class="text-sm text-slate-500">Nearest crops to monitor.</p>
                            </div>
                            <a href="{{ route('farmer.calendar.page') }}" class="inline-flex w-fit items-center rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600 hover:border-primary-200 hover:text-primary-dark">
                                Open calendar
                            </a>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach($harvestItems as $item)
                            <a href="{{ route('farmer.calendar.page') }}" class="farmer-action-row block px-4 py-3 sm:px-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-semibold text-slate-950">{{ $item['crop'] }}</h3>
                                        <p class="mt-0.5 text-xs text-slate-500">Harvest: {{ $item['harvest_date'] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full border px-2.5 py-1 text-[11px] font-medium {{ $item['status']['classes'] }}">
                                        {{ $item['status']['label'] }}
                                    </span>
                                </div>

                                <div class="mt-3">
                                    <div class="mb-1 flex items-center justify-between text-[11px] text-slate-500">
                                        <span>{{ $item['planning_date'] }}</span>
                                        <span>{{ $item['progress_percent'] }}%</span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full bg-green-600" style="width: {{ $item['progress_percent'] }}%"></div>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[11px]">
                                    @if($item['adjusted_production_mt'] !== null)
                                        <span class="rounded-full bg-green-50 px-2 py-1 font-medium text-green-700">
                                            {{ number_format($item['adjusted_production_mt'], 2) }} MT est.
                                        </span>
                                    @endif
                                    @if($item['damage_area_sqm'] > 0)
                                        <span class="rounded-full bg-red-50 px-2 py-1 font-medium text-red-700">
                                            {{ number_format($item['damage_area_sqm']) }} sqm damaged
                                        </span>
                                    @endif
                                    @if($item['next_task'])
                                        <span class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-600">
                                            Next: {{ $item['next_task']['date'] }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if(($harvestProgress['hidden_count'] ?? 0) > 0)
                        <p class="border-t border-slate-100 px-4 py-3 text-xs text-slate-500 sm:px-5">
                            {{ $harvestProgress['hidden_count'] }} more crop {{ $harvestProgress['hidden_count'] === 1 ? 'plan is' : 'plans are' }} available in the calendar.
                        </p>
                    @endif
                </section>
            @else
                <section class="farmer-dashboard-card px-4 py-4 sm:px-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-slate-950">No active crop plan yet</h2>
                            <p class="mt-1 text-sm text-slate-500">Create a plan to track reminders, harvest timing, and expected production.</p>
                        </div>
                        <a href="{{ route('farmer.calendar.page') }}" class="inline-flex w-fit items-center justify-center rounded-lg bg-primary-dark px-4 py-2 text-sm font-semibold text-white hover:bg-primary-900">
                            Create plan
                        </a>
                    </div>
                </section>
            @endif

            <section class="farmer-dashboard-card overflow-hidden">
                <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-slate-950">Planting around your area</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $cropBalancePulse['message'] }}</p>
                        </div>
                        <span class="inline-flex w-fit items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">
                            {{ $cropBalancePulse['window_label'] }}
                        </span>
                    </div>
                </div>

                @if($cropBalancePulse['has_data'])
                    <div class="divide-y divide-slate-100">
                        @foreach($cropBalanceItems->take(4) as $item)
                            @php
                                $pressureClass = match ($item['pressure_key'] ?? 'low') {
                                    'high' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'balanced' => 'bg-primary-50 text-primary-dark border-primary-100',
                                    default => 'bg-green-50 text-green-700 border-green-100',
                                };
                            @endphp
                            <div class="px-4 py-3 sm:px-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-sm font-semibold text-slate-950">{{ $item['crop'] }}</h3>
                                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ $item['short_message'] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full border px-2.5 py-1 text-[11px] font-medium {{ $pressureClass }}">
                                        {{ $item['label'] }}
                                    </span>
                                </div>

                                <div class="mt-2 flex flex-wrap gap-1.5 text-[11px]">
                                    <span class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-600">{{ $item['plan_count'] }} plans</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-600">{{ number_format($item['expected_production_mt'], 2) }} MT expected</span>
                                    @if($item['harvest_window'])
                                        <span class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-600">{{ $item['harvest_window'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($cropBalanceAlternatives->isNotEmpty())
                        <div class="border-t border-slate-100 px-4 py-3 sm:px-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Less crowded options</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach($cropBalanceAlternatives as $alternative)
                                    <span class="rounded-full border border-green-100 bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">
                                        {{ $alternative['crop'] }} - {{ $alternative['label'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @elseif($cropBalancePulse['has_location'])
                    <div class="px-4 py-5 text-sm text-slate-500 sm:px-5">
                        Nearby crop plans will appear here when farmers in {{ $cropBalancePulse['municipality'] }} add them.
                    </div>
                @else
                    <div class="px-4 py-5 text-sm text-slate-500 sm:px-5">
                        Add your town in your profile to see local crop balance.
                    </div>
                @endif
            </section>

            <section class="farmer-dashboard-card overflow-hidden">
                <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-slate-950">Crop outlook</h2>
                            <p class="mt-1 text-sm text-slate-500">Top crops from historical data and current-year forecasts.</p>
                        </div>
                        @if($locationLabel)
                            <span class="inline-flex w-fit items-center rounded-full border border-primary-100 bg-primary-50 px-3 py-1 text-xs font-medium text-primary-dark">
                                {{ $locationLabel }}
                            </span>
                        @endif
                    </div>
                </div>

                <div x-show="!municipality" class="px-4 py-6 text-center sm:px-5" x-cloak>
                    <p class="text-sm font-semibold text-slate-900">Set your farm location first.</p>
                    <p class="mt-1 text-sm text-slate-500">The outlook works best when it uses your town.</p>
                    <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex items-center justify-center rounded-lg bg-primary-dark px-4 py-2 text-sm font-semibold text-white hover:bg-primary-900">
                        Set location
                    </a>
                </div>

                <div x-show="loading && municipality" class="px-4 py-8 text-center sm:px-5" x-cloak>
                    <svg class="mx-auto h-7 w-7 animate-spin text-primary-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500">Loading crop outlook...</p>
                </div>

                <div x-show="error && municipality" class="px-4 py-6 text-center sm:px-5" x-cloak>
                    <p class="text-sm font-semibold text-red-700" x-text="timedOut ? 'The crop outlook is taking too long.' : 'Crop outlook is unavailable right now.'"></p>
                    <p class="mt-1 text-sm text-slate-500">You can still use the calendar and community signals above.</p>
                </div>

                <div x-show="!loading && !error && municipality && rows.length > 0" x-cloak>
                    <div x-show="insightText" class="border-b border-slate-100 bg-slate-50 px-4 py-3 sm:px-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Quick insight</p>
                        <p class="mt-1 text-sm leading-6 text-slate-700" x-text="insightText"></p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <template x-for="row in visibleRows" :key="row.rank + '-' + row.crop">
                            <div class="px-4 py-3 sm:px-5">
                                <div class="flex items-start gap-3">
                                    <div class="farmer-crop-thumb shrink-0">
                                        <template x-if="row.image && !isCropImageMissing(row.crop)">
                                            <img :src="row.image" :alt="row.crop" class="h-full w-full object-cover" x-on:error="markCropImageMissing(row.crop)">
                                        </template>
                                        <template x-if="!row.image || isCropImageMissing(row.crop)">
                                            <div class="flex h-full w-full items-center justify-center text-xs font-semibold text-slate-500" x-text="row.initials"></div>
                                        </template>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="truncate text-sm font-semibold text-slate-950" x-text="row.crop"></h3>
                                            <span class="rounded-full border px-2.5 py-1 text-[11px] font-medium" :class="outlookClass(row)" x-text="outlookLabel(row)"></span>
                                        </div>
                                        <p class="mt-1 text-xs leading-5 text-slate-500" x-text="outlookDescription(row)"></p>
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[11px]">
                                            <span class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-600" x-text="'Rank ' + row.rank"></span>
                                            <span class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-600" x-text="'Forecast ' + formatMetric(row.predicted) + ' MT'"></span>
                                            <span class="rounded-full bg-slate-100 px-2 py-1 font-medium text-slate-600" x-text="'Past avg ' + formatMetric(row.historical) + ' MT'"></span>
                                        </div>
                                    </div>

                                    <a :href="predictionLink(row.crop)" class="hidden shrink-0 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-primary-200 hover:text-primary-dark sm:inline-flex">
                                        Predict
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="hasExtraRows" class="border-t border-slate-100 px-4 py-3 text-center sm:px-5">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:border-primary-200 hover:text-primary-dark"
                            x-on:click="showAllRows = !showAllRows"
                            x-text="showAllRows ? 'Show top 3 only' : 'Show all crops'"
                        ></button>
                    </div>
                </div>

                <div x-show="!loading && !error && municipality && rows.length === 0" class="px-4 py-6 text-center sm:px-5" x-cloak>
                    <p class="text-sm font-semibold text-slate-900">No crop outlook data yet.</p>
                    <p class="mt-1 text-sm text-slate-500">Try again later or check the interactive map.</p>
                </div>
            </section>
        </div>
    </div>

    <script>
        function farmerDashboard(config) {
            return {
                municipality: config.municipality || '',
                predictionUrl: config.predictionUrl,
                topCropsUrl: config.topCropsUrl,
                loading: false,
                error: false,
                timedOut: false,
                showAllRows: false,
                rows: [],
                insightText: '',
                cropImageErrors: {},
                cropImageMap: {
                    'CABBAGE': @json(asset('images/crops/cabbage.png')),
                    'BROCCOLI': @json(asset('images/crops/broccoli.png')),
                    'LETTUCE': @json(asset('images/crops/lettuce.png')),
                    'CAULIFLOWER': @json(asset('images/crops/cauliflower.png')),
                    'CHINESE CABBAGE': @json(asset('images/crops/chinesecabbage.png')),
                    'CARROTS': @json(asset('images/crops/carrot.png')),
                    'GARDEN PEAS': @json(asset('images/crops/gardenpeas.png')),
                    'WHITE POTATO': @json(asset('images/crops/whitepotato.png')),
                    'SNAP BEANS': @json(asset('images/crops/snapbean.png')),
                    'SWEET PEPPER': @json(asset('images/crops/sweetpepper.png')),
                },

                get visibleRows() {
                    return this.showAllRows ? this.rows : this.rows.slice(0, 3);
                },

                get hasExtraRows() {
                    return this.rows.length > 3;
                },

                init() {
                    this.loadCropOutlook();
                },

                normalizeMunicipality(value) {
                    const normalized = String(value || '').trim().toUpperCase();
                    return normalized === 'LA TRINIDAD' ? 'LATRINIDAD' : normalized;
                },

                cropKey(crop) {
                    return String(crop || '').trim().toUpperCase();
                },

                cropInitials(crop) {
                    return String(crop || '')
                        .split(/\s+/)
                        .filter(Boolean)
                        .slice(0, 2)
                        .map((part) => part.charAt(0).toUpperCase())
                        .join('');
                },

                getCropImage(crop) {
                    return this.cropImageMap[this.cropKey(crop)] || '';
                },

                isCropImageMissing(crop) {
                    return Boolean(this.cropImageErrors[this.cropKey(crop)]);
                },

                markCropImageMissing(crop) {
                    this.cropImageErrors = {
                        ...this.cropImageErrors,
                        [this.cropKey(crop)]: true,
                    };
                },

                formatMetric(value) {
                    const number = Number(value || 0);
                    return number.toLocaleString(undefined, {
                        maximumFractionDigits: number >= 100 ? 0 : 2,
                    });
                },

                predictionLink(crop) {
                    const params = new URLSearchParams({
                        tab: 'forecast',
                        crop: crop || '',
                    });

                    if (this.municipality) {
                        params.set('municipality', this.municipality);
                    }

                    return `${this.predictionUrl}?${params.toString()}`;
                },

                outlookLabel(row) {
                    if (row.rank === 1) return 'Strongest';
                    if (row.predicted > row.historical && row.predicted > 0) return 'Rising';
                    if (row.predicted === 0) return 'Historical';
                    return 'Stable';
                },

                outlookClass(row) {
                    if (row.rank === 1) return 'border-green-100 bg-green-50 text-green-700';
                    if (row.predicted > row.historical && row.predicted > 0) return 'border-primary-100 bg-primary-50 text-primary-dark';
                    return 'border-slate-200 bg-slate-50 text-slate-600';
                },

                outlookDescription(row) {
                    if (row.predicted > 0) {
                        return `${row.crop} is forecast at ${this.formatMetric(row.predicted)} MT, compared with a past average of ${this.formatMetric(row.historical)} MT.`;
                    }

                    return `${row.crop} ranks from historical records, with a past average of ${this.formatMetric(row.historical)} MT.`;
                },

                buildInsight(rows) {
                    if (!rows.length) return '';

                    const leader = rows[0];
                    const area = this.municipality ? this.municipality.toLowerCase().replace(/\b\w/g, (letter) => letter.toUpperCase()) : 'your area';

                    if (leader.predicted > 0) {
                        return `${leader.crop} has the strongest current outlook for ${area}. Check nearby planting activity before making a final plan.`;
                    }

                    return `${leader.crop} leads the historical records for ${area}. Use it as a starting point, then compare timing and nearby supply.`;
                },

                async loadCropOutlook() {
                    this.error = false;
                    this.timedOut = false;
                    this.showAllRows = false;
                    this.rows = [];
                    this.insightText = '';
                    this.cropImageErrors = {};

                    if (!this.municipality) return;

                    this.loading = true;
                    const controller = new AbortController();
                    const timeoutId = window.setTimeout(() => {
                        this.timedOut = true;
                        controller.abort();
                    }, 9000);

                    try {
                        const response = await fetch(this.topCropsUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ MUNICIPALITY: this.normalizeMunicipality(this.municipality) }),
                            signal: controller.signal,
                        });

                        if (!response.ok) {
                            throw new Error('Failed to fetch crop outlook');
                        }

                        const data = await response.json();

                        if (!data.success) {
                            throw new Error('Crop outlook returned an error');
                        }

                        const currentYear = new Date().getFullYear();
                        const merged = new Map();

                        (data.historical_top5?.crops || []).forEach((crop) => {
                            const key = this.cropKey(crop.crop);
                            if (!merged.has(key)) {
                                merged.set(key, {
                                    crop: crop.crop,
                                    historical: Number(crop.yearly_data?.average || 0),
                                    predicted: 0,
                                });
                            }
                        });

                        (data.predicted_top5?.crops || []).forEach((crop) => {
                            const key = this.cropKey(crop.crop);
                            const currentForecast = (crop.forecasts || []).find((forecast) => Number(forecast.year) === currentYear);
                            const predicted = Number(currentForecast?.production || 0);

                            if (!merged.has(key)) {
                                merged.set(key, {
                                    crop: crop.crop,
                                    historical: 0,
                                    predicted,
                                });
                                return;
                            }

                            merged.get(key).predicted = predicted;
                        });

                        this.rows = Array.from(merged.values())
                            .map((row) => ({
                                crop: row.crop,
                                historical: Number(row.historical || 0),
                                predicted: Number(row.predicted || 0),
                                image: this.getCropImage(row.crop),
                                initials: this.cropInitials(row.crop),
                            }))
                            .sort((a, b) => {
                                const aScore = a.predicted > 0 ? a.predicted : a.historical;
                                const bScore = b.predicted > 0 ? b.predicted : b.historical;

                                if (bScore !== aScore) return bScore - aScore;
                                if (b.predicted !== a.predicted) return b.predicted - a.predicted;
                                if (b.historical !== a.historical) return b.historical - a.historical;
                                return String(a.crop || '').localeCompare(String(b.crop || ''));
                            })
                            .slice(0, 5)
                            .map((row, index) => ({
                                ...row,
                                rank: index + 1,
                            }));

                        this.insightText = this.buildInsight(this.rows);
                    } catch (error) {
                        console.error('Error loading crop outlook:', error);
                        this.error = true;
                        this.timedOut = error?.name === 'AbortError' || this.timedOut;
                    } finally {
                        window.clearTimeout(timeoutId);
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>

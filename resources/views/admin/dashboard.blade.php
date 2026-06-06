<x-admin-layout>
    <div class="py-3 lg:py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Header Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-3 lg:mb-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold text-gray-900 mb-0.5">
                            Admin Dashboard
                        </h1>
                        <p class="text-sm text-gray-500">
                            Welcome back, <span class="font-semibold text-gray-900">{{ Auth::user()->name }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-left sm:text-right">
                        <div class="hidden sm:flex items-center justify-center w-9 h-9 rounded-lg bg-primary-50">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">{{ now()->format('l') }}</p>
                            <p class="text-sm lg:text-base font-semibold text-gray-900">{{ now()->format('F d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-3 mb-3 lg:mb-4">
                <!-- Total Users -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 px-3 py-3 hover:shadow-md transition-all duration-200 hover:border-blue-200">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-50 p-2 rounded-lg">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xl lg:text-2xl font-bold leading-tight text-gray-900">{{ \App\Models\User::count() }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Users</p>
                        </div>
                    </div>
                </div>

                <!-- Crop Records -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 px-3 py-3 hover:shadow-md transition-all duration-200 hover:border-green-200">
                    <div class="flex items-center gap-3">
                        <div class="bg-green-50 p-2 rounded-lg">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4">
                                    </path>
                                </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xl lg:text-2xl font-bold leading-tight text-gray-900">
                                {{ number_format(\App\Models\CropProduction::count()) }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Crop Records</p>
                        </div>
                    </div>
                </div>

                <!-- Predictions -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 px-3 py-3 hover:shadow-md transition-all duration-200 hover:border-purple-200">
                    <div class="flex items-center gap-3">
                        <div class="bg-purple-50 p-2 rounded-lg">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                    </path>
                                </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xl lg:text-2xl font-bold leading-tight text-gray-900">
                                {{ number_format(\App\Models\Prediction::count()) }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Predictions</p>
                        </div>
                    </div>
                </div>

                <!-- Municipalities -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 px-3 py-3 hover:shadow-md transition-all duration-200 hover:border-amber-200">
                    <div class="flex items-center gap-3">
                        <div class="bg-amber-50 p-2 rounded-lg">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xl lg:text-2xl font-bold leading-tight text-gray-900">13</p>
                            <p class="text-xs text-gray-500 mt-0.5">Municipalities</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 lg:gap-3 mb-3 lg:mb-4">
                <!-- Manage Crop Data -->
                <a href="{{ route('admin.crop-data.index') }}"
                    class="group bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3 hover:shadow-md hover:border-green-200 transition-all duration-200">
                    <div class="flex items-center gap-3">
                        <div
                            class="bg-green-50 group-hover:bg-green-100 p-2 rounded-lg flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                                </path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900">Crop Data</h3>
                            <p class="text-xs text-gray-500">Import and manage records</p>
                            <span
                                class="inline-flex items-center gap-1 text-green-600 text-xs font-medium group-hover:gap-2 transition-all">
                                Manage data
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>

                <!-- Manage Users -->
                <a href="{{ route('admin.users.index') }}"
                    class="group bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3 hover:shadow-md hover:border-blue-200 transition-all duration-200">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-50 group-hover:bg-blue-100 p-2 rounded-lg flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900">Users</h3>
                            <p class="text-xs text-gray-500">Manage roles and access</p>
                            <span
                                class="inline-flex items-center gap-1 text-blue-600 text-xs font-medium group-hover:gap-2 transition-all">
                                Manage users
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>

                <!-- View Reports -->
                <a href="{{ route('admin.reports.index') }}"
                    class="group bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3 hover:shadow-md hover:border-purple-200 transition-all duration-200">
                    <div class="flex items-center gap-3">
                        <div
                            class="bg-purple-50 group-hover:bg-purple-100 p-2 rounded-lg flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900">Reports</h3>
                            <p class="text-xs text-gray-500">Generate exports</p>
                            <span
                                class="inline-flex items-center gap-1 text-purple-600 text-xs font-medium group-hover:gap-2 transition-all">
                                View reports
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Real-time Municipal Supply Forecast -->
            <div class="mb-3 lg:mb-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3 mb-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 13.5l7.5-7.5L14 9.5l7-7m0 0v6m0-6h-6M4 20h16" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Real-time Supply Forecast</h3>
                                    <p class="text-xs text-gray-600 mt-0.5">Live crop plans, damage, and actual harvest records.</p>
                                </div>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                <span id="adminSupplyForecastYear"
                                    class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700">Current season</span>
                                <span id="adminSupplyForecastStatus" class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1">Loading live supply...</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.map.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                            Open map
                        </a>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-4">
                        <div class="rounded-lg border border-emerald-100 bg-emerald-50/70 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Supply Forecast</p>
                            <p id="adminSupplyForecastTotal" class="mt-1 text-lg lg:text-xl font-bold text-gray-900">-</p>
                        </div>
                        <div class="rounded-lg border border-green-100 bg-green-50/70 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-green-700">Harvested</p>
                            <p id="adminSupplyHarvestedTotal" class="mt-1 text-lg lg:text-xl font-bold text-gray-900">-</p>
                        </div>
                        <div class="rounded-lg border border-sky-100 bg-sky-50/70 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-700">Remaining</p>
                            <p id="adminSupplyRemainingTotal" class="mt-1 text-lg lg:text-xl font-bold text-gray-900">-</p>
                        </div>
                        <div class="rounded-lg border border-red-100 bg-red-50/70 p-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-red-700">Damaged</p>
                            <p id="adminSupplyDamagedTotal" class="mt-1 text-lg lg:text-xl font-bold text-gray-900">-</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                        <div class="rounded-lg border border-gray-200 p-3">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">Top Municipalities</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Ranked by live supply forecast</p>
                                </div>
                                <span id="adminSupplyPlanCount" class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600">- plans</span>
                            </div>
                            <div id="adminSupplyMunicipalityList" class="space-y-2">
                                <p class="text-sm text-gray-500">Loading municipality supply...</p>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-3">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">Selected Area Crop Supply</h4>
                                    <p id="adminSupplySelectedArea" class="text-xs text-gray-500 mt-0.5">La Trinidad</p>
                                </div>
                                <span id="adminSupplySelectedCropCount" class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600">- crops</span>
                            </div>
                            <div id="adminSupplyCropList" class="space-y-2">
                                <p class="text-sm text-gray-500">Loading crop supply...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 5 Crops by Production Chart -->
            <div class="mb-3 lg:mb-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-3 mb-3">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-sm font-semibold text-gray-700">
                                    #
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Top 5 Crops</h3>
                                    <p class="text-xs text-gray-600 mt-0.5">Full-year crop outlook for the selected municipality.</p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                                <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs text-gray-600">
                                    <span class="font-medium uppercase tracking-wide text-gray-500">Using</span>
                                    <span id="adminChartAreaLabel" class="font-semibold text-gray-900">La Trinidad</span>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label for="adminMunicipalitySelect"
                                        class="text-xs font-medium uppercase tracking-wide text-gray-500 whitespace-nowrap">Switch area</label>
                                    <select id="adminMunicipalitySelect"
                                        class="w-full sm:w-auto rounded-full border-gray-300 bg-white px-3 py-1.5 text-xs shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                        <option value="LATRINIDAD">La Trinidad</option>
                                        <option value="ITOGON">Itogon</option>
                                        <option value="SABLAN">Sablan</option>
                                        <option value="TUBA">Tuba</option>
                                        <option value="TUBLAY">Tublay</option>
                                        <option value="ATOK">Atok</option>
                                        <option value="BAKUN">Bakun</option>
                                        <option value="BOKOD">Bokod</option>
                                        <option value="BUGUIAS">Buguias</option>
                                        <option value="KABAYAN">Kabayan</option>
                                        <option value="KAPANGAN">Kapangan</option>
                                        <option value="KIBUNGAN">Kibungan</option>
                                        <option value="MANKAYAN">Mankayan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="adminChartInsightCard" class="flex items-center relative w-full xl:max-w-md">
                            <div class="hidden sm:block shrink-0 relative z-20 w-[88px]">
                                <div class="overflow-hidden">
                                    <div id="adminChartInsightAvatar" class="w-[88px] h-[88px]" aria-hidden="true"></div>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1 relative z-10 sm:ml-5">
                                {{-- Thought Bubble Tails --}}
                                <div class="hidden sm:block absolute top-[60%] -left-5 w-2.5 h-2.5 rounded-full bg-gray-800 border border-white/10 z-0"></div>
                                <div class="hidden sm:block absolute top-[35%] -left-3 w-4 h-4 rounded-full bg-gray-800 border border-white/10 z-0"></div>
                                
                                {{-- Main Cloud Box --}}
                                <div class="relative rounded-2xl bg-gray-800 px-4 py-3 shadow-xl border border-white/10 z-10">
                                    <p class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-widest text-[#a1a1aa] mb-1">Quick insight</p>
                                    <p id="adminChartInsightText" class="text-xs leading-relaxed text-gray-200" aria-live="polite">
                                        Loading the strongest crop outlook for the selected municipality...
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="adminChartLoading" class="text-center py-8">
                        <svg class="inline-block animate-spin h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-gray-600 mt-2 text-sm">Loading chart data...</p>
                    </div>
                    <div id="adminChartContainer" class="hidden">
                        <div class="h-[260px] md:h-[250px] lg:h-[280px]">
                            <canvas id="adminTopCropsChart"></canvas>
                        </div>
                        <div class="mt-3 text-xs text-gray-500 border-t border-gray-200 pt-2 space-y-0.5">
                            <p><strong>Historical Average:</strong> Average annual production from actual data between 2015 and 2024</p>
                            <p><strong>This Year Forecast:</strong> Current year forecast using the ML model</p>
                        </div>
                    </div>
                    <div id="adminChartError" class="hidden text-center py-8 text-red-600">
                        <svg class="inline-block h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm">Failed to load chart data. Please try again.</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 lg:gap-4">
                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4"
                    x-data="{ showAllPredictions: false }">
                    @php
                        $selectedFilterLabel = $activityStats['filters'][$activityFilter]['label'] ?? 'All';
                        $selectedFilterText = $selectedFilterLabel === 'All' ? 'activity' : strtolower($selectedFilterLabel);
                    @endphp

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Recent Activity</h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $selectedFilterLabel === 'All' ? 'Compact mode groups rapid prediction activity' : 'Showing ' . $selectedFilterText }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 p-1">
                                <button type="button" @click="showAllPredictions = false"
                                    :class="showAllPredictions ? 'text-gray-500' : 'bg-white text-gray-900 shadow-sm'"
                                    class="px-2.5 py-1 text-[11px] font-medium rounded-md transition-colors">
                                    Compact
                                </button>
                                <button type="button" @click="showAllPredictions = true"
                                    :class="showAllPredictions ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                                    class="px-2.5 py-1 text-[11px] font-medium rounded-md transition-colors">
                                    Show all
                                </button>
                            </div>
                            <a href="{{ route('admin.activities.index', ['activity_type' => $activityFilter]) }}"
                                class="text-xs lg:text-sm text-primary hover:text-primary-700 font-medium">View all</a>
                        </div>
                    </div>

                    @if($activityFeedUnavailable ?? false)
                        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                            <p class="text-xs lg:text-sm text-amber-800">
                                The activity feed is temporarily unavailable on this environment. Core dashboard metrics are still loaded.
                            </p>
                        </div>
                    @endif

                    <div class="mb-3 flex flex-wrap gap-1.5">
                        @foreach($activityStats['filters'] as $filterKey => $filter)
                            <a href="{{ route('admin.dashboard', ['activity_type' => $filterKey]) }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors {{ $activityFilter === $filterKey ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:text-gray-800' }}">
                                <span>{{ $filter['label'] }}</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] {{ $activityFilter === $filterKey ? 'bg-white text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ number_format($filter['count']) }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <div x-show="!showAllPredictions" class="space-y-3">
                        @forelse(($compactRecentActivities ?? collect())->take(3) as $activity)
                            <div
                                class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex-shrink-0 mt-0.5">
                                    @include('admin.activities.partials.icon', ['type' => $activity->activity_type])
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $activity->user_name }}</p>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-white text-gray-600 border border-gray-200">
                                            {{ $activity->type_label }}
                                        </span>
                                        @if(($activity->grouped_prediction_count ?? 1) > 1)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-700">
                                                {{ $activity->grouped_prediction_count }} entries
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-700 mt-0.5">{{ $activity->title }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $activity->description }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $activity->activity_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <p class="text-sm font-medium">No recent {{ $selectedFilterText }}</p>
                                <p class="text-xs mt-1">Try another activity filter or check back once new actions are recorded</p>
                            </div>
                        @endforelse
                    </div>

                    <div x-show="showAllPredictions" class="space-y-3">
                        @forelse(($recentActivities ?? collect())->take(5) as $activity)
                            <div
                                class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex-shrink-0 mt-0.5">
                                    @include('admin.activities.partials.icon', ['type' => $activity->activity_type])
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $activity->user_name }}</p>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-white text-gray-600 border border-gray-200">
                                            {{ $activity->type_label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-700 mt-0.5">{{ $activity->title }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $activity->description }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $activity->activity_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <p class="text-sm font-medium">No recent {{ $selectedFilterText }}</p>
                                <p class="text-xs mt-1">Try another activity filter or check back once new actions are recorded</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- System Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">System Overview</h3>
                    <div class="space-y-2.5">
                        <div
                            class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-100">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Active Farmers</p>
                                    <p class="text-xs text-gray-500">Registered farmer accounts</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold text-blue-600">
                                {{ \App\Models\User::where('role', 'farmer')->count() }}</p>
                        </div>

                        <div
                            class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-100">
                            <div class="flex items-center gap-3">
                                <div class="bg-green-100 p-2 rounded-lg">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Data Coverage</p>
                                    <p class="text-xs text-gray-500">Years of historical data</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold text-green-600">2015-2024</p>
                        </div>

                        <div
                            class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-100">
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-100 p-2 rounded-lg">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">System Status</p>
                                    <p class="text-xs text-gray-500">All services operational</p>
                                </div>
                            </div>
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full border border-green-200">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                Active
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between p-3 bg-amber-50 rounded-lg border border-amber-100">
                            <div class="flex items-center gap-3">
                                <div class="bg-amber-100 p-2 rounded-lg">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Coverage Area</p>
                                    <p class="text-xs text-gray-500">Benguet municipalities</p>
                                </div>
                            </div>
                            <p class="text-lg font-bold text-amber-600">13</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>

    <script>
        let adminTopCropsChart = null;
        let adminTopCropsChartState = {
            municipality: null,
            municipalityName: '',
            currentYear: null,
            rows: [],
            recommendedCrop: '',
            recommendationMonth: '',
            insightTypingTimer: null,
            insightToken: 0,
            isTyping: false,
            animationInstance: null,
            insightObserver: null
        };

        function isAdminMobile() {
            return window.innerWidth < 768;
        }

        function formatAdminCropAxisLabel(crop) {
            if (!crop || !crop.includes(' ')) {
                return crop;
            }

            return crop.split(' ');
        }

        function formatAdminMunicipalityName(municipality) {
            if (!municipality) {
                return 'the selected municipality';
            }

            if (String(municipality).replace(/\s+/g, '').toUpperCase() === 'LATRINIDAD') {
                return 'La Trinidad';
            }

            return municipality.charAt(0) + municipality.slice(1).toLowerCase();
        }

        function getAdminCurrentMonthCode() {
            return new Date().toLocaleString('en-US', { month: 'short' }).toUpperCase();
        }

        function getAdminMonthLabel(monthCode) {
            const monthNames = {
                JAN: 'January',
                FEB: 'February',
                MAR: 'March',
                APR: 'April',
                MAY: 'May',
                JUN: 'June',
                JUL: 'July',
                AUG: 'August',
                SEP: 'September',
                OCT: 'October',
                NOV: 'November',
                DEC: 'December'
            };

            return monthNames[monthCode] || monthCode;
        }

        function escapeAdminHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            })[char]);
        }

        function formatAdminMetricTons(value) {
            const amount = Number(value) || 0;
            return `${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} mt`;
        }

        function formatAdminCompactNumber(value) {
            return (Number(value) || 0).toLocaleString();
        }

        function getAdminSupplyApiUrl(params = {}) {
            const url = new URL('{{ url('/api/map/supply-forecast') }}', window.location.origin);

            Object.entries(params).forEach(([key, value]) => {
                if (value !== undefined && value !== null && value !== '') {
                    url.searchParams.set(key, value);
                }
            });

            return url.toString();
        }

        function summarizeAdminSupply(rows) {
            return rows.reduce((summary, row) => {
                summary.supply += Number(row.supply_forecast_mt || 0);
                summary.harvested += Number(row.harvested_production_mt || 0);
                summary.remaining += Number(row.net_expected_production_mt || 0);
                summary.damaged += Number(row.damaged_production_mt || 0);
                summary.plans += Number(row.plan_count || 0);
                return summary;
            }, {
                supply: 0,
                harvested: 0,
                remaining: 0,
                damaged: 0,
                plans: 0
            });
        }

        function setAdminSupplyStatus(text, tone = 'neutral') {
            const statusEl = document.getElementById('adminSupplyForecastStatus');
            if (!statusEl) return;

            const toneClasses = {
                neutral: 'bg-gray-100 text-gray-600',
                ready: 'bg-emerald-50 text-emerald-700',
                error: 'bg-red-50 text-red-700'
            };

            statusEl.className = `inline-flex items-center rounded-full px-3 py-1 ${toneClasses[tone] || toneClasses.neutral}`;
            statusEl.textContent = text;
        }

        function renderAdminSupplyMunicipalities(rows) {
            const listEl = document.getElementById('adminSupplyMunicipalityList');
            if (!listEl) return;

            const rankedRows = [...rows]
                .filter((row) => Number(row.supply_forecast_mt || 0) > 0 || Number(row.plan_count || 0) > 0)
                .sort((a, b) => Number(b.supply_forecast_mt || 0) - Number(a.supply_forecast_mt || 0))
                .slice(0, 5);

            if (!rankedRows.length) {
                listEl.innerHTML = '<p class="text-sm text-gray-500">No live crop plans have been reported for this season yet.</p>';
                return;
            }

            listEl.innerHTML = rankedRows.map((row, index) => `
                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 bg-gray-50/70 px-3 py-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">${index + 1}. ${escapeAdminHtml(formatAdminMunicipalityName(row.normalized_municipality || row.municipality))}</p>
                        <p class="text-[11px] text-gray-500">${formatAdminCompactNumber(row.plan_count)} ${Number(row.plan_count || 0) === 1 ? 'plan' : 'plans'} &middot; ${formatAdminCompactNumber(row.farmer_count)} ${Number(row.farmer_count || 0) === 1 ? 'farmer' : 'farmers'}</p>
                    </div>
                    <p class="shrink-0 text-sm font-bold text-emerald-700">${formatAdminMetricTons(row.supply_forecast_mt)}</p>
                </div>
            `).join('');
        }

        function renderAdminSelectedSupply(rows, municipalityName) {
            const listEl = document.getElementById('adminSupplyCropList');
            const areaEl = document.getElementById('adminSupplySelectedArea');
            const countEl = document.getElementById('adminSupplySelectedCropCount');

            if (areaEl) {
                areaEl.textContent = municipalityName;
            }

            const cropRows = [...rows]
                .filter((row) => Number(row.supply_forecast_mt || 0) > 0 || Number(row.plan_count || 0) > 0)
                .sort((a, b) => Number(b.supply_forecast_mt || 0) - Number(a.supply_forecast_mt || 0))
                .slice(0, 4);

            if (countEl) {
                countEl.textContent = `${cropRows.length} ${cropRows.length === 1 ? 'crop' : 'crops'}`;
            }

            if (!listEl) return;

            if (!cropRows.length) {
                listEl.innerHTML = `<p class="text-sm text-gray-500">No live crop plans for ${escapeAdminHtml(municipalityName)} yet.</p>`;
                return;
            }

            listEl.innerHTML = cropRows.map((row) => `
                <div class="rounded-lg border border-gray-100 bg-gray-50/70 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 break-words">${escapeAdminHtml(row.crop || 'Crop')}</p>
                            <p class="text-[11px] text-gray-500">${formatAdminCompactNumber(row.plan_count)} ${Number(row.plan_count || 0) === 1 ? 'plan' : 'plans'} &middot; ${formatAdminCompactNumber(row.harvested_count)} harvested</p>
                        </div>
                        <p class="shrink-0 text-sm font-bold text-emerald-700">${formatAdminMetricTons(row.supply_forecast_mt)}</p>
                    </div>
                    <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
                        <div class="rounded bg-green-50 px-2 py-1">
                            <p class="font-semibold text-green-700">Harvested</p>
                            <p class="text-gray-900">${formatAdminMetricTons(row.harvested_production_mt)}</p>
                        </div>
                        <div class="rounded bg-sky-50 px-2 py-1">
                            <p class="font-semibold text-sky-700">Remaining</p>
                            <p class="text-gray-900">${formatAdminMetricTons(row.net_expected_production_mt)}</p>
                        </div>
                        <div class="rounded bg-red-50 px-2 py-1">
                            <p class="font-semibold text-red-700">Damage</p>
                            <p class="text-gray-900">${formatAdminMetricTons(row.damaged_production_mt)}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function loadAdminSupplyForecast(municipality) {
            const currentYear = new Date().getFullYear();
            const municipalityName = formatAdminMunicipalityName(municipality);

            document.getElementById('adminSupplyForecastYear').textContent = `${currentYear} season`;
            setAdminSupplyStatus('Loading live supply...', 'neutral');

            try {
                const [allResponse, selectedResponse] = await Promise.all([
                    fetch(getAdminSupplyApiUrl({ year: currentYear }), { headers: { 'Accept': 'application/json' } }),
                    fetch(getAdminSupplyApiUrl({ year: currentYear, municipality }), { headers: { 'Accept': 'application/json' } })
                ]);

                if (!allResponse.ok || !selectedResponse.ok) {
                    throw new Error('Supply forecast request failed');
                }

                const [allPayload, selectedPayload] = await Promise.all([
                    allResponse.json(),
                    selectedResponse.json()
                ]);

                const allRows = Array.isArray(allPayload.data) ? allPayload.data : [];
                const selectedRows = Array.isArray(selectedPayload.data) ? selectedPayload.data : [];
                const summary = summarizeAdminSupply(allRows);

                document.getElementById('adminSupplyForecastTotal').textContent = formatAdminMetricTons(summary.supply);
                document.getElementById('adminSupplyHarvestedTotal').textContent = formatAdminMetricTons(summary.harvested);
                document.getElementById('adminSupplyRemainingTotal').textContent = formatAdminMetricTons(summary.remaining);
                document.getElementById('adminSupplyDamagedTotal').textContent = formatAdminMetricTons(summary.damaged);
                document.getElementById('adminSupplyPlanCount').textContent = `${formatAdminCompactNumber(summary.plans)} ${summary.plans === 1 ? 'plan' : 'plans'}`;

                renderAdminSupplyMunicipalities(allRows);
                renderAdminSelectedSupply(selectedRows, municipalityName);
                setAdminSupplyStatus(allRows.length ? 'Live supply loaded' : 'No live crop plans yet', allRows.length ? 'ready' : 'neutral');
            } catch (error) {
                console.error('Unable to load live supply forecast:', error);
                setAdminSupplyStatus('Live supply unavailable', 'error');
                document.getElementById('adminSupplyMunicipalityList').innerHTML = '<p class="text-sm text-red-600">Unable to load municipality supply right now.</p>';
                document.getElementById('adminSupplyCropList').innerHTML = '<p class="text-sm text-red-600">Unable to load selected-area crop supply.</p>';
            }
        }

        async function fetchAdminRecommendationContext(municipality) {
            const monthCode = getAdminCurrentMonthCode();
            const fallbackMonthLabel = getAdminMonthLabel(monthCode);

            try {
                const response = await fetch(`{{ route('admin.recommendations') }}?municipality=${encodeURIComponent(municipality)}&month=${monthCode}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                if (!response.ok) {
                    return {
                        recommendedCrop: '',
                        recommendationMonth: fallbackMonthLabel
                    };
                }

                const data = await response.json();

                return {
                    recommendedCrop: data.recommendations?.[0]?.crop || '',
                    recommendationMonth: data.month || fallbackMonthLabel
                };
            } catch (error) {
                console.error('Unable to load admin recommendation context:', error);

                return {
                    recommendedCrop: '',
                    recommendationMonth: fallbackMonthLabel
                };
            }
        }

        function buildAdminTopCropsInsight(crops, historicalData, predictedData, municipalityName, recommendedCrop = '', recommendationMonth = '') {
            if (!crops.length) {
                return `No crop outlook data is available for ${municipalityName} yet.`;
            }

            const highestPredicted = Math.max(...predictedData);
            const highestHistorical = Math.max(...historicalData);
            const predictedLeaderIndex = highestPredicted > 0 ? predictedData.indexOf(highestPredicted) : -1;
            const historicalLeaderIndex = historicalData.indexOf(highestHistorical);
            const bestIndex = predictedLeaderIndex >= 0 ? predictedLeaderIndex : historicalLeaderIndex;
            const bestCrop = crops[bestIndex] || crops[0];
            const normalizedBestCrop = String(bestCrop || '').trim().toUpperCase();
            const normalizedRecommendedCrop = String(recommendedCrop || '').trim().toUpperCase();

            if (normalizedRecommendedCrop && recommendationMonth) {
                if (normalizedBestCrop === normalizedRecommendedCrop) {
                    return `${bestCrop} stands out as the strongest crop choice for ${recommendationMonth}, and it is also expected to lead overall performance in ${municipalityName} for the rest of the year.`;
                }

                return `${recommendedCrop} stands out as the strongest crop choice for ${recommendationMonth}, while ${bestCrop} is expected to lead overall performance in ${municipalityName} for the rest of the year.`;
            }

            return `${bestCrop} is expected to lead overall performance in ${municipalityName} for the rest of the year, based on historical averages and this year's forecast.`;
        }

        function adminPrefersReducedMotion() {
            return typeof window.matchMedia === 'function'
                && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        }

        function applyAdminInsightAvatarTrim(instance) {
            const svg = instance?.renderer?.svgElement;
            if (!svg) return;

            svg.style.overflow = 'visible';
            svg.style.transformOrigin = '50% 62%';
            svg.style.transform = `translate(0px, 12%) scale(1.48)`;
        }

        function initAdminInsightAnimation() {
            if (adminPrefersReducedMotion()) {
                destroyAdminInsightAnimation();
                return;
            }

            if (typeof lottie === 'undefined') return;

            const lottieOpts = {
                renderer: 'svg',
                loop: true,
                autoplay: false,
                path: '{{ asset('animations/talking-character.json') }}',
                rendererSettings: { preserveAspectRatio: 'xMidYMid slice' }
            };

            if (!adminTopCropsChartState.animationInstance) {
                const desktop = document.getElementById('adminChartInsightAvatar');
                if (desktop) {
                    const desktopInstance = lottie.loadAnimation({ container: desktop, ...lottieOpts });
                    desktopInstance.addEventListener('DOMLoaded', function() {
                        applyAdminInsightAvatarTrim(desktopInstance);
                    });
                    adminTopCropsChartState.animationInstance = desktopInstance;
                }
            }
        }

        function playAdminInsightAnimation() {
            if (adminPrefersReducedMotion()) return;
            initAdminInsightAnimation();
            if (adminTopCropsChartState.animationInstance) adminTopCropsChartState.animationInstance.goToAndPlay(0, true);
        }

        function stopAdminInsightAnimation() {
            const inst = adminTopCropsChartState.animationInstance;
            if (!inst) return;
            const totalFrames = Number(inst.totalFrames || 0);
            if (totalFrames > 1) { inst.goToAndStop(totalFrames - 1, true); }
            else { inst.stop(); }
        }

        function destroyAdminInsightAnimation() {
            if (adminTopCropsChartState.animationInstance) {
                adminTopCropsChartState.animationInstance.destroy();
                adminTopCropsChartState.animationInstance = null;
            }
        }

        function cancelAdminInsightNarration() {
            adminTopCropsChartState.insightToken += 1;

            if (!adminTopCropsChartState.insightTypingTimer) {
                adminTopCropsChartState.isTyping = false;
                return;
            }

            clearInterval(adminTopCropsChartState.insightTypingTimer);
            adminTopCropsChartState.insightTypingTimer = null;
            adminTopCropsChartState.isTyping = false;
        }

        function narrateAdminInsightText(nextText) {
            const insightTextEl = document.getElementById('adminChartInsightText');
            const cardEl = document.getElementById('adminChartInsightCard');
            const safeText = String(nextText || '');

            cancelAdminInsightNarration();

            if (!insightTextEl || !cardEl) {
                return;
            }

            function setAllText(text) {
                insightTextEl.textContent = text;
            }

            if (!safeText || adminPrefersReducedMotion()) {
                setAllText(safeText);
                stopAdminInsightAnimation();
                return;
            }

            setAllText('');
            
            const startTyping = () => {
                cancelAdminInsightNarration();
                setAllText('');
                adminTopCropsChartState.isTyping = true;
                playAdminInsightAnimation();

                const token = adminTopCropsChartState.insightToken;
                const typingDelay = 24;
                let charIndex = 0;

                const timerId = window.setInterval(() => {
                    if (token !== adminTopCropsChartState.insightToken) {
                        clearInterval(timerId);
                        if (adminTopCropsChartState.insightTypingTimer === timerId) {
                            adminTopCropsChartState.insightTypingTimer = null;
                        }
                        return;
                    }

                    charIndex += 1;
                    setAllText(safeText.slice(0, charIndex));

                    if (charIndex < safeText.length) {
                        return;
                    }

                    clearInterval(timerId);
                    if (adminTopCropsChartState.insightTypingTimer === timerId) {
                        adminTopCropsChartState.insightTypingTimer = null;
                    }
                    adminTopCropsChartState.isTyping = false;

                    window.setTimeout(() => {
                        if (token === adminTopCropsChartState.insightToken) {
                            stopAdminInsightAnimation();
                        }
                    }, 200);
                }, typingDelay);

                adminTopCropsChartState.insightTypingTimer = timerId;
            };

            if (adminTopCropsChartState.insightObserver) {
                adminTopCropsChartState.insightObserver.disconnect();
            }

            if (typeof IntersectionObserver !== 'undefined') {
                adminTopCropsChartState.insightObserver = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        adminTopCropsChartState.insightObserver.disconnect();
                        startTyping();
                    }
                }, { threshold: 0.3 });
                adminTopCropsChartState.insightObserver.observe(cardEl);
            } else {
                startTyping();
            }
        }

        function mergeAdminTopCropRows(data, currentYear) {
            const merged = new Map();

            (data.historical_top5?.crops || []).forEach((crop) => {
                const key = String(crop.crop || '').toUpperCase();

                if (!merged.has(key)) {
                    merged.set(key, {
                        crop: crop.crop,
                        historical: Number(crop.yearly_data?.average || 0),
                        predicted: 0,
                    });
                }
            });

            (data.predicted_top5?.crops || []).forEach((crop) => {
                const key = String(crop.crop || '').toUpperCase();
                const currentYearForecast = (crop.forecasts || []).find((forecast) => Number(forecast.year) === currentYear);
                const predictedValue = Number(currentYearForecast?.production || 0);

                if (!merged.has(key)) {
                    merged.set(key, {
                        crop: crop.crop,
                        historical: 0,
                        predicted: predictedValue,
                    });
                    return;
                }

                merged.get(key).predicted = predictedValue;
            });

            return Array.from(merged.values()).slice(0, 5);
        }

        function renderAdminTopCropsChart(rows, municipalityName, currentYear, shouldNarrate = true) {
            const loadingEl = document.getElementById('adminChartLoading');
            const containerEl = document.getElementById('adminChartContainer');
            const errorEl = document.getElementById('adminChartError');
            const insightTextEl = document.getElementById('adminChartInsightText');
            const crops = rows.map((row) => row.crop);
            const historicalData = rows.map((row) => row.historical);
            const predictedData = rows.map((row) => row.predicted);
            const mobile = isAdminMobile();
            const chartLabels = crops.map((crop) => mobile ? formatAdminCropAxisLabel(crop) : crop);

            if (!rows.length) {
                throw new Error('No chart data available');
            }

            if (adminTopCropsChart) {
                adminTopCropsChart.destroy();
            }

            const insightText = buildAdminTopCropsInsight(
                crops,
                historicalData,
                predictedData,
                municipalityName,
                adminTopCropsChartState.recommendedCrop,
                adminTopCropsChartState.recommendationMonth
            );

            if (shouldNarrate) {
                narrateAdminInsightText(insightText);
            } else {
                cancelAdminInsightNarration();
                insightTextEl.textContent = insightText;
                stopAdminInsightAnimation();
            }

            const ctx = document.getElementById('adminTopCropsChart').getContext('2d');
            adminTopCropsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Historical Average',
                            data: historicalData,
                            backgroundColor: 'rgba(34, 197, 94, 0.72)',
                            borderColor: 'rgba(34, 197, 94, 1)',
                            borderWidth: 2,
                            borderRadius: 10,
                            barPercentage: mobile ? 0.7 : 0.82,
                            categoryPercentage: mobile ? 0.78 : 0.9
                        },
                        {
                            label: 'This Year Forecast',
                            data: predictedData,
                            backgroundColor: 'rgba(59, 130, 246, 0.72)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 2,
                            borderRadius: 10,
                            barPercentage: mobile ? 0.7 : 0.82,
                            categoryPercentage: mobile ? 0.78 : 0.9
                        }
                    ]
                },
                options: {
                    indexAxis: mobile ? 'y' : 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 12,
                                font: {
                                    size: mobile ? 10 : 12
                                },
                                padding: mobile ? 10 : 14
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    const value = mobile ? context.parsed.x : context.parsed.y;
                                    return label + ': ' + value.toFixed(2) + ' MT';
                                }
                            },
                            titleFont: {
                                size: mobile ? 10 : 12
                            },
                            bodyFont: {
                                size: mobile ? 9 : 11
                            }
                        }
                    },
                    scales: {
                        [mobile ? 'x' : 'y']: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toFixed(0);
                                },
                                font: {
                                    size: mobile ? 9 : 11
                                }
                            },
                            grid: {
                                color: 'rgba(148, 163, 184, 0.18)'
                            }
                        },
                        [mobile ? 'y' : 'x']: {
                            ticks: {
                                font: {
                                    size: mobile ? 10 : 11
                                },
                                autoSkip: false,
                                maxRotation: 0,
                                minRotation: 0,
                                padding: mobile ? 6 : 8
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    layout: {
                        padding: {
                            left: mobile ? 6 : 10,
                            right: mobile ? 12 : 10,
                            top: 6,
                            bottom: mobile ? 6 : 10
                        }
                    }
                }
            });

            loadingEl.classList.add('hidden');
            errorEl.classList.add('hidden');
            containerEl.classList.remove('hidden');
        }

        async function loadAdminTopCropsChart(municipality) {
            const loadingEl = document.getElementById('adminChartLoading');
            const containerEl = document.getElementById('adminChartContainer');
            const errorEl = document.getElementById('adminChartError');
            const areaLabelEl = document.getElementById('adminChartAreaLabel');
            const insightTextEl = document.getElementById('adminChartInsightText');
            const municipalityName = formatAdminMunicipalityName(municipality);

            cancelAdminInsightNarration();
            stopAdminInsightAnimation();
            areaLabelEl.textContent = municipalityName;
            insightTextEl.textContent = `Loading the strongest crop outlook for ${municipalityName}...`;

            loadingEl.classList.remove('hidden');
            containerEl.classList.add('hidden');
            errorEl.classList.add('hidden');

            try {
                const recommendationContextPromise = fetchAdminRecommendationContext(municipality);

                const response = await fetch('{{ config("services.ml_api.url") }}/api/top-crops', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ MUNICIPALITY: municipality })
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch data');
                }

                const data = await response.json();

                if (!data.success) {
                    throw new Error('API returned error');
                }

                const currentYear = new Date().getFullYear();
                const rows = mergeAdminTopCropRows(data, currentYear);
                const recommendationContext = await recommendationContextPromise;

                if (!rows.length) {
                    throw new Error('No chart data available');
                }

                adminTopCropsChartState = {
                    ...adminTopCropsChartState,
                    municipality,
                    municipalityName,
                    currentYear,
                    rows,
                    recommendedCrop: recommendationContext.recommendedCrop,
                    recommendationMonth: recommendationContext.recommendationMonth
                };

                renderAdminTopCropsChart(rows, municipalityName, currentYear);

            } catch (error) {
                console.error('Error loading chart:', error);
                loadingEl.classList.add('hidden');
                containerEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
                insightTextEl.textContent = `The outlook summary for ${municipalityName} is temporarily unavailable.`;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const municipalitySelect = document.getElementById('adminMunicipalitySelect');
            const adminMobileLayoutQuery = window.matchMedia('(max-width: 767px)');

            initAdminInsightAnimation();
            loadAdminSupplyForecast(municipalitySelect.value);
            loadAdminTopCropsChart(municipalitySelect.value);

            municipalitySelect.addEventListener('change', function() {
                loadAdminSupplyForecast(this.value);
                loadAdminTopCropsChart(this.value);
            });

            const rerenderAdminTopCropsChart = function() {
                if (!adminTopCropsChartState.rows.length) {
                    return;
                }

                if (adminTopCropsChartState.municipality !== municipalitySelect.value) {
                    return;
                }

                renderAdminTopCropsChart(
                    adminTopCropsChartState.rows,
                    adminTopCropsChartState.municipalityName,
                    adminTopCropsChartState.currentYear,
                    false
                );
            };

            if (typeof adminMobileLayoutQuery.addEventListener === 'function') {
                adminMobileLayoutQuery.addEventListener('change', rerenderAdminTopCropsChart);
            } else if (typeof adminMobileLayoutQuery.addListener === 'function') {
                adminMobileLayoutQuery.addListener(rerenderAdminTopCropsChart);
            }

            window.addEventListener('beforeunload', function() {
                cancelAdminInsightNarration();
                destroyAdminInsightAnimation();
            });
        });
    </script>
</x-admin-layout>

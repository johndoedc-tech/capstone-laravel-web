<x-admin-layout>
    <div class="py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Header Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:p-6 mb-5 lg:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-1">
                            Admin Dashboard
                        </h1>
                        <p class="text-sm lg:text-base text-gray-500">
                            Welcome back, <span class="font-semibold text-gray-900">{{ Auth::user()->name }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-left sm:text-right">
                        <div class="hidden sm:flex items-center justify-center w-10 h-10 rounded-lg bg-primary-50">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs lg:text-sm text-gray-400">{{ now()->format('l') }}</p>
                            <p class="text-base lg:text-lg font-semibold text-gray-900">{{ now()->format('F d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-5 lg:mb-6">
                <!-- Total Users -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-5 hover:shadow-md transition-all duration-200 hover:border-blue-200">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <div class="bg-blue-50 p-2.5 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900">{{ \App\Models\User::count() }}</p>
                            <p class="text-xs lg:text-sm text-gray-500 mt-0.5">Total Users</p>
                        </div>
                    </div>
                </div>

                <!-- Crop Records -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-5 hover:shadow-md transition-all duration-200 hover:border-green-200">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <div class="bg-green-50 p-2.5 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900">
                                {{ number_format(\App\Models\CropProduction::count()) }}</p>
                            <p class="text-xs lg:text-sm text-gray-500 mt-0.5">Crop Records</p>
                        </div>
                    </div>
                </div>

                <!-- Predictions -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-5 hover:shadow-md transition-all duration-200 hover:border-purple-200">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <div class="bg-purple-50 p-2.5 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900">
                                {{ number_format(\App\Models\Prediction::count()) }}</p>
                            <p class="text-xs lg:text-sm text-gray-500 mt-0.5">Predictions Made</p>
                        </div>
                    </div>
                </div>

                <!-- Municipalities -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 lg:p-5 hover:shadow-md transition-all duration-200 hover:border-amber-200">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <div class="bg-amber-50 p-2.5 rounded-lg">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-2xl lg:text-3xl font-bold text-gray-900">13</p>
                            <p class="text-xs lg:text-sm text-gray-500 mt-0.5">Municipalities</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-5 mb-5 lg:mb-6">
                <!-- Manage Crop Data -->
                <a href="{{ route('admin.crop-data.index') }}"
                    class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:p-6 hover:shadow-md hover:border-green-200 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div
                            class="bg-green-50 group-hover:bg-green-100 p-3 rounded-xl flex-shrink-0 transition-colors">
                            <svg class="w-6 h-6 lg:w-7 lg:h-7 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4">
                                </path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-1">Crop Data Management</h3>
                            <p class="text-xs lg:text-sm text-gray-500 mb-3">Import, view, and manage agricultural
                                production records</p>
                            <span
                                class="inline-flex items-center gap-1 text-green-600 text-xs lg:text-sm font-medium group-hover:gap-2 transition-all">
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
                    class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:p-6 hover:shadow-md hover:border-blue-200 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div class="bg-blue-50 group-hover:bg-blue-100 p-3 rounded-xl flex-shrink-0 transition-colors">
                            <svg class="w-6 h-6 lg:w-7 lg:h-7 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-1">User Management</h3>
                            <p class="text-xs lg:text-sm text-gray-500 mb-3">Manage farmers, admins, and user
                                permissions</p>
                            <span
                                class="inline-flex items-center gap-1 text-blue-600 text-xs lg:text-sm font-medium group-hover:gap-2 transition-all">
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
                    class="group bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:p-6 hover:shadow-md hover:border-purple-200 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <div
                            class="bg-purple-50 group-hover:bg-purple-100 p-3 rounded-xl flex-shrink-0 transition-colors">
                            <svg class="w-6 h-6 lg:w-7 lg:h-7 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-1">Reports & Analytics</h3>
                            <p class="text-xs lg:text-sm text-gray-500 mb-3">Generate and export system reports and
                                statistics</p>
                            <span
                                class="inline-flex items-center gap-1 text-purple-600 text-xs lg:text-sm font-medium group-hover:gap-2 transition-all">
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

            <!-- Top 5 Crops by Production Chart -->
            <div class="mb-5 lg:mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:p-6">
                    <div class="flex flex-col gap-3 mb-4">
                        <h3 class="text-base lg:text-lg font-semibold text-gray-900">Top 5 Crops by Production</h3>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <label for="adminMunicipalitySelect"
                                class="text-xs lg:text-sm text-gray-600 whitespace-nowrap">Municipality:</label>
                            <select id="adminMunicipalitySelect"
                                class="w-full sm:w-auto border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-xs lg:text-sm">
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
                        <div class="w-full overflow-hidden">
                            <canvas id="adminTopCropsChart"></canvas>
                        </div>
                        <div class="mt-4 text-xs lg:text-sm text-gray-500 border-t border-gray-200 pt-3 space-y-1">
                            <p><strong>Historical (2015-2024):</strong> Average annual production from actual data</p>
                            <p><strong>Predicted:</strong> Current year forecast using ML models</p>
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">
                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:p-6"
                    x-data="{ showAllPredictions: false }">
                    @php
                        $selectedFilterLabel = $activityStats['filters'][$activityFilter]['label'] ?? 'All';
                        $selectedFilterText = $selectedFilterLabel === 'All' ? 'activity' : strtolower($selectedFilterLabel);
                    @endphp

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base lg:text-lg font-semibold text-gray-900">Recent Activity</h3>
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

                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach($activityStats['filters'] as $filterKey => $filter)
                            <a href="{{ route('admin.dashboard', ['activity_type' => $filterKey]) }}"
                                class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-[11px] font-medium transition-colors {{ $activityFilter === $filterKey ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:text-gray-800' }}">
                                <span>{{ $filter['label'] }}</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] {{ $activityFilter === $filterKey ? 'bg-white text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ number_format($filter['count']) }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <div x-show="!showAllPredictions" class="space-y-3">
                        @forelse(($compactRecentActivities ?? collect()) as $activity)
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
                        @forelse(($recentActivities ?? collect()) as $activity)
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 lg:p-6">
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-4">System Overview</h3>
                    <div class="space-y-3">
                        <div
                            class="flex items-center justify-between p-3.5 bg-blue-50 rounded-xl border border-blue-100">
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
                            <p class="text-xl font-bold text-blue-600">
                                {{ \App\Models\User::where('role', 'farmer')->count() }}</p>
                        </div>

                        <div
                            class="flex items-center justify-between p-3.5 bg-green-50 rounded-xl border border-green-100">
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
                            <p class="text-xl font-bold text-green-600">2015-2024</p>
                        </div>

                        <div
                            class="flex items-center justify-between p-3.5 bg-purple-50 rounded-xl border border-purple-100">
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
                            class="flex items-center justify-between p-3.5 bg-amber-50 rounded-xl border border-amber-100">
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
                            <p class="text-xl font-bold text-amber-600">13</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        let adminTopCropsChart = null;

        function isAdminMobile() {
            return window.innerWidth < 768;
        }

        async function loadAdminTopCropsChart(municipality) {
            const loadingEl = document.getElementById('adminChartLoading');
            const containerEl = document.getElementById('adminChartContainer');
            const errorEl = document.getElementById('adminChartError');

            loadingEl.classList.remove('hidden');
            containerEl.classList.add('hidden');
            errorEl.classList.add('hidden');

            try {
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

                const crops = data.historical_top5.crops.map(crop => crop.crop);
                const historicalData = data.historical_top5.crops.map(crop => crop.yearly_data.average);

                const currentYear = new Date().getFullYear();
                const predictedData = data.predicted_top5.crops.map(crop => {
                    const currentYearForecast = crop.forecasts.find(f => f.year === currentYear);
                    return currentYearForecast ? currentYearForecast.production : 0;
                });

                if (adminTopCropsChart) {
                    adminTopCropsChart.destroy();
                }

                const mobile = isAdminMobile();
                const municipalityName = municipality.charAt(0) + municipality.slice(1).toLowerCase().replace('trinidad', ' Trinidad');

                const ctx = document.getElementById('adminTopCropsChart').getContext('2d');
                adminTopCropsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: crops,
                        datasets: [
                            {
                                label: mobile ? 'Historical' : 'Historical Avg (2015-2024)',
                                data: historicalData,
                                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: mobile ? 1 : 2
                            },
                            {
                                label: mobile ? `Predicted` : `Predicted (${currentYear})`,
                                data: predictedData,
                                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: mobile ? 1 : 2
                            }
                        ]
                    },
                    options: {
                        indexAxis: mobile ? 'y' : 'x',
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: mobile ? 1 : 2,
                        plugins: {
                            title: {
                                display: true,
                                text: mobile ? `Top 5 - ${municipalityName}` : `Top 5 Crops in ${municipalityName}`,
                                font: {
                                    size: mobile ? 12 : 16,
                                    weight: 'bold'
                                },
                                padding: {
                                    top: mobile ? 5 : 10,
                                    bottom: mobile ? 10 : 20
                                }
                            },
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: mobile ? 10 : 40,
                                    font: {
                                        size: mobile ? 9 : 12
                                    },
                                    padding: mobile ? 8 : 10
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
                                title: {
                                    display: !mobile,
                                    text: 'Average Production (MT)',
                                    font: {
                                        size: mobile ? 10 : 12
                                    }
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value.toFixed(0);
                                    },
                                    font: {
                                        size: mobile ? 9 : 11
                                    }
                                },
                                grid: {
                                    display: true
                                }
                            },
                            [mobile ? 'y' : 'x']: {
                                title: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: mobile ? 10 : 11
                                    },
                                    autoSkip: false,
                                    maxRotation: 0,
                                    minRotation: 0
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        layout: {
                            padding: {
                                left: mobile ? 5 : 10,
                                right: mobile ? 5 : 10,
                                top: mobile ? 5 : 10,
                                bottom: mobile ? 5 : 10
                            }
                        }
                    }
                });

                loadingEl.classList.add('hidden');
                containerEl.classList.remove('hidden');

            } catch (error) {
                console.error('Error loading chart:', error);
                loadingEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const municipalitySelect = document.getElementById('adminMunicipalitySelect');

            loadAdminTopCropsChart(municipalitySelect.value);

            municipalitySelect.addEventListener('change', function() {
                loadAdminTopCropsChart(this.value);
            });

            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    loadAdminTopCropsChart(municipalitySelect.value);
                }, 250);
            });
        });
    </script>
</x-admin-layout>

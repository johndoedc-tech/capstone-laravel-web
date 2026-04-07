<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
                    {{ __('User Activity') }}
                </h2>
                <p class="text-xs lg:text-sm text-gray-600 mt-1">
                    Complete feed of tracked user actions across the platform
                </p>
            </div>
            <a href="{{ route('admin.dashboard', ['activity_type' => $activityFilter]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-4 lg:py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-4 lg:space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                    <p class="text-xs text-gray-600">Total Activities</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($activityStats['total_activities']) }}</p>
                    <p class="text-xs text-gray-500">Tracked records</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                    <p class="text-xs text-gray-600">Predictions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($activityStats['predictions']) }}</p>
                    <p class="text-xs text-gray-500">Prediction actions</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-orange-500">
                    <p class="text-xs text-gray-600">Forum Activity</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($activityStats['forum_interactions']) }}</p>
                    <p class="text-xs text-gray-500">Posts and replies</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <p class="text-xs text-gray-600">Calendar Entries</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($activityStats['calendar_events']) }}</p>
                    <p class="text-xs text-gray-500">Notes and reminders</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ showAllPredictions: false }">
                <div class="p-4 lg:p-6">
                    @php
                        $selectedFilterLabel = $activityStats['filters'][$activityFilter]['label'] ?? 'All';
                        $selectedFilterText = $selectedFilterLabel === 'All' ? 'activity' : strtolower($selectedFilterLabel) . ' activity';
                    @endphp

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base lg:text-lg font-semibold text-gray-900">All Activities</h3>
                            <p class="text-xs lg:text-sm text-gray-500 mt-1">
                                {{ $selectedFilterLabel === 'All' ? 'Registrations, predictions, forum activity, and calendar actions' : 'Showing ' . $selectedFilterText . ' only' }}
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 p-1">
                                <button type="button" @click="showAllPredictions = false"
                                    :class="showAllPredictions ? 'text-gray-500' : 'bg-white text-gray-900 shadow-sm'"
                                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                                    Compact predictions
                                </button>
                                <button type="button" @click="showAllPredictions = true"
                                    :class="showAllPredictions ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500'"
                                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors">
                                    Show all entries
                                </button>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ number_format($activityStats['registrations']) }} users registered
                            </span>
                        </div>
                    </div>

                    <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                        <p class="text-xs lg:text-sm text-blue-800">
                            Compact mode groups rapid prediction activity from the same user so the feed stays readable.
                            Switch to <span class="font-semibold">Show all entries</span> when you want every prediction listed one by one.
                        </p>
                    </div>

                    <div class="mb-5 flex flex-wrap gap-2">
                        @foreach($activityStats['filters'] as $filterKey => $filter)
                            <a href="{{ route('admin.activities.index', ['activity_type' => $filterKey]) }}"
                                class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors {{ $activityFilter === $filterKey ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:text-gray-800' }}">
                                <span>{{ $filter['label'] }}</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] {{ $activityFilter === $filterKey ? 'bg-white text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ number_format($filter['count']) }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    @if($activities->count() > 0)
                        <div x-show="!showAllPredictions" class="space-y-3">
                            @foreach(($compactActivities ?? collect()) as $activity)
                                <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex-shrink-0 mt-0.5">
                                        @include('admin.activities.partials.icon', ['type' => $activity->activity_type])
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $activity->user_name }}</p>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $activity->user_role === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                        {{ $activity->role_label }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-white text-gray-600 border border-gray-200">
                                                        {{ $activity->type_label }}
                                                    </span>
                                                    @if(($activity->grouped_prediction_count ?? 1) > 1)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-700">
                                                            {{ $activity->grouped_prediction_count }} entries
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-800 mt-1">{{ $activity->title }}</p>
                                                <p class="text-xs lg:text-sm text-gray-500 mt-1">{{ $activity->description }}</p>
                                            </div>

                                            <div class="text-left sm:text-right flex-shrink-0">
                                                <p class="text-xs lg:text-sm text-gray-500">{{ $activity->activity_at->format('M d, Y h:i A') }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $activity->activity_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div x-show="showAllPredictions" class="space-y-3">
                            @foreach($activities as $activity)
                                <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex-shrink-0 mt-0.5">
                                        @include('admin.activities.partials.icon', ['type' => $activity->activity_type])
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $activity->user_name }}</p>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $activity->user_role === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                        {{ $activity->role_label }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-white text-gray-600 border border-gray-200">
                                                        {{ $activity->type_label }}
                                                    </span>
                                                </div>
                                                <p class="text-sm text-gray-800 mt-1">{{ $activity->title }}</p>
                                                <p class="text-xs lg:text-sm text-gray-500 mt-1">{{ $activity->description }}</p>
                                            </div>

                                            <div class="text-left sm:text-right flex-shrink-0">
                                                <p class="text-xs lg:text-sm text-gray-500">{{ $activity->activity_at->format('M d, Y h:i A') }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $activity->activity_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $activities->appends(['activity_type' => $activityFilter])->links() }}
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-lg font-medium">No {{ $selectedFilterText }} found</p>
                            <p class="text-sm mt-1">Try a different activity type filter or check back after more platform usage.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

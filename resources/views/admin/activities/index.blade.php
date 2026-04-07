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
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 lg:p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base lg:text-lg font-semibold text-gray-900">All Activities</h3>
                            <p class="text-xs lg:text-sm text-gray-500 mt-1">
                                Registrations, predictions, forum activity, and calendar actions
                            </p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            {{ number_format($activityStats['registrations']) }} users registered
                        </span>
                    </div>

                    @if($activities->count() > 0)
                        <div class="space-y-3">
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
                            {{ $activities->links() }}
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-lg font-medium">No activity found</p>
                            <p class="text-sm mt-1">Tracked user actions will appear here once the platform is in use.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
            {{ __('My Farm Calendar') }}
        </h2>
    </x-slot>

    <div class="py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto" x-data="farmerCalendar()">
            
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-1">📅 My Farm Calendar</h1>
                        <p class="text-sm lg:text-base text-gray-600">Track your farming activities, notes, and reminders</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="goToToday()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                            Today
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                
                <!-- Calendar Section -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
                    <!-- Month Navigation -->
                    <div class="flex items-center justify-between mb-6">
                        <button @click="prevMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <h2 class="text-xl font-bold text-gray-900" x-text="monthYearDisplay"></h2>
                        <button @click="nextMonth()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="selectedDate" class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Selected date</p>
                            <p class="text-sm font-medium text-gray-900" x-text="selectedDateDisplay"></p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button @click="openAddModal('damage_report')" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                                Damage Report
                            </button>
                            <button @click="openAddModal('crop_plan')" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                                Plan a Crop
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-1 lg:gap-2">
                        <!-- Day Headers -->
                        <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                            <div class="text-center text-xs lg:text-sm font-semibold text-gray-500 py-2 lg:py-3" x-text="day"></div>
                        </template>
                        
                        <!-- Calendar Days -->
                        <template x-for="day in calendarDays" :key="day.date">
                            <div 
                                @click="day.isCurrentMonth && selectDay(day)"
                                :class="{
                                    'bg-gray-50 text-gray-300': !day.isCurrentMonth,
                                    'bg-white hover:bg-orange-50 cursor-pointer': day.isCurrentMonth,
                                    'ring-2 ring-orange-500 bg-orange-50': day.isSelected,
                                    'bg-orange-100': day.isToday && !day.isSelected
                                }"
                                class="min-h-[80px] lg:min-h-[100px] p-2 border border-gray-100 rounded-lg text-center relative transition-all">
                                <span class="text-sm lg:text-base font-medium" :class="day.isToday ? 'text-orange-600 font-bold' : ''" x-text="day.day"></span>
                                
                                <!-- Event Indicators -->
                                <div class="mt-1 space-y-1">
                                    <template x-for="event in (eventsByDay[day.date] || []).slice(0, 2)" :key="event.id">
                                        <div 
                                            class="text-[10px] lg:text-xs px-1 py-0.5 rounded truncate"
                                            :class="{
                                                'bg-red-100 text-red-700': event.category === 'pest' || event.category === 'damage_report',
                                                'bg-green-100 text-green-700': event.category === 'harvest',
                                                'bg-emerald-100 text-emerald-700': event.category === 'planting' || event.category === 'crop_plan',
                                                'bg-blue-100 text-blue-700': event.category === 'fertilizer',
                                                'bg-yellow-100 text-yellow-700': event.category === 'weather',
                                                'bg-gray-100 text-gray-700': event.category === 'other'
                                            }"
                                            x-text="event.title">
                                        </div>
                                    </template>
                                    <div x-show="(eventsByDay[day.date] || []).length > 2" class="text-[10px] text-gray-400">
                                        +<span x-text="(eventsByDay[day.date] || []).length - 2"></span> more
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t text-xs text-gray-500">
                        <span class="font-medium">Categories:</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-100 border border-red-200"></span> Pest</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-100 border border-red-200"></span> Damage</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-100 border border-green-200"></span> Harvest</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-200"></span> Planting</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-200"></span> Crop Plan</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-100 border border-blue-200"></span> Fertilizer</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-100 border border-yellow-200"></span> Weather</span>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4 lg:space-y-6">
                    
                    <!-- Upcoming Reminders -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-xl">🔔</span>
                            <h3 class="font-semibold text-gray-900">Upcoming Reminders</h3>
                        </div>
                        
                        <div x-show="upcomingReminders.length === 0" class="text-sm text-gray-400 text-center py-4">
                            No upcoming reminders
                        </div>
                        
                        <div class="space-y-3">
                            <template x-for="reminder in upcomingReminders" :key="reminder.id">
                                <div class="flex items-start gap-3 p-3 bg-amber-50 rounded-lg border border-amber-100">
                                    <span class="text-lg" x-text="reminder.category_icon"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 text-sm" x-text="reminder.title"></p>
                                        <p class="text-xs text-amber-600 mt-0.5">
                                            <span x-text="reminder.is_today ? 'Today' : (reminder.is_tomorrow ? 'Tomorrow' : reminder.day_name + ', ' + reminder.date)"></span>
                                            <span x-show="reminder.reminder_time" x-text="' at ' + reminder.reminder_time"></span>
                                        </p>
                                        <span x-show="reminder.crop" class="inline-block text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded mt-1" x-text="reminder.crop"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Selected Day Details -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
                        <div x-show="!selectedDate" class="text-center py-8 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-sm">Select a day to view or add events</p>
                        </div>

                        <div x-show="selectedDate">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-900" x-text="selectedDateDisplay"></h3>
                            </div>

                            <!-- Add Buttons -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                                <button @click="openAddModal('note')" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors">
                                    <span>📝</span> Add Note
                                </button>
                                <button @click="openAddModal('reminder')" class="text-sm bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors">
                                    <span>🔔</span> Reminder
                                </button>
                            </div>

                            <!-- Planted Crop Details -->
                            <div x-show="selectedCropPlanEvents.length > 0" class="mb-4 space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-semibold text-gray-900">Planted Crop Details</h4>
                                    <span class="text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-0.5">
                                        <span x-text="selectedCropPlanEvents.length"></span>
                                        <span x-text="selectedCropPlanEvents.length === 1 ? ' crop' : ' crops'"></span>
                                    </span>
                                </div>

                                <template x-for="plan in selectedCropPlanEvents" :key="'details-' + plan.id">
                                    <div class="rounded-lg border border-emerald-100 bg-emerald-50/70 p-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate" x-text="plan.title"></p>
                                                <p class="text-xs text-emerald-700 mt-0.5">
                                                    <span x-text="plan.crop || 'Crop plan'"></span>
                                                    <span> planted on </span>
                                                    <span x-text="formatDisplayDate(plan.date)"></span>
                                                </p>
                                            </div>
                                            <span class="text-[11px] bg-white text-emerald-700 border border-emerald-100 rounded px-1.5 py-0.5">Crop Plan</span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2 mt-3">
                                            <div class="rounded-md bg-white/80 px-2.5 py-2">
                                                <p class="text-[11px] uppercase font-semibold text-gray-500">Area</p>
                                                <p class="text-sm font-medium text-gray-900" x-text="formatSquareMeters(plan.desired_area_sqm) || '-'"></p>
                                            </div>
                                            <div class="rounded-md bg-white/80 px-2.5 py-2">
                                                <p class="text-[11px] uppercase font-semibold text-gray-500">Water</p>
                                                <p class="text-sm font-medium text-gray-900" x-text="formatCropPlanOption(plan.water_source) || '-'"></p>
                                            </div>
                                            <div class="rounded-md bg-white/80 px-2.5 py-2">
                                                <p class="text-[11px] uppercase font-semibold text-gray-500">Seed Type</p>
                                                <p class="text-sm font-medium text-gray-900" x-text="formatCropPlanOption(plan.planting_material) || '-'"></p>
                                            </div>
                                            <div class="rounded-md bg-white/80 px-2.5 py-2">
                                                <p class="text-[11px] uppercase font-semibold text-gray-500">Harvest</p>
                                                <p class="text-sm font-medium text-gray-900" x-text="plan.estimated_harvest_date ? formatDisplayDate(plan.estimated_harvest_date) : '-'"></p>
                                            </div>
                                        </div>

                                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <div x-show="plan.predicted_production_mt" class="rounded-md border border-orange-100 bg-orange-50 px-2.5 py-2">
                                                <p class="text-[11px] uppercase font-semibold text-orange-700">Predicted Production</p>
                                                <p class="text-sm font-semibold text-gray-900" x-text="formatMetricTons(plan.predicted_production_mt)"></p>
                                                <p x-show="plan.prediction_confidence" class="text-[11px] text-orange-700 mt-0.5" x-text="'Confidence: ' + formatPercent(plan.prediction_confidence)"></p>
                                            </div>

                                            <div class="rounded-md border border-red-100 bg-red-50 px-2.5 py-2">
                                                <p class="text-[11px] uppercase font-semibold text-red-700">Damage Status</p>
                                                <p class="text-sm font-semibold text-gray-900" x-text="formatCropPlanDamageStatus(plan)"></p>
                                                <p class="text-[11px] text-red-700 mt-0.5" x-text="formatCropPlanRemainingStatus(plan)"></p>
                                            </div>
                                        </div>

                                        <div x-show="getCropPlanSchedule(plan).length > 0" class="mt-3">
                                            <p class="text-[11px] uppercase font-semibold text-gray-500 mb-1.5">Generated Schedule</p>
                                            <div class="space-y-1.5">
                                                <template x-for="item in getCropPlanSchedule(plan)" :key="plan.id + '-' + item.label + '-' + item.date">
                                                    <div class="flex items-center justify-between gap-3 rounded-md bg-white/80 px-2.5 py-1.5">
                                                        <span class="text-xs font-medium text-gray-700 truncate" x-text="item.label"></span>
                                                        <span class="text-xs text-gray-500 whitespace-nowrap" x-text="formatDisplayDate(item.date)"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <p x-show="plan.description" class="mt-3 text-xs text-gray-600" x-text="plan.description"></p>
                                    </div>
                                </template>
                            </div>

                            <!-- Events List -->
                            <div x-show="selectedDayEvents.length > 0" class="space-y-2 max-h-[400px] overflow-y-auto">
                                <template x-for="calEvent in selectedDayEvents" :key="calEvent.id">
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg group hover:bg-gray-100 transition-colors">
                                        <span class="text-lg" x-text="calEvent.category_icon"></span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-medium text-gray-900 text-sm" :class="calEvent.is_completed ? 'line-through text-gray-400' : ''" x-text="calEvent.title"></span>
                                                <span x-show="calEvent.category === 'crop_plan'" class="text-xs bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded">Crop Plan</span>
                                                <span x-show="calEvent.category === 'damage_report'" class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Damage Report</span>
                                                <span x-show="calEvent.type === 'reminder'" class="text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded">🔔</span>
                                            </div>
                                            <p x-show="calEvent.description" class="text-xs text-gray-500 mt-1" x-text="calEvent.description"></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span x-show="calEvent.crop" class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded" x-text="calEvent.crop"></span>
                                                <span x-show="calEvent.desired_area_sqm" class="text-xs bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded" x-text="formatSquareMeters(calEvent.desired_area_sqm)"></span>
                                                <span x-show="calEvent.damage_area_sqm" class="text-xs bg-red-50 text-red-700 px-1.5 py-0.5 rounded" x-text="'Damage: ' + formatSquareMeters(calEvent.damage_area_sqm)"></span>
                                                <span x-show="calEvent.water_source" class="text-xs bg-sky-50 text-sky-700 px-1.5 py-0.5 rounded" x-text="formatCropPlanOption(calEvent.water_source)"></span>
                                                <span x-show="calEvent.planting_material" class="text-xs bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded" x-text="formatCropPlanOption(calEvent.planting_material)"></span>
                                                <span x-show="calEvent.estimated_harvest_date" class="text-xs bg-green-50 text-green-700 px-1.5 py-0.5 rounded" x-text="'Harvest: ' + formatDisplayDate(calEvent.estimated_harvest_date)"></span>
                                                <span x-show="calEvent.crop_plan_stage" class="text-xs bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded" x-text="formatCropPlanStage(calEvent.crop_plan_stage)"></span>
                                                <span x-show="calEvent.predicted_production_mt" class="text-xs bg-orange-50 text-orange-700 px-1.5 py-0.5 rounded" x-text="'Pred: ' + formatMetricTons(calEvent.predicted_production_mt)"></span>
                                                <span x-show="calEvent.reminder_time" class="text-xs text-gray-400" x-text="calEvent.reminder_time"></span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button @click="toggleEventComplete(calEvent)" class="p-1.5 hover:bg-gray-200 rounded transition-colors" :title="calEvent.is_completed ? 'Mark incomplete' : 'Mark complete'">
                                                <svg class="w-4 h-4" :class="calEvent.is_completed ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                            <button @click="deleteEvent(calEvent)" class="p-1.5 hover:bg-red-100 rounded text-gray-400 hover:text-red-500 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            
                            <p x-show="selectedDayEvents.length === 0" class="text-sm text-gray-400 text-center py-6">
                                No events for this day.<br>Plan a crop, add a note, or set a reminder.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Event Modal -->
            <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

                    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-4 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-4 pb-3 sm:px-5 sm:pt-5 sm:pb-4">
                            <h3 class="text-base font-semibold text-gray-900 mb-3" x-text="modalTitle"></h3>
                            
                            <div class="space-y-3">
                                <!-- Title -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1" x-text="modalType === 'crop_plan' ? 'Plan Title (optional)' : (modalType === 'damage_report' ? 'Report Title (optional)' : 'Title *')"></label>
                                    <input type="text" x-model="eventForm.title" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-orange-500 focus:border-orange-500" :placeholder="modalType === 'crop_plan' ? 'e.g., Start cabbage seedbed' : (modalType === 'damage_report' ? 'e.g., Typhoon damage' : 'e.g., Cabbage harvest day')">
                                </div>

                                <!-- Category -->
                                <div x-show="modalType !== 'crop_plan'">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <template x-for="cat in categories" :key="cat.value">
                                            <button type="button" @click="eventForm.category = cat.value"
                                                :class="eventForm.category === cat.value ? 'ring-2 ring-orange-500 bg-orange-50 border-orange-300' : 'bg-gray-50 hover:bg-gray-100 border-gray-200'"
                                                class="px-3 py-2 rounded-lg text-sm flex items-center justify-center gap-1 border transition-all">
                                                <span x-text="cat.icon"></span>
                                                <span x-text="cat.label"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Crop (optional) -->
                                <div x-show="modalType !== 'damage_report'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1" x-text="modalType === 'crop_plan' ? 'Crop to Plan *' : 'Related Crop (optional)'"></label>
                                    <select x-model="eventForm.crop" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-orange-500 focus:border-orange-500">
                                        <option value="">Select crop...</option>
                                        <option value="Cabbage">Cabbage</option>
                                        <option value="Broccoli">Broccoli</option>
                                        <option value="Lettuce">Lettuce</option>
                                        <option value="Cauliflower">Cauliflower</option>
                                        <option value="Chinese Cabbage">Chinese Cabbage</option>
                                        <option value="Carrots">Carrots</option>
                                        <option value="Garden Peas">Garden Peas</option>
                                        <option value="White Potato">White Potato</option>
                                        <option value="Snap Beans">Snap Beans</option>
                                        <option value="Sweet Pepper">Sweet Pepper</option>
                                    </select>
                                </div>

                                <div x-show="modalType === 'damage_report'" class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Reported Planted Crop *</label>
                                        <select x-model="eventForm.crop_plan_event_id" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-red-500 focus:border-red-500">
                                            <option value="">Select planted crop...</option>
                                            <template x-for="plan in cropPlans" :key="plan.id">
                                                <option :value="plan.id" :disabled="Number(plan.remaining_damage_sqm) <= 0" x-text="damageCropPlanOption(plan)"></option>
                                            </template>
                                        </select>
                                        <p x-show="cropPlans.length === 0" class="text-[11px] text-red-600 mt-1">Plan a crop first before reporting damage.</p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Damaged Area (sqm) *</label>
                                        <input type="number" min="0.01" :max="selectedDamageCropPlan ? selectedDamageCropPlan.remaining_damage_sqm : null" step="0.01" inputmode="decimal" x-model="eventForm.damage_area_sqm" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-red-500 focus:border-red-500" placeholder="e.g., 50">
                                        <p x-show="selectedDamageCropPlan" class="text-[11px] text-gray-500 mt-1">
                                            <span x-text="'Remaining reportable area: ' + formatSquareMeters(selectedDamageCropPlan.remaining_damage_sqm)"></span>
                                            <span x-text="' of ' + formatSquareMeters(selectedDamageCropPlan.planted_area_sqm)"></span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Desired Area (only for crop plans) -->
                                <div x-show="modalType === 'crop_plan'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Desired Area (sqm) *</label>
                                    <input type="number" min="0.01" step="0.01" inputmode="decimal" x-model="eventForm.desired_area_sqm" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-orange-500 focus:border-orange-500" placeholder="e.g., 250">
                                </div>

                                <!-- Water Source and Seed Type (only for crop plans) -->
                                <div x-show="modalType === 'crop_plan'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Water Source *</label>
                                        <select x-model="eventForm.water_source" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-orange-500 focus:border-orange-500">
                                            <option value="">Select source...</option>
                                            <option value="rainfed">Rainfed</option>
                                            <option value="irrigated">Irrigated</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Seed Type *</label>
                                        <select x-model="eventForm.planting_material" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-orange-500 focus:border-orange-500">
                                            <option value="">Select type...</option>
                                            <option value="seed">Seed</option>
                                            <option value="seedling">Seedling</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Planning Date (only for crop plans) -->
                                <div x-show="modalType === 'crop_plan' || modalType === 'damage_report'">
                                    <label class="block text-xs font-medium text-gray-700 mb-1" x-text="modalType === 'damage_report' ? 'Damage Date *' : 'Planning Date *'"></label>
                                    <input type="date" x-model="eventForm.planning_date" :max="modalType === 'damage_report' ? todayDate : null" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-orange-500 focus:border-orange-500">
                                    <p x-show="modalType === 'damage_report'" class="text-[11px] text-gray-500 mt-1">Future dates are disabled for damage reports.</p>
                                </div>

                                <div x-show="modalType === 'crop_plan' && (estimatedHarvestDate || productionPrediction.loading || productionPrediction.data || productionPrediction.error)" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <!-- Estimated Harvest Date (only for crop plans) -->
                                    <div x-show="estimatedHarvestDate" class="rounded-md border border-green-200 bg-green-50 px-3 py-2">
                                        <p class="text-[11px] font-semibold uppercase text-green-700">Harvest Date</p>
                                        <p class="text-sm font-semibold text-gray-900 leading-tight mt-0.5" x-text="estimatedHarvestDate ? estimatedHarvestDate.display : ''"></p>
                                        <p class="text-[11px] text-green-700 leading-tight mt-0.5" x-text="estimatedHarvestDate ? estimatedHarvestDate.days + ' days from planning' : ''"></p>
                                    </div>

                                    <!-- Production Prediction (only for crop plans) -->
                                    <div x-show="productionPrediction.loading || productionPrediction.data || productionPrediction.error" class="rounded-md border border-orange-200 bg-orange-50 px-3 py-2">
                                        <p class="text-[11px] font-semibold uppercase text-orange-700">Production</p>
                                        <div x-show="productionPrediction.loading" class="mt-1 text-xs text-orange-700">Calculating...</div>
                                        <div x-show="!productionPrediction.loading && productionPrediction.data" class="mt-0.5">
                                            <p class="text-sm font-semibold text-gray-900 leading-tight" x-text="productionPrediction.data ? formatMetricTons(productionPrediction.data.predicted_production_mt) : ''"></p>
                                            <p class="text-[11px] text-orange-700 leading-tight mt-0.5">
                                                <span x-text="productionPrediction.data ? formatSquareMeters(productionPrediction.data.area_sqm) : ''"></span>
                                                <span x-show="productionPrediction.data"> / </span>
                                                <span x-text="productionPrediction.data ? productionPrediction.data.area_hectares + ' ha' : ''"></span>
                                                <span x-show="productionPrediction.data && productionPrediction.data.prediction_confidence">, </span>
                                                <span x-show="productionPrediction.data && productionPrediction.data.prediction_confidence" x-text="formatPercent(productionPrediction.data.prediction_confidence)"></span>
                                            </p>
                                            <p x-show="productionPrediction.data && productionPrediction.data.production_per_ha_mt" class="text-[11px] text-orange-600 leading-tight mt-0.5" x-text="formatMetricTons(productionPrediction.data.production_per_ha_mt) + ' per ha'"></p>
                                        </div>
                                        <p x-show="!productionPrediction.loading && productionPrediction.error" class="mt-1 text-xs text-orange-700" x-text="productionPrediction.error"></p>
                                    </div>
                                </div>

                                <!-- Fertilization Stages (only for crop plans) -->
                                <div x-show="modalType === 'crop_plan' && fertilizationStages.length" class="rounded-md border border-blue-200 bg-blue-50 px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase text-blue-700">Fertilization Stages</p>
                                    <div class="mt-1 divide-y divide-blue-100 rounded-md bg-white border border-blue-100">
                                        <template x-for="stage in fertilizationStages" :key="stage.key">
                                            <div class="flex items-center justify-between gap-3 px-2.5 py-1.5">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-medium text-gray-900 leading-tight truncate" x-text="stage.label"></p>
                                                    <p class="text-[11px] text-blue-700 leading-tight" x-text="stage.timingText"></p>
                                                </div>
                                                <p class="text-[11px] font-semibold text-gray-700 whitespace-nowrap" x-text="stage.display"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Reminder Time (only for reminders) -->
                                <div x-show="modalType === 'reminder'">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reminder Time</label>
                                    <input type="time" x-model="eventForm.reminder_time" class="w-full border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500">
                                    <p class="text-xs text-gray-400 mt-1">Set a time to be reminded</p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Notes (optional)</label>
                                    <textarea x-model="eventForm.description" rows="2" class="w-full border-gray-300 rounded-md text-sm py-1.5 focus:ring-orange-500 focus:border-orange-500" placeholder="Add any additional details..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-2.5 sm:flex sm:flex-row-reverse sm:px-5 gap-2">
                            <button @click="saveEvent()" :disabled="!canSaveEvent || saving" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-sm font-medium text-white hover:bg-orange-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span x-show="!saving" x-text="modalSubmitText"></span>
                                <span x-show="saving">Saving...</span>
                            </button>
                            <button @click="closeModal()" class="mt-2 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Helper function to format date as YYYY-MM-DD in local timezone (avoids UTC conversion issues)
        function formatLocalDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function parseLocalDate(dateString) {
            return new Date(dateString + 'T00:00:00');
        }

        function farmerCalendar() {
            return {
                currentDate: new Date(),
                selectedDate: null,
                events: [],
                eventsByDay: {},
                upcomingReminders: [],
                cropPlans: [],
                showModal: false,
                modalType: 'note',
                saving: false,
                productionPrediction: {
                    loading: false,
                    data: null,
                    error: '',
                },
                productionPredictionTimer: null,
                productionPredictionRequestId: 0,
                categories: [
                    { value: 'pest', label: 'Pest', icon: '🐛' },
                    { value: 'harvest', label: 'Harvest', icon: '🌾' },
                    { value: 'planting', label: 'Planting', icon: '🌱' },
                    { value: 'fertilizer', label: 'Fertilizer', icon: '💧' },
                    { value: 'weather', label: 'Weather', icon: '🌤️' },
                    { value: 'other', label: 'Other', icon: '📝' },
                ],
                harvestBaseDays: {
                    'Cabbage': 58,
                    'Broccoli': 60,
                    'Lettuce': 55,
                    'Cauliflower': 68,
                    'Chinese Cabbage': 58,
                    'Carrots': 90,
                    'Garden Peas': 63,
                    'White Potato': 100,
                    'Snap Beans': 62,
                    'Sweet Pepper': 80,
                },
                transplantedCrops: [
                    'Cabbage',
                    'Broccoli',
                    'Lettuce',
                    'Cauliflower',
                    'Chinese Cabbage',
                    'Sweet Pepper',
                ],
                fertilizationStageRules: {
                    'Cabbage': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'First side-dress', offset: 21 },
                        { key: 'head_formation', label: 'Head formation feeding', offset: 42 },
                    ],
                    'Broccoli': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'First side-dress', offset: 21 },
                        { key: 'head_formation', label: 'Before head formation', offset: 42 },
                    ],
                    'Lettuce': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'Light side-dress', offset: 18 },
                    ],
                    'Cauliflower': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'First side-dress', offset: 21 },
                        { key: 'curd_formation', label: 'Curd formation feeding', offset: 45 },
                    ],
                    'Chinese Cabbage': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'First side-dress', offset: 14 },
                        { key: 'side_dress_2', label: 'Second side-dress', offset: 28 },
                    ],
                    'Carrots': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'Root development side-dress', offset: 28 },
                    ],
                    'Garden Peas': [
                        { key: 'basal', label: 'Basal compost/P-K', offset: 0 },
                        { key: 'flowering', label: 'Flowering or pod formation feed', offset: 35 },
                    ],
                    'White Potato': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'hilling', label: 'Hilling side-dress', offset: 25 },
                        { key: 'tuber_initiation', label: 'Tuber initiation feeding', offset: 45 },
                    ],
                    'Snap Beans': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'flowering', label: 'Flowering or early pod feed', offset: 35 },
                    ],
                    'Sweet Pepper': [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'First side-dress', offset: 21 },
                        { key: 'fruit_setting', label: 'Flowering and fruit setting feed', offset: 45 },
                    ],
                },
                eventForm: {
                    title: '',
                    description: '',
                    category: 'other',
                    crop: '',
                    desired_area_sqm: '',
                    damage_area_sqm: '',
                    crop_plan_event_id: '',
                    water_source: '',
                    planting_material: '',
                    planning_date: '',
                    reminder_time: '',
                },

                init() {
                    // Select today by default (using local timezone)
                    this.selectedDate = formatLocalDate(new Date());
                    this.loadEvents();
                    this.loadUpcomingReminders();
                    this.loadCropPlans();
                    ['crop', 'desired_area_sqm', 'water_source', 'planting_material', 'planning_date'].forEach((field) => {
                        this.$watch(`eventForm.${field}`, () => this.scheduleProductionPrediction());
                    });
                },

                get calendarDays() {
                    const year = this.currentDate.getFullYear();
                    const month = this.currentDate.getMonth();
                    const firstDay = new Date(year, month, 1);
                    const lastDay = new Date(year, month + 1, 0);
                    const startPadding = firstDay.getDay();
                    const days = [];

                    // Previous month padding
                    const prevMonth = new Date(year, month, 0);
                    for (let i = startPadding - 1; i >= 0; i--) {
                        const d = new Date(year, month - 1, prevMonth.getDate() - i);
                        days.push({
                            day: d.getDate(),
                            date: formatLocalDate(d),
                            isCurrentMonth: false,
                            isToday: false,
                            isSelected: false,
                        });
                    }

                    // Current month days
                    const today = formatLocalDate(new Date());
                    for (let i = 1; i <= lastDay.getDate(); i++) {
                        const d = new Date(year, month, i);
                        const dateStr = formatLocalDate(d);
                        days.push({
                            day: i,
                            date: dateStr,
                            isCurrentMonth: true,
                            isToday: dateStr === today,
                            isSelected: this.selectedDate === dateStr,
                        });
                    }

                    // Next month padding
                    const remaining = 42 - days.length;
                    for (let i = 1; i <= remaining; i++) {
                        const d = new Date(year, month + 1, i);
                        days.push({
                            day: i,
                            date: formatLocalDate(d),
                            isCurrentMonth: false,
                            isToday: false,
                            isSelected: false,
                        });
                    }

                    return days;
                },

                get monthYearDisplay() {
                    return this.currentDate.toLocaleString('en-US', { month: 'long', year: 'numeric' });
                },

                get selectedDateDisplay() {
                    if (!this.selectedDate) return '';
                    const date = new Date(this.selectedDate + 'T00:00:00');
                    return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
                },

                get selectedDayEvents() {
                    if (!this.selectedDate) return [];
                    return this.eventsByDay[this.selectedDate] || [];
                },

                get selectedCropPlanEvents() {
                    return this.selectedDayEvents.filter((event) => event.category === 'crop_plan');
                },

                formatSquareMeters(value) {
                    const amount = Number(value);
                    if (!Number.isFinite(amount)) return '';

                    return `${amount.toLocaleString(undefined, { maximumFractionDigits: 2 })} sqm`;
                },

                damageCropPlanOption(plan) {
                    const remaining = this.formatSquareMeters(plan.remaining_damage_sqm);
                    const planted = this.formatSquareMeters(plan.planted_area_sqm);
                    const date = this.formatDisplayDate(plan.planning_date);

                    return `${plan.crop} - ${planted} planted, ${remaining} remaining (${date})`;
                },

                formatMetricTons(value) {
                    const amount = Number(value);
                    if (!Number.isFinite(amount)) return '';

                    return `${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} mt`;
                },

                formatPercent(value) {
                    const amount = Number(value);
                    if (!Number.isFinite(amount)) return '';

                    return `${(amount * 100).toLocaleString(undefined, { maximumFractionDigits: 1 })}%`;
                },

                formatCropPlanOption(value) {
                    const labels = {
                        rainfed: 'Rainfed',
                        irrigated: 'Irrigated',
                        seed: 'Seed',
                        seedling: 'Seedling',
                    };

                    return labels[value] || value;
                },

                formatDisplayDate(dateString) {
                    if (!dateString) return '';

                    return parseLocalDate(dateString).toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                    });
                },

                formatCropPlanStage(value) {
                    if (!value) return '';

                    return value
                        .replace(/^fertilizer_/, '')
                        .replace(/_/g, ' ')
                        .replace(/\b\w/g, (letter) => letter.toUpperCase());
                },

                getCropPlanDamageSummary(plan) {
                    return this.cropPlans.find((cropPlan) => String(cropPlan.id) === String(plan.id)) || null;
                },

                formatCropPlanDamageStatus(plan) {
                    const summary = this.getCropPlanDamageSummary(plan);
                    if (!summary) return 'No damage reported';

                    return `${this.formatSquareMeters(summary.reported_damage_sqm)} reported`;
                },

                formatCropPlanRemainingStatus(plan) {
                    const summary = this.getCropPlanDamageSummary(plan);
                    if (!summary) return 'Full planted area remains available';

                    return `${this.formatSquareMeters(summary.remaining_damage_sqm)} remaining of ${this.formatSquareMeters(summary.planted_area_sqm)}`;
                },

                getCropPlanSchedule(plan) {
                    const schedule = [];

                    if (plan.estimated_harvest_date) {
                        schedule.push({
                            label: 'Estimated harvest',
                            date: plan.estimated_harvest_date,
                        });
                    }

                    if (!plan.crop || !plan.water_source || !plan.planting_material || !plan.date) {
                        return schedule;
                    }

                    const rules = this.fertilizationStageRules[plan.crop] || [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'First side-dress', offset: 21 },
                    ];
                    const fieldStartDelay = this.calculateFieldStartDelayDays(plan.crop, plan.planting_material);

                    rules.forEach((stage) => {
                        const rainfedDelay = plan.water_source === 'rainfed' && stage.offset > 0 ? 3 : 0;
                        const stageDate = parseLocalDate(plan.date);
                        stageDate.setDate(stageDate.getDate() + fieldStartDelay + stage.offset + rainfedDelay);
                        schedule.push({
                            label: stage.label,
                            date: formatLocalDate(stageDate),
                        });
                    });

                    return schedule.sort((a, b) => parseLocalDate(a.date) - parseLocalDate(b.date));
                },

                get todayDate() {
                    return formatLocalDate(new Date());
                },

                get modalTitle() {
                    if (this.modalType === 'crop_plan') return 'Plan a Crop';
                    if (this.modalType === 'damage_report') return 'Damage Report';
                    if (this.modalType === 'reminder') return '🔔 Set Reminder';
                    return '📝 Add Note';
                },

                get modalSubmitText() {
                    if (this.modalType === 'crop_plan') return 'Save Crop Plan';
                    if (this.modalType === 'damage_report') return 'Save Damage Report';
                    if (this.modalType === 'reminder') return 'Set Reminder';
                    return 'Save Note';
                },

                get canSaveEvent() {
                    if (this.modalType === 'crop_plan') {
                        return Boolean(this.eventForm.crop)
                            && Number(this.eventForm.desired_area_sqm) > 0
                            && Boolean(this.eventForm.water_source)
                            && Boolean(this.eventForm.planting_material)
                            && Boolean(this.eventForm.planning_date);
                    }
                    if (this.modalType === 'damage_report') {
                        return Boolean(this.eventForm.crop_plan_event_id)
                            && Number(this.eventForm.damage_area_sqm) > 0
                            && Boolean(this.eventForm.planning_date)
                            && this.eventForm.planning_date <= this.todayDate
                            && this.selectedDamageCropPlan
                            && Number(this.eventForm.damage_area_sqm) <= Number(this.selectedDamageCropPlan.remaining_damage_sqm);
                    }

                    return Boolean(this.eventForm.title);
                },

                get defaultPlanningDate() {
                    if (this.selectedDate) {
                        return this.selectedDate;
                    }

                    return this.todayDate;
                },

                get defaultDamageDate() {
                    if (this.selectedDate && this.selectedDate <= this.todayDate) {
                        return this.selectedDate;
                    }

                    return this.todayDate;
                },

                get selectedDamageCropPlan() {
                    return this.cropPlans.find((plan) => String(plan.id) === String(this.eventForm.crop_plan_event_id)) || null;
                },

                get estimatedHarvestDate() {
                    if (this.modalType !== 'crop_plan'
                        || !this.eventForm.crop
                        || !this.eventForm.water_source
                        || !this.eventForm.planting_material
                        || !this.eventForm.planning_date) {
                        return null;
                    }

                    const days = this.calculateHarvestDays(
                        this.eventForm.crop,
                        this.eventForm.water_source,
                        this.eventForm.planting_material
                    );
                    const harvestDate = parseLocalDate(this.eventForm.planning_date);
                    harvestDate.setDate(harvestDate.getDate() + days);
                    const date = formatLocalDate(harvestDate);

                    return {
                        days,
                        date,
                        display: this.formatDisplayDate(date),
                    };
                },

                calculateHarvestDays(crop, waterSource, plantingMaterial) {
                    let days = this.harvestBaseDays[crop] || 75;

                    if (plantingMaterial === 'seed' && this.transplantedCrops.includes(crop)) {
                        days += 30;
                    }

                    if (waterSource === 'rainfed') {
                        days += 7;
                    }

                    return days;
                },

                get fertilizationStages() {
                    if (this.modalType !== 'crop_plan'
                        || !this.eventForm.crop
                        || !this.eventForm.water_source
                        || !this.eventForm.planting_material
                        || !this.eventForm.planning_date) {
                        return [];
                    }

                    const rules = this.fertilizationStageRules[this.eventForm.crop] || [
                        { key: 'basal', label: 'Basal fertilizer', offset: 0 },
                        { key: 'side_dress_1', label: 'First side-dress', offset: 21 },
                    ];
                    const fieldStartDelay = this.calculateFieldStartDelayDays(
                        this.eventForm.crop,
                        this.eventForm.planting_material
                    );

                    return rules.map((stage) => {
                        const rainfedDelay = this.eventForm.water_source === 'rainfed' && stage.offset > 0 ? 3 : 0;
                        const days = fieldStartDelay + stage.offset + rainfedDelay;
                        const stageDate = parseLocalDate(this.eventForm.planning_date);
                        stageDate.setDate(stageDate.getDate() + days);
                        const date = formatLocalDate(stageDate);

                        return {
                            key: stage.key,
                            label: stage.label,
                            days,
                            date,
                            display: this.formatDisplayDate(date),
                            timingText: this.formatFertilizationTiming(stage, days),
                        };
                    });
                },

                formatFertilizationTiming(stage, days) {
                    if (stage.key === 'basal') {
                        return days > 0
                            ? `${days} days from planning, before/during field planting`
                            : 'Apply before or during planting';
                    }

                    return `${days} days from planning`;
                },

                calculateFieldStartDelayDays(crop, plantingMaterial) {
                    if (plantingMaterial === 'seed' && this.transplantedCrops.includes(crop)) {
                        return 30;
                    }

                    return 0;
                },

                get canRequestProductionPrediction() {
                    return this.modalType === 'crop_plan'
                        && Boolean(this.eventForm.crop)
                        && Number(this.eventForm.desired_area_sqm) > 0
                        && Boolean(this.eventForm.water_source)
                        && Boolean(this.eventForm.planting_material)
                        && Boolean(this.eventForm.planning_date);
                },

                resetProductionPrediction() {
                    this.productionPrediction = {
                        loading: false,
                        data: null,
                        error: '',
                    };
                    if (this.productionPredictionTimer) {
                        clearTimeout(this.productionPredictionTimer);
                        this.productionPredictionTimer = null;
                    }
                },

                scheduleProductionPrediction() {
                    if (!this.canRequestProductionPrediction) {
                        this.resetProductionPrediction();
                        return;
                    }

                    if (this.productionPredictionTimer) {
                        clearTimeout(this.productionPredictionTimer);
                    }

                    this.productionPrediction.loading = true;
                    this.productionPrediction.error = '';
                    this.productionPredictionTimer = setTimeout(() => this.loadProductionPrediction(), 500);
                },

                async loadProductionPrediction() {
                    if (!this.canRequestProductionPrediction) {
                        this.resetProductionPrediction();
                        return;
                    }

                    const requestId = ++this.productionPredictionRequestId;

                    try {
                        const response = await fetch('{{ route('farmer.calendar.production-prediction') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                crop: this.eventForm.crop,
                                desired_area_sqm: this.eventForm.desired_area_sqm,
                                water_source: this.eventForm.water_source,
                                planting_material: this.eventForm.planting_material,
                                planning_date: this.eventForm.planning_date,
                            })
                        });

                        const data = await response.json();
                        if (requestId !== this.productionPredictionRequestId) return;

                        if (response.ok && data.success) {
                            this.productionPrediction = {
                                loading: false,
                                data: data.prediction,
                                error: '',
                            };
                            return;
                        }

                        this.productionPrediction = {
                            loading: false,
                            data: null,
                            error: data.message || data.error || 'Production prediction is unavailable.',
                        };
                    } catch (error) {
                        if (requestId !== this.productionPredictionRequestId) return;

                        this.productionPrediction = {
                            loading: false,
                            data: null,
                            error: 'Production prediction is unavailable.',
                        };
                    }
                },

                goToToday() {
                    this.currentDate = new Date();
                    this.selectedDate = formatLocalDate(new Date());
                    this.loadEvents();
                },

                prevMonth() {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
                    this.selectedDate = null;
                    this.loadEvents();
                },

                nextMonth() {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
                    this.selectedDate = null;
                    this.loadEvents();
                },

                selectDay(day) {
                    this.selectedDate = day.date;
                },

                async loadEvents() {
                    try {
                        const year = this.currentDate.getFullYear();
                        const month = this.currentDate.getMonth() + 1;
                        const response = await fetch(`{{ route('farmer.calendar.events') }}?year=${year}&month=${month}`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.events = data.events || [];
                            this.eventsByDay = data.events_by_day || {};
                        }
                    } catch (error) {
                        console.error('Failed to load calendar events:', error);
                    }
                },

                async loadUpcomingReminders() {
                    try {
                        const response = await fetch('{{ route('farmer.reminders.upcoming') }}', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.upcomingReminders = data.reminders || [];
                        }
                    } catch (error) {
                        console.error('Failed to load reminders:', error);
                    }
                },

                async loadCropPlans() {
                    try {
                        const response = await fetch('{{ route('farmer.calendar.crop-plans') }}', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.cropPlans = data.crop_plans || [];
                        }
                    } catch (error) {
                        console.error('Failed to load crop plans:', error);
                    }
                },

                openAddModal(type) {
                    this.modalType = type;
                    this.resetProductionPrediction();
                    if (type === 'damage_report') {
                        this.loadCropPlans();
                    }
                    this.eventForm = {
                        title: '',
                        description: '',
                        category: type === 'crop_plan' ? 'crop_plan' : (type === 'damage_report' ? 'damage_report' : 'other'),
                        crop: '',
                        desired_area_sqm: '',
                        damage_area_sqm: '',
                        crop_plan_event_id: '',
                        water_source: '',
                        planting_material: '',
                        planning_date: type === 'crop_plan' ? this.defaultPlanningDate : (type === 'damage_report' ? this.defaultDamageDate : ''),
                        reminder_time: type === 'reminder' ? '08:00' : '',
                    };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                async saveEvent() {
                    if (!this.canSaveEvent || !this.selectedDate) return;
                    this.saving = true;

                    try {
                        const isCropPlan = this.modalType === 'crop_plan';
                        const isDamageReport = this.modalType === 'damage_report';
                        const eventDate = (isCropPlan || isDamageReport) ? this.eventForm.planning_date : this.selectedDate;
                        const eventTitle = isCropPlan
                            ? (this.eventForm.title || `Plan ${this.eventForm.crop}`)
                            : (isDamageReport
                                ? (this.eventForm.title || `Damage report - ${this.selectedDamageCropPlan?.crop || 'Crop'}`)
                                : this.eventForm.title);

                        const selectedPlan = this.selectedDamageCropPlan;
                        const eventDescription = isDamageReport && !this.eventForm.description
                            ? `Damage reported for ${selectedPlan?.crop || 'selected crop plan'}. Damaged area: ${this.formatSquareMeters(this.eventForm.damage_area_sqm)}.`
                            : this.eventForm.description;

                        const response = await fetch('{{ route('farmer.calendar.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                event_date: eventDate,
                                event_type: this.modalType === 'reminder' ? 'reminder' : 'note',
                                title: eventTitle,
                                description: isDamageReport ? (this.eventForm.description || eventDescription) : this.eventForm.description,
                                category: isCropPlan ? 'crop_plan' : (isDamageReport ? 'damage_report' : this.eventForm.category),
                                crop: this.eventForm.crop,
                                desired_area_sqm: isCropPlan && this.eventForm.desired_area_sqm ? this.eventForm.desired_area_sqm : null,
                                damage_area_sqm: isDamageReport && this.eventForm.damage_area_sqm ? this.eventForm.damage_area_sqm : null,
                                crop_plan_event_id: isDamageReport ? this.eventForm.crop_plan_event_id : null,
                                water_source: isCropPlan ? this.eventForm.water_source : null,
                                planting_material: isCropPlan ? this.eventForm.planting_material : null,
                                reminder_time: this.modalType === 'reminder' ? (this.eventForm.reminder_time || null) : null,
                            })
                        });

                        if (response.ok) {
                            this.closeModal();
                            if (isCropPlan || isDamageReport) {
                                this.selectedDate = eventDate;
                                this.currentDate = new Date(eventDate + 'T00:00:00');
                            }
                            this.loadEvents();
                            this.loadCropPlans();
                            if (this.modalType === 'reminder') {
                                this.loadUpcomingReminders();
                            }
                        } else {
                            const data = await response.json().catch(() => ({}));
                            alert(data.message || 'Failed to save event. Please try again.');
                        }
                    } catch (error) {
                        console.error('Failed to save event:', error);
                    } finally {
                        this.saving = false;
                    }
                },

                async toggleEventComplete(calEvent) {
                    try {
                        const response = await fetch(`{{ url('farmer/calendar-events') }}/${calEvent.id}/toggle`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        if (response.ok) {
                            this.loadEvents();
                        } else {
                            console.error('Toggle failed:', await response.text());
                        }
                    } catch (error) {
                        console.error('Failed to toggle event:', error);
                    }
                },

                async deleteEvent(calEvent) {
                    if (!confirm('Delete this event?')) return;

                    try {
                        const response = await fetch(`{{ url('farmer/calendar-events') }}/${calEvent.id}/delete`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success) {
                                this.loadEvents();
                                this.loadCropPlans();
                                this.loadUpcomingReminders();
                            }
                        } else {
                            alert('Failed to delete event. Please try again.');
                        }
                    } catch (error) {
                        console.error('Failed to delete event:', error);
                        alert('Failed to delete event. Please try again.');
                    }
                }
            }
        }
    </script>
</x-app-layout>

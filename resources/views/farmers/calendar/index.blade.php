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
                                    <template x-for="event in (eventsByDay[day.day] || []).slice(0, 2)" :key="event.id">
                                        <div 
                                            class="text-[10px] lg:text-xs px-1 py-0.5 rounded truncate"
                                            :class="{
                                                'bg-red-100 text-red-700': event.category === 'pest',
                                                'bg-green-100 text-green-700': event.category === 'harvest',
                                                'bg-emerald-100 text-emerald-700': event.category === 'planting',
                                                'bg-blue-100 text-blue-700': event.category === 'fertilizer',
                                                'bg-yellow-100 text-yellow-700': event.category === 'weather',
                                                'bg-gray-100 text-gray-700': event.category === 'other'
                                            }"
                                            x-text="event.title">
                                        </div>
                                    </template>
                                    <div x-show="(eventsByDay[day.day] || []).length > 2" class="text-[10px] text-gray-400">
                                        +<span x-text="(eventsByDay[day.day] || []).length - 2"></span> more
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap items-center gap-3 mt-4 pt-4 border-t text-xs text-gray-500">
                        <span class="font-medium">Categories:</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-100 border border-red-200"></span> Pest</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-100 border border-green-200"></span> Harvest</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-200"></span> Planting</span>
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
                            <div class="flex gap-2 mb-4">
                                <button @click="openAddModal('note')" class="flex-1 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors">
                                    <span>📝</span> Add Note
                                </button>
                                <button @click="openAddModal('reminder')" class="flex-1 text-sm bg-orange-100 hover:bg-orange-200 text-orange-700 px-3 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors">
                                    <span>🔔</span> Reminder
                                </button>
                            </div>

                            <!-- Events List -->
                            <div x-show="selectedDayEvents.length > 0" class="space-y-2 max-h-[400px] overflow-y-auto">
                                <template x-for="calEvent in selectedDayEvents" :key="calEvent.id">
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg group hover:bg-gray-100 transition-colors">
                                        <span class="text-lg" x-text="calEvent.category_icon"></span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-medium text-gray-900 text-sm" :class="calEvent.is_completed ? 'line-through text-gray-400' : ''" x-text="calEvent.title"></span>
                                                <span x-show="calEvent.type === 'reminder'" class="text-xs bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded">🔔</span>
                                            </div>
                                            <p x-show="calEvent.description" class="text-xs text-gray-500 mt-1" x-text="calEvent.description"></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span x-show="calEvent.crop" class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded" x-text="calEvent.crop"></span>
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
                                No events for this day.<br>Add a note or set a reminder!
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Event Modal -->
            <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

                    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4" x-text="modalType === 'reminder' ? '🔔 Set Reminder' : '📝 Add Note'"></h3>
                            
                            <div class="space-y-4">
                                <!-- Title -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                                    <input type="text" x-model="eventForm.title" class="w-full border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500" placeholder="e.g., Cabbage harvest day">
                                </div>

                                <!-- Category -->
                                <div>
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
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Related Crop (optional)</label>
                                    <select x-model="eventForm.crop" class="w-full border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500">
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

                                <!-- Reminder Time (only for reminders) -->
                                <div x-show="modalType === 'reminder'">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reminder Time</label>
                                    <input type="time" x-model="eventForm.reminder_time" class="w-full border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500">
                                    <p class="text-xs text-gray-400 mt-1">Set a time to be reminded</p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                                    <textarea x-model="eventForm.description" rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:ring-orange-500 focus:border-orange-500" placeholder="Add any additional details..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                            <button @click="saveEvent()" :disabled="!eventForm.title || saving" class="w-full sm:w-auto inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed sm:text-sm transition-colors">
                                <span x-show="!saving" x-text="modalType === 'reminder' ? 'Set Reminder' : 'Save Note'"></span>
                                <span x-show="saving">Saving...</span>
                            </button>
                            <button @click="closeModal()" class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm transition-colors">
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

        function farmerCalendar() {
            return {
                currentDate: new Date(),
                selectedDate: null,
                events: [],
                eventsByDay: {},
                upcomingReminders: [],
                showModal: false,
                modalType: 'note',
                saving: false,
                categories: [
                    { value: 'pest', label: 'Pest', icon: '🐛' },
                    { value: 'harvest', label: 'Harvest', icon: '🌾' },
                    { value: 'planting', label: 'Planting', icon: '🌱' },
                    { value: 'fertilizer', label: 'Fertilizer', icon: '💧' },
                    { value: 'weather', label: 'Weather', icon: '🌤️' },
                    { value: 'other', label: 'Other', icon: '📝' },
                ],
                eventForm: {
                    title: '',
                    description: '',
                    category: 'other',
                    crop: '',
                    reminder_time: '',
                },

                init() {
                    // Select today by default (using local timezone)
                    this.selectedDate = formatLocalDate(new Date());
                    this.loadEvents();
                    this.loadUpcomingReminders();
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
                    const day = new Date(this.selectedDate + 'T00:00:00').getDate();
                    return this.eventsByDay[day] || [];
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

                openAddModal(type) {
                    this.modalType = type;
                    this.eventForm = {
                        title: '',
                        description: '',
                        category: 'other',
                        crop: '',
                        reminder_time: type === 'reminder' ? '08:00' : '',
                    };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                async saveEvent() {
                    if (!this.eventForm.title || !this.selectedDate) return;
                    this.saving = true;

                    try {
                        const response = await fetch('{{ route('farmer.calendar.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                event_date: this.selectedDate,
                                event_type: this.modalType,
                                title: this.eventForm.title,
                                description: this.eventForm.description,
                                category: this.eventForm.category,
                                crop: this.eventForm.crop,
                                reminder_time: this.eventForm.reminder_time || null,
                            })
                        });

                        if (response.ok) {
                            this.closeModal();
                            this.loadEvents();
                            if (this.modalType === 'reminder') {
                                this.loadUpcomingReminders();
                            }
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

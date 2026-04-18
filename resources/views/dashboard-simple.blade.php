<x-app-layout>
    <style>
        .stat-card:hover {
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .recommendation-card {
            transition: all 0.2s ease;
        }
        .recommendation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .crop-tag {
            transition: all 0.2s ease;
        }
        .crop-tag:hover {
            transform: scale(1.05);
        }
        .quick-action-btn {
            transition: all 0.2s ease;
        }
        .quick-action-btn:hover {
            transform: translateY(-1px);
        }
        .insight-card {
            transition: all 0.2s ease;
        }
        .insight-card:hover {
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }
        .quiet-stat-card {
            transition: all 0.2s ease;
        }
        .quiet-stat-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        }
        .insight-heading > .text-xl {
            display: none;
        }
        .insight-heading::before {
            content: '#';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.75rem;
            background: #f1f5f9;
            color: #355872;
            font-weight: 700;
        }
        .farmer-action-grid > a:nth-child(1) .text-3xl,
        .farmer-action-grid > a:nth-child(2) .text-2xl,
        .farmer-action-grid > a:nth-child(3) .text-2xl,
        .farmer-action-grid > a:nth-child(4) .text-2xl {
            color: transparent;
            position: relative;
        }
        .farmer-action-grid > a:nth-child(1) .text-3xl::before,
        .farmer-action-grid > a:nth-child(2) .text-2xl::before,
        .farmer-action-grid > a:nth-child(3) .text-2xl::before,
        .farmer-action-grid > a:nth-child(4) .text-2xl::before {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: currentColor;
            font-weight: 700;
        }
        .farmer-action-grid > a:nth-child(1) .text-3xl::before {
            content: 'P';
            color: #ffffff;
        }
        .farmer-action-grid > a:nth-child(2) .text-2xl::before {
            content: 'M';
            color: #2563eb;
        }
        .farmer-action-grid > a:nth-child(3) .text-2xl::before {
            content: 'H';
            color: #15803d;
        }
        .farmer-action-grid > a:nth-child(4) .text-2xl::before {
            content: 'F';
            color: #b45309;
        }

        /* Translation is disabled globally. Keep controls hidden and default language in English. */
        .lang-toggle,
        .lang-popup-overlay {
            display: none !important;
        }
        
        /* Language Toggle Styles */
        .lang-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 50;
            transition: all 0.3s ease;
        }
        .lang-toggle:hover .lang-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .lang-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .lang-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        .lang-menu {
            position: absolute;
            bottom: 56px;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.2s ease;
        }
        .lang-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .lang-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            white-space: nowrap;
            transition: background 0.2s;
        }
        .lang-option:hover {
            background: #f3f4f6;
        }
        .lang-option.active {
            background: #dcfce7;
        }
        
        /* Popup Styles */
        .lang-popup-overlay {
            animation: fadeIn 0.3s ease;
        }
        .lang-popup-content {
            animation: slideUp 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- Language System Wrapper -->
    <div x-data="languageSystem()" x-init="init()">
        
        <!-- Language Popup (shows on every login/session) -->
        <div x-show="showPopup" 
             x-cloak
             class="lang-popup-overlay fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4">
            <div class="lang-popup-content bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center">
                <div class="text-4xl mb-3">🌐</div>
                <h2 class="text-xl font-bold text-gray-900 mb-2" x-text="t('language_popup_title')"></h2>
                <p class="text-sm text-gray-600 mb-5" x-text="t('language_popup_desc')"></p>
                
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <!-- English Option -->
                    <button @click="lang = 'en'" 
                        :class="lang === 'en' ? 'ring-2 ring-primary-dark bg-primary-50 border-primary-300' : 'border-gray-200 hover:bg-gray-50'"
                        class="p-4 rounded-xl border-2 transition-all">
                        <div class="text-3xl mb-1">🇺🇸</div>
                        <div class="font-semibold text-gray-900 text-sm">English</div>
                    </button>
                    
                    <!-- Tagalog Option -->
                    <button @click="lang = 'tl'" 
                        :class="lang === 'tl' ? 'ring-2 ring-primary-dark bg-primary-50 border-primary-300' : 'border-gray-200 hover:bg-gray-50'"
                        class="p-4 rounded-xl border-2 transition-all">
                        <div class="text-3xl mb-1">🇵🇭</div>
                        <div class="font-semibold text-gray-900 text-sm">Tagalog</div>
                    </button>
                </div>
                
                <button @click="confirmLanguage()" 
                    class="w-full bg-primary-dark hover:bg-primary-900 text-white font-semibold py-3 px-6 rounded-xl transition-colors flex items-center justify-center gap-2">
                    <span x-text="t('continue')"></span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Floating Language Toggle -->
        <div class="lang-toggle">
            <!-- Language Menu (appears on click) -->
            <div class="lang-menu" :class="showMenu ? 'show' : ''" @click.away="showMenu = false">
                <div class="lang-option" :class="lang === 'en' ? 'active' : ''" @click="setLanguage('en')">
                    <span class="text-xl">🇺🇸</span>
                    <span class="text-sm font-medium text-gray-700">English</span>
                    <span x-show="lang === 'en'" class="text-primary-dark ml-auto">✓</span>
                </div>
                <div class="lang-option" :class="lang === 'tl' ? 'active' : ''" @click="setLanguage('tl')">
                    <span class="text-xl">🇵🇭</span>
                    <span class="text-sm font-medium text-gray-700">Tagalog</span>
                    <span x-show="lang === 'tl'" class="text-primary-dark ml-auto">✓</span>
                </div>
            </div>
            
            <!-- Toggle Button -->
            <button class="lang-btn" @click="showMenu = !showMenu" :title="t('change_language')">
                <span class="text-2xl" x-text="lang === 'en' ? '🇺🇸' : '🇵🇭'"></span>
            </button>
        </div>

    <div class="py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- ============================================ -->
            <!-- SIMPLE GREETING HEADER -->
            <!-- ============================================ -->
            <div class="rounded-2xl shadow-sm p-4 lg:p-6 mb-4 lg:mb-6 text-white bg-gradient-to-br from-primary-dark via-primary to-primary-900">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-white/80 text-sm font-medium tracking-wide uppercase" x-text="getGreeting()"></p>
                        <h1 class="text-2xl lg:text-3xl font-bold mb-1">
                            {{ Auth::user()->name }}! 👋
                        </h1>
                        <p class="text-primary-100 text-sm" x-text="t('dashboard_subtitle')"></p>
                    </div>
                    <div class="text-left sm:text-right bg-white/10 rounded-lg px-4 py-2">
                        <p class="text-primary-100 text-xs">{{ now()->format('l') }}</p>
                        <p class="text-lg font-semibold">{{ now()->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- MY FARM (Read-only, set from onboarding) -->
            <!-- ============================================ -->
            <div x-data="farmPreferences()" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-2xl flex-shrink-0">🏡</span>
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold text-gray-900" x-text="t('my_farm')"></h2>
                            <p class="text-xs text-gray-500" x-text="t('my_farm_desc')"></p>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex-shrink-0 text-xs text-primary-dark hover:text-primary-900 font-medium underline underline-offset-2 mt-1">Edit</a>
                </div>

                <!-- Municipality Display (from onboarding) -->
                <div class="mt-4">
                    <p class="block text-sm font-medium text-gray-700 mb-2">📍 <span x-text="t('where_is_farm')"></span></p>
                    @if($preferredMunicipality)
                        <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-green-50 border border-green-200">
                            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-sm font-semibold text-green-800">{{ ucwords(strtolower($preferredMunicipality)) }}</span>
                        </div>
                    @else
                        <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-sm text-gray-500 italic">
                            No location set — <a href="{{ route('profile.edit') }}" class="text-primary-dark underline">update your profile</a>.
                        </div>
                    @endif
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TOP 5 CROPS INSIGHT -->
            <!-- ============================================ -->
            <div x-data="topCropsInsight()" class="insight-card bg-white rounded-2xl shadow-sm border border-gray-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                    <div class="space-y-3">
                        <div class="insight-heading flex items-center gap-2">
                            <span class="text-xl">📊</span>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900" x-text="t('top_5_crops')"></h2>
                                <p class="text-sm text-gray-600">This ranking shows the broader full-year crop outlook in your area.</p>
                            </div>
                        </div>
                        <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs text-gray-600">
                            <span class="font-medium uppercase tracking-wide text-gray-500">Using</span>
                            <span class="font-semibold text-gray-900" x-text="municipalityLabel || 'your saved farm location'"></span>
                        </div>
                    </div>

                    <div x-show="municipality && insightText" class="w-full lg:max-w-xl">
                        <div class="flex items-center relative w-full lg:max-w-xl">
                            <div class="shrink-0 relative z-20 w-[110px] sm:w-[140px]">
                                <div class="overflow-hidden">
                                    <div x-ref="insightAvatar" class="w-[110px] h-[110px] sm:w-[140px] sm:h-[140px]" aria-hidden="true"></div>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1 relative z-10 ml-5 sm:ml-8">
                                {{-- Thought Bubble Tails --}}
                                <div class="absolute top-[60%] -left-4 sm:-left-6 w-2.5 h-2.5 sm:w-3.5 sm:h-3.5 rounded-full bg-gray-800 border border-white/10 z-0"></div>
                                <div class="absolute top-[35%] -left-2 sm:-left-3 w-4 h-4 sm:w-6 sm:h-6 rounded-full bg-gray-800 border border-white/10 z-0"></div>
                                
                                {{-- Main Cloud Box --}}
                                <div class="relative rounded-[2rem] bg-gray-800 p-4 sm:px-6 sm:py-5 shadow-xl border border-white/10 z-10">
                                    <p class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-widest text-[#a1a1aa] mb-1">Quick insight</p>
                                    <p class="text-xs sm:text-sm leading-relaxed text-gray-200" x-text="insightDisplayText" aria-live="polite"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="!municipality" class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center">
                    <p class="text-sm font-medium text-gray-700">Set your farm location above to unlock the crop insight ranking.</p>
                    <p class="mt-1 text-xs text-gray-500">This keeps recommendations and the ranking focused on the same municipality.</p>
                </div>

                <div x-show="loading" class="text-center py-8">
                    <svg class="inline-block animate-spin h-8 w-8 text-primary-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-600 mt-2" x-text="t('loading')"></p>
                </div>

                <div x-show="error && municipality" class="rounded-2xl border border-red-200 bg-red-50 p-6 text-center text-red-600">
                    <p class="text-sm" x-text="t('load_error')"></p>
                </div>

                <div x-show="!loading && !error && municipality" class="space-y-3">
                    <template x-for="row in rankedCropRows" :key="row.rank + '-' + row.crop">
                        <div
                            class="rounded-2xl border p-4 transition-all duration-200"
                            :class="row.rank === 1
                                ? 'border-amber-300 bg-gradient-to-r from-amber-50 via-white to-emerald-50 shadow-md shadow-amber-100/70'
                                : 'border-gray-200 bg-white shadow-sm'"
                        >
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-2xl text-center"
                                        :class="row.rank === 1 ? 'bg-amber-500 text-white shadow-lg shadow-amber-200/80' : 'bg-slate-100 text-slate-700'"
                                    >
                                        <span class="text-[10px] font-semibold uppercase tracking-[0.2em]">Rank</span>
                                        <span class="text-base font-bold leading-none" x-text="row.rank"></span>
                                    </div>

                                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-gray-200 bg-slate-100">
                                        <template x-if="row.image && !isCropImageMissing(row.crop)">
                                            <img
                                                :src="row.image"
                                                :alt="row.crop"
                                                class="h-full w-full object-cover"
                                                @error="markCropImageMissing(row.crop)"
                                            >
                                        </template>
                                        <template x-if="!row.image || isCropImageMissing(row.crop)">
                                            <div
                                                class="flex h-full w-full items-center justify-center bg-slate-100 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400"
                                            >
                                                No Image
                                            </div>
                                        </template>
                                    </div>

                                    <div class="min-w-0">
                                        <p
                                            class="text-[11px] font-semibold uppercase tracking-[0.2em]"
                                            :class="row.rank === 1 ? 'text-amber-600' : 'text-slate-500'"
                                            x-text="row.rank === 1 ? 'Top Performer' : `Rank ${row.rank}`"
                                        ></p>
                                        <h3 class="truncate text-base font-semibold text-gray-900 sm:text-lg" x-text="row.crop"></h3>
                                        <p class="text-xs text-gray-500">
                                            Ranked by forecast, using historical average when this year's forecast is zero.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:min-w-[280px] sm:grid-cols-2">
                                    <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Historical Average</p>
                                        <p class="mt-1 text-lg font-semibold text-emerald-900" x-text="formatCropValue(row.historical)"></p>
                                    </div>
                                    <div class="rounded-xl border border-sky-100 bg-sky-50 px-4 py-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-700">This Year Forecast</p>
                                        <p class="mt-1 text-lg font-semibold text-sky-900" x-text="formatCropValue(row.predicted)"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TODAY'S RECOMMENDATION (The Main Focus) -->
            <!-- ============================================ -->
            <div x-data="cropRecommendations()" class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg shadow-sm border border-amber-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-2xl">💡</span>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-900" x-text="t('recommendations')"></h2>
                        <p class="text-xs text-gray-600" x-text="t('recommendations_desc', { month: '{{ now()->format('F') }}' })"></p>
                    </div>
                </div>

                <div x-show="selectedMunicipality" class="mb-4 inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1.5 text-xs text-gray-600 shadow-sm">
                    <span class="font-medium uppercase tracking-wide text-gray-500">Area</span>
                    <span class="font-semibold text-gray-900" x-text="formatMunicipality(selectedMunicipality)"></span>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-8">
                    <svg class="inline-block animate-spin h-8 w-8 text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-600 mt-2" x-text="t('finding_best_crops')"></p>
                </div>

                <!-- Empty State - Friendly message -->
                <div x-show="!loading && !selectedMunicipality" class="text-center py-8">
                    <div class="text-4xl mb-3">👆</div>
                    <p class="text-gray-600" x-text="t('select_location_first')"></p>
                </div>

                <!-- Top 3 Recommendations - Simple Cards -->
                <div x-show="!loading && recommendations.length > 0" class="space-y-3">
                    <!-- Featured Top Pick -->
                    <template x-if="recommendations[0]">
                        <div class="bg-white rounded-lg p-4 border-2 border-primary-400 shadow-sm">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="bg-primary-100 p-3 rounded-full">
                                        <span class="text-2xl" x-text="getCropEmoji(recommendations[0].crop)"></span>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="bg-primary-dark text-white text-xs font-bold px-2 py-0.5 rounded">🏆 #1 <span x-text="t('best')"></span></span>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 mt-1" x-text="recommendations[0].crop"></h3>
                                        <p class="text-sm text-gray-600">
                                            <span x-text="t('avg_harvest')"></span>: <span class="font-semibold" x-text="recommendations[0].avg_production + ' mt (metric tons)'"></span>
                                        </p>
                                    </div>
                                </div>
                                <a :href="'{{ route('predictions.predict.form') }}?tab=forecast&crop=' + encodeURIComponent(recommendations[0].crop) + '&municipality=' + encodeURIComponent(selectedMunicipality)"
                                   class="bg-primary-dark hover:bg-primary-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
                                    <span x-text="t('predict')"></span> →
                                </a>
                            </div>
                        </div>
                    </template>

                    <!-- Other good options -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="(rec, index) in recommendations.slice(1, 3)" :key="rec.crop">
                            <div class="bg-white rounded-lg p-3 border border-gray-200">
                                <div class="flex items-center gap-3">
                                    <span class="text-xl" x-text="getCropEmoji(rec.crop)"></span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">#<span x-text="index + 2"></span></span>
                                            <h4 class="font-semibold text-gray-900 truncate" x-text="rec.crop"></h4>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            ~<span x-text="rec.avg_production"></span> mt (metric tons) average
                                        </p>
                                    </div>
                                    <a :href="'{{ route('predictions.predict.form') }}?tab=forecast&crop=' + encodeURIComponent(rec.crop) + '&municipality=' + encodeURIComponent(selectedMunicipality)"
                                       class="text-primary-dark hover:text-primary-900 text-sm font-medium">
                                        <span x-text="t('predict')"></span> →
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-white/70 px-4 py-3">
                        <p class="text-sm text-gray-600">The chart above shows the broader full-year outlook in your area, so it may not always match this month's top pick exactly.</p>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- NEXT STEP ACTIONS -->
            <!-- ============================================ -->
            <div x-data="dashboardActions()" class="mb-4 lg:mb-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Take the next step</h2>
                    <p class="text-sm text-gray-500">Use these tools after reviewing your recommendations and crop trends.</p>
                </div>

                <div class="farmer-action-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 lg:gap-4">
                    <a :href="predictionHref" class="quick-action-btn sm:col-span-2 lg:col-span-6 lg:row-span-2 rounded-2xl bg-gradient-to-br from-primary-dark via-primary to-primary-900 p-6 text-white shadow-sm">
                        <div class="flex h-full flex-col justify-between gap-6">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white/90">Recommended action</span>
                                    <h3 class="mt-4 text-2xl font-bold" x-text="t('action_predict')"></h3>
                                    <p class="mt-2 max-w-sm text-sm leading-6 text-white/85" x-text="t('action_predict_desc')"></p>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-3xl">ðŸ”®</div>
                            </div>
                            <div class="flex items-center justify-between gap-3 text-sm font-medium text-white/90 mt-2">
                                <span x-text="municipality ? 'Use your saved area for a faster start' : 'Start with a quick harvest forecast'"></span>
                                <span class="group inline-flex items-center justify-center gap-2 rounded-full bg-white px-5 py-2 text-sm font-bold text-primary-dark shadow-md transition-all duration-300 hover:scale-105 hover:bg-gray-50 hover:shadow-lg">
                                    Open
                                    <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('map.index') }}" class="quick-action-btn sm:col-span-2 lg:col-span-6 rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900" x-text="t('action_map')"></h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600" x-text="t('action_map_desc')"></p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-2xl shadow-sm">ðŸ—ºï¸</div>
                        </div>
                    </a>

                    <a href="{{ route('predictions.history') }}" class="quick-action-btn rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900" x-text="t('action_history')"></h3>
                                <p class="mt-2 text-sm leading-6 text-gray-500" x-text="t('action_history_desc')"></p>
                            </div>
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-100 text-2xl">ðŸ“Š</div>
                        </div>
                    </a>

                    <a href="{{ route('forum.index') }}" class="quick-action-btn rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:col-span-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900" x-text="t('action_forum')"></h3>
                                <p class="mt-2 text-sm leading-6 text-gray-500" x-text="t('action_forum_desc')"></p>
                            </div>
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-2xl">ðŸ’¬</div>
                        </div>
                    </a>
                </div>
            </div>

            @if (false)
            <!-- ============================================ -->
            <!-- QUICK ACTIONS - Big Friendly Buttons -->
            <!-- ============================================ -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4 lg:mb-6">
                <!-- Predict Production -->
                <a href="{{ route('predictions.predict.form') }}?tab=forecast" class="quick-action-btn bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all text-center">
                    <div class="bg-purple-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">🔮</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 text-sm" x-text="t('action_predict')"></h3>
                    <p class="text-xs text-gray-500 mt-1" x-text="t('action_predict_desc')"></p>
                </a>

                <!-- View Map -->
                <a href="{{ route('map.index') }}" class="quick-action-btn bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all text-center">
                    <div class="bg-blue-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">🗺️</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 text-sm" x-text="t('action_map')"></h3>
                    <p class="text-xs text-gray-500 mt-1" x-text="t('action_map_desc')"></p>
                </a>

                <!-- My Predictions History -->
                <a href="{{ route('predictions.history') }}" class="quick-action-btn bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all text-center">
                    <div class="bg-green-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">📊</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 text-sm" x-text="t('action_history')"></h3>
                    <p class="text-xs text-gray-500 mt-1" x-text="t('action_history_desc')"></p>
                </a>

                <!-- Forum -->
                <a href="{{ route('forum.index') }}" class="quick-action-btn bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all text-center">
                    <div class="bg-amber-100 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-2xl">💬</span>
                    </div>
                    <h3 class="font-semibold text-gray-900 text-sm" x-text="t('action_forum')"></h3>
                    <p class="text-xs text-gray-500 mt-1" x-text="t('action_forum_desc')"></p>
                </a>
            </div>

            <!-- ============================================ -->
            <!-- SIMPLE STATS - What farmers care about -->
            <!-- ============================================ -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4 lg:mb-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 mb-1" x-text="t('your_predictions')"></p>
                    <p class="text-2xl font-bold text-gray-900">{{ $predictionsCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 mb-1" x-text="t('crop_types')"></p>
                    <p class="text-2xl font-bold text-gray-900">{{ $cropTypesCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 mb-1" x-text="t('municipalities')"></p>
                    <p class="text-2xl font-bold text-gray-900">{{ $municipalitiesCount }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <p class="text-xs text-gray-500 mb-1" x-text="t('data_records')"></p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalRecords) }}</p>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TOP 5 CROPS CHART -->
            <!-- ============================================ -->
            <!-- Top 5 Crops Chart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex flex-col gap-3 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📊</span>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="t('top_5_crops')"></h3>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <label for="municipalitySelect" class="text-sm text-gray-600" x-text="t('location') + ':'"></label>
                            <select id="municipalitySelect" class="w-full sm:w-auto border-gray-300 rounded-lg shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-200">
                                <option value="LATRINIDAD">La Trinidad</option>
                                <option value="ATOK">Atok</option>
                                <option value="BAKUN">Bakun</option>
                                <option value="BOKOD">Bokod</option>
                                <option value="BUGUIAS">Buguias</option>
                                <option value="ITOGON">Itogon</option>
                                <option value="KABAYAN">Kabayan</option>
                                <option value="KAPANGAN">Kapangan</option>
                                <option value="KIBUNGAN">Kibungan</option>
                                <option value="MANKAYAN">Mankayan</option>
                                <option value="SABLAN">Sablan</option>
                                <option value="TUBA">Tuba</option>
                                <option value="TUBLAY">Tublay</option>
                            </select>
                        </div>
                    </div>
                    <div id="chartLoading" class="text-center py-8">
                        <svg class="inline-block animate-spin h-8 w-8 text-primary-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-600 mt-2" x-text="t('loading')"></p>
                    </div>
                    <div id="chartContainer" class="hidden">
                        <div class="w-full overflow-hidden">
                            <canvas id="topCropsChart"></canvas>
                        </div>
                    </div>
                    <div id="chartError" class="hidden text-center py-8 text-red-600">
                        <p class="text-sm" x-text="t('load_error')"></p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    </div> <!-- End of languageSystem wrapper -->

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
    
    <script>
        // ============================================
        // Language System with Translations
        // ============================================
        const translations = {
            en: {
                // Popup
                language_popup_title: 'Choose Your Language',
                language_popup_desc: 'Select your preferred language for the dashboard',
                continue: 'Continue',
                change_language: 'Change Language',
                
                // Greetings
                good_morning: 'Good morning,',
                good_afternoon: 'Good afternoon,',
                good_evening: 'Good evening,',
                dashboard_subtitle: 'What shall we do on the farm today?',
                
                // My Farm
                my_farm: 'My Farm',
                my_farm_desc: 'Set your location so recommendations and crop trends stay focused on your area',
                saved: 'Saved! ✓',
                where_is_farm: 'Where is your farm?',
                select_location: 'Select location...',
                what_crops: 'What do you grow?',
                
                // Recommendations
                recommendations: 'Recommendations for You',
                recommendations_desc: 'Best crops to check for {month} in your area',
                finding_best_crops: 'Finding best crops...',
                select_location_first: 'Select a location above to see recommendations',
                best: 'BEST',
                avg_harvest: 'Average harvest',
                predict: 'Predict',
                
                // Quick Actions
                action_predict: 'Make Prediction',
                action_predict_desc: 'Start a harvest forecast for your farm',
                action_map: 'View Map',
                action_map_desc: 'See crop patterns across municipalities',
                action_history: 'My History',
                action_history_desc: 'Review your past predictions',
                action_forum: 'Ask Community',
                action_forum_desc: 'Get help and tips from other farmers',
                
                // Stats
                your_predictions: 'My Predictions',
                crop_types: 'Available Crops',
                municipalities: 'Covered Areas',
                data_records: 'Historical Records',
                
                // Advanced Tools
                advanced_tools: 'Advanced Tools',
                advanced_tools_desc: 'Additional features for detailed analysis',
                
                // Harvest Calendar
                harvest_calendar: 'Harvest Calendar',
                harvest_calendar_desc: 'See when is the best time to harvest each crop',
                all_areas: 'All areas',
                crop: 'Crop',
                legend: 'Legend',
                legend_best: 'Best',
                legend_good: 'Good',
                legend_medium: 'Medium',
                legend_low: 'Low',
                legend_nodata: 'No data',
                
                // Comparison
                compare_crops: 'Compare Crops',
                compare_crops_desc: 'Select 2-3 crops to compare',
                compare: 'Compare',
                yearly_harvest: 'Yearly harvest',
                avg_productivity: 'Average productivity',
                best_months: 'Best months',
                select_2_crops: 'Select at least 2 crops to compare',
                
                // What-If
                what_if: 'What If...?',
                what_if_desc: 'Try different scenarios and see possible yields',
                where: 'Where?',
                what_to_plant: 'What to plant?',
                select_crop: 'Select crop...',
                when: 'When?',
                how_big: 'How big? (hectares)',
                complete_details: 'Complete the details to see possible harvest',
                expected_harvest: 'Expected Harvest',
                based_on_records: 'Based on {count} historical records',
                highest: 'Highest',
                lowest: 'Lowest',
                get_detailed_prediction: 'Get Detailed Prediction',
                
                // Months
                month_jan: 'January',
                month_feb: 'February',
                month_mar: 'March',
                month_apr: 'April',
                month_may: 'May',
                month_jun: 'June',
                month_jul: 'July',
                month_aug: 'August',
                month_sep: 'September',
                month_oct: 'October',
                month_nov: 'November',
                month_dec: 'December',
                
                // Chart
                top_5_crops: 'Top 5 Crops',
                location: 'Location',
                loading: 'Loading...',
                load_error: 'Failed to load data. Please try again.',
                chart_historical: 'Historical',
                chart_historical_full: 'Historical Average',
                chart_predicted: 'This Year',
                chart_predicted_year: 'This Year Forecast',
                
                // Calendar tooltips
                no_data_for: 'No data for {crop} in {month}',
                best_month_tip: '⭐ BEST MONTH! Highest harvest expected.',
                good_month_tip: '✅ GOOD month to harvest. High yields recorded.',
                ok_month_tip: '👍 OK to plant. Medium harvest expected.',
                low_month_tip: '⚠️ LOW harvest usually. Consider other months.',
            },
            tl: {
                // Popup
                language_popup_title: 'Piliin ang Wika',
                language_popup_desc: 'Piliin ang gusto mong wika para sa dashboard',
                continue: 'Magpatuloy',
                change_language: 'Palitan ang Wika',
                
                // Greetings
                good_morning: 'Magandang umaga,',
                good_afternoon: 'Magandang hapon,',
                good_evening: 'Magandang gabi,',
                dashboard_subtitle: 'Ano ang gagawin natin sa bukid ngayon?',
                
                // My Farm
                my_farm: 'Ang Aking Bukid',
                my_farm_desc: 'I-set ang iyong lokasyon para manatiling naka-focus sa iyong lugar ang rekomendasyon at crop trends',
                saved: 'Na-save! ✓',
                where_is_farm: 'Nasaan ang bukid mo?',
                select_location: 'Pumili ng lugar...',
                what_crops: 'Ano ang mga tinataniman mo?',
                
                // Recommendations
                recommendations: 'Rekomendasyon para sa Iyo',
                recommendations_desc: 'Pinakamagandang pananim na tingnan para sa {month} sa iyong lugar',
                finding_best_crops: 'Naghahanap ng pinakamahusay na pananim...',
                select_location_first: 'Pumili muna ng lokasyon sa itaas para makita ang rekomendasyon',
                best: 'PINAKAMAHUSAY',
                avg_harvest: 'Karaniwang ani',
                predict: 'I-predict',
                
                // Quick Actions
                action_predict: 'Gumawa ng Prediction',
                action_predict_desc: 'Simulan ang forecast ng ani',
                action_map: 'Tingnan ang Mapa',
                action_map_desc: 'Tingnan ang pattern ng pananim sa mga lugar',
                action_history: 'Aking History',
                action_history_desc: 'Balikan ang mga nakaraang prediction',
                action_forum: 'Magtanong sa Komunidad',
                action_forum_desc: 'Humingi ng payo mula sa ibang magsasaka',
                
                // Stats
                your_predictions: 'Aking Mga Prediction',
                crop_types: 'Mga Pananim',
                municipalities: 'Sakop na Lugar',
                data_records: 'Mga Historical Record',
                
                // Advanced Tools
                advanced_tools: 'Advanced Tools',
                advanced_tools_desc: 'Mga karagdagang feature para sa detalyadong pagsusuri',
                
                // Harvest Calendar
                harvest_calendar: 'Harvest Calendar',
                harvest_calendar_desc: 'Tingnan kung kailan maganda mag-ani ng bawat pananim',
                all_areas: 'Lahat ng lugar',
                crop: 'Pananim',
                legend: 'Ibig sabihin',
                legend_best: 'Pinakamahusay',
                legend_good: 'Maganda',
                legend_medium: 'Katamtaman',
                legend_low: 'Mababa',
                legend_nodata: 'Walang data',
                
                // Comparison
                compare_crops: 'I-compare ang mga Pananim',
                compare_crops_desc: 'Piliin ng 2-3 pananim para ihambing',
                compare: 'I-compare',
                yearly_harvest: 'Taunang ani',
                avg_productivity: 'Average productivity',
                best_months: 'Pinakamahusay na buwan',
                select_2_crops: 'Pumili ng kahit 2 pananim para ihambing',
                
                // What-If
                what_if: 'Paano Kung...?',
                what_if_desc: 'Subukan ang iba\'t ibang scenario at makita ang posibleng ani',
                where: 'Saan?',
                what_to_plant: 'Anong itatanim?',
                select_crop: 'Pumili ng pananim...',
                when: 'Kailan?',
                how_big: 'Gaano kalaki? (hectares)',
                complete_details: 'Kumpletuhin ang mga detalye para makita ang posibleng ani',
                expected_harvest: 'Inaasahang Ani',
                based_on_records: 'Base sa {count} historical records',
                highest: 'Pinakamataas',
                lowest: 'Pinakamababa',
                get_detailed_prediction: 'Kumuha ng Detalyadong Prediction',
                
                // Months
                month_jan: 'Enero',
                month_feb: 'Pebrero',
                month_mar: 'Marso',
                month_apr: 'Abril',
                month_may: 'Mayo',
                month_jun: 'Hunyo',
                month_jul: 'Hulyo',
                month_aug: 'Agosto',
                month_sep: 'Setyembre',
                month_oct: 'Oktubre',
                month_nov: 'Nobyembre',
                month_dec: 'Disyembre',
                
                // Chart
                top_5_crops: 'Top 5 Pananim',
                location: 'Lugar',
                loading: 'Nag-loload...',
                load_error: 'Hindi ma-load ang data. Subukan ulit.',
                chart_historical: 'Dati',
                chart_historical_full: 'Karaniwang Ani',
                chart_predicted: 'Ngayong Taon',
                chart_predicted_year: 'Forecast Ngayong Taon',
                
                // Calendar tooltips
                no_data_for: 'Walang data para sa {crop} sa {month}',
                best_month_tip: '⭐ PINAKAMAHUSAY na buwan! Pinakamataas na ani.',
                good_month_tip: '✅ MAGANDA ang ani sa buwang ito.',
                ok_month_tip: '👍 OK lang magtanim. Katamtamang ani.',
                low_month_tip: '⚠️ MABABA ang ani karaniwang. Subukan ibang buwan.',
            }
        };

        // Language System Alpine Component
        function languageSystem() {
            return {
                lang: 'en',
                showMenu: false,
                showPopup: false,
                
                init() {
                    this.lang = 'en';
                    this.showMenu = false;
                    this.showPopup = false;
                    localStorage.setItem('dashboard_language', 'en');
                    localStorage.setItem('preferred_language', 'en');
                    sessionStorage.setItem('lang_popup_shown', 'true');
                    
                    // Keep helper functions available while forcing English text.
                    window.currentLang = 'en';
                    window.t = this.t.bind(this);
                    window.getGreeting = this.getGreeting.bind(this);
                },
                
                t(key, params = {}) {
                    let text = translations['en']?.[key] || key;
                    
                    // Replace parameters like {month}, {count}
                    Object.keys(params).forEach(param => {
                        text = text.replace(`{${param}}`, params[param]);
                    });
                    
                    return text;
                },
                
                getGreeting() {
                    const hour = new Date().getHours();
                    if (hour < 12) return this.t('good_morning');
                    if (hour < 18) return this.t('good_afternoon');
                    return this.t('good_evening');
                },
                
                setLanguage(newLang) {
                    this.lang = 'en';
                    window.currentLang = 'en';
                    localStorage.setItem('dashboard_language', 'en');
                    localStorage.setItem('preferred_language', 'en');

                    this.showMenu = false;
                },
                
                confirmLanguage() {
                    this.lang = 'en';
                    localStorage.setItem('dashboard_language', 'en');
                    localStorage.setItem('preferred_language', 'en');
                    window.currentLang = 'en';
                    sessionStorage.setItem('lang_popup_shown', 'true');
                    this.showPopup = false;
                }
            }
        }

        // ============================================
        // Alpine.js Components
        // ============================================

        function normalizeMunicipalityForApi(municipality) {
            const normalized = String(municipality || '').trim().toUpperCase();
            return normalized === 'LA TRINIDAD' ? 'LATRINIDAD' : normalized;
        }

        function formatMunicipalityName(municipality) {
            const normalized = normalizeMunicipalityForApi(municipality);

            if (!normalized) {
                return '';
            }

            if (normalized === 'LATRINIDAD') {
                return 'La Trinidad';
            }

            return normalized
                .toLowerCase()
                .split(' ')
                .filter(Boolean)
                .map(part => part.charAt(0).toUpperCase() + part.slice(1))
                .join(' ');
        }

        // Farm Preferences Component
        // Municipality is read from onboarding; no dropdown save needed.
        function farmPreferences() {
            return {
                municipality: '{{ $preferredMunicipality ?? '' }}',

                init() {
                    // Broadcast once on init so all widgets pick up the saved location.
                    if (this.municipality) {
                        window.dispatchEvent(new CustomEvent('farm-preferences-updated', {
                            detail: { municipality: this.municipality }
                        }));
                    }
                }
            }
        }

        // Crop Recommendations Component
        function cropRecommendations() {
            const currentMonth = new Date().toLocaleString('en-US', { month: 'short' }).toUpperCase();
            return {
                selectedMunicipality: '{{ $preferredMunicipality ?? '' }}',
                selectedMonth: currentMonth,
                recommendations: [],
                loading: false,

                init() {
                    if (this.selectedMunicipality) {
                        this.loadRecommendations();
                    }
                    
                    // Listen for farm preferences updates
                    window.addEventListener('farm-preferences-updated', (e) => {
                        this.selectedMunicipality = e.detail.municipality;
                        this.loadRecommendations();
                    });
                },

                getCropEmoji(crop) {
                    const emojis = {
                        'Cabbage': '🥬',
                        'Broccoli': '🥦',
                        'Lettuce': '🥗',
                        'Cauliflower': '🌸',
                        'Chinese Cabbage': '🥬',
                        'Carrots': '🥕',
                        'Garden Peas': '🫛',
                        'White Potato': '🥔',
                        'Snap Beans': '🫘',
                        'Sweet Pepper': '🫑'
                    };
                    return emojis[crop] || '🌱';
                },

                formatMunicipality(municipality) {
                    return formatMunicipalityName(municipality);
                },

                monthLabel() {
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
                        DEC: 'December',
                    };

                    return monthNames[this.selectedMonth] || this.selectedMonth;
                },

                emitRecommendationContext() {
                    window.dispatchEvent(new CustomEvent('farmer-recommendations-updated', {
                        detail: {
                            municipality: this.selectedMunicipality || '',
                            monthLabel: this.monthLabel(),
                            topCrop: this.recommendations[0]?.crop || '',
                        }
                    }));
                },

                async loadRecommendations() {
                    if (!this.selectedMunicipality) {
                        this.recommendations = [];
                        this.emitRecommendationContext();
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch(`{{ route('farmer.recommendations') }}?municipality=${encodeURIComponent(this.selectedMunicipality)}&month=${this.selectedMonth}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.recommendations = data.recommendations || [];
                        }
                    } catch (error) {
                        console.error('Failed to load recommendations:', error);
                        this.recommendations = [];
                    } finally {
                        this.emitRecommendationContext();
                        this.loading = false;
                    }
                }
            }
        }

        function dashboardActions() {
            return {
                municipality: '{{ $preferredMunicipality ?? '' }}',

                init() {
                    window.addEventListener('farm-preferences-updated', (event) => {
                        this.municipality = event.detail.municipality || '';
                    });
                },

                get predictionHref() {
                    const params = new URLSearchParams({ tab: 'forecast' });

                    if (this.municipality) {
                        params.append('municipality', this.municipality);
                    }

                    return `{{ route('predictions.predict.form') }}?${params.toString()}`;
                }
            }
        }

        function applySimpleInsightAvatarTrim(instance) {
            const svg = instance?.renderer?.svgElement;
            if (!svg) return;

            svg.style.overflow = 'visible';
            svg.style.transformOrigin = '50% 62%';
            svg.style.transform = `translate(0px, 12%) scale(1.48)`;
        }

        function topCropsInsight() {
            return {
                municipality: '{{ $preferredMunicipality ?? '' }}',
                loading: false,
                error: false,
                insightText: '',
                insightDisplayText: '',
                recommendedCrop: '',
                recommendationMonth: '',
                chartCrops: [],
                chartHistoricalData: [],
                chartPredictedData: [],
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
                insightTypingTimer: null,
                isTypingInsight: false,
                insightToken: 0,
                animationInstance: null,
                insightObserver: null,

                init() {
                    this.$nextTick(() => {
                        this.initInsightAnimation();
                    });

                    if (this.municipality) {
                        this.loadChart();
                    }

                    window.addEventListener('farm-preferences-updated', (event) => {
                        this.municipality = event.detail.municipality || '';
                        this.loadChart();
                    });

                    window.addEventListener('farmer-recommendations-updated', (event) => {
                        this.recommendedCrop = event.detail.topCrop || '';
                        this.recommendationMonth = event.detail.monthLabel || '';
                        this.refreshInsightText();
                    });
                },

                get municipalityLabel() {
                    return formatMunicipalityName(this.municipality);
                },

                get rankedCropRows() {
                    return this.chartCrops.map((crop, index) => ({
                        rank: index + 1,
                        crop,
                        historical: Number(this.chartHistoricalData[index] || 0),
                        predicted: Number(this.chartPredictedData[index] || 0),
                        image: this.getCropImage(crop),
                    }));
                },

                getCropImage(crop) {
                    const key = String(crop || '').trim().toUpperCase();
                    return this.cropImageMap[key] || '';
                },

                isCropImageMissing(crop) {
                    const key = String(crop || '').trim().toUpperCase();
                    return Boolean(this.cropImageErrors[key]);
                },

                markCropImageMissing(crop) {
                    const key = String(crop || '').trim().toUpperCase();
                    this.cropImageErrors = {
                        ...this.cropImageErrors,
                        [key]: true,
                    };
                },

                formatCropValue(value) {
                    const number = Number(value || 0);

                    return new Intl.NumberFormat(undefined, {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2,
                    }).format(number);
                },

                prefersReducedMotion() {
                    return typeof window.matchMedia === 'function'
                        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                },

                initInsightAnimation() {
                    if (this.prefersReducedMotion()) {
                        this.destroyInsightAnimation();
                        return;
                    }

                    if (typeof lottie === 'undefined') {
                        return;
                    }

                    const lottieOpts = {
                        renderer: 'svg',
                        loop: true,
                        autoplay: false,
                        path: '{{ asset('animations/talking-character.json') }}',
                        rendererSettings: {
                            preserveAspectRatio: 'xMidYMid slice'
                        }
                    };

                    if (!this.animationInstance && this.$refs.insightAvatar) {
                        const desktopInstance = lottie.loadAnimation({
                            container: this.$refs.insightAvatar,
                            ...lottieOpts
                        });
                        desktopInstance.addEventListener('DOMLoaded', function() {
                            applySimpleInsightAvatarTrim(desktopInstance);
                        });
                        this.animationInstance = desktopInstance;
                    }
                },

                playInsightAnimation() {
                    if (this.prefersReducedMotion()) {
                        return;
                    }

                    this.initInsightAnimation();

                    if (this.animationInstance) {
                        this.animationInstance.goToAndPlay(0, true);
                    }
                },

                stopInsightAnimation() {
                    const instance = this.animationInstance;
                    if (!instance) return;
                    
                    const totalFrames = Number(instance.totalFrames || 0);
                    if (totalFrames > 1) {
                        instance.goToAndStop(totalFrames - 1, true);
                    } else {
                        instance.stop();
                    }
                },

                destroyInsightAnimation() {
                    if (this.animationInstance) {
                        this.animationInstance.destroy();
                        this.animationInstance = null;
                    }
                },

                cancelInsightNarration() {
                    this.insightToken += 1;

                    if (this.insightTypingTimer) {
                        clearInterval(this.insightTypingTimer);
                        this.insightTypingTimer = null;
                    }

                    this.isTypingInsight = false;
                },

                narrateInsightText(nextText) {
                    const safeText = String(nextText || '');

                    this.cancelInsightNarration();

                    if (!safeText) {
                        this.insightText = '';
                        this.insightDisplayText = '';
                        this.stopInsightAnimation();
                        return;
                    }

                    this.insightText = safeText;

                    if (this.prefersReducedMotion()) {
                        this.insightDisplayText = safeText;
                        this.stopInsightAnimation();
                        return;
                    }

                    const cardEl = this.$refs.insightAvatar ? this.$refs.insightAvatar.closest('.flex.items-center') : null;

                    const startTyping = () => {
                        this.cancelInsightNarration();
                        this.insightDisplayText = '';
                        this.isTypingInsight = true;
                        this.playInsightAnimation();

                        const token = this.insightToken;
                        const typingDelay = 24;
                        let charIndex = 0;

                        const timerId = window.setInterval(() => {
                            if (token !== this.insightToken) {
                                clearInterval(timerId);

                                if (this.insightTypingTimer === timerId) {
                                    this.insightTypingTimer = null;
                                }

                                return;
                            }

                            charIndex += 1;
                            this.insightDisplayText = safeText.slice(0, charIndex);

                            if (charIndex < safeText.length) {
                                return;
                            }

                            clearInterval(timerId);

                            if (this.insightTypingTimer === timerId) {
                                this.insightTypingTimer = null;
                            }

                            this.isTypingInsight = false;

                            window.setTimeout(() => {
                                if (token === this.insightToken) {
                                    this.stopInsightAnimation();
                                }
                            }, 200);
                        }, typingDelay);

                        this.insightTypingTimer = timerId;
                    };

                    if (this.insightObserver) {
                        this.insightObserver.disconnect();
                    }

                    if (cardEl && typeof IntersectionObserver !== 'undefined') {
                        this.insightObserver = new IntersectionObserver((entries) => {
                            if (entries[0].isIntersecting) {
                                this.insightObserver.disconnect();
                                startTyping();
                            }
                        }, { threshold: 0.3 });
                        this.insightObserver.observe(cardEl);
                    } else {
                        startTyping();
                    }
                },

                refreshInsightText() {
                    this.narrateInsightText(
                        this.buildTakeaway(this.chartCrops, this.chartPredictedData, this.chartHistoricalData)
                    );
                },

                buildTakeaway(crops, predictedData, historicalData) {
                    if (!crops.length) {
                        return '';
                    }

                    const highestPredicted = Math.max(...predictedData);
                    const highestHistorical = Math.max(...historicalData);
                    const predictedIndex = highestPredicted > 0 ? predictedData.indexOf(highestPredicted) : -1;
                    const historicalIndex = historicalData.indexOf(highestHistorical);
                    const bestIndex = predictedIndex >= 0 ? predictedIndex : historicalIndex;
                    const bestCrop = crops[bestIndex] || crops[0];
                    const municipalityLabel = this.municipalityLabel || 'your area';
                    const normalizedBestCrop = String(bestCrop || '').trim().toUpperCase();
                    const normalizedRecommendedCrop = String(this.recommendedCrop || '').trim().toUpperCase();

                    if (normalizedRecommendedCrop && this.recommendationMonth) {
                        if (normalizedBestCrop === normalizedRecommendedCrop) {
                            return `${bestCrop} stands out as the strongest crop choice for ${this.recommendationMonth}, and it is also expected to lead overall performance in ${municipalityLabel} for the rest of the year.`;
                        }

                        return `${this.recommendedCrop} stands out as the strongest crop choice for ${this.recommendationMonth}, while ${bestCrop} is expected to lead overall performance in ${municipalityLabel} for the rest of the year.`;
                    }

                    return `${bestCrop} is expected to lead overall performance in ${municipalityLabel} for the rest of the year, based on historical averages and this year's forecast.`;
                },

                async loadChart() {
                    this.cancelInsightNarration();
                    this.error = false;
                    this.insightText = '';
                    this.insightDisplayText = '';
                    this.stopInsightAnimation();
                    this.chartCrops = [];
                    this.chartHistoricalData = [];
                    this.chartPredictedData = [];
                    this.cropImageErrors = {};

                    if (!this.municipality) {
                        this.loading = false;
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch('{{ config("services.ml_api.url") }}/api/top-crops', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ MUNICIPALITY: normalizeMunicipalityForApi(this.municipality) })
                        });

                        if (!response.ok) {
                            throw new Error('Failed to fetch data');
                        }

                        const data = await response.json();

                        if (!data.success) {
                            throw new Error('API returned error');
                        }

                        const currentYear = new Date().getFullYear();
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

                        const rows = Array.from(merged.values())
                            .map((row) => ({
                                crop: row.crop,
                                historical: Number(row.historical || 0),
                                predicted: Number(row.predicted || 0),
                            }))
                            .sort((a, b) => {
                                const aRankValue = a.predicted > 0 ? a.predicted : a.historical;
                                const bRankValue = b.predicted > 0 ? b.predicted : b.historical;

                                if (bRankValue !== aRankValue) {
                                    return bRankValue - aRankValue;
                                }

                                if (b.predicted !== a.predicted) {
                                    return b.predicted - a.predicted;
                                }

                                if (b.historical !== a.historical) {
                                    return b.historical - a.historical;
                                }

                                return String(a.crop || '').localeCompare(String(b.crop || ''));
                            })
                            .slice(0, 5);
                        const crops = rows.map((row) => row.crop);
                        const historicalData = rows.map((row) => row.historical);
                        const predictedData = rows.map((row) => row.predicted);

                        if (!rows.length) {
                            throw new Error('No crop ranking data available');
                        }

                        this.chartCrops = crops;
                        this.chartHistoricalData = historicalData;
                        this.chartPredictedData = predictedData;
                        this.refreshInsightText();
                        this.loading = false;
                    } catch (error) {
                        console.error('Error loading crop ranking:', error);
                        this.error = true;
                        this.chartCrops = [];
                        this.chartHistoricalData = [];
                        this.chartPredictedData = [];
                        this.loading = false;
                    }
                }
            }
        }

        // Crop Comparison Component
        function cropComparison() {
            return {
                availableCrops: ['Cabbage', 'Broccoli', 'Lettuce', 'Cauliflower', 'Chinese Cabbage', 'Carrots', 'Garden Peas', 'White Potato', 'Snap Beans', 'Sweet Pepper'],
                selectedCrops: [],
                comparisonMunicipality: '{{ $preferredMunicipality ?? '' }}',
                comparisonData: null,
                loading: false,
                comparisonChart: null,

                toggleCropSelection(crop) {
                    if (this.selectedCrops.includes(crop)) {
                        this.selectedCrops = this.selectedCrops.filter(c => c !== crop);
                    } else if (this.selectedCrops.length < 3) {
                        this.selectedCrops.push(crop);
                    }
                    this.comparisonData = null;
                    if (this.comparisonChart) {
                        this.comparisonChart.destroy();
                        this.comparisonChart = null;
                    }
                },

                async compareCrops() {
                    if (this.selectedCrops.length < 2) return;
                    this.loading = true;

                    try {
                        const params = new URLSearchParams();
                        this.selectedCrops.forEach(crop => params.append('crops[]', crop));
                        if (this.comparisonMunicipality) {
                            params.append('municipality', this.comparisonMunicipality);
                        }

                        const response = await fetch(`{{ route('farmer.compare') }}?${params.toString()}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.comparisonData = data.comparison;
                            this.$nextTick(() => this.renderComparisonChart());
                        }
                    } catch (error) {
                        console.error('Failed to compare crops:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                renderComparisonChart() {
                    const canvas = document.getElementById('comparisonChart');
                    if (!canvas || !this.comparisonData) return;

                    if (this.comparisonChart) {
                        this.comparisonChart.destroy();
                    }

                    const months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                    const colors = [
                        { bg: 'rgba(34, 197, 94, 0.7)', border: 'rgba(34, 197, 94, 1)' },
                        { bg: 'rgba(59, 130, 246, 0.7)', border: 'rgba(59, 130, 246, 1)' },
                        { bg: 'rgba(249, 115, 22, 0.7)', border: 'rgba(249, 115, 22, 1)' }
                    ];

                    const datasets = this.selectedCrops.map((crop, index) => ({
                        label: crop,
                        data: months.map(month => this.comparisonData[crop]?.monthly_data?.[month] || 0),
                        backgroundColor: colors[index].bg,
                        borderColor: colors[index].border,
                        borderWidth: 2,
                        tension: 0.3
                    }));

                    this.comparisonChart = new Chart(canvas, {
                        type: 'line',
                        data: { labels: months, datasets: datasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: window.innerWidth < 768 ? 1.5 : 2.5,
                            plugins: {
                                title: { display: true, text: 'Buwanang Ani (Average MT)', font: { size: 14, weight: 'bold' } },
                                legend: { position: 'top' }
                            },
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Produksyon (MT)' } }
                            }
                        }
                    });
                }
            }
        }

        // Planting Calendar Component
        function plantingCalendar() {
            return {
                selectedMunicipality: '{{ $preferredMunicipality ?? '' }}',
                months: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
                crops: ['Cabbage', 'Broccoli', 'Lettuce', 'Cauliflower', 'Chinese Cabbage', 'Carrots', 'Garden Peas', 'White Potato', 'Snap Beans', 'Sweet Pepper'],
                calendarData: {},
                maxProduction: 1,
                loading: false,

                init() {
                    this.loadCalendarData();
                },

                getCropEmoji(crop) {
                    const emojis = {
                        'Cabbage': '🥬', 'Broccoli': '🥦', 'Lettuce': '🥗', 'Cauliflower': '🌸',
                        'Chinese Cabbage': '🥬', 'Carrots': '🥕', 'Garden Peas': '🫛',
                        'White Potato': '🥔', 'Snap Beans': '🫘', 'Sweet Pepper': '🫑'
                    };
                    return emojis[crop] || '🌱';
                },

                async loadCalendarData() {
                    this.loading = true;
                    try {
                        let url = '{{ route('farmer.calendar') }}';
                        if (this.selectedMunicipality) {
                            url += `?municipality=${encodeURIComponent(this.selectedMunicipality)}`;
                        }

                        const response = await fetch(url, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.calendarData = data.calendar || {};
                            this.maxProduction = data.max_production || 1;
                        }
                    } catch (error) {
                        console.error('Failed to load calendar data:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                getTableCellColor(crop, month) {
                    const production = this.calendarData[crop]?.monthly?.[month] || 0;
                    if (production === 0) return 'bg-gray-200';
                    
                    const ratio = production / this.maxProduction;
                    if (ratio >= 0.75) return 'bg-green-600';
                    if (ratio >= 0.55) return 'bg-green-400';
                    if (ratio >= 0.40) return 'bg-cyan-400';
                    if (ratio >= 0.25) return 'bg-yellow-400';
                    if (ratio >= 0.10) return 'bg-orange-400';
                    return 'bg-red-500';
                },

                getSimpleTooltip(crop, month) {
                    const production = this.calendarData[crop]?.monthly?.[month] || 0;
                    const monthKey = 'month_' + month.toLowerCase();
                    const monthName = window.t ? window.t(monthKey) : month;
                    
                    if (production === 0) {
                        return window.t ? window.t('no_data_for', { crop: crop, month: monthName }) : `No data for ${crop} in ${month}`;
                    }
                    
                    const ratio = production / this.maxProduction;
                    const bestMonth = this.calendarData[crop]?.best_month;
                    let advice = '';
                    
                    if (month === bestMonth) {
                        advice = window.t ? window.t('best_month_tip') : '⭐ BEST MONTH!';
                    } else if (ratio >= 0.7) {
                        advice = window.t ? window.t('good_month_tip') : '✅ GOOD month';
                    } else if (ratio >= 0.4) {
                        advice = window.t ? window.t('ok_month_tip') : '👍 OK to plant';
                    } else {
                        advice = window.t ? window.t('low_month_tip') : '⚠️ LOW harvest';
                    }
                    
                    return `${crop} - ${monthName}:\n${advice}\n(${production.toFixed(1)} MT)`;
                }
            }
        }

        // What-If Scenario Component
        function whatIfScenario() {
            const currentMonth = new Date().toLocaleString('en-US', { month: 'short' }).toUpperCase();
            return {
                scenario: {
                    municipality: '{{ $preferredMunicipality ?? '' }}',
                    crop: '',
                    month: currentMonth,
                    area: 1
                },
                result: null,
                loading: false,

                async calculateScenario() {
                    if (!this.scenario.municipality || !this.scenario.crop || !this.scenario.area) {
                        this.result = null;
                        return;
                    }

                    this.loading = true;

                    try {
                        const params = new URLSearchParams({
                            municipality: this.scenario.municipality,
                            crop: this.scenario.crop,
                            month: this.scenario.month,
                            area: this.scenario.area
                        });

                        const response = await fetch(`{{ route('farmer.scenario') }}?${params.toString()}`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.result = data.success ? data : null;
                        }
                    } catch (error) {
                        console.error('Failed to calculate scenario:', error);
                        this.result = null;
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }

    </script>
</x-app-layout>

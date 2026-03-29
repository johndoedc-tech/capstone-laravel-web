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
            <div class="rounded-lg shadow-sm p-4 lg:p-6 mb-4 lg:mb-6 text-white" style="background: linear-gradient(135deg, #355872 0%, #4A7399 50%, #5B8FB7 100%);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-primary-100 text-sm" x-text="getGreeting()"></p>
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
            <!-- MY FARM QUICK SETUP (Simplified) -->
            <!-- ============================================ -->
            <div x-data="farmPreferences()" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-2xl">🏡</span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900" x-text="t('my_farm')"></h2>
                        <p class="text-xs text-gray-500" x-text="t('my_farm_desc')"></p>
                    </div>
                    <span x-show="saved" x-transition class="ml-auto text-xs text-primary-dark bg-primary-100 px-2 py-0.5 rounded-full" x-text="t('saved')"></span>
                </div>
                
                <!-- Municipality Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">📍 <span x-text="t('where_is_farm')"></span></label>
                    <select x-model="municipality" @change="savePreferences()" 
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                        <option value="" x-text="t('select_location')"></option>
                        <option value="ATOK">Atok</option>
                        <option value="BAKUN">Bakun</option>
                        <option value="BOKOD">Bokod</option>
                        <option value="BUGUIAS">Buguias</option>
                        <option value="ITOGON">Itogon</option>
                        <option value="KABAYAN">Kabayan</option>
                        <option value="KAPANGAN">Kapangan</option>
                        <option value="KIBUNGAN">Kibungan</option>
                        <option value="LA TRINIDAD">La Trinidad</option>
                        <option value="MANKAYAN">Mankayan</option>
                        <option value="SABLAN">Sablan</option>
                        <option value="TUBA">Tuba</option>
                        <option value="TUBLAY">Tublay</option>
                    </select>
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
                </div>
            </div>

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

        </div>
    </div>

    </div> <!-- End of languageSystem wrapper -->

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
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
                my_farm_desc: 'Set up your location to get the right recommendations',
                saved: 'Saved! ✓',
                where_is_farm: 'Where is your farm?',
                select_location: 'Select location...',
                what_crops: 'What do you grow?',
                
                // Recommendations
                recommendations: 'Recommendations for You',
                recommendations_desc: 'Based on your location and current month ({month})',
                finding_best_crops: 'Finding best crops...',
                select_location_first: 'Select a location above to see recommendations',
                best: 'BEST',
                avg_harvest: 'Average harvest',
                predict: 'Predict',
                
                // Quick Actions
                action_predict: 'Predict',
                action_predict_desc: 'Forecast harvest',
                action_map: 'Map',
                action_map_desc: 'View on map',
                action_history: 'History',
                action_history_desc: 'Past predictions',
                action_forum: 'Forum',
                action_forum_desc: 'Discuss',
                
                // Stats
                your_predictions: 'Your predictions',
                crop_types: 'Crop types',
                municipalities: 'Municipalities',
                data_records: 'Data records',
                
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
                chart_historical_full: 'Historical Avg (2015-2024)',
                chart_predicted: 'Predicted',
                chart_predicted_year: 'Predicted ({year})',
                
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
                my_farm_desc: 'I-setup ang iyong lokasyon para makakuha ng tamang rekomendasyon',
                saved: 'Na-save! ✓',
                where_is_farm: 'Nasaan ang bukid mo?',
                select_location: 'Pumili ng lugar...',
                what_crops: 'Ano ang mga tinataniman mo?',
                
                // Recommendations
                recommendations: 'Rekomendasyon para sa Iyo',
                recommendations_desc: 'Base sa lokasyon mo at sa buwan ngayon ({month})',
                finding_best_crops: 'Naghahanap ng pinakamahusay na pananim...',
                select_location_first: 'Pumili muna ng lokasyon sa itaas para makita ang rekomendasyon',
                best: 'PINAKAMAHUSAY',
                avg_harvest: 'Karaniwang ani',
                predict: 'I-predict',
                
                // Quick Actions
                action_predict: 'Mag-predict',
                action_predict_desc: 'Hulaan ang ani',
                action_map: 'Mapa',
                action_map_desc: 'Tingnan sa mapa',
                action_history: 'History',
                action_history_desc: 'Mga nakaraang hulaan',
                action_forum: 'Forum',
                action_forum_desc: 'Makipag-usap',
                
                // Stats
                your_predictions: 'Iyong mga prediction',
                crop_types: 'Uri ng pananim',
                municipalities: 'Mga bayan',
                data_records: 'Data records',
                
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
                chart_historical_full: 'Karaniwang Ani (2015-2024)',
                chart_predicted: 'Hinuhulaan',
                chart_predicted_year: 'Hinuhulaan ({year})',
                
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
                    // Check if language was previously set
                    const savedLang = localStorage.getItem('dashboard_language');
                    
                    if (savedLang) {
                        this.lang = savedLang;
                    } else {
                        // Default to English
                        this.lang = 'en';
                    }
                    
                    // Show popup on every new session (login)
                    // sessionStorage is cleared when browser/tab is closed
                    const popupShownThisSession = sessionStorage.getItem('lang_popup_shown');
                    if (!popupShownThisSession) {
                        this.showPopup = true;
                    }
                    
                    // Make translation function globally available
                    window.currentLang = this.lang;
                    window.t = this.t.bind(this);
                    window.getGreeting = this.getGreeting.bind(this);
                },
                
                t(key, params = {}) {
                    let text = translations[this.lang]?.[key] || translations['en']?.[key] || key;
                    
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
                    this.lang = newLang;
                    window.currentLang = newLang;
                    localStorage.setItem('dashboard_language', newLang);

                    if (typeof window.applyAppLanguagePreference === 'function') {
                        window.applyAppLanguagePreference(newLang, false);
                    }

                    this.showMenu = false;
                },
                
                confirmLanguage() {
                    // Save language preference
                    localStorage.setItem('dashboard_language', this.lang);
                    window.currentLang = this.lang;

                    if (typeof window.applyAppLanguagePreference === 'function') {
                        window.applyAppLanguagePreference(this.lang, false);
                    }
                    
                    // Mark popup as shown for this session
                    sessionStorage.setItem('lang_popup_shown', 'true');
                    
                    // Close popup
                    this.showPopup = false;
                }
            }
        }

        // ============================================
        // Alpine.js Components
        // ============================================

        // Farm Preferences Component
        function farmPreferences() {
            return {
                municipality: '{{ $preferredMunicipality ?? '' }}',
                saved: false,
                saving: false,

                async savePreferences() {
                    if (this.saving) return;
                    this.saving = true;

                    try {
                        const response = await fetch('{{ route('farmer.preferences.save') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                preferred_municipality: this.municipality
                            })
                        });

                        if (response.ok) {
                            this.saved = true;
                            setTimeout(() => this.saved = false, 2000);
                            
                            // Trigger custom event to update recommendations
                            window.dispatchEvent(new CustomEvent('farm-preferences-updated', {
                                detail: { municipality: this.municipality }
                            }));
                        }
                    } catch (error) {
                        console.error('Failed to save preferences:', error);
                    } finally {
                        this.saving = false;
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

                async loadRecommendations() {
                    if (!this.selectedMunicipality) {
                        this.recommendations = [];
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

        // ============================================
        // Top Crops Chart
        // ============================================
        let topCropsChart = null;

        function isMobile() {
            return window.innerWidth < 768;
        }

        async function loadTopCropsChart(municipality) {
            const loadingEl = document.getElementById('chartLoading');
            const containerEl = document.getElementById('chartContainer');
            const errorEl = document.getElementById('chartError');

            loadingEl.classList.remove('hidden');
            containerEl.classList.add('hidden');
            errorEl.classList.add('hidden');

            try {
                const response = await fetch('{{ config("services.ml_api.url") }}/api/top-crops', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ MUNICIPALITY: municipality })
                });

                if (!response.ok) throw new Error('Failed to fetch data');

                const data = await response.json();
                if (!data.success) throw new Error('API returned error');

                const crops = data.historical_top5.crops.map(crop => crop.crop);
                const historicalData = data.historical_top5.crops.map(crop => crop.yearly_data.average);
                const currentYear = new Date().getFullYear();
                const predictedData = data.predicted_top5.crops.map(crop => {
                    const currentYearForecast = crop.forecasts.find(f => f.year === currentYear);
                    return currentYearForecast ? currentYearForecast.production : 0;
                });

                if (topCropsChart) topCropsChart.destroy();

                const mobile = isMobile();
                const municipalityName = municipality.charAt(0) + municipality.slice(1).toLowerCase().replace('trinidad', ' Trinidad');

                const ctx = document.getElementById('topCropsChart').getContext('2d');
                topCropsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: crops,
                        datasets: [
                            {
                                label: mobile ? window.t('chart_historical') : window.t('chart_historical_full'),
                                data: historicalData,
                                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: mobile ? 1 : 2
                            },
                            {
                                label: mobile ? window.t('chart_predicted') : window.t('chart_predicted_year', {year: currentYear}),
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
                                text: mobile ? `Top 5 - ${municipalityName}` : `Top 5 Pananim sa ${municipalityName}`,
                                font: { size: mobile ? 12 : 16, weight: 'bold' }
                            },
                            legend: { display: true, position: 'top' }
                        },
                        scales: {
                            [mobile ? 'x' : 'y']: { beginAtZero: true },
                            [mobile ? 'y' : 'x']: { ticks: { autoSkip: false } }
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
            const municipalitySelect = document.getElementById('municipalitySelect');
            if (municipalitySelect) {
                loadTopCropsChart(municipalitySelect.value);
                municipalitySelect.addEventListener('change', function() {
                    loadTopCropsChart(this.value);
                });
            }
        });
    </script>
</x-app-layout>

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
        .crop-tag.selected {
            ring: 2px;
            ring-color: #355872;
        }
    </style>

    <div class="py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header Section -->
            <div class="rounded-lg shadow-sm p-4 lg:p-6 mb-4 lg:mb-6" style="background: linear-gradient(135deg, #355872 0%, #4A7399 50%, #5B8FB7 100%);">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-sm text-primary-200">Good {{ now()->format('H') < 12 ? 'morning' : (now()->format('H') < 18 ? 'afternoon' : 'evening') }},</p>
                        <h1 class="text-2xl lg:text-3xl font-bold text-white mb-1">
                            {{ Auth::user()->name }}! 👋
                        </h1>
                        <p class="text-sm lg:text-base text-primary-200">
                            What shall we do on the farm today?
                        </p>
                    </div>
                    <div class="text-left sm:text-right bg-white/10 backdrop-blur-sm rounded-lg px-4 py-2">
                        <p class="text-xs lg:text-sm text-primary-200">{{ now()->format('l') }}</p>
                        <p class="text-base lg:text-lg font-semibold text-white">{{ now()->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- NEW: My Farm Preferences Section -->
            <!-- ============================================ -->
            <div x-data="farmPreferences()" class="bg-gradient-to-r from-sage-50 to-sage-100 rounded-lg shadow-sm border border-sage-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-sage-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <h2 class="text-lg font-semibold text-gray-900">My Farm</h2>
                            <span x-show="saved" x-transition class="text-xs text-sage-dark bg-sage-light/50 px-2 py-0.5 rounded-full">Saved!</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Municipality Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">My Municipality</label>
                                <select x-model="municipality" @change="savePreferences()" 
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm">
                                    <option value="">Select your location...</option>
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

                            <!-- Favorite Crops -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Favorite Crops <span class="text-gray-400">(up to 5)</span></label>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="crop in availableCrops" :key="crop">
                                        <button type="button" 
                                            @click="toggleCrop(crop)"
                                            :class="favoriteCrops.includes(crop) ? 'bg-primary-dark text-white border-primary-dark' : 'bg-white text-gray-700 border-gray-300 hover:border-primary'"
                                            class="crop-tag px-2 py-1 text-xs rounded-full border transition-colors">
                                            <span x-text="crop"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- NEW: Best Crop Recommendations Widget -->
            <!-- ============================================ -->
            <div x-data="cropRecommendations()" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="bg-amber-100 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Best Crops to Plant</h3>
                            <p class="text-xs text-gray-500">Based on historical production data</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                        <select x-model="selectedMunicipality" @change="loadRecommendations()"
                            class="border-gray-300 rounded-lg shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-200 text-sm">
                            <option value="">Select Municipality</option>
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
                        
                        <select x-model="selectedMonth" @change="loadRecommendations()"
                            class="border-gray-300 rounded-lg shadow-sm focus:border-primary-dark focus:ring focus:ring-primary-200 text-sm">
                            <option value="JAN">January</option>
                            <option value="FEB">February</option>
                            <option value="MAR">March</option>
                            <option value="APR">April</option>
                            <option value="MAY">May</option>
                            <option value="JUN">June</option>
                            <option value="JUL">July</option>
                            <option value="AUG">August</option>
                            <option value="SEP">September</option>
                            <option value="OCT">October</option>
                            <option value="NOV">November</option>
                            <option value="DEC">December</option>
                        </select>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="text-center py-8">
                    <svg class="inline-block animate-spin h-8 w-8 text-primary-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-600 mt-2 text-sm">Finding best crops...</p>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && !selectedMunicipality" class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm">Select a municipality to see crop recommendations</p>
                </div>

                <!-- Recommendations Grid -->
                <div x-show="!loading && recommendations.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
                    <template x-for="(rec, index) in recommendations" :key="rec.crop">
                        <div class="recommendation-card bg-gradient-to-br from-gray-50 to-white rounded-lg border border-gray-200 p-4 relative overflow-hidden">
                            <!-- Rank Badge -->
                            <div class="absolute top-2 right-2">
                                <span :class="{
                                    'bg-yellow-400 text-yellow-900': index === 0,
                                    'bg-gray-300 text-gray-700': index === 1,
                                    'bg-amber-600 text-white': index === 2,
                                    'bg-gray-200 text-gray-600': index > 2
                                }" class="text-xs font-bold px-2 py-0.5 rounded-full">
                                    #<span x-text="index + 1"></span>
                                </span>
                            </div>
                            
                            <h4 class="font-semibold text-gray-900 mb-2 pr-8" x-text="rec.crop"></h4>
                            
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Avg Production:</span>
                                    <span class="font-medium text-gray-900"><span x-text="rec.avg_production"></span> MT</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Productivity:</span>
                                    <span class="font-medium text-gray-900"><span x-text="rec.avg_productivity"></span> MT/ha</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Consistency:</span>
                                    <span :class="{
                                        'text-green-600': rec.consistency_rating === 'High',
                                        'text-yellow-600': rec.consistency_rating === 'Medium',
                                        'text-red-600': rec.consistency_rating === 'Variable'
                                    }" class="font-medium" x-text="rec.consistency_rating"></span>
                                </div>
                            </div>
                            
                            <!-- Quick Action -->
                            <a :href="'{{ route('predictions.predict.form') }}?crop=' + encodeURIComponent(rec.crop) + '&municipality=' + encodeURIComponent(selectedMunicipality)"
                               class="mt-3 block text-center text-xs text-primary-dark hover:text-primary-900 font-medium">
                                Predict Production →
                            </a>
                        </div>
                    </template>
                </div>

                <!-- No Results -->
                <div x-show="!loading && selectedMunicipality && recommendations.length === 0" class="text-center py-8 text-gray-500">
                    <p class="text-sm">No production data available for this selection</p>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- NEW: Crop Comparison Tool -->
            <!-- ============================================ -->
            <div x-data="cropComparison()" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Compare Crops</h3>
                            <p class="text-xs text-gray-500">Side-by-side analysis of 2-3 crops</p>
                        </div>
                    </div>

                    <!-- Municipality Filter for Comparison -->
                    <select x-model="comparisonMunicipality"
                        class="border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 text-sm">
                        <option value="">All Municipalities</option>
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

                <!-- Crop Selection -->
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="text-sm text-gray-600">Select crops:</span>
                    <template x-for="crop in availableCrops" :key="crop">
                        <button type="button" 
                            @click="toggleCropSelection(crop)"
                            :disabled="!selectedCrops.includes(crop) && selectedCrops.length >= 3"
                            :class="{
                                'bg-blue-600 text-white border-blue-600': selectedCrops.includes(crop),
                                'bg-white text-gray-700 border-gray-300 hover:border-blue-400': !selectedCrops.includes(crop) && selectedCrops.length < 3,
                                'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed': !selectedCrops.includes(crop) && selectedCrops.length >= 3
                            }"
                            class="px-3 py-1.5 text-xs rounded-full border transition-colors">
                            <span x-text="crop"></span>
                        </button>
                    </template>
                    
                    <button x-show="selectedCrops.length >= 2" @click="compareCrops()"
                        class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 text-xs rounded-full transition-colors">
                        Compare
                    </button>
                </div>

                <!-- Comparison Results -->
                <div x-show="loading" class="text-center py-6">
                    <svg class="inline-block animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <div x-show="!loading && comparisonData" class="space-y-4">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <template x-for="crop in selectedCrops" :key="crop">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-3" x-text="crop"></h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Yearly Production:</span>
                                        <span class="font-medium" x-text="(comparisonData[crop]?.yearly_production || 0) + ' MT'"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Avg Productivity:</span>
                                        <span class="font-medium" x-text="(comparisonData[crop]?.avg_productivity || 0) + ' MT/ha'"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Peak Months:</span>
                                        <span class="font-medium text-green-600" x-text="(comparisonData[crop]?.peak_months || []).join(', ')"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Monthly Comparison Chart -->
                    <div x-show="comparisonData">
                        <canvas id="comparisonChart" class="w-full" style="max-height: 300px;"></canvas>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="!loading && !comparisonData && selectedCrops.length < 2" class="text-center py-6 text-gray-500">
                    <p class="text-sm">Select at least 2 crops to compare their performance</p>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- Harvest Calendar - Table Layout -->
            <!-- ============================================ -->
            <div x-data="plantingCalendar()" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <!-- Simple Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Harvest Calendar</h3>
                        <p class="text-xs text-gray-500 mt-1">Colors show harvest quality throughout the year</p>
                    </div>
                    
                    <select x-model="selectedMunicipality" @change="loadCalendarData()"
                        class="text-sm border-gray-300 rounded-lg focus:border-primary-dark focus:ring-primary-200">
                        <option value="">All Areas</option>
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

                <!-- Loading -->
                <div x-show="loading" class="text-center py-8">
                    <div class="animate-spin h-8 w-8 border-3 border-gray-300 border-t-primary-dark rounded-full mx-auto"></div>
                </div>

                <!-- Table -->
                <div x-show="!loading" class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <!-- Header -->
                        <thead>
                            <tr>
                                <th class="bg-gray-100 border border-gray-300 px-4 py-2 text-left font-semibold text-gray-700 text-sm"></th>
                                <template x-for="month in months" :key="'header-' + month">
                                    <th class="border border-gray-300 px-2 py-2 text-white font-medium text-sm text-center" style="background-color: #355872;"
                                        x-text="month.substring(0, 3)"></th>
                                </template>
                            </tr>
                        </thead>
                        <!-- Body -->
                        <tbody>
                            <template x-for="crop in crops" :key="crop">
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 font-medium text-gray-800 text-sm bg-gray-50" x-text="crop"></td>
                                    <template x-for="month in months" :key="crop + '-' + month">
                                        <td 
                                            :class="getTableCellColor(crop, month)"
                                            :title="getSimpleTooltip(crop, month)"
                                            class="border border-gray-300 cursor-help">
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Legend -->
                <div x-show="!loading" class="mt-4 flex flex-wrap items-center gap-4 text-xs">
                    <span class="flex items-center gap-2">
                        <span class="w-6 h-4 bg-green-600 border border-gray-300"></span>
                        <span class="text-gray-700">Best Harvest</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-6 h-4 bg-cyan-400 border border-gray-300"></span>
                        <span class="text-gray-700">Good</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-6 h-4 bg-yellow-400 border border-gray-300"></span>
                        <span class="text-gray-700">Medium</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-6 h-4 bg-red-500 border border-gray-300"></span>
                        <span class="text-gray-700">Low</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-6 h-4 bg-gray-200 border border-gray-300"></span>
                        <span class="text-gray-700">No Data</span>
                    </span>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- NEW: What-If Scenario Builder -->
            <!-- ============================================ -->
            <div x-data="whatIfScenario()" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 mb-4 lg:mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <div class="bg-indigo-100 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">What-If Scenario Builder</h3>
                            <p class="text-xs text-gray-500">Simulate different planting decisions and see predicted outcomes</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Input Section -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-700 text-sm border-b pb-2">Configure Your Scenario</h4>
                        
                        <!-- Municipality -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Municipality</label>
                            <select x-model="scenario.municipality" @change="calculateScenario()"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                                <option value="">Select municipality...</option>
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

                        <!-- Crop Selection -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Crop to Plant</label>
                            <select x-model="scenario.crop" @change="calculateScenario()"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
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

                        <!-- Month -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Planting Month</label>
                            <select x-model="scenario.month" @change="calculateScenario()"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                                <option value="JAN">January</option>
                                <option value="FEB">February</option>
                                <option value="MAR">March</option>
                                <option value="APR">April</option>
                                <option value="MAY">May</option>
                                <option value="JUN">June</option>
                                <option value="JUL">July</option>
                                <option value="AUG">August</option>
                                <option value="SEP">September</option>
                                <option value="OCT">October</option>
                                <option value="NOV">November</option>
                                <option value="DEC">December</option>
                            </select>
                        </div>

                        <!-- Area to Plant -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Area to Plant (hectares)</label>
                            <input type="number" x-model="scenario.area" @input="calculateScenario()" 
                                min="0.1" max="100" step="0.1"
                                placeholder="e.g., 2.5"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                            <p class="text-xs text-gray-400 mt-1">Enter the area you plan to cultivate</p>
                        </div>

                        <!-- Quick Area Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <button @click="scenario.area = 0.5; calculateScenario()" 
                                class="px-3 py-1 text-xs rounded-full border border-gray-300 hover:bg-gray-100 transition-colors">0.5 ha</button>
                            <button @click="scenario.area = 1; calculateScenario()" 
                                class="px-3 py-1 text-xs rounded-full border border-gray-300 hover:bg-gray-100 transition-colors">1 ha</button>
                            <button @click="scenario.area = 2; calculateScenario()" 
                                class="px-3 py-1 text-xs rounded-full border border-gray-300 hover:bg-gray-100 transition-colors">2 ha</button>
                            <button @click="scenario.area = 5; calculateScenario()" 
                                class="px-3 py-1 text-xs rounded-full border border-gray-300 hover:bg-gray-100 transition-colors">5 ha</button>
                        </div>
                    </div>

                    <!-- Results Section -->
                    <div>
                        <h4 class="font-medium text-gray-700 text-sm border-b pb-2 mb-4">Predicted Outcome</h4>

                        <!-- Loading -->
                        <div x-show="loading" class="text-center py-8">
                            <svg class="inline-block animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && !result" class="text-center py-8 text-gray-400">
                            <svg class="mx-auto h-12 w-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            <p class="text-sm">Configure your scenario to see predictions</p>
                        </div>

                        <!-- Results Cards -->
                        <div x-show="!loading && result" class="space-y-3">
                            <!-- Predicted Production -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-green-600 font-medium">Predicted Production</p>
                                        <p class="text-2xl font-bold text-green-800"><span x-text="result?.predicted_production || 0"></span> MT</p>
                                    </div>
                                    <div class="bg-green-100 p-3 rounded-full">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-xs text-green-600 mt-2">Based on <span x-text="result?.data_points || 0"></span> historical records</p>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <p class="text-xs text-gray-500">Avg Productivity</p>
                                    <p class="text-lg font-semibold text-gray-900"><span x-text="result?.avg_productivity || 0"></span> <span class="text-xs font-normal">MT/ha</span></p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <p class="text-xs text-gray-500">Your Area</p>
                                    <p class="text-lg font-semibold text-gray-900"><span x-text="scenario.area || 0"></span> <span class="text-xs font-normal">hectares</span></p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <p class="text-xs text-gray-500">Best Case</p>
                                    <p class="text-lg font-semibold text-green-600"><span x-text="result?.best_case || 0"></span> <span class="text-xs font-normal">MT</span></p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                    <p class="text-xs text-gray-500">Worst Case</p>
                                    <p class="text-lg font-semibold text-red-600"><span x-text="result?.worst_case || 0"></span> <span class="text-xs font-normal">MT</span></p>
                                </div>
                            </div>

                            <!-- Recommendation -->
                            <div x-show="result?.recommendation" 
                                :class="result?.is_recommended ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200'"
                                class="rounded-lg p-3 border">
                                <div class="flex items-start gap-2">
                                    <span x-text="result?.is_recommended ? '✅' : '⚠️'" class="text-lg"></span>
                                    <p class="text-sm" :class="result?.is_recommended ? 'text-green-700' : 'text-yellow-700'" x-text="result?.recommendation"></p>
                                </div>
                            </div>

                            <!-- Make Prediction Button -->
                            <a :href="`{{ route('predictions.predict.form') }}?municipality=${scenario.municipality}&crop=${scenario.crop}&month=${scenario.month}`"
                               class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                Get Detailed ML Prediction →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 5 Crops Chart Section -->
            <div class="mb-4 lg:mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 lg:p-6">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-base font-semibold text-gray-700">
                                    #
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Top 5 Crops</h3>
                                    <p class="text-sm text-gray-600 mt-1">This chart shows the broader full-year crop outlook in your area.</p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs text-gray-600">
                                    <span class="font-medium uppercase tracking-wide text-gray-500">Using</span>
                                    <span id="farmerChartAreaLabel" class="font-semibold text-gray-900">La Trinidad</span>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label for="municipalitySelect"
                                        class="text-xs font-medium uppercase tracking-wide text-gray-500 whitespace-nowrap">Switch area</label>
                                    <select id="municipalitySelect"
                                        class="w-full sm:w-auto rounded-full border-gray-300 bg-white px-4 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
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

                        <div id="farmerChartInsightCard" class="w-full lg:max-w-xl">
                            {{-- Mobile: stacked vertically --}}
                            <div class="flex flex-col items-center sm:hidden">
                                <div class="relative z-10" style="width: 100px; height: 100px; margin-bottom: -18px;">
                                    <div id="farmerChartInsightAvatarMobile" class="w-full h-full overflow-hidden" aria-hidden="true"></div>
                                </div>
                                <div class="w-full rounded-2xl bg-gray-800 px-4 pt-6 pb-3 shadow-lg" style="border: 1px solid rgba(255,255,255,0.1);">
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-1">Quick insight</p>
                                    <p id="farmerChartInsightTextMobile" class="text-sm leading-relaxed text-gray-200" aria-live="polite">
                                        Loading the strongest crop outlook for the selected municipality...
                                    </p>
                                </div>
                            </div>
                            {{-- Desktop: horizontal with large character --}}
                            <div class="hidden sm:flex items-end relative">
                                <div class="shrink-0 relative z-10" style="width: 140px; margin-right: -16px; margin-bottom: -4px;">
                                    <div class="overflow-hidden">
                                        <div id="farmerChartInsightAvatar" style="width: 140px; height: 140px;" aria-hidden="true"></div>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1 rounded-2xl bg-gray-800 px-5 py-4 shadow-lg" style="border: 1px solid rgba(255,255,255,0.1);">
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 mb-1">Quick insight</p>
                                    <p id="farmerChartInsightText" class="text-sm leading-relaxed text-gray-200" aria-live="polite">
                                        Loading the strongest crop outlook for the selected municipality...
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="chartLoading" class="text-center py-8">
                        <svg class="inline-block animate-spin h-8 w-8 text-primary-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-gray-600 mt-2 text-sm">Loading chart data...</p>
                    </div>
                    <div id="chartContainer" class="hidden">
                        <div class="w-full overflow-hidden">
                            <canvas id="topCropsChart"></canvas>
                        </div>
                        <div class="mt-4 text-xs lg:text-sm text-gray-500 border-t border-gray-200 pt-3 space-y-1">
                            <p><strong>Historical (2015-2024):</strong> Average annual production from actual data</p>
                            <p><strong>Predicted:</strong> Current year forecast using ML models. <a href="{{ route('predictions.predict.form') }}" class="text-blue-600 hover:underline">View multi-year trends →</a></p>
                        </div>
                    </div>
                    <div id="chartError" class="hidden text-center py-8 text-red-600">
                        <svg class="inline-block h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm">Failed to load chart data. Please try again.</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4 mb-4 lg:mb-6">
                <!-- Total Records Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 lg:p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 lg:gap-3">
                        <div class="bg-blue-50 p-2 rounded-lg flex-shrink-0">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-600">Crop Records</p>
                            <p class="text-lg lg:text-xl font-bold text-gray-900">{{ number_format($totalRecords) }}</p>
                            <p class="text-xs text-gray-500 truncate">Historical data points</p>
                        </div>
                    </div>
                </div>

                <!-- Municipalities Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 lg:p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 lg:gap-3">
                        <div class="bg-green-50 p-2 rounded-lg flex-shrink-0">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-600">Municipalities</p>
                            <p class="text-lg lg:text-xl font-bold text-gray-900">{{ $municipalitiesCount }}</p>
                            <p class="text-xs text-gray-500 truncate">Covered areas</p>
                        </div>
                    </div>
                </div>

                <!-- Crop Types Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 lg:p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 lg:gap-3">
                        <div class="bg-amber-50 p-2 rounded-lg flex-shrink-0">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-600">Crop Types</p>
                            <p class="text-lg lg:text-xl font-bold text-gray-900">{{ $cropTypesCount }}</p>
                            <p class="text-xs text-gray-500 truncate">Different varieties</p>
                        </div>
                    </div>
                </div>

                <!-- Predictions Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 lg:p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-2 lg:gap-3">
                        <div class="bg-purple-50 p-2 rounded-lg flex-shrink-0">
                            <svg class="w-4 h-4 lg:w-5 lg:h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-600">Predictions Made</p>
                            <p class="text-lg lg:text-xl font-bold text-gray-900">{{ $predictionsCount }}</p>
                            <p class="text-xs text-purple-600 hover:underline cursor-pointer truncate">View history →</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6 mb-4 lg:mb-6">
                <!-- Interactive Map -->
                <a href="{{ route('map.index') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-3 lg:gap-4">
                        <div class="bg-blue-50 p-2 lg:p-3 rounded-lg flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-1">Interactive Map</h3>
                            <p class="text-xs lg:text-sm text-gray-600 mb-2 lg:mb-3">Visualize crop production data across Benguet municipalities</p>
                            <span class="text-blue-600 text-xs lg:text-sm font-medium hover:underline">View map →</span>
                        </div>
                    </div>
                </a>

                <!-- Make Predictions -->
                <a href="{{ route('predictions.predict.form') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start gap-3 lg:gap-4">
                        <div class="bg-purple-50 p-2 lg:p-3 rounded-lg flex-shrink-0">
                            <svg class="w-6 h-6 lg:w-8 lg:h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-1">Crop Predictions</h3>
                            <p class="text-xs lg:text-sm text-gray-600 mb-2 lg:mb-3">Use machine learning to forecast crop production</p>
                            <span class="text-purple-600 text-xs lg:text-sm font-medium hover:underline">Make prediction →</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Tips Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6">
                <h3 class="text-base lg:text-lg font-semibold text-gray-900 mb-3 lg:mb-4">System Guidelines</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 lg:gap-4">
                    <div class="flex gap-2 lg:gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-2 h-2 rounded-full mt-1 lg:mt-2" style="background-color: #355872;"></div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm lg:text-base font-semibold text-gray-900 mb-1">Data Accuracy</h4>
                            <p class="text-xs lg:text-sm text-gray-600">Keep your crop data updated for accurate predictions and insights</p>
                        </div>
                    </div>
                    <div class="flex gap-2 lg:gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-2 h-2 rounded-full mt-1 lg:mt-2" style="background-color: #5B8FB7;"></div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm lg:text-base font-semibold text-gray-900 mb-1">Seasonal Planning</h4>
                            <p class="text-xs lg:text-sm text-gray-600">Use historical trends to optimize your planting schedules</p>
                        </div>
                    </div>
                    <div class="flex gap-2 lg:gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-2 h-2 rounded-full mt-1 lg:mt-2" style="background-color: #7AAACE;"></div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-sm lg:text-base font-semibold text-gray-900 mb-1">Track Progress</h4>
                            <p class="text-xs lg:text-sm text-gray-600">Compare predictions with actual yields to improve planning</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js & Lottie CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js"></script>
    
    <script>
        // ============================================
        // Alpine.js Components for Interactive Dashboard
        // ============================================

        // Farm Preferences Component
        function farmPreferences() {
            return {
                municipality: '{{ $preferredMunicipality ?? '' }}',
                favoriteCrops: @json($favoriteCrops ?? []),
                availableCrops: ['Cabbage', 'Broccoli', 'Lettuce', 'Cauliflower', 'Chinese Cabbage', 'Carrots', 'Garden Peas', 'White Potato', 'Snap Beans', 'Sweet Pepper'],
                saved: false,
                saving: false,

                toggleCrop(crop) {
                    if (this.favoriteCrops.includes(crop)) {
                        this.favoriteCrops = this.favoriteCrops.filter(c => c !== crop);
                    } else if (this.favoriteCrops.length < 5) {
                        this.favoriteCrops.push(crop);
                    }
                    this.savePreferences();
                },

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
                                preferred_municipality: this.municipality,
                                favorite_crops: this.favoriteCrops
                            })
                        });

                        if (response.ok) {
                            this.saved = true;
                            setTimeout(() => this.saved = false, 2000);
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
                    // Clear comparison when selection changes
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
                        { bg: 'rgba(53, 88, 114, 0.7)', border: 'rgba(53, 88, 114, 1)' },
                        { bg: 'rgba(122, 170, 206, 0.7)', border: 'rgba(122, 170, 206, 1)' },
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
                        data: {
                            labels: months,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: window.innerWidth < 768 ? 1.5 : 2.5,
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Monthly Production Comparison (Average MT)',
                                    font: { size: 14, weight: 'bold' }
                                },
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Production (MT)'
                                    }
                                }
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
                selectedYear: '',
                months: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'],
                crops: ['Cabbage', 'Broccoli', 'Lettuce', 'Cauliflower', 'Chinese Cabbage', 'Carrots', 'Garden Peas', 'White Potato', 'Snap Beans', 'Sweet Pepper'],
                calendarData: {},
                maxProduction: 1,
                insights: [],
                loading: false,

                init() {
                    this.loadCalendarData();
                },

                async loadCalendarData() {
                    this.loading = true;

                    try {
                        let url = '{{ route('farmer.calendar') }}';
                        let params = [];
                        if (this.selectedMunicipality) {
                            params.push(`municipality=${encodeURIComponent(this.selectedMunicipality)}`);
                        }
                        if (this.selectedYear) {
                            params.push(`year=${encodeURIComponent(this.selectedYear)}`);
                        }
                        if (params.length > 0) {
                            url += '?' + params.join('&');
                        }

                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.calendarData = data.calendar || {};
                            this.maxProduction = data.max_production || 1;
                            this.insights = data.insights || [];
                        }
                    } catch (error) {
                        console.error('Failed to load calendar data:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                // Simple crop emojis that farmers can relate to
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

                // Simple indicator that farmers can easily understand
                getSimpleIndicator(crop, month) {
                    const production = this.calendarData[crop]?.monthly?.[month] || 0;
                    if (production === 0) return '➖';
                    
                    const ratio = production / this.maxProduction;
                    const bestMonth = this.calendarData[crop]?.best_month;
                    
                    // If this is the best month, show star
                    if (month === bestMonth) return '⭐';
                    
                    // Otherwise show based on relative production
                    if (ratio >= 0.7) return '✅';  // Good
                    if (ratio >= 0.4) return '👍';  // OK
                    if (ratio >= 0.1) return '⚠️'; // Low
                    return '➖';  // No significant data
                },

                // Background color for the indicator cell
                getSimpleIndicatorClass(crop, month) {
                    const production = this.calendarData[crop]?.monthly?.[month] || 0;
                    if (production === 0) return 'bg-gray-100';
                    
                    const ratio = production / this.maxProduction;
                    const bestMonth = this.calendarData[crop]?.best_month;
                    
                    if (month === bestMonth) return 'bg-gradient-to-br from-yellow-100 to-green-100 border-2 border-yellow-400';
                    if (ratio >= 0.7) return 'bg-green-100 border border-green-300';
                    if (ratio >= 0.4) return 'bg-yellow-50 border border-yellow-200';
                    if (ratio >= 0.1) return 'bg-orange-50 border border-orange-200';
                    return 'bg-gray-50';
                },

                // Farmer-friendly tooltip
                getSimpleTooltip(crop, month) {
                    const production = this.calendarData[crop]?.monthly?.[month] || 0;
                    const monthNames = {
                        'JAN': 'January', 'FEB': 'February', 'MAR': 'March',
                        'APR': 'April', 'MAY': 'May', 'JUN': 'June',
                        'JUL': 'July', 'AUG': 'August', 'SEP': 'September',
                        'OCT': 'October', 'NOV': 'November', 'DEC': 'December'
                    };
                    
                    if (production === 0) {
                        return `No data for ${crop} in ${monthNames[month]}`;
                    }
                    
                    const ratio = production / this.maxProduction;
                    const bestMonth = this.calendarData[crop]?.best_month;
                    let advice = '';
                    
                    if (month === bestMonth) {
                        advice = '⭐ BEST MONTH! Highest harvest expected.';
                    } else if (ratio >= 0.7) {
                        advice = '✅ GOOD month to harvest. High yields recorded.';
                    } else if (ratio >= 0.4) {
                        advice = '👍 OK to plant. Medium harvest expected.';
                    } else {
                        advice = '⚠️ LOW harvest usually. Consider other months.';
                    }
                    
                    return `${crop} in ${monthNames[month]}:\n${advice}\n(${production.toFixed(1)} MT average production)`;
                },

                getBestMonth(crop) {
                    const bestMonth = this.calendarData[crop]?.best_month || '-';
                    const monthNames = {
                        'JAN': 'Jan', 'FEB': 'Feb', 'MAR': 'Mar',
                        'APR': 'Apr', 'MAY': 'May', 'JUN': 'Jun',
                        'JUL': 'Jul', 'AUG': 'Aug', 'SEP': 'Sep',
                        'OCT': 'Oct', 'NOV': 'Nov', 'DEC': 'Dec'
                    };
                    return monthNames[bestMonth] || bestMonth;
                },

                // Minimalist indicator - just dots and stars
                getMinimalIndicator(crop, month) {
                    const production = this.calendarData[crop]?.monthly?.[month] || 0;
                    if (production === 0) return '';
                    
                    const bestMonth = this.calendarData[crop]?.best_month;
                    if (month === bestMonth) return '⭐';
                    
                    const ratio = production / this.maxProduction;
                    if (ratio >= 0.3) return '●';
                    return '●';
                },

                // Minimalist class - subtle colors
                getMinimalClass(crop, month) {
                    const production = this.calendarData[crop]?.monthly?.[month] || 0;
                    if (production === 0) return 'text-gray-200';
                    
                    const bestMonth = this.calendarData[crop]?.best_month;
                    if (month === bestMonth) return 'text-base';
                    
                    const ratio = production / this.maxProduction;
                    if (ratio >= 0.5) return 'text-green-500';
                    if (ratio >= 0.3) return 'text-green-300';
                    return 'text-gray-300';
                },

                // Color coding for table cells (like the image)
                getTableCellColor(crop, month) {
                    const production = this.calendarData[crop]?.monthly?.[month] || 0;
                    
                    if (production === 0) {
                        return 'bg-gray-200';  // No data
                    }
                    
                    const ratio = production / this.maxProduction;
                    
                    // Match the image colors
                    if (ratio >= 0.75) return 'bg-green-600';      // Dark green - Best
                    if (ratio >= 0.55) return 'bg-green-400';      // Light green - Very good
                    if (ratio >= 0.40) return 'bg-cyan-400';       // Cyan - Good
                    if (ratio >= 0.25) return 'bg-yellow-400';     // Yellow - Medium
                    if (ratio >= 0.10) return 'bg-orange-400';     // Orange - Low-medium
                    return 'bg-red-500';                            // Red - Low
                },

                // Keep old functions for compatibility
                getHeatmapColor(crop, month) {
                    return this.getSimpleIndicatorClass(crop, month);
                },

                getProductionValue(crop, month) {
                    return this.getSimpleIndicator(crop, month);
                },

                getTooltip(crop, month) {
                    return this.getSimpleTooltip(crop, month);
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
                    // Validate inputs
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
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.success) {
                                this.result = data;
                            } else {
                                this.result = null;
                            }
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
        // Original Top Crops Chart
        // ============================================
        let topCropsChart = null;
        let farmerInsightState = {
            insightTypingTimer: null,
            insightToken: 0,
            isTyping: false,
            animationInstance: null,
            mobileAnimationInstance: null
        };

        // Check if mobile device
        function isMobile() {
            return window.innerWidth < 768;
        }

        function farmerPrefersReducedMotion() {
            return typeof window.matchMedia === 'function'
                && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        }

        function applyFarmerInsightAvatarTrim(instance, mode) {
            const svg = instance?.renderer?.svgElement;
            if (!svg) return;

            const isMobile = mode === 'mobile';
            const scale = isMobile ? 1.46 : 1.50;
            const offsetY = isMobile ? 14 : 12;

            svg.style.overflow = 'visible';
            svg.style.transformOrigin = '50% 62%';
            svg.style.transform = `translate(0px, ${offsetY}px) scale(${scale})`;
        }

        function initFarmerInsightAnimation() {
            if (farmerPrefersReducedMotion()) {
                destroyFarmerInsightAnimation();
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

            // Desktop container
            if (!farmerInsightState.animationInstance) {
                const desktop = document.getElementById('farmerChartInsightAvatar');
                if (desktop) {
                    const desktopInstance = lottie.loadAnimation({ container: desktop, ...lottieOpts });
                    desktopInstance.addEventListener('DOMLoaded', function() {
                        applyFarmerInsightAvatarTrim(desktopInstance, 'desktop');
                    });
                    farmerInsightState.animationInstance = desktopInstance;
                }
            }

            // Mobile container
            if (!farmerInsightState.mobileAnimationInstance) {
                const mobile = document.getElementById('farmerChartInsightAvatarMobile');
                if (mobile) {
                    const mobileInstance = lottie.loadAnimation({ container: mobile, ...lottieOpts });
                    mobileInstance.addEventListener('DOMLoaded', function() {
                        applyFarmerInsightAvatarTrim(mobileInstance, 'mobile');
                    });
                    farmerInsightState.mobileAnimationInstance = mobileInstance;
                }
            }
        }

        function playFarmerInsightAnimation() {
            if (farmerPrefersReducedMotion()) return;
            initFarmerInsightAnimation();
            if (farmerInsightState.animationInstance) farmerInsightState.animationInstance.goToAndPlay(0, true);
            if (farmerInsightState.mobileAnimationInstance) farmerInsightState.mobileAnimationInstance.goToAndPlay(0, true);
        }

        function stopFarmerInsightAnimation() {
            [farmerInsightState.animationInstance, farmerInsightState.mobileAnimationInstance].forEach(inst => {
                if (!inst) return;
                const totalFrames = Number(inst.totalFrames || 0);
                if (totalFrames > 1) { inst.goToAndStop(totalFrames - 1, true); }
                else { inst.stop(); }
            });
        }

        function destroyFarmerInsightAnimation() {
            if (farmerInsightState.animationInstance) {
                farmerInsightState.animationInstance.destroy();
                farmerInsightState.animationInstance = null;
            }
            if (farmerInsightState.mobileAnimationInstance) {
                farmerInsightState.mobileAnimationInstance.destroy();
                farmerInsightState.mobileAnimationInstance = null;
            }
        }

        function cancelFarmerInsightNarration() {
            farmerInsightState.insightToken += 1;
            if (!farmerInsightState.insightTypingTimer) {
                farmerInsightState.isTyping = false;
                return;
            }
            clearInterval(farmerInsightState.insightTypingTimer);
            farmerInsightState.insightTypingTimer = null;
            farmerInsightState.isTyping = false;
        }

        function narrateFarmerInsightText(nextText) {
            const el = document.getElementById('farmerChartInsightText');
            const elMobile = document.getElementById('farmerChartInsightTextMobile');
            const safeText = String(nextText || '');
            cancelFarmerInsightNarration();
            if (!el && !elMobile) return;

            function setAllText(text) {
                if (el) el.textContent = text;
                if (elMobile) elMobile.textContent = text;
            }

            if (!safeText) { setAllText(''); stopFarmerInsightAnimation(); return; }
            if (farmerPrefersReducedMotion()) { setAllText(safeText); stopFarmerInsightAnimation(); return; }
            setAllText('');
            farmerInsightState.isTyping = true;
            playFarmerInsightAnimation();
            const token = farmerInsightState.insightToken;
            let charIndex = 0;
            const timerId = window.setInterval(() => {
                if (token !== farmerInsightState.insightToken) { clearInterval(timerId); return; }
                charIndex += 1;
                setAllText(safeText.slice(0, charIndex));
                if (charIndex >= safeText.length) {
                    clearInterval(timerId);
                    farmerInsightState.insightTypingTimer = null;
                    farmerInsightState.isTyping = false;
                    window.setTimeout(() => { if (token === farmerInsightState.insightToken) stopFarmerInsightAnimation(); }, 200);
                }
            }, 24);
            farmerInsightState.insightTypingTimer = timerId;
        }

        function buildFarmerInsight(crops, historicalData, predictedData, municipalityName, currentYear) {
            if (!crops.length) return `No crop outlook data is available for ${municipalityName} yet.`;
            const predictedLeaderIndex = predictedData.reduce((best, val, i, arr) => val > arr[best] ? i : best, 0);
            const historicalLeaderIndex = historicalData.reduce((best, val, i, arr) => val > arr[best] ? i : best, 0);
            const predictedLeader = crops[predictedLeaderIndex];
            const historicalLeader = crops[historicalLeaderIndex];
            if (predictedLeader === historicalLeader) {
                return `${predictedLeader} remains the strongest full-year crop in ${municipalityName}, leading both the historical average and the ${currentYear} forecast.`;
            }
            return `${predictedLeader} has the strongest ${currentYear} outlook in ${municipalityName}, while ${historicalLeader} leads the historical average.`;
        }

        // Load chart data for selected municipality
        async function loadTopCropsChart(municipality) {
            const loadingEl = document.getElementById('chartLoading');
            const containerEl = document.getElementById('chartContainer');
            const errorEl = document.getElementById('chartError');
            const areaLabelEl = document.getElementById('farmerChartAreaLabel');
            const municipalityName = municipality.charAt(0) + municipality.slice(1).toLowerCase().replace('trinidad', ' Trinidad');

            if (areaLabelEl) areaLabelEl.textContent = municipalityName;

            // Show loading state
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

                // Prepare chart data
                const crops = data.historical_top5.crops.map(crop => crop.crop);
                // Convert historical monthly average to yearly average for fair comparison
                const historicalData = data.historical_top5.crops.map(crop => crop.yearly_data.average);
                
                // Get only the current year's prediction (or next year if past December)
                const currentYear = new Date().getFullYear();
                const predictedData = data.predicted_top5.crops.map(crop => {
                    // Find the forecast for current year
                    const currentYearForecast = crop.forecasts.find(f => f.year === currentYear);
                    return currentYearForecast ? currentYearForecast.production : 0;
                });

                // Destroy existing chart if any
                if (topCropsChart) {
                    topCropsChart.destroy();
                }

                const mobile = isMobile();
                const municipalityName = municipality.charAt(0) + municipality.slice(1).toLowerCase().replace('trinidad', ' Trinidad');

                // Generate and narrate insight
                const insightText = buildFarmerInsight(crops, historicalData, predictedData, municipalityName, currentYear);
                narrateFarmerInsightText(insightText);

                // Create new chart
                const ctx = document.getElementById('topCropsChart').getContext('2d');
                topCropsChart = new Chart(ctx, {
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
                                    maxRotation: mobile ? 0 : 0,
                                    minRotation: mobile ? 0 : 0
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

                // Hide loading, show chart
                loadingEl.classList.add('hidden');
                containerEl.classList.remove('hidden');

            } catch (error) {
                console.error('Error loading chart:', error);
                loadingEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
            }
        }

        // Initialize chart on page load
        document.addEventListener('DOMContentLoaded', function() {
            const municipalitySelect = document.getElementById('municipalitySelect');
            
            // Load initial chart
            loadTopCropsChart(municipalitySelect.value);

            // Update chart when municipality changes
            municipalitySelect.addEventListener('change', function() {
                loadTopCropsChart(this.value);
            });

            // Reload chart on window resize to switch between mobile/desktop view
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    loadTopCropsChart(municipalitySelect.value);
                }, 250);
            });
        });
    </script>
</x-app-layout>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BenguetCropMap - GIS-Integrated DSS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #f5f5dc; /* Light beige */
        }
        
        .hero-section {
            position: relative;
            background-image: url('{{ asset('images/crop-yield-ant-rozetsky-c0PJUAtpSo-unsplash.jpg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin-top: 0;
            padding-top: 0;
        }
        
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(245, 245, 220, 0.3), rgba(245, 245, 220, 0.5));
        }
        
        .hero-content {
            position: relative;
            z-index: 10;
        }
        
        /* Fade effect on scroll */
        .fade-on-scroll {
            transition: opacity 0.5s ease-in-out;
        }
        
        /* Prevent layout shift */
        #hero {
            will-change: opacity;
        }
        
        /* Fade animation for sections */
        .fade-section {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        
        .fade-section.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="font-sans text-gray-800 m-0 p-0">
    <!-- Header -->
    <header class="fixed top-2 sm:top-3 md:top-6 left-0 right-0 z-50 flex justify-center px-2 sm:px-3 md:px-0">
        <div class="bg-white rounded-full shadow-xl px-2 sm:px-4 md:px-8 py-2 sm:py-3 md:py-4 inline-flex max-w-[calc(100%-16px)] sm:w-full sm:max-w-4xl md:w-auto">
            <nav class="flex flex-wrap items-center justify-center gap-1 sm:gap-2 md:gap-4 lg:gap-8 w-full">
                    <a href="#about" class="text-gray-900 text-[10px] sm:text-xs md:text-base font-medium hover:text-lime-400 transition-colors duration-300 relative group whitespace-nowrap">
                        About
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-lime-400 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#features" class="text-gray-900 text-[10px] sm:text-xs md:text-base font-medium hover:text-lime-400 transition-colors duration-300 relative group whitespace-nowrap">
                        Features
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-lime-400 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    <a href="#developers" class="text-gray-900 text-[10px] sm:text-xs md:text-base font-medium hover:text-lime-400 transition-colors duration-300 relative group whitespace-nowrap">
                        Team
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-lime-400 group-hover:w-full transition-all duration-300"></span>
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-lime-400 text-gray-900 px-2 sm:px-4 md:px-6 py-1 sm:py-2 md:py-2.5 rounded-full font-semibold hover:bg-lime-500 hover:-translate-y-0.5 transition-all duration-300 shadow-md hover:shadow-lime-400/50 inline-flex items-center gap-1 sm:gap-2 text-[10px] sm:text-xs md:text-base whitespace-nowrap">
                            <span class="hidden min-[400px]:inline">Dashboard</span>
                            <span class="min-[400px]:hidden">🏠</span>
                            <span>→</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-900 text-[10px] sm:text-xs md:text-base font-medium hover:text-lime-400 transition-colors duration-300 relative group whitespace-nowrap">
                            Login
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-lime-400 group-hover:w-full transition-all duration-300"></span>
                        </a>
                        <a href="{{ route('register') }}" class="bg-lime-400 text-gray-900 px-2 sm:px-4 md:px-6 py-1 sm:py-2 md:py-2.5 rounded-full font-semibold hover:bg-lime-500 hover:-translate-y-0.5 transition-all duration-300 shadow-md hover:shadow-lime-400/50 inline-flex items-center gap-1 sm:gap-2 text-[10px] sm:text-xs md:text-base whitespace-nowrap">
                            Sign Up
                            <span>→</span>
                        </a>
                    @endauth
                </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="hero-section min-h-screen flex items-center justify-center px-3 sm:px-4 md:px-6 fade-on-scroll">
        <div class="hero-overlay"></div>
        <div class="hero-content max-w-5xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl font-bold text-gray-900 mb-4 sm:mb-6 md:mb-8 leading-tight drop-shadow-lg px-2">
                BenguetCropMap
            </h1>
            <p class="text-base sm:text-lg md:text-xl lg:text-3xl text-gray-800 font-light italic leading-relaxed max-w-4xl mx-auto drop-shadow-md px-4 sm:px-6">
                "Empowering farmers and decision-makers with intelligent insights for sustainable agriculture in the highlands of Benguet."
            </p>
            <div class="mt-6 sm:mt-8 md:mt-12 flex flex-col sm:flex-row gap-3 md:gap-4 justify-center items-center px-4">
                <a href="#features" class="w-full sm:w-auto bg-lime-400 text-gray-900 px-6 md:px-8 py-3 md:py-4 rounded-full font-semibold hover:bg-lime-500 hover:-translate-y-1 transition-all duration-300 shadow-lg hover:shadow-lime-400/50 text-sm sm:text-base md:text-lg text-center">
                    Explore Features
                </a>
                <a href="#about" class="w-full sm:w-auto bg-white text-gray-900 px-6 md:px-8 py-3 md:py-4 rounded-full font-semibold hover:bg-gray-100 hover:-translate-y-1 transition-all duration-300 shadow-lg text-sm sm:text-base md:text-lg border-2 border-gray-900 text-center">
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-12 py-8 sm:py-12 md:py-20">
        <!-- About Section -->
        <section id="about" class="mb-16 sm:mb-20 md:mb-32 fade-section">
            <div class="text-center mb-8 sm:mb-12 md:mb-16">
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-2">
                    About BenguetCropMap
                </h2>
                <div class="w-16 sm:w-20 md:w-24 h-1 bg-lime-400 mx-auto"></div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6 sm:gap-8 md:gap-12 items-center">
                <div class="space-y-3 sm:space-y-4 md:space-y-6 px-2 sm:px-0">
                    <p class="text-sm sm:text-base md:text-lg text-gray-700 leading-relaxed">
                        BenguetCropMap is a cutting-edge <span class="font-semibold text-lime-600">GIS-Integrated Decision Support System</span> designed to revolutionize agricultural planning in Benguet Province.
                    </p>
                    <p class="text-sm sm:text-base md:text-lg text-gray-700 leading-relaxed">
                        By combining <span class="font-semibold">Geographic Information Systems (GIS)</span>, <span class="font-semibold">Machine Learning</span>, and <span class="font-semibold">real-time data visualization</span>, we empower farmers, agricultural officers, and decision-makers with actionable insights.
                    </p>
                    <div class="flex flex-wrap gap-2 sm:gap-3 md:gap-4 pt-2 sm:pt-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-lime-400 rounded-full"></div>
                            <span class="text-xs sm:text-sm md:text-base text-gray-700">Data-Driven Insights</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-lime-400 rounded-full"></div>
                            <span class="text-xs sm:text-sm md:text-base text-gray-700">ML Predictions</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 bg-lime-400 rounded-full"></div>
                            <span class="text-xs sm:text-sm md:text-base text-gray-700">Interactive Maps</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-lime-400 to-lime-500 rounded-2xl p-5 sm:p-6 md:p-8 shadow-xl">
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-3 sm:mb-4 md:mb-6">Our Mission</h3>
                    <p class="text-gray-800 text-sm sm:text-base md:text-lg leading-relaxed mb-3 sm:mb-4 md:mb-6">
                        To transform agricultural decision-making in Benguet's highlands through innovative technology and accessible data visualization.
                    </p>
                    <div class="grid grid-cols-2 gap-2 sm:gap-3 md:gap-4">
                        <div class="bg-white/30 backdrop-blur rounded-lg p-3 md:p-4">
                            <div class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">13</div>
                            <div class="text-xs md:text-sm text-gray-800">Municipalities</div>
                        </div>
                        <div class="bg-white/30 backdrop-blur rounded-lg p-3 md:p-4">
                            <div class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">8+</div>
                            <div class="text-xs md:text-sm text-gray-800">Crop Types</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Key Features Section -->
        <section id="features" class="mb-16 sm:mb-20 md:mb-32 fade-section">
            <div class="text-center mb-8 sm:mb-12 md:mb-16">
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-2">
                    Key Features
                </h2>
                <div class="w-16 sm:w-20 md:w-24 h-1 bg-lime-400 mx-auto mb-3 sm:mb-4"></div>
                <p class="text-base sm:text-lg md:text-xl text-gray-600 max-w-3xl mx-auto px-4">
                    Powerful tools designed to help you make informed agricultural decisions
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 md:gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 md:p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border-t-4 border-lime-400">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-lime-100 rounded-full flex items-center justify-center mb-3 sm:mb-4 md:mb-6">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-2 sm:mb-3 md:mb-4">Interactive GIS Maps</h3>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 leading-relaxed mb-3 sm:mb-4">
                        Visualize crop production trends across Benguet municipalities with color-coded heatmaps and interactive tooltips.
                    </p>
                    <ul class="space-y-1.5 sm:space-y-2 text-xs md:text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Municipality-level visualization</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Crop-specific filtering</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Historical data comparison</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 md:p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border-t-4 border-lime-400">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-lime-100 rounded-full flex items-center justify-center mb-3 sm:mb-4 md:mb-6">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-2 sm:mb-3 md:mb-4">ML-Powered Predictions</h3>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 leading-relaxed mb-3 sm:mb-4">
                        Leverage Random Forest machine learning to predict crop yields based on historical patterns and environmental factors.
                    </p>
                    <ul class="space-y-1.5 sm:space-y-2 text-xs md:text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Yield forecasting</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Optimal planting schedules</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Production recommendations</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-5 sm:p-6 md:p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border-t-4 border-lime-400">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-lime-100 rounded-full flex items-center justify-center mb-3 sm:mb-4 md:mb-6">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-2 sm:mb-3 md:mb-4">Analytics Dashboard</h3>
                    <p class="text-xs sm:text-sm md:text-base text-gray-600 leading-relaxed mb-3 sm:mb-4">
                        Comprehensive data visualizations including trend charts, seasonal patterns, and comparative analysis tools.
                    </p>
                    <ul class="space-y-1.5 sm:space-y-2 text-xs md:text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Production trends</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Seasonal analysis</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-lime-500 mt-1">✓</span>
                            <span>Export reports (PDF/Excel)</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Developers Section -->
        <section id="developers" class="mb-16 sm:mb-20 md:mb-32 fade-section">
            <div class="text-center mb-8 sm:mb-12 md:mb-16">
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-2">
                    Meet The Team
                </h2>
                <div class="w-16 sm:w-20 md:w-24 h-1 bg-lime-400 mx-auto mb-3 sm:mb-4"></div>
                <p class="text-base sm:text-lg md:text-xl text-gray-600 max-w-3xl mx-auto px-4">
                    The passionate developers behind BenguetCropMap
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-4 sm:gap-6 md:gap-8 max-w-4xl mx-auto">
                <!-- Developer 1 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 w-full sm:w-80">
                    <div class="h-32 sm:h-40 md:h-48 bg-gradient-to-br from-lime-400 to-lime-500"></div>
                    <div class="p-4 sm:p-6 md:p-8 text-center">
                        <img src="{{ asset('images/jd.jpg') }}" alt="Johndoe Dela Cruz" class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 rounded-full mx-auto -mt-10 sm:-mt-12 md:-mt-16 mb-3 sm:mb-4 border-4 border-white shadow-lg object-cover">
                        <h3 class="text-base sm:text-lg md:text-xl font-bold text-gray-900 mb-1.5 sm:mb-2">Johndoe Dela Cruz</h3>
                        <p class="text-lime-600 font-medium mb-2 sm:mb-3 md:mb-4 text-xs sm:text-sm md:text-base">Full Stack Developer</p>
                        <p class="text-gray-600 text-xs md:text-sm mb-3 sm:mb-4 md:mb-6">
                            Specializes in Laravel backend development and GIS integration.
                        </p>
                        <div class="flex justify-center gap-2 sm:gap-3 md:gap-4">
                            <a href="https://github.com/JohndoeDelaCruz" class="w-9 h-9 sm:w-10 sm:h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-lime-400 transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                            </a>
                            <a href="https://www.linkedin.com/in/johndoe-amiel-dela-cruz-b33b95359/" class="w-9 h-9 sm:w-10 sm:h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-lime-400 transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Developer 2 -->
                <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 w-full sm:w-80">
                    <div class="h-32 sm:h-40 md:h-48 bg-gradient-to-br from-lime-400 to-lime-500"></div>
                    <div class="p-4 sm:p-6 md:p-8 text-center">
                        <img src="{{ asset('images/alfrey.jpg') }}" alt="Lord Alfrey Baterina" class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 rounded-full mx-auto -mt-10 sm:-mt-12 md:-mt-16 mb-3 sm:mb-4 border-4 border-white shadow-lg object-cover">
                        <h3 class="text-base sm:text-lg md:text-xl font-bold text-gray-900 mb-1.5 sm:mb-2">Lord Alfrey Baterina</h3>
                        <p class="text-lime-600 font-medium mb-2 sm:mb-3 md:mb-4 text-xs sm:text-sm md:text-base">Full Stack Developer</p>
                        <p class="text-gray-600 text-xs md:text-sm mb-3 sm:mb-4 md:mb-6">
                            Specializes in Laravel backend development and GIS integration.
                        </p>
                        <div class="flex justify-center gap-2 sm:gap-3 md:gap-4">
                            <a href="https://github.com/Garousin" class="w-9 h-9 sm:w-10 sm:h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-lime-400 transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                            </a>
                            <a href="#" class="w-9 h-9 sm:w-10 sm:h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-lime-400 transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                
                </div>
            </div>
        </section>
    </div>

    <!-- Scroll Fade Effect Script -->
    <script>
        // Smooth scroll animation for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                
                if (targetId === '#contact') {
                    // Handle contact button if needed
                    return;
                }
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    // Calculate offset for fixed header (responsive for all screen sizes)
                    const isMobile = window.innerWidth < 640; // sm breakpoint
                    const isTablet = window.innerWidth >= 640 && window.innerWidth < 768; // md breakpoint
                    let headerOffset;
                    
                    if (isMobile) {
                        headerOffset = 100; // Smaller offset for mobile (< 640px)
                    } else if (isTablet) {
                        headerOffset = 130; // Medium offset for tablets (640-768px)
                    } else {
                        headerOffset = 160; // Larger offset for desktop
                    }
                    
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Scroll fade effect for hero section
        let lastScrollPosition = 0;
        
        window.addEventListener('scroll', function() {
            const hero = document.getElementById('hero');
            const scrollPosition = window.scrollY;
            const heroHeight = hero.offsetHeight;
            const isScrollingUp = scrollPosition < lastScrollPosition;
            
            // Calculate opacity based on scroll position
            let opacity;
            
            if (isScrollingUp && scrollPosition < heroHeight) {
                // Fade in when scrolling up
                opacity = Math.min(1, 1 - (scrollPosition / heroHeight));
            } else {
                // Fade out when scrolling down
                opacity = Math.max(0, 1 - (scrollPosition / heroHeight) * 1.5);
            }
            
            hero.style.opacity = opacity;
            
            // Use pointer-events instead of display to prevent layout shifts
            if (scrollPosition > heroHeight * 1.5) {
                hero.style.pointerEvents = 'none';
            } else {
                hero.style.pointerEvents = 'auto';
            }
            
            lastScrollPosition = scrollPosition;
        });
        
        // Fade in/out animation for sections on scroll (optimized for mobile)
        const fadeElements = document.querySelectorAll('.fade-section');
        
        const checkFade = () => {
            fadeElements.forEach(element => {
                const rect = element.getBoundingClientRect();
                const windowHeight = window.innerHeight;
                
                // More lenient trigger for mobile screens
                const isMobile = window.innerWidth < 768;
                const triggerPoint = isMobile ? 0.90 : 0.85;
                
                // Element is visible when it's in the viewport
                if (rect.top < windowHeight * triggerPoint && rect.bottom > 0) {
                    element.classList.add('visible');
                } else {
                    element.classList.remove('visible');
                }
            });
        };
        
        // Check on load and scroll
        checkFade();
        window.addEventListener('scroll', checkFade);
        
        // Recalculate on window resize (for orientation changes on mobile)
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(checkFade, 150);
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $routeName = request()->route()?->getName() ?? '';

            $pageTitle = match ($routeName) {
                'login' => 'Login',
                'register' => 'Register',
                'password.request' => 'Password Help',
                'verification.notice' => 'Verify Email',
                'password.confirm' => 'Confirm Password',
                default => 'Authentication',
            };
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Harviana - {{ $pageTitle }}</title>
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        @include('partials.pwa')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            html {
                min-height: 100%;
            }

            body {
                background: linear-gradient(135deg, #F6F0D7 0%, #E8EFD9 50%, #D9E4C2 100%);
                min-height: 100vh;
                min-height: var(--harviana-viewport-height, 100dvh);
            }

            .site-return {
                top: calc(0.75rem + env(safe-area-inset-top, 0px));
                left: calc(0.75rem + env(safe-area-inset-left, 0px));
            }

            @media (min-width: 640px) {
                .site-return {
                    top: calc(1rem + env(safe-area-inset-top, 0px));
                    left: calc(1rem + env(safe-area-inset-left, 0px));
                }
            }

            @media (min-width: 768px) {
                .site-return {
                    top: calc(1.5rem + env(safe-area-inset-top, 0px));
                    left: calc(1.5rem + env(safe-area-inset-left, 0px));
                }

                .auth-brand-panel,
                .auth-form-panel {
                    min-height: var(--harviana-viewport-height, 100dvh);
                }
            }

            .auth-shell {
                min-height: 100vh;
                min-height: var(--harviana-viewport-height, 100dvh);
                padding-right: env(safe-area-inset-right, 0px);
                padding-bottom: env(safe-area-inset-bottom, 0px);
                padding-left: env(safe-area-inset-left, 0px);
            }

            .auth-card {
                backdrop-filter: blur(10px);
                background: rgba(255, 255, 255, 0.95);
            }

            @media (max-width: 767px) {
                body {
                    overflow-x: hidden;
                }

                .auth-shell {
                    justify-content: flex-start;
                    gap: 1.25rem;
                    padding: calc(4.875rem + env(safe-area-inset-top, 0px)) calc(1rem + env(safe-area-inset-right, 0px)) calc(1.25rem + env(safe-area-inset-bottom, 0px)) calc(1rem + env(safe-area-inset-left, 0px));
                }

                .auth-brand-panel,
                .auth-form-panel {
                    flex: 0 0 auto;
                    min-height: 0;
                    padding: 0;
                }

                .auth-form-panel {
                    background: transparent;
                    justify-content: flex-start;
                }

                .auth-logo-frame {
                    width: 12rem;
                    height: 6.75rem;
                }

                .auth-logo-image {
                    width: 21rem;
                }

                .auth-title {
                    font-size: 2rem;
                    margin-top: -1.25rem;
                }

                .auth-subtitle {
                    max-width: 20rem;
                    margin-left: auto;
                    margin-right: auto;
                    font-size: 0.875rem;
                }

                .auth-card {
                    padding: 1.25rem;
                    border-radius: 1.25rem;
                }
            }

            @media (max-width: 374px), (max-height: 740px) and (max-width: 767px) {
                .auth-shell {
                    gap: 0.875rem;
                    padding-top: calc(4.5rem + env(safe-area-inset-top, 0px));
                }

                .auth-logo-frame {
                    width: 10.5rem;
                    height: 5.75rem;
                }

                .auth-logo-image {
                    width: 18rem;
                }

                .auth-title {
                    font-size: 1.75rem;
                    margin-top: -1rem;
                }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <!-- Header with Back to Home Button -->
        <header class="site-return fixed z-50">
            <div class="bg-white/90 backdrop-blur-sm rounded-full shadow-lg px-4 sm:px-6 md:px-8 py-2.5 sm:py-3 md:py-3.5 inline-flex border border-white/50">
                <nav class="flex items-center justify-center">
                    <a href="{{ route('welcome') }}" class="text-gray-700 text-xs sm:text-sm md:text-base font-medium hover:text-green-700 transition-colors duration-300 relative group inline-flex items-center gap-1.5 sm:gap-2 whitespace-nowrap">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span class="hidden min-[400px]:inline">Back to Home</span>
                        <span class="min-[400px]:hidden">Home</span>
                    </a>
                </nav>
            </div>
        </header>

        <div class="auth-shell min-h-screen flex flex-col md:flex-row">
            <!-- Left Panel - Logo / Brand -->
            <div class="auth-brand-panel w-full md:w-2/5 flex flex-col justify-center items-center py-8 sm:py-10 md:py-0 px-6 md:px-12 md:min-h-screen">
                <div class="text-center">
                    <div class="auth-logo-frame mb-0 relative mx-auto w-72 h-36 sm:w-80 sm:h-48 md:w-[33rem] md:h-56 overflow-hidden">
                        <img src="{{ asset('images/HarvianaLogo.png') }}" alt="Harviana Logo" class="auth-logo-image w-[30rem] sm:w-[33rem] md:w-[35rem] h-auto absolute top-0 left-1/2 -translate-x-1/2">
                    </div>
                    <h1 class="auth-title text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 tracking-tight leading-none -mt-5 sm:-mt-4 md:-mt-2">
                        Harviana
                    </h1>
                    <p class="auth-subtitle text-sm sm:text-base md:text-lg text-gray-500 mt-1 leading-tight">Agricultural Decision Support System</p>
                </div>
                <!-- Footer (visible on desktop) -->
                <p class="hidden md:block mt-12 text-xs text-gray-400">&copy; {{ date('Y') }} BenguetCropMap. All rights reserved.</p>
            </div>

            <!-- Right Panel - Auth Form -->
            <div class="auth-form-panel w-full md:w-3/5 flex flex-col justify-center items-center py-4 sm:py-6 md:py-0 px-4 sm:px-6 md:px-12 md:min-h-screen bg-white/50 md:bg-white/70">
                <div class="w-full max-w-md auth-card px-5 sm:px-8 py-6 sm:py-8 shadow-xl rounded-2xl border border-gray-100 bg-white">
                    {{ $slot }}
                </div>
                <!-- Footer (visible on mobile) -->
                <p class="md:hidden mt-4 text-xs text-gray-400">&copy; {{ date('Y') }} BenguetCropMap. All rights reserved.</p>
            </div>
        </div>
    </body>
</html>

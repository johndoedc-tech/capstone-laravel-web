<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Harviana - Complete Your Profile</title>
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .onboarding-select {
                appearance: none;
                -webkit-appearance: none;
                -moz-appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E");
                background-position: right 0.75rem center;
                background-repeat: no-repeat;
                background-size: 1.25rem 1.25rem;
                padding-right: 2.5rem;
            }

            .onboarding-card {
                animation: cardEntry 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
            }

            .onboarding-icon {
                animation: iconPulse 2s ease-in-out infinite;
            }

            @keyframes cardEntry {
                from {
                    opacity: 0;
                    transform: translateY(16px) scale(0.98);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            @keyframes iconPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.06); }
            }

            .step-badge {
                animation: badgeFadeIn 0.6s 0.2s cubic-bezier(0.16, 1, 0.3, 1) both;
            }

            @keyframes badgeFadeIn {
                from { opacity: 0; transform: translateY(-6px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body class="min-h-screen bg-gradient-to-br from-lime-50 via-white to-emerald-50 font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full max-w-md onboarding-card">
                {{-- Step badge --}}
                <div class="flex justify-center mb-4 step-badge">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3.5 py-1.5 text-xs font-semibold text-green-700 ring-1 ring-green-200/60">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        One last step
                    </span>
                </div>

                {{-- Card --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xl sm:p-8">
                    <div class="mb-6 text-center">
                        <div class="onboarding-icon mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-600">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">Complete Your Profile</h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Welcome, <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>! Tell us about your location and cooperative so we can personalise your experience.
                        </p>
                    </div>

                    @if (session('error'))
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-5" id="onboardingForm">
                        @csrf

                        {{-- Municipality --}}
                        <div>
                            <label for="municipality" class="block text-sm font-medium text-gray-700 mb-1">Municipality</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <select
                                    id="municipality"
                                    name="municipality"
                                    required
                                    class="onboarding-select block w-full pl-10 rounded-xl border-gray-200 bg-gray-50 focus:border-green-500 focus:bg-white focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm py-2.5 sm:py-3 transition-colors"
                                >
                                    <option value="" disabled {{ old('municipality') ? '' : 'selected' }}>Select your municipality</option>
                                    @foreach ($municipalities as $municipality)
                                        <option value="{{ $municipality }}" {{ old('municipality') === $municipality ? 'selected' : '' }}>
                                            {{ $municipality }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input-error :messages="$errors->get('municipality')" class="mt-1.5" />
                        </div>

                        {{-- Cooperative --}}
                        <div>
                            <label for="cooperative" class="block text-sm font-medium text-gray-700 mb-1">Cooperative</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <select
                                    id="cooperative"
                                    name="cooperative"
                                    required
                                    class="onboarding-select block w-full pl-10 rounded-xl border-gray-200 bg-gray-50 focus:border-green-500 focus:bg-white focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm py-2.5 sm:py-3 transition-colors"
                                >
                                    <option value="" disabled {{ old('cooperative') ? '' : 'selected' }}>Select your cooperative</option>
                                    @foreach ($cooperatives as $cooperative)
                                        <option value="{{ $cooperative }}" {{ old('cooperative') === $cooperative ? 'selected' : '' }}>
                                            {{ $cooperative }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input-error :messages="$errors->get('cooperative')" class="mt-1.5" />
                        </div>

                        {{-- Submit --}}
                        <button
                            type="submit"
                            id="onboardingSubmit"
                            class="w-full rounded-full bg-lime-400 px-6 py-3 text-sm font-semibold text-gray-900 transition hover:-translate-y-0.5 hover:bg-lime-500 hover:shadow-lg hover:shadow-lime-300/40 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none"
                        >
                            Complete Profile & Continue
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-full border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('onboardingForm');
                const btn = document.getElementById('onboardingSubmit');
                const municipality = document.getElementById('municipality');
                const cooperative = document.getElementById('cooperative');

                function updateButton() {
                    btn.disabled = !municipality.value || !cooperative.value;
                }

                municipality.addEventListener('change', updateButton);
                cooperative.addEventListener('change', updateButton);
                updateButton();
            });
        </script>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>GeoMap - Change Password</title>
        <link rel="icon" type="image/png" href="{{ asset('images/GeoMapLogo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/GeoMapLogo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-lime-50 via-white to-emerald-50 font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-xl sm:p-8">
                <div class="mb-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11V7m0 8h.01M5.455 18.545A9 9 0 1118.545 5.455 9 9 0 015.455 18.545z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Change Password Required</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        An administrator reset your password. Set a new one now before continuing to the app.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.change-required.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="password" :value="__('New Password')" class="text-sm font-medium text-gray-700" />
                        <x-text-input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block w-full rounded-xl border-gray-200 bg-gray-50 focus:border-green-500 focus:bg-white focus:ring focus:ring-green-200 focus:ring-opacity-50"
                            required
                            autocomplete="new-password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-sm font-medium text-gray-700" />
                        <x-text-input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="mt-1 block w-full rounded-xl border-gray-200 bg-gray-50 focus:border-green-500 focus:bg-white focus:ring focus:ring-green-200 focus:ring-opacity-50"
                            required
                            autocomplete="new-password"
                        />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <button type="submit" class="w-full rounded-full bg-lime-400 px-6 py-3 text-sm font-semibold text-gray-900 transition hover:-translate-y-0.5 hover:bg-lime-500 hover:shadow-lg hover:shadow-lime-300/40">
                        Save New Password
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
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $routeName = request()->route()?->getName() ?? '';

            $pageTitle = match ($routeName) {
                'dashboard' => 'Dashboard',
                'map.index' => 'Interactive Map',
                'predictions.index', 'predictions.predict.form' => 'Predictions',
                'predictions.history' => 'Prediction History',
                'profile.edit' => 'Profile',
                'farmer.calendar.page' => 'My Calendar',
                default => 'Dashboard',
            };
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Harviana - {{ $pageTitle }}</title>
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex" style="background-color: #F7F8F0;">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="flex-1 lg:ml-64 overflow-y-auto pt-14 lg:pt-0">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>

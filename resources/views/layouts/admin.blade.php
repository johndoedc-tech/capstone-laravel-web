<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $routeName = request()->route()?->getName() ?? '';

            $pageTitle = match ($routeName) {
                'admin.dashboard' => 'Admin Dashboard',
                'admin.activities.index' => 'Admin Activity',
                'admin.crop-data.index' => 'Crop Data',
                'admin.predictions.index', 'admin.predictions.predict.form' => 'Predictions',
                'admin.predictions.history' => 'Prediction History',
                'admin.map.index' => 'Interactive Map',
                'admin.users.index' => 'Users',
                'admin.reports.index' => 'Reports',
                'admin.reports.production-summary' => 'Production Summary',
                'admin.reports.prediction-analytics' => 'Prediction Analytics',
                'admin.reports.comparative-analysis' => 'Comparative Analysis',
                'admin.reports.user-activity' => 'User Activity',
                'admin.settings.index' => 'Settings',
                default => 'Admin',
            };
        @endphp

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>GeoMap - {{ $pageTitle }}</title>
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
            @include('layouts.admin-navigation')

            <!-- Page Content -->
            <main class="flex-1 lg:ml-64 overflow-y-auto pt-16 lg:pt-0">
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-cream shadow-sm border-b border-primary-300/30">
                        <div class="max-w-7xl mx-auto py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </body>
</html>

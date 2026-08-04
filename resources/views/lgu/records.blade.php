<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Harviana - LGU Records</title>
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        @include('partials.pwa')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-cream font-sans antialiased text-gray-900">
        @include('layouts.lgu-header', ['activePage' => 'records'])

        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:py-8">
            <section class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">LGU dashboard</p>
                    <h1 class="mt-1 text-2xl font-bold text-gray-900">Decision Records</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ number_format($items->total()) }} approved or declined application{{ $items->total() === 1 ? '' : 's' }}</p>
                </div>
                <a href="{{ route('lgu.dashboard', ['status' => 'pending']) }}" class="inline-flex w-fit rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">
                    Validation queue
                </a>
            </section>

            <section class="mb-5 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('lgu.records') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_180px_200px_auto]">
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search farmer, crop, title..." class="rounded-xl border-gray-300 text-sm focus:border-primary focus:ring-primary">
                    <select name="type" class="rounded-xl border-gray-300 text-sm focus:border-primary focus:ring-primary">
                        <option value="all" @selected($type === 'all')>All reports</option>
                        <option value="damage" @selected($type === 'damage')>Damage reports</option>
                        <option value="harvest" @selected($type === 'harvest')>Actual harvest</option>
                    </select>
                    <select name="status" class="rounded-xl border-gray-300 text-sm focus:border-primary focus:ring-primary">
                        <option value="all" @selected($status === 'all')>All decisions</option>
                        <option value="approved" @selected($status === 'approved')>Approved</option>
                        <option value="rejected" @selected($status === 'rejected')>Declined / needs correction</option>
                    </select>
                    <button type="submit" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">
                        Filter
                    </button>
                </form>
            </section>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left">
                        <thead class="bg-gray-50">
                            <tr class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <th scope="col" class="px-4 py-3 sm:px-5">Application</th>
                                <th scope="col" class="px-4 py-3">Farmer</th>
                                <th scope="col" class="px-4 py-3">Details</th>
                                <th scope="col" class="px-4 py-3">Decision</th>
                                <th scope="col" class="px-4 py-3 sm:px-5">LGU record</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse($items as $item)
                                @php
                                    $isDamage = $item->category === 'damage_report';
                                    $isApproved = $item->lgu_validation_status === 'approved';
                                @endphp
                                <tr class="align-top hover:bg-gray-50">
                                    <td class="min-w-64 px-4 py-4 sm:px-5">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $isDamage ? 'bg-red-50 text-red-700' : 'bg-sky-50 text-sky-700' }}">
                                                {{ $isDamage ? 'Damage report' : 'Actual harvest' }}
                                            </span>
                                        </div>
                                        <p class="mt-2 font-semibold text-gray-900">{{ $item->title }}</p>
                                        @if($item->description)
                                            <p class="mt-1 max-w-xs text-xs leading-5 text-gray-500">{{ $item->description }}</p>
                                        @endif
                                    </td>
                                    <td class="min-w-48 px-4 py-4">
                                        <p class="font-medium text-gray-900">{{ $item->user?->name ?? 'Unknown' }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $item->user?->email ?? '-' }}</p>
                                    </td>
                                    <td class="min-w-44 px-4 py-4 text-xs leading-5 text-gray-600">
                                        <p><span class="font-semibold text-gray-900">Crop:</span> {{ $item->crop ?: '-' }}</p>
                                        @if($isDamage)
                                            <p><span class="font-semibold text-gray-900">Area:</span> {{ number_format((float) $item->damage_area_sqm, 2) }} sqm</p>
                                        @else
                                            <p><span class="font-semibold text-gray-900">Harvest:</span> {{ number_format((float) $item->actual_harvest_production_mt, 4) }} mt</p>
                                        @endif
                                        <p><span class="font-semibold text-gray-900">Submitted:</span> {{ $item->submitted_to_lgu_at?->format('M d, Y') ?? '-' }}</p>
                                    </td>
                                    <td class="min-w-48 px-4 py-4">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $isApproved ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                            {{ $isApproved ? 'Approved' : 'Declined' }}
                                        </span>
                                        @if($item->lgu_validation_notes)
                                            <p class="mt-2 max-w-xs text-xs leading-5 text-gray-600">{{ $item->lgu_validation_notes }}</p>
                                        @endif
                                    </td>
                                    <td class="min-w-48 px-4 py-4 text-xs leading-5 text-gray-600 sm:px-5">
                                        <p class="font-medium text-gray-900">{{ $item->lguValidator?->name ?? 'LGU validator' }}</p>
                                        <p>{{ $item->lgu_validated_at?->format('M d, Y h:i A') ?? '-' }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">
                                        No approved or declined applications match these filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($items->hasPages())
                    <div class="border-t border-gray-100 px-4 py-3 sm:px-5">
                        {{ $items->links() }}
                    </div>
                @endif
            </section>
        </main>

        @include('layouts.page-loader')
        @include('layouts.toast')
    </body>
</html>
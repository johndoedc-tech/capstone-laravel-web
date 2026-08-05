<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Harviana - LGU Validation</title>
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
        @include('partials.pwa')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-cream font-sans antialiased text-gray-900">
        @include('layouts.lgu-header', ['activePage' => 'dashboard'])

        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:py-8">
            <section class="mb-5 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="mb-3">
                    <p class="text-sm font-semibold text-gray-900">Pending validation queue</p>
                    <p class="mt-1 text-xs text-gray-500">Completed decisions are available in Records.</p>
                </div>
                <form method="GET" action="{{ route('lgu.dashboard') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_180px_auto]">
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search farmer, crop, title..." class="rounded-xl border-gray-300 text-sm focus:border-primary focus:ring-primary">
                    <select name="type" class="rounded-xl border-gray-300 text-sm focus:border-primary focus:ring-primary">
                        <option value="all" @selected($type === 'all')>All reports</option>
                        <option value="damage" @selected($type === 'damage')>Damage reports</option>
                        <option value="harvest" @selected($type === 'harvest')>Actual harvest</option>
                    </select>
                    <button type="submit" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">
                        Filter
                    </button>
                </form>
            </section>

            <section class="space-y-4">
                @forelse($items as $item)
                    @php
                        $isDamage = $item->category === 'damage_report';
                        $authFlags = collect($item->authenticity_flags ?? [])->filter();
                        $authStatus = $item->authenticity_status ?: 'unchecked';
                        $authClass = match ($authStatus) {
                            'clear' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                            'needs_review' => 'bg-amber-50 text-amber-700 ring-amber-100',
                            default => 'bg-gray-50 text-gray-600 ring-gray-100',
                        };
                        $evidencePhotoUrl = ($item->evidence_photo_path || $item->damage_photo_path)
                            ? route('calendar.evidence-photo', $item)
                            : null;
                        $hasLocation = $item->evidence_latitude !== null && $item->evidence_longitude !== null;
                    @endphp
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $isDamage ? 'bg-red-50 text-red-700 ring-red-100' : 'bg-sky-50 text-sky-700 ring-sky-100' }}">
                                        {{ $isDamage ? 'Damage report' : 'Actual harvest' }}
                                    </span>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $authClass }}">
                                        {{ $item->authenticity_status_label ?: 'Evidence not checked' }}
                                    </span>
                                </div>
                                <h2 class="mt-3 text-lg font-semibold text-gray-900">{{ $item->title }}</h2>
                                <p class="mt-1 text-sm text-gray-600">{{ $item->description ?: 'No notes provided.' }}</p>
                                <div class="mt-3 grid grid-cols-1 gap-2 text-sm text-gray-600 sm:grid-cols-2 lg:grid-cols-4">
                                    <p><span class="font-semibold text-gray-900">Farmer:</span> {{ $item->user?->name ?? 'Unknown' }}</p>
                                    <p><span class="font-semibold text-gray-900">Crop:</span> {{ $item->crop ?: '-' }}</p>
                                    <p><span class="font-semibold text-gray-900">Date:</span> {{ $item->event_date?->format('M d, Y') }}</p>
                                    @if($isDamage)
                                        <p><span class="font-semibold text-gray-900">Area:</span> {{ number_format((float) $item->damage_area_sqm, 2) }} sqm</p>
                                    @else
                                        <p><span class="font-semibold text-gray-900">Harvest:</span> {{ number_format((float) $item->actual_harvest_production_mt, 4) }} mt</p>
                                    @endif
                                </div>
                                <div class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Evidence check</p>
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $authClass }}">
                                            {{ $item->authenticity_status_label ?: 'Evidence not checked' }}
                                        </span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-1 gap-2 text-xs text-gray-600 sm:grid-cols-3">
                                        <p><span class="font-semibold text-gray-900">Photo:</span> {{ $evidencePhotoUrl ? 'Attached' : 'Missing' }}</p>
                                        <p><span class="font-semibold text-gray-900">Location:</span> {{ $hasLocation ? 'Captured' : 'Missing' }}</p>
                                        <p><span class="font-semibold text-gray-900">Submitted:</span> {{ $item->submitted_to_lgu_at?->format('M d, Y h:i A') ?? '-' }}</p>
                                    </div>
                                    @if($hasLocation)
                                        <a href="https://www.google.com/maps?q={{ $item->evidence_latitude }},{{ $item->evidence_longitude }}" target="_blank" rel="noopener" class="mt-2 inline-flex text-xs font-semibold text-blue-700 underline">
                                            View captured location
                                            @if($item->evidence_accuracy_m)
                                                (about {{ number_format((float) $item->evidence_accuracy_m, 0) }}m accuracy)
                                            @endif
                                        </a>
                                    @endif
                                    @if($authFlags->isNotEmpty())
                                        <div class="mt-3 space-y-1">
                                            @foreach($authFlags->take(3) as $flag)
                                                <p class="rounded-lg bg-white px-2.5 py-1.5 text-xs text-gray-700">
                                                    <span class="font-semibold">{{ $flag['label'] ?? 'Needs review.' }}</span>
                                                    @if(! empty($flag['message']))
                                                        <span class="text-gray-500">{{ $flag['message'] }}</span>
                                                    @endif
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if($evidencePhotoUrl)
                                        <a href="{{ $evidencePhotoUrl }}" target="_blank" class="mt-3 inline-flex overflow-hidden rounded-xl border border-gray-200 bg-white">
                                            <img src="{{ $evidencePhotoUrl }}" alt="Evidence photo" class="h-36 w-48 object-cover">
                                        </a>
                                    @endif
                                </div>
                                @if($item->lgu_validation_notes)
                                    <p class="mt-3 rounded-xl bg-gray-50 px-3 py-2 text-sm text-gray-600">
                                        <span class="font-semibold text-gray-900">LGU note:</span> {{ $item->lgu_validation_notes }}
                                    </p>
                                @endif
                                @if($item->audits->isNotEmpty())
                                    <div class="mt-3 rounded-xl border border-gray-100 bg-white px-3 py-2">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">History</p>
                                        <div class="mt-2 space-y-1 text-xs text-gray-600">
                                            @foreach($item->audits->take(3) as $audit)
                                                <p>
                                                    <span class="font-semibold text-gray-900">{{ str_replace('_', ' ', ucfirst($audit->action)) }}</span>
                                                    by {{ $audit->user?->name ?? 'System' }}
                                                    <span class="text-gray-400">{{ $audit->created_at?->format('M d, h:i A') }}</span>
                                                </p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="w-full space-y-2 lg:w-80">
                                <form method="POST" action="{{ route('lgu.validation.approve', $item) }}" class="rounded-xl border border-emerald-100 bg-emerald-50 p-3" data-no-page-loader data-offline-operation="lgu.validation.approve" data-record-id="{{ $item->id }}" data-record-title="{{ $item->title }}" data-record-status="{{ $item->lgu_validation_status }}">
                                    @csrf
                                    <input type="hidden" name="expected_revision" value="{{ (int) $item->lgu_validation_revision }}">
                                    <label class="block text-xs font-semibold text-emerald-800">Approve note</label>
                                    <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-emerald-200 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Optional"></textarea>
                                    <button type="submit" class="mt-2 w-full rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('lgu.validation.reject', $item) }}" class="rounded-xl border border-red-100 bg-red-50 p-3" data-no-page-loader data-offline-operation="lgu.validation.reject" data-record-id="{{ $item->id }}" data-record-title="{{ $item->title }}" data-record-status="{{ $item->lgu_validation_status }}">
                                    @csrf
                                    <input type="hidden" name="expected_revision" value="{{ (int) $item->lgu_validation_revision }}">
                                    <label class="block text-xs font-semibold text-red-800">Correction note *</label>
                                    <textarea name="notes" rows="2" required class="mt-1 w-full rounded-lg border-red-200 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Tell the farmer what to correct"></textarea>
                                    <button type="submit" class="mt-2 w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                        Request correction
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                        No pending applications found for this queue.
                    </div>
                @endforelse

                {{ $items->links() }}
            </section>
        </main>

        @include('layouts.page-loader')
        @include('layouts.toast')
        @include('layouts.offline-sync')
    </body>
</html>

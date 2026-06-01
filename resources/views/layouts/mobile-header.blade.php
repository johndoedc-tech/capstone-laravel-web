@php
    $homeRoute = $homeRoute ?? route('dashboard');
    $sectionLabel = $sectionLabel ?? 'Agricultural System';
    $user = Auth::user();
@endphp

<header class="app-mobile-header lg:hidden fixed inset-x-0 top-0 z-30 bg-white shadow-sm border-b border-gray-100">
    <div class="flex h-full items-center justify-between gap-3 px-4">
        <button
            type="button"
            @click="open = true; window.dispatchEvent(new CustomEvent('harviana-sidebar-theme', { detail: { open: true } }))"
            class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl text-gray-700 transition-colors active:bg-gray-100"
            aria-label="Open navigation menu">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"></path>
            </svg>
        </button>

        <a href="{{ $homeRoute }}" class="flex min-w-0 flex-1 items-center justify-center gap-2.5">
            <span class="harviana-header-logo relative h-12 w-12 flex-shrink-0 overflow-hidden rounded-full bg-white">
                <img
                    src="{{ asset('images/HarvianaLogo.png') }}"
                    alt="Harviana logo"
                    class="absolute left-1/2 top-1/2 h-auto w-24 -translate-x-1/2 -translate-y-1/2"
                    loading="eager"
                    decoding="async"
                >
            </span>
            <span class="min-w-0">
                <span class="block truncate text-xl font-extrabold leading-none text-green-700">Harviana</span>
                <span class="mt-0.5 block truncate text-[10px] font-medium leading-tight text-gray-500">{{ $sectionLabel }}</span>
            </span>
        </a>

        <a
            href="{{ route('profile.edit') }}"
            class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-full bg-green-50 text-green-700 ring-1 ring-green-100 transition-colors active:bg-green-100"
            aria-label="Open profile">
            @if(! empty($user?->avatar))
                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
            @else
                <span class="text-base font-bold">{{ strtoupper(substr($user?->name ?? 'U', 0, 1)) }}</span>
            @endif
        </a>
    </div>
</header>

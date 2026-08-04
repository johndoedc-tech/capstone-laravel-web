@php
    $activePage = $activePage ?? 'dashboard';
    $municipality = $validator?->lgu_municipality
        ? ucwords(strtolower($validator->lgu_municipality))
        : 'Municipality unassigned';
    $validatorInitial = strtoupper(substr($validator?->name ?? 'L', 0, 1));
@endphp

<header class="sticky top-0 z-30 border-b border-primary-800 bg-primary-dark text-white shadow-[0_8px_24px_rgba(31,51,64,0.16)]">
    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-3 px-4 py-3 sm:px-6 lg:flex-nowrap lg:py-4">
        <a href="{{ route('lgu.dashboard') }}" class="flex min-w-0 flex-1 items-center gap-3 lg:flex-none">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-cream p-1.5 shadow-sm">
                <img src="{{ asset('images/HarvianaLogo.png') }}" alt="Harviana" class="h-full w-full object-contain">
            </span>
            <span class="min-w-0">
                <span class="block text-[11px] font-semibold uppercase tracking-wide text-sage-200">LGU workspace</span>
                <span class="block truncate text-base font-bold leading-tight text-white">Harviana Validation</span>
            </span>
        </a>

        <nav class="order-3 flex w-full items-center gap-1 rounded-xl border border-white/15 bg-primary-800/70 p-1 sm:order-2 sm:w-auto lg:order-none lg:mx-auto" aria-label="LGU dashboard">
            <a href="{{ route('lgu.dashboard') }}" @if($activePage === 'dashboard') aria-current="page" @endif class="flex-1 rounded-lg px-3 py-2 text-center text-xs font-semibold transition sm:flex-none {{ $activePage === 'dashboard' ? 'bg-cream text-primary-dark shadow-sm' : 'text-primary-100 hover:bg-white/10 hover:text-white' }}">
                Validation queue
            </a>
            <a href="{{ route('lgu.records') }}" @if($activePage === 'records') aria-current="page" @endif class="flex-1 rounded-lg px-3 py-2 text-center text-xs font-semibold transition sm:flex-none {{ $activePage === 'records' ? 'bg-cream text-primary-dark shadow-sm' : 'text-primary-100 hover:bg-white/10 hover:text-white' }}">
                Records
            </a>
        </nav>

        <div class="order-2 ml-auto flex items-center gap-2 sm:order-3 lg:ml-0">
            <div class="flex min-w-0 items-center gap-2 rounded-xl border border-white/15 bg-white/10 py-1.5 pl-1.5 pr-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sage-100 text-xs font-bold text-sage-700">
                    {{ $validatorInitial }}
                </span>
                <span class="hidden min-w-0 lg:block">
                    <span class="block max-w-36 truncate text-xs font-semibold text-white">{{ $validator->name }}</span>
                    <span class="block max-w-36 truncate text-[11px] text-primary-100">{{ $municipality }}</span>
                </span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-xl border border-white/20 px-3 py-2 text-xs font-semibold text-white transition hover:border-white/40 hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-sage-200 focus:ring-offset-2 focus:ring-offset-primary-dark">
                    Log out
                </button>
            </form>
        </div>
    </div>
</header>
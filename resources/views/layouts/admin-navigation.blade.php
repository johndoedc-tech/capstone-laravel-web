<!-- Admin Sidebar Navigation -->
<div x-data="{ open: false }">
<!-- Sidebar -->
<aside class="w-64 flex flex-col h-screen fixed left-0 top-0 z-40 transform transition-transform duration-300 ease-in-out lg:translate-x-0" style="background-color: #355872;" :class="{ '-translate-x-full': !open, 'translate-x-0': open }">
    <!-- Sidebar Header -->
    <div class="p-4 lg:p-6 border-b border-primary-700/30">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-white text-base lg:text-lg leading-tight">GeoMap</h1>
                <p class="text-xs text-primary-200">Admin Panel</p>
            </div>
            <div class="bg-black/20 border border-white/20 w-9 h-9 lg:w-10 lg:h-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">
                <span class="text-white font-bold text-sm lg:text-base">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
        </a>
    </div>

    <!-- Navigation Links -->
    <nav class="sidebar-scroll flex-1 p-3 lg:p-4 space-y-1 overflow-y-auto">
        <!-- Main Section -->
        <p class="px-3 lg:px-4 pt-1 pb-2 text-[10px] lg:text-xs font-semibold uppercase tracking-wider text-primary-200/60">Main</p>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 rounded-lg text-sm lg:text-base {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white' : 'text-cream hover:bg-primary-700/50' }}">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Divider -->
        <div class="!my-3 border-t border-white/10"></div>

        <!-- Data Management Section -->
        <p class="px-3 lg:px-4 pt-1 pb-2 text-[10px] lg:text-xs font-semibold uppercase tracking-wider text-primary-200/60">Data Management</p>

        <!-- Crop Data -->
        <a href="{{ route('admin.crop-data.index') }}" class="flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 rounded-lg text-sm lg:text-base {{ request()->routeIs('admin.crop-data.*') ? 'bg-primary text-white' : 'text-cream hover:bg-primary-700/50' }}">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
            </svg>
            <span class="font-medium">Crop Data</span>
        </a>

        <!-- Predictions -->
        <a href="{{ route('admin.predictions.index') }}" class="flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 rounded-lg text-sm lg:text-base {{ request()->routeIs('admin.predictions.*') ? 'bg-primary text-white' : 'text-cream hover:bg-primary-700/50' }}">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span class="font-medium">Predictions</span>
        </a>

        <!-- Interactive Map -->
        <a href="{{ route('admin.map.index') }}" class="flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 rounded-lg text-sm lg:text-base {{ request()->routeIs('admin.map.*') ? 'bg-primary text-white' : 'text-cream hover:bg-primary-700/50' }}">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
            </svg>
            <span class="font-medium">Interactive Map</span>
        </a>

        <!-- Divider -->
        <div class="!my-3 border-t border-white/10"></div>

        <!-- User Management Section -->
        <p class="px-3 lg:px-4 pt-1 pb-2 text-[10px] lg:text-xs font-semibold uppercase tracking-wider text-primary-200/60">User Management</p>

        <!-- Users -->
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 rounded-lg text-sm lg:text-base {{ request()->routeIs('admin.users.*') ? 'bg-primary text-white' : 'text-cream hover:bg-primary-700/50' }}">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <span class="font-medium">Users</span>
        </a>

        <!-- Divider -->
        <div class="!my-3 border-t border-white/10"></div>

        <!-- Reports Section -->
        <p class="px-3 lg:px-4 pt-1 pb-2 text-[10px] lg:text-xs font-semibold uppercase tracking-wider text-primary-200/60">Reports</p>

        <!-- Reports -->
        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 rounded-lg text-sm lg:text-base {{ request()->routeIs('admin.reports.*') ? 'bg-primary text-white' : 'text-cream hover:bg-primary-700/50' }}">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="font-medium">Reports</span>
        </a>

        <!-- Divider -->
        <div class="!my-3 border-t border-white/10"></div>

        <!-- System Section -->
        <p class="px-3 lg:px-4 pt-1 pb-2 text-[10px] lg:text-xs font-semibold uppercase tracking-wider text-primary-200/60">System</p>

        <!-- Settings -->
        <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 lg:px-4 py-2.5 lg:py-3 rounded-lg text-sm lg:text-base {{ request()->routeIs('admin.settings.*') ? 'bg-primary text-white' : 'text-cream hover:bg-primary-700/50' }}">
            <svg class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="font-medium">Settings</span>
        </a>

    </nav>

    <!-- Account Actions -->
    <div class="border-t border-white/10 p-3 lg:p-4">
        <div class="space-y-1">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 lg:px-4 py-2 text-xs lg:text-sm text-cream hover:bg-primary-700/50 rounded-lg">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Settings
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 lg:px-4 py-2 text-xs lg:text-sm text-red-300 hover:bg-red-900/30 rounded-lg">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Mobile Menu Button -->
<button @click="open = !open" class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg shadow-lg border border-primary-700/30 hover:bg-primary-600 active:scale-95 transition-transform" style="background-color: #355872;">
    <svg x-show="!open" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
    <svg x-show="open" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
    </svg>
</button>

<!-- Mobile overlay -->
<div x-show="open" @click="open = false" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-30"></div>
</div>

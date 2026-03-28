<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
            {{ __('Farmer Forum') }}
        </h2>
    </x-slot>

    <div class="py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 lg:p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-1">🌾 Farmer Community Forum</h1>
                        <p class="text-sm lg:text-base text-gray-600">Share knowledge, ask questions, and connect with fellow Benguet farmers</p>
                    </div>
                    <a href="{{ route('forum.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Post
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                
                <!-- Sidebar - Categories & Filters -->
                <div class="lg:col-span-1 space-y-4">
                    
                    <!-- Categories -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">📂 Categories</h3>
                        <div class="space-y-2">
                            <a href="{{ route('forum.index') }}" class="block px-3 py-2 rounded-lg text-sm {{ !request('category') ? 'bg-green-100 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                                All Topics
                            </a>
                            @foreach($categories as $category)
                                <a href="{{ route('forum.index', ['category' => $category->slug]) }}" 
                                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ request('category') === $category->slug ? 'bg-green-100 text-green-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                                    <span>{{ $category->icon }}</span>
                                    <span>{{ $category->name }}</span>
                                    <span class="ml-auto text-xs text-gray-400">{{ $category->posts_count }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">🔍 Filters</h3>
                        <form action="{{ route('forum.index') }}" method="GET" class="space-y-3">
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif
                            
                            <!-- Search -->
                            <div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts..." 
                                       class="w-full border-gray-300 rounded-lg text-sm focus:ring-green-500 focus:border-green-500">
                            </div>

                            <!-- Crop Filter -->
                            <div>
                                <select name="crop" class="w-full border-gray-300 rounded-lg text-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="">All Crops</option>
                                    @foreach($crops as $crop)
                                        <option value="{{ $crop }}" {{ request('crop') === $crop ? 'selected' : '' }}>{{ $crop }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Municipality Filter -->
                            <div>
                                <select name="municipality" class="w-full border-gray-300 rounded-lg text-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="">All Municipalities</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality }}" {{ request('municipality') === $municipality ? 'selected' : '' }}>{{ $municipality }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                                Apply Filters
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Main Content - Posts List -->
                <div class="lg:col-span-3">
                    
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($posts->isEmpty())
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                            <div class="text-6xl mb-4">🌱</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No posts yet</h3>
                            <p class="text-gray-500 mb-4">Be the first to start a discussion!</p>
                            <a href="{{ route('forum.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                Create First Post
                            </a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($posts as $post)
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                                    <div class="flex gap-4">
                                        <!-- Vote Score -->
                                        <div class="flex flex-col items-center text-gray-400">
                                            <button class="hover:text-green-600 p-1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            </button>
                                            <span class="text-sm font-medium {{ $post->vote_score > 0 ? 'text-green-600' : ($post->vote_score < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                                {{ $post->vote_score }}
                                            </span>
                                            <button class="hover:text-red-600 p-1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Post Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                                        @if($post->is_pinned)
                                                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded font-medium">📌 Pinned</span>
                                                        @endif
                                                        @if($post->is_solved)
                                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">✓ Solved</span>
                                                        @endif
                                                        <span class="text-xs {{ $post->category->color_class }} px-2 py-0.5 rounded border">
                                                            {{ $post->category->icon }} {{ $post->category->name }}
                                                        </span>
                                                    </div>
                                                    <a href="{{ route('forum.show', $post->slug) }}" class="text-lg font-semibold text-gray-900 hover:text-green-600 block">
                                                        {{ $post->title }}
                                                    </a>
                                                </div>
                                            </div>

                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $post->excerpt }}</p>

                                            <div class="flex items-center gap-4 mt-3 text-xs text-gray-500">
                                                <div class="flex items-center gap-1">
                                                    <span class="w-6 h-6 bg-green-100 text-green-700 rounded-full flex items-center justify-center font-medium">
                                                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                                    </span>
                                                    <span>{{ $post->user->name }}</span>
                                                </div>
                                                <span>{{ $post->time_ago }}</span>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                    {{ $post->comments_count }} replies
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    {{ $post->views_count }} views
                                                </span>
                                                @if($post->crop)
                                                    <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded">🌿 {{ $post->crop }}</span>
                                                @endif
                                                @if($post->municipality)
                                                    <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded">📍 {{ $post->municipality }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $posts->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

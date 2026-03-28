<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
            {{ __('Create New Post') }}
        </h2>
    </x-slot>

    <div class="py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            
            <!-- Back Link -->
            <a href="{{ route('forum.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Forum
            </a>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">✍️ Create New Post</h1>

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('forum.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                               placeholder="What's your question or topic?">
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" id="category_id" required
                                class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                            <option value="">Select a category...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->icon }} {{ $category->name }} - {{ $category->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                        <textarea name="content" id="content" rows="8" required
                                  class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                                  placeholder="Describe your question or share your knowledge in detail...">{{ old('content') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Minimum 10 characters</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Related Crop -->
                        <div>
                            <label for="crop" class="block text-sm font-medium text-gray-700 mb-1">Related Crop (optional)</label>
                            <select name="crop" id="crop"
                                    class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                                <option value="">Select if related to a crop...</option>
                                @foreach($crops as $crop)
                                    <option value="{{ $crop }}" {{ old('crop') === $crop ? 'selected' : '' }}>{{ $crop }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Municipality -->
                        <div>
                            <label for="municipality" class="block text-sm font-medium text-gray-700 mb-1">Municipality (optional)</label>
                            <select name="municipality" id="municipality"
                                    class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500">
                                <option value="">Select if location-specific...</option>
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality }}" {{ old('municipality') === $municipality ? 'selected' : '' }}>{{ $municipality }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center gap-4 pt-4 border-t">
                        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            Publish Post
                        </button>
                        <a href="{{ route('forum.index') }}" class="px-6 py-2 text-gray-600 hover:text-gray-900">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tips -->
            <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                <h3 class="font-medium text-amber-800 mb-2">💡 Tips for a great post:</h3>
                <ul class="text-sm text-amber-700 space-y-1">
                    <li>• Be specific and clear in your title</li>
                    <li>• Provide enough detail for others to understand your situation</li>
                    <li>• Add related crop and municipality to help others with similar conditions find your post</li>
                    <li>• Be respectful and helpful to fellow farmers</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>

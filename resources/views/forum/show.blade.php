<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg lg:text-xl text-gray-800 leading-tight">
            {{ $post->title }}
        </h2>
    </x-slot>

    <div class="py-4 lg:py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto" x-data="forumPost()">
            
            <!-- Back Link -->
            <a href="{{ route('forum.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Forum
            </a>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Post -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex gap-4">
                    <!-- Voting -->
                    <div class="flex flex-col items-center">
                        <button @click="vote('post', {{ $post->id }}, 1)" 
                                class="p-2 rounded hover:bg-gray-100 transition-colors"
                                :class="userVotes.post_{{ $post->id }} === 1 ? 'text-green-600' : 'text-gray-400 hover:text-green-600'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        </button>
                        <span class="text-lg font-bold" 
                              :class="scores.post_{{ $post->id }} > 0 ? 'text-green-600' : (scores.post_{{ $post->id }} < 0 ? 'text-red-600' : 'text-gray-400')"
                              x-text="scores.post_{{ $post->id }}">{{ $post->vote_score }}</span>
                        <button @click="vote('post', {{ $post->id }}, -1)"
                                class="p-2 rounded hover:bg-gray-100 transition-colors"
                                :class="userVotes.post_{{ $post->id }} === -1 ? 'text-red-600' : 'text-gray-400 hover:text-red-600'">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            @if($post->is_pinned)
                                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded font-medium">📌 Pinned</span>
                            @endif
                            @if($post->is_solved)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">✓ Solved</span>
                            @endif
                            <span class="text-xs {{ $post->category->color_class }} px-2 py-0.5 rounded border">
                                {{ $post->category->icon }} {{ $post->category->name }}
                            </span>
                            @if($post->crop)
                                <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded">🌿 {{ $post->crop }}</span>
                            @endif
                            @if($post->municipality)
                                <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded">📍 {{ $post->municipality }}</span>
                            @endif
                        </div>

                        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>

                        <div class="prose prose-green max-w-none text-gray-700 mb-4">
                            {!! nl2br(e($post->content)) !!}
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t">
                            <div class="flex items-center gap-3 text-sm text-gray-500">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 bg-green-100 text-green-700 rounded-full flex items-center justify-center font-medium">
                                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $post->user->name }}</span>
                                        <span class="text-gray-400 mx-1">•</span>
                                        <span>{{ $post->created_at->format('M d, Y \a\t g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <span>{{ $post->views_count }} views</span>
                                @if($post->user_id === auth()->id())
                                    <form action="{{ route('forum.destroy', $post->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this post?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    💬 {{ $post->comments->count() }} {{ Str::plural('Reply', $post->comments->count()) }}
                </h2>

                <!-- Add Comment Form -->
                <form action="{{ route('forum.comment.store', $post->id) }}" method="POST" class="mb-6">
                    @csrf
                    <textarea name="content" rows="3" required
                              class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                              placeholder="Share your answer or thoughts..."></textarea>
                    <div class="flex justify-end mt-2">
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            Post Reply
                        </button>
                    </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-4">
                    @forelse($post->rootComments as $comment)
                        <div class="border-l-4 {{ $comment->is_best_answer ? 'border-green-500 bg-green-50' : 'border-gray-200' }} pl-4 py-3 rounded-r-lg">
                            <div class="flex gap-3">
                                <!-- Comment Voting -->
                                <div class="flex flex-col items-center">
                                    <button @click="vote('comment', {{ $comment->id }}, 1)" 
                                            class="p-1 rounded hover:bg-gray-100 transition-colors"
                                            :class="userVotes.comment_{{ $comment->id }} === 1 ? 'text-green-600' : 'text-gray-400 hover:text-green-600'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-medium" 
                                          :class="scores.comment_{{ $comment->id }} > 0 ? 'text-green-600' : (scores.comment_{{ $comment->id }} < 0 ? 'text-red-600' : 'text-gray-400')"
                                          x-text="scores.comment_{{ $comment->id }}">{{ $comment->vote_score }}</span>
                                    <button @click="vote('comment', {{ $comment->id }}, -1)"
                                            class="p-1 rounded hover:bg-gray-100 transition-colors"
                                            :class="userVotes.comment_{{ $comment->id }} === -1 ? 'text-red-600' : 'text-gray-400 hover:text-red-600'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex-1">
                                    @if($comment->is_best_answer)
                                        <div class="text-xs text-green-700 font-medium mb-1">✓ Best Answer</div>
                                    @endif
                                    
                                    <div class="text-gray-700">{!! nl2br(e($comment->content)) !!}</div>
                                    
                                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                        <div class="flex items-center gap-1">
                                            <span class="w-5 h-5 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center font-medium text-xs">
                                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                            </span>
                                            <span>{{ $comment->user->name }}</span>
                                        </div>
                                        <span>{{ $comment->time_ago }}</span>
                                        
                                        @if($post->user_id === auth()->id() && !$comment->is_best_answer)
                                            <button @click="markBestAnswer({{ $comment->id }})" class="text-green-600 hover:text-green-700 font-medium">
                                                Mark as Best Answer
                                            </button>
                                        @endif
                                        
                                        @if($comment->user_id === auth()->id())
                                            <form action="{{ route('forum.comment.destroy', $comment->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this reply?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                                            </form>
                                        @endif
                                    </div>

                                    <!-- Nested Replies -->
                                    @if($comment->replies->count() > 0)
                                        <div class="mt-3 ml-4 space-y-3">
                                            @foreach($comment->replies as $reply)
                                                <div class="border-l-2 border-gray-200 pl-3 py-2">
                                                    <div class="text-gray-700 text-sm">{!! nl2br(e($reply->content)) !!}</div>
                                                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-500">
                                                        <span>{{ $reply->user->name }}</span>
                                                        <span>{{ $reply->time_ago }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-2">💭</div>
                            <p>No replies yet. Be the first to respond!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        function forumPost() {
            return {
                scores: {
                    post_{{ $post->id }}: {{ $post->vote_score }},
                    @foreach($post->rootComments as $comment)
                    comment_{{ $comment->id }}: {{ $comment->vote_score }},
                    @endforeach
                },
                userVotes: {
                    post_{{ $post->id }}: {{ $post->getUserVote(auth()->id()) }},
                    @foreach($post->rootComments as $comment)
                    comment_{{ $comment->id }}: {{ $comment->getUserVote(auth()->id()) }},
                    @endforeach
                },

                async vote(type, id, value) {
                    try {
                        const response = await fetch('{{ route("forum.vote") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                voteable_type: type,
                                voteable_id: id,
                                vote: value
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.scores[type + '_' + id] = data.score;
                            this.userVotes[type + '_' + id] = data.userVote;
                        }
                    } catch (error) {
                        console.error('Vote failed:', error);
                    }
                },

                async markBestAnswer(commentId) {
                    try {
                        const response = await fetch(`{{ url('forum/best-answer') }}/${commentId}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            location.reload();
                        }
                    } catch (error) {
                        console.error('Failed to mark best answer:', error);
                    }
                }
            }
        }
    </script>
</x-app-layout>

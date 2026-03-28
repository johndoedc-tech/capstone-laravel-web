<?php

namespace App\Http\Controllers;

use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    /**
     * Display the forum index with categories and recent posts.
     */
    public function index(Request $request)
    {
        $categories = ForumCategory::orderBy('order')->get();
        
        $query = ForumPost::with(['user', 'category'])
            ->withCount('comments')
            ->pinnedFirst()
            ->latest();

        // Filter by category
        if ($request->category) {
            $category = ForumCategory::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Filter by crop
        if ($request->crop) {
            $query->forCrop($request->crop);
        }

        // Filter by municipality
        if ($request->municipality) {
            $query->forMunicipality($request->municipality);
        }

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(15);

        // Get crops and municipalities for filters
        $crops = ['Cabbage', 'Potato', 'Carrot', 'Lettuce', 'Broccoli', 'Cauliflower', 'Chinese Cabbage', 'Bell Pepper', 'Tomato', 'Beans'];
        $municipalities = ['Atok', 'Bakun', 'Bokod', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'];

        return view('forum.index', compact('categories', 'posts', 'crops', 'municipalities'));
    }

    /**
     * Show form to create a new post.
     */
    public function create()
    {
        $categories = ForumCategory::orderBy('order')->get();
        $crops = ['Cabbage', 'Potato', 'Carrot', 'Lettuce', 'Broccoli', 'Cauliflower', 'Chinese Cabbage', 'Bell Pepper', 'Tomato', 'Beans'];
        $municipalities = ['Atok', 'Bakun', 'Bokod', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay'];

        return view('forum.create', compact('categories', 'crops', 'municipalities'));
    }

    /**
     * Store a new forum post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:forum_categories,id',
            'content' => 'required|string|min:10',
            'crop' => 'nullable|string|max:100',
            'municipality' => 'nullable|string|max:100',
        ]);

        $post = ForumPost::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'crop' => $validated['crop'] ?? null,
            'municipality' => $validated['municipality'] ?? null,
        ]);

        return redirect()->route('forum.show', $post->slug)
            ->with('success', 'Your post has been published!');
    }

    /**
     * Display a single post with comments.
     */
    public function show($slug)
    {
        $post = ForumPost::with(['user', 'category', 'rootComments.user', 'rootComments.replies.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views
        $post->incrementViews();

        return view('forum.show', compact('post'));
    }

    /**
     * Store a comment on a post.
     */
    public function storeComment(Request $request, $postId)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:2',
            'parent_id' => 'nullable|exists:forum_comments,id',
        ]);

        $post = ForumPost::findOrFail($postId);

        ForumComment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Your reply has been posted!');
    }

    /**
     * Vote on a post or comment.
     */
    public function vote(Request $request)
    {
        $validated = $request->validate([
            'voteable_type' => 'required|in:post,comment',
            'voteable_id' => 'required|integer',
            'vote' => 'required|in:1,-1',
        ]);

        $type = $validated['voteable_type'] === 'post' ? ForumPost::class : ForumComment::class;
        $voteable = $type::findOrFail($validated['voteable_id']);

        // Check if user already voted
        $existingVote = ForumVote::where('user_id', Auth::id())
            ->where('voteable_type', $type)
            ->where('voteable_id', $voteable->id)
            ->first();

        if ($existingVote) {
            if ($existingVote->vote == $validated['vote']) {
                // Same vote, remove it
                $existingVote->delete();
                $newVote = 0;
            } else {
                // Different vote, update it
                $existingVote->update(['vote' => $validated['vote']]);
                $newVote = $validated['vote'];
            }
        } else {
            // Create new vote
            ForumVote::create([
                'user_id' => Auth::id(),
                'voteable_type' => $type,
                'voteable_id' => $voteable->id,
                'vote' => $validated['vote'],
            ]);
            $newVote = $validated['vote'];
        }

        // Get new score
        $newScore = $voteable->votes()->sum('vote');

        return response()->json([
            'success' => true,
            'score' => $newScore,
            'userVote' => $newVote,
        ]);
    }

    /**
     * Mark a comment as best answer.
     */
    public function markBestAnswer(Request $request, $commentId)
    {
        $comment = ForumComment::with('post')->findOrFail($commentId);
        
        // Only post author can mark best answer
        if ($comment->post->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Remove existing best answer
        ForumComment::where('post_id', $comment->post_id)
            ->where('is_best_answer', true)
            ->update(['is_best_answer' => false]);

        // Mark this one as best
        $comment->update(['is_best_answer' => true]);

        // Mark post as solved
        $comment->post->update(['is_solved' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a post.
     */
    public function destroy($id)
    {
        $post = ForumPost::findOrFail($id);

        // Only author can delete
        if ($post->user_id !== Auth::id()) {
            return back()->with('error', 'You can only delete your own posts.');
        }

        $post->delete();

        return redirect()->route('forum.index')->with('success', 'Post deleted successfully.');
    }

    /**
     * Delete a comment.
     */
    public function destroyComment($id)
    {
        $comment = ForumComment::findOrFail($id);

        // Only author can delete
        if ($comment->user_id !== Auth::id()) {
            return back()->with('error', 'You can only delete your own comments.');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }
}

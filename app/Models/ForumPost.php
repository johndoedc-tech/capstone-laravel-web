<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ForumPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'content',
        'crop',
        'municipality',
        'is_pinned',
        'is_solved',
        'views_count',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_solved' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title) . '-' . Str::random(6);
            }
        });
    }

    /**
     * Get the user who created the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the post.
     */
    public function category()
    {
        return $this->belongsTo(ForumCategory::class, 'category_id');
    }

    /**
     * Get the comments on the post.
     */
    public function comments()
    {
        return $this->hasMany(ForumComment::class, 'post_id');
    }

    /**
     * Get root level comments only.
     */
    public function rootComments()
    {
        return $this->hasMany(ForumComment::class, 'post_id')->whereNull('parent_id');
    }

    /**
     * Get the votes on the post.
     */
    public function votes()
    {
        return $this->morphMany(ForumVote::class, 'voteable');
    }

    /**
     * Get the vote score.
     */
    public function getVoteScoreAttribute()
    {
        return $this->votes()->sum('vote');
    }

    /**
     * Get comments count.
     */
    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * Check if user has voted.
     */
    public function hasVotedBy($userId)
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }

    /**
     * Get user's vote.
     */
    public function getUserVote($userId)
    {
        $vote = $this->votes()->where('user_id', $userId)->first();
        return $vote ? $vote->vote : 0;
    }

    /**
     * Increment views.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Scope for pinned posts first.
     */
    public function scopePinnedFirst($query)
    {
        return $query->orderByDesc('is_pinned');
    }

    /**
     * Scope for filtering by crop.
     */
    public function scopeForCrop($query, $crop)
    {
        if ($crop) {
            return $query->where('crop', $crop);
        }
        return $query;
    }

    /**
     * Scope for filtering by municipality.
     */
    public function scopeForMunicipality($query, $municipality)
    {
        if ($municipality) {
            return $query->where('municipality', $municipality);
        }
        return $query;
    }

    /**
     * Get short excerpt of content.
     */
    public function getExcerptAttribute()
    {
        return Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Get time ago format.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}

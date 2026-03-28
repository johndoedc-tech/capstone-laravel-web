<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'is_best_answer',
    ];

    protected $casts = [
        'is_best_answer' => 'boolean',
    ];

    /**
     * Get the post this comment belongs to.
     */
    public function post()
    {
        return $this->belongsTo(ForumPost::class, 'post_id');
    }

    /**
     * Get the user who wrote the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for nested replies).
     */
    public function parent()
    {
        return $this->belongsTo(ForumComment::class, 'parent_id');
    }

    /**
     * Get replies to this comment.
     */
    public function replies()
    {
        return $this->hasMany(ForumComment::class, 'parent_id');
    }

    /**
     * Get the votes on the comment.
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
     * Get time ago format.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}

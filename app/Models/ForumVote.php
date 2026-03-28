<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voteable_id',
        'voteable_type',
        'vote',
    ];

    /**
     * Get the user who voted.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the voteable model (post or comment).
     */
    public function voteable()
    {
        return $this->morphTo();
    }
}

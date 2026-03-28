<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'order',
    ];

    /**
     * Get the posts in this category.
     */
    public function posts()
    {
        return $this->hasMany(ForumPost::class, 'category_id');
    }

    /**
     * Get posts count.
     */
    public function getPostsCountAttribute()
    {
        return $this->posts()->count();
    }

    /**
     * Get the color class for Tailwind.
     */
    public function getColorClassAttribute()
    {
        $colors = [
            'red' => 'bg-red-100 text-red-700 border-red-200',
            'green' => 'bg-green-100 text-green-700 border-green-200',
            'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            'blue' => 'bg-blue-100 text-blue-700 border-blue-200',
            'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'gray' => 'bg-gray-100 text-gray-700 border-gray-200',
            'purple' => 'bg-purple-100 text-purple-700 border-purple-200',
            'orange' => 'bg-orange-100 text-orange-700 border-orange-200',
        ];

        return $colors[$this->color] ?? $colors['gray'];
    }
}

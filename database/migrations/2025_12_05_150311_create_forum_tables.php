<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Forum Categories
        Schema::create('forum_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('icon')->default('💬');
            $table->string('color')->default('gray');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Forum Posts
        Schema::create('forum_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('forum_categories')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('crop')->nullable();
            $table->string('municipality')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_solved')->default(false);
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });

        // Forum Comments/Replies
        Schema::create('forum_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('forum_posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('forum_comments')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_best_answer')->default(false);
            $table->timestamps();
        });

        // Post/Comment Votes
        Schema::create('forum_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('voteable');
            $table->tinyInteger('vote')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'voteable_id', 'voteable_type']);
        });

        // Seed default categories
        DB::table('forum_categories')->insert([
            [
                'name' => 'Pest Control',
                'slug' => 'pest-control',
                'description' => 'Discuss pest problems, prevention, and solutions',
                'icon' => '🐛',
                'color' => 'red',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Planting Tips',
                'slug' => 'planting-tips',
                'description' => 'Share and learn planting techniques',
                'icon' => '🌱',
                'color' => 'green',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Harvest & Post-Harvest',
                'slug' => 'harvest',
                'description' => 'Harvesting timing, storage, and handling',
                'icon' => '🌾',
                'color' => 'yellow',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Weather & Climate',
                'slug' => 'weather-climate',
                'description' => 'Weather updates and climate adaptation',
                'icon' => '🌤️',
                'color' => 'blue',
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Market & Prices',
                'slug' => 'market-prices',
                'description' => 'Market information, prices, and buyers',
                'icon' => '💰',
                'color' => 'emerald',
                'order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Equipment & Tools',
                'slug' => 'equipment-tools',
                'description' => 'Farm equipment, tools, and machinery',
                'icon' => '🔧',
                'color' => 'gray',
                'order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General Discussion',
                'slug' => 'general',
                'description' => 'General farming topics and community chat',
                'icon' => '💬',
                'color' => 'purple',
                'order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_votes');
        Schema::dropIfExists('forum_comments');
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_categories');
    }
};

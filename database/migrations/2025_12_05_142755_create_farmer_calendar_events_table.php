<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('farmer_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('event_date');
            $table->enum('event_type', ['note', 'reminder'])->default('note');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // pest, harvest, planting, fertilizer, weather, other
            $table->string('crop')->nullable();
            $table->time('reminder_time')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'event_date']);
            $table->index(['event_type', 'reminder_sent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmer_calendar_events');
    }
};

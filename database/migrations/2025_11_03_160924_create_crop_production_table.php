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
        Schema::create('crop_production', function (Blueprint $table) {
            $table->id();
            $table->string('municipality', 100);
            $table->string('farm_type', 50); // IRRIGATED or RAINFED
            $table->integer('year');
            $table->string('month', 20); // JAN, FEB, etc.
            $table->string('crop', 100);
            $table->decimal('area_planted', 10, 2)->nullable();
            $table->decimal('area_harvested', 10, 2)->nullable();
            $table->decimal('production', 12, 2)->nullable(); // in metric tons
            $table->decimal('productivity', 10, 2)->nullable(); // mt/ha
            $table->timestamps();
            
            // Add indexes for faster queries
            $table->index(['municipality', 'year', 'crop']);
            $table->index('year');
            $table->index('crop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crop_production');
    }
};

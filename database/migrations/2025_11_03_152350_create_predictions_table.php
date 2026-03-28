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
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Input parameters
            $table->string('municipality');
            $table->string('farm_type');
            $table->integer('year');
            $table->integer('month');
            $table->string('crop');
            $table->decimal('area_planted_ha', 10, 2);
            $table->decimal('area_harvested_ha', 10, 2);
            $table->decimal('productivity_mt_ha', 10, 2);
            
            // Prediction results
            $table->decimal('predicted_production_mt', 10, 2);
            $table->decimal('expected_from_productivity', 10, 2);
            $table->decimal('difference', 10, 2);
            $table->decimal('confidence_score', 10, 4);
            
            // Metadata
            $table->integer('api_response_time_ms')->nullable();
            $table->string('status')->default('success'); // success, failed
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('user_id');
            $table->index('municipality');
            $table->index('crop');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};

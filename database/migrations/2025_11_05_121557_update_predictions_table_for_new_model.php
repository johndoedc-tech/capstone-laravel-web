<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update predictions table to support new 6-feature ML model
     * These fields are no longer required by the new model
     */
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            // Make old model fields nullable since new model doesn't use them
            $table->decimal('area_planted_ha', 10, 2)->nullable()->change();
            $table->decimal('area_harvested_ha', 10, 2)->nullable()->change();
            $table->decimal('productivity_mt_ha', 10, 2)->nullable()->change();
            $table->decimal('expected_from_productivity', 10, 2)->nullable()->change();
            $table->decimal('difference', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            // Revert back to NOT NULL (if rolling back)
            $table->decimal('area_planted_ha', 10, 2)->nullable(false)->change();
            $table->decimal('area_harvested_ha', 10, 2)->nullable(false)->change();
            $table->decimal('productivity_mt_ha', 10, 2)->nullable(false)->change();
            $table->decimal('expected_from_productivity', 10, 2)->nullable(false)->change();
            $table->decimal('difference', 10, 2)->nullable(false)->change();
        });
    }
};

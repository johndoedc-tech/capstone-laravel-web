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
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            $table->date('estimated_harvest_date')->nullable()->after('planting_material');
            $table->unsignedSmallInteger('estimated_harvest_days')->nullable()->after('estimated_harvest_date');
            $table->unsignedBigInteger('harvest_event_id')->nullable()->after('estimated_harvest_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            $table->dropColumn([
                'estimated_harvest_date',
                'estimated_harvest_days',
                'harvest_event_id',
            ]);
        });
    }
};

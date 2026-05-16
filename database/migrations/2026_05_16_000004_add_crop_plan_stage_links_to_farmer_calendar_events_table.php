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
            $table->unsignedBigInteger('crop_plan_event_id')->nullable()->after('harvest_event_id');
            $table->string('crop_plan_stage')->nullable()->after('crop_plan_event_id');

            $table->index('crop_plan_event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            $table->dropIndex(['crop_plan_event_id']);
            $table->dropColumn(['crop_plan_event_id', 'crop_plan_stage']);
        });
    }
};

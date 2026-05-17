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
            $table->decimal('predicted_production_mt', 12, 2)->nullable()->after('crop_plan_stage');
            $table->decimal('prediction_confidence', 8, 4)->nullable()->after('predicted_production_mt');
            $table->string('prediction_source')->nullable()->after('prediction_confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            $table->dropColumn([
                'predicted_production_mt',
                'prediction_confidence',
                'prediction_source',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            $table->date('actual_harvest_date')->nullable()->after('prediction_source');
            $table->decimal('actual_harvest_amount', 12, 2)->nullable()->after('actual_harvest_date');
            $table->string('actual_harvest_unit', 10)->nullable()->after('actual_harvest_amount');
            $table->decimal('actual_harvest_production_mt', 12, 4)->nullable()->after('actual_harvest_unit');
            $table->text('actual_harvest_notes')->nullable()->after('actual_harvest_production_mt');
            $table->timestamp('actual_harvest_recorded_at')->nullable()->after('actual_harvest_notes');
        });
    }

    public function down(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            $table->dropColumn([
                'actual_harvest_date',
                'actual_harvest_amount',
                'actual_harvest_unit',
                'actual_harvest_production_mt',
                'actual_harvest_notes',
                'actual_harvest_recorded_at',
            ]);
        });
    }
};

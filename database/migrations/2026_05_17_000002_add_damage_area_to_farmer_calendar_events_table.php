<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            $table->decimal('damage_area_sqm', 12, 2)->nullable()->after('desired_area_sqm');
        });
    }

    public function down(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            $table->dropColumn('damage_area_sqm');
        });
    }
};

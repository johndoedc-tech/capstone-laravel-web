<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'farmer'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50) USING role::text');
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'farmer'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'lgu_municipality')) {
                $table->string('lgu_municipality')->nullable()->after('favorite_crops')->index();
            }

            if (! Schema::hasColumn('users', 'lgu_barangay')) {
                $table->string('lgu_barangay')->nullable()->after('lgu_municipality')->index();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('lgu_barangay')->index();
            }
        });

        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            if (! Schema::hasColumn('farmer_calendar_events', 'lgu_validation_status')) {
                $table->string('lgu_validation_status')->default('approved')->after('is_completed')->index();
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'lgu_validated_by')) {
                $table->foreignId('lgu_validated_by')->nullable()->after('lgu_validation_status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'lgu_validated_at')) {
                $table->timestamp('lgu_validated_at')->nullable()->after('lgu_validated_by');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'lgu_validation_notes')) {
                $table->text('lgu_validation_notes')->nullable()->after('lgu_validated_at');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'lgu_validation_revision')) {
                $table->unsignedInteger('lgu_validation_revision')->default(0)->after('lgu_validation_notes');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'submitted_to_lgu_at')) {
                $table->timestamp('submitted_to_lgu_at')->nullable()->after('lgu_validation_revision');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'damage_photo_path')) {
                $table->string('damage_photo_path')->nullable()->after('submitted_to_lgu_at');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'damage_photo_original_name')) {
                $table->string('damage_photo_original_name')->nullable()->after('damage_photo_path');
            }
        });

        DB::table('farmer_calendar_events')
            ->whereNull('lgu_validation_status')
            ->update(['lgu_validation_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            foreach ([
                'damage_photo_original_name',
                'damage_photo_path',
                'submitted_to_lgu_at',
                'lgu_validation_revision',
                'lgu_validation_notes',
                'lgu_validated_at',
            ] as $column) {
                if (Schema::hasColumn('farmer_calendar_events', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('farmer_calendar_events', 'lgu_validated_by')) {
                $table->dropConstrainedForeignId('lgu_validated_by');
            }

            if (Schema::hasColumn('farmer_calendar_events', 'lgu_validation_status')) {
                $table->dropColumn('lgu_validation_status');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['is_active', 'lgu_barangay', 'lgu_municipality'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

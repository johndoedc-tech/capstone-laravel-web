<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            if (! Schema::hasColumn('farmer_calendar_events', 'evidence_photo_path')) {
                $table->string('evidence_photo_path')->nullable()->after('damage_photo_original_name');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'evidence_photo_original_name')) {
                $table->string('evidence_photo_original_name')->nullable()->after('evidence_photo_path');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'evidence_photo_hash')) {
                $table->string('evidence_photo_hash', 64)->nullable()->after('evidence_photo_original_name')->index();
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'evidence_latitude')) {
                $table->decimal('evidence_latitude', 10, 7)->nullable()->after('evidence_photo_hash');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'evidence_longitude')) {
                $table->decimal('evidence_longitude', 10, 7)->nullable()->after('evidence_latitude');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'evidence_accuracy_m')) {
                $table->decimal('evidence_accuracy_m', 10, 2)->nullable()->after('evidence_longitude');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'evidence_captured_at')) {
                $table->timestamp('evidence_captured_at')->nullable()->after('evidence_accuracy_m');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'evidence_user_agent')) {
                $table->string('evidence_user_agent', 500)->nullable()->after('evidence_captured_at');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'authenticity_status')) {
                $table->string('authenticity_status')->default('unchecked')->after('evidence_user_agent')->index();
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'authenticity_flags')) {
                $table->json('authenticity_flags')->nullable()->after('authenticity_status');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'authenticity_checked_at')) {
                $table->timestamp('authenticity_checked_at')->nullable()->after('authenticity_flags');
            }

            if (! Schema::hasColumn('farmer_calendar_events', 'authenticity_notes')) {
                $table->text('authenticity_notes')->nullable()->after('authenticity_checked_at');
            }
        });

        if (! Schema::hasTable('calendar_event_audits')) {
            Schema::create('calendar_event_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('farmer_calendar_event_id')->constrained('farmer_calendar_events')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['farmer_calendar_event_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_audits');

        Schema::table('farmer_calendar_events', function (Blueprint $table) {
            foreach ([
                'authenticity_notes',
                'authenticity_checked_at',
                'authenticity_flags',
                'authenticity_status',
                'evidence_user_agent',
                'evidence_captured_at',
                'evidence_accuracy_m',
                'evidence_longitude',
                'evidence_latitude',
                'evidence_photo_hash',
                'evidence_photo_original_name',
                'evidence_photo_path',
            ] as $column) {
                if (Schema::hasColumn('farmer_calendar_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

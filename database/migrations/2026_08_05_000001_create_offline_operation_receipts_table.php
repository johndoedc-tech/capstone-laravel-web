<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_operation_receipts', function (Blueprint $table) {
            $table->id();
            $table->uuid('idempotency_key')->unique();
            $table->uuid('client_operation_uuid');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('operation_type', 80);
            $table->string('status', 24)->default('processing');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->json('response_body')->nullable();
            $table->string('resource_type', 100)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'operation_type']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_operation_receipts');
    }
};

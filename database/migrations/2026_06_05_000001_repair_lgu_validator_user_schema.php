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

        if (Schema::hasColumn('users', 'role')) {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY role VARCHAR(50) NOT NULL DEFAULT 'farmer'");
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
                DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50) USING role::text');
                DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'farmer'");
            }
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'lgu_municipality')) {
                $table->string('lgu_municipality')->nullable()->index();
            }

            if (! Schema::hasColumn('users', 'lgu_barangay')) {
                $table->string('lgu_barangay')->nullable()->index();
            }

            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['is_active', 'lgu_barangay', 'lgu_municipality'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

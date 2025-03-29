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
        // Add deleted_at to maintenance_lines if it doesn't exist
        if (Schema::hasTable('maintenance_lines') && !Schema::hasColumn('maintenance_lines', 'deleted_at')) {
            Schema::table('maintenance_lines', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add deleted_at to maintenance_areas if it doesn't exist
        if (Schema::hasTable('maintenance_areas') && !Schema::hasColumn('maintenance_areas', 'deleted_at')) {
            Schema::table('maintenance_areas', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('maintenance_lines') && Schema::hasColumn('maintenance_lines', 'deleted_at')) {
            Schema::table('maintenance_lines', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('maintenance_areas') && Schema::hasColumn('maintenance_areas', 'deleted_at')) {
            Schema::table('maintenance_areas', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

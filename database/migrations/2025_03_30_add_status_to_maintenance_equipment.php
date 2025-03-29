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
        Schema::table('maintenance_equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_equipment', 'status')) {
                $table->string('status')->default('operational')
                      ->comment('Equipment status: operational, maintenance, out_of_service')
                      ->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_equipment', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_equipment', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

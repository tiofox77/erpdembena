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
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('mrp_production_schedules', 'actual_start_time')) {
                $table->dropColumn('actual_start_time');
            }
            
            if (Schema::hasColumn('mrp_production_schedules', 'actual_end_time')) {
                $table->dropColumn('actual_end_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            $table->datetime('actual_start_time')->nullable();
            $table->datetime('actual_end_time')->nullable();
        });
    }
};

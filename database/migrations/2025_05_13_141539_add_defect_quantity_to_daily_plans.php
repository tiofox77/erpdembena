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
        Schema::table('mrp_production_daily_plans', function (Blueprint $table) {
            $table->decimal('defect_quantity', 10, 3)->default(0)->before('actual_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_production_daily_plans', function (Blueprint $table) {
            $table->dropColumn('defect_quantity');
        });
    }
};

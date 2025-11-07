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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('position_subsidy', 10, 2)->default(0)->after('bonus_amount')->comment('Subsídio de Cargo');
            $table->decimal('performance_subsidy', 10, 2)->default(0)->after('position_subsidy')->comment('Subsídio de Desempenho');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['position_subsidy', 'performance_subsidy']);
        });
    }
};

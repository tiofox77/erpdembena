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
        Schema::table('overtime_records', function (Blueprint $table) {
            // Adicionando campo para o tipo de entrada (time_range, daily, monthly)
            $table->string('input_type')->default('time_range')->after('status');
            
            // Adicionando campo para o tipo de período (day, month)
            $table->string('period_type')->nullable()->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->dropColumn('input_type');
            $table->dropColumn('period_type');
        });
    }
};

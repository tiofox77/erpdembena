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
            // Horas de trabalho diárias
            $table->decimal('working_hours_per_day', 5, 2)->default(8.0)->after('location_id');
            
            // Taxa de produção por hora
            $table->decimal('hourly_production_rate', 10, 2)->default(0.0)->after('working_hours_per_day');
            
            // Dias de trabalho (JSON)
            $table->json('working_days')->nullable()->after('hourly_production_rate');
            
            // Tempos de setup e limpeza
            $table->integer('setup_time')->default(30)->after('working_days');
            $table->integer('cleanup_time')->default(15)->after('setup_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'working_hours_per_day',
                'hourly_production_rate',
                'working_days',
                'setup_time',
                'cleanup_time'
            ]);
        });
    }
};

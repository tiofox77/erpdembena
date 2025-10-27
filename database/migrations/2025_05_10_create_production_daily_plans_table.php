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
        // Adicionar novos campos à tabela de produção
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('start_date');
            $table->time('end_time')->nullable()->after('end_date');
            $table->decimal('actual_quantity', 10, 3)->default(0)->after('planned_quantity');
            $table->dateTime('actual_start_time')->nullable()->after('actual_quantity');
            $table->dateTime('actual_end_time')->nullable()->after('actual_start_time');
            $table->boolean('is_delayed')->default(false)->after('actual_end_time');
            $table->text('delay_reason')->nullable()->after('is_delayed');
        });

        // Criar tabela para planos diários de produção
        Schema::create('mrp_production_daily_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('mrp_production_schedules')->onDelete('cascade');
            $table->date('production_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('planned_quantity', 10, 3);
            $table->decimal('actual_quantity', 10, 3)->default(0);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_production_daily_plans');
        
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'start_time',
                'end_time',
                'actual_quantity',
                'actual_start_time',
                'actual_end_time',
                'is_delayed',
                'delay_reason'
            ]);
        });
    }
};

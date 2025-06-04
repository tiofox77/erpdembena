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
        Schema::create('maintenance_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained('maintenance_tasks')->nullOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('maintenance_equipment')->nullOnDelete();
            $table->foreignId('line_id')->nullable()->constrained('maintenance_lines')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('maintenance_areas')->nullOnDelete();
            $table->date('scheduled_date')->nullable();
            $table->enum('frequency_type', ['once', 'daily', 'weekly', 'monthly', 'yearly', 'custom'])->default('once');
            $table->integer('custom_days')->nullable(); // Para frequência customizada em dias
            $table->string('day_of_week')->nullable(); // Para frequência semanal (0-6)
            $table->integer('day_of_month')->nullable(); // Para frequência mensal (1-31)
            $table->integer('month_day')->nullable(); // Para frequência anual (dia do mês)
            $table->integer('month')->nullable(); // Para frequência anual (1-12)
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('type', ['preventive', 'corrective', 'predictive', 'other'])->default('preventive');
            $table->unsignedBigInteger('assigned_to')->nullable(); // Referência ao ID de usuário
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('next_maintenance_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key para assigned_to (usuário)
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_plans');
    }
};

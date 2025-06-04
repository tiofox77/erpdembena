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
        Schema::create('mrp_capacity_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique();
            $table->foreignId('work_center_id')->nullable();
            $table->string('resource_name')->comment('Máquina, equipamento ou recurso humano');
            $table->enum('resource_type', ['machine', 'equipment', 'labor', 'other'])->default('machine');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('available_capacity', 10, 2)->comment('Capacidade disponível em horas');
            $table->decimal('planned_capacity', 10, 2)->default(0)->comment('Capacidade planejada em horas');
            $table->decimal('capacity_utilization', 5, 2)->default(0)->comment('Percentual de utilização');
            $table->decimal('efficiency_factor', 5, 2)->default(100)->comment('Fator de eficiência (%)');
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
        Schema::dropIfExists('mrp_capacity_plans');
    }
};

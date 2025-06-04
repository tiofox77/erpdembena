<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceTaskLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('maintenance_task_logs')) {
            Schema::create('maintenance_task_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained('maintenance_tasks')->onDelete('cascade');
                $table->foreignId('equipment_id')->constrained('maintenance_equipment')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('maintenance_plan_id')->nullable()->constrained('maintenance_plans')->onDelete('set null');
                $table->dateTime('date_performed')->nullable();
                $table->dateTime('scheduled_date')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->integer('duration_minutes')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maintenance_task_logs');
    }
}

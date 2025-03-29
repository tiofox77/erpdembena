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
        // Verifica se a tabela existe e a remove para criar uma nova
        if (Schema::hasTable('maintenance_correctives')) {
            Schema::dropIfExists('maintenance_correctives');
        }

        // Cria a tabela com a estrutura necessária
        Schema::create('maintenance_correctives', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->integer('month');
            $table->integer('week')->nullable();
            $table->string('system_process')->nullable();
            $table->foreignId('equipment_id')->nullable()->constrained('maintenance_equipment')->nullOnDelete();
            $table->string('failure_mode')->nullable();
            $table->string('failure_mode_category')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('downtime_length')->nullable()->comment('Stored as hours with decimal places');
            $table->string('failure_cause')->nullable();
            $table->string('failure_cause_category')->nullable();
            $table->text('description')->nullable();
            $table->text('actions_taken')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_correctives');
    }
};

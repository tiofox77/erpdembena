<?php

declare(strict_types=1);

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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('training_type')->index(); // technical, soft_skills, leadership, etc.
            $table->string('training_title');
            $table->text('training_description')->nullable();
            $table->string('provider')->nullable(); // Training provider/institution
            $table->string('status')->default('planned')->index(); // planned, approved, in_progress, completed, cancelled, etc.
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('duration_hours', 8, 2)->nullable(); // Total hours
            $table->string('location')->nullable(); // Physical or online location
            $table->decimal('cost', 10, 2)->nullable(); // Training cost
            $table->boolean('budget_approved')->default(false);
            $table->string('completion_status')->default('not_started')->index(); // not_started, in_progress, completed, failed, withdrawn
            $table->date('completion_date')->nullable();
            $table->boolean('certification_received')->default(false);
            $table->date('certification_expiry_date')->nullable();
            $table->string('trainer_name')->nullable();
            $table->string('trainer_email')->nullable();
            $table->text('skills_acquired')->nullable(); // Skills gained from training
            $table->decimal('evaluation_score', 4, 2)->nullable(); // Training evaluation score (0-10)
            $table->text('feedback')->nullable(); // Employee feedback
            $table->text('next_steps')->nullable(); // Follow-up actions
            $table->json('attachments')->nullable(); // Certificates, materials, etc.
            $table->foreignId('created_by')->constrained('users'); // Who registered the training
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();

            // Indexes for better performance
            $table->index(['employee_id', 'training_type']);
            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('certification_expiry_date');
            $table->index(['status', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};

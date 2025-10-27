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
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('evaluation_type', [
                'annual', 
                'semi_annual', 
                'quarterly', 
                'probationary', 
                'project_based', 
                'performance_improvement'
            ])->default('annual');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('overall_score', 3, 2)->nullable();
            $table->decimal('goals_achievement', 3, 2)->nullable();
            $table->decimal('technical_skills', 3, 2)->nullable();
            $table->decimal('soft_skills', 3, 2)->nullable();
            $table->decimal('attendance_punctuality', 3, 2)->nullable();
            $table->decimal('teamwork_collaboration', 3, 2)->nullable();
            $table->decimal('initiative_innovation', 3, 2)->nullable();
            $table->decimal('quality_of_work', 3, 2)->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('development_plan')->nullable();
            $table->text('additional_comments')->nullable();
            $table->enum('status', [
                'draft', 
                'pending', 
                'completed', 
                'approved', 
                'cancelled'
            ])->default('draft');
            $table->date('evaluation_date');
            $table->date('next_evaluation_date')->nullable();
            $table->json('attachments')->nullable();
            $table->foreignId('evaluated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'evaluation_type']);
            $table->index(['status', 'evaluation_date']);
            $table->index('evaluated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};

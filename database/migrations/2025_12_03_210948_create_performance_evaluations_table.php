<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Based on Quarterly Performance Appraisal Form
     */
    public function up(): void
    {
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            
            // 1. Employee Details
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            
            // Evaluation Period
            $table->enum('evaluation_quarter', ['Q1', 'Q2', 'Q3', 'Q4'])->default('Q1');
            $table->year('evaluation_year');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('evaluation_date')->nullable();
            
            // 2. Performance Criteria (Rating 1-5) with Remarks
            // 1 = Poor | 2 = Fair | 3 = Satisfactory | 4 = Good | 5 = Excellent
            
            // 1. Productivity / Output
            $table->tinyInteger('productivity_output')->unsigned()->nullable()->comment('1-5: Meets or exceeds daily/weekly production targets');
            $table->text('productivity_output_remarks')->nullable();
            
            // 2. Quality of Work
            $table->tinyInteger('quality_of_work')->unsigned()->nullable()->comment('1-5: Produces work that meets quality standards with minimal rework');
            $table->text('quality_of_work_remarks')->nullable();
            
            // 3. Attendance & Punctuality
            $table->tinyInteger('attendance_punctuality')->unsigned()->nullable()->comment('1-5: Reports to work on time; follows shift schedules');
            $table->text('attendance_punctuality_remarks')->nullable();
            
            // 4. Safety Compliance
            $table->tinyInteger('safety_compliance')->unsigned()->nullable()->comment('1-5: Follows safety procedures, uses PPE correctly');
            $table->text('safety_compliance_remarks')->nullable();
            
            // 5. Machine Operation Skills
            $table->tinyInteger('machine_operation_skills')->unsigned()->nullable()->comment('1-5: Efficient in operating assigned machines/equipment');
            $table->text('machine_operation_skills_remarks')->nullable();
            
            // 6. Teamwork & Cooperation
            $table->tinyInteger('teamwork_cooperation')->unsigned()->nullable()->comment('1-5: Works well with team members, supports others');
            $table->text('teamwork_cooperation_remarks')->nullable();
            
            // 7. Adaptability & Learning
            $table->tinyInteger('adaptability_learning')->unsigned()->nullable()->comment('1-5: Responds positively to new tasks and training');
            $table->text('adaptability_learning_remarks')->nullable();
            
            // 8. Housekeeping (5S)
            $table->tinyInteger('housekeeping_5s')->unsigned()->nullable()->comment('1-5: Keeps workstation clean and organized');
            $table->text('housekeeping_5s_remarks')->nullable();
            
            // 9. Discipline & Attitude
            $table->tinyInteger('discipline_attitude')->unsigned()->nullable()->comment('1-5: Follows company rules, shows positive attitude');
            $table->text('discipline_attitude_remarks')->nullable();
            
            // 10. Initiative & Responsibility
            $table->tinyInteger('initiative_responsibility')->unsigned()->nullable()->comment('1-5: Takes ownership of tasks, suggests improvements');
            $table->text('initiative_responsibility_remarks')->nullable();
            
            // 3. Overall Performance Summary
            $table->decimal('average_score', 3, 2)->nullable()->comment('Average of all criteria (1-5)');
            $table->enum('performance_level', [
                'needs_improvement',
                'satisfactory', 
                'good',
                'excellent'
            ])->nullable();
            $table->boolean('eligible_for_bonus')->default(false);
            
            // 4. Supervisor's Comments
            $table->text('supervisor_comments')->nullable()->comment('Key strengths, areas for improvement, recommendations');
            
            // 5. Employee's Comments
            $table->text('employee_comments')->nullable()->comment('Employee feedback or concerns');
            
            // 6. Signatures
            $table->foreignId('supervisor_signed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('supervisor_signed_at')->nullable();
            
            $table->foreignId('department_head_signed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('department_head_signed_at')->nullable();
            
            $table->timestamp('employee_signed_at')->nullable();
            
            // Status
            $table->enum('status', [
                'draft',
                'pending_supervisor',
                'pending_department_head', 
                'pending_employee',
                'completed',
                'cancelled'
            ])->default('draft');
            
            // Metadata
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id', 'evaluation_year', 'evaluation_quarter'], 'perf_eval_emp_year_quarter_idx');
            $table->index(['status', 'evaluation_date'], 'perf_eval_status_date_idx');
            $table->index('supervisor_id', 'perf_eval_supervisor_idx');
            $table->index('department_id', 'perf_eval_department_idx');
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

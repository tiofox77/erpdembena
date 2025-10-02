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
        Schema::create('payroll_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')->constrained('payroll_batches')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls');
            
            // Financial data (copied from payroll when processed)
            $table->decimal('gross_salary', 15, 2)->nullable();
            $table->decimal('net_salary', 15, 2)->nullable();
            $table->decimal('total_deductions', 15, 2)->nullable();
            
            // Processing status and control
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'skipped'
            ])->default('pending');
            $table->integer('processing_order')->default(0);
            $table->timestamp('processed_at')->nullable();
            
            // Error handling
            $table->text('error_message')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('payroll_batch_id');
            $table->index('employee_id');
            $table->index('status');
            $table->index('processing_order');
            
            // Unique constraint to prevent duplicate employees in same batch
            $table->unique(['payroll_batch_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_batch_items');
    }
};

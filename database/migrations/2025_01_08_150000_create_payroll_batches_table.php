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
        Schema::create('payroll_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->enum('status', [
                'draft',
                'ready_to_process',
                'processing',
                'completed',
                'failed',
                'approved',
                'paid'
            ])->default('draft');
            
            // Employee and processing counters
            $table->integer('total_employees')->default(0);
            $table->integer('processed_employees')->default(0);
            
            // Financial totals
            $table->decimal('total_gross_amount', 15, 2)->default(0);
            $table->decimal('total_net_amount', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            
            // Date and time tracking
            $table->date('batch_date');
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            
            // User tracking
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            // Payment information
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check'])->default('bank_transfer');
            $table->string('bank_transfer_reference')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index('status');
            $table->index('payroll_period_id');
            $table->index('department_id');
            $table->index('batch_date');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_batches');
    }
};

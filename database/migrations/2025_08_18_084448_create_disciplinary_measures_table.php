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
        Schema::create('disciplinary_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->enum('measure_type', [
                'verbal_warning',
                'written_warning', 
                'suspension',
                'termination',
                'fine',
                'other'
            ]);
            $table->string('reason');
            $table->text('description');
            $table->date('applied_date');
            $table->date('effective_date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->foreignId('applied_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id', 'applied_date']);
            $table->index(['measure_type', 'status']);
            $table->index('applied_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinary_measures');
    }
};

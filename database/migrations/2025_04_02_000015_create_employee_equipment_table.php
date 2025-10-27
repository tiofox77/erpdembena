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
        Schema::create('employee_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->date('return_date')->nullable();
            $table->string('condition_on_issue');
            $table->string('condition_on_return')->nullable();
            $table->enum('status', ['issued', 'returned', 'damaged', 'lost']);
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
            
            // Ensure unique assignment - only one active assignment per equipment
            $table->unique(['equipment_id', 'status'], 'unique_active_equipment_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_equipment');
    }
};

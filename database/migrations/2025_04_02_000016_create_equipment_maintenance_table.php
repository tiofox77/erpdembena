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
        Schema::create('equipment_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->enum('maintenance_type', ['preventive', 'corrective', 'predictive', 'conditional', 'upgrade', 'inspection']);
            $table->date('maintenance_date');
            $table->decimal('cost', 10, 2)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->enum('status', ['planned', 'schedule', 'in_progress', 'completed', 'cancelled']);
            $table->text('description')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenance');
    }
};

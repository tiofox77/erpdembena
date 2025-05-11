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
        Schema::create('mrp_production_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('sc_products')->onDelete('cascade');
            $table->string('schedule_number')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('planned_quantity', 10, 3);
            $table->enum('status', ['draft', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('responsible')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('sc_inventory_locations');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_production_schedules');
    }
};

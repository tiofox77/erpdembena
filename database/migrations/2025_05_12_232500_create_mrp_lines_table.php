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
        Schema::create('mrp_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->decimal('capacity_per_hour', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('location_id')->nullable()->constrained('sc_inventory_locations')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabela pivot para relação muitos-para-muitos entre linhas e turnos
        Schema::create('mrp_line_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained('mrp_lines')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('mrp_shifts')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Impedir duplicatas na relação linha-turno
            $table->unique(['line_id', 'shift_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_line_shifts');
        Schema::dropIfExists('mrp_lines');
    }
};

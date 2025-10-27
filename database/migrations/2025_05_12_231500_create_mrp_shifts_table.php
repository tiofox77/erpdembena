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
        Schema::create('mrp_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('start_time', 10); // Formato HH:MM
            $table->string('end_time', 10); // Formato HH:MM
            $table->string('description', 255)->nullable();
            $table->string('color_code', 20)->default('#3B82F6');
            $table->boolean('is_active')->default(true);
            $table->string('break_start', 10)->nullable(); // Formato HH:MM
            $table->string('break_end', 10)->nullable(); // Formato HH:MM
            $table->json('working_days')->nullable(); // Array de dias da semana
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_shifts');
    }
};

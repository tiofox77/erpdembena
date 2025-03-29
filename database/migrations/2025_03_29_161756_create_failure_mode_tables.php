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
        // Verificar se a tabela já existe antes de criar
        if (!Schema::hasTable('failure_mode_categories')) {
            // Create failure mode categories table
            Schema::create('failure_mode_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Verificar se a tabela já existe antes de criar
        if (!Schema::hasTable('failure_modes')) {
            // Create failure modes table
            Schema::create('failure_modes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('failure_mode_categories')->nullOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failure_modes');
        Schema::dropIfExists('failure_mode_categories');
    }
};

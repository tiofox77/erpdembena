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
        if (!Schema::hasTable('failure_cause_categories')) {
            // Create failure cause categories table
            Schema::create('failure_cause_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Verificar se a tabela já existe antes de criar
        if (!Schema::hasTable('failure_causes')) {
            // Create failure causes table
            Schema::create('failure_causes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('failure_cause_categories')->nullOnDelete();
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
        Schema::dropIfExists('failure_causes');
        Schema::dropIfExists('failure_cause_categories');
    }
};

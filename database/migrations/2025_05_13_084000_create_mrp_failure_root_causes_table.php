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
        // Verificar se a tabela já existe para evitar erros em ambiente de produção
        if (Schema::hasTable('mrp_failure_root_causes')) {
            return;
        }

        // Garantir que a tabela de categorias existe antes de criar a tabela de causas
        if (!Schema::hasTable('mrp_failure_categories')) {
            throw new \Exception('A tabela mrp_failure_categories deve ser criada antes da tabela mrp_failure_root_causes');
        }

        Schema::create('mrp_failure_root_causes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('mrp_failure_categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_failure_root_causes');
    }
};

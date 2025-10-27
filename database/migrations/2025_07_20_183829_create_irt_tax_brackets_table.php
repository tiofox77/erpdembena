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
        Schema::create('irt_tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('bracket_number')->unique()->comment('Número do escalão (1-12)');
            $table->decimal('min_income', 15, 2)->comment('Rendimento mínimo do escalão em AKZ');
            $table->decimal('max_income', 15, 2)->nullable()->comment('Rendimento máximo do escalão em AKZ (null para o último escalão)');
            $table->decimal('fixed_amount', 15, 2)->default(0)->comment('Parcela fixa em AKZ');
            $table->decimal('tax_rate', 5, 2)->comment('Taxa sobre o excedente em percentagem');
            $table->string('description')->comment('Descrição do escalão');
            $table->boolean('is_active')->default(true)->comment('Se o escalão está ativo');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['is_active', 'bracket_number']);
            $table->index(['min_income', 'max_income']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('irt_tax_brackets');
    }
};

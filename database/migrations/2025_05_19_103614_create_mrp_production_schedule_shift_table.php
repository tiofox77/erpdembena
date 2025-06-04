<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa as migrações.
     *
     * Cria a tabela de relacionamento entre programações de produção e turnos
     * seguindo o padrão de nomenclatura do módulo MRP
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('mrp_production_schedule_shift', function (Blueprint $table) {
            $table->id();
            
            // Chave estrangeira para a programação de produção
            $table->foreignId('production_schedule_id')
                ->constrained('mrp_production_schedules')
                ->onDelete('cascade');
                
            // Chave estrangeira para o turno
            $table->foreignId('shift_id')
                ->constrained('mrp_shifts')
                ->onDelete('cascade');
            
            $table->timestamps();
            
            // Garante que não haja duplicação de relacionamentos
            $table->unique(['production_schedule_id', 'shift_id'], 'mrp_prod_schedule_shift_uniq');
        });
    }

    /**
     * Reverte as migrações.
     *
     * Remove a tabela de relacionamento entre programações de produção e turnos
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_production_schedule_shift');
    }
};

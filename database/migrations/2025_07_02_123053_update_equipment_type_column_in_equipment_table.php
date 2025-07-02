<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Altera a coluna equipment_type de varchar para unsignedBigInteger
     * para armazenar o ID da categoria de equipamento.
     */
    public function up(): void
    {
        // Etapa 1: Primeiro renomeamos a coluna existente para equipment_type_old
        Schema::table('equipment', function (Blueprint $table) {
            $table->renameColumn('equipment_type', 'equipment_type_old');
        });

        // Etapa 2: Criamos a nova coluna como unsignedBigInteger
        Schema::table('equipment', function (Blueprint $table) {
            $table->unsignedBigInteger('equipment_type')->nullable()->after('equipment_type_old');
            
            // Adicionar a chave estrangeira
            $table->foreign('equipment_type')
                  ->references('id')
                  ->on('work_equipment_categories')
                  ->onDelete('set null');
        });

        // Etapa 3: Atualizar os dados existentes mapeando os valores antigos para IDs
        // Para os registos existentes, vamos buscar as categorias pelo nome
        // Se não encontrar uma categoria correspondente, deixa como null
        DB::statement(
            "UPDATE equipment e 
             LEFT JOIN work_equipment_categories wec ON e.equipment_type_old = wec.name 
             SET e.equipment_type = wec.id"       
        );

        // Etapa 4: Remover a coluna antiga após a migração dos dados
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('equipment_type_old');
        });
    }

    /**
     * Reverse the migrations.
     * Reverte a alteração da coluna de volta para varchar.
     */
    public function down(): void
    {
        // Etapa 1: Primeiro renomeamos a coluna existente para equipment_type_old
        Schema::table('equipment', function (Blueprint $table) {
            // Remover a chave estrangeira
            $table->dropForeign(['equipment_type']);
            $table->renameColumn('equipment_type', 'equipment_type_temp');
        });

        // Etapa 2: Criamos a nova coluna como varchar
        Schema::table('equipment', function (Blueprint $table) {
            $table->string('equipment_type')->nullable()->after('equipment_type_temp');
        });

        // Etapa 3: Atualizar os dados existentes mapeando os IDs para nomes
        DB::statement(
            "UPDATE equipment e 
             LEFT JOIN work_equipment_categories wec ON e.equipment_type_temp = wec.id 
             SET e.equipment_type = wec.name"       
        );

        // Etapa 4: Remover a coluna temporária
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('equipment_type_temp');
        });
    }
};

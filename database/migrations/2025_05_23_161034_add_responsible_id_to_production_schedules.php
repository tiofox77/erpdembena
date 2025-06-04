<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            // Verificar se a coluna responsible existe e removê-la após migrar os dados
            if (Schema::hasColumn('mrp_production_schedules', 'responsible')) {
                // Adicionar responsible_id como inteiro unsigned nullable e índice
                $table->unsignedBigInteger('responsible_id')->nullable()->after('priority');
                
                // Adicionar chave estrangeira para mrp_responsibles
                $table->foreign('responsible_id')
                      ->references('id')
                      ->on('mrp_responsibles')
                      ->onDelete('set null');
                
                // Transferir dados da coluna responsible para responsible_id se possível
                // Isso acontece fora do esquema pois é uma operação de dados
            } else {
                // Se não existir, apenas adicionar a nova coluna
                $table->unsignedBigInteger('responsible_id')->nullable()->after('priority');
                
                // Adicionar chave estrangeira para mrp_responsibles
                $table->foreign('responsible_id')
                      ->references('id')
                      ->on('mrp_responsibles')
                      ->onDelete('set null');
            }
        });
        
        // Migrar dados da coluna string para o relacionamento com ID
        if (Schema::hasColumn('mrp_production_schedules', 'responsible')) {
            // Obter os responsáveis existentes
            $schedules = DB::table('mrp_production_schedules')
                ->whereNotNull('responsible')
                ->get(['id', 'responsible']);
            
            foreach ($schedules as $schedule) {
                // Tentar encontrar o responsável pelo nome
                $responsible = DB::table('mrp_responsibles')
                    ->where('name', 'like', '%' . $schedule->responsible . '%')
                    ->first();
                
                if ($responsible) {
                    // Atualizar o ID do responsável
                    DB::table('mrp_production_schedules')
                        ->where('id', $schedule->id)
                        ->update(['responsible_id' => $responsible->id]);
                }
            }
            
            // Remover a coluna antiga após a migração dos dados
            Schema::table('mrp_production_schedules', function (Blueprint $table) {
                $table->dropColumn('responsible');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            // Adicionar novamente a coluna responsible como string
            $table->string('responsible')->nullable()->after('priority');
            
            // Transferir dados de volta se possível
            if (Schema::hasColumn('mrp_production_schedules', 'responsible_id')) {
                // Isso seria feito em código separado para operações com dados
                
                // Remover a chave estrangeira e a coluna
                $table->dropForeign(['responsible_id']);
                $table->dropColumn('responsible_id');
            }
        });
    }
};

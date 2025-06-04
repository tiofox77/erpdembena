<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Este migration apenas registra as migrações do MRP como concluídas,
     * sem tentar criar ou modificar tabelas.
     */
    public function up(): void
    {
        // Lista de migrações MRP para registrar
        $migrations = [
            '2025_05_08_000001_create_mrp_demand_forecasts_table',
            '2025_05_08_000002_create_mrp_bom_headers_table',
            '2025_05_08_000003_create_mrp_bom_details_table',
            '2025_05_08_000004_create_mrp_inventory_levels_table',
            '2025_05_08_000005_create_mrp_production_schedules_table',
            '2025_05_08_000006_create_mrp_production_orders_table',
            '2025_05_08_000007_create_mrp_purchase_plans_table',
            '2025_05_08_000008_create_mrp_capacity_plans_table',
            '2025_05_08_000009_create_mrp_financial_reports_table'
        ];

        // Verifica o batch atual
        $batch = DB::table('migrations')->max('batch');
        
        // Registra cada migração que ainda não está registrada
        foreach ($migrations as $migration) {
            $exists = DB::table('migrations')->where('migration', $migration)->exists();
            
            if (!$exists) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $batch + 1
                ]);
                
                // Não exibe output aqui pois afetaria a saída normal do migrador
            }
        }
    }

    /**
     * Reverse the migrations.
     * Não fazemos nada no down, pois é apenas um registro
     */
    public function down(): void
    {
        // Não remover os registros, pois as tabelas já existem
    }
};

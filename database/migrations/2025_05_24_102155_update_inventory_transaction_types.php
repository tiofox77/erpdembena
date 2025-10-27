<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SupplyChain\InventoryTransaction;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // Atualiza transações relacionadas a planos diários com tipo genérico para usar o tipo específico
        DB::table('sc_inventory_transactions')
            ->where('reference_type', 'production_daily_plan')
            ->where('transaction_type', 'production')
            ->whereNotNull('destination_location_id')
            ->whereNull('source_location_id')
            ->update([
                'transaction_type' => InventoryTransaction::TYPE_DAILY_PRODUCTION_FG
            ]);
            
        // Atualiza transações relacionadas a planos diários com tipo genérico para usar o tipo específico
        DB::table('sc_inventory_transactions')
            ->where('reference_type', 'production_daily_plan')
            ->where('transaction_type', 'production')
            ->whereNotNull('source_location_id')
            ->whereNull('destination_location_id')
            ->update([
                'transaction_type' => InventoryTransaction::TYPE_DAILY_PRODUCTION
            ]);

        // Corrige casos onde o tipo não está definido corretamente (produtos acabados)
        DB::table('sc_inventory_transactions')
            ->where('reference_type', 'production_daily_plan')
            ->where(function($query) {
                $query->where('transaction_type', '<>', InventoryTransaction::TYPE_DAILY_PRODUCTION_FG)
                      ->where('transaction_type', '<>', InventoryTransaction::TYPE_DAILY_PRODUCTION);
            })
            ->whereNotNull('destination_location_id')
            ->whereNull('source_location_id')
            ->update([
                'transaction_type' => InventoryTransaction::TYPE_DAILY_PRODUCTION_FG
            ]);

        // Corrige casos onde o tipo não está definido corretamente (matérias-primas)
        DB::table('sc_inventory_transactions')
            ->where('reference_type', 'production_daily_plan')
            ->where(function($query) {
                $query->where('transaction_type', '<>', InventoryTransaction::TYPE_DAILY_PRODUCTION_FG)
                      ->where('transaction_type', '<>', InventoryTransaction::TYPE_DAILY_PRODUCTION);
            })
            ->whereNotNull('source_location_id')
            ->whereNull('destination_location_id')
            ->update([
                'transaction_type' => InventoryTransaction::TYPE_DAILY_PRODUCTION
            ]);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // Não é necessário reverter esta migração, pois é apenas uma correção de dados
    }
};

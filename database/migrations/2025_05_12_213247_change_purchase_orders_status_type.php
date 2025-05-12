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
        // Primeiro obtemos a definição atual da coluna para garantir que todas as entradas são preservadas
        $statusColumn = DB::select("SHOW COLUMNS FROM sc_purchase_orders WHERE Field = 'status'")[0];
        $statusType = $statusColumn->Type;
        
        // Verificar se o campo é um ENUM
        if (strpos($statusType, 'enum') === 0) {
            // Converter status de ENUM para VARCHAR
            DB::statement("ALTER TABLE sc_purchase_orders MODIFY status VARCHAR(50) NOT NULL DEFAULT 'draft'");
            
            // Registrar a mudança no log
            \Illuminate\Support\Facades\Log::info('Campo status da tabela sc_purchase_orders convertido de ENUM para VARCHAR');
        } else {
            // Caso já tenha sido convertido ou não seja ENUM
            \Illuminate\Support\Facades\Log::info('Campo status da tabela sc_purchase_orders já não é do tipo ENUM');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertemos para o tipo ENUM original com os status padrão do sistema
        DB::statement("ALTER TABLE sc_purchase_orders MODIFY status ENUM(
            'draft',
            'pending_approval',
            'approved',
            'ordered',
            'partially_received',
            'completed',
            'cancelled'
        ) NOT NULL DEFAULT 'draft'");
            
        // Registrar a mudança no log
        \Illuminate\Support\Facades\Log::info('Campo status da tabela sc_purchase_orders revertido para ENUM');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomFormFieldsToShippingNotes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar se as colunas existem antes de adicionar
        Schema::table('shipping_notes', function (Blueprint $table) {
            // Verificar se a coluna 'custom_form_id' não existe
            if (!Schema::hasColumn('shipping_notes', 'custom_form_id')) {
                $table->foreignId('custom_form_id')->nullable()->after('status')
                    ->constrained('sc_custom_forms')->nullOnDelete();
            }
            
            // Verificar se a coluna 'form_data' não existe
            if (!Schema::hasColumn('shipping_notes', 'form_data')) {
                $table->json('form_data')->nullable()->after('custom_form_id');
            }
            
            // A modificação da coluna 'status' pode ser aplicada de qualquer forma para garantir que o enum tenha a opção 'custom_form'
            // Verificamos se a opção 'custom_form' já existe no enum
            $statusColumn = DB::select("SHOW COLUMNS FROM shipping_notes WHERE Field = 'status'")[0];
            $enumValues = preg_replace("/^enum\((.*)\)$/", "$1", $statusColumn->Type);
            $enumValues = str_replace("'", "", $enumValues);
            $values = explode(',', $enumValues);
            
            if (!in_array('custom_form', $values)) {
                DB::statement("ALTER TABLE shipping_notes MODIFY status ENUM(
                    'order_placed',
                    'proforma_invoice_received',
                    'payment_completed',
                    'du_in_process',
                    'goods_acquired',
                    'shipped_to_port',
                    'shipping_line_booking_confirmed',
                    'container_loaded',
                    'on_board',
                    'arrived_at_port',
                    'customs_clearance',
                    'delivered',
                    'custom_form'
                ) NOT NULL");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_notes', function (Blueprint $table) {
            // Remover as colunas adicionadas
            $table->dropForeign(['custom_form_id']);
            $table->dropColumn('custom_form_id');
            $table->dropColumn('form_data');
            
            // Reverter a modificação da coluna 'status'
            DB::statement("ALTER TABLE shipping_notes MODIFY status ENUM(
                'order_placed',
                'proforma_invoice_received',
                'payment_completed',
                'du_in_process',
                'goods_acquired',
                'shipped_to_port',
                'shipping_line_booking_confirmed',
                'container_loaded',
                'on_board',
                'arrived_at_port',
                'customs_clearance',
                'delivered'
            ) NOT NULL");
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckGoodsReceiptItemsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar se as colunas já existem e adicioná-las se não existirem
        Schema::table('sc_goods_receipt_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sc_goods_receipt_items', 'is_latest')) {
                $table->boolean('is_latest')->default(true)->after('status');
            }
            
            if (!Schema::hasColumn('sc_goods_receipt_items', 'total_accepted')) {
                $table->decimal('total_accepted', 12, 2)->default(0)->after('accepted_quantity');
            }
            
            if (!Schema::hasColumn('sc_goods_receipt_items', 'total_rejected')) {
                $table->decimal('total_rejected', 12, 2)->default(0)->after('rejected_quantity');
            }
            
            if (!Schema::hasColumn('sc_goods_receipt_items', 'remaining_quantity')) {
                $table->decimal('remaining_quantity', 12, 2)->default(0)->after('total_rejected');
            }
            
            if (!Schema::hasColumn('sc_goods_receipt_items', 'received_by')) {
                $table->unsignedBigInteger('received_by')->nullable()->after('notes');
                $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('sc_goods_receipt_items', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('received_by');
            }
        });
        
        // Adicionar o índice composto se não existir
        Schema::table('sc_goods_receipt_items', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEX FROM sc_goods_receipt_items"))
                ->pluck('Key_name')
                ->toArray();
                
            if (!in_array('idx_gr_items_composite', $indexes)) {
                $table->index(['goods_receipt_id', 'product_id', 'is_latest'], 'idx_gr_items_composite');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Não remover as colunas para evitar perda de dados
        // Apenas remover o índice se existir
        Schema::table('sc_goods_receipt_items', function (Blueprint $table) {
            $indexes = collect(DB::select("SHOW INDEX FROM sc_goods_receipt_items"))
                ->pluck('Key_name')
                ->toArray();
                
            if (in_array('idx_gr_items_composite', $indexes)) {
                $table->dropIndex('idx_gr_items_composite');
            }
        });
    }
}

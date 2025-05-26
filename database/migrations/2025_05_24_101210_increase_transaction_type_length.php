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
        Schema::table('sc_inventory_transactions', function (Blueprint $table) {
            // Alterar o tamanho da coluna transaction_type para suportar valores mais longos
            $table->string('transaction_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_inventory_transactions', function (Blueprint $table) {
            // Restaurar o tamanho original da coluna (assumindo que era 20)
            $table->string('transaction_type', 20)->change();
        });
    }
};

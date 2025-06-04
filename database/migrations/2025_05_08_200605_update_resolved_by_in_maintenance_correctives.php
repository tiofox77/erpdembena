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
        Schema::table('maintenance_correctives', function (Blueprint $table) {
            // Primeiro remover a constraint anterior que aponta para users
            $table->dropForeign(['resolved_by']);
            
            // Redefinir a coluna para referenciar a tabela technicians
            $table->foreign('resolved_by')
                  ->references('id')
                  ->on('technicians')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_correctives', function (Blueprint $table) {
            // Reverter a alteração, voltando a apontar para users
            $table->dropForeign(['resolved_by']);
            
            // Redefinir a constraint original
            $table->foreign('resolved_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }
};

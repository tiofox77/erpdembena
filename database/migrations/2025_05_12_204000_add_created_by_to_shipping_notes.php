<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByToShippingNotes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipping_notes', function (Blueprint $table) {
            // Verificar se a coluna 'created_by' não existe antes de adicioná-la
            if (!Schema::hasColumn('shipping_notes', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('attachment_url')
                      ->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_notes', function (Blueprint $table) {
            // Remover a coluna 'created_by' se ela existir
            if (Schema::hasColumn('shipping_notes', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
}

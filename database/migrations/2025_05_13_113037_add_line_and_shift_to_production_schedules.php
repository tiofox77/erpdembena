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
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            // Adicionar campos de linha de produção e turno
            $table->foreignId('line_id')->nullable()->after('location_id')
                ->constrained('mrp_lines')->onDelete('set null');
            $table->foreignId('shift_id')->nullable()->after('line_id')
                ->constrained('mrp_shifts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            // Remover os campos adicionados
            $table->dropConstrainedForeignId('shift_id');
            $table->dropConstrainedForeignId('line_id');
        });
    }
};

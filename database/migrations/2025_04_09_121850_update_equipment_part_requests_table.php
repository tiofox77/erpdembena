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
        Schema::table('equipment_part_requests', function (Blueprint $table) {
            // Tornar o campo equipment_part_id nullable porque agora estamos usando a tabela de itens
            $table->unsignedBigInteger('equipment_part_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_part_requests', function (Blueprint $table) {
            // Reverter para não nullable
            $table->unsignedBigInteger('equipment_part_id')->nullable(false)->change();
        });
    }
};

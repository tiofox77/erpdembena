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
        Schema::table('mrp_production_orders', function (Blueprint $table) {
            // Adiciona a coluna schedule_id como chave estrangeira para mrp_production_schedules
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->foreign('schedule_id')
                  ->references('id')
                  ->on('mrp_production_schedules')
                  ->onDelete('set null'); // permite excluir um agendamento sem excluir as ordens
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_production_orders', function (Blueprint $table) {
            // Remove a chave estrangeira e a coluna
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
        });
    }
};

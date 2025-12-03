<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adiciona colunas para o Subsídio Noturno conforme Lei Angola Art. 102º
     * Trabalho noturno (20h-06h) = +25% sobre a remuneração
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('night_shift_allowance', 15, 2)->default(0)->after('total_overtime_hours')
                  ->comment('Subsídio noturno - Lei Angola Art. 102º - 25%');
            $table->integer('night_shift_days')->default(0)->after('night_shift_allowance')
                  ->comment('Número de dias trabalhados em turno noturno');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['night_shift_allowance', 'night_shift_days']);
        });
    }
};

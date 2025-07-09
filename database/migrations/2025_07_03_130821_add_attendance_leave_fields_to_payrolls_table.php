<?php

declare(strict_types=1);

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
        Schema::table('payrolls', function (Blueprint $table) {
            // Campos para integração com presença
            $table->decimal('attendance_hours', 10, 2)->nullable()->comment('Total de horas trabalhadas no período');
            
            // Campos para integração com licenças
            $table->decimal('leave_days', 10, 2)->nullable()->comment('Total de dias de licença no período');
            $table->decimal('maternity_days', 10, 2)->nullable()->comment('Total de dias de licença maternidade');
            $table->decimal('special_leave_days', 10, 2)->nullable()->comment('Total de dias de licença especial (não maternidade)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_hours',
                'leave_days',
                'maternity_days',
                'special_leave_days',
            ]);
        });
    }
};

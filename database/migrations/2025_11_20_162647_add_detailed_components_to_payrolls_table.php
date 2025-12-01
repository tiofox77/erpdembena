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
        Schema::table('payrolls', function (Blueprint $table) {
            // Subsídios e benefícios detalhados
            $table->decimal('transport_allowance', 12, 2)->default(0)->after('allowances')->comment('Subsídio de transporte');
            $table->decimal('food_allowance', 12, 2)->default(0)->after('transport_allowance')->comment('Subsídio de alimentação');
            $table->decimal('family_allowance', 12, 2)->default(0)->after('food_allowance')->comment('Subsídio familiar');
            $table->decimal('position_subsidy', 12, 2)->default(0)->after('family_allowance')->comment('Subsídio de posição');
            $table->decimal('additional_bonus', 12, 2)->default(0)->after('position_subsidy')->comment('Bônus adicional');
            
            // Subsídios de Natal e Férias (valores calculados)
            $table->decimal('christmas_subsidy_amount', 12, 2)->default(0)->after('additional_bonus')->comment('Valor subsídio de Natal');
            $table->decimal('vacation_subsidy_amount', 12, 2)->default(0)->after('christmas_subsidy_amount')->comment('Valor subsídio de férias');
            
            // Deduções detalhadas
            $table->decimal('advance_deduction', 12, 2)->default(0)->after('vacation_subsidy_amount')->comment('Desconto de adiantamentos');
            $table->decimal('late_deduction', 12, 2)->default(0)->after('advance_deduction')->comment('Desconto por atrasos');
            $table->decimal('total_salary_discounts', 12, 2)->default(0)->after('late_deduction')->comment('Total de descontos salariais');
            
            // Dados de presença
            $table->integer('present_days')->default(0)->after('total_salary_discounts')->comment('Dias presentes');
            $table->integer('absent_days')->default(0)->after('present_days')->comment('Dias ausentes');
            $table->integer('late_arrivals')->default(0)->after('absent_days')->comment('Dias de atraso');
            $table->decimal('total_overtime_hours', 10, 2)->default(0)->after('late_arrivals')->comment('Total de horas extras');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'transport_allowance',
                'food_allowance',
                'family_allowance',
                'position_subsidy',
                'additional_bonus',
                'christmas_subsidy_amount',
                'vacation_subsidy_amount',
                'advance_deduction',
                'late_deduction',
                'total_salary_discounts',
                'present_days',
                'absent_days',
                'late_arrivals',
                'total_overtime_hours',
            ]);
        });
    }
};

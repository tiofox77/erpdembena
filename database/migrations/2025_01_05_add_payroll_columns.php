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
            // Bónus e componentes adicionais
            $table->decimal('profile_bonus', 12, 2)->default(0)->after('bonuses');
            $table->decimal('overtime_amount', 12, 2)->default(0)->after('profile_bonus');
            $table->decimal('gross_salary', 12, 2)->default(0)->after('overtime_amount');
            
            // Base tributável e deduções específicas
            $table->decimal('base_irt_taxable_amount', 12, 2)->default(0)->after('gross_salary');
            $table->decimal('deductions_irt', 12, 2)->default(0)->after('base_irt_taxable_amount');
            $table->decimal('inss_3_percent', 12, 2)->default(0)->after('deductions_irt');
            $table->decimal('inss_8_percent', 12, 2)->default(0)->after('inss_3_percent');
            
            // Deduções por faltas
            $table->decimal('absence_deduction_amount', 12, 2)->default(0)->after('inss_8_percent');
            
            // Salário principal e totais calculados
            $table->decimal('main_salary', 12, 2)->default(0)->after('absence_deduction_amount');
            $table->decimal('total_deductions_calculated', 12, 2)->default(0)->after('main_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'profile_bonus',
                'overtime_amount', 
                'gross_salary',
                'base_irt_taxable_amount',
                'deductions_irt',
                'inss_3_percent',
                'inss_8_percent',
                'absence_deduction_amount',
                'main_salary',
                'total_deductions_calculated'
            ]);
        });
    }
};

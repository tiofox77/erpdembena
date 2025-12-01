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
            // Performance Subsidy (estava faltando)
            $table->decimal('performance_subsidy', 12, 2)->default(0)->after('position_subsidy')->comment('Subsídio de desempenho');
            
            // IRT Calculation Fields - Exemptions (isenções até 30k)
            $table->decimal('food_exemption', 12, 2)->default(0)->after('total_overtime_hours')->comment('Isenção subsídio alimentação (até 30k)');
            $table->decimal('transport_exemption', 12, 2)->default(0)->after('food_exemption')->comment('Isenção subsídio transporte (até 30k)');
            
            // IRT Calculation Fields - Taxable amounts (valores tributáveis/excesso)
            $table->decimal('food_taxable', 12, 2)->default(0)->after('transport_exemption')->comment('Subsídio alimentação tributável (excesso > 30k)');
            $table->decimal('transport_taxable', 12, 2)->default(0)->after('food_taxable')->comment('Subsídio transporte tributável (excesso > 30k)');
            
            // IRT Base before INSS deduction
            $table->decimal('irt_base_before_inss', 12, 2)->default(0)->after('transport_taxable')->comment('Base IRT antes de deduzir INSS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'performance_subsidy',
                'food_exemption',
                'transport_exemption',
                'food_taxable',
                'transport_taxable',
                'irt_base_before_inss',
            ]);
        });
    }
};

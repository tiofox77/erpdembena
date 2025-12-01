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
        Schema::table('payroll_batch_items', function (Blueprint $table) {
            // Editable subsidy flags (checkboxes)
            $table->boolean('christmas_subsidy')->default(false)->after('total_deductions')->comment('Incluir subsídio de Natal?');
            $table->boolean('vacation_subsidy')->default(false)->after('christmas_subsidy')->comment('Incluir subsídio de férias?');
            
            // Editable bonus
            $table->decimal('additional_bonus', 12, 2)->default(0)->after('vacation_subsidy')->comment('Bônus adicional editável');
            
            // Calculated amounts (from helper)
            $table->decimal('christmas_subsidy_amount', 12, 2)->default(0)->after('additional_bonus')->comment('Valor calculado subsídio Natal');
            $table->decimal('vacation_subsidy_amount', 12, 2)->default(0)->after('christmas_subsidy_amount')->comment('Valor calculado subsídio férias');
            
            // Detailed breakdown fields
            $table->decimal('basic_salary', 12, 2)->default(0)->after('vacation_subsidy_amount');
            $table->decimal('transport_allowance', 12, 2)->default(0)->after('basic_salary');
            $table->decimal('food_allowance', 12, 2)->default(0)->after('transport_allowance');
            $table->decimal('family_allowance', 12, 2)->default(0)->after('food_allowance');
            $table->decimal('overtime_amount', 12, 2)->default(0)->after('family_allowance');
            
            // Deductions breakdown
            $table->decimal('inss_deduction', 12, 2)->default(0)->after('overtime_amount');
            $table->decimal('irt_deduction', 12, 2)->default(0)->after('inss_deduction');
            $table->decimal('advance_deduction', 12, 2)->default(0)->after('irt_deduction');
            $table->decimal('discount_deduction', 12, 2)->default(0)->after('advance_deduction');
            $table->decimal('late_deduction', 12, 2)->default(0)->after('discount_deduction');
            $table->decimal('absence_deduction', 12, 2)->default(0)->after('late_deduction');
            
            // Attendance data
            $table->integer('present_days')->default(0)->after('absence_deduction');
            $table->integer('absent_days')->default(0)->after('present_days');
            $table->integer('late_days')->default(0)->after('absent_days');
            $table->integer('total_working_days')->default(0)->after('late_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_batch_items', function (Blueprint $table) {
            $table->dropColumn([
                'christmas_subsidy',
                'vacation_subsidy',
                'additional_bonus',
                'christmas_subsidy_amount',
                'vacation_subsidy_amount',
                'basic_salary',
                'transport_allowance',
                'food_allowance',
                'family_allowance',
                'overtime_amount',
                'inss_deduction',
                'irt_deduction',
                'advance_deduction',
                'discount_deduction',
                'late_deduction',
                'absence_deduction',
                'present_days',
                'absent_days',
                'late_days',
                'total_working_days',
            ]);
        });
    }
};

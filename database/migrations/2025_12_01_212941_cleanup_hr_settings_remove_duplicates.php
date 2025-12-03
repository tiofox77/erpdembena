<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Chaves redundantes/não usadas a remover
     */
    private array $keysToRemove = [
        'daily_work_hours',       // Duplicado de working_hours_per_day
        'monthly_work_hours',     // Calculado, não precisa
        'weekend_multiplier',     // Duplicado de overtime_multiplier_weekend
        'holiday_multiplier',     // Duplicado de overtime_multiplier_holiday
        'night_shift_multiplier', // Substituído por night_shift_percentage
        'inss_rate',              // Duplicado de inss_employee_rate
        'irt_min_salary',         // Duplicado de min_salary_tax_exempt
        'irt_rate',               // Não usado (IRT usa tabela de escalões)
        'inss_max_salary',        // Não usado
        'tax_calculation_base',   // Não usado
        'working_days_per_week',  // Não usado no cálculo
        'working_hours_per_week', // Não usado no cálculo
    ];

    /**
     * Chaves que faltam e precisam ser adicionadas
     */
    private array $keysToAdd = [
        [
            'key' => 'vacation_subsidy_percentage',
            'value' => '50',
            'group' => 'benefits',
            'description' => 'Percentual do subsídio de férias sobre o salário base',
        ],
        [
            'key' => 'christmas_subsidy_percentage',
            'value' => '50',
            'group' => 'benefits',
            'description' => 'Percentual do subsídio de Natal sobre o salário base',
        ],
        [
            'key' => 'overtime_first_hour_weekday',
            'value' => '1.25',
            'group' => 'labor_rules',
            'description' => 'Multiplicador para a primeira hora extra em dias úteis',
        ],
        [
            'key' => 'overtime_additional_hours_weekday',
            'value' => '1.375',
            'group' => 'labor_rules',
            'description' => 'Multiplicador para horas extras adicionais em dias úteis',
        ],
        [
            'key' => 'night_shift_percentage',
            'value' => '25',
            'group' => 'labor_rules',
            'description' => 'Percentual adicional para trabalho noturno (Lei Angola Art. 102º)',
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Remover chaves redundantes/duplicadas
        DB::table('hr_settings')
            ->whereIn('key', $this->keysToRemove)
            ->delete();

        // 2. Adicionar chaves que faltam (se não existirem)
        foreach ($this->keysToAdd as $setting) {
            $exists = DB::table('hr_settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('hr_settings')->insert([
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                    'is_system' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Corrigir overtime_multiplier_holiday se estiver 0
        DB::table('hr_settings')
            ->where('key', 'overtime_multiplier_holiday')
            ->where('value', '0')
            ->update(['value' => '2.0']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar chaves removidas com valores padrão
        $restoreKeys = [
            ['key' => 'daily_work_hours', 'value' => '8', 'group' => 'labor_rules', 'description' => 'Número de horas de trabalho por dia'],
            ['key' => 'monthly_work_hours', 'value' => '192', 'group' => 'labor_rules', 'description' => 'Total de horas de trabalho por mês'],
            ['key' => 'weekend_multiplier', 'value' => '1.5', 'group' => 'labor_rules', 'description' => 'Multiplicador para horas extras ao fim de semana'],
            ['key' => 'holiday_multiplier', 'value' => '2.0', 'group' => 'labor_rules', 'description' => 'Multiplicador para horas extras em feriados'],
            ['key' => 'night_shift_multiplier', 'value' => '1.25', 'group' => 'labor_rules', 'description' => 'Multiplicador adicional para turnos noturnos'],
            ['key' => 'inss_rate', 'value' => '3.0', 'group' => 'tax', 'description' => 'Taxa de INSS em percentagem'],
            ['key' => 'irt_min_salary', 'value' => '70000', 'group' => 'tax', 'description' => 'Salário mínimo isento de IRT em AOA'],
            ['key' => 'irt_rate', 'value' => '6.5', 'group' => 'tax', 'description' => 'Taxa de IRT em percentagem'],
            ['key' => 'inss_max_salary', 'value' => '0', 'group' => 'tax', 'description' => 'Salário máximo para cálculo de INSS'],
            ['key' => 'tax_calculation_base', 'value' => 'gross', 'group' => 'tax', 'description' => 'Base de cálculo de impostos'],
            ['key' => 'working_days_per_week', 'value' => '5', 'group' => 'labor_rules', 'description' => 'Número de dias de trabalho semanal'],
            ['key' => 'working_hours_per_week', 'value' => '44', 'group' => 'labor_rules', 'description' => 'Número de horas de trabalho semanal'],
        ];

        foreach ($restoreKeys as $setting) {
            $exists = DB::table('hr_settings')->where('key', $setting['key'])->exists();
            if (!$exists) {
                DB::table('hr_settings')->insert([
                    'key' => $setting['key'],
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                    'is_system' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Remover chaves adicionadas
        $addedKeys = array_column($this->keysToAdd, 'key');
        DB::table('hr_settings')->whereIn('key', $addedKeys)->delete();
    }
};

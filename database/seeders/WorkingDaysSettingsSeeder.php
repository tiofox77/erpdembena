<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HR\HRSetting;
use Illuminate\Database\Seeder;

class WorkingDaysSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workingDaysSettings = [
            [
                'key' => 'monthly_working_days',
                'value' => '24',
                'group' => 'labor_rules',
                'description' => 'Número de dias úteis trabalhados por mês (22, 24, 26, 28, 30)',
                'is_system' => false
            ],
            [
                'key' => 'monthly_work_hours',
                'value' => '192',
                'group' => 'labor_rules',
                'description' => 'Total de horas de trabalho por mês (calculado automaticamente: dias úteis × 8h)',
                'is_system' => false
            ],
            [
                'key' => 'daily_work_hours',
                'value' => '8',
                'group' => 'labor_rules',
                'description' => 'Número de horas de trabalho por dia',
                'is_system' => false
            ],
            [
                'key' => 'weekend_multiplier',
                'value' => '1.5',
                'group' => 'labor_rules',
                'description' => 'Multiplicador para horas extras ao fim de semana',
                'is_system' => false
            ],
            [
                'key' => 'holiday_multiplier',
                'value' => '2.0',
                'group' => 'labor_rules',
                'description' => 'Multiplicador para horas extras em feriados',
                'is_system' => false
            ],
            [
                'key' => 'night_shift_multiplier',
                'value' => '1.25',
                'group' => 'labor_rules',
                'description' => 'Multiplicador adicional para turnos noturnos',
                'is_system' => false
            ],
            [
                'key' => 'overtime_daily_limit',
                'value' => '2.0',
                'group' => 'labor_rules',
                'description' => 'Limite diário de horas extras permitidas',
                'is_system' => false
            ],
            [
                'key' => 'overtime_monthly_limit',
                'value' => '48.0',
                'group' => 'labor_rules',
                'description' => 'Limite mensal de horas extras permitidas',
                'is_system' => false
            ]
        ];

        foreach ($workingDaysSettings as $setting) {
            HRSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use App\Models\HR\HRSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Isenções fiscais para IRT (conforme legislação Angolana)
        HRSetting::updateOrCreate(
            ['key' => 'transport_tax_exempt'],
            [
                'value' => '30000',
                'group' => 'tax',
                'description' => 'Valor de subsídio de transporte isento de IRT (até 30.000 AOA)'
            ]
        );

        HRSetting::updateOrCreate(
            ['key' => 'food_tax_exempt'],
            [
                'value' => '30000',
                'group' => 'tax',
                'description' => 'Valor de subsídio de alimentação isento de IRT (até 30.000 AOA)'
            ]
        );

        // Dias de trabalho mensais (já existe monthly_working_days, garantir que existe)
        HRSetting::updateOrCreate(
            ['key' => 'working_days_per_month'],
            [
                'value' => '22',
                'group' => 'labor_rules',
                'description' => 'Dias de trabalho padrão por mês'
            ]
        );

        HRSetting::updateOrCreate(
            ['key' => 'working_hours_per_day'],
            [
                'value' => '8',
                'group' => 'labor_rules',
                'description' => 'Horas de trabalho padrão por dia'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'transport_tax_exempt',
            'food_tax_exempt',
        ];

        foreach ($keys as $key) {
            HRSetting::where('key', $key)->delete();
        }
    }
};

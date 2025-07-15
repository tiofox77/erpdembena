<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\HR\HRSetting;

class AddOvertimeHrSettings extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Configurações para multiplicadores específicos de horas extras
        HRSetting::updateOrCreate(
            ['key' => 'overtime_first_hour_weekday'],
            [
                'value' => '1.25', 
                'group' => 'labor_rules', 
                'description' => 'Multiplicador para a primeira hora extra em dias úteis (acréscimo de 25%)'
            ]
        );
        
        HRSetting::updateOrCreate(
            ['key' => 'overtime_additional_hours_weekday'],
            [
                'value' => '1.375', 
                'group' => 'labor_rules', 
                'description' => 'Multiplicador para horas extras adicionais em dias úteis (acréscimo de 37,5%)'
            ]
        );
        
        // Limites legais
        HRSetting::updateOrCreate(
            ['key' => 'overtime_daily_limit'],
            [
                'value' => '2', 
                'group' => 'labor_rules', 
                'description' => 'Limite diário de horas extras permitidas por dia útil'
            ]
        );
        
        HRSetting::updateOrCreate(
            ['key' => 'overtime_monthly_limit'],
            [
                'value' => '48', 
                'group' => 'labor_rules', 
                'description' => 'Limite mensal de horas extras permitidas'
            ]
        );
        
        HRSetting::updateOrCreate(
            ['key' => 'overtime_yearly_limit'],
            [
                'value' => '200', 
                'group' => 'labor_rules', 
                'description' => 'Limite anual de horas extras permitidas'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'overtime_first_hour_weekday',
            'overtime_additional_hours_weekday',
            'overtime_daily_limit',
            'overtime_monthly_limit',
            'overtime_yearly_limit'
        ];
        
        foreach ($keys as $key) {
            HRSetting::where('key', $key)->delete();
        }
    }
}

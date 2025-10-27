<?php

declare(strict_types=1);

namespace App\Helpers;

class PayrollTranslationHelper
{
    /**
     * Mapear nomes de componentes da folha para keys de tradução
     */
    public static function getNameTranslationKey(string $itemName): string
    {
        $nameMapping = [
            // Earnings
            'Salário Base' => 'basic_salary',
            'Subsídio de Transporte' => 'transport_allowance',
            'Subsídio de Alimentação' => 'meal_allowance',
            'Horas Extras' => 'overtime_hours',
            'Bónus' => 'bonus',
            'Subsídio de Natal' => 'christmas_subsidy',
            'Subsídio de Férias' => 'vacation_subsidy',
            
            // Deductions
            'Desconto por Atrasos' => 'late_deduction',
            'Desconto por Faltas' => 'absence_deduction',
            'INSS' => 'social_security',
            'IRT' => 'income_tax',
            'Descontos Salariais' => 'salary_discounts',
            'Adiantamentos Salariais' => 'salary_advances',
        ];

        return $nameMapping[$itemName] ?? strtolower(str_replace(' ', '_', $itemName));
    }

    /**
     * Obter tradução do nome do componente
     */
    public static function getTranslatedName(string $itemName): string
    {
        $key = self::getNameTranslationKey($itemName);
        $translationKey = 'payroll.' . $key;
        
        $translated = __($translationKey);
        
        // Se a tradução não existir, retornar o nome original
        return $translated !== $translationKey ? $translated : $itemName;
    }

    /**
     * Obter tradução da descrição do componente
     */
    public static function getTranslatedDescription(string $itemName, string $originalDescription = ''): string
    {
        $key = self::getNameTranslationKey($itemName);
        $translationKey = 'payroll.' . $key . '_description';
        
        $translated = __($translationKey);
        
        // Se a tradução não existir, retornar a descrição original
        return $translated !== $translationKey ? $translated : $originalDescription;
    }

    /**
     * Obter tradução do tipo de componente
     */
    public static function getTranslatedType(string $type): string
    {
        $translationKey = 'payroll.' . $type;
        $translated = __($translationKey);
        
        return $translated !== $translationKey ? $translated : ucfirst($type);
    }
}

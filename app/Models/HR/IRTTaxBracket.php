<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IRTTaxBracket extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     */
    protected $table = 'irt_tax_brackets';
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'bracket_number',
        'min_income',
        'max_income',
        'fixed_amount',
        'tax_rate',
        'description',
        'is_active'
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    
    /**
     * Calculate IRT tax for a given income using fixed tax brackets (Angola 2024)
     */
    public static function calculateIRT(float $income): float
    {
        if ($income <= 0) {
            return 0.0;
        }
        
        // Escalões IRT Angola 2024 (conforme tabela oficial AGT)
        $brackets = [
            // 1º Escalão: Isento até 100.000 AOA
            ['min' => 0, 'max' => 100000, 'fixed' => 0, 'rate' => 0],
            // 2º Escalão: 100.001 a 150.000 AOA - 13% (sem parcela fixa)
            ['min' => 100000, 'max' => 150000, 'fixed' => 0, 'rate' => 13],
            // 3º Escalão: 150.001 a 200.000 AOA - 16% + 12.500 AOA
            ['min' => 150000, 'max' => 200000, 'fixed' => 12500, 'rate' => 16],
            // 4º Escalão: 200.001 a 300.000 AOA - 18% + 31.250 AOA
            ['min' => 200000, 'max' => 300000, 'fixed' => 31250, 'rate' => 18],
            // 5º Escalão: 300.001 a 500.000 AOA - 19% + 49.250 AOA
            ['min' => 300000, 'max' => 500000, 'fixed' => 49250, 'rate' => 19],
            // 6º Escalão: 500.001 a 1.000.000 AOA - 20% + 87.250 AOA
            ['min' => 500000, 'max' => 1000000, 'fixed' => 87250, 'rate' => 20],
            // 7º Escalão: 1.000.001 a 1.500.000 AOA - 21% + 187.250 AOA
            ['min' => 1000000, 'max' => 1500000, 'fixed' => 187250, 'rate' => 21],
            // 8º Escalão: 1.500.001 a 2.000.000 AOA - 22% + 292.250 AOA
            ['min' => 1500000, 'max' => 2000000, 'fixed' => 292250, 'rate' => 22],
            // 9º Escalão: 2.000.001 a 2.500.000 AOA - 23% + 402.250 AOA
            ['min' => 2000000, 'max' => 2500000, 'fixed' => 402250, 'rate' => 23],
            // 10º Escalão: 2.500.001 a 5.000.000 AOA - 24% + 517.250 AOA
            ['min' => 2500000, 'max' => 5000000, 'fixed' => 517250, 'rate' => 24],
            // 11º Escalão: 5.000.001 a 10.000.000 AOA - 24,5% + 1.117.250 AOA
            ['min' => 5000000, 'max' => 10000000, 'fixed' => 1117250, 'rate' => 24.5],
            // 12º Escalão: Acima de 10.000.000 AOA - 25% + 2.342.250 AOA
            ['min' => 10000000, 'max' => null, 'fixed' => 2342250, 'rate' => 25],
        ];
        
        $totalTax = 0.0;
        
        foreach ($brackets as $bracket) {
            // Skip if income is below this bracket's minimum
            if ($income <= $bracket['min']) {
                continue;
            }
            
            // Determine the taxable amount for this bracket
            $taxableInThisBracket = 0.0;
            
            if ($bracket['max'] === null) {
                // This is the highest bracket (no upper limit)
                $taxableInThisBracket = $income - $bracket['min'];
            } else {
                // Calculate taxable amount within this bracket's limits
                $taxableInThisBracket = min($income, $bracket['max']) - $bracket['min'];
            }
            
            // Only calculate tax if there's taxable income in this bracket
            if ($taxableInThisBracket > 0) {
                // Add fixed amount and percentage on excess
                $totalTax = $bracket['fixed'] + ($taxableInThisBracket * ($bracket['rate'] / 100));
                
                // If income fits entirely in this bracket, we're done
                if ($bracket['max'] === null || $income <= $bracket['max']) {
                    break;
                }
            }
        }
        
        return round($totalTax, 2);
    }
    
    /**
     * Get the tax bracket that applies to a specific income (using fixed brackets)
     */
    public static function getBracketForIncome(float $income): ?object
    {
        // Escalões IRT Angola 2024 (conforme tabela oficial AGT)
        $brackets = [
            ['bracket_number' => 1, 'min' => 0, 'max' => 100000, 'fixed_amount' => 0, 'tax_rate' => 0, 'description' => 'Isento'],
            ['bracket_number' => 2, 'min' => 100000, 'max' => 150000, 'fixed_amount' => 0, 'tax_rate' => 13, 'description' => '13%'],
            ['bracket_number' => 3, 'min' => 150000, 'max' => 200000, 'fixed_amount' => 12500, 'tax_rate' => 16, 'description' => '16%'],
            ['bracket_number' => 4, 'min' => 200000, 'max' => 300000, 'fixed_amount' => 31250, 'tax_rate' => 18, 'description' => '18%'],
            ['bracket_number' => 5, 'min' => 300000, 'max' => 500000, 'fixed_amount' => 49250, 'tax_rate' => 19, 'description' => '19%'],
            ['bracket_number' => 6, 'min' => 500000, 'max' => 1000000, 'fixed_amount' => 87250, 'tax_rate' => 20, 'description' => '20%'],
            ['bracket_number' => 7, 'min' => 1000000, 'max' => 1500000, 'fixed_amount' => 187250, 'tax_rate' => 21, 'description' => '21%'],
            ['bracket_number' => 8, 'min' => 1500000, 'max' => 2000000, 'fixed_amount' => 292250, 'tax_rate' => 22, 'description' => '22%'],
            ['bracket_number' => 9, 'min' => 2000000, 'max' => 2500000, 'fixed_amount' => 402250, 'tax_rate' => 23, 'description' => '23%'],
            ['bracket_number' => 10, 'min' => 2500000, 'max' => 5000000, 'fixed_amount' => 517250, 'tax_rate' => 24, 'description' => '24%'],
            ['bracket_number' => 11, 'min' => 5000000, 'max' => 10000000, 'fixed_amount' => 1117250, 'tax_rate' => 24.5, 'description' => '24,5%'],
            ['bracket_number' => 12, 'min' => 10000000, 'max' => null, 'fixed_amount' => 2342250, 'tax_rate' => 25, 'description' => '25%'],
        ];
        
        foreach ($brackets as $bracket) {
            if ($income > $bracket['min'] && ($bracket['max'] === null || $income <= $bracket['max'])) {
                return (object) $bracket; // Return as object to maintain compatibility
            }
        }
        
        return null;
    }
}

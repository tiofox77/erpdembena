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
        
        // Escalões IRT Angola (conforme tabela oficial)
        $brackets = [
            // Escalão 1: Isento até 100.000 AOA
            ['min' => 0, 'max' => 100000, 'fixed' => 0, 'rate' => 0],
            // Escalão 2: 100.001 a 150.000 AOA - 13%
            ['min' => 100000, 'max' => 150000, 'fixed' => 0, 'rate' => 13],
            // Escalão 3: 150.001 a 200.000 AOA - 16%
            ['min' => 150000, 'max' => 200000, 'fixed' => 6500, 'rate' => 16],
            // Escalão 4: 200.001 a 300.000 AOA - 18%
            ['min' => 200000, 'max' => 300000, 'fixed' => 14500, 'rate' => 18],
            // Escalão 5: 300.001 a 500.000 AOA - 19%
            ['min' => 300000, 'max' => 500000, 'fixed' => 32500, 'rate' => 19],
            // Escalão 6: 500.001 a 1.000.000 AOA - 20%
            ['min' => 500000, 'max' => 1000000, 'fixed' => 70500, 'rate' => 20],
            // Escalão 7: 1.000.001 a 1.500.000 AOA - 21%
            ['min' => 1000000, 'max' => 1500000, 'fixed' => 170500, 'rate' => 21],
            // Escalão 8: Acima de 1.500.000 AOA - 21%
            ['min' => 1500000, 'max' => null, 'fixed' => 275500, 'rate' => 21],
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
        // Escalões IRT Angola (conforme tabela oficial)
        $brackets = [
            ['bracket_number' => 1, 'min' => 0, 'max' => 100000, 'fixed_amount' => 0, 'tax_rate' => 0, 'description' => 'Isento'],
            ['bracket_number' => 2, 'min' => 100000, 'max' => 150000, 'fixed_amount' => 0, 'tax_rate' => 13, 'description' => '13%'],
            ['bracket_number' => 3, 'min' => 150000, 'max' => 200000, 'fixed_amount' => 6500, 'tax_rate' => 16, 'description' => '16%'],
            ['bracket_number' => 4, 'min' => 200000, 'max' => 300000, 'fixed_amount' => 14500, 'tax_rate' => 18, 'description' => '18%'],
            ['bracket_number' => 5, 'min' => 300000, 'max' => 500000, 'fixed_amount' => 32500, 'tax_rate' => 19, 'description' => '19%'],
            ['bracket_number' => 6, 'min' => 500000, 'max' => 1000000, 'fixed_amount' => 70500, 'tax_rate' => 20, 'description' => '20%'],
            ['bracket_number' => 7, 'min' => 1000000, 'max' => 1500000, 'fixed_amount' => 170500, 'tax_rate' => 21, 'description' => '21%'],
            ['bracket_number' => 8, 'min' => 1500000, 'max' => null, 'fixed_amount' => 275500, 'tax_rate' => 21, 'description' => '21%'],
        ];
        
        foreach ($brackets as $bracket) {
            if ($income > $bracket['min'] && ($bracket['max'] === null || $income <= $bracket['max'])) {
                return (object) $bracket; // Return as object to maintain compatibility
            }
        }
        
        return null;
    }
}

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
     * Calculate IRT tax for a given income using the progressive tax brackets
     */
    public static function calculateIRT(float $income): float
    {
        if ($income <= 0) {
            return 0.0;
        }
        
        // Get active tax brackets ordered by bracket number
        $brackets = self::where('is_active', true)
            ->orderBy('bracket_number')
            ->get();
        
        $totalTax = 0.0;
        
        foreach ($brackets as $bracket) {
            // Skip if income is below this bracket's minimum
            if ($income <= $bracket->min_income) {
                continue;
            }
            
            // Determine the taxable amount for this bracket
            $taxableInThisBracket = 0.0;
            
            if ($bracket->max_income === null || $bracket->max_income == 0) {
                // This is the highest bracket (no upper limit)
                $taxableInThisBracket = $income - $bracket->min_income;
            } else {
                // Calculate taxable amount within this bracket's limits
                $taxableInThisBracket = min($income, $bracket->max_income) - $bracket->min_income;
            }
            
            // Only calculate tax if there's taxable income in this bracket
            if ($taxableInThisBracket > 0) {
                // Add fixed amount (if any) and percentage on excess
                $totalTax = $bracket->fixed_amount + ($taxableInThisBracket * ($bracket->tax_rate / 100));
                
                // If income fits entirely in this bracket, we're done
                if ($bracket->max_income === null || $income <= $bracket->max_income) {
                    break;
                }
            }
        }
        
        return round($totalTax, 2);
    }
    
    /**
     * Get the tax bracket that applies to a specific income
     */
    public static function getBracketForIncome(float $income): ?self
    {
        return self::where('is_active', true)
            ->where('min_income', '<=', $income)
            ->where(function ($query) use ($income) {
                $query->where('max_income', '>=', $income)
                      ->orWhereNull('max_income')
                      ->orWhere('max_income', 0);
            })
            ->orderBy('bracket_number')
            ->first();
    }
}

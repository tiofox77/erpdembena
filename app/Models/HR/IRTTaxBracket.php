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
     * Calculate IRT tax for a given income using tax brackets from DATABASE
     */
    public static function calculateIRT(float $income): float
    {
        if ($income <= 0) {
            return 0.0;
        }
        
        // Buscar bracket correto da tabela do banco de dados
        $bracket = static::getBracketForIncome($income);
        
        if (!$bracket) {
            return 0.0;
        }
        
        // Calcular IRT: PF + (taxa × excesso)
        $excess = max(0, $income - $bracket->min_income);
        $irt = $bracket->fixed_amount + ($excess * $bracket->tax_rate);
        
        return round($irt, 2);
    }
    
    /**
     * Get the tax bracket that applies to a specific income (FROM DATABASE)
     */
    public static function getBracketForIncome(float $income): ?IRTTaxBracket
    {
        return static::where('is_active', true)
            ->where('min_income', '<=', $income)
            ->where(function($query) use ($income) {
                $query->whereNull('max_income')
                      ->orWhere('max_income', '>=', $income);
            })
            ->orderBy('min_income', 'desc')
            ->first();
    }
}

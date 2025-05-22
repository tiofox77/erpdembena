<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\User;

class InventoryTransaction extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_inventory_transactions';

    protected $fillable = [
        'transaction_number',
        'transaction_type',
        'product_id',
        'source_location_id',
        'destination_location_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'batch_number',
        'expiry_date',
        'reference_id',
        'reference_type',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date'
    ];
    
    /**
     * Constants for transaction types
     */
    const TYPE_PURCHASE_RECEIPT = 'purchase_receipt';
    const TYPE_SALES_ISSUE = 'sales_issue';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_PRODUCTION = 'production';
    const TYPE_PRODUCTION_RECEIPT = 'production_receipt';
    const TYPE_PRODUCTION_ISSUE = 'production_issue';
    const TYPE_RAW_PRODUCTION = 'raw_production';
    const TYPE_PRODUCTION_ORDER = 'production_order';
    
    /**
     * Constants for adjustment actions
     */
    const ACTION_INCREASE = 'increase';
    const ACTION_DECREASE = 'decrease';
    const ACTION_SET = 'set';

    /**
     * Get the product associated with this transaction
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the source location for this transaction
     */
    public function sourceLocation()
    {
        return $this->belongsTo(InventoryLocation::class, 'source_location_id');
    }

    /**
     * Get the destination location for this transaction
     */
    public function destinationLocation()
    {
        return $this->belongsTo(InventoryLocation::class, 'destination_location_id');
    }

    /**
     * Get the user who created this transaction
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference object (polymorphic relation)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Get purchase receipts
     */
    public function scopePurchaseReceipts($query)
    {
        return $query->where('transaction_type', 'purchase_receipt');
    }

    /**
     * Get sales issues
     */
    public function scopeSalesIssues($query)
    {
        return $query->where('transaction_type', 'sales_issue');
    }

    /**
     * Get transfers
     */
    public function scopeTransfers($query)
    {
        return $query->where('transaction_type', 'transfer');
    }

    /**
     * Get adjustments
     */
    public function scopeAdjustments($query)
    {
        return $query->where('transaction_type', 'adjustment');
    }

    /**
     * Get transactions within a date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Generate a unique transaction number
     * 
     * @param string|null $type Optional transaction type prefix
     * @return string
     */
    public static function generateTransactionNumber($type = null)
    {
        $prefix = 'IT';
        $date = now()->format('ymd');
        
        // Use a database lock to prevent race conditions
        $maxNumber = 0;
        
        // Get the highest number for today to prevent duplicates
        $pattern = $prefix . $date . '%';
        $lastTransaction = self::where('transaction_number', 'like', $pattern)
            ->orderByRaw('CAST(SUBSTRING(transaction_number, -4) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->first();
        
        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->transaction_number, -4));
            $maxNumber = $lastNumber + 1;
        } else {
            $maxNumber = 1;
        }
        
        return $prefix . $date . str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total_cost before saving
     */
    protected static function booted()
    {
        static::saving(function ($transaction) {
            if ($transaction->quantity && $transaction->unit_cost) {
                $transaction->total_cost = $transaction->quantity * $transaction->unit_cost;
            }
        });
    }
    
    /**
     * Check if this transaction decreases stock
     */
    public function isStockDecrease()
    {
        if ($this->transaction_type === self::TYPE_ADJUSTMENT && $this->quantity < 0) {
            return true;
        }
        
        if ($this->transaction_type === self::TYPE_SALES_ISSUE) {
            return true;
        }
        
        // Source location loses stock in a transfer
        if ($this->transaction_type === self::TYPE_TRANSFER && $this->source_location_id) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if this transaction increases stock
     */
    public function isStockIncrease()
    {
        if ($this->transaction_type === self::TYPE_ADJUSTMENT && $this->quantity > 0) {
            return true;
        }
        
        if ($this->transaction_type === self::TYPE_PURCHASE_RECEIPT) {
            return true;
        }
        
        // Destination location gains stock in a transfer
        if ($this->transaction_type === self::TYPE_TRANSFER && $this->destination_location_id) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the background color class for this transaction type
     */
    public function getBackgroundColorClass()
    {
        if ($this->isStockDecrease()) {
            return 'bg-red-50 hover:bg-red-100';
        }
        
        if ($this->isStockIncrease()) {
            return 'bg-green-50 hover:bg-green-100';
        }
        
        if ($this->transaction_type === self::TYPE_TRANSFER) {
            return 'bg-indigo-50 hover:bg-indigo-100';
        }
        
        if ($this->transaction_type === self::TYPE_PRODUCTION) {
            return 'bg-purple-50 hover:bg-purple-100';
        }
        
        return '';
    }
    
    /**
     * Get the icon for this transaction type
     */
    public function getIcon()
    {
        switch ($this->transaction_type) {
            case self::TYPE_ADJUSTMENT:
                return $this->quantity > 0 ? 'fa-plus-circle' : 'fa-minus-circle';
            case self::TYPE_PURCHASE_RECEIPT:
                return 'fa-truck-loading';
            case self::TYPE_SALES_ISSUE:
                return 'fa-shopping-cart';
            case self::TYPE_TRANSFER:
                return 'fa-exchange-alt';
            case self::TYPE_PRODUCTION:
                return 'fa-industry';
            default:
                return 'fa-box';
        }
    }
    
    /**
     * Get the color class for the icon based on transaction type
     */
    public function getIconColorClass()
    {
        if ($this->isStockDecrease()) {
            return 'text-red-600';
        }
        
        if ($this->isStockIncrease()) {
            return 'text-green-600';
        }
        
        if ($this->transaction_type === self::TYPE_TRANSFER) {
            return 'text-indigo-600';
        }
        
        if ($this->transaction_type === self::TYPE_PRODUCTION) {
            return 'text-purple-600';
        }
        
        return 'text-gray-600';
    }
}

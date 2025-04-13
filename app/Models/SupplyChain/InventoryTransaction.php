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
     */
    public static function generateTransactionNumber()
    {
        $prefix = 'IT';
        $date = now()->format('ymd');
        $lastTransaction = self::orderBy('id', 'desc')->first();
        
        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->transaction_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
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
}

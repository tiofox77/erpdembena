<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\PurchaseOrderItem;
use App\Models\SupplyChain\GoodsReceipt;
use App\Models\User;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_purchase_orders';

    protected $fillable = [
        'order_number',
        'supplier_id',
        'created_by',
        'approved_by',
        'status',
        'order_date',
        'expected_delivery_date',
        'delivery_date',
        'shipping_method',
        'shipping_terms',
        'payment_terms',
        'currency',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'shipping_address_id',
        'notes',
        'internal_notes',
        'reference_number'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Get the supplier that the purchase order belongs to
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who created the purchase order
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the purchase order
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the items for this purchase order
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the goods receipts for this purchase order
     */
    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    /**
     * Get draft purchase orders
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get pending approval purchase orders
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Get approved purchase orders
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get ordered purchase orders
     */
    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    /**
     * Get purchase orders with partial receipts
     */
    public function scopePartiallyReceived($query)
    {
        return $query->where('status', 'partially_received');
    }

    /**
     * Get completed purchase orders
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get cancelled purchase orders
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get overdue purchase orders
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['approved', 'ordered', 'partially_received'])
                     ->whereNotNull('expected_delivery_date')
                     ->where('expected_delivery_date', '<', now());
    }

    /**
     * Calculate the receipt percentage
     */
    public function getReceiptPercentageAttribute()
    {
        $totalExpected = $this->items->sum('quantity');
        $totalReceived = $this->items->sum('received_quantity');
        
        if ($totalExpected > 0) {
            return min(100, round(($totalReceived / $totalExpected) * 100));
        }
        
        return 0;
    }

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber()
    {
        $prefix = 'PO';
        $date = now()->format('ymd');
        $lastOrder = self::orderBy('id', 'desc')->first();
        
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate totals before saving
     */
    protected static function booted()
    {
        static::saving(function ($purchaseOrder) {
            $purchaseOrder->recalculateTotals();
        });
    }

    /**
     * Recalculate order totals based on items
     */
    public function recalculateTotals()
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        
        // Calculate final total
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_amount - $this->discount_amount;
        
        return $this;
    }
}

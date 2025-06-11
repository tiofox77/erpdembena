<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\GoodsReceiptItem;

class PurchaseOrderItem extends Model
{
    use HasFactory;
    
    protected $table = 'sc_purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'description',
        'quantity',
        'received_quantity',
        'unit_of_measure',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'line_total',
        'expected_delivery_date',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'expected_delivery_date' => 'date'
    ];

    /**
     * Get the purchase order that owns the item
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product that this item is for
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the goods receipt items linked to this purchase order item
     */
    public function receiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Get the receipt percentage for this item
     */
    public function getReceiptPercentageAttribute()
    {
        if ($this->quantity > 0) {
            return min(100, round(($this->received_quantity / $this->quantity) * 100));
        }
        
        return 0;
    }

    /**
     * Check if the item is fully received
     */
    public function getIsFullyReceivedAttribute()
    {
        return $this->received_quantity >= $this->quantity;
    }

    /**
     * Check if the item is partially received
     */
    public function getIsPartiallyReceivedAttribute()
    {
        return $this->received_quantity > 0 && $this->received_quantity < $this->quantity;
    }

    /**
     * Get the remaining quantity to be received
     */
    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity - $this->received_quantity);
    }

    /**
     * Update received quantity for a partial receipt
     * 
     * @param float $quantity
     * @return array
     */
    public function receiveQuantity($quantity)
    {
        $remaining = $this->remaining_quantity;
        
        // If trying to receive more than remaining, adjust to remaining
        $toReceive = min($quantity, $remaining);
        
        // Update received quantity
        $this->received_quantity += $toReceive;
        $this->save();
        
        // Check if fully received
        $isFullyReceived = $this->remaining_quantity <= 0;
        
        return [
            'received' => $toReceive,
            'remaining' => $this->remaining_quantity,
            'is_partial' => $toReceive < $remaining,
            'is_complete' => $isFullyReceived
        ];
    }

    /**
     * Calculate line total, tax and discount before saving
     */
    protected static function booted()
    {
        static::saving(function ($orderItem) {
            // Calculate discount amount
            if ($orderItem->discount_rate > 0) {
                $orderItem->discount_amount = $orderItem->quantity * $orderItem->unit_price * ($orderItem->discount_rate / 100);
            }
            
            // Calculate subtotal (quantity * unit_price - discount)
            $subtotal = ($orderItem->quantity * $orderItem->unit_price) - $orderItem->discount_amount;
            
            // Calculate tax amount
            if ($orderItem->tax_rate > 0) {
                $orderItem->tax_amount = $subtotal * ($orderItem->tax_rate / 100);
            }
            
            // Calculate line total
            $orderItem->line_total = $subtotal + $orderItem->tax_amount;
        });
    }
}

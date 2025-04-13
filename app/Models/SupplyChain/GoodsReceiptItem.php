<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SupplyChain\GoodsReceipt;
use App\Models\SupplyChain\PurchaseOrderItem;
use App\Models\SupplyChain\Product;

class GoodsReceiptItem extends Model
{
    use HasFactory;
    
    protected $table = 'sc_goods_receipt_items';

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'expected_quantity',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'rejection_reason',
        'batch_number',
        'expiry_date',
        'serial_numbers',
        'status',
        'unit_cost',
        'notes'
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:2',
        'received_quantity' => 'decimal:2',
        'accepted_quantity' => 'decimal:2',
        'rejected_quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date'
    ];

    /**
     * Get the goods receipt that owns the item
     */
    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    /**
     * Get the purchase order item associated with this receipt item
     */
    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * Get the product for this receipt item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get items with accepted status
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Get items with partially accepted status
     */
    public function scopePartiallyAccepted($query)
    {
        return $query->where('status', 'partially_accepted');
    }

    /**
     * Get items with rejected status
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get items pending quality assurance
     */
    public function scopePendingQA($query)
    {
        return $query->where('status', 'pending_qa');
    }

    /**
     * Get the total value of the receipt item
     */
    public function getTotalValueAttribute()
    {
        return $this->accepted_quantity * $this->unit_cost;
    }

    /**
     * Get the rejection percentage
     */
    public function getRejectionPercentageAttribute()
    {
        if ($this->received_quantity > 0) {
            return round(($this->rejected_quantity / $this->received_quantity) * 100, 2);
        }
        
        return 0;
    }

    /**
     * Check if this item has any rejections
     */
    public function getHasRejectionsAttribute()
    {
        return $this->rejected_quantity > 0;
    }

    /**
     * Get the discrepancy between expected and received quantity
     */
    public function getQuantityDiscrepancyAttribute()
    {
        if ($this->expected_quantity) {
            return $this->received_quantity - $this->expected_quantity;
        }
        
        return 0;
    }

    /**
     * Check if this item has a quantity discrepancy
     */
    public function getHasDiscrepancyAttribute()
    {
        return $this->expected_quantity && $this->quantity_discrepancy != 0;
    }
}

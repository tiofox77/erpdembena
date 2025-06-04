<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\SupplyChain\GoodsReceiptItem;
use App\Models\User;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_goods_receipts';

    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'supplier_id',
        'location_id',
        'received_by',
        'receipt_date',
        'delivery_note_number',
        'carrier',
        'tracking_number',
        'status',
        'notes'
    ];

    protected $casts = [
        'receipt_date' => 'date'
    ];

    /**
     * Get the purchase order associated with this receipt
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the supplier for this receipt
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the inventory location for this receipt
     */
    public function location()
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    /**
     * Get the user who received this goods receipt
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the items for this goods receipt
     */
    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    /**
     * Get pending goods receipts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get partially processed goods receipts
     */
    public function scopePartiallyProcessed($query)
    {
        return $query->where('status', 'partially_processed');
    }

    /**
     * Get completed goods receipts
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get receipts with discrepancies
     */
    public function scopeWithDiscrepancy($query)
    {
        return $query->where('status', 'discrepancy');
    }

    /**
     * Get receipts from a specific date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('receipt_date', [$from, $to]);
    }

    /**
     * Generate a unique receipt number
     */
    public static function generateReceiptNumber()
    {
        $prefix = 'GR';
        $date = now()->format('ymd');
        $lastReceipt = self::orderBy('id', 'desc')->first();
        
        if ($lastReceipt) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get total quantity received
     */
    public function getTotalQuantityReceivedAttribute()
    {
        return $this->items->sum('received_quantity');
    }

    /**
     * Get total quantity accepted
     */
    public function getTotalQuantityAcceptedAttribute()
    {
        return $this->items->sum('accepted_quantity');
    }

    /**
     * Get total quantity rejected
     */
    public function getTotalQuantityRejectedAttribute()
    {
        return $this->items->sum('rejected_quantity');
    }

    /**
     * Check if receipt has any rejections
     */
    public function getHasRejectionsAttribute()
    {
        return $this->total_quantity_rejected > 0;
    }
}

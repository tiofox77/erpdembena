<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransferRequestItem extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_warehouse_transfer_request_items';
    
    protected $fillable = [
        'transfer_request_id',
        'product_id',
        'quantity_requested',
        'quantity_approved',
        'quantity_transferred',
        'notes',
        'unit_cost',
        'status'
    ];
    
    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_approved' => 'decimal:2',
        'quantity_transferred' => 'decimal:2',
        'unit_cost' => 'decimal:2'
    ];
    
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETED = 'completed';
    
    /**
     * Get the transfer request that owns this item
     */
    public function transferRequest()
    {
        return $this->belongsTo(WarehouseTransferRequest::class, 'transfer_request_id');
    }
    
    /**
     * Get the product for this item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get total value of this item
     */
    public function getTotalValueAttribute()
    {
        return $this->quantity_requested * ($this->unit_cost ?? $this->product->cost_price ?? 0);
    }
    
    /**
     * Get color class based on status
     */
    public function getStatusColorClass()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'bg-yellow-100 text-yellow-800';
            case self::STATUS_APPROVED:
                return 'bg-green-100 text-green-800';
            case self::STATUS_REJECTED:
                return 'bg-red-100 text-red-800';
            case self::STATUS_PARTIAL:
                return 'bg-blue-100 text-blue-800';
            case self::STATUS_COMPLETED:
                return 'bg-indigo-100 text-indigo-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
}

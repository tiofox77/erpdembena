<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'equipment_part_id',
        'quantity',
        'type',
        'unit_cost',
        'supplier',
        'supplier_id',
        'invoice_number',
        'transaction_date',
        'notes',
        'created_by',
        'work_order_id',
        'maintenance_request_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'transaction_date' => 'datetime',
        'unit_cost' => 'decimal:2',
    ];

    /**
     * Get the part associated with the transaction.
     */
    public function part()
    {
        return $this->belongsTo(EquipmentPart::class, 'equipment_part_id');
    }

    /**
     * Get the user who created the transaction.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Alias for createdBy to maintain compatibility with existing code.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the work order associated with the transaction, if any.
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * Get the maintenance request associated with the transaction, if any.
     */
    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_request_id');
    }
}

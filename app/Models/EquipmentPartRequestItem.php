<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentPartRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'equipment_part_id',
        'quantity_required',
        'unit',
        'supplier_reference',
    ];

    /**
     * Get the request that owns the item.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(EquipmentPartRequest::class, 'request_id');
    }

    /**
     * Get the equipment part associated with the item.
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(EquipmentPart::class, 'equipment_part_id');
    }
}

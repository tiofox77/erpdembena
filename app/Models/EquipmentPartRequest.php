<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentPartRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment_part_requests';

    protected $fillable = [
        'reference_number',
        'suggested_vendor',
        'delivery_date',
        'remarks',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the items for this request.
     */
    public function items(): HasMany
    {
        return $this->hasMany(EquipmentPartRequestItem::class, 'request_id');
    }

    /**
     * Get the images for this request.
     */
    public function images(): HasMany
    {
        return $this->hasMany(EquipmentPartRequestImage::class, 'request_id');
    }

    /**
     * Get the user who requested the part.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

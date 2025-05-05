<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Technician extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'gender',
        'age',
        'line_id',
        'area_id',
        'function'
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    /**
     * Get the line that the technician belongs to
     */
    public function line(): BelongsTo
    {
        return $this->belongsTo(MaintenanceLine::class, 'line_id');
    }

    /**
     * Get the area that the technician belongs to
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(MaintenanceArea::class, 'area_id');
    }
}

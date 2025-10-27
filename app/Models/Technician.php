<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{
    use HasFactory, SoftDeletes;
    
    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ON_LEAVE = 'on_leave';

    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'gender',
        'age',
        'line_id',
        'area_id',
        'function',
        'user_id',
        'status'
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

    /**
     * Get the user associated with the technician
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the maintenance tasks assigned to this technician
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class, 'technician_id');
    }

    /**
     * Get the maintenance task logs created by this technician
     */
    public function taskLogs(): HasMany
    {
        return $this->hasMany(MaintenanceTaskLog::class, 'technician_id');
    }


}

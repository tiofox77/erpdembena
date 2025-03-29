<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailureMode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that this failure mode belongs to.
     */
    public function category()
    {
        return $this->belongsTo(FailureModeCategory::class, 'category_id');
    }

    /**
     * Get the corrective maintenance records associated with this failure mode.
     */
    public function correctiveMaintenances()
    {
        return $this->hasMany(MaintenanceCorrective::class, 'failure_mode_id');
    }

    /**
     * Scope a query to only include active failure modes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

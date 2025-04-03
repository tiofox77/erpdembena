<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'break_duration',
        'description',
        'is_night_shift',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break_duration' => 'integer', // in minutes
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the shift assignments
     */
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * Calculate shift duration
     */
    public function getDurationAttribute()
    {
        if ($this->start_time && $this->end_time) {
            return $this->end_time->diffInMinutes($this->start_time) - $this->break_duration;
        }
        return 0;
    }
}

<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'start_date',
        'end_date',
        'is_permanent',
        'rotation_pattern',
        'assigned_by',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_permanent' => 'boolean',
        'rotation_pattern' => 'json',
    ];

    /**
     * Get the employee that the shift is assigned to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the employee who assigned the shift
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_by');
    }
    
    /**
     * Check if this assignment has rotation
     */
    public function hasRotation(): bool
    {
        return !empty($this->rotation_pattern) && is_array($this->rotation_pattern);
    }
    
    /**
     * Get rotation type (daily, weekly, monthly, yearly)
     */
    public function getRotationType(): ?string
    {
        if (!$this->hasRotation()) {
            return null;
        }
        
        return $this->rotation_pattern['type'] ?? null;
    }
    
    /**
     * Get rotation interval (e.g., every 2 weeks, every 3 days)
     */
    public function getRotationInterval(): int
    {
        if (!$this->hasRotation()) {
            return 1;
        }
        
        return $this->rotation_pattern['interval'] ?? 1;
    }
    
    /**
     * Get shifts in rotation pattern
     */
    public function getRotationShifts(): array
    {
        if (!$this->hasRotation()) {
            return [$this->shift_id];
        }
        
        return $this->rotation_pattern['shifts'] ?? [$this->shift_id];
    }
    
    /**
     * Calculate which shift should be active on a given date
     */
    public function getActiveShiftForDate(\Carbon\Carbon $date): int
    {
        if (!$this->hasRotation()) {
            return $this->shift_id;
        }
        
        $rotationType = $this->getRotationType();
        $rotationInterval = $this->getRotationInterval();
        $shifts = $this->getRotationShifts();
        $startDate = $this->start_date;
        
        // Calculate days since start of rotation
        $daysSinceStart = $startDate->diffInDays($date);
        
        switch ($rotationType) {
            case 'daily':
                $cycleLength = count($shifts) * $rotationInterval;
                $currentPosition = floor($daysSinceStart / $rotationInterval) % count($shifts);
                break;
                
            case 'weekly':
                $weeksSinceStart = floor($daysSinceStart / 7);
                $cycleLength = count($shifts) * $rotationInterval;
                $currentPosition = floor($weeksSinceStart / $rotationInterval) % count($shifts);
                break;
                
            case 'monthly':
                $monthsSinceStart = $startDate->diffInMonths($date);
                $currentPosition = floor($monthsSinceStart / $rotationInterval) % count($shifts);
                break;
                
            case 'yearly':
                $yearsSinceStart = $startDate->diffInYears($date);
                $currentPosition = floor($yearsSinceStart / $rotationInterval) % count($shifts);
                break;
                
            default:
                $currentPosition = 0;
        }
        
        return $shifts[$currentPosition] ?? $this->shift_id;
    }
    
    /**
     * Get next rotation date
     */
    public function getNextRotationDate(\Carbon\Carbon $fromDate = null): ?\Carbon\Carbon
    {
        if (!$this->hasRotation()) {
            return null;
        }
        
        $fromDate = $fromDate ?? \Carbon\Carbon::now();
        $rotationType = $this->getRotationType();
        $rotationInterval = $this->getRotationInterval();
        $startDate = $this->start_date;
        
        switch ($rotationType) {
            case 'daily':
                $daysSinceStart = $startDate->diffInDays($fromDate);
                $nextRotationDays = (floor($daysSinceStart / $rotationInterval) + 1) * $rotationInterval;
                return $startDate->copy()->addDays($nextRotationDays);
                
            case 'weekly':
                $weeksSinceStart = floor($startDate->diffInDays($fromDate) / 7);
                $nextRotationWeeks = (floor($weeksSinceStart / $rotationInterval) + 1) * $rotationInterval;
                return $startDate->copy()->addWeeks($nextRotationWeeks);
                
            case 'monthly':
                $monthsSinceStart = $startDate->diffInMonths($fromDate);
                $nextRotationMonths = (floor($monthsSinceStart / $rotationInterval) + 1) * $rotationInterval;
                return $startDate->copy()->addMonths($nextRotationMonths);
                
            case 'yearly':
                $yearsSinceStart = $startDate->diffInYears($fromDate);
                $nextRotationYears = (floor($yearsSinceStart / $rotationInterval) + 1) * $rotationInterval;
                return $startDate->copy()->addYears($nextRotationYears);
                
            default:
                return null;
        }
    }
    
    /**
     * Get rotation summary for display
     */
    public function getRotationSummary(): array
    {
        if (!$this->hasRotation()) {
            return [
                'has_rotation' => false,
                'type' => null,
                'interval' => null,
                'shifts_count' => 1,
                'next_rotation' => null,
                'current_shift_id' => $this->shift_id,
            ];
        }
        
        $today = \Carbon\Carbon::now();
        
        return [
            'has_rotation' => true,
            'type' => $this->getRotationType(),
            'interval' => $this->getRotationInterval(),
            'shifts_count' => count($this->getRotationShifts()),
            'next_rotation' => $this->getNextRotationDate($today),
            'current_shift_id' => $this->getActiveShiftForDate($today),
            'is_permanent' => $this->is_permanent,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];
    }
}

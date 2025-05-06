<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Technician;
use App\Models\User;

class MaintenanceCorrective extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenance_correctives';

    protected $fillable = [
        'year',
        'month',
        'week',
        'system_process',
        'equipment_id',
        'failure_mode_id',
        'failure_cause_id',
        'start_time',
        'end_time',
        'downtime_length',
        'description',
        'actions_taken',
        'reported_by',
        'resolved_by',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_downtime',
    ];

    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    /**
     * Get list of possible statuses with translations
     * 
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_OPEN => __('messages.open'),
            self::STATUS_IN_PROGRESS => __('messages.in_progress'),
            self::STATUS_RESOLVED => __('messages.resolved'),
            self::STATUS_CLOSED => __('messages.closed'),
        ];
    }

    // Relationships
    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'equipment_id');
    }

    public function failureMode()
    {
        return $this->belongsTo(FailureMode::class, 'failure_mode_id');
    }

    public function failureCause()
    {
        return $this->belongsTo(FailureCause::class, 'failure_cause_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver()
    {
        return $this->belongsTo(Technician::class, 'resolved_by');
    }

    public function tasks()
    {
        return $this->hasMany(MaintenanceTask::class, 'corrective_id');
    }

    // Scopes
    public function scopeFilterByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }

        return $query;
    }

    public function scopeFilterByEquipment($query, $equipmentId)
    {
        if ($equipmentId) {
            return $query->where('equipment_id', $equipmentId);
        }

        return $query;
    }

    public function scopeFilterByYear($query, $year)
    {
        if ($year) {
            return $query->where('year', $year);
        }

        return $query;
    }

    public function scopeFilterByMonth($query, $month)
    {
        if ($month) {
            return $query->where('month', $month);
        }

        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('system_process', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('actions_taken', 'like', "%{$search}%")
                  ->orWhereHas('failureMode', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('failureCause', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    // Accessors and Mutators
    public function getFormattedDowntimeAttribute()
    {
        if (!$this->downtime_length) {
            return '00:00:00';
        }

        // If downtime_length is already in hours:minutes:seconds format, return it
        if (strpos($this->downtime_length, ':') !== false) {
            return $this->downtime_length;
        }

        // If downtime_length is a number (hours), convert to hours:minutes:seconds
        $hours = floor($this->downtime_length);
        $minutes = floor(($this->downtime_length - $hours) * 60);
        $seconds = floor((($this->downtime_length - $hours) * 60 - $minutes) * 60);

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function calculateDowntimeLength()
    {
        if ($this->start_time && $this->end_time) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            $diffInHours = $end->diffInSeconds($start) / 3600; // Convert seconds to hours
            $this->downtime_length = number_format($diffInHours, 2);
        }
    }

    // Status helpers
    public function isOpen()
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isClosed()
    {
        return $this->status === self::STATUS_CLOSED;
    }

    // Helper accessors for backward compatibility
    public function getFailureModeAttribute()
    {
        if ($this->relationLoaded('failureMode')) {
            $mode = $this->getRelation('failureMode');
            return $mode ? $mode->name : null;
        }

        $mode = $this->failureMode()->first();
        return $mode ? $mode->name : null;
    }

    public function getFailureModeNameAttribute()
    {
        if ($this->relationLoaded('failureMode')) {
            $mode = $this->getRelation('failureMode');
            return $mode ? $mode->name : null;
        }

        $mode = $this->failureMode()->first();
        return $mode ? $mode->name : null;
    }

    public function getFailureModeCategoryAttribute()
    {
        if ($this->relationLoaded('failureMode')) {
            $mode = $this->getRelation('failureMode');
            if ($mode && $mode->relationLoaded('category')) {
                $category = $mode->getRelation('category');
                return $category ? $category->name : null;
            }
        }

        $mode = $this->failureMode()->with('category')->first();
        return ($mode && $mode->category) ? $mode->category->name : null;
    }

    public function getFailureCauseNameAttribute()
    {
        if ($this->relationLoaded('failureCause')) {
            $cause = $this->getRelation('failureCause');
            return $cause ? $cause->name : null;
        }

        $cause = $this->failureCause()->first();
        return $cause ? $cause->name : null;
    }

    public function getFailureCauseCategoryAttribute()
    {
        if ($this->relationLoaded('failureCause')) {
            $cause = $this->getRelation('failureCause');
            if ($cause && $cause->relationLoaded('category')) {
                $category = $cause->getRelation('category');
                return $category ? $category->name : null;
            }
        }

        $cause = $this->failureCause()->with('category')->first();
        return ($cause && $cause->category) ? $cause->category->name : null;
    }
}
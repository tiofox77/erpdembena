<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MaintenanceTaskLog extends Model
{
    use HasFactory;

    protected $table = 'maintenance_task_logs';

    protected $fillable = [
        'task_id',
        'equipment_id',
        'user_id',
        'maintenance_plan_id',
        'date_performed',
        'scheduled_date',
        'completed_at',
        'duration_minutes',
        'notes',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'date_performed' => 'datetime',
        'scheduled_date' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relacionamentos
    public function task()
    {
        return $this->belongsTo(MaintenanceTask::class, 'task_id');
    }

    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'equipment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function maintenancePlan()
    {
        return $this->belongsTo(MaintenancePlan::class, 'maintenance_plan_id');
    }

    // Métodos auxiliares
    public function getFormattedDateAttribute()
    {
        return $this->date_performed ? $this->date_performed->format('d/m/Y') : null;
    }

    public function getFormattedTimeAttribute()
    {
        return $this->date_performed ? $this->date_performed->format('H:i') : null;
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration_minutes) {
            return null;
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }

        return "{$minutes}min";
    }
}

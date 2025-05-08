<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceTask extends Model
{
    use HasFactory, SoftDeletes;
    
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    
    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';
    
    // Type constants
    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_PREDICTIVE = 'predictive';
    const TYPE_OTHER = 'other';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenance_tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'maintenance_equipment_id',
        'maintenance_plan_id',
        'assigned_to',      // Usuário (legacy)
        'technician_id',   // Novo campo para relacionamento direto com técnico
        'start_date',
        'due_date',
        'completed_date',
        'status',
        'priority',
        'type',
        'notes',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_date' => 'datetime',
        'due_date' => 'datetime',
        'completed_date' => 'datetime',
    ];
    
    /**
     * Get the equipment this task is for
     */
    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'maintenance_equipment_id');
    }
    
    /**
     * Get the maintenance plan this task belongs to
     */
    public function plan()
    {
        return $this->belongsTo(MaintenancePlan::class, 'maintenance_plan_id');
    }
    
    /**
     * Get the user this task is assigned to (legacy relationship)
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    /**
     * Get the technician this task is assigned to (new relationship)
     */
    public function technician()
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }
    
    /**
     * Get the logs for this task
     */
    public function logs()
    {
        return $this->hasMany(MaintenanceTaskLog::class, 'maintenance_task_id');
    }
    
    /**
     * Get the notes for this task
     */
    public function notes()
    {
        return $this->hasMany(MaintenanceNote::class, 'maintenance_task_id');
    }
    
    /**
     * Get the user who created this task
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Scope a query to only include tasks assigned to a specific technician
     */
    public function scopeAssignedToTechnician($query, $technicianId)
    {
        return $query->where('technician_id', $technicianId);
    }
    
    /**
     * Scope a query to only include tasks with a specific status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope a query to only include overdue tasks
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->where('status', '!=', self::STATUS_COMPLETED)
                     ->where('status', '!=', self::STATUS_CANCELLED);
    }
}

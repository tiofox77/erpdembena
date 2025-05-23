<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceNote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'maintenance_plan_id',
        'maintenance_task_id',
        'note_date', // Campo para a data específica da nota
        'status',
        'notes',
        'file_path',
        'file_name',
        'user_id',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'note_date' => 'date',
    ];

    /**
     * Get the maintenance plan that owns the note.
     */
    public function maintenancePlan()
    {
        return $this->belongsTo(MaintenancePlan::class);
    }

    /**
     * Get the user who created the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the maintenance task related to this note.
     */
    public function maintenanceTask()
    {
        return $this->belongsTo(MaintenanceTask::class, 'maintenance_task_id');
    }
}

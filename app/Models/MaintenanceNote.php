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
        'status',
        'notes',
        'file_path',
        'file_name',
        'user_id',
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
}

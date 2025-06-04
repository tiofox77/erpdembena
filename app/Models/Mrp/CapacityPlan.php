<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\HR\Department;

class CapacityPlan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'capacity_plans';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'resource_id',
        'resource_type_id',
        'department_id',
        'location_id',
        'start_date',
        'end_date',
        'planned_capacity',
        'actual_capacity',
        'capacity_uom',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'available_capacity' => 'decimal:2',
        'planned_capacity' => 'decimal:2',
        'capacity_utilization' => 'decimal:2',
        'efficiency_factor' => 'decimal:2',
    ];

    /**
     * Calculate the remaining capacity
     */
    public function getRemainingCapacityAttribute()
    {
        return max(0, $this->available_capacity - $this->planned_capacity);
    }

    /**
     * Calculate the effective capacity after applying efficiency factor
     */
    public function getEffectiveCapacityAttribute()
    {
        return $this->available_capacity * ($this->efficiency_factor / 100);
    }

    /**
     * Get the user that created the capacity plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the capacity plan.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Relacionamento com o recurso
     */
    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }
    
    /**
     * Relacionamento com o tipo de recurso
     */
    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class, 'resource_type_id');
    }
    
    /**
     * Relacionamento com o departamento
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    
    /**
     * Relacionamento com a localização
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    
    /**
     * Get the user that created this record
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user that last updated this record
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MaintenanceArea;
use App\Models\SupplyChain\InventoryLocation as Location;

class Resource extends Model
{
    use HasFactory;

    protected $table = 'resources';
    
    protected $fillable = [
        'name',
        'description',
        'resource_type_id',
        'department_id',
        'location_id',
        'capacity',
        'capacity_uom',
        'efficiency_factor',
        'active'
    ];

    /**
     * Relacionamento com o tipo de recurso
     */
    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class);
    }
    
    /**
     * Relacionamento com departamento (using maintenance area)
     */
    public function department()
    {
        return $this->belongsTo(MaintenanceArea::class, 'department_id');
    }
    
    /**
     * Relacionamento com localização
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    
    /**
     * Relacionamento com planos de capacidade
     */
    public function capacityPlans()
    {
        return $this->hasMany(CapacityPlan::class);
    }
}

<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceType extends Model
{
    use HasFactory;

    protected $table = 'resource_types';
    
    protected $fillable = [
        'name',
        'description',
        'active'
    ];

    /**
     * Relacionamento com os recursos deste tipo
     */
    public function resources()
    {
        return $this->hasMany(Resource::class);
    }
    
    /**
     * Relacionamento com planos de capacidade
     */
    public function capacityPlans()
    {
        return $this->hasMany(CapacityPlan::class);
    }
}

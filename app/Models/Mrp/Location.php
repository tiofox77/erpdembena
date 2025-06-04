<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'locations';
    
    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'active'
    ];

    /**
     * Relacionamento com recursos nesta localização
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

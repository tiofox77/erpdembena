<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Line extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mrp_lines';

    protected $fillable = [
        'name',
        'code',
        'description',
        'capacity_per_hour',
        'is_active',
        'location_id',
        'department_id',
        'manager_id',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity_per_hour' => 'float'
    ];

    /**
     * Relação com a localização
     */
    public function location()
    {
        return $this->belongsTo('App\Models\SupplyChain\InventoryLocation', 'location_id');
    }

    /**
     * Relação com o departamento
     */
    public function department()
    {
        return $this->belongsTo('App\Models\HR\Department', 'department_id');
    }

    /**
     * Relação com o gerente responsável
     */
    public function manager()
    {
        return $this->belongsTo('App\Models\User', 'manager_id');
    }

    /**
     * Relação com o usuário que criou a linha
     */
    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    /**
     * Relação com o usuário que atualizou a linha
     */
    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }

    /**
     * Relação com os turnos associados à linha
     */
    public function shifts()
    {
        return $this->belongsToMany('App\Models\Mrp\Shift', 'mrp_line_shifts', 'line_id', 'shift_id')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    /**
     * Relação com as ordens de produção
     */
    public function productionOrders()
    {
        return $this->hasMany('App\Models\Mrp\ProductionOrder', 'line_id');
    }

    /**
     * Relação com os agendamentos de produção
     */
    public function productionSchedules()
    {
        return $this->hasMany('App\Models\Mrp\ProductionSchedule', 'line_id');
    }

    /**
     * Escopo para linhas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

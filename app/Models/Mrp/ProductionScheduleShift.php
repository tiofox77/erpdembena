<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductionScheduleShift extends Pivot
{
    use SoftDeletes;

    protected $table = 'mrp_production_schedule_shift';
    
    protected $fillable = [
        'production_schedule_id',
        'shift_id'
    ];
    
    protected $dates = ['deleted_at'];
    
    /**
     * Get the production schedule that owns the pivot record.
     */
    public function productionSchedule()
    {
        return $this->belongsTo(ProductionSchedule::class, 'production_schedule_id');
    }
    
    /**
     * Get the shift that owns the pivot record.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
    
    // Removidos os métodos createdBy e updatedBy pois não temos essas colunas na tabela
}

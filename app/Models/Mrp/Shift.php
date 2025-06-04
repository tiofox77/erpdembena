<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Mrp\ProductionScheduleShift;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mrp_shifts';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'description',
        'color_code',
        'is_active',
        'break_start',
        'break_end',
        'working_days',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'working_days' => 'array'
    ];

    /**
     * Relação com o usuário que criou o turno
     */
    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    /**
     * Relação com o usuário que atualizou o turno
     */
    public function updatedBy()
    {
        return $this->belongsTo('App\Models\User', 'updated_by');
    }
    
    /**
     * Relação muitos-para-muitos com ProductionSchedule
     */
    public function productionSchedules()
    {
        return $this->belongsToMany(
            'App\Models\Mrp\ProductionSchedule',
            'mrp_production_schedule_shift',
            'shift_id',
            'production_schedule_id'
        )->withTimestamps()
         ->withPivot(['created_by', 'updated_by'])
         ->using(ProductionScheduleShift::class);
    }

    /**
     * Calcular a duração do turno em horas
     */
    public function getDurationAttribute()
    {
        if (empty($this->start_time) || empty($this->end_time)) {
            return 0;
        }

        $start = \Carbon\Carbon::createFromFormat('H:i', $this->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i', $this->end_time);

        // Se o turno terminar no dia seguinte
        if ($end < $start) {
            $end->addDay();
        }

        // Subtrair tempo de intervalo se existir
        $breakDuration = 0;
        if (!empty($this->break_start) && !empty($this->break_end)) {
            $breakStart = \Carbon\Carbon::createFromFormat('H:i', $this->break_start);
            $breakEnd = \Carbon\Carbon::createFromFormat('H:i', $this->break_end);
            $breakDuration = $breakEnd->diffInMinutes($breakStart) / 60;
        }

        return $end->diffInMinutes($start) / 60 - $breakDuration;
    }

    /**
     * Obter dias de trabalho formatados para exibição
     */
    public function getWorkingDaysLabelAttribute()
    {
        $days = is_array($this->working_days) ? $this->working_days : json_decode($this->working_days, true);
        
        if (!is_array($days) || empty($days)) {
            return '';
        }

        $result = [];
        foreach ($days as $day) {
            // Usar a função de tradução do Laravel para obter o nome localizado do dia
            $translationKey = 'messages.' . $day;
            $result[] = __($translationKey);
        }

        return implode(', ', $result);
    }
}

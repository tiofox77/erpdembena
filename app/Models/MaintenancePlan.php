<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Technician;

class MaintenancePlan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenance_plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'equipment_id',
        'line_id',
        'area_id',
        'scheduled_date',
        'frequency_type',
        'custom_days',
        'day_of_week',
        'day_of_month',
        'month_day',
        'month',
        'priority',
        'type',
        'assigned_to',
        'description',
        'notes',
        'status',
        'next_maintenance_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_date' => 'datetime',
        'next_maintenance_date' => 'datetime',
        'last_maintenance_date' => 'datetime',
    ];

    /**
     * Get the equipment that this plan is for.
     */
    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'equipment_id');
    }

    /**
     * Get the line for this plan.
     */
    public function line()
    {
        return $this->belongsTo(MaintenanceLine::class, 'line_id');
    }

    /**
     * Get the area for this plan.
     */
    public function area()
    {
        return $this->belongsTo(MaintenanceArea::class, 'area_id');
    }

    /**
     * Get the task for this plan.
     */
    public function task()
    {
        return $this->belongsTo(MaintenanceTask::class, 'task_id');
    }

    /**
     * Get the technician assigned to this plan.
     */
    public function assignedTo()
    {
        return $this->belongsTo(Technician::class, 'assigned_to');
    }

    /**
     * Get the notes for this maintenance plan.
     */
    public function notes()
    {
        return $this->hasMany(MaintenanceNote::class);
    }

    /**
     * Check if a date is a holiday (fixed or recurring)
     *
     * @param Carbon $date
     * @return bool
     */
    public static function isHoliday(Carbon $date)
    {
        // Verificar feriados fixos na data específica
        $fixedHoliday = Holiday::where('date', $date->format('Y-m-d'))
            ->where('is_active', true)
            ->exists();

        if ($fixedHoliday) {
            return true;
        }

        // Verificar feriados recorrentes (mesma data todo ano)
        $recurringHoliday = Holiday::whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->where('is_recurring', true)
            ->where('is_active', true)
            ->exists();

        return $recurringHoliday;
    }

    /**
     * Check if a date is a Sunday
     *
     * @param Carbon $date
     * @return bool
     */
    public static function isSunday(Carbon $date)
    {
        return $date->dayOfWeek === Carbon::SUNDAY;
    }

    /**
     * Get next valid working date (not a holiday or Sunday)
     *
     * @param Carbon $date
     * @return Carbon
     */
    public static function getNextValidWorkingDate(Carbon $date)
    {
        $nextDate = $date->copy();

        // Continuar avançando até encontrar uma data válida
        while (self::isHoliday($nextDate) || self::isSunday($nextDate)) {
            $nextDate->addDay();
        }

        return $nextDate;
    }

    /**
     * Calculate the next maintenance date based on the frequency,
     * skipping holidays and Sundays.
     *
     * @return Carbon
     */
    public function calculateNextMaintenanceDate()
    {
        $baseDate = $this->scheduled_date ?? now();
        $nextDate = null;

        switch ($this->frequency_type) {
            case 'once':
                $nextDate = $baseDate; // Não recorrente, retorna a data agendada
                break;

            case 'daily':
                $nextDate = $baseDate->copy()->addDay();
                break;

            case 'custom':
                $nextDate = $baseDate->copy()->addDays($this->custom_days);
                break;

            case 'weekly':
                $nextDate = $baseDate->copy()->addWeek();
                if (!is_null($this->day_of_week)) {
                    // Ajustar para o dia da semana especificado
                    while ($nextDate->dayOfWeek != $this->day_of_week) {
                        $nextDate->addDay();
                    }
                }
                break;

            case 'monthly':
                $nextDate = $baseDate->copy()->addMonth();
                // Garantir que não excedemos os dias no mês
                if (!is_null($this->day_of_month)) {
                    $daysInMonth = $nextDate->daysInMonth;
                    $day = min($this->day_of_month, $daysInMonth);
                    $nextDate->setDay($day);
                }
                break;

            case 'yearly':
                $nextDate = $baseDate->copy()->addYear();
                // Lidar com 29 de fevereiro em anos não bissextos
                if (!is_null($this->month) && !is_null($this->month_day)) {
                    if ($this->month == 2 && $this->month_day == 29 && !$nextDate->isLeapYear()) {
                        $nextDate->setMonth(2)->setDay(28);
                    } else {
                        $nextDate->setMonth($this->month)->setDay($this->month_day);
                    }
                }
                break;

            default:
                $nextDate = $baseDate->copy();
                break;
        }

        // Verificar e ajustar para a próxima data válida (não feriado e não domingo)
        return self::getNextValidWorkingDate($nextDate);
    }

    /**
     * Check if the scheduled date is valid (not a holiday or Sunday)
     *
     * @return bool
     */
    public function hasValidScheduledDate()
    {
        if (!$this->scheduled_date) {
            return false;
        }

        return !self::isHoliday($this->scheduled_date) && !self::isSunday($this->scheduled_date);
    }

    /**
     * Get the formatted duration as hours and minutes.
     *
     * @return string
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->estimated_duration_minutes) {
            return 'N/A';
        }

        $hours = floor($this->estimated_duration_minutes / 60);
        $minutes = $this->estimated_duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h " . ($minutes > 0 ? "{$minutes}m" : "");
        } else {
            return "{$minutes}m";
        }
    }
    
    /**
     * Get maintenance task logs associated with this plan
     */
    public function taskLogs()
    {
        return $this->hasMany(MaintenanceTaskLog::class, 'maintenance_plan_id');
    }
    
    /**
     * Get maintenance task logs that are completed
     */
    public function completedTaskLogs()
    {
        return $this->taskLogs()->where('status', 'completed');
    }
    
    /**
     * Get maintenance task logs that are pending or in progress
     */
    public function pendingTaskLogs()
    {
        return $this->taskLogs()->whereIn('status', ['pending', 'in_progress']);
    }
}
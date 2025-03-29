<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceWorkingDay extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenance_working_days';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'day_name',
        'day_number',
        'is_working_day',
        'start_time',
        'end_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_working_day' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Scope a query to only include working days.
     */
    public function scopeWorkingDays($query)
    {
        return $query->where('is_working_day', true);
    }

    /**
     * Scope a query to only include non-working days.
     */
    public function scopeNonWorkingDays($query)
    {
        return $query->where('is_working_day', false);
    }

    /**
     * Check if a given day of the week is a working day.
     *
     * @param int|\Carbon\Carbon $day Day number (1-7) or Carbon instance
     * @return bool
     */
    public static function isWorkingDay($day)
    {
        if ($day instanceof \Carbon\Carbon) {
            $day = $day->dayOfWeek === 0 ? 7 : $day->dayOfWeek; // Convert Sunday from 0 to 7
        }

        $workingDay = self::where('day_number', $day)->first();

        return $workingDay && $workingDay->is_working_day;
    }

    /**
     * Get working days information for all days of the week.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getWeekSchedule()
    {
        return self::orderBy('day_number')->get();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceHoliday extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenance_holidays';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'date',
        'is_recurring',
        'is_working_day',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_working_day' => 'boolean',
    ];

    /**
     * Scope a query to only include recurring holidays.
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope a query to only include holidays for a specific year.
     */
    public function scopeForYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }

    /**
     * Scope a query to only include holidays that are working days.
     */
    public function scopeWorkingDays($query)
    {
        return $query->where('is_working_day', true);
    }

    /**
     * Scope a query to only include holidays that are not working days.
     */
    public function scopeNonWorkingDays($query)
    {
        return $query->where('is_working_day', false);
    }

    /**
     * Check if a given date is a holiday.
     *
     * @param \Carbon\Carbon|string $date
     * @return bool
     */
    public static function isHoliday($date)
    {
        $date = is_string($date) ? \Carbon\Carbon::parse($date) : $date;

        // Check if date matches any holiday
        $holiday = self::where(function ($query) use ($date) {
            // Exact date match
            $query->whereDate('date', $date->toDateString());

            // Or recurring holiday (month and day match)
            $query->orWhere(function ($q) use ($date) {
                $q->where('is_recurring', true)
                  ->whereMonth('date', $date->month)
                  ->whereDay('date', $date->day);
            });
        })->first();

        return $holiday !== null;
    }
}

<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'responsibilities',
        'requirements',
        'salary_range_min',
        'salary_range_max',
        'department_id',
        'category_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
    ];

    /**
     * Get the employees for the position
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    /**
     * Get the department that the position belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the job category that the position belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'category_id');
    }
}

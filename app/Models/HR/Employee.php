<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'date_of_birth',
        'gender',
        'id_card',
        'tax_number',
        'address',
        'phone',
        'email',
        'marital_status',
        'dependents',
        'photo',
        'bank_name',
        'bank_account',
        'position_id',
        'department_id',
        'hire_date',
        'food_benefit',
        'transport_benefit',
        'bonus_amount',
        'employment_status',
        'inss_number',
        'base_salary',
    ];

    /**
     * Employment status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_RETIRED = 'retired';

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'dependents' => 'integer',
        'base_salary' => 'decimal:2',
    ];

    /**
     * Get the position that the employee belongs to
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class, 'position_id');
    }

    /**
     * Get the department that the employee belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employee documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Get the emergency contacts
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Get the leaves
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the attendances
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the payrolls
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get the assigned equipment
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(EmployeeEquipment::class);
    }
    
    /**
     * Get the shift assignments for this employee
     */
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * Get the salary advances for this employee
     */
    public function salaryAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class);
    }
}

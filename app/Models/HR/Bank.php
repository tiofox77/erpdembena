<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'short_name',
        'code',
        'swift_code',
        'country',
        'is_active',
        'logo',
        'website',
        'phone',
        'address',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the employees that have accounts in this bank
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'bank_id');
    }

    /**
     * Scope to get only active banks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive banks
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get formatted bank name (short_name if available, otherwise name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->short_name ?: $this->name;
    }

    /**
     * Get full bank information
     */
    public function getFullInfoAttribute(): string
    {
        $info = $this->name;
        if ($this->short_name) {
            $info .= ' (' . $this->short_name . ')';
        }
        if ($this->code) {
            $info .= ' - ' . $this->code;
        }
        return $info;
    }
}

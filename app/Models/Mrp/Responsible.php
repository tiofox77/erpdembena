<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Responsible extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'mrp_responsibles';
    
    protected $fillable = [
        'name',
        'position',
        'department',
        'email',
        'phone',
        'notes',
        'is_active',
        'created_by',
        'updated_by'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the user who created this responsible
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who last updated this responsible
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Scope a query to only include active responsibles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

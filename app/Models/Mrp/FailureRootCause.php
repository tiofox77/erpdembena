<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class FailureRootCause extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mrp_failure_root_causes';
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'category_id',
        'is_active',
        'metadata',
        'created_by',
        'updated_by'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relacionamentos
    public function category()
    {
        return $this->belongsTo(FailureCategory::class, 'category_id');
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    // Escopo para filtrar apenas causas ativas
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Escopo para filtrar por categoria
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}

<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class FailureCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mrp_failure_categories';
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'color',
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
    public function rootCauses()
    {
        return $this->hasMany(FailureRootCause::class, 'category_id');
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    // Acessor para pegar a cor com "#" se não estiver presente
    public function getColorAttribute($value)
    {
        if ($value && !str_starts_with($value, '#')) {
            return '#' . $value;
        }
        return $value;
    }
    
    // Mutator para remover "#" ao salvar a cor
    public function setColorAttribute($value)
    {
        $this->attributes['color'] = str_replace('#', '', $value);
    }
}

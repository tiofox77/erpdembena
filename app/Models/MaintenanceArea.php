<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceArea extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenance_areas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Get the attributes that should be mutated to dates.
     *
     * @return array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Garantir que deleted_at receba apenas valores de data válidos
        static::saving(function ($model) {
            if (isset($model->attributes['deleted_at']) && !empty($model->attributes['deleted_at'])) {
                if (!is_null($model->attributes['deleted_at']) && !strtotime($model->attributes['deleted_at'])) {
                    $model->attributes['deleted_at'] = null;
                }
            }
        });
    }

    /**
     * Get the lines for this area.
     */
    public function lines()
    {
        return $this->hasMany(MaintenanceLine::class, 'maintenance_area_id');
    }
}

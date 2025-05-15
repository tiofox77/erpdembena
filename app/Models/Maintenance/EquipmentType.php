<?php

namespace App\Models\Maintenance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'color_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Obter as peças de equipamento que pertencem a este tipo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function equipmentParts(): HasMany
    {
        return $this->hasMany(EquipmentPart::class, 'equipment_type_id');
    }
}

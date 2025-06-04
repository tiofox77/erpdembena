<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'symbol',
        'description',
        'is_active',
        'category'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get formatted name with symbol
     *
     * @return string
     */
    public function getFormattedNameAttribute()
    {
        return $this->name . ' (' . $this->symbol . ')';
    }

    /**
     * Get all active unit types
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive()
    {
        return self::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Get all unit types by category
     *
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByCategory($category)
    {
        return self::where('category', $category)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}

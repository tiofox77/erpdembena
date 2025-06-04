<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentPartRequestImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment_part_request_images';

    protected $fillable = [
        'request_id',
        'image_path',
        'original_filename',
        'caption',
        'file_size',
        'order',
    ];

    /**
     * Get the part request that owns this image.
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(EquipmentPartRequest::class, 'request_id');
    }
}

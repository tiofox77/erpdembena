<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_out_items';

    protected $fillable = [
        'stock_out_id',
        'equipment_part_id',
        'quantity'
    ];

    /**
     * Get the stock out transaction associated with this item
     */
    public function stockOut()
    {
        return $this->belongsTo(StockOut::class, 'stock_out_id');
    }

    /**
     * Get the equipment part associated with this item
     */
    public function equipmentPart()
    {
        return $this->belongsTo(EquipmentPart::class, 'equipment_part_id');
    }
}

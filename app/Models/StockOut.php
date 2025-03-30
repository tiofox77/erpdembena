<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EquipmentPart;
use App\Models\User;
use App\Models\StockOutItem;

class StockOut extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_outs';

    protected $fillable = [
        'date',
        'reason',
        'user_id',
        'notes',
        'reference_number'
    ];

    /**
     * Get the stock out items associated with this stock out
     */
    public function items()
    {
        return $this->hasMany(StockOutItem::class);
    }

    /**
     * Get the user who initiated the stock out
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

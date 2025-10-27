<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\Product;
use App\Models\User;

class DemandForecast extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_demand_forecasts';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'forecast_date',
        'forecast_quantity',
        'confidence_level',
        'forecast_type',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'forecast_date' => 'date',
        'forecast_quantity' => 'integer',
        'confidence_level' => 'decimal:2',
    ];

    /**
     * Get the product that owns the demand forecast.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the user that created the demand forecast.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the demand forecast.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

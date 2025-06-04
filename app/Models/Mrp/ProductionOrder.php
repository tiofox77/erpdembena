<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;

class ProductionOrder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_production_orders';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'product_id',
        'bom_header_id',
        'schedule_id',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'planned_quantity',
        'produced_quantity',
        'rejected_quantity',
        'status',
        'priority',
        'location_id',
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
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'planned_quantity' => 'decimal:3',
        'produced_quantity' => 'decimal:3',
        'rejected_quantity' => 'decimal:3',
    ];

    /**
     * Get the product being produced.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the BOM header used for this production order.
     */
    public function bomHeader()
    {
        return $this->belongsTo(BomHeader::class, 'bom_header_id');
    }

    /**
     * Get the production schedule this order belongs to.
     */
    public function schedule()
    {
        return $this->belongsTo(ProductionSchedule::class, 'schedule_id');
    }

    /**
     * Get the location for this production order.
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Calculate the percentage of completion for this order.
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->planned_quantity <= 0) {
            return 0;
        }
        
        return min(100, round(($this->produced_quantity / $this->planned_quantity) * 100, 2));
    }

    /**
     * Get the user that created the production order.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the production order.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\Supplier;

class PurchasePlan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_purchase_plans';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_number',
        'product_id',
        'supplier_id',
        'planned_date',
        'required_date',
        'quantity',
        'unit_price',
        'status',
        'purchase_order_id',
        'priority',
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
        'planned_date' => 'date',
        'required_date' => 'date',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Get the product being purchased.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the supplier for this purchase plan.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Calculate the total value of the purchase plan
     */
    public function getTotalValueAttribute()
    {
        return $this->quantity * ($this->unit_price ?? 0);
    }

    /**
     * Get the user that created the purchase plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the purchase plan.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

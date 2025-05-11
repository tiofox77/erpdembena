<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\SupplyChain\Supplier;

class PurchasePlanHeader extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_purchase_plan_headers';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_number',
        'title',
        'supplier_id',
        'planned_date',
        'required_date',
        'status',
        'purchase_order_id',
        'priority',
        'notes',
        'total_value',
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
        'total_value' => 'decimal:2',
    ];

    /**
     * Get the items associated with this purchase plan.
     */
    public function items()
    {
        return $this->hasMany(PurchasePlanItem::class, 'purchase_plan_id');
    }

    /**
     * Get the supplier for this purchase plan.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user that created the purchase plan.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the purchase plan.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calcula o valor total do plano baseado nos itens
     */
    public function calculateTotalValue()
    {
        $this->total_value = $this->items->sum('total_price');
        $this->save();
    }
}

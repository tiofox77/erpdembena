<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\Product;

class PurchasePlanItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nome da tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_purchase_plan_items';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_plan_id',
        'product_id',
        'quantity',
        'unit_of_measure',
        'unit_price',
        'total_price',
        'notes',
        'status'
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the purchase plan that owns this item.
     */
    public function purchasePlan()
    {
        return $this->belongsTo(PurchasePlanHeader::class, 'purchase_plan_id');
    }

    /**
     * Get the product associated with this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcula o preço total do item com base na quantidade e preço unitário
     */
    public function calculateTotalPrice()
    {
        $this->total_price = $this->quantity * ($this->unit_price ?? 0);
        $this->save();
        
        // Atualiza o total do plano de compra
        if ($this->purchasePlan) {
            $this->purchasePlan->calculateTotalValue();
        }
    }
}

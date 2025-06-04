<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\SupplyChain\InventoryItem;

class InventoryLevel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_inventory_levels';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'location_id',
        'safety_stock',
        'reorder_point',
        'maximum_stock',
        'economic_order_quantity',
        'lead_time_days',
        'daily_usage_rate',
        'abc_classification',
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
        'safety_stock' => 'decimal:3',
        'reorder_point' => 'decimal:3',
        'maximum_stock' => 'decimal:3',
        'economic_order_quantity' => 'decimal:3',
        'lead_time_days' => 'integer',
        'daily_usage_rate' => 'decimal:3',
    ];

    /**
     * Atributos virtuais que serão adicionados ao objeto quando for serializado para JSON.
     *
     * @var array
     */
    protected $appends = ['current_stock', 'available_stock'];

    /**
     * Get the product that owns the inventory level.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the location for this inventory level.
     */
    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    /**
     * Get the user that created the inventory level.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the inventory level.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the current stock status based on safety stock and reorder point.
     *
     * @return string The stock status: 'critical', 'low', 'normal', or 'overstock'
     */
    /**
     * Obtém itens de inventário relacionados ao produto e localização configurados.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventoryItems()
    {
        return InventoryItem::where('product_id', $this->product_id)
                          ->where('location_id', $this->location_id);
    }

    /**
     * Obtém o estoque atual para o produto e localização configurados.
     *
     * @return float
     */
    public function getCurrentStockAttribute()
    {
        // Buscar todos os itens de inventário para este produto/localização
        $inventoryItems = $this->inventoryItems()->get();
        
        // Calcular o total da quantidade em mãos
        return $inventoryItems->sum('quantity_on_hand') ?? 0;
    }

    /**
     * Obtém a quantidade disponível para o produto e localização configurados.
     *
     * @return float
     */
    public function getAvailableStockAttribute()
    {
        // Buscar todos os itens de inventário para este produto/localização
        $inventoryItems = $this->inventoryItems()->get();
        
        // Calcular o total da quantidade disponível
        return $inventoryItems->sum('quantity_available') ?? 0;
    }

    /**
     * Determina o status do estoque com base nos níveis configurados e no estoque atual.
     * 
     * @return string 'critical', 'low', 'normal' ou 'overstock'
     */
    public function getStockStatus()
    {
        $currentStock = $this->current_stock;
        $safetyStock = $this->safety_stock ?? 0;
        $reorderPoint = $this->reorder_point ?? 0;
        $maximumStock = $this->maximum_stock ?? 0;

        // Estoque crítico: abaixo do estoque de segurança
        if ($currentStock <= $safetyStock) {
            return 'critical';
        }
        
        // Estoque baixo: abaixo do ponto de reposição
        if ($currentStock <= $reorderPoint) {
            return 'low';
        }
        
        // Excesso de estoque: acima do estoque máximo e se o estoque máximo estiver definido
        if ($maximumStock > 0 && $currentStock > $maximumStock) {
            return 'overstock';
        }
        
        // Estoque normal: entre o ponto de reposição e o máximo
        return 'normal';
    }
}

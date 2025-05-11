<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\SupplyChain\Product;

class BomHeader extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_bom_headers';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'bom_number',
        'description',
        'status',
        'effective_date',
        'expiration_date',
        'version',
        'uom',
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
        'effective_date' => 'date',
        'expiration_date' => 'date',
        'version' => 'integer',
    ];

    /**
     * Get the product that owns the BOM.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\SupplyChain\Product::class);
    }

    /**
     * Get the details for the BOM.
     */
    public function details()
    {
        return $this->hasMany(BomDetail::class, 'bom_header_id');
    }

    /**
     * Get the production orders for this BOM.
     */
    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'bom_header_id');
    }

    /**
     * Get the user that created the BOM.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the BOM.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

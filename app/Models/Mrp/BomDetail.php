<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\SupplyChain\Product;

class BomDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_bom_details';

    /**
     * NOTA: Discrepancias entre modelo e banco de dados
     * - Coluna 'position': Presente no modelo, possivelmente ausente na tabela
     * - Coluna 'level': Presente no modelo, possivelmente ausente na tabela 
     * - Coluna 'scrap_percentage': Presente no modelo, possivelmente ausente na tabela
     * - Coluna 'created_by': Presente no modelo, possivelmente ausente na tabela
     * - Coluna 'updated_by': Presente no modelo, possivelmente ausente na tabela
     *
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bom_header_id',
        'component_id',
        'quantity',
        'uom',
        'position',
        'level',
        'scrap_percentage',
        'is_critical',
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
        'quantity' => 'decimal:3',
        'scrap_percentage' => 'decimal:2',
        'level' => 'integer',
        'is_critical' => 'boolean',
    ];

    /**
     * Get the BOM header that owns the detail.
     */
    public function bomHeader()
    {
        return $this->belongsTo(BomHeader::class, 'bom_header_id');
    }

    /**
     * Get the component product.
     */
    public function component()
    {
        return $this->belongsTo(Product::class, 'component_id');
    }

    /**
     * Get the user that created the BOM detail.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the BOM detail.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

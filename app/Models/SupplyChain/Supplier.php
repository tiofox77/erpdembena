<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\GoodsReceipt;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_suppliers';

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'notes',
        'status',
        'tax_id',
        'website',
        'payment_terms',
        'credit_limit',
        'bank_name',
        'bank_account',
    ];

    /**
     * Get all purchase orders from this supplier
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get all products where this supplier is the primary supplier
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'primary_supplier_id');
    }

    /**
     * Get all goods receipts from this supplier
     */
    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    /**
     * Get active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get inactive suppliers
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}

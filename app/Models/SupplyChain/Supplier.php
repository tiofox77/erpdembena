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
        'category_id',
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
        'position',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the category that owns the supplier
     */
    public function category()
    {
        return $this->belongsTo(SupplierCategory::class, 'category_id')->withDefault([
            'name' => '--',
            'code' => null,
        ]);
    }
    
    /**
     * Scope a query to only include suppliers of a given category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

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

<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\PurchaseOrderItem;
use App\Models\SupplyChain\GoodsReceipt;
use App\Models\SupplyChain\ShippingNote;
use App\Models\SupplyChain\CustomFormField;
use App\Models\User;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_purchase_orders';

    protected $fillable = [
        'order_number',
        'supplier_id',
        'created_by',
        'approved_by',
        'status',
        'is_active',
        'order_date',
        'expected_delivery_date',
        'delivery_date',
        'shipping_method',
        'shipping_terms',
        'payment_terms',
        'currency',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount', 
        'total_amount',
        'notes',
        'internal_notes',
        'reference_number',
        'bill_of_lading'
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    /**
     * Get the supplier that the purchase order belongs to
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who created the purchase order
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who approved the purchase order
     */
    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get the items for this purchase order
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the goods receipts for this purchase order
     */
    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }
    
    /**
     * Get the shipping notes for this purchase order
     */
    public function shippingNotes()
    {
        return $this->hasMany(ShippingNote::class);
    }
    
    /**
     * Check if the purchase order is partially received
     */
    public function getIsPartiallyReceivedAttribute()
    {
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        
        return $this->items->contains(function($item) {
            return $item->is_partially_received;
        }) || (
            $this->items->sum('received_quantity') > 0 && 
            $this->items->sum('received_quantity') < $this->items->sum('quantity')
        );
    }
    
    /**
     * Check if all items are fully received
     */
    public function getIsFullyReceivedAttribute()
    {
        if (!$this->relationLoaded('items')) {
            $this->load('items');
        }
        
        return $this->items->every(function($item) {
            return $item->is_fully_received;
        });
    }
    
    /**
     * Update the purchase order status based on received quantities
     */
    public function updateReceiptStatus()
    {
        if ($this->is_fully_received) {
            $this->status = 'completed';
        } elseif ($this->is_partially_received) {
            $this->status = 'partially_received';
        } else {
            $this->status = 'approved'; // Default status if not fully or partially received
        }
        
        $this->save();
        
        return $this->status;
    }
    
    /**
     * Get the latest shipping note for this purchase order
     */
    public function latestShippingNote()
    {
        return $this->hasMany(ShippingNote::class)->latest();
    }
    
    /**
     * Get the current shipping status from the latest shipping note
     */
    public function getCurrentShippingStatusAttribute()
    {
        $latestNote = $this->latestShippingNote()->first();
        return $latestNote ? $latestNote->status : null;
    }
    
    /**
     * Get the current shipping status text from the latest shipping note
     */
    public function getCurrentShippingStatusTextAttribute()
    {
        $latestNote = $this->latestShippingNote()->first();
        return $latestNote ? $latestNote->status_text : null;
    }
    
    /**
     * Get the shipping progress percentage based on the latest status
     */
    public function getShippingProgressPercentageAttribute()
    {
        $latestNote = $this->latestShippingNote()->first();
        return $latestNote ? $latestNote->progress_percentage : 0;
    }

    /**
     * Get draft purchase orders
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get pending approval purchase orders
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Get approved purchase orders
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get ordered purchase orders
     */
    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    /**
     * Get purchase orders with partial receipts
     */
    public function scopePartiallyReceived($query)
    {
        return $query->where('status', 'partially_received');
    }

    /**
     * Get completed purchase orders
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get cancelled purchase orders
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get overdue purchase orders
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->where('expected_delivery_date', '<', now());
    }
    
    /**
     * Filter purchase orders by active status
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Filter purchase orders by inactive status
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Calculate the receipt percentage
     */
    public function getReceiptPercentageAttribute()
    {
        $totalExpected = $this->items->sum('quantity');
        $totalReceived = $this->items->sum('received_quantity');
        
        if ($totalExpected > 0) {
            return min(100, round(($totalReceived / $totalExpected) * 100));
        }
        
        return 0;
    }

    /**
     * Generate a unique order number
     */
    public static function generateOrderNumber()
    {
        $prefix = 'PO';
        $date = now()->format('ymd');
        $lastOrder = self::orderBy('id', 'desc')->first();
        
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate totals before saving
     */
    protected static function booted()
    {
        static::saving(function ($purchaseOrder) {
            $purchaseOrder->recalculateTotals();
        });
    }

    /**
     * Recalculate order totals based on items
     */
    public function recalculateTotals()
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        
        // Calculate final total
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_amount - $this->discount_amount;
        
        return $this;
    }
    
    /**
     * Get the status display field value from shipping notes and their associated custom forms
     * 
     * @return array
     */
    public function getStatusDisplayFieldValue()
    {
        // Removido log de debug: PO Status Debug
        
        $result = [
            'status' => ucfirst(str_replace('_', ' ', $this->status)),
            'custom_field_value' => null,
            'custom_field_name' => null
        ];
        
        // Removido log de debug: PO Status Debug
        
        // 1. Encontrar a última nota de envio (shipping note) deste pedido de compra
        $latestShippingNote = $this->latestShippingNote()->first();
        
        if (!$latestShippingNote) {
            // Removido log de debug: PO Status Debug
            return $result;
        }
        
        // Removido log de debug: PO Status Debug
        
        // 2. Verificar se a nota de envio tem um formulário personalizado associado
        $customForm = $latestShippingNote->customForm;
        if (!$customForm) {
            // Removido log de debug: PO Status Debug
            return $result;
        }
        
        // Removido log de debug: PO Status Debug
        
        // 3. Verificar se o formulário tem configuração de exibição de status
        $statusDisplayConfig = $customForm->status_display_config;
        // Removido log de debug: PO Status Debug
        
        if (empty($statusDisplayConfig) || !isset($statusDisplayConfig['field_id']) || !isset($statusDisplayConfig['enabled']) || !$statusDisplayConfig['enabled']) {
            // Removido log de debug: PO Status Debug
            return $result;
        }
        
        // 4. Buscar o campo configurado para exibição de status
        $fieldId = $statusDisplayConfig['field_id'];
        $field = \App\Models\SupplyChain\CustomFormField::find($fieldId);
        
        if (!$field) {
            // Removido log de debug: PO Status Debug
            return $result;
        }
        
        // Removido log de debug: PO Status Debug
        
        // 5. Guardar o nome do campo para exibição
        $result['custom_field_name'] = $field->label ?? $field->name;
        
        // 6. Usar o método currentStatus da ShippingNote para obter o valor formatado do campo
        $customFieldValue = $latestShippingNote->currentStatus();
        
        // Removido log de debug: PO Status Debug
        
        // Diretamente usar o valor de customFieldValue sem criar um objeto stdClass
        if ($customFieldValue !== null) {
            // Extrair o valor do campo
            $fieldValue = $customFieldValue;
            // Removido log de debug: PO Status Debug
        } else {
            // Tentar consulta direta ao banco para depuração
            $rawValue = \Illuminate\Support\Facades\DB::table('sc_custom_form_field_values')
                ->where('field_id', $fieldId)
                ->where('submission_id', $latestShippingNote->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            // Removido log de debug: PO Status Debug
            
            // Removido log de debug: PO Status Debug
        }
        
        if (empty($fieldValue)) {
            // Removido log de debug: PO Status Debug
            return $result;
        }
        
        // O método currentStatus() já retorna o valor formatado
        // não é necessário formatar novamente
        $formattedValue = $fieldValue;
        
        // Removido log de debug: PO Status Debug
        
        $result['custom_field_value'] = $formattedValue;
        
        // Removido log de debug: PO Status Debug
        
        return $result;
    }
}

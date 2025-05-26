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
        'reference_number'
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
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Iniciando getStatusDisplayFieldValue para PO #' . $this->id);
        
        $result = [
            'status' => ucfirst(str_replace('_', ' ', $this->status)),
            'custom_field_value' => null,
            'custom_field_name' => null
        ];
        
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Status base do PO', [
            'po_id' => $this->id,
            'status' => $this->status,
            'formatted_status' => $result['status']
        ]);
        
        // 1. Encontrar a última nota de envio (shipping note) deste pedido de compra
        $latestShippingNote = $this->latestShippingNote()->first();
        
        if (!$latestShippingNote) {
            \Illuminate\Support\Facades\Log::debug('PO Status Debug: Nenhuma shipping note encontrada para PO #' . $this->id);
            return $result;
        }
        
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Shipping note encontrada', [
            'po_id' => $this->id,
            'shipping_note_id' => $latestShippingNote->id,
            'created_at' => $latestShippingNote->created_at
        ]);
        
        // 2. Verificar se a nota de envio tem um formulário personalizado associado
        $customForm = $latestShippingNote->customForm;
        if (!$customForm) {
            \Illuminate\Support\Facades\Log::debug('PO Status Debug: Shipping note não tem formulário personalizado associado', [
                'po_id' => $this->id,
                'shipping_note_id' => $latestShippingNote->id
            ]);
            return $result;
        }
        
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Formulário personalizado encontrado', [
            'po_id' => $this->id,
            'shipping_note_id' => $latestShippingNote->id,
            'custom_form_id' => $customForm->id,
            'custom_form_name' => $customForm->name
        ]);
        
        // 3. Verificar se o formulário tem configuração de exibição de status
        $statusDisplayConfig = $customForm->status_display_config;
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Configuração de exibição de status', [
            'po_id' => $this->id,
            'custom_form_id' => $customForm->id,
            'status_display_config' => $statusDisplayConfig
        ]);
        
        if (empty($statusDisplayConfig) || !isset($statusDisplayConfig['field_id']) || !isset($statusDisplayConfig['enabled']) || !$statusDisplayConfig['enabled']) {
            \Illuminate\Support\Facades\Log::debug('PO Status Debug: Configuração de status inválida ou desativada', [
                'po_id' => $this->id,
                'custom_form_id' => $customForm->id,
                'empty_config' => empty($statusDisplayConfig),
                'has_field_id' => isset($statusDisplayConfig['field_id']),
                'has_enabled' => isset($statusDisplayConfig['enabled']),
                'is_enabled' => $statusDisplayConfig['enabled'] ?? false
            ]);
            return $result;
        }
        
        // 4. Buscar o campo configurado para exibição de status
        $fieldId = $statusDisplayConfig['field_id'];
        $field = \App\Models\SupplyChain\CustomFormField::find($fieldId);
        
        if (!$field) {
            \Illuminate\Support\Facades\Log::debug('PO Status Debug: Campo configurado não encontrado', [
                'po_id' => $this->id,
                'field_id' => $fieldId
            ]);
            return $result;
        }
        
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Campo configurado encontrado', [
            'po_id' => $this->id,
            'field_id' => $fieldId,
            'field_name' => $field->name,
            'field_label' => $field->label,
            'field_type' => $field->type
        ]);
        
        // 5. Guardar o nome do campo para exibição
        $result['custom_field_name'] = $field->label ?? $field->name;
        
        // 6. Usar o método currentStatus da ShippingNote para obter o valor formatado do campo
        $customFieldValue = $latestShippingNote->currentStatus();
        
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Valor obtido através do método currentStatus()', [
            'po_id' => $this->id,
            'shipping_note_id' => $latestShippingNote->id,
            'field_id' => $fieldId,
            'custom_field_value' => $customFieldValue
        ]);
        
        // Diretamente usar o valor de customFieldValue sem criar um objeto stdClass
        if ($customFieldValue !== null) {
            // Extrair o valor do campo
            $fieldValue = $customFieldValue;
            \Illuminate\Support\Facades\Log::debug('PO Status Debug: Valor formatado encontrado', [
                'po_id' => $this->id,
                'field_value' => $fieldValue
            ]);
        } else {
            // Tentar consulta direta ao banco para depuração
            $rawValue = \Illuminate\Support\Facades\DB::table('sc_custom_form_field_values')
                ->where('field_id', $fieldId)
                ->where('submission_id', $latestShippingNote->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            \Illuminate\Support\Facades\Log::debug('PO Status Debug: Tentativa de consulta direta à tabela', [
                'raw_result' => $rawValue ? json_encode($rawValue) : 'null',
                'value_if_exists' => $rawValue ? $rawValue->value : null
            ]);
            
            \Illuminate\Support\Facades\Log::debug('PO Status Debug: Nenhum valor encontrado na tabela de valores');
        }
        
        if (empty($fieldValue)) {
            \Illuminate\Support\Facades\Log::debug('PO Status Debug: Nenhum valor de campo encontrado para exibição de status');
            return $result;
        }
        
        // O método currentStatus() já retorna o valor formatado
        // não é necessário formatar novamente
        $formattedValue = $fieldValue;
        
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Usando valor já formatado do método currentStatus()', [
            'formatted_value' => $formattedValue
        ]);
        
        $result['custom_field_value'] = $formattedValue;
        
        \Illuminate\Support\Facades\Log::debug('PO Status Debug: Resultado final', [
            'po_id' => $this->id,
            'result' => $result
        ]);
        
        return $result;
    }
}

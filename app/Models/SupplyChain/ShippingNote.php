<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ShippingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'status',
        'note',
        'attachment_url',
        'updated_by'
    ];

    /**
     * Lista de status possíveis para acompanhamento do envio
     */
    public static $statusList = [
        'order_placed' => 'Encomendado',
        'proforma_invoice_received' => 'Fatura pró-forma recebida',
        'payment_completed' => 'Pagamento efetuado',
        'du_in_process' => 'DU em processamento',
        'goods_acquired' => 'Encomenda adquirida',
        'shipped_to_port' => 'Enviada para o porto',
        'shipping_line_booking_confirmed' => 'Reserva confirmada',
        'container_loaded' => 'Contentor carregado',
        'on_board' => 'Em trânsito',
        'arrived_at_port' => 'Chegada ao porto de Luanda',
        'customs_clearance' => 'Desembaraço aduaneiro',
        'delivered' => 'Entregue / Pronta para recolha'
    ];

    /**
     * Cores associadas a cada status para exibição visual
     */
    public static $statusColors = [
        'order_placed' => 'gray',
        'proforma_invoice_received' => 'indigo',
        'payment_completed' => 'green',
        'du_in_process' => 'orange',
        'goods_acquired' => 'blue',
        'shipped_to_port' => 'teal',
        'shipping_line_booking_confirmed' => 'cyan',
        'container_loaded' => 'purple',
        'on_board' => 'blue',
        'arrived_at_port' => 'emerald',
        'customs_clearance' => 'amber',
        'delivered' => 'green'
    ];

    /**
     * Ícones associados a cada status para exibição visual
     */
    public static $statusIcons = [
        'order_placed' => 'fa-shopping-cart',
        'proforma_invoice_received' => 'fa-file-invoice-dollar',
        'payment_completed' => 'fa-money-bill-wave',
        'du_in_process' => 'fa-file-alt',
        'goods_acquired' => 'fa-boxes',
        'shipped_to_port' => 'fa-dolly',
        'shipping_line_booking_confirmed' => 'fa-calendar-check',
        'container_loaded' => 'fa-container-storage',
        'on_board' => 'fa-ship',
        'arrived_at_port' => 'fa-anchor',
        'customs_clearance' => 'fa-clipboard-check',
        'delivered' => 'fa-check-circle'
    ];

    /**
     * Calcula a porcentagem de conclusão com base no status atual
     */
    public function getProgressPercentageAttribute()
    {
        $totalSteps = count(self::$statusList);
        $currentStep = array_search($this->status, array_keys(self::$statusList)) + 1;
        
        return round(($currentStep / $totalSteps) * 100);
    }

    /**
     * Obtém o texto formatado do status
     */
    public function getStatusTextAttribute()
    {
        return self::$statusList[$this->status] ?? $this->status;
    }

    /**
     * Obtém a cor associada ao status atual
     */
    public function getStatusColorAttribute()
    {
        return self::$statusColors[$this->status] ?? 'gray';
    }

    /**
     * Obtém o ícone associado ao status atual
     */
    public function getStatusIconAttribute()
    {
        return self::$statusIcons[$this->status] ?? 'fa-question-circle';
    }

    /**
     * Relacionamento com a ordem de compra
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Relacionamento com o usuário que atualizou
     */
    public function updatedByUser()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Alias para o relacionamento com o usuário (para compatibilidade)
     */
    public function user()
    {
        return $this->updatedByUser();
    }
}

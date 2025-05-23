<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ShippingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'status',
        'note',
        'attachment_url',
        'updated_by',
        'custom_form_id',
        'form_data'
    ];
    
    protected $casts = [
        'form_data' => 'array',
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
        'delivered' => 'Entregue / Pronta para recolha',
        'custom_form' => 'Formulário Personalizado',
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
        'delivered' => 'green',
        'custom_form' => 'blue'
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
        'delivered' => 'fa-check-circle',
        'custom_form' => 'fa-clipboard-list'
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
        if ($this->status === 'custom_form' && $this->customForm) {
            return $this->customForm->name;
        }
        
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
    
    /**
     * Relacionamento com o formulário personalizado
     */
    public function customForm()
    {
        return $this->belongsTo(CustomForm::class, 'custom_form_id');
    }

    /**
     * Relacionamento com a submissão do formulário
     */
    public function formSubmission()
    {
        return $this->hasOne(CustomFormSubmission::class, 'entity_id')
                ->where('form_id', $this->custom_form_id);
    }
    
    /**
     * Busca o valor do campo configurado em status_display_config do formulário
     * 
     * @return string|null
     */
    public function currentStatus()
    {
        // Verificar se existe um formulário personalizado associado
        if (!$this->custom_form_id) {
            return null;
        }

        // Consulta SQL otimizada para obter o valor do campo de status em uma única consulta
        $result = DB::table('sc_custom_forms')
            ->select('scfv.value', 'scff.type', 'scff.options')
            ->join('sc_custom_form_submissions as scfs', function ($join) {
                $join->on('scfs.form_id', '=', 'sc_custom_forms.id')
                     ->where('scfs.entity_id', '=', $this->id);
            })
            ->join('sc_custom_form_field_values as scfv', function ($join) {
                $join->on('scfv.submission_id', '=', 'scfs.id')
                     ->whereRaw('scfv.field_id = JSON_UNQUOTE(JSON_EXTRACT(sc_custom_forms.status_display_config, "$.field_id"))');
            })
            ->join('sc_custom_form_fields as scff', 'scff.id', '=', 'scfv.field_id')
            ->where('sc_custom_forms.id', '=', $this->custom_form_id)
            ->whereRaw('JSON_EXTRACT(sc_custom_forms.status_display_config, "$.enabled") = true')
            ->orderBy('scfv.created_at', 'desc')
            ->first();
        
        if (!$result) {
            return null;
        }
        
        // Formatar o valor baseado no tipo do campo
        if ($result->type === 'select' || $result->type === 'radio') {
            $options = is_string($result->options) ? json_decode($result->options, true) : $result->options;
            
            if (is_array($options)) {
                foreach ($options as $option) {
                    if (isset($option['value']) && $option['value'] == $result->value) {
                        return $option['label'] ?? $result->value;
                    }
                }
            }
        } elseif ($result->type === 'checkbox') {
            return $result->value ? 'Sim' : 'Não';
        }
        
        return $result->value;
    }
}


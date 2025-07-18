<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\SalaryDiscount;
use App\Models\HR\SalaryDiscountPayment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SalaryDiscounts extends Component
{
    use WithPagination;
    
    // Propriedades do formulário
    public ?int $discount_id = null;
    public ?int $employee_id = null;
    public ?string $request_date = null;
    public ?float $amount = null;
    public ?int $installments = 1;
    public ?float $installment_amount = null;
    public ?string $first_deduction_date = null;
    public ?string $reason = null;
    public string $discount_type = 'others';
    public ?string $status = 'pending';
    public ?string $notes = null;
    
    // Controles da modal
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showViewModal = false;
    public bool $showPaymentModal = false;
    public bool $isEditing = false;
    public string $currentViewTab = 'details'; // Controla qual aba está ativa na modal de visualização
    
    // Propriedades para pagamentos
    public ?float $payment_amount = null;
    public ?string $payment_date = null;
    public ?int $installment_number = null;
    public string $payment_type = 'installment';
    public ?string $payment_notes = null;
    
    // Desconto para visualização completa
    public ?SalaryDiscount $viewDiscount = null;
    
    // Desconto para modal de pagamento
    public ?SalaryDiscount $paymentDiscount = null;
    
    // Desconto para modal de exclusão
    public ?SalaryDiscount $deleteDiscount = null;
    
    // Desconto para exclusão
    public ?SalaryDiscount $discountToDelete = null;
    
    // Controles de filtro e pesquisa
    public string $search = '';
    public string $statusFilter = '';
    public string $typeFilter = '';
    public string $sortBy = 'request_date';
    public string $sortDirection = 'desc';
    
    // Propriedades para histórico de pagamentos
    public array $paymentHistory = [];
    
    /**
     * Regras de validação para o formulário de desconto
     */
    protected function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'request_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'installments' => ['required', 'integer', 'min:1'],
            'first_deduction_date' => ['required', 'date', 'after_or_equal:request_date'],
            'reason' => ['required', 'string', 'min:3'],
            'discount_type' => ['required', 'in:union,others'],
            'notes' => ['nullable', 'string'],
        ];
    }
    
    /**
     * Define nomes amigáveis para os atributos na validação
     */
    protected function validationAttributes(): array
    {
        return [
            'employee_id' => __('messages.employee'),
            'request_date' => __('messages.request_date'),
            'amount' => __('messages.amount'),
            'installments' => __('messages.installments'),
            'first_deduction_date' => __('messages.first_deduction_date'),
            'reason' => __('messages.reason'),
            'discount_type' => __('messages.discount_type'),
            'notes' => __('messages.notes'),
        ];
    }
    
    /**
     * Define qual aba está ativa na modal de visualização
     * 
     * @param string $tab Nome da aba ('details' ou 'payments')
     * @return void
     */
    public function setViewTab(string $tab): void
    {
        $this->currentViewTab = $tab;
        
        if ($tab === 'payments' && $this->viewDiscount) {
            $this->paymentHistory = $this->viewDiscount->payments()
                ->with('processor')
                ->orderBy('payment_date', 'desc')
                ->get()
                ->toArray();
        }
    }
    
    /**
     * Fecha a modal de visualização
     * 
     * @return void
     */
    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewDiscount = null;
        $this->paymentHistory = [];
        $this->currentViewTab = 'details';
    }
    
    /**
     * Método para renderizar o componente
     */
    public function render()
    {
        $query = SalaryDiscount::with(['employee', 'approver'])
            ->when($this->search, fn($q) => $q->whereHas('employee', function($query) {
                $query->where('full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $this->search . '%');
            }))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn($q) => $q->where('discount_type', $this->typeFilter))
            ->orderBy($this->sortBy, $this->sortDirection);
        
        $discounts = $query->paginate(10);
        
        $employees = Employee::orderBy('full_name')->get();
        
        return view('livewire.hr.salary-discounts', compact('discounts', 'employees'));
    }
    
    /**
     * Método para ordenar os registos
     */
    public function sortBy($field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->resetPage();
    }
    
    /**
     * Cria um novo registo de desconto salarial
     */
    public function create(): void
    {
        $this->reset([
            'discount_id', 'employee_id', 'request_date', 'amount',
            'installments', 'installment_amount', 'first_deduction_date',
            'reason', 'discount_type', 'notes'
        ]);
        
        $this->request_date = date('Y-m-d');
        $this->first_deduction_date = date('Y-m-d');
        $this->discount_type = 'others';
        $this->isEditing = false;
        $this->showModal = true;
    }
    
    /**
     * Edita um registo de desconto salarial existente
     */
    public function edit(int $id): void
    {
        $discount = SalaryDiscount::findOrFail($id);
        
        $this->discount_id = $discount->id;
        $this->employee_id = $discount->employee_id;
        $this->request_date = $discount->request_date->format('Y-m-d');
        $this->amount = $discount->amount;
        $this->installments = $discount->installments;
        $this->installment_amount = $discount->installment_amount;
        $this->first_deduction_date = $discount->first_deduction_date->format('Y-m-d');
        $this->reason = $discount->reason;
        $this->discount_type = $discount->discount_type;
        $this->notes = $discount->notes;
        
        $this->isEditing = true;
        $this->showModal = true;
    }
    
    /**
     * Visualiza os detalhes de um desconto salarial
     * 
     * @param int $id ID do desconto a ser visualizado
     * @return void
     */
    public function view(int $id): void
    {
        $this->discount_id = $id;
        
        $discount = SalaryDiscount::with(['employee', 'approver', 'payments'])->findOrFail($id);
        $this->viewDiscount = $discount; // Atribuir o objeto completo à propriedade viewDiscount
        
        $this->employee_id = $discount->employee_id;
        $this->request_date = $discount->request_date->format('Y-m-d');
        $this->amount = (float) $discount->amount;
        $this->installments = $discount->installments;
        $this->installment_amount = (float) $discount->installment_amount;
        $this->first_deduction_date = $discount->first_deduction_date->format('Y-m-d');
        $this->reason = $discount->reason;
        $this->discount_type = $discount->discount_type;
        $this->status = $discount->status;
        $this->notes = $discount->notes;
        
        // Definir a aba inicial como "details"
        $this->currentViewTab = 'details';
        $this->showViewModal = true;
    }
    
    /**
     * Calcula o valor da parcela com base no montante total e número de parcelas
     */
    public function calculateInstallmentAmount(): void
    {
        if ($this->amount && $this->installments) {
            $this->installment_amount = $this->amount / $this->installments;
        }
    }
    
    /**
     * Salva o registo de desconto salarial
     */
    public function save(): void
    {
        $this->validate();
        
        $data = [
            'employee_id' => $this->employee_id,
            'request_date' => $this->request_date,
            'amount' => $this->amount,
            'installments' => $this->installments,
            'installment_amount' => $this->installment_amount,
            'first_deduction_date' => $this->first_deduction_date,
            'remaining_installments' => $this->installments, // Inicializa com o número total de parcelas
            'reason' => $this->reason,
            'discount_type' => $this->discount_type,
            'notes' => $this->notes,
        ];
        
        if ($this->isEditing) {
            $discount = SalaryDiscount::findOrFail($this->discount_id);
            $discount->update($data);
            session()->flash('message', __('messages.discount_updated'));
        } else {
            SalaryDiscount::create($data);
            session()->flash('message', __('messages.discount_created'));
        }
        
        $this->showModal = false;
        $this->reset([
            'discount_id', 'employee_id', 'request_date', 'amount',
            'installments', 'installment_amount', 'first_deduction_date',
            'reason', 'discount_type', 'notes'
        ]);
    }
    
    /**
     * Abre o modal para registar um pagamento de parcela
     */
    public function registerPaymentModal(int $id): void
    {
        $this->discount_id = $id;
        $discount = SalaryDiscount::with('employee')->findOrFail($id);
        $this->paymentDiscount = $discount; // Carregar o desconto para uso na modal
        
        $this->payment_amount = (float) $discount->installment_amount;
        $this->payment_date = date('Y-m-d');
        $this->installment_number = $discount->installments - $discount->remaining_installments + 1;
        $this->payment_type = 'installment'; // Definir valor padrão
        
        $this->showPaymentModal = true;
    }
    
    /**
     * Atualiza o valor do pagamento baseado no tipo selecionado
     */
    public function updatedPaymentType(): void
    {
        \Log::info('updatedPaymentType chamado', [
            'payment_type' => $this->payment_type,
            'current_amount' => $this->payment_amount
        ]);
        
        if (!$this->paymentDiscount) {
            return;
        }
        
        switch ($this->payment_type) {
            case 'installment':
                $this->payment_amount = (float) $this->paymentDiscount->installment_amount;
                \Log::info('Definido para installment', ['amount' => $this->payment_amount]);
                break;
            case 'full':
                $this->payment_amount = (float) $this->paymentDiscount->remaining_amount;
                \Log::info('Definido para full', ['amount' => $this->payment_amount]);
                break;
            case 'custom':
                // Para tipo personalizado, define um valor inicial de 0 para não interferir
                $this->payment_amount = 0.00;
                \Log::info('Custom: definido para 0 (campo para preenchimento)');
                break;
        }
    }
    
    
    /**
     * Processa um pagamento de parcela
     */
    public function processPayment(): void
    {
        $this->validate($this->paymentRules());
        
        $discount = SalaryDiscount::findOrFail($this->discount_id);
        
        // Define o valor do pagamento baseado no tipo
        if ($this->payment_type === 'installment') {
            $this->payment_amount = (float) $discount->installment_amount;
        } elseif ($this->payment_type === 'full') {
            $this->payment_amount = (float) $discount->remaining_amount;
        }
        // Para 'custom', mantém o valor que o usuário digitou
        
        // Se for um pagamento completo, ajusta o número de parcelas
        $installmentNumber = $this->payment_type === 'full' 
            ? 0  // 0 significa pagamento completo
            : $this->installment_number;
        
        try {
            $discount->registerPayment(
                $this->payment_amount,
                $this->payment_date,
                $installmentNumber,
                Auth::id(),
                $this->payment_notes // Passar as notas de pagamento
            );
            
            $this->showPaymentModal = false;
            $this->reset(['payment_amount', 'payment_date', 'installment_number', 'payment_type', 'payment_notes']);
            $this->paymentDiscount = null;
            
            session()->flash('message', __('messages.payment_processed'));
        } catch (\Exception $e) {
            session()->flash('error', __('messages.payment_error') . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Alias para processPayment - usado no formulário
     */
    public function savePayment(): void
    {
        $this->processPayment();
    }
    
    /**
     * Fecha a modal de pagamento
     */
    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->reset(['payment_amount', 'payment_date', 'installment_number', 'payment_type', 'payment_notes']);
        $this->paymentDiscount = null;
    }
    
    /**
     * Regras de validação para pagamentos
     * 
     * @return array<string, mixed>
     */
    protected function paymentRules(): array
    {
        $rules = [
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_type' => ['required', 'in:installment,custom,full'],
        ];
        
        if ($this->payment_type === 'custom') {
            $rules['payment_amount'] = ['required', 'numeric', 'min:1'];
            
            if ($this->paymentDiscount) {
                $rules['payment_amount'][] = 'max:' . $this->paymentDiscount->remaining_amount;
            }
        }
        
        return $rules;
    }
    
    /**
     * Confirma a exclusão do registo
     */
    public function confirmDelete(int $id): void
    {
        $this->discount_id = $id;
        // Carrega o desconto com a relação employee para exibição na modal
        $this->discountToDelete = SalaryDiscount::with('employee')->findOrFail($id);
        $this->showDeleteModal = true;
    }
    
    /**
     * Exclui o registo de desconto salarial
     */
    public function delete(): void
    {
        SalaryDiscount::findOrFail($this->discount_id)->delete();
        $this->closeDeleteModal();
        
        session()->flash('message', __('messages.discount_deleted'));
    }
    
    /**
     * Fecha a modal de exclusão
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->discountToDelete = null;
        $this->discount_id = null;
    }
    
    /**
     * Aprova um desconto salarial
     */
    public function approve(int $id): void
    {
        $discount = SalaryDiscount::findOrFail($id);
        $discount->status = 'approved';
        $discount->approved_by = Auth::id();
        $discount->approved_at = now();
        $discount->save();
        
        session()->flash('message', __('messages.discount_approved'));
    }
    
    /**
     * Rejeita um desconto salarial
     */
    public function reject(int $id): void
    {
        $discount = SalaryDiscount::findOrFail($id);
        $discount->status = 'rejected';
        $discount->approved_by = Auth::id();
        $discount->approved_at = now();
        $discount->save();
        
        session()->flash('message', __('messages.discount_rejected'));
    }
    
    /**
     * Fecha as modais
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showViewModal = false;
        $this->showPaymentModal = false;
    }
    
    /**
     * Atualiza número de parcelas e recalcula valor da parcela
     */
    public function updatedInstallments(): void
    {
        $this->calculateInstallmentAmount();
    }
    
    /**
     * Atualiza montante e recalcula valor da parcela
     */
    public function updatedAmount(): void
    {
        $this->calculateInstallmentAmount();
    }
}

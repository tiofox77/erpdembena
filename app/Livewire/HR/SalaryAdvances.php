<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryAdvancePayment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SalaryAdvances extends Component
{
    use WithPagination;
    
    // Propriedades do formulário
    public ?int $advance_id = null;
    public ?int $employee_id = null;
    public ?string $request_date = null;
    public ?float $amount = null;
    public ?int $installments = 1;
    public ?float $installment_amount = null;
    public ?string $first_deduction_date = null;
    public ?string $reason = null;
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
    
    // Adiantamento para visualização completa
    public ?SalaryAdvance $viewAdvance = null;
    
    // Adiantamento para modal de pagamento
    public ?SalaryAdvance $paymentAdvance = null;
    
    // Adiantamento para modal de exclusão
    public ?SalaryAdvance $advanceToDelete = null;
    
    // Filtros
    public array $filters = [
        'search' => '',
        'status' => '',
        'date_from' => '',
        'date_to' => '',
        'employee_id' => '',
    ];
    
    // Ordenação
    public string $sortField = 'request_date';
    public string $sortDirection = 'desc';
    
    // Proteção contra mass assignment
    protected $listeners = ['refreshSalaryAdvances' => '$refresh'];
    
    /**
     * Regras de validação para o formulário de adiantamento
     */
    protected function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'request_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'installments' => 'required|integer|min:1|max:12',
            'first_deduction_date' => 'required|date|after_or_equal:request_date',
            'reason' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected,completed',
            'notes' => 'nullable|string',
        ];
    }
    
    // Regras de validação para pagamentos foram movidas para o método único abaixo
    
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
            'installment_amount' => __('messages.installment_amount'),
            'first_deduction_date' => __('messages.first_deduction_date'),
            'reason' => __('messages.reason'),
            'status' => __('messages.status'),
            'notes' => __('messages.notes'),
            'payment_date' => __('messages.payment_date'),
            'payment_amount' => __('messages.payment_amount'),
            'installment_number' => __('messages.installment_number'),
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
    }
    
    /**
     * Fecha a modal de visualização
     *
     * @return void
     */
    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->currentViewTab = 'details'; // Reset para a aba padrão
        $this->resetErrorBag();
        $this->resetValidation();
    }
    
    // Método view() removido para evitar duplicação - usando o método existente na linha 247
    
    /**
     * Método para renderizar o componente
     */
    public function render()
    {
        $employees = Employee::orderBy('full_name')->get();
        
        $salaryAdvancesQuery = SalaryAdvance::query()
            ->when($this->filters['search'] ?? false, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->whereHas('employee', function ($qe) use ($search) {
                        $qe->where('full_name', 'like', "%{$search}%");
                    })
                    ->orWhere('reason', 'like', "%{$search}%");
                });
            })
            ->when($this->filters['employee_id'] ?? false, function ($query, $employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when($this->filters['status'] ?? false, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($this->filters['date_from'] ?? false, function ($query, $dateFrom) {
                return $query->where('request_date', '>=', $dateFrom);
            })
            ->when($this->filters['date_to'] ?? false, function ($query, $dateTo) {
                return $query->where('request_date', '<=', $dateTo);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $salaryAdvances = $salaryAdvancesQuery->paginate(10);
        
        return view('livewire.hr.salary-advances', [
            'salaryAdvances' => $salaryAdvances,
            'employees' => $employees,
        ]);
    }
    
    /**
     * Método para ordenar os registos
     */
    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    /**
     * Cria um novo registo de adiantamento salarial
     */
    public function create(): void
    {
        $this->reset(['advance_id', 'employee_id', 'request_date', 'amount', 'installments', 'installment_amount', 'first_deduction_date', 'reason', 'status', 'notes']);
        $this->status = 'pending';
        $this->request_date = date('Y-m-d');
        $this->installments = 1;
        $this->isEditing = false;
        $this->showModal = true;
    }
    
    /**
     * Edita um registo de adiantamento salarial existente
     */
    public function edit(int $id): void
    {
        $this->advance_id = $id;
        $this->isEditing = true;
        
        $advance = SalaryAdvance::findOrFail($id);
        $this->employee_id = $advance->employee_id;
        $this->request_date = $advance->request_date->format('Y-m-d');
        $this->amount = (float) $advance->amount; // Conversão explícita para float
        $this->installments = $advance->installments;
        $this->installment_amount = (float) $advance->installment_amount; // Conversão explícita para float
        $this->first_deduction_date = $advance->first_deduction_date->format('Y-m-d');
        $this->reason = $advance->reason;
        $this->status = $advance->status;
        $this->notes = $advance->notes;
        
        $this->showModal = true;
    }
    
    /**
     * Visualiza os detalhes de um adiantamento salarial
     *
     * @param int $id ID do adiantamento a ser visualizado
     * @return void
     */
    public function view(int $id): void
    {
        $this->advance_id = $id;
        
        $advance = SalaryAdvance::with(['employee', 'approver', 'payments'])->findOrFail($id);
        $this->viewAdvance = $advance; // Atribuir o objeto completo à propriedade viewAdvance
        
        $this->employee_id = $advance->employee_id;
        $this->request_date = $advance->request_date->format('Y-m-d');
        $this->amount = $advance->amount;
        $this->installments = $advance->installments;
        $this->installment_amount = $advance->installment_amount;
        $this->first_deduction_date = $advance->first_deduction_date->format('Y-m-d');
        $this->reason = $advance->reason;
        $this->status = $advance->status;
        $this->notes = $advance->notes;
        
        // Definir a aba inicial como "details"
        $this->currentViewTab = 'details';
        $this->showViewModal = true;
    }
    
    /**
     * Calcula o valor da parcela com base no montante total e número de parcelas
     */
    public function calculateInstallmentAmount(): void
    {
        if ($this->amount && $this->installments && $this->installments > 0) {
            $this->installment_amount = $this->amount / $this->installments;
        } else {
            $this->installment_amount = null;
        }
    }
    
    /**
     * Salva o registo de adiantamento salarial
     */
    public function save(): void
    {
        $this->calculateInstallmentAmount();
        $this->validate();
        
        if ($this->advance_id) {
            $advance = SalaryAdvance::findOrFail($this->advance_id);
        } else {
            $advance = new SalaryAdvance();
        }
        
        $advance->employee_id = $this->employee_id;
        $advance->request_date = $this->request_date;
        $advance->amount = $this->amount;
        $advance->installments = $this->installments;
        $advance->installment_amount = $this->installment_amount;
        $advance->first_deduction_date = $this->first_deduction_date;
        $advance->remaining_installments = $this->installments;
        $advance->reason = $this->reason;
        $advance->status = $this->status;
        $advance->notes = $this->notes;
        
        if ($this->status === 'approved' && $advance->approved_by === null) {
            $advance->approved_by = Auth::id();
            $advance->approved_at = now();
        }
        
        $advance->save();
        
        $this->showModal = false;
        $this->reset(['advance_id', 'employee_id', 'request_date', 'amount', 'installments', 'installment_amount', 'first_deduction_date', 'reason', 'notes']);
        
        if ($this->isEditing) {
            session()->flash('message', __('messages.advance_updated'));
        } else {
            session()->flash('message', __('messages.advance_created'));
        }
    }
    
    /**
     * Abre o modal para registar um pagamento de parcela
     */
    public function registerPaymentModal(int $id): void
    {
        $this->advance_id = $id;
        $advance = SalaryAdvance::with('employee')->findOrFail($id);
        $this->paymentAdvance = $advance; // Carregar o adiantamento para uso na modal
        
        $this->payment_amount = $advance->installment_amount;
        $this->payment_date = date('Y-m-d');
        $this->installment_number = $advance->installments - $advance->remaining_installments + 1;
        $this->payment_type = 'installment'; // Definir valor padrão
        
        $this->showPaymentModal = true;
    }
    
    /**
     * Atualiza o valor do pagamento baseado no tipo selecionado
     */
    public function updatedPaymentType(): void
    {
        if (!$this->paymentAdvance) {
            return;
        }
        
        switch ($this->payment_type) {
            case 'installment':
                $this->payment_amount = $this->paymentAdvance->installment_amount;
                break;
            case 'full':
                $this->payment_amount = $this->paymentAdvance->remaining_amount;
                break;
            case 'custom':
                // Mantém o valor atual ou define um valor padrão
                if (!$this->payment_amount) {
                    $this->payment_amount = 0;
                }
                break;
        }
    }
    
    /**
     * Processa um pagamento de parcela
     */
    public function processPayment(): void
    {
        $this->validate($this->paymentRules());
        
        $advance = SalaryAdvance::findOrFail($this->advance_id);
        
        // Define o valor do pagamento baseado no tipo
        if ($this->payment_type === 'installment') {
            $this->payment_amount = $advance->installment_amount;
        } elseif ($this->payment_type === 'full') {
            $this->payment_amount = $advance->remaining_amount;
        }
        
        // Se for um pagamento completo, ajusta o número de parcelas
        $installmentNumber = $this->payment_type === 'full' 
            ? 0  // 0 significa pagamento completo
            : $this->installment_number;
        
        try {
            $advance->registerPayment(
                $this->payment_amount,
                $this->payment_date,
                $installmentNumber,
                Auth::id(),
                $this->payment_notes // Passar as notas de pagamento
            );
            
            $this->showPaymentModal = false;
            $this->reset(['payment_amount', 'payment_date', 'installment_number', 'payment_type', 'payment_notes']);
            $this->paymentAdvance = null;
            
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
        $this->paymentAdvance = null;
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
            
            if ($this->paymentAdvance) {
                $rules['payment_amount'][] = 'max:' . $this->paymentAdvance->remaining_amount;
            }
        }
        
        return $rules;
    }
    
    /**
     * Confirma a exclusão do registo
     */
    public function confirmDelete(int $id): void
    {
        $this->advance_id = $id;
        // Carrega o adiantamento com a relação employee para exibição na modal
        $this->advanceToDelete = SalaryAdvance::with('employee')->findOrFail($id);
        $this->showDeleteModal = true;
    }
    
    /**
     * Exclui o registo de adiantamento salarial
     */
    public function delete(): void
    {
        SalaryAdvance::findOrFail($this->advance_id)->delete();
        $this->closeDeleteModal();
        
        session()->flash('message', __('messages.advance_deleted'));
    }
    
    /**
     * Fecha a modal de exclusão
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->advanceToDelete = null;
        $this->advance_id = null;
    }
    
    /**
     * Aprova um adiantamento salarial
     */
    public function approve(int $id): void
    {
        $advance = SalaryAdvance::findOrFail($id);
        $advance->status = 'approved';
        $advance->approved_by = Auth::id();
        $advance->approved_at = now();
        $advance->save();
        
        session()->flash('message', __('messages.advance_approved'));
    }
    
    /**
     * Rejeita um adiantamento salarial
     */
    public function reject(int $id): void
    {
        $advance = SalaryAdvance::findOrFail($id);
        $advance->status = 'rejected';
        $advance->approved_by = Auth::id();
        $advance->approved_at = now();
        $advance->save();
        
        session()->flash('message', __('messages.advance_rejected'));
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

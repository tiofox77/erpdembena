<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\SalaryAdvance;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\HR\SalaryAdvancePayment;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;

class SalaryAdvances extends Component
{
    use WithPagination, WithFileUploads;
    
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
    public $signed_document = null;
    public ?string $existing_signed_document = null;
    
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
    public $filters = [
        'search' => '',
        'status' => '',
        'amount_min' => '',
        'amount_max' => '',
        'date_from' => '',
        'date_to' => '',
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
            'signed_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
     * Computed property para obter os adiantamentos salariais filtrados
     */
    public function getSalaryAdvancesProperty()
    {
        $query = SalaryAdvance::select('salary_advances.*')
            ->with('employee')
            ->when($this->filters['search'], function ($q) {
                $q->whereHas('employee', function ($eq) {
                    $eq->where('full_name', 'like', '%' . $this->filters['search'] . '%')
                       ->orWhere('id_card', 'like', '%' . $this->filters['search'] . '%');
                });
            })
            ->when($this->filters['status'], fn($q) => $q->where('status', $this->filters['status']))
            ->when($this->filters['amount_min'], fn($q) => $q->where('amount', '>=', $this->filters['amount_min']))
            ->when($this->filters['amount_max'], fn($q) => $q->where('amount', '<=', $this->filters['amount_max']))
            ->when($this->filters['date_from'], fn($q) => $q->whereDate('request_date', '>=', $this->filters['date_from']))
            ->when($this->filters['date_to'], fn($q) => $q->whereDate('request_date', '<=', $this->filters['date_to']))
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate(15);
    }

    /**
     * Método para renderizar o componente
     */
    public function render()
    {
        $employees = Employee::orderBy('full_name')->get();
        
        return view('livewire.hr.salary-advances', [
            'advances' => $this->salaryAdvances,
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
        $this->reset(['advance_id', 'employee_id', 'request_date', 'amount', 'installments', 'installment_amount', 'first_deduction_date', 'reason', 'status', 'notes', 'signed_document', 'existing_signed_document']);
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
        $this->employee_id = (int) $advance->employee_id;
        $this->request_date = $advance->request_date->format('Y-m-d');
        $this->amount = (float) $advance->amount; // Conversão explícita para float
        $this->installments = (int) $advance->installments;
        $this->installment_amount = (float) $advance->installment_amount; // Conversão explícita para float
        $this->first_deduction_date = $advance->first_deduction_date->format('Y-m-d');
        $this->reason = $advance->reason;
        $this->status = $advance->status;
        $this->notes = $advance->notes;
        $this->existing_signed_document = $advance->signed_document;
        
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
        
        $advance = SalaryAdvance::with(['employee', 'approver', 'creator', 'payments'])->findOrFail($id);
        $this->viewAdvance = $advance; // Atribuir o objeto completo à propriedade viewAdvance
        
        $this->employee_id = (int) $advance->employee_id;
        $this->request_date = $advance->request_date->format('Y-m-d');
        $this->amount = (float) $advance->amount;
        $this->installments = (int) $advance->installments;
        $this->installment_amount = (float) $advance->installment_amount;
        $this->first_deduction_date = $advance->first_deduction_date->format('Y-m-d');
        $this->reason = $advance->reason;
        $this->status = $advance->status;
        $this->notes = $advance->notes;
        
        // Definir a aba inicial como "details"
        $this->currentViewTab = 'details';
        $this->showViewModal = true;
    }
    
    /**
     * Remove o documento assinado
     */
    public function removeSignedDocument(): void
    {
        if ($this->advance_id && $this->existing_signed_document) {
            $advance = SalaryAdvance::find($this->advance_id);
            if ($advance && $advance->signed_document && \Storage::disk('public')->exists($advance->signed_document)) {
                \Storage::disk('public')->delete($advance->signed_document);
                $advance->signed_document = null;
                $advance->save();
            }
        }
        
        $this->existing_signed_document = null;
        session()->flash('message', __('messages.document_removed_successfully'));
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
            $advance->created_by = Auth::id();
        }
        
        $advance->employee_id = $this->employee_id;
        $advance->request_date = $this->request_date;
        $advance->amount = $this->amount;
        $advance->installments = $this->installments;
        $advance->installment_amount = $this->installment_amount;
        $advance->first_deduction_date = $this->first_deduction_date;
        
        // Só atualiza remaining_installments se for novo registro
        if (!$this->advance_id) {
            $advance->remaining_installments = $this->installments;
        }
        
        $advance->reason = $this->reason;
        $advance->status = $this->status;
        $advance->notes = $this->notes;
        
        // Processar upload do documento assinado
        if ($this->signed_document) {
            // Deletar documento antigo se existir
            if ($advance->signed_document && \Storage::disk('public')->exists($advance->signed_document)) {
                \Storage::disk('public')->delete($advance->signed_document);
            }
            
            // Salvar novo documento
            $filename = 'advance_' . $this->employee_id . '_' . time() . '.' . $this->signed_document->extension();
            $path = $this->signed_document->storeAs('salary-advances', $filename, 'public');
            $advance->signed_document = $path;
        }
        
        if ($this->status === 'approved' && $advance->approved_by === null) {
            $advance->approved_by = Auth::id();
            $advance->approved_at = now();
        }
        
        $advance->save();
        
        $this->showModal = false;
        $this->reset(['advance_id', 'employee_id', 'request_date', 'amount', 'installments', 'installment_amount', 'first_deduction_date', 'reason', 'notes', 'signed_document', 'existing_signed_document']);
        
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
        $this->paymentAdvance = $advance;
        
        // Reset campos
        $this->payment_date = date('Y-m-d');
        $this->installment_number = $advance->installments - $advance->remaining_installments + 1;
        $this->payment_type = 'installment';
        $this->payment_notes = null;
        
        // Definir valor inicial para parcela
        $this->payment_amount = (float) $advance->installment_amount;
        
        $this->showPaymentModal = true;
    }
    
    /**
     * Atualiza o valor do pagamento baseado no tipo selecionado
     */
    public function updatedPaymentType(): void
    {
        $this->resetErrorBag('payment_amount');
        
        if (!$this->paymentAdvance) {
            return;
        }
        
        switch ($this->payment_type) {
            case 'installment':
                $this->payment_amount = (float) $this->paymentAdvance->installment_amount;
                break;
            case 'full':
                $this->payment_amount = (float) $this->paymentAdvance->remaining_amount;
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
                $this->payment_notes
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
        return [
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_type' => ['required', 'in:installment,full'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
        ];
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

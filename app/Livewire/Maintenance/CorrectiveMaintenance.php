<?php

namespace App\Livewire\Maintenance;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\MaintenanceCorrective;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\FailureMode;
use App\Models\FailureMode as Mode;
use App\Models\FailureModeCategory as ModeCategory;
use App\Models\FailureCause;
use App\Models\FailureCause as Cause;
use App\Models\FailureCauseCategory as CauseCategory;

class CorrectiveMaintenance extends Component
{
    use WithPagination;

    // Search and filter properties
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $filterStatus = '';

    #[Url(history: true)]
    public $filterEquipment = '';

    #[Url(history: true)]
    public $filterYear = '';

    #[Url(history: true)]
    public $filterMonth = '';

    #[Url(history: true)]
    public $sortField = 'start_time';

    #[Url(history: true)]
    public $sortDirection = 'desc';

    public $perPage = 10;

    // Modal properties
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $isEditing = false;
    public $deleteId = null;
    public $viewingCorrective = null;

    // Form data
    public $corrective = [
        'year' => '',
        'month' => '',
        'week' => '',
        'system_process' => '',
        'equipment_id' => '',
        'failure_mode_id' => '',
        'failure_mode_category_id' => '',
        'failure_cause_id' => '',
        'failure_cause_category_id' => '',
        'start_time' => '',
        'end_time' => '',
        'downtime_length' => '',
        'description' => '',
        'actions_taken' => '',
        'reported_by' => '',
        'resolved_by' => '',
        'status' => 'open',
    ];

    // Validation rules
    protected function rules()
    {
        return [
            'corrective.year' => 'required|numeric|digits:4',
            'corrective.month' => 'required|numeric|min:1|max:12',
            'corrective.week' => 'nullable|numeric|min:1|max:53',
            'corrective.system_process' => 'required|string|max:255',
            'corrective.equipment_id' => 'required|exists:maintenance_equipment,id',
            'corrective.failure_mode_id' => 'required|exists:failure_modes,id',
            'corrective.failure_mode_category_id' => 'required|exists:failure_mode_categories,id',
            'corrective.failure_cause_id' => 'required|exists:failure_causes,id',
            'corrective.failure_cause_category_id' => 'required|exists:failure_cause_categories,id',
            'corrective.start_time' => 'required|date',
            'corrective.end_time' => 'nullable|date|after_or_equal:corrective.start_time',
            'corrective.downtime_length' => 'nullable|string',
            'corrective.description' => 'nullable|string',
            'corrective.actions_taken' => 'nullable|string',
            'corrective.reported_by' => 'nullable|exists:users,id',
            'corrective.resolved_by' => 'nullable|exists:technicians,id',
            'corrective.status' => 'required|in:open,in_progress,resolved,closed',
        ];
    }

    // Custom error messages
    protected function messages()
    {
        return [
            'corrective.year.required' => 'Year is required',
            'corrective.month.required' => 'Month is required',
            'corrective.system_process.required' => 'System/Process is required',
            'corrective.equipment_id.required' => 'Equipment is required',
            'corrective.equipment_id.exists' => 'Selected equipment is invalid',
            'corrective.failure_mode_id.required' => 'Failure Mode is required',
            'corrective.failure_mode_id.exists' => 'Selected failure mode is invalid',
            'corrective.failure_mode_category_id.required' => 'Failure Mode Category is required',
            'corrective.failure_mode_category_id.exists' => 'Selected failure mode category is invalid',
            'corrective.failure_cause_id.required' => 'Failure Cause is required',
            'corrective.failure_cause_id.exists' => 'Selected failure cause is invalid',
            'corrective.failure_cause_category_id.required' => 'Failure Cause Category is required',
            'corrective.failure_cause_category_id.exists' => 'Selected failure cause category is invalid',
            'corrective.start_time.required' => 'Start time is required',
            'corrective.end_time.after_or_equal' => 'End time must be after start time',
            'corrective.status.required' => 'Status is required',
        ];
    }

    public function mount()
    {
        // Set default year and month to current date if not set
        if (empty($this->filterYear)) {
            $this->filterYear = now()->year;
        }

        if (empty($this->filterMonth)) {
            $this->filterMonth = now()->month;
        }

        // Set current user as reporter by default
        $this->corrective['reported_by'] = Auth::id();
        $this->corrective['year'] = now()->year;
        $this->corrective['month'] = now()->month;
        $this->corrective['week'] = now()->week;
    }

    // Reset pagination when filters change
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterEquipment()
    {
        $this->resetPage();
    }

    public function updatedFilterYear()
    {
        $this->resetPage();
    }

    public function updatedFilterMonth()
    {
        $this->resetPage();
    }

    // Reset pagination when perPage changes
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Clear all filters and reset to default values
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterEquipment = '';
        $this->filterYear = '';
        $this->filterMonth = '';
        $this->perPage = 10;
        $this->resetPage();

        $this->dispatch('filters-cleared');

        $notificationType = 'info';
        $message = 'All filters have been reset.';
        $this->dispatch('notify', type: $notificationType, message: $message);
    }

    // Real-time validation
    public function updated($propertyName)
    {
        if (strpos($propertyName, 'corrective.') === 0) {
            $this->validateOnly($propertyName);
        }

        // Auto-calculate downtime when start and end time are set
        if ($propertyName === 'corrective.start_time' || $propertyName === 'corrective.end_time') {
            $this->calculateDowntime();
        }
        
        // NÃO atualizar automaticamente a categoria quando o modo de falha é alterado
        if ($propertyName === 'corrective.failure_mode_id' && !empty($this->corrective['failure_mode_id'])) {
            // Apenas registrar a mudança para debug
            $failureMode = \App\Models\FailureMode::find($this->corrective['failure_mode_id']);
            if ($failureMode) {
                \Log::info('Categoria atualizada automaticamente', [
                    'failure_mode_id' => $this->corrective['failure_mode_id'],
                    'category_id' => $failureMode->category_id
                ]);
            }
        }
    }

    // Calculate downtime based on start and end time
    private function calculateDowntime()
    {
        if (!empty($this->corrective['start_time']) && !empty($this->corrective['end_time'])) {
            $start = Carbon::parse($this->corrective['start_time']);
            $end = Carbon::parse($this->corrective['end_time']);

            if ($end->gt($start)) {
                $diffInHours = $end->diffInSeconds($start) / 3600; // Convert seconds to hours
                $this->corrective['downtime_length'] = number_format($diffInHours, 2);
            }
        }
    }

    // Sorting
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Open modal for creating a new corrective record
    public function openModal()
    {
        $this->resetValidation();
        $this->reset('corrective');
        $this->isEditing = false;

        // Set default values
        $this->corrective = [
            'year' => now()->year,
            'month' => now()->month,
            'week' => now()->weekOfYear,
            'system_process' => '',
            'equipment_id' => '',
            'failure_mode_id' => null,
            'failure_mode_category_id' => null,
            'failure_cause_id' => null,
            'failure_cause_category_id' => null,
            'start_time' => now()->format('Y-m-d H:i'),
            'end_time' => null,
            'downtime_length' => '',
            'description' => '',
            'actions_taken' => '',
            'reported_by' => Auth::id(),
            'resolved_by' => null,
            'status' => 'open',
        ];

        $this->showModal = true;
    }

    // Open modal for editing a corrective record
    public function edit($id)
    {
        $this->resetValidation();
        $this->isEditing = true;

        // Carregar o registro com suas relações
        $correctiveRecord = MaintenanceCorrective::with([
            'failureMode.category', 
            'failureCause.category'
        ])->findOrFail($id);

        // Para debug
        \Log::info('Editando registro de manutenção corretiva', [
            'id' => $id,
            'has_failure_mode' => isset($correctiveRecord->failureMode),
            'failure_mode_id' => $correctiveRecord->failure_mode_id,
            'failure_mode' => (is_object($correctiveRecord->failureMode)) ? $correctiveRecord->failureMode->toArray() : $correctiveRecord->failureMode
        ]);

        // Buscar diretamente a categoria do modo de falha do banco de dados
        $failureModeCategory = null;
        if (!empty($correctiveRecord->failure_mode_id)) {
            // Buscar diretamente do banco de dados, ignorando o relacionamento carregado
            $failureMode = \App\Models\FailureMode::find($correctiveRecord->failure_mode_id);
            
            if ($failureMode) {
                $failureModeCategory = $failureMode->category_id;
                
                // Registrar para debug
                \Log::info('Categoria obtida diretamente do banco de dados', [
                    'failure_mode_id' => $correctiveRecord->failure_mode_id,
                    'category_id' => $failureModeCategory
                ]);
            }
        }

        // Buscar diretamente a categoria da causa de falha do banco de dados
        $failureCauseCategory = null;
        if (!empty($correctiveRecord->failure_cause_id)) {
            // Buscar diretamente do banco de dados, ignorando o relacionamento carregado
            $failureCause = \App\Models\FailureCause::find($correctiveRecord->failure_cause_id);
            
            if ($failureCause) {
                $failureCauseCategory = $failureCause->category_id;
                
                // Registrar para debug
                \Log::info('Categoria da causa obtida diretamente do banco de dados', [
                    'failure_cause_id' => $correctiveRecord->failure_cause_id,
                    'category_id' => $failureCauseCategory
                ]);
            }
        }

        // Inicializar os dados do formulário
        $this->corrective = [
            'id' => $correctiveRecord->id,
            'year' => $correctiveRecord->year,
            'month' => $correctiveRecord->month,
            'week' => $correctiveRecord->week,
            'system_process' => $correctiveRecord->system_process,
            'equipment_id' => $correctiveRecord->equipment_id,
            'failure_mode_id' => $correctiveRecord->failure_mode_id,
            'failure_mode_category_id' => $failureModeCategory,
            'failure_cause_id' => $correctiveRecord->failure_cause_id,
            'failure_cause_category_id' => $failureCauseCategory,
            'start_time' => $correctiveRecord->start_time ? $correctiveRecord->start_time->format('Y-m-d H:i') : null,
            'end_time' => $correctiveRecord->end_time ? $correctiveRecord->end_time->format('Y-m-d H:i') : null,
            'downtime_length' => $correctiveRecord->downtime_length,
            'description' => $correctiveRecord->description,
            'actions_taken' => $correctiveRecord->actions_taken,
            'reported_by' => $correctiveRecord->reported_by,
            'resolved_by' => $correctiveRecord->resolved_by,
            'status' => $correctiveRecord->status,
        ];

        // Registrar os valores para debug
        \Log::info('Valores definidos no formulário', [
            'failure_mode_id' => $this->corrective['failure_mode_id'],
            'failure_mode_category_id' => $this->corrective['failure_mode_category_id'],
        ]);

        $this->showModal = true;
        
        // Disparar evento para que o Alpine.js saiba que a modal foi aberta
        $this->dispatch('modal-opened', [
            'isEditing' => $this->isEditing,
            'failure_mode_id' => $this->corrective['failure_mode_id'],
            'failure_mode_category_id' => $this->corrective['failure_mode_category_id'],
            'failure_cause_id' => $this->corrective['failure_cause_id'],
            'failure_cause_category_id' => $this->corrective['failure_cause_category_id'],
        ]);
    }

    // View details of a corrective record
    public function view($id)
    {
        try {
            Log::info("View method called with ID: $id");

            // Reset any existing states
            $this->showModal = false;
            $this->showDeleteModal = false;

            $record = MaintenanceCorrective::with([
                'equipment',
                'reporter',
                'resolver',
                'failureMode.category',
                'failureCause.category'
            ])->findOrFail($id);

            Log::info("Record found, ID: " . $record->id);

            $this->viewingCorrective = $record;
            $this->showViewModal = true;

            Log::info("View modal opened, showViewModal = " . ($this->showViewModal ? 'true' : 'false'));
        } catch (\Exception $e) {
            Log::error("Error in view method: " . $e->getMessage());
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Error loading record: ' . $e->getMessage()
            );
        }
    }

    // Add a specific close view modal method
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingCorrective = null;
    }

    // Close all modals
    public function closeModal()
    {
        $this->showModal = false;
        $this->showViewModal = false;
        $this->showDeleteModal = false;
        $this->isEditing = false;
        $this->viewingCorrective = null;
        $this->deleteId = null;
        $this->resetValidation();
    }

    // Save or update a corrective record
    public function save()
    {
        // Validate the form data
        $this->validate();

        try {
            // Calculate downtime if both start and end time are set
            if (!empty($this->corrective['start_time']) && !empty($this->corrective['end_time'])) {
                $this->calculateDowntime();
            }

            $isCreate = !isset($this->corrective['id']);
            $id = $this->corrective['id'] ?? null;

            // Get or create the corrective maintenance record
            $correctiveRecord = $isCreate
                ? new MaintenanceCorrective()
                : MaintenanceCorrective::findOrFail($id);

            // Set the fields
            $correctiveRecord->year = $this->corrective['year'];
            $correctiveRecord->month = $this->corrective['month'];
            $correctiveRecord->week = $this->corrective['week'];
            $correctiveRecord->system_process = $this->corrective['system_process'];
            $correctiveRecord->equipment_id = $this->corrective['equipment_id'];

            // Registrando os valores para debug antes de salvar
            \Log::info('Valores recebidos no formulário antes de salvar', [
                'failure_mode_id' => $this->corrective['failure_mode_id'] ?? null,
                'failure_mode_category_id' => $this->corrective['failure_mode_category_id'] ?? null,
                'failure_cause_id' => $this->corrective['failure_cause_id'] ?? null,
                'failure_cause_category_id' => $this->corrective['failure_cause_category_id'] ?? null
            ]);
            
            // NÃO alterar o modo de falha com base na categoria - manter os campos independentes
            // Registrar apenas para fins de debug se houver inconsistência
            if (!empty($this->corrective['failure_mode_id'])) {
                $failureMode = \App\Models\FailureMode::find($this->corrective['failure_mode_id']);
                if ($failureMode) {
                    if (!empty($this->corrective['failure_mode_category_id']) && $failureMode->category_id != $this->corrective['failure_mode_category_id']) {
                        \Log::info('Modo de falha e categoria inconsistentes, mantendo seleção do usuário', [
                            'failure_mode_id' => $this->corrective['failure_mode_id'],
                            'failure_mode_category' => $failureMode->category_id,
                            'selected_category' => $this->corrective['failure_mode_category_id']
                        ]);
                    }
                }
            }
            
            // O mesmo para causa de falha
            if (!empty($this->corrective['failure_cause_id'])) {
                $failureCause = \App\Models\FailureCause::find($this->corrective['failure_cause_id']);
                if ($failureCause) {
                    if (!empty($this->corrective['failure_cause_category_id']) && $failureCause->category_id != $this->corrective['failure_cause_category_id']) {
                        \Log::info('Causa de falha e categoria inconsistentes, mantendo seleção do usuário', [
                            'failure_cause_id' => $this->corrective['failure_cause_id'],
                            'failure_cause_category' => $failureCause->category_id,
                            'selected_category' => $this->corrective['failure_cause_category_id']
                        ]);
                    }
                }
            }
            
            // Agora usamos os IDs potencialmente atualizados
            $correctiveRecord->failure_mode_id = $this->corrective['failure_mode_id'];
            $correctiveRecord->failure_cause_id = $this->corrective['failure_cause_id'];

            $correctiveRecord->start_time = $this->corrective['start_time'];
            $correctiveRecord->end_time = $this->corrective['end_time'];
            $correctiveRecord->downtime_length = $this->corrective['downtime_length'];
            $correctiveRecord->description = $this->corrective['description'];
            $correctiveRecord->actions_taken = $this->corrective['actions_taken'];
            $correctiveRecord->reported_by = $this->corrective['reported_by'];
            
            // Verificando se o valor de resolved_by é válido antes de atribuir
            // A estrutura do banco agora exige que o resolved_by seja um ID válido da tabela technicians
            try {
                // Registrar para debug
                \Log::info('Tentando definir resolved_by', [
                    'status' => $this->corrective['status'],
                    'resolved_by_input' => $this->corrective['resolved_by'] ?? 'null'
                ]);
                
                // Se o status for 'resolved' ou 'closed', configuramos o resolved_by
                if ($this->corrective['status'] === 'resolved' || $this->corrective['status'] === 'closed') {
                    // Definir como o valor do técnico selecionado no formulário
                    if (!empty($this->corrective['resolved_by'])) {
                        $correctiveRecord->resolved_by = $this->corrective['resolved_by'];
                    } else {
                        $correctiveRecord->resolved_by = null;
                    }
                } else {
                    // Se não estiver resolvido ou fechado, definir como null
                    $correctiveRecord->resolved_by = null;
                }
            } catch (\Exception $e) {
                // Em caso de erro, definir como null para evitar falha no salvamento
                \Log::error('Erro ao configurar resolved_by: ' . $e->getMessage());
                $correctiveRecord->resolved_by = null;
            }
            $correctiveRecord->status = $this->corrective['status'];

            // Save the record
            $correctiveRecord->save();

            // Close the modal
            $this->showModal = false;

            // Show success message using named parameters
            $this->dispatch(
                'notify',
                type: $isCreate ? 'success' : 'info',
                message: $isCreate
                    ? 'Equipment downtime reported successfully!'
                    : 'Equipment downtime updated successfully!'
            );

        } catch (\Exception $e) {
            Log::error('Error saving corrective maintenance: ' . $e->getMessage());

            // Show error message using named parameters
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Error: ' . $e->getMessage()
            );
        }
    }

    // Confirm deletion of a corrective record
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    // Delete a corrective record
    public function delete()
    {
        try {
            $corrective = MaintenanceCorrective::findOrFail($this->deleteId);
            $corrective->delete();

            $this->dispatch('notify', type: 'warning', message: 'Corrective maintenance record deleted successfully');
            $this->closeModal();

        } catch (\Exception $e) {
            Log::error('Error deleting corrective maintenance record: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting record: ' . $e->getMessage());
        }
    }

    // Get equipment options for dropdown
    public function getEquipmentOptions()
    {
        return MaintenanceEquipment::orderBy('name')->get();
    }

    // Get users for reporter dropdown
    public function getUserOptions()
    {
        return User::orderBy('name')->get();
    }

    // Get technicians for resolver dropdown
    public function getTechnicianOptions()
    {
        return Technician::orderBy('name')->get();
    }

    // Get available years for filtering
    public function getYearOptions()
    {
        $currentYear = now()->year;
        $years = [];

        for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
            $years[$i] = $i;
        }

        return $years;
    }

    // Get months for filtering
    public function getMonthOptions()
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    }

    // Get statuses for filtering
    public function getStatusOptions()
    {
        return MaintenanceCorrective::getStatuses();
    }

    // Computed property to get corrective records with filters applied
    public function getCorrectiveRecordsProperty()
    {
        return MaintenanceCorrective::with([
            'equipment',
            'reporter',
            'resolver',
            'failureMode.category',
            'failureCause.category'
        ])
            ->search($this->search)
            ->filterByStatus($this->filterStatus)
            ->filterByEquipment($this->filterEquipment)
            ->filterByYear($this->filterYear)
            ->filterByMonth($this->filterMonth)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // Add these methods before the render() method to get failure modes and causes
    public function getFailureModes()
    {
        return FailureMode::with('category')->where('is_active', true)->orderBy('name')->get();
    }

    public function getFailureCauses()
    {
        return FailureCause::with('category')->where('is_active', true)->orderBy('name')->get();
    }

    public function getModeCategories()
    {
        return ModeCategory::where('is_active', true)->orderBy('name')->get();
    }

    public function getCauseCategories()
    {
        return CauseCategory::where('is_active', true)->orderBy('name')->get();
    }

    // Modify the render method to use all available modes and causes regardless of category
    public function render()
    {
        return view('livewire.maintenance.corrective-maintenance', [
            'correctiveRecords' => $this->correctiveRecords,
            'equipment' => $this->getEquipmentOptions(),
            'users' => $this->getUserOptions(),
            'technicians' => $this->getTechnicianOptions(),
            'years' => $this->getYearOptions(),
            'months' => $this->getMonthOptions(),
            'statuses' => $this->getStatusOptions(),
            'failureModes' => $this->getFailureModes(), // Use all failure modes regardless of category
            'failureCauses' => $this->getFailureCauses(), // Use all failure causes regardless of category
            'modeCategories' => $this->getModeCategories(),
            'causeCategories' => $this->getCauseCategories(),
        ]);
    }

    /**
     * Quando uma categoria de modo de falha é selecionada
     * 
     * NOTA: Aqui não resetamos o modo de falha, apenas permitimos que a categoria seja selecionada
     * durante a edição sem afetar o modo de falha já selecionado
     */
    public function updatedCorrectiveFailureModeCategoryId()
    {
        // Não fazer nada - só queremos que a categoria seja exibida corretamente
        // sem limpar o modo de falha selecionado
    }
    
    /**
     * Quando um modo de falha é selecionado, atualizamos automaticamente a categoria
     * Isso garante que a categoria seja sempre consistente com o modo de falha
     */
    public function updatedCorrectiveFailureModeId($value)
    {
        if (!empty($value)) {
            // Buscar a categoria do modo de falha selecionado
            $mode = FailureMode::find($value);
            if ($mode && $mode->category_id) {
                $this->corrective['failure_mode_category_id'] = $mode->category_id;
                \Log::info('Categoria atualizada automaticamente', [
                    'failure_mode_id' => $value,
                    'category_id' => $mode->category_id
                ]);
            }
        }
    }
    
    /**
     * Quando uma categoria de causa de falha é selecionada
     * 
     * NOTA: Aqui não resetamos a causa de falha, apenas permitimos que a categoria seja selecionada
     * durante a edição sem afetar a causa de falha já selecionada
     */
    public function updatedCorrectiveFailureCauseCategoryId()
    {
        // Não fazer nada - só queremos que a categoria seja exibida corretamente
        // sem limpar a causa de falha selecionada
    }
    
    /**
     * Quando uma causa de falha é selecionada, atualizamos automaticamente a categoria
     * Isso garante que a categoria seja sempre consistente com a causa de falha
     */
    public function updatedCorrectiveFailureCauseId($value)
    {
        if (!empty($value)) {
            // Buscar a categoria da causa de falha selecionada
            $cause = FailureCause::find($value);
            if ($cause && $cause->category_id) {
                $this->corrective['failure_cause_category_id'] = $cause->category_id;
                \Log::info('Categoria de causa atualizada automaticamente', [
                    'failure_cause_id' => $value,
                    'category_id' => $cause->category_id
                ]);
            }
        }
    }
    
    /**
     * Get list of months for filtering
     * 
     * @return array
     */
    protected function getMonths()
    {
        return [
            1 => __('messages.january'),
            2 => __('messages.february'),
            3 => __('messages.march'),
            4 => __('messages.april'),
            5 => __('messages.may'),
            6 => __('messages.june'),
            7 => __('messages.july'),
            8 => __('messages.august'),
            9 => __('messages.september'),
            10 => __('messages.october'),
            11 => __('messages.november'),
            12 => __('messages.december'),
        ];
    }
    
    /**
     * Generate PDF for a filtered list of corrective maintenance plans
     * 
     * @return mixed Response with PDF download or null on error
     */
    public function generateListPdf()
    {
        try {
            // Apply the same filters as in the current view
            $query = MaintenanceCorrective::query();
            
            // Apply search filter if provided
            if (!empty($this->search)) {
                $search = '%' . $this->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', $search)
                      ->orWhereHas('equipment', function($eq) use ($search) {
                          $eq->where('name', 'like', $search);
                      })
                      ->orWhereHas('failureMode', function($fmq) use ($search) {
                          $fmq->where('name', 'like', $search);
                      })
                      ->orWhereHas('failureCause', function($fcq) use ($search) {
                          $fcq->where('name', 'like', $search);
                      });
                });
            }
            
            // Apply equipment filter if provided
            if (!empty($this->filterEquipment)) {
                $query->where('equipment_id', $this->filterEquipment);
            }
            
            // Apply status filter if provided
            if (!empty($this->filterStatus)) {
                $query->where('status', $this->filterStatus);
            }
            
            // Apply year filter if provided
            if (!empty($this->filterYear)) {
                $query->whereYear('start_time', $this->filterYear);
            }
            
            // Apply month filter if provided
            if (!empty($this->filterMonth)) {
                $query->whereMonth('start_time', $this->filterMonth);
            }
            
            // Apply sorting
            $query->orderBy($this->sortField, $this->sortDirection);
            
            // Eager load all relationships
            $corrective_plans = $query->with([
                'equipment', 
                'equipment.area', 
                'equipment.line', 
                'failureMode', 
                'failureCause', 
                'reporter',
                'resolver'
            ])
            ->limit(100)
            ->get();
            
            // Calcular duração para cada plano se não estiver definida
            foreach ($corrective_plans as $plan) {
                if (!$plan->duration && $plan->start_time && $plan->end_time) {
                    $plan->duration = $plan->end_time->diffForHumans($plan->start_time, true);
                }
            }
            
            // Get month and year names for filters display
            $monthName = !empty($this->filterMonth) ? $this->getMonths()[$this->filterMonth] : null;
            $yearValue = !empty($this->filterYear) ? $this->filterYear : null;
            
            // Obter nome do status traduzido
            $statusName = null;
            if (!empty($this->filterStatus)) {
                $statuses = MaintenanceCorrective::getStatuses();
                $statusName = $statuses[$this->filterStatus] ?? $this->filterStatus;
            }
            
            // Obter nome do equipamento se existir
            $equipmentName = null;
            if (!empty($this->filterEquipment)) {
                $equipment = MaintenanceEquipment::find($this->filterEquipment);
                $equipmentName = $equipment ? $equipment->name : 'Unknown';
            }
            
            // Prepare the data for the PDF
            $data = [
                'corrective_plans' => $corrective_plans,
                'title' => __('messages.corrective_maintenance_list'),
                'filters' => [
                    'status' => $statusName,
                    'equipment_name' => $equipmentName,
                    'year' => $yearValue,
                    'month' => $monthName,
                    'search' => $this->search,
                ],
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ];
            
            // Load the PDF view
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.corrective-maintenance-list', $data);
            
            $filename = 'corrective_maintenance_list_' . now()->format('Y-m-d') . '.pdf';
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_list_generated_successfully')
            );
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error generating corrective maintenance list PDF: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed') . ' - ' . $e->getMessage()
            );
            return null;
        }
    }
    
    /**
     * Exporta a lista de manutenção corretiva para PDF
     * 
     * @return mixed
     */
    public function exportToPdf()
    {
        try {
            // Get filtered data
            $corrective_plans = $this->getFilteredData(false);
            
            // Prepare filter descriptions
            $monthName = !empty($this->filterMonth) ? $this->getMonths()[$this->filterMonth] : null;
            $yearValue = !empty($this->filterYear) ? $this->filterYear : null;
            
            // Obter nome do status traduzido
            $statusName = null;
            if (!empty($this->filterStatus)) {
                $statuses = MaintenanceCorrective::getStatuses();
                $statusName = $statuses[$this->filterStatus] ?? $this->filterStatus;
            }
            
            // Prepare the data for the PDF
            $data = [
                'corrective_plans' => $corrective_plans,
                'title' => __('messages.corrective_maintenance_list'),
                'filters' => [
                    'status' => $statusName,
                    'year' => $yearValue,
                    'month' => $monthName,
                ],
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ];
            
            // Load the PDF view
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.corrective-maintenance-list', $data);
            
            $filename = 'corrective_maintenance_list_' . now()->format('Y-m-d') . '.pdf';
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error generating corrective maintenance list PDF: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed') . ' - ' . $e->getMessage()
            );
            return null;
        }
    }
    
    /**
     * Gera e disponibiliza para download um PDF para um registro específico
     * de manutenção corretiva
     * 
     * @param int $id ID do registro de manutenção corretiva
     * @return mixed
     */
    public function downloadSinglePdf($id)
    {
        try {
            $corrective = MaintenanceCorrective::with(['equipment', 'failureMode', 'failureCause', 'reporter', 'resolver'])
                                              ->findOrFail($id);
            
            // Gerar o PDF usando o método estático do modelo
            $pdfPath = MaintenanceCorrective::generatePdf($corrective);
            
            // Exibir mensagem de sucesso
            $this->dispatch(
                'notify',
                type: 'success',
                message: __('messages.pdf_generated_successfully')
            );
            
            // Retornar o caminho para download
            return response()->download(storage_path('app/public/' . $pdfPath));
        } catch (\Exception $e) {
            // Registrar erro com detalhes completos para debugging
            Log::error('Erro ao gerar PDF da manutenção corretiva: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            
            // Mostrar mensagem de erro exata para o usuário
            $this->dispatch(
                'notify',
                type: 'error',
                message: __('messages.error_generating_pdf') . ': ' . $e->getMessage()
            );
            
            return null;
        }
    }
    
    /**
     * Gera e disponibiliza para download um PDF com relatório de múltiplos registros
     * de manutenção corretiva baseado nos filtros atuais
     * 
     * @return mixed
     */
    public function downloadReportPdf()
    {
        try {
            // Criar array de filtros a partir das propriedades públicas
            $filters = [
                'year' => $this->filterYear,
                'month' => $this->filterMonth,
                'status' => $this->filterStatus,
                'equipment_id' => $this->filterEquipment
            ];
            
            // Usar os filtros atuais para gerar o relatório
            $pdfPath = MaintenanceCorrective::generatePdf(null, $filters);
            
            // Exibir mensagem de sucesso
            $this->dispatch(
                'notify',
                type: 'success',
                message: __('messages.report_pdf_generated_successfully')
            );
            
            // Retornar o caminho para download
            return response()->download(storage_path('app/public/' . $pdfPath));
        } catch (\Exception $e) {
            // Registrar erro com detalhes completos para debugging
            Log::error('Erro ao gerar relatório PDF: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
            Log::error('Filtros usados: ' . json_encode($this->filters));
            
            // Mostrar mensagem de erro exata para o usuário
            $this->dispatch(
                'notify',
                type: 'error',
                message: __('messages.error_generating_report') . ': ' . $e->getMessage()
            );
            
            return null;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\PayrollBatch as PayrollBatchModel;
use App\Models\HR\PayrollBatchItem;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Payroll;
use App\Jobs\ProcessPayrollBatch;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayrollBatch extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    
    // Modal states
    public bool $showBatchModal = false;
    public bool $showCreateModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;
    
    // Form properties
    public string $batch_name = '';
    public string $batch_description = '';
    public ?int $payroll_period_id = null;
    public ?int $department_id = null;
    public string $payment_method = 'bank_transfer';
    public string $batch_date = '';
    public array $selected_employees = [];
    public array $eligible_employees = [];
    
    // Current batch for operations
    public ?PayrollBatchModel $currentBatch = null;
    public ?PayrollBatchModel $batchToDelete = null;
    
    // Filters
    public array $filters = [
        'status' => '',
        'department_id' => '',
        'period_id' => '',
    ];

    protected $listeners = [
        'refreshBatches' => '$refresh',
        'batchProcessed' => 'handleBatchProcessed',
    ];

    public function mount(): void
    {
        Log::info('PayrollBatch component mounting');
        $this->batch_date = now()->format('Y-m-d');
        Log::info('Component mounted successfully');
    }

    /**
     * Check if user can process payroll batches
     */
    public function canProcessBatch(): bool
    {
        return auth()->user()->can('hr.payroll.process') || auth()->user()->can('hr.payroll.batch.create');
    }

    /**
     * Get payroll batches with filters
     */
    public function getPayrollBatchesProperty()
    {
        $query = PayrollBatchModel::query()
            ->with(['payrollPeriod', 'department', 'creator', 'approver', 'batchItems.employee'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filters['status'], function ($q) {
                $q->where('status', $this->filters['status']);
            })
            ->when($this->filters['department_id'], function ($q) {
                $q->where('department_id', $this->filters['department_id']);
            })
            ->when($this->filters['period_id'], function ($q) {
                $q->where('payroll_period_id', $this->filters['period_id']);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    /**
     * Get departments for filters
     */
    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }

    /**
     * Get payroll periods for filters
     */
    public function getPayrollPeriodsProperty()
    {
        return PayrollPeriod::orderBy('start_date', 'desc')->get();
    }

    /**
     * Open batch creation modal
     */
    public function openBatchModal(): void
    {
        Log::info('openBatchModal called');
        $this->resetForm();
        $this->showBatchModal = true;
        Log::info('Modal state set to true: ' . ($this->showBatchModal ? 'true' : 'false'));
    }

    /**
     * Close batch modal
     */
    public function closeBatchModal(): void
    {
        $this->showBatchModal = false;
        $this->resetForm();
    }

    /**
     * Reset form data
     */
    public function resetForm(): void
    {
        $this->batch_name = '';
        $this->batch_description = '';
        $this->payroll_period_id = null;
        $this->department_id = null;
        $this->payment_method = 'bank_transfer';
        $this->batch_date = now()->format('Y-m-d');
        $this->selected_employees = [];
        $this->eligible_employees = [];
    }

    /**
     * Load eligible employees when period is selected
     */
    public function updatedPayrollPeriodId(): void
    {
        if ($this->payroll_period_id) {
            $this->loadEligibleEmployees();
        }
    }

    /**
     * Load eligible employees when department filter changes
     */
    public function updatedDepartmentId(): void
    {
        if ($this->payroll_period_id) {
            $this->loadEligibleEmployees();
        }
    }

    /**
     * Load eligible employees for batch processing
     */
    public function loadEligibleEmployees(): void
    {
        Log::info('loadEligibleEmployees called', [
            'payroll_period_id' => $this->payroll_period_id,
            'department_id' => $this->department_id,
        ]);

        if (!$this->payroll_period_id) {
            $this->eligible_employees = [];
            Log::info('No payroll period selected, clearing eligible employees');
            return;
        }

        $query = Employee::query()
            ->with(['department', 'position'])
            ->where('employment_status', 'active')
            ->when($this->department_id, function ($q) {
                $q->where('department_id', $this->department_id);
            })
            // Exclude employees already processed in this period
            ->whereDoesntHave('payrolls', function ($payrollQuery) {
                $payrollQuery->where('payroll_period_id', $this->payroll_period_id);
            });

        $employees = $query->get();
        Log::info('Found employees for batch', ['count' => $employees->count()]);

        $this->eligible_employees = $employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'id_card' => $employee->id_card,
                'department_name' => $employee->department->name ?? 'N/A',
                'position_name' => $employee->position->name ?? 'N/A',
                'base_salary' => $employee->base_salary,
                'selected' => false,
            ];
        })->toArray();

        // Auto-select all employees
        $this->selected_employees = collect($this->eligible_employees)->pluck('id')->toArray();
        
        Log::info('Eligible employees loaded and selected', [
            'eligible_count' => count($this->eligible_employees),
            'selected_count' => count($this->selected_employees),
        ]);
    }

    /**
     * Toggle employee selection
     */
    public function toggleEmployee(int $employeeId): void
    {
        if (in_array($employeeId, $this->selected_employees)) {
            $this->selected_employees = array_diff($this->selected_employees, [$employeeId]);
        } else {
            $this->selected_employees[] = $employeeId;
        }
    }

    /**
     * Select all eligible employees
     */
    public function selectAllEmployees(): void
    {
        $this->selected_employees = collect($this->eligible_employees)->pluck('id')->toArray();
    }

    /**
     * Deselect all employees
     */
    public function deselectAllEmployees(): void
    {
        $this->selected_employees = [];
    }

    /**
     * Create new payroll batch
     */
    public function createBatch(): void
    {
        Log::info('createBatch method called', [
            'batch_name' => $this->batch_name,
            'batch_description' => $this->batch_description,
            'payroll_period_id' => $this->payroll_period_id,
            'batch_date' => $this->batch_date,
            'payment_method' => $this->payment_method,
            'selected_employees_count' => count($this->selected_employees),
            'selected_employees' => $this->selected_employees,
            'department_id' => $this->department_id,
            'eligible_employees_count' => count($this->eligible_employees),
        ]);

        $this->validate([
            'batch_name' => 'required|string|max:255',
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'batch_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'selected_employees' => 'required|array|min:1',
        ], [
            'batch_name.required' => __('livewire/hr/payroll-batch.batch_name_required_msg'),
            'payroll_period_id.required' => __('livewire/hr/payroll-batch.payroll_period_required_msg'),
            'batch_date.required' => __('livewire/hr/payroll-batch.batch_date_required_msg'),
            'selected_employees.required' => __('livewire/hr/payroll-batch.select_employees_msg'),
            'selected_employees.min' => __('livewire/hr/payroll-batch.min_employees_msg'),
        ]);

        Log::info('Validation passed successfully');

        try {
            DB::transaction(function () {
                // Create batch
                $batch = PayrollBatchModel::create([
                    'name' => $this->batch_name,
                    'description' => $this->batch_description,
                    'payroll_period_id' => $this->payroll_period_id,
                    'department_id' => $this->department_id,
                    'status' => PayrollBatchModel::STATUS_DRAFT,
                    'total_employees' => count($this->selected_employees),
                    'batch_date' => $this->batch_date,
                    'payment_method' => $this->payment_method,
                    'created_by' => Auth::id(),
                ]);

                // Create batch items
                foreach ($this->selected_employees as $employeeId) {
                    PayrollBatchItem::create([
                        'payroll_batch_id' => $batch->id,
                        'employee_id' => $employeeId,
                        'status' => PayrollBatchItem::STATUS_PENDING,
                        'processing_order' => 0,
                    ]);
                }

                $this->closeBatchModal();
                
                session()->flash('success', __('livewire/hr/payroll-batch.batch_created_success'));
                
                Log::info('Payroll batch created', [
                    'batch_id' => $batch->id,
                    'batch_name' => $batch->name,
                    'total_employees' => count($this->selected_employees),
                    'created_by' => Auth::id(),
                ]);
            });
        } catch (\Exception $e) {
            session()->flash('error', __('livewire/hr/payroll-batch.batch_creation_error', ['error' => $e->getMessage()]));
            Log::error('Error creating payroll batch', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Start batch processing
     */
    public function processBatch(int $batchId): void
    {
        Log::info('processBatch called', ['batch_id' => $batchId, 'user_id' => Auth::id()]);
        
        $batch = PayrollBatchModel::find($batchId);
        
        if (!$batch) {
            Log::error('Batch not found', ['batch_id' => $batchId]);
            session()->flash('error', __('livewire/hr/payroll-batch.batch_not_found'));
            return;
        }
        
        if (!$batch->canBeProcessed()) {
            Log::error('Batch cannot be processed', [
                'batch_id' => $batchId, 
                'status' => $batch->status,
                'total_employees' => $batch->total_employees
            ]);
            session()->flash('error', __('livewire/hr/payroll-batch.cannot_process_batch', ['status' => $batch->status_label]));
            return;
        }

        try {
            Log::info('Starting batch processing', ['batch_id' => $batch->id]);
            
            $batch->update([
                'status' => PayrollBatchModel::STATUS_PROCESSING,
                'processing_started_at' => now(),
            ]);

            // Dispatch job to process batch
            ProcessPayrollBatch::dispatch($batch);
            
            session()->flash('success', __('livewire/hr/payroll-batch.processing_started_success'));
            
            Log::info('Payroll batch processing started', [
                'batch_id' => $batch->id,
                'started_by' => Auth::id(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error starting batch processing', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', __('livewire/hr/payroll-batch.processing_start_error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * View batch details
     */
    public function viewBatch(int $batchId): void
    {
        $this->currentBatch = PayrollBatchModel::with([
            'payrollPeriod', 
            'department', 
            'creator', 
            'approver',
            'batchItems.employee',
            'batchItems.payroll'
        ])->find($batchId);
        
        $this->showViewModal = true;
    }

    /**
     * Close view modal
     */
    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->currentBatch = null;
    }

    /**
     * Confirm batch deletion
     */
    public function confirmDelete(int $batchId): void
    {
        $this->batchToDelete = PayrollBatchModel::find($batchId);
        $this->showDeleteModal = true;
    }

    /**
     * Delete batch
     */
    public function deleteBatch(): void
    {
        if (!$this->batchToDelete || !$this->batchToDelete->isEditable()) {
            session()->flash('error', 'Lote não pode ser excluído.');
            $this->closeDeleteModal();
            return;
        }

        try {
            $batchName = $this->batchToDelete->name;
            $this->batchToDelete->delete();
            
            session()->flash('success', "Lote '{$batchName}' excluído com sucesso!");
            
            Log::info('Payroll batch deleted', [
                'batch_id' => $this->batchToDelete->id,
                'batch_name' => $batchName,
                'deleted_by' => Auth::id(),
            ]);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao excluir lote: ' . $e->getMessage());
        }

        $this->closeDeleteModal();
    }

    /**
     * Close delete modal
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->batchToDelete = null;
    }

    /**
     * Handle batch processing completion
     */
    public function handleBatchProcessed($batchId): void
    {
        $this->dispatch('refreshBatches');
        session()->flash('success', 'Lote processado com sucesso!');
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->filters = [
            'status' => '',
            'department_id' => '',
            'period_id' => '',
        ];
        $this->search = '';
    }

    /**
     * Sort by field
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Debug method to test functionality
     */
    public function debugTest(): void
    {
        Log::info('DEBUG: Testing basic functionality', [
            'user_id' => Auth::id(),
            'can_process' => $this->canProcessBatch(),
            'batch_name' => $this->batch_name,
            'payroll_period_id' => $this->payroll_period_id,
            'selected_employees' => $this->selected_employees,
        ]);

        session()->flash('success', __('livewire/hr/payroll-batch.debug_executed'));
    }

    public function render()
    {
        Log::info('PayrollBatch render called', [
            'showBatchModal' => $this->showBatchModal,
            'showViewModal' => $this->showViewModal,
            'showDeleteModal' => $this->showDeleteModal,
        ]);

        $batches = $this->payrollBatches;
        $departments = $this->departments;
        $payrollPeriods = $this->payrollPeriods;

        Log::info('Data loaded successfully', [
            'batches_count' => $batches->count(),
            'departments_count' => $departments->count(),
            'periods_count' => $payrollPeriods->count(),
        ]);

        return view('livewire.hr.payroll-batch.payroll-batch', [
            'batches' => $batches,
            'departments' => $departments,
            'payrollPeriods' => $payrollPeriods,
        ]);
    }
}

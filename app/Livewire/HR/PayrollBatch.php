<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\PayrollBatch as PayrollBatchModel;
use App\Models\HR\PayrollBatchItem;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Payroll;
use App\Models\HR\Attendance;
use App\Jobs\ProcessPayrollBatch;
use App\Services\PayrollCalculationService;
use App\Services\PayrollBatchReportService;
use App\Helpers\PayrollCalculatorHelper;
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
    
    // Modals
    public bool $showBatchModal = false;
    public bool $showCreateModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;
    public bool $showEditItemModal = false;
    
    // Form properties
    public string $batch_name = '';
    public string $batch_description = '';
    public ?int $payroll_period_id = null;
    public ?int $department_id = null;
    public string $batch_date = '';
    public string $payment_method = 'bank_transfer';
    public array $selected_employees = [];
    public array $eligible_employees = [];
    
    // Current batch for operations
    public ?PayrollBatchModel $currentBatch = null;
    public ?PayrollBatchModel $batchToDelete = null;
    public ?PayrollBatchItem $editingItem = null;
    
    // Edit batch item properties
    public float $edit_gross_salary = 0;
    public float $edit_net_salary = 0;
    public float $edit_total_deductions = 0;
    public string $edit_notes = '';
    public array $calculatedData = [];
    public float $edit_additional_bonus = 0;
    public float $edit_overtime_amount = 0;
    public float $edit_advance_deduction = 0;
    public bool $edit_christmas_subsidy = false;
    public bool $edit_vacation_subsidy = false;
    
    // Related data from other tables
    public array $overtimeRecords = [];
    public array $salaryAdvances = [];
    public array $salaryDiscounts = [];
    
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
            // Exclude employees already processed individually in this period
            ->whereDoesntHave('payrolls', function ($payrollQuery) {
                $payrollQuery->where('payroll_period_id', $this->payroll_period_id);
            })
            // Exclude employees already in batch items for this period
            ->whereDoesntHave('payrollBatchItems', function ($batchItemQuery) {
                $batchItemQuery->whereHas('batch', function ($batchQuery) {
                    $batchQuery->where('payroll_period_id', $this->payroll_period_id);
                });
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

                // Create batch items with pre-calculated values
                $period = PayrollPeriod::findOrFail($this->payroll_period_id);
                $totalGross = 0;
                $totalNet = 0;
                $totalDeductions = 0;
                
                foreach ($this->selected_employees as $index => $employeeId) {
                    $employee = Employee::findOrFail($employeeId);
                    
                    // Calculate payroll using centralized service
                    try {
                        $calculator = new PayrollCalculationService($employee, $period);
                        $result = $calculator->calculate();
                        
                        PayrollBatchItem::create([
                            'payroll_batch_id' => $batch->id,
                            'employee_id' => $employeeId,
                            'status' => PayrollBatchItem::STATUS_PENDING,
                            'processing_order' => $index + 1,
                            // Pre-populate with calculated values
                            'gross_salary' => $result['gross_salary'],
                            'net_salary' => $result['net_salary'],
                            'total_deductions' => $result['deductions'],
                        ]);
                        
                        // Accumulate totals
                        $totalGross += $result['gross_salary'];
                        $totalNet += $result['net_salary'];
                        $totalDeductions += $result['deductions'];
                        
                        Log::info('Batch item created with calculated values', [
                            'employee_id' => $employeeId,
                            'employee_name' => $employee->full_name,
                            'gross_salary' => $result['gross_salary'],
                            'net_salary' => $result['net_salary'],
                        ]);
                        
                    } catch (\Exception $e) {
                        // If calculation fails, create with zeros
                        PayrollBatchItem::create([
                            'payroll_batch_id' => $batch->id,
                            'employee_id' => $employeeId,
                            'status' => PayrollBatchItem::STATUS_PENDING,
                            'processing_order' => $index + 1,
                            'gross_salary' => 0,
                            'net_salary' => 0,
                            'total_deductions' => 0,
                            'error_message' => 'Erro ao calcular: ' . $e->getMessage(),
                        ]);
                        
                        Log::error('Error calculating batch item', [
                            'employee_id' => $employeeId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                // Update batch totals
                $batch->update([
                    'total_gross_amount' => $totalGross,
                    'total_net_amount' => $totalNet,
                    'total_deductions' => $totalDeductions,
                ]);
                
                Log::info('Batch totals calculated', [
                    'batch_id' => $batch->id,
                    'total_gross' => $totalGross,
                    'total_net' => $totalNet,
                    'total_deductions' => $totalDeductions,
                ]);

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
     * Open edit modal for batch item (usando PayrollCalculatorHelper - replica modal individual)
     */
    public function editBatchItem(int $itemId): void
    {
        Log::info('=== INICIANDO editBatchItem ===', ['item_id' => $itemId]);
        
        try {
            // Carregar item
            Log::info('Carregando item do batch...');
            $this->editingItem = PayrollBatchItem::with(['employee.department', 'payroll', 'payrollBatch.payrollPeriod'])->findOrFail($itemId);
            Log::info('Item carregado', [
                'employee' => $this->editingItem->employee->full_name ?? 'N/A',
                'batch_id' => $this->editingItem->payroll_batch_id,
            ]);
            
            // Carregar valores atuais do item ou usar defaults
            $this->edit_additional_bonus = $this->editingItem->additional_bonus ?? 0;
            $this->edit_overtime_amount = $this->editingItem->overtime_amount ?? 0;
            $this->edit_advance_deduction = $this->editingItem->advance_deduction ?? 0;
            $this->edit_christmas_subsidy = $this->editingItem->christmas_subsidy ?? false;
            $this->edit_vacation_subsidy = $this->editingItem->vacation_subsidy ?? false;
            $this->edit_notes = $this->editingItem->notes ?? '';
            
            // Carregar dados relacionados do período (como no payroll individual)
            $period = $this->editingItem->payrollBatch->payrollPeriod;
            $employee = $this->editingItem->employee;
            
            // Carregar overtime records do período
            $this->overtimeRecords = \App\Models\HR\OvertimeRecord::where('employee_id', $employee->id)
                ->whereBetween('date', [$period->start_date, $period->end_date])
                ->where('status', 'approved')
                ->get()
                ->toArray();
            
            // Carregar salary advances do período
            $this->salaryAdvances = \App\Models\HR\SalaryAdvance::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function($query) use ($period) {
                    $query->whereBetween('request_date', [$period->start_date, $period->end_date])
                          ->orWhere('remaining_installments', '>', 0);
                })
                ->get()
                ->toArray();
            
            // Carregar salary discounts do período (similar a advances)
            $this->salaryDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function($query) use ($period) {
                    $query->whereBetween('request_date', [$period->start_date, $period->end_date])
                          ->orWhere('remaining_installments', '>', 0);
                })
                ->get()
                ->toArray();
            
            Log::info('Valores carregados', [
                'additional_bonus' => $this->edit_additional_bonus,
                'overtime_amount' => $this->edit_overtime_amount,
                'advance_deduction' => $this->edit_advance_deduction,
                'christmas' => $this->edit_christmas_subsidy,
                'vacation' => $this->edit_vacation_subsidy,
            ]);
            
            // Calcular usando o helper (popula $calculatedData)
            Log::info('Chamando recalculateEditingItem...');
            $this->recalculateEditingItem();
            
            // Verificar se calculatedData foi populado
            if (empty($this->calculatedData)) {
                Log::error('❌ calculatedData VAZIO após recalcular', [
                    'item_id' => $itemId,
                    'employee_id' => $this->editingItem->employee_id,
                ]);
                session()->flash('warning', 'Os dados do payroll estão sendo calculados. Aguarde...');
                // Mostra modal mesmo com calculatedData vazio - a modal vai mostrar mensagem de loading
                $this->showEditItemModal = true;
                return;
            }
            
            Log::info('✅ Modal de edição pronta para abrir', [
                'item_id' => $itemId,
                'employee' => $this->editingItem->employee->full_name,
                'calculatedData_keys' => array_keys($this->calculatedData),
                'gross_salary' => $this->calculatedData['gross_salary'] ?? 'N/A',
                'net_salary' => $this->calculatedData['net_salary'] ?? 'N/A',
            ]);
            
            $this->showEditItemModal = true;
            Log::info('=== Modal aberta com sucesso ===');
            
        } catch (\Exception $e) {
            Log::error('❌ ERRO CRÍTICO ao abrir modal de edição', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            session()->flash('error', 'Erro ao abrir modal: ' . $e->getMessage());
            $this->showEditItemModal = false;
        }
    }
    
    /**
     * Recalculate payroll when subsidies or bonus change (alias para compatibilidade)
     */
    public function recalculatePayroll(): void
    {
        $this->recalculateEditingItem();
    }
    
    /**
     * Save edited batch item (usando PayrollCalculatorHelper)
     */
    public function saveEditedItem(): void
    {
        if (!$this->editingItem || empty($this->calculatedData)) {
            session()->flash('error', 'Dados de cálculo não disponíveis. Recalcule o item.');
            return;
        }
        
        try {
            // Atualizar item com valores calculados pelo helper
            $this->editingItem->update([
                'additional_bonus' => $this->edit_additional_bonus,
                'christmas_subsidy' => $this->edit_christmas_subsidy,
                'vacation_subsidy' => $this->edit_vacation_subsidy,
                'christmas_subsidy_amount' => $this->calculatedData['christmas_subsidy_amount'],
                'vacation_subsidy_amount' => $this->calculatedData['vacation_subsidy_amount'],
                'basic_salary' => $this->calculatedData['basic_salary'],
                'transport_allowance' => $this->calculatedData['transport_allowance'],
                'food_allowance' => $this->calculatedData['food_benefit'],
                'overtime_amount' => $this->calculatedData['total_overtime_amount'],
                'bonus_amount' => $this->calculatedData['bonus_amount'],
                'gross_salary' => $this->calculatedData['gross_salary'],
                'inss_deduction' => $this->calculatedData['inss_3_percent'],
                'irt_deduction' => $this->calculatedData['irt'],
                'advance_deduction' => $this->calculatedData['advance_deduction'],
                'discount_deduction' => $this->calculatedData['total_salary_discounts'],
                'late_deduction' => $this->calculatedData['late_deduction'],
                'absence_deduction' => $this->calculatedData['absence_deduction'],
                'total_deductions' => $this->calculatedData['total_deductions'],
                'net_salary' => $this->calculatedData['net_salary'],
                'present_days' => $this->calculatedData['present_days'],
                'absent_days' => $this->calculatedData['absent_days'],
                'late_days' => $this->calculatedData['late_arrivals'],
                'notes' => $this->edit_notes,
            ]);
            
            // If payroll exists, update it too
            if ($this->editingItem->payroll_id) {
                $payroll = Payroll::find($this->editingItem->payroll_id);
                if ($payroll) {
                    $payroll->update([
                        'gross_salary' => $this->calculatedData['gross_salary'],
                        'net_salary' => $this->calculatedData['net_salary'],
                        'total_deductions' => $this->calculatedData['total_deductions'],
                    ]);
                }
            }
            
            // Atualizar totais do batch
            $this->updateBatchTotals((int) $this->editingItem->payroll_batch_id);
            
            $this->showEditItemModal = false;
            $this->editingItem = null;
            $this->calculatedData = [];
            
            session()->flash('success', 'Item atualizado com sucesso!');
            
            // Refresh view
            if ($this->currentBatch) {
                $this->viewBatch((int) $this->currentBatch->id);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao salvar item editado', [
                'item_id' => $this->editingItem->id ?? null,
                'error' => $e->getMessage(),
            ]);
            
            session()->flash('error', 'Erro ao salvar: ' . $e->getMessage());
        }
    }
    
    /**
     * Close edit item modal
     */
    public function closeEditItemModal(): void
    {
        $this->showEditItemModal = false;
        $this->editingItem = null;
        $this->edit_gross_salary = 0;
        $this->edit_net_salary = 0;
        $this->edit_total_deductions = 0;
        $this->edit_additional_bonus = 0;
        $this->edit_overtime_amount = 0;
        $this->edit_advance_deduction = 0;
        $this->edit_christmas_subsidy = false;
        $this->edit_vacation_subsidy = false;
        $this->edit_notes = '';
        $this->calculatedData = [];
        
        // Clear related data
        $this->overtimeRecords = [];
        $this->salaryAdvances = [];
        $this->salaryDiscounts = [];
    }
    
    /**
     * Update batch totals after editing items
     */
    private function updateBatchTotals(int $batchId): void
    {
        $batch = PayrollBatchModel::findOrFail($batchId);
        
        $totals = PayrollBatchItem::where('payroll_batch_id', $batchId)
            ->selectRaw('
                SUM(gross_salary) as total_gross,
                SUM(net_salary) as total_net,
                SUM(total_deductions) as total_deductions
            ')
            ->first();
        
        $batch->update([
            'total_gross_amount' => $totals->total_gross ?? 0,
            'total_net_amount' => $totals->total_net ?? 0,
            'total_deductions' => $totals->total_deductions ?? 0,
        ]);
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

    /**
     * Calcular item do batch usando PayrollCalculatorHelper
     */
    public function calculateBatchItemWithHelper(PayrollBatchItem $item): array
    {
        try {
            $employee = $item->employee;
            $batch = $item->payrollBatch;
            $period = $batch->payrollPeriod;
            
            $startDate = Carbon::parse($period->start_date);
            $endDate = Carbon::parse($period->end_date);
            
            // Criar calculator
            $calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);
            
            // Carregar todos os dados
            $calculator->loadAllEmployeeData();
            
            // Configurar subsídios do item
            $calculator->setChristmasSubsidy($item->christmas_subsidy ?? false);
            $calculator->setVacationSubsidy($item->vacation_subsidy ?? false);
            $calculator->setAdditionalBonus($item->additional_bonus ?? 0);
            
            // Calcular
            $results = $calculator->calculate();
            
            // Atualizar item com resultados
            $item->update([
                'basic_salary' => $results['basic_salary'],
                'transport_allowance' => $results['transport_allowance'],
                'food_allowance' => $results['food_benefit'],
                'overtime_amount' => $results['total_overtime_amount'],
                'bonus_amount' => $results['bonus_amount'],
                'christmas_subsidy_amount' => $results['christmas_subsidy_amount'],
                'vacation_subsidy_amount' => $results['vacation_subsidy_amount'],
                'gross_salary' => $results['gross_salary'],
                'inss_deduction' => $results['inss_3_percent'],
                'irt_deduction' => $results['irt'],
                'advance_deduction' => $results['advance_deduction'],
                'discount_deduction' => $results['total_salary_discounts'],
                'late_deduction' => $results['late_deduction'],
                'absence_deduction' => $results['absence_deduction'],
                'total_deductions' => $results['total_deductions'],
                'net_salary' => $results['net_salary'],
                'present_days' => $results['present_days'],
                'absent_days' => $results['absent_days'],
                'late_days' => $results['late_arrivals'],
                'total_working_days' => $results['total_working_days'],
                'status' => 'calculated',
            ]);
            
            Log::info('Item do batch calculado com helper', [
                'item_id' => $item->id,
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'net_salary' => $results['net_salary'],
            ]);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('Erro ao calcular item do batch', [
                'item_id' => $item->id,
                'employee_id' => $item->employee_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $item->update([
                'status' => 'error',
                'notes' => 'Erro: ' . $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Recalcular item em edição usando helper
     */
    public function recalculateEditingItem(): void
    {
        if (!$this->editingItem) {
            Log::warning('⚠️ recalculateEditingItem: editingItem está vazio');
            return;
        }
        
        try {
            Log::info('🔄 Iniciando recalculateEditingItem', [
                'item_id' => $this->editingItem->id,
                'employee_id' => $this->editingItem->employee_id,
            ]);
            
            $employee = $this->editingItem->employee;
            $period = $this->editingItem->payrollBatch->payrollPeriod;
            
            Log::info('📅 Dados do período', [
                'period_name' => $period->name,
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
            ]);
            
            $startDate = Carbon::parse($period->start_date);
            $endDate = Carbon::parse($period->end_date);
            
            // Criar calculator
            Log::info('🧮 Criando PayrollCalculatorHelper...');
            $calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);
            
            // Carregar dados
            $calculator->loadAllEmployeeData();
            
            // Configurar subsídios e valores editáveis
            $calculator->setChristmasSubsidy($this->edit_christmas_subsidy);
            $calculator->setVacationSubsidy($this->edit_vacation_subsidy);
            $calculator->setAdditionalBonus($this->edit_additional_bonus);
            $calculator->setOvertimeAmount($this->edit_overtime_amount);
            $calculator->setAdvanceDeduction($this->edit_advance_deduction);
            
            // Configurar food in kind (apenas para exibição - food SEMPRE é deduzido)
            $isFoodInKind = (bool)($employee->is_food_in_kind ?? false);
            $calculator->setFoodInKind($isFoodInKind);
            
            // Calcular
            Log::info('💰 Executando cálculo...');
            $this->calculatedData = $calculator->calculate();
            
            Log::info('✅ Cálculo concluído', [
                'calculatedData_keys' => !empty($this->calculatedData) ? array_keys($this->calculatedData) : 'VAZIO',
                'gross_salary' => $this->calculatedData['gross_salary'] ?? 'N/A',
                'net_salary' => $this->calculatedData['net_salary'] ?? 'N/A',
            ]);
            
            // Atualizar propriedades de exibição
            $this->edit_gross_salary = $this->calculatedData['gross_salary'] ?? 0;
            $this->edit_net_salary = $this->calculatedData['net_salary'] ?? 0;
            $this->edit_total_deductions = $this->calculatedData['total_deductions'] ?? 0;
            
            Log::info('Item recalculado em edição - DETALHADO', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'period' => $period->name,
                'basic_salary' => $this->calculatedData['basic_salary'] ?? 'N/A',
                'absent_days' => $this->calculatedData['absent_days'] ?? 'N/A',
                'present_days' => $this->calculatedData['present_days'] ?? 'N/A',
                'absence_deduction' => $this->calculatedData['absence_deduction'] ?? 'N/A',
                'late_deduction' => $this->calculatedData['late_deduction'] ?? 'N/A',
                'inss_3_percent' => $this->calculatedData['inss_3_percent'] ?? 'N/A',
                'irt' => $this->calculatedData['irt'] ?? 'N/A',
                'total_deductions' => $this->calculatedData['total_deductions'] ?? 'N/A',
                'gross_salary' => $this->calculatedData['gross_salary'] ?? 'N/A',
                'net_salary' => $this->calculatedData['net_salary'] ?? 'N/A',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao recalcular item em edição', [
                'item_id' => $this->editingItem->id ?? null,
                'error' => $e->getMessage(),
            ]);
            
            session()->flash('error', 'Erro ao calcular: ' . $e->getMessage());
        }
    }

    /**
     * Recalcular quando bônus adicional muda
     */
    public function updatedEditAdditionalBonus(): void
    {
        $this->recalculateEditingItem();
    }

    /**
     * Recalcular quando subsídio de Natal muda
     */
    public function updatedEditChristmasSubsidy(): void
    {
        $this->recalculateEditingItem();
    }

    /**
     * Recalcular quando subsídio de férias muda
     */
    public function updatedEditVacationSubsidy(): void
    {
        $this->recalculateEditingItem();
    }

    /**
     * Processar todos os itens do batch usando helper
     */
    public function processBatchWithHelper(int $batchId): void
    {
        try {
            $batch = PayrollBatchModel::with(['batchItems.employee', 'payrollPeriod'])->findOrFail($batchId);
            
            $totalProcessed = 0;
            $totalErrors = 0;
            
            foreach ($batch->batchItems as $item) {
                try {
                    $this->calculateBatchItemWithHelper($item);
                    $totalProcessed++;
                } catch (\Exception $e) {
                    $totalErrors++;
                }
            }
            
            // Atualizar status do batch
            $batch->update([
                'status' => $totalErrors > 0 ? 'partially_processed' : 'processed',
                'processed_at' => now(),
            ]);
            
            // Atualizar totais
            $this->updateBatchTotals($batchId);
            
            session()->flash('success', "Batch processado: {$totalProcessed} itens calculados, {$totalErrors} erros");
            
            Log::info('Batch processado com helper', [
                'batch_id' => $batchId,
                'total_items' => $batch->batchItems->count(),
                'processed' => $totalProcessed,
                'errors' => $totalErrors,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);
            
            session()->flash('error', 'Erro ao processar batch: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se funcionário está de férias no período
     */
    public function isEmployeeOnLeave($employeeId, $periodId): bool
    {
        $period = PayrollPeriod::find($periodId);
        if (!$period) {
            return false;
        }

        // Verificar attendance com status 'leave'
        $hasLeave = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->where('status', 'leave')
            ->exists();

        return $hasLeave;
    }

    /**
     * Gerar relatório de resumo do batch
     */
    public function downloadBatchReport($batchId)
    {
        try {
            $batch = PayrollBatchModel::findOrFail($batchId);
            
            $reportService = new PayrollBatchReportService();
            return $reportService->generateBatchReport($batch);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório do batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);
            
            session()->flash('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
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

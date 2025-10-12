<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\PayrollBatch;
use App\Models\HR\Payroll;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Department;
use App\Services\PayrollBatchReportService;
use App\Services\IndividualPayrollReportService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class PayrollReports extends Component
{
    use WithPagination;
    
    public string $search = '';
    public string $selectedPeriod = '';
    public string $selectedDepartment = '';
    public string $selectedStatus = 'completed'; // Apenas completed por padrão
    public string $reportType = 'batch'; // 'batch' ou 'individual'
    
    protected $queryString = [
        'search' => ['except' => ''],
        'selectedPeriod' => ['except' => ''],
        'selectedDepartment' => ['except' => ''],
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedPeriod()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedDepartment()
    {
        $this->resetPage();
    }
    
    public function updatingReportType()
    {
        $this->resetPage();
    }
    
    public function clearFilters()
    {
        $this->search = '';
        $this->selectedPeriod = '';
        $this->selectedDepartment = '';
        $this->selectedStatus = 'completed';
        $this->reportType = 'batch';
        $this->resetPage();
    }
    
    /**
     * Gerar relatório de um batch
     */
    public function generateBatchReport($batchId)
    {
        try {
            $batch = PayrollBatch::findOrFail($batchId);
            
            $reportService = new PayrollBatchReportService();
            return $reportService->generateBatchReport($batch);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);
            
            session()->flash('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }
    
    /**
     * Gerar recibo individual
     */
    public function generateIndividualReceipt($payrollId)
    {
        try {
            return redirect()->route('payroll.receipt.view.by-id', ['payrollId' => $payrollId]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar recibo individual', [
                'payroll_id' => $payrollId,
                'error' => $e->getMessage(),
            ]);
            
            session()->flash('error', 'Erro ao gerar recibo: ' . $e->getMessage());
        }
    }
    
    /**
     * Obter batches filtrados
     */
    public function getBatchesProperty()
    {
        if ($this->reportType !== 'batch') {
            return collect();
        }
        
        return PayrollBatch::query()
            ->with(['payrollPeriod', 'department', 'creator'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedPeriod, function ($query) {
                $query->where('payroll_period_id', $this->selectedPeriod);
            })
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department_id', $this->selectedDepartment);
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
    
    /**
     * Obter payrolls individuais filtrados
     */
    public function getIndividualPayrollsProperty()
    {
        if ($this->reportType !== 'individual') {
            return collect();
        }
        
        return Payroll::query()
            ->with(['employee.department', 'payrollPeriod'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('id_card', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedPeriod, function ($query) {
                $query->where('payroll_period_id', $this->selectedPeriod);
            })
            ->when($this->selectedDepartment, function ($query) {
                $query->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            })
            ->where('status', 'paid') // Apenas pagos
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
    
    /**
     * Obter períodos para filtro
     */
    public function getPeriodsProperty()
    {
        return PayrollPeriod::orderBy('start_date', 'desc')->get();
    }
    
    /**
     * Obter departamentos para filtro
     */
    public function getDepartmentsProperty()
    {
        return Department::orderBy('name')->get();
    }
    
    public function render()
    {
        return view('livewire.hr.payroll-reports', [
            'batches' => $this->batches,
            'individualPayrolls' => $this->individualPayrolls,
            'periods' => $this->periods,
            'departments' => $this->departments,
        ]);
    }
}

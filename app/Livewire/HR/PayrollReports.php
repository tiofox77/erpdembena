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
    public string $selectedDepartment = '';
    public int $selectedYear = 0;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'selectedDepartment' => ['except' => ''],
        'selectedYear' => ['except' => 0],
    ];
    
    public function mount()
    {
        // Define ano atual como padrão
        $this->selectedYear = (int) date('Y');
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedDepartment()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedYear()
    {
        $this->resetPage();
    }
    
    public function clearFilters()
    {
        $this->search = '';
        $this->selectedDepartment = '';
        $this->selectedYear = (int) date('Y');
        $this->resetPage();
    }
    
    /**
     * Gerar relatório consolidado por período
     */
    public function generatePeriodReport($periodId)
    {
        try {
            Log::info('PayrollReports: generatePeriodReport chamado', ['period_id' => $periodId]);
            
            // Redirecionar para rota que gera o relatório consolidado
            return redirect()->route('hr.payroll-period.report', ['periodId' => $periodId]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de período', [
                'period_id' => $periodId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }
    
    /**
     * Gerar relatório de um batch específico (manter para compatibilidade)
     */
    public function generateBatchReport($batchId)
    {
        try {
            Log::info('PayrollReports: generateBatchReport chamado', ['batch_id' => $batchId]);
            
            // Redirecionar para rota que gera o relatório
            return redirect()->route('hr.payroll-batch.report', ['batchId' => $batchId]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }
    
    /**
     * Gerar relatório HTML de batch para um período
     */
    public function generateBatchReportForPeriod($periodId)
    {
        try {
            Log::info('PayrollReports: generateBatchReportForPeriod chamado', ['period_id' => $periodId]);
            
            // Buscar o primeiro batch do período
            $batch = PayrollBatch::where('payroll_period_id', $periodId)
                ->whereIn('status', ['completed', 'approved', 'paid'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            if (!$batch) {
                session()->flash('error', 'Nenhum batch encontrado para este período.');
                return;
            }
            
            // Redirecionar para rota que gera o relatório HTML do batch
            return redirect()->route('hr.payroll-batch.report', ['batchId' => $batch->id]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório de batch do período', [
                'period_id' => $periodId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
     * Obter períodos com totais consolidados (batch + individual)
     */
    public function getPeriodsWithTotalsProperty()
    {
        $query = PayrollPeriod::query()
            ->orderBy('start_date', 'desc');
        
        // Filtro por ano
        if ($this->selectedYear) {
            $query->whereYear('start_date', $this->selectedYear);
        }
        
        // Filtro por pesquisa (nome do período)
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        $periods = $query->get();
        
        $periodData = [];
        foreach ($periods as $period) {
            // Calcular totais de batches (completed, approved ou paid)
            $batchQuery = PayrollBatch::where('payroll_period_id', $period->id)
                ->whereIn('status', ['completed', 'approved', 'paid']);
            
            if ($this->selectedDepartment) {
                $batchQuery->where('department_id', $this->selectedDepartment);
            }
            
            $batchTotals = $batchQuery->selectRaw('
                SUM(COALESCE(total_gross_amount, 0)) as total_gross,
                SUM(COALESCE(total_net_amount, 0)) as total_net,
                SUM(COALESCE(total_deductions, 0)) as total_deductions,
                COUNT(*) as batch_count,
                SUM(COALESCE(total_employees, 0)) as employee_count
            ')->first();
            
            // Calcular totais de payrolls individuais (paid ou approved)
            $payrollQuery = Payroll::where('payroll_period_id', $period->id)
                ->whereIn('status', ['paid', 'approved']);
            
            if ($this->selectedDepartment) {
                $payrollQuery->whereHas('employee', function($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }
            
            $individualTotals = $payrollQuery->selectRaw('
                SUM(COALESCE(gross_salary, 0)) as total_gross,
                SUM(COALESCE(net_salary, 0)) as total_net,
                SUM(COALESCE(deductions, 0)) as total_deductions,
                COUNT(*) as payroll_count
            ')->first();
            
            $grossTotal = floatval($batchTotals->total_gross ?? 0) + floatval($individualTotals->total_gross ?? 0);
            $netTotal = floatval($batchTotals->total_net ?? 0) + floatval($individualTotals->total_net ?? 0);
            $deductionsTotal = floatval($batchTotals->total_deductions ?? 0) + floatval($individualTotals->total_deductions ?? 0);
            $totalEmployees = intval($batchTotals->employee_count ?? 0) + intval($individualTotals->payroll_count ?? 0);
            
            // Log para debug
            \Log::info('Period Totals', [
                'period' => $period->name,
                'batch_gross' => $batchTotals->total_gross ?? 0,
                'batch_net' => $batchTotals->total_net ?? 0,
                'batch_deductions' => $batchTotals->total_deductions ?? 0,
                'individual_gross' => $individualTotals->total_gross ?? 0,
                'individual_net' => $individualTotals->total_net ?? 0,
                'individual_deductions' => $individualTotals->total_deductions ?? 0,
                'gross_total' => $grossTotal,
                'net_total' => $netTotal,
                'deductions_total' => $deductionsTotal,
            ]);
            
            // Só incluir períodos com pagamentos
            if ($totalEmployees > 0) {
                $periodData[] = [
                    'period' => $period,
                    'gross_total' => $grossTotal,
                    'net_total' => $netTotal,
                    'deductions_total' => $deductionsTotal,
                    'batch_count' => intval($batchTotals->batch_count ?? 0),
                    'individual_count' => intval($individualTotals->payroll_count ?? 0),
                    'total_employees' => $totalEmployees,
                ];
            }
        }
        
        return collect($periodData);
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
    
    /**
     * Obter anos disponíveis para filtro
     */
    public function getAvailableYearsProperty()
    {
        $years = PayrollPeriod::selectRaw('DISTINCT YEAR(start_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
        
        // Se não houver anos, adicionar ano atual
        if (empty($years)) {
            $years[] = (int) date('Y');
        }
        
        return $years;
    }
    
    public function render()
    {
        return view('livewire.hr.payroll-reports', [
            'periodsWithTotals' => $this->periodsWithTotals,
            'departments' => $this->departments,
            'availableYears' => $this->availableYears,
        ]);
    }
}

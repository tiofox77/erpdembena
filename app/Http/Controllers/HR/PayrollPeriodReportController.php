<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\PayrollBatch;
use App\Models\HR\Payroll;
use Illuminate\Support\Facades\Log;

class PayrollPeriodReportController extends Controller
{
    /**
     * Gerar relatório consolidado HTML do período SOMENTE da tabela payrolls
     */
    public function show($periodId)
    {
        try {
            Log::info('PayrollPeriodReportController: Gerando relatório', ['period_id' => $periodId]);
            
            $period = PayrollPeriod::findOrFail($periodId);
            
            // Buscar SOMENTE payrolls da tabela payrolls (approved ou paid)
            $payrolls = Payroll::where('payroll_period_id', $period->id)
                ->whereIn('status', ['paid', 'approved'])
                ->with(['employee.department'])
                ->get();
            
            // Calcular totais
            $totals = [
                'grand_total' => $payrolls->sum('gross_salary'),
                'total_deductions' => $payrolls->sum('deductions'),
                'net_total' => $payrolls->sum('net_salary'),
                'total_employees' => $payrolls->unique('employee_id')->count(),
            ];
            
            // Log para debug
            Log::info('PayrollPeriodReportController: Totais calculados (SOMENTE Payrolls)', [
                'period' => $period->name,
                'gross_total' => $totals['grand_total'],
                'deductions_total' => $totals['total_deductions'],
                'net_total' => $totals['net_total'],
                'employee_count' => $totals['total_employees'],
            ]);
            
            // Dados para a view
            $data = [
                'period' => $period,
                'batchName' => 'Relatório de Folha de Pagamento - ' . $period->name,
                'periodName' => $period->name,
                'batchDate' => $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y'),
                'departmentName' => 'Todos os Departamentos',
                'totalEmployees' => $totals['total_employees'],
                'creatorName' => auth()->user()->name ?? 'Sistema',
                'generatedAt' => now()->format('d/m/Y H:i'),
                'totals' => $totals,
                'payrolls' => $payrolls,
            ];
            
            return view('reports.payroll-period-html', $data);
            
        } catch (\Exception $e) {
            Log::error('PayrollPeriodReportController: Erro ao gerar relatório', [
                'period_id' => $periodId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }
}

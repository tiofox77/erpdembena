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
     * Gerar relatório consolidado HTML do período (batch + individual)
     */
    public function show($periodId)
    {
        try {
            Log::info('PayrollPeriodReportController: Gerando relatório', ['period_id' => $periodId]);
            
            $period = PayrollPeriod::findOrFail($periodId);
            
            // Buscar todos os batches do período
            $batches = PayrollBatch::where('payroll_period_id', $period->id)
                ->whereIn('status', ['completed', 'approved', 'paid'])
                ->with(['department', 'creator'])
                ->get();
            
            // Buscar todos os payrolls individuais do período
            $individualPayrolls = Payroll::where('payroll_period_id', $period->id)
                ->whereIn('status', ['paid', 'approved'])
                ->with(['employee.department'])
                ->get();
            
            // Calcular totais
            $totals = [
                // Totais de batches
                'batch_gross' => $batches->sum('total_gross_amount'),
                'batch_net' => $batches->sum('total_net_amount'),
                'batch_deductions' => $batches->sum('total_deductions'),
                'batch_employees' => $batches->sum('total_employees'),
                'batch_count' => $batches->count(),
                
                // Totais de individuais (deductions já contém o total)
                'individual_gross' => $individualPayrolls->sum('gross_salary'),
                'individual_net' => $individualPayrolls->sum('net_salary'),
                'individual_deductions' => $individualPayrolls->sum('deductions'),
                'individual_count' => $individualPayrolls->count(),
                
                // Totais consolidados
                'grand_total' => 0,
                'total_deductions' => 0,
                'net_total' => 0,
                'total_employees' => 0,
            ];
            
            // Calcular totais consolidados
            $totals['grand_total'] = floatval($totals['batch_gross']) + floatval($totals['individual_gross']);
            $totals['total_deductions'] = floatval($totals['batch_deductions']) + floatval($totals['individual_deductions']);
            $totals['net_total'] = floatval($totals['batch_net']) + floatval($totals['individual_net']);
            $totals['total_employees'] = intval($totals['batch_employees']) + intval($totals['individual_count']);
            
            // Garantir que net_total = gross_total - deductions (recalcular para garantir)
            if ($totals['net_total'] == 0 && $totals['grand_total'] > 0) {
                $totals['net_total'] = $totals['grand_total'] - $totals['total_deductions'];
            }
            
            // Log para debug
            Log::info('PayrollPeriodReportController: Totais calculados', [
                'period' => $period->name,
                'batch_gross' => $totals['batch_gross'],
                'batch_net' => $totals['batch_net'],
                'batch_deductions' => $totals['batch_deductions'],
                'individual_gross' => $totals['individual_gross'],
                'individual_net' => $totals['individual_net'],
                'individual_deductions' => $totals['individual_deductions'],
                'grand_total' => $totals['grand_total'],
                'total_deductions' => $totals['total_deductions'],
                'net_total' => $totals['net_total'],
            ]);
            
            // Dados para a view
            $data = [
                'period' => $period,
                'batchName' => 'Relatório Consolidado - ' . $period->name,
                'periodName' => $period->name,
                'batchDate' => $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y'),
                'departmentName' => 'Todos os Departamentos',
                'totalEmployees' => $totals['total_employees'],
                'creatorName' => auth()->user()->name ?? 'Sistema',
                'generatedAt' => now()->format('d/m/Y H:i'),
                'totals' => $totals,
                'batches' => $batches,
                'individualPayrolls' => $individualPayrolls,
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

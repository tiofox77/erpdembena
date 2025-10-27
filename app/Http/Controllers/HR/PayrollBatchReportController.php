<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollBatch;
use App\Models\HR\PayrollBatchItem;
use App\Models\HR\Payroll;
use App\Models\HR\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayrollBatchReportController extends Controller
{
    /**
     * Gerar relatório HTML consolidado do período (a partir de um batch)
     */
    public function show($batchId)
    {
        try {
            Log::info('PayrollBatchReportController: Gerando relatório consolidado do período', ['batch_id' => $batchId]);
            
            // Buscar batch para identificar o período
            $batch = PayrollBatch::with('payrollPeriod')->findOrFail($batchId);
            $period = $batch->payrollPeriod;
            
            if (!$period) {
                return back()->with('error', 'Período não encontrado para este batch.');
            }
            
            // Buscar TODOS os batches do período
            $batches = PayrollBatch::where('payroll_period_id', $period->id)
                ->whereIn('status', ['completed', 'approved', 'paid'])
                ->with(['batchItems', 'department', 'creator'])
                ->get();
            
            // Buscar TODOS os payrolls individuais do período
            $individualPayrolls = Payroll::where('payroll_period_id', $period->id)
                ->whereIn('status', ['paid', 'approved'])
                ->with(['employee.department'])
                ->get();
            
            // Calcular totais consolidados do período
            $totals = $this->calculatePeriodTotals($batches, $individualPayrolls);
            
            // Preparar dados para a view (formato antigo do batch)
            $data = [
                'batchName' => $period->name,
                'batchDate' => $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y'),
                'periodName' => $period->name,
                'departmentName' => 'Consolidado - Todos os Departamentos',
                'creatorName' => $batch->creator->name ?? auth()->user()->name ?? 'Sistema',
                'totalEmployees' => $totals['total_employees'],
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'totals' => $totals,
            ];
            
            Log::info('PayrollBatchReportController: Totais calculados', $totals);
            
            // Retornar view HTML para impressão
            return view('reports.payroll-batch-html', $data);
            
        } catch (\Exception $e) {
            Log::error('PayrollBatchReportController: Erro ao gerar relatório', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erro ao gerar relatório: ' . $e->getMessage());
        }
    }
    
    /**
     * Calcular totais consolidados do período (batches + individuais)
     */
    protected function calculatePeriodTotals($batches, $individualPayrolls): array
    {
        // Totais dos batches
        $batchTotals = [
            'basic_salary' => 0,
            'transport' => 0,
            'overtime' => 0,
            'vacation_pay' => 0,
            'food_allow' => 0,
            'christmas_offer' => 0,
            'bonus' => 0,
            'inss_3_percent' => 0,
            'irt' => 0,
            'staff_advance' => 0,
            'absent_deduction' => 0,
            'other_deduction' => 0,
            'employees' => 0,
        ];
        
        foreach ($batches as $batch) {
            $items = $batch->batchItems;
            
            $batchTotals['basic_salary'] += $items->sum('basic_salary');
            $batchTotals['transport'] += $items->sum('transport_allowance');
            $batchTotals['overtime'] += $items->sum('overtime_amount');
            $batchTotals['vacation_pay'] += $items->sum('vacation_subsidy_amount');
            $batchTotals['food_allow'] += $items->sum('food_allowance');
            $batchTotals['christmas_offer'] += $items->sum('christmas_bonus_amount');
            $batchTotals['bonus'] += $items->sum('profile_bonus_amount') + $items->sum('production_bonus_amount');
            $batchTotals['inss_3_percent'] += $items->sum('inss_3_percent');
            $batchTotals['irt'] += $items->sum('deductions_irt');
            $batchTotals['staff_advance'] += $items->sum('advance_deduction');
            $batchTotals['absent_deduction'] += $items->sum('absence_deduction_amount');
            $batchTotals['other_deduction'] += $items->sum('discount_deduction') + $items->sum('late_deduction');
            $batchTotals['employees'] += $items->count();
        }
        
        // Totais dos payrolls individuais
        $individualTotals = [
            'basic_salary' => $individualPayrolls->sum('basic_salary'),
            'transport' => $individualPayrolls->sum('allowances'),
            'overtime' => $individualPayrolls->sum('overtime_amount'),
            'vacation_pay' => 0, // não há campo específico
            'food_allow' => 0, // não há campo específico
            'christmas_offer' => 0, // não há campo específico
            'bonus' => $individualPayrolls->sum('bonuses'),
            'inss_3_percent' => $individualPayrolls->sum('inss_3_percent'),
            'irt' => $individualPayrolls->sum('deductions_irt'),
            'staff_advance' => 0, // não há campo específico
            'absent_deduction' => $individualPayrolls->sum('absence_deduction_amount'),
            'other_deduction' => $individualPayrolls->sum('deductions'),
            'employees' => $individualPayrolls->count(),
        ];
        
        // Consolidar totais
        $grandTotal = $batchTotals['basic_salary'] + $individualTotals['basic_salary'] +
                      $batchTotals['transport'] + $individualTotals['transport'] +
                      $batchTotals['overtime'] + $individualTotals['overtime'] +
                      $batchTotals['vacation_pay'] + $individualTotals['vacation_pay'] +
                      $batchTotals['food_allow'] + $individualTotals['food_allow'] +
                      $batchTotals['christmas_offer'] + $individualTotals['christmas_offer'] +
                      $batchTotals['bonus'] + $individualTotals['bonus'];
        
        $totalDeductions = $batchTotals['inss_3_percent'] + $individualTotals['inss_3_percent'] +
                          $batchTotals['irt'] + $individualTotals['irt'] +
                          $batchTotals['staff_advance'] + $individualTotals['staff_advance'] +
                          $batchTotals['absent_deduction'] + $individualTotals['absent_deduction'] +
                          $batchTotals['other_deduction'] + $individualTotals['other_deduction'];
        
        $netTotal = $grandTotal - $totalDeductions;
        
        return [
            'grand_total' => $grandTotal,
            'net_before_deductions' => $grandTotal, // Total antes das deduções (mesmo que grand_total)
            'net_total' => $netTotal,
            'total_deductions' => $totalDeductions,
            'total' => $netTotal,
            'total_employees' => $batchTotals['employees'] + $individualTotals['employees'],
            
            // Earnings (estrutura esperada pela view)
            'earnings' => [
                'basic_salary' => $batchTotals['basic_salary'] + $individualTotals['basic_salary'],
                'transport' => $batchTotals['transport'] + $individualTotals['transport'],
                'overtime' => $batchTotals['overtime'] + $individualTotals['overtime'],
                'vacation_pay' => $batchTotals['vacation_pay'] + $individualTotals['vacation_pay'],
                'food_allow' => $batchTotals['food_allow'] + $individualTotals['food_allow'],
                'christmas_offer' => $batchTotals['christmas_offer'] + $individualTotals['christmas_offer'],
                'bonus' => $batchTotals['bonus'] + $individualTotals['bonus'],
            ],
            
            // Deductions (estrutura esperada pela view)
            'deductions' => [
                'inss_3_percent' => $batchTotals['inss_3_percent'] + $individualTotals['inss_3_percent'],
                'irt' => $batchTotals['irt'] + $individualTotals['irt'],
                'staff_advance' => $batchTotals['staff_advance'] + $individualTotals['staff_advance'],
                'absent' => $batchTotals['absent_deduction'] + $individualTotals['absent_deduction'],
                'other_deduction' => $batchTotals['other_deduction'] + $individualTotals['other_deduction'],
                'food_allow_deduction' => 0, // não calculado
                'union_fund' => 0, // não calculado
                'union_deduction' => 0, // não calculado
            ],
        ];
    }
}

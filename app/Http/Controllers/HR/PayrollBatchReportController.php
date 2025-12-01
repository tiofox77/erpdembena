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
     * Gerar relatório HTML de um batch específico USANDO SOMENTE TABELA PAYROLLS
     */
    public function show($batchId)
    {
        try {
            Log::info('PayrollBatchReportController: Gerando relatório do batch específico', ['batch_id' => $batchId]);
            
            // Buscar apenas o batch específico solicitado
            $batch = PayrollBatch::with(['payrollPeriod', 'department', 'creator'])->findOrFail($batchId);
            $period = $batch->payrollPeriod;
            
            if (!$period) {
                return back()->with('error', 'Período não encontrado para este batch.');
            }
            
            // Buscar TODOS os payrolls da tabela payrolls que pertencem a este batch (sem filtro de status)
            $payrolls = Payroll::where('payroll_batch_id', $batch->id)
                ->with(['employee.department'])
                ->get();
            
            // Calcular totais DIRETAMENTE dos campos da BD (não recalcular)
            $totals = $this->calculateBatchTotalsFromDB($payrolls);
            
            // Preparar dados para a view
            $data = [
                'batchName' => $batch->name ?? $period->name,
                'batchDate' => $batch->batch_date ? $batch->batch_date->format('d/m/Y') : ($period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y')),
                'periodName' => $period->name,
                'departmentName' => $batch->department->name ?? 'Todos os Departamentos',
                'creatorName' => $batch->creator->name ?? auth()->user()->name ?? 'Sistema',
                'totalEmployees' => $totals['total_employees'],
                'generatedAt' => now()->format('d/m/Y H:i:s'),
                'totals' => $totals,
                'payrolls' => $payrolls, // Passar payrolls para a view
            ];
            
            Log::info('PayrollBatchReportController: Totais calculados do batch (SOMENTE Payrolls)', [
                'batch_id' => $batch->id,
                'batch_name' => $batch->name,
                'total_payrolls' => $payrolls->count(),
                'grand_total' => $totals['grand_total'],
                'total_deductions' => $totals['total_deductions'],
                'net_total' => $totals['net_total'],
                'earnings' => $totals['earnings'],
                'deductions' => $totals['deductions'],
            ]);
            
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
     * Calcular totais do batch DIRETAMENTE DA BD (sem recalcular)
     * Usa gross_salary, net_salary e deductions já salvos na tabela payrolls
     */
    protected function calculateBatchTotalsFromDB($payrolls): array
    {
        // TOTAIS PRINCIPAIS - Diretamente da BD (já calculados e salvos)
        $grandTotal = $payrolls->sum('gross_salary');
        $netTotal = $payrolls->sum('net_salary');
        $totalDeductions = $payrolls->sum('deductions');
        $totalEmployees = $payrolls->unique('employee_id')->count();
        
        // EARNINGS - Somar campos individuais da BD
        $earnings = [
            'basic_salary' => $payrolls->sum('basic_salary'),
            'transport' => $payrolls->sum('transport_allowance'),
            'overtime' => $payrolls->sum('overtime_amount'),
            'vacation_pay' => $payrolls->sum('vacation_subsidy_amount'),
            'food_allow' => $payrolls->sum('food_allowance'),
            'christmas_offer' => $payrolls->sum('christmas_subsidy_amount'),
            'bonus' => $payrolls->sum('additional_bonus'),
            'family_allowance' => $payrolls->sum('family_allowance'),
            'position_subsidy' => $payrolls->sum('position_subsidy'),
            'performance_subsidy' => $payrolls->sum('performance_subsidy'),
        ];
        
        // DEDUCTIONS - Somar campos individuais da BD
        $deductions = [
            'inss_3_percent' => $payrolls->sum('inss_3_percent'),
            'inss_8_percent' => $payrolls->sum('inss_8_percent'),
            'irt' => $payrolls->sum('tax'),
            'staff_advance' => $payrolls->sum('advance_deduction'),
            'absent' => $payrolls->sum('absence_deduction'),
            'other_deduction' => $payrolls->sum('total_salary_discounts') + $payrolls->sum('late_deduction'),
            'food_allow_deduction' => 0,
            'union_fund' => 0,
            'union_deduction' => 0,
        ];
        
        Log::info('calculateBatchTotalsFromDB - Valores da BD', [
            'total_payrolls' => $payrolls->count(),
            'grand_total_from_db' => $grandTotal,
            'net_total_from_db' => $netTotal,
            'deductions_from_db' => $totalDeductions,
        ]);
        
        return [
            'grand_total' => $grandTotal,
            'net_before_deductions' => $grandTotal,
            'net_total' => $netTotal,
            'total_deductions' => $totalDeductions,
            'total' => $netTotal,
            'total_employees' => $totalEmployees,
            'earnings' => $earnings,
            'deductions' => $deductions,
        ];
    }
    
    /**
     * MÉTODO ANTIGO - Calcular totais recalculando (mantido para compatibilidade)
     */
    protected function calculateBatchTotals($payrolls): array
    {
        // Usar o novo método que busca da BD
        return $this->calculateBatchTotalsFromDB($payrolls);
    }
    
    /**
     * MÉTODO ANTIGO - Calcular totais do(s) batch(es) especificado(s)
     * Mantido para compatibilidade se necessário
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
            'family_allowance' => 0,
            'position_subsidy' => 0,
            'performance_subsidy' => 0,
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
            $batchTotals['christmas_offer'] += $items->sum('christmas_subsidy_amount');
            $batchTotals['bonus'] += $items->sum('additional_bonus');
            $batchTotals['family_allowance'] += $items->sum('family_allowance');
            $batchTotals['position_subsidy'] += $items->sum('position_subsidy');
            $batchTotals['performance_subsidy'] += $items->sum('performance_subsidy');
            $batchTotals['inss_3_percent'] += $items->sum('inss_deduction');
            $batchTotals['irt'] += $items->sum('irt_deduction');
            $batchTotals['staff_advance'] += $items->sum('advance_deduction');
            $batchTotals['absent_deduction'] += $items->sum('absence_deduction');
            $batchTotals['other_deduction'] += $items->sum('discount_deduction') + $items->sum('late_deduction');
            $batchTotals['employees'] += $items->count();
        }
        
        // Totais dos payrolls individuais
        $individualTotals = [
            'basic_salary' => $individualPayrolls->sum('basic_salary'),
            'transport' => $individualPayrolls->sum('transport_allowance'),
            'overtime' => $individualPayrolls->sum('overtime_amount'),
            'vacation_pay' => $individualPayrolls->sum('vacation_subsidy_amount'),
            'food_allow' => $individualPayrolls->sum('food_allowance'),
            'christmas_offer' => $individualPayrolls->sum('christmas_subsidy_amount'),
            'bonus' => $individualPayrolls->sum('additional_bonus'),
            'family_allowance' => $individualPayrolls->sum('family_allowance'),
            'position_subsidy' => $individualPayrolls->sum('position_subsidy'),
            'performance_subsidy' => $individualPayrolls->sum('performance_subsidy'),
            'inss_3_percent' => $individualPayrolls->sum('inss_3_percent'),
            'irt' => $individualPayrolls->sum('tax'),
            'staff_advance' => $individualPayrolls->sum('advance_deduction'),
            'absent_deduction' => $individualPayrolls->sum('absence_deduction'),
            'other_deduction' => $individualPayrolls->sum('total_salary_discounts') + $individualPayrolls->sum('late_deduction'),
            'employees' => $individualPayrolls->count(),
        ];
        
        // Consolidar totais
        $grandTotal = $batchTotals['basic_salary'] + $individualTotals['basic_salary'] +
                      $batchTotals['transport'] + $individualTotals['transport'] +
                      $batchTotals['overtime'] + $individualTotals['overtime'] +
                      $batchTotals['vacation_pay'] + $individualTotals['vacation_pay'] +
                      $batchTotals['food_allow'] + $individualTotals['food_allow'] +
                      $batchTotals['christmas_offer'] + $individualTotals['christmas_offer'] +
                      $batchTotals['bonus'] + $individualTotals['bonus'] +
                      $batchTotals['family_allowance'] + $individualTotals['family_allowance'] +
                      $batchTotals['position_subsidy'] + $individualTotals['position_subsidy'] +
                      $batchTotals['performance_subsidy'] + $individualTotals['performance_subsidy'];
        
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
                'family_allowance' => $batchTotals['family_allowance'] + $individualTotals['family_allowance'],
                'position_subsidy' => $batchTotals['position_subsidy'] + $individualTotals['position_subsidy'],
                'performance_subsidy' => $batchTotals['performance_subsidy'] + $individualTotals['performance_subsidy'],
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

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HR\PayrollBatch;

class PayrollBatchReportService
{
    /**
     * Gerar relatório HTML do batch de payroll (preview para impressão)
     */
    public function generateBatchReport(PayrollBatch $batch)
    {
        $batch->load(['batchItems.employee', 'payrollPeriod', 'department', 'creator']);
        
        // Calcular totais agregados
        $totals = $this->calculateBatchTotals($batch);
        
        // Preparar dados
        $data = [
            'batchName' => $batch->name ?? 'N/A',
            'batchDate' => $batch->formatted_batch_date ?? '',
            'periodName' => $batch->payrollPeriod->name ?? 'N/A',
            'departmentName' => $batch->department->name ?? 'Todos',
            'creatorName' => $batch->creator->name ?? 'N/A',
            'totalEmployees' => $batch->total_employees ?? 0,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
            'totals' => $totals,
        ];
        
        // Retornar view HTML para impressão
        return view('reports.payroll-batch-html', $data);
    }
    

    /**
     * Calcular totais agregados do batch
     */
    protected function calculateBatchTotals(PayrollBatch $batch): array
    {
        $items = $batch->batchItems;
        
        // Earnings
        $basicSalary = $items->sum('basic_salary');
        $transport = $items->sum('transport_allowance');
        $overtime = $items->sum('overtime_amount');
        $vacationPay = $items->sum('vacation_subsidy_amount');
        $foodAllow = $items->sum('food_allowance');
        $christmasOffer = $items->sum('christmas_subsidy_amount');
        $bonus = $items->sum('bonus_amount');
        
        $grossTotal = $basicSalary + $transport + $overtime + $vacationPay + $foodAllow + $christmasOffer + $bonus;
        
        // Deductions
        $inss = $items->sum('inss_deduction');
        $irt = $items->sum('irt_deduction');
        $staffAdvance = $items->sum('advance_deduction');
        $absent = $items->sum('absence_deduction');
        $unionFund = 0; // Se tiver campo específico
        $unionDeduction = 0; // Se tiver campo específico
        $otherDeduction = $items->sum('discount_deduction');
        $foodDeduction = $items->where('is_food_in_kind', true)->sum('food_allowance'); // Food deduzido
        
        $totalDeductions = $inss + $irt + $staffAdvance + $absent + $unionFund + $unionDeduction + $otherDeduction + $foodDeduction;
        
        $netTotal = $items->sum('net_salary');
        
        return [
            // Grand Total
            'grand_total' => $grossTotal,
            
            // Earnings Breakdown
            'earnings' => [
                'basic_salary' => $basicSalary,
                'transport' => $transport,
                'overtime' => $overtime,
                'vacation_pay' => $vacationPay,
                'food_allow' => $foodAllow,
                'christmas_offer' => $christmasOffer,
                'bonus' => $bonus,
            ],
            'net_before_deductions' => $grossTotal,
            
            // Deductions Breakdown
            'deductions' => [
                'inss_3_percent' => $inss,
                'irt' => $irt,
                'staff_advance' => $staffAdvance,
                'absent' => $absent,
                'union_fund' => $unionFund,
                'union_deduction' => $unionDeduction,
                'other_deduction' => $otherDeduction,
                'food_allow_deduction' => $foodDeduction,
            ],
            'total_deductions' => $totalDeductions,
            
            // Final Total
            'net_total' => $netTotal,
            
            // Statistics
            'employee_count' => $items->count(),
        ];
    }
}

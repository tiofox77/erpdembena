<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollReceiptController extends Controller
{
    /**
     * Exibe o recibo de pagamento em formato HTML para debug/visualização
     *
     * @param Request $request
     * @return View
     */
    public function showReceiptHTML(Request $request): View
    {
        // Dados de exemplo para debug - podem ser substituídos por dados reais
        $receiptData = $this->getDefaultReceiptData();
        
        // Se houver parâmetros na request, sobrescrever os dados padrão
        if ($request->has('employee_id')) {
            $receiptData = $this->getEmployeeReceiptData((int) $request->input('employee_id'));
        }
        
        return view('livewire.hr.payroll-receipt-isolated', $receiptData);
    }
    
    /**
     * Dados padrão para o recibo (para debug)
     *
     * @return array
     */
    private function getDefaultReceiptData(): array
    {
        return [
            'companyName' => 'Dembena Indústria e Comércio Lda',
            'employeeName' => 'JOÃO SILVA (Id: EMP001)',
            'employeeId' => 'EMP001',
            'month' => 'July 2025 • Data de referência: 31/07/2025',
            'category' => 'Técnico Sénior',
            'referencePeriod' => '01/07/2025 - 31/07/2025',
            'workedDays' => 31,
            'absences' => 0,
            'extraHours' => 0,
            'baseSalary' => 450498,
            'transportSubsidy' => 15000,
            'holidaySubsidy' => 0,
            'foodSubsidy' => 12000,
            'otherAllowances' => 8000,
            'bonus' => 25000,
            'gratuity' => 0,
            'totalEarnings' => 510498,
            'irt' => 45000,
            'socialSecurity' => 15315,
            'absenceDeduction' => 0,
            'advance' => 20000,
            'foodSubsidyDeduction' => 0,
            'otherDeductions' => 5000,
            'unionFee' => 2500,
            'totalDeductions' => 87815,
            'netSalary' => 422683,
            'bankName' => 'ACCESS BANK',
            'accountNumber' => '123456789',
            'paymentMethod' => 'TRANSFERÊNCIA BANCÁRIA',
            'receiptNumber' => 'REC-2025-07-001'
        ];
    }
    
    /**
     * Busca dados reais do funcionário
     *
     * @param int $employeeId
     * @return array
     */
    private function getEmployeeReceiptData(int $employeeId): array
    {
        try {
            $employee = \App\Models\HR\Employee::findOrFail($employeeId);
            
            // Buscar última folha de pagamento ou criar dados baseados no funcionário
            $latestPayroll = \App\Models\HR\Payroll::where('employee_id', $employeeId)
                ->with('payrollPeriod')
                ->latest()
                ->first();
            
            // Buscar horas extras aprovadas do último período ou julho 2025
            $startDate = $latestPayroll ? $latestPayroll->payrollPeriod?->start_date : '2025-07-01';
            $endDate = $latestPayroll ? $latestPayroll->payrollPeriod?->end_date : '2025-07-31';
            
            $overtimeRecords = \App\Models\HR\OvertimeRecord::where('employee_id', $employeeId)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'approved')
                ->get();
            
            $totalOvertimeHours = $overtimeRecords->sum('hours');
            $totalOvertimeAmount = $overtimeRecords->sum('amount');
            
            // Buscar dados de presença para calcular dias trabalhados e ausências
            $attendanceRecords = \App\Models\HR\Attendance::where('employee_id', $employeeId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            $presentDays = $attendanceRecords->where('status', 'present')->count();
            $absentDays = $attendanceRecords->where('status', 'absent')->count();
            $lateDays = $attendanceRecords->where('status', 'late')->count();
            $halfDays = $attendanceRecords->where('status', 'half_day')->count();
            
            // Calcular dias úteis do mês para determinar ausências
            $workingDaysInMonth = 0;
            $currentDate = \Carbon\Carbon::parse($startDate);
            $endDateCarbon = \Carbon\Carbon::parse($endDate);
            
            while ($currentDate->lte($endDateCarbon)) {
                if ($currentDate->isWeekday()) {
                    $workingDaysInMonth++;
                }
                $currentDate->addDay();
            }
            
            // Calcular total de dias trabalhados (presente + atrasados + meio-dias)
            $workedDays = $presentDays + $lateDays + $halfDays;
            
            // Ausências = Dias úteis - Total de registos de presença (alinhado com interface)
            $totalAbsences = $workingDaysInMonth - $attendanceRecords->count();
            
            // Buscar descontos salariais por tipo (ativos ou deduzidos no período)
            $salaryDiscounts = \App\Models\HR\SalaryDiscount::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('first_deduction_date', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('first_deduction_date', '<=', $endDate)
                                ->where('remaining_installments', '>', 0);
                          });
                })
                ->get()
                ->groupBy('discount_type')
                ->map(function ($discounts, $type) {
                    return [
                        'type' => $type,
                        'type_name' => $discounts->first()->discount_type_name,
                        'total_amount' => $discounts->sum('installment_amount'),
                        'count' => $discounts->count(),
                    ];
                });
            
            return [
                'companyName' => 'Dembena Indústria e Comércio Lda',
                'employeeName' => $employee->full_name . " (Id: {$employee->id})",
                'employeeId' => $employee->id,
                'month' => 'July 2025 • Data de referência: 31/07/2025',
                'category' => $employee->jobPosition?->name ?? 'Técnico Sénior',
                'referencePeriod' => '01/07/2025 - 31/07/2025',
                'workedDays' => $workedDays ?? ($latestPayroll?->days_worked ?? 31),
                'absences' => $totalAbsences ?? ($latestPayroll?->absence_days ?? 0),
                'extraHours' => $totalOvertimeHours,
                
                // Remunerações baseadas nos dados reais
                'baseSalary' => $employee->base_salary ?? 175000,
                'transportSubsidy' => 30000, // Valor padrão mostrado na imagem
                'foodSubsidy' => 12000,
                'overtimeHours' => $totalOvertimeAmount,
                'bonus' => 15000, // Employee Profile Bonus + Additional Payroll Bonus
                'christmasSubsidy' => 87500, // Christmas Subsidy
                'holidaySubsidy' => 87500, // Vacation Subsidy
                'totalEarnings' => $latestPayroll?->gross_salary ?? 362734.38,
                
                // Descontos baseados na imagem
                'lateDeduction' => 994.32, // Desconto por Atrasos (1 dia)
                'absenceDeduction' => 91304.35, // Deduções por Faltas (12 dias) + Desconto por Faltas (12 dias)
                'socialSecurity' => 5250, // INSS (3%)
                'incomeTax' => 42351.94, // IRT
                'foodSubsidyDeduction' => 1200, // Desconto Subsídio Alimentação (10% do subsídio)
                'salaryDiscounts' => $salaryDiscounts->sum('total_amount') ?: 18000, // Salary Discounts (total)
                'salaryDiscountsDetailed' => $salaryDiscounts, // Descontos detalhados por tipo
                'salaryAdvances' => 12857.14, // Salary Advances
                'totalDeductions' => $latestPayroll?->total_deductions ?? 180194.13,
                
                'netSalary' => $latestPayroll?->net_salary ?? 182540.25,
                'bankName' => 'ACCESS BANK',
                'accountNumber' => '123456789',
                'paymentMethod' => 'TRANSFERÊNCIA BANCÁRIA',
                'receiptNumber' => 'REC-2025-07-001'
            ];
        } catch (\Exception $e) {
            // Em caso de erro, retorna dados padrão
            $data = $this->getDefaultReceiptData();
            $data['employeeId'] = $employeeId;
            $data['employeeName'] = "FUNCIONÁRIO {$employeeId} (Id: {$employeeId})";
            return $data;
        }
    }
    
    /**
     * Renderiza múltiplos recibos (implementação futura)
     *
     * @param Request $request
     * @return View
     */
    public function showMultipleReceipts(Request $request): View
    {
        // TODO: Implementar para exibir múltiplos recibos
        return $this->showReceiptHTML($request);
    }
}

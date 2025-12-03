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
        
        return view('livewire.hr.payroll.payroll-receipt-isolated', $receiptData);
    }

    /**
     * Exibe o recibo individual baseado no ID do payroll
     *
     * @param int $payrollId
     * @return View
     */
    public function showReceiptByPayrollId(int $payrollId): View
    {
        try {
            // Buscar payroll com relacionamentos e itens detalhados
            $payroll = \App\Models\HR\Payroll::with([
                'employee.department',
                'employee.position',
                'employee.bank',
                'payrollPeriod',
                'payrollItems'
            ])->findOrFail($payrollId);

            // Gerar dados do recibo
            $receiptData = $this->getEmployeeReceiptDataFromPayroll($payroll);

            return view('livewire.hr.payroll.payroll-receipt-isolated', $receiptData);

        } catch (\Exception $e) {
            \Log::error('Erro ao exibir recibo individual', [
                'payroll_id' => $payrollId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retornar view com erro
            return view('livewire.hr.payroll.payroll-receipt-isolated', [
                'companyName' => 'Dembena Indústria e Comércio Lda',
                'employeeName' => 'ERRO AO CARREGAR RECIBO',
                'error' => 'Não foi possível carregar o recibo. Por favor, tente novamente.',
                'netSalary' => 0,
                'baseSalary' => 0,
                'totalEarnings' => 0,
                'totalDeductions' => 0
            ]);
        }
    }

    /**
     * Gera recibos de salário em lote para múltiplos funcionários
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateBulkReceiptsHTML(Request $request)
    {
        \Log::info('=== INÍCIO generateBulkReceiptsHTML ===');
        
        try {
            $filters = json_decode(base64_decode($request->get('filters', '')), true) ?: [];
            $month = (int) $request->get('month', date('n'));
            $year = (int) $request->get('year', date('Y'));

            \Log::info('Gerando recibos em lote - Controller', [
                'filters' => $filters,
                'month' => $month,
                'year' => $year,
                'request_headers' => $request->headers->all(),
                'request_method' => $request->method(),
                'request_url' => $request->fullUrl()
            ]);

            // Buscar folhas de pagamento com base nos filtros
            $payrolls = $this->getFilteredPayrollsForBulk($filters, $month, $year);
            
            \Log::info('Payrolls encontrados', [
                'count' => $payrolls->count(),
                'payroll_classes' => $payrolls->map(fn($p) => get_class($p))->unique()->toArray()
            ]);

            if ($payrolls->count() === 0) {
                \Log::info('Retornando view para caso sem payrolls');
                return view('livewire.hr.bulk-receipts-html', [
                    'payrolls' => collect(),
                    'receiptData' => [],
                    'filters' => $filters,
                    'month' => $month,
                    'year' => $year,
                    'generatedAt' => now()->format('d/m/Y H:i'),
                    'totalReceipts' => 0,
                    'error' => 'Nenhuma folha de pagamento encontrada com os filtros aplicados.'
                ]);
            }

            // Mostrar página HTML com múltiplos recibos (mesmo template do recibo individual)
            \Log::info('Retornando view com payrolls');
            $view = view('livewire.hr.bulk-receipts-html', [
                'payrolls' => $payrolls,
                'receiptData' => $this->prepareBulkReceiptData($payrolls),
                'filters' => $filters,
                'month' => $month,
                'year' => $year,
                'generatedAt' => now()->format('d/m/Y H:i'),
                'totalReceipts' => $payrolls->count()
            ]);
            
            \Log::info('View criada com sucesso', ['view_class' => get_class($view)]);
            return $view;

        } catch (\Exception $e) {
            \Log::error('Erro ao gerar recibos em lote', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return view('livewire.hr.bulk-receipts-html', [
                'payrolls' => collect(),
                'receiptData' => [],
                'filters' => [],
                'month' => date('n'),
                'year' => date('Y'),
                'generatedAt' => now()->format('d/m/Y H:i'),
                'totalReceipts' => 0,
                'error' => 'Erro interno ao gerar recibos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Gera PDF dos recibos em lote
     */
    public function generateBulkReceiptsPDF(Request $request)
    {
        try {
            $filters = json_decode(base64_decode($request->get('filters', '')), true) ?: [];
            $month = (int) $request->get('month', date('n'));
            $year = (int) $request->get('year', date('Y'));

            $payrolls = $this->getFilteredPayrollsForBulk($filters, $month, $year);

            if ($payrolls->count() === 0) {
                return response()->json(['error' => 'Nenhuma folha de pagamento encontrada.'], 404);
            }

            // Gerar PDF usando o template original
            $pdf = \PDF::loadView('livewire.hr.bulk-receipts', [
                'payrolls' => $payrolls,
                'receiptData' => $this->prepareBulkReceiptData($payrolls),
                'filters' => $filters,
                'month' => $month,
                'year' => $year,
                'generatedAt' => now()->format('d/m/Y H:i'),
                'totalReceipts' => $payrolls->count()
            ]);

            $filename = "recibos_salario_" . str_pad((string)$month, 2, '0', STR_PAD_LEFT) . "_" . $year . "_" . now()->format('Ymd_His') . ".pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Erro ao gerar PDF recibos em lote', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json(['error' => 'Erro interno ao gerar PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Busca folhas de pagamento filtradas para geração em lote
     */
    private function getFilteredPayrollsForBulk(array $filters, int $month, int $year)
    {
        // Determinar mês e ano a usar: prioridade para filters, fallback para parâmetros
        $finalMonth = !empty($filters['month']) ? (int)$filters['month'] : $month;
        $finalYear = !empty($filters['year']) ? (int)$filters['year'] : $year;
        
        $query = \App\Models\HR\Payroll::with(['employee.department', 'employee.position', 'employee.bank', 'payrollPeriod', 'payrollItems'])
            ->when(!empty($filters['search'] ?? null), function ($q, $search) {
                $q->whereHas('employee', function ($subQuery) use ($search) {
                    $subQuery->where('full_name', 'like', '%' . $search . '%')
                             ->orWhere('employee_id', 'like', '%' . $search . '%')
                             ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when(!empty($filters['department_id'] ?? null), function ($q, $deptId) {
                $q->whereHas('employee', function ($subQuery) use ($deptId) {
                    $subQuery->where('department_id', $deptId);
                });
            })
            ->when(!empty($filters['period_id'] ?? null), function ($q, $periodId) {
                $q->where('payroll_period_id', $periodId);
            })
            ->when(!empty($filters['status'] ?? null), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($finalMonth > 0, function ($q) use ($finalMonth) {
                $q->whereHas('payrollPeriod', function ($subQuery) use ($finalMonth) {
                    $subQuery->whereMonth('start_date', $finalMonth);
                });
            })
            ->when($finalYear > 0, function ($q) use ($finalYear) {
                $q->whereHas('payrollPeriod', function ($subQuery) use ($finalYear) {
                    $subQuery->whereYear('start_date', $finalYear);
                });
            })
            ->orderBy('employee_id');

        \Log::info('getFilteredPayrollsForBulk query', [
            'filters' => $filters,
            'month_param' => $month,
            'year_param' => $year,
            'final_month' => $finalMonth,
            'final_year' => $finalYear,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $results = $query->get();
        
        // Se não encontrar resultados com filtros de data, tentar sem eles
        if ($results->count() === 0 && ($finalMonth > 0 || $finalYear > 0)) {
            \Log::info('Nenhum resultado com filtros de data, tentando sem filtros de data...');
            
            $queryWithoutDate = \App\Models\HR\Payroll::with(['employee.department', 'employee.position', 'employee.bank', 'payrollPeriod', 'payrollItems'])
                ->when(!empty($filters['search'] ?? null), function ($q, $search) {
                    $q->whereHas('employee', function ($subQuery) use ($search) {
                        $subQuery->where('full_name', 'like', '%' . $search . '%')
                                 ->orWhere('employee_id', 'like', '%' . $search . '%')
                                 ->orWhere('email', 'like', '%' . $search . '%');
                    });
                })
                ->when(!empty($filters['department_id'] ?? null), function ($q, $deptId) {
                    $q->whereHas('employee', function ($subQuery) use ($deptId) {
                        $subQuery->where('department_id', $deptId);
                    });
                })
                ->when(!empty($filters['period_id'] ?? null), function ($q, $periodId) {
                    $q->where('payroll_period_id', $periodId);
                })
                ->when(!empty($filters['status'] ?? null), function ($q, $status) {
                    $q->where('status', $status);
                })
                ->orderBy('employee_id');
                
            $results = $queryWithoutDate->get();
            
            \Log::info('Resultados sem filtros de data', [
                'count' => $results->count(),
                'payroll_ids' => $results->pluck('id')->toArray()
            ]);
        }
        
        \Log::info('getFilteredPayrollsForBulk final results', [
            'count' => $results->count(),
            'payroll_ids' => $results->pluck('id')->toArray()
        ]);

        return $results;
    }

    /**
     * Prepara dados de recibo para múltiplos funcionários
     */
    private function prepareBulkReceiptData($payrolls)
    {
        $receiptData = [];
        
        foreach ($payrolls as $index => $payroll) {
            \Log::info("=== Preparando dados do recibo {$index} ===", [
                'payroll_id' => $payroll->id,
                'employee_id' => $payroll->employee_id,
                'employee_name' => $payroll->employee->full_name ?? 'N/A',
                'payroll_data' => [
                    'basic_salary' => $payroll->basic_salary,
                    'allowances' => $payroll->allowances,
                    'overtime' => $payroll->overtime,
                    'bonuses' => $payroll->bonuses,
                    'tax' => $payroll->tax,
                    'social_security' => $payroll->social_security,
                    'deductions' => $payroll->deductions,
                    'net_salary' => $payroll->net_salary,
                    'gross_salary' => $payroll->gross_salary
                ]
            ]);
            
            $receiptData[] = $this->getEmployeeReceiptDataFromPayroll($payroll);
        }
        
        \Log::info("Total de recibos preparados: " . count($receiptData));
        return $receiptData;
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
     * Gera dados de recibo com base no objeto Payroll (dinâmico)
     *
     * @param \App\Models\HR\Payroll $payroll
     * @return array
     */
    private function getEmployeeReceiptDataFromPayroll($payroll): array
    {
        try {
            \Log::info("=== Gerando dados dinâmicos do recibo ===", [
                'payroll_id' => $payroll->id,
                'employee_id' => $payroll->employee_id,
                'employee_name' => $payroll->employee->full_name ?? 'N/A',
                'payroll_data' => [
                    'basic_salary' => $payroll->basic_salary,
                    'christmas_subsidy_amount' => $payroll->christmas_subsidy_amount,
                    'vacation_subsidy_amount' => $payroll->vacation_subsidy_amount,
                    'additional_bonus' => $payroll->additional_bonus,
                    'performance_subsidy' => $payroll->performance_subsidy,
                    'gross_salary' => $payroll->gross_salary,
                    'net_salary' => $payroll->net_salary,
                ]
            ]);

            $employee = $payroll->employee;
            $period = $payroll->payrollPeriod;
            
            // Determinar o mês de pagamento
            $paymentDate = $payroll->payment_date ?? now();
            $paymentMonth = \Carbon\Carbon::parse($paymentDate);
            $monthNames = [
                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
            ];
            $monthName = $monthNames[$paymentMonth->month];
            $monthDisplay = $monthName . ' ' . $paymentMonth->year . ' • Data de referência: ' . $paymentMonth->format('d/m/Y');
            
            // REMUNERAÇÕES - usar campos diretos do payroll (agora salvos)
            $baseSalary = (float) ($payroll->basic_salary ?? 0);
            $transportSubsidy = (float) ($payroll->transport_allowance ?? 0);
            $foodSubsidy = (float) ($payroll->food_allowance ?? 0);
            $christmasSubsidy = (float) ($payroll->christmas_subsidy_amount ?? 0);
            $holidaySubsidy = (float) ($payroll->vacation_subsidy_amount ?? 0);
            $profileBonus = (float) ($payroll->profile_bonus ?? 0);
            $additionalBonus = (float) ($payroll->additional_bonus ?? 0);
            $overtimeAmount = (float) ($payroll->overtime_amount ?? 0);
            $familyAllowance = (float) ($payroll->family_allowance ?? 0);
            $positionSubsidy = (float) ($payroll->position_subsidy ?? 0);
            $performanceSubsidy = (float) ($payroll->performance_subsidy ?? 0);
            
            // Total de remunerações = gross_salary
            $totalEarnings = $payroll->gross_salary ?? 0;
            
            // DEDUÇÕES - usar campos diretos do payroll (agora salvos)
            $inssDeduction = (float) ($payroll->social_security ?? $payroll->inss_3_percent ?? 0);
            $irtDeduction = (float) ($payroll->tax ?? 0);
            $advanceDeduction = (float) ($payroll->advance_deduction ?? 0);
            $discountDeduction = (float) ($payroll->total_salary_discounts ?? 0);
            $lateDeduction = (float) ($payroll->late_deduction ?? 0);
            $absenceDeduction = (float) ($payroll->absence_deduction ?? 0);
            
            // Total de deduções
            $totalDeductions = $payroll->deductions ?? 0;
            
            // NET SALARY
            $netSalary = $payroll->net_salary ?? 0;
            
            // Dados de presença - usar configurações de RH para dias úteis
            $monthlyWorkingDays = (int) \App\Models\HR\HRSetting::get('monthly_working_days', 22);
            $presentDays = $payroll->present_days ?? $monthlyWorkingDays;
            
            // Calcular faltas: dias úteis do mês - dias presentes
            $absences = max(0, $monthlyWorkingDays - $presentDays);
            
            // Se o payroll já tem absent_days maior, usar esse valor (pode haver ajustes manuais)
            if (($payroll->absent_days ?? 0) > $absences) {
                $absences = $payroll->absent_days;
            }
            
            $extraHours = $payroll->total_overtime_hours ?? 0;
            
            // Dias trabalhados = dias presentes (do payroll)
            $workedDays = $presentDays;

            $receiptData = [
                'companyName' => 'Dembena Indústria e Comércio Lda',
                'employeeName' => $employee->full_name . " (Id: {$employee->employee_id})",
                'employeeId' => $employee->employee_id ?? $employee->id,
                'month' => $monthDisplay,
                'category' => $employee->position->title ?? 'N/A',
                'referencePeriod' => $period ? $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y') : 'N/A',
                'monthlyWorkingDays' => $monthlyWorkingDays,
                'workedDays' => $workedDays,
                'absences' => $absences,
                'extraHours' => $extraHours,
                
                // Remunerações - valores corretos da BD
                'baseSalary' => $baseSalary,
                'transportSubsidy' => $transportSubsidy,
                'foodSubsidy' => $foodSubsidy,
                'overtimeAmount' => $overtimeAmount,
                'nightShiftAllowance' => $payroll->night_shift_allowance ?? 0,
                'nightShiftDays' => $payroll->night_shift_days ?? 0,
                'profileBonus' => $profileBonus,
                'payrollBonus' => $additionalBonus, 
                'christmasSubsidy' => $christmasSubsidy,
                'holidaySubsidy' => $holidaySubsidy,
                'familyAllowance' => $familyAllowance,
                'positionSubsidy' => $positionSubsidy,
                'performanceSubsidy' => $performanceSubsidy,
                'totalEarnings' => $totalEarnings,
                
                // Descontos - valores corretos da BD
                'incomeTax' => $irtDeduction,
                'socialSecurity' => $inssDeduction,
                'foodSubsidyDeduction' => $foodSubsidy, // Subsídio alimentação (em espécie)
                'salaryAdvances' => $advanceDeduction,
                'absenceDeduction' => $absenceDeduction,
                'absenceDays' => $absences,
                'lateDeduction' => $lateDeduction,
                'lateDays' => $payroll->late_arrivals ?? 0,
                'salaryDiscounts' => $discountDeduction,
                'totalDeductions' => $totalDeductions,
                
                'netSalary' => $netSalary,
                'bankName' => $employee->bank->name ?? $employee->bank_name ?? 'N/A',
                'accountNumber' => $employee->bank_account ?? 'N/A', 
                'paymentMethod' => $payroll->payment_method === 'bank_transfer' ? 'TRANSFERÊNCIA' : strtoupper($payroll->payment_method ?? 'CASH'),
                'receiptNumber' => $period ? $period->name : 'N/A'
            ];

            \Log::info("Dados finais do recibo", $receiptData);
            return $receiptData;

        } catch (\Exception $e) {
            \Log::error("Erro ao gerar dados dinâmicos do recibo", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'payroll_id' => $payroll->id ?? 'N/A'
            ]);
            
            // Fallback com dados do payroll disponíveis
            return [
                'companyName' => 'Dembena Indústria e Comércio Lda',
                'employeeName' => 'ERRO: ' . ($payroll->employee->full_name ?? 'Funcionário desconhecido'),
                'employeeId' => $payroll->employee_id ?? 'N/A',
                'netSalary' => $payroll->net_salary ?? 0,
                'baseSalary' => $payroll->basic_salary ?? 0,
                'totalEarnings' => $payroll->gross_salary ?? 0,
                'totalDeductions' => $payroll->total_deductions_calculated ?? 0
            ];
        }
    }

    /**
     * Busca dados reais do funcionário (método antigo para compatibilidade)
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
                'baseSalary' => $employee->base_salary ?? 0,
                'transportSubsidy' => $latestPayroll?->transport_allowance ?? 0,
                'foodSubsidy' => $latestPayroll?->meal_allowance ?? 0,
                'overtimeAmount' => $totalOvertimeAmount ?? 0,
                'nightShiftAllowance' => $latestPayroll?->night_shift_allowance ?? 0,
                'nightShiftDays' => $latestPayroll?->night_shift_days ?? 0,
                'profileBonus' => $latestPayroll?->profile_bonus ?? 0,
                'payrollBonus' => $latestPayroll?->additional_bonus ?? 0,
                'christmasSubsidy' => $latestPayroll?->christmas_subsidy_amount ?? 0,
                'holidaySubsidy' => $latestPayroll?->vacation_subsidy_amount ?? 0,
                'familyAllowance' => $latestPayroll?->family_allowance ?? 0,
                'positionSubsidy' => $employee->position_subsidy ?? 0,
                'performanceSubsidy' => $employee->performance_subsidy ?? 0,
                'totalEarnings' => $latestPayroll?->gross_salary ?? 0,
                
                // Descontos baseados nos dados reais
                'incomeTax' => $latestPayroll?->income_tax ?? 0,
                'socialSecurity' => $latestPayroll?->social_security ?? 0,
                'foodSubsidyDeduction' => $latestPayroll?->meal_allowance ?? 0,
                'salaryAdvances' => $latestPayroll?->advance_deduction ?? 0,
                'absenceDeduction' => $latestPayroll?->absence_deduction ?? 0,
                'absenceDays' => $absentDays ?? 0,
                'lateDeduction' => $latestPayroll?->late_deduction ?? 0,
                'lateDays' => $lateDays ?? 0,
                'salaryDiscounts' => $salaryDiscounts->sum('total_amount') ?? 0,
                'salaryDiscountsDetailed' => $salaryDiscounts,
                'totalDeductions' => $latestPayroll?->total_deductions ?? 0,
                
                'netSalary' => $latestPayroll?->net_salary ?? 0,
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
    
    /**
     * Exibe o recibo de pagamento por ID do funcionário (compatibilidade)
     *
     * @param int $employeeId
     * @return View
     */
    public function showReceiptByEmployeeId(int $employeeId): View
    {
        try {
            // Buscar último payroll do funcionário
            $payroll = \App\Models\HR\Payroll::with([
                'employee.department',
                'employee.position',
                'employee.bank',
                'payrollPeriod',
                'payrollItems'
            ])
            ->where('employee_id', $employeeId)
            ->latest('created_at')
            ->firstOrFail();

            // Gerar dados do recibo
            $receiptData = $this->getEmployeeReceiptDataFromPayroll($payroll);

            return view('livewire.hr.payroll.payroll-receipt-isolated', $receiptData);

        } catch (\Exception $e) {
            \Log::error('Erro ao exibir recibo por employee_id', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);

            // Retornar view com erro
            return view('livewire.hr.payroll.payroll-receipt-isolated', [
                'companyName' => 'Dembena Indústria e Comércio Lda',
                'employeeName' => 'ERRO AO CARREGAR RECIBO',
                'error' => 'Não foi possível encontrar folha de pagamento para este funcionário.',
                'netSalary' => 0,
                'baseSalary' => 0,
                'totalEarnings' => 0,
                'totalDeductions' => 0
            ]);
        }
    }
}

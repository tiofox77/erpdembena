<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\HR\Employee;
use App\Models\HR\HRSetting;
use App\Models\HR\IRTTaxBracket;
use App\Models\HR\Attendance;
use App\Models\HR\OvertimeRecord;
use App\Models\HR\SalaryAdvance;
use App\Models\HR\SalaryDiscount;
use App\Models\HR\Leave;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * PayrollCalculatorHelper
 * 
 * Helper centralizado para cálculos de folha de pagamento (payroll)
 * Contém toda a lógica de cálculo extraída da modal ProcessPayrollModal e componente Livewire
 * 
 * Este helper garante que os cálculos sejam consistentes entre:
 * - Pagamento individual de salário
 * - Pagamento em lote (batch)
 * - Processamento de payroll
 */
class PayrollCalculatorHelper
{
    protected Employee $employee;
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected array $hrSettings = [];
    
    // Dados de presença
    protected int $totalWorkingDays = 0;
    protected int $presentDays = 0; // Inclui férias para cálculo de salário
    protected int $daysWorkedEffectively = 0; // Exclui férias (para subsídio transporte)
    protected int $absentDays = 0;
    protected int $lateArrivals = 0;
    protected float $totalAttendanceHours = 0.0;
    protected array $attendanceData = [];
    
    // Dados de horas extras
    protected float $totalOvertimeHours = 0.0;
    protected float $totalOvertimeAmount = 0.0;
    protected array $overtimeRecords = [];
    
    // Adiantamentos salariais
    protected float $totalSalaryAdvances = 0.0;
    protected float $advanceDeduction = 0.0;
    protected array $salaryAdvances = [];
    
    // Descontos salariais
    protected float $totalSalaryDiscounts = 0.0;
    protected array $salaryDiscounts = [];
    
    // Licenças
    protected int $totalLeaveDays = 0;
    protected int $unpaidLeaveDays = 0;
    protected float $leaveDeduction = 0.0;
    protected array $leaveRecords = [];
    
    // Componentes salariais
    protected float $basicSalary = 0.0;
    protected float $hourlyRate = 0.0;
    protected float $transportAllowance = 0.0;
    protected float $mealAllowance = 0.0;
    protected float $bonusAmount = 0.0;
    protected float $additionalBonusAmount = 0.0;
    protected bool $christmasSubsidy = false;
    protected bool $vacationSubsidy = false;
    protected bool $isFoodInKind = false;
    
    // Deduções por presença
    protected float $lateDeduction = 0.0;
    protected float $absenceDeduction = 0.0;
    
    // Resultados calculados
    protected array $calculationResults = [];
    
    public function __construct(Employee $employee, Carbon $startDate, Carbon $endDate)
    {
        $this->employee = $employee;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        
        $this->loadHRSettings();
        // Usar base_salary DIRETO (coluna correta do banco)
        $this->basicSalary = (float) ($employee->base_salary ?? 0);
        $this->mealAllowance = (float) ($employee->food_benefit ?? 0);
        $this->bonusAmount = (float) ($employee->bonus_amount ?? 0);
        
        \Log::info('💰 PayrollCalculatorHelper - Constructor', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'base_salary_from_db' => $employee->base_salary ?? 'NULL',
            'basicSalary_set_to' => $this->basicSalary,
        ]);
    }
    
    protected function loadHRSettings(): void
    {
        $this->hrSettings = [
            // Horas e dias de trabalho
            'working_hours_per_day' => (float) HRSetting::get('working_hours_per_day', 8),
            'monthly_working_days' => (int) HRSetting::get('monthly_working_days', 22),
            
            // Subsídios percentuais
            'vacation_subsidy_percentage' => (float) HRSetting::get('vacation_subsidy_percentage', 50),
            'christmas_subsidy_percentage' => (float) HRSetting::get('christmas_subsidy_percentage', 50),
            
            // INSS - Taxas dinâmicas
            'inss_employee_rate' => (float) HRSetting::get('inss_employee_rate', 3),
            'inss_employer_rate' => (float) HRSetting::get('inss_employer_rate', 8),
            
            // IRT - Isenções fiscais dinâmicas
            'min_salary_tax_exempt' => (float) HRSetting::get('min_salary_tax_exempt', 70000),
            'transport_tax_exempt' => (float) HRSetting::get('transport_tax_exempt', 30000),
            'food_tax_exempt' => (float) HRSetting::get('food_tax_exempt', 30000),
            
            // Overtime - Multiplicadores dinâmicos
            'overtime_multiplier_weekday' => (float) HRSetting::get('overtime_multiplier_weekday', 1.5),
            'overtime_multiplier_weekend' => (float) HRSetting::get('overtime_multiplier_weekend', 2.0),
            'overtime_multiplier_holiday' => (float) HRSetting::get('overtime_multiplier_holiday', 2.5),
            'overtime_first_hour_weekday' => (float) HRSetting::get('overtime_first_hour_weekday', 1.25),
            'overtime_additional_hours_weekday' => (float) HRSetting::get('overtime_additional_hours_weekday', 1.375),
            
            // Limites de overtime
            'overtime_daily_limit' => (int) HRSetting::get('overtime_daily_limit', 2),
            'overtime_monthly_limit' => (int) HRSetting::get('overtime_monthly_limit', 48),
            'overtime_yearly_limit' => (int) HRSetting::get('overtime_yearly_limit', 200),
        ];
    }
    
    public function calculateHourlyRate(): float
    {
        $workingDays = $this->hrSettings['monthly_working_days'] ?? 22;
        $workingHours = $this->hrSettings['working_hours_per_day'] ?? 8;
        $totalMonthlyHours = $workingDays * $workingHours;
        
        $this->hourlyRate = $totalMonthlyHours > 0 ? $this->basicSalary / $totalMonthlyHours : 0;
        return $this->hourlyRate;
    }
    
    public function loadAttendanceData(): self
    {
        $attendances = Attendance::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();
        
        $monthlyWorkingDays = (int) HRSetting::get('monthly_working_days', 22);
        
        if ($monthlyWorkingDays > 0) {
            $this->totalWorkingDays = $monthlyWorkingDays;
        } else {
            $this->totalWorkingDays = 0;
            $current = $this->startDate->copy();
            while ($current <= $this->endDate) {
                if ($current->isWeekday()) {
                    $this->totalWorkingDays++;
                }
                $current->addDay();
            }
        }
        
        // Férias (leave) são consideradas como dias presentes e pagos para salário
        $this->presentDays = $attendances->whereIn('status', ['present', 'late', 'half_day', 'leave'])->count();
        
        // Dias efetivamente trabalhados (EXCLUINDO férias) para subsídio de transporte
        $this->daysWorkedEffectively = $attendances->whereIn('status', ['present', 'late', 'half_day'])->count();
        
        $this->absentDays = $this->totalWorkingDays - $this->presentDays;
        $this->lateArrivals = $attendances->where('status', 'late')->count();
        
        $this->totalAttendanceHours = 0;
        $standardWorkDay = 8;
        
        foreach ($attendances as $attendance) {
            // Férias (leave) contam como dia trabalhado completo
            if (in_array($attendance->status, ['present', 'late', 'half_day', 'leave'])) {
                $hours = 0;
                
                if ($attendance->time_in && $attendance->time_out) {
                    $timeIn = Carbon::parse($attendance->time_in);
                    $timeOut = Carbon::parse($attendance->time_out);
                    $hours = $timeIn->diffInHours($timeOut);
                    
                    if ($attendance->status === 'half_day') {
                        $hours = min($hours / 2, 4);
                    }
                } else {
                    switch ($attendance->status) {
                        case 'present':
                            $hours = $standardWorkDay;
                            break;
                        case 'late':
                            $hours = $standardWorkDay;
                            break;
                        case 'half_day':
                            $hours = $standardWorkDay / 2;
                            break;
                        case 'leave':
                            // Férias = dia completo pago
                            $hours = $standardWorkDay;
                            break;
                    }
                }
                
                $this->totalAttendanceHours += $hours;
            }
        }
        
        $this->attendanceData = $attendances->toArray();
        $this->calculateAttendanceDeductions($attendances);
        
        return $this;
    }
    
    protected function calculateAttendanceDeductions($attendances): void
    {
        $this->lateDeduction = 0.0;
        $this->absenceDeduction = 0.0;
        
        $monthlyWorkingDays = (int) HRSetting::get('monthly_working_days', 22);
        $workingDaysForCalculation = $monthlyWorkingDays > 0 ? $monthlyWorkingDays : $this->totalWorkingDays;
        
        $dailyRate = $workingDaysForCalculation > 0 ? $this->basicSalary / $workingDaysForCalculation : 0;
        
        // Contar registros explícitos de ausência e meio-dia
        $explicitAbsences = 0;
        $explicitHalfDays = 0;
        
        foreach ($attendances as $attendance) {
            switch ($attendance->status) {
                case 'late':
                    $this->lateDeduction += $this->hourlyRate;
                    break;
                case 'absent':
                    $this->absenceDeduction += $dailyRate;
                    $explicitAbsences++;
                    break;
                case 'half_day':
                    $this->absenceDeduction += ($dailyRate / 2);
                    $explicitHalfDays++;
                    break;
            }
        }
        
        // CORREÇÃO: Se absentDays > 0 mas não há registros explícitos suficientes,
        // calcular dedução para os dias ausentes sem registro
        $implicitAbsences = $this->absentDays - $explicitAbsences - ($explicitHalfDays * 0.5);
        
        if ($implicitAbsences > 0) {
            $this->absenceDeduction += ($implicitAbsences * $dailyRate);
        }
    }
    
    public function loadOvertimeData(): self
    {
        $overtimeRecords = OvertimeRecord::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
            ->where('status', 'approved')
            ->get();
        
        $this->totalOvertimeHours = $overtimeRecords->sum('hours');
        $this->totalOvertimeAmount = $overtimeRecords->sum('amount');
        $this->overtimeRecords = $overtimeRecords->toArray();
        
        return $this;
    }
    
    public function loadSalaryAdvances(): self
    {
        $advances = SalaryAdvance::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->get();
        
        $this->totalSalaryAdvances = $advances->sum('amount');
        $this->advanceDeduction = $advances->sum('installment_amount');
        
        $this->salaryAdvances = $advances->map(function($advance) {
            return [
                'id' => $advance->id,
                'request_date' => $advance->request_date->format('d/m/Y'),
                'amount' => $advance->amount,
                'installments' => $advance->installments,
                'installment_amount' => $advance->installment_amount,
                'remaining_installments' => $advance->remaining_installments,
                'reason' => $advance->reason,
            ];
        })->toArray();
        
        return $this;
    }
    
    public function loadSalaryDiscounts(): self
    {
        $discounts = SalaryDiscount::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->where('remaining_installments', '>', 0)
            ->get();
        
        $this->totalSalaryDiscounts = $discounts->sum('installment_amount');
        
        $this->salaryDiscounts = $discounts->map(function($discount) {
            return [
                'id' => $discount->id,
                'request_date' => $discount->request_date->format('d/m/Y'),
                'amount' => $discount->amount,
                'installments' => $discount->installments,
                'installment_amount' => $discount->installment_amount,
                'remaining_installments' => $discount->remaining_installments,
                'reason' => $discount->reason,
                'discount_type' => $discount->discount_type,
            ];
        })->toArray();
        
        return $this;
    }
    
    public function loadLeaveData(): self
    {
        $leaves = Leave::where('employee_id', $this->employee->id)
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')])
                      ->orWhereBetween('end_date', [$this->startDate->format('Y-m-d'), $this->endDate->format('Y-m-d')]);
            })
            ->where('status', 'approved')
            ->get();
        
        $this->totalLeaveDays = 0;
        $this->unpaidLeaveDays = 0;
        
        foreach ($leaves as $leave) {
            $leaveDays = Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
            $this->totalLeaveDays += $leaveDays;
            
            $leaveType = \App\Models\HR\LeaveType::find($leave->leave_type_id);
            if ($leaveType && !$leaveType->is_paid) {
                $this->unpaidLeaveDays += $leaveDays;
            }
        }
        
        $dailyRate = $this->basicSalary / ($this->hrSettings['monthly_working_days'] ?? 22);
        $this->leaveDeduction = $this->unpaidLeaveDays * $dailyRate;
        $this->leaveRecords = $leaves->toArray();
        
        return $this;
    }
    
    public function loadAllEmployeeData(): self
    {
        $this->calculateHourlyRate();
        $this->loadAttendanceData();
        $this->loadOvertimeData();
        $this->loadSalaryAdvances();
        $this->loadSalaryDiscounts();
        $this->loadLeaveData();
        
        return $this;
    }
    
    public function setChristmasSubsidy(bool $value): self
    {
        $this->christmasSubsidy = $value;
        return $this;
    }
    
    public function setVacationSubsidy(bool $value): self
    {
        $this->vacationSubsidy = $value;
        return $this;
    }
    
    public function setAdditionalBonus(float $amount): self
    {
        $this->additionalBonusAmount = $amount;
        return $this;
    }
    
    public function setFoodInKind(bool $value): self
    {
        $this->isFoodInKind = $value;
        return $this;
    }
    
    public function setOvertimeAmount(float $amount): self
    {
        $this->totalOvertimeAmount = $amount;
        return $this;
    }
    
    public function setAdvanceDeduction(float $amount): self
    {
        $this->advanceDeduction = $amount;
        return $this;
    }
    
    /**
     * Calcular subsídio de transporte proporcional baseado nos dias EFETIVAMENTE trabalhados
     * IMPORTANTE: Dias de férias NÃO recebem subsídio de transporte (não há deslocamento)
     */
    public function calculateProportionalTransportAllowance(): float
    {
        if ($this->totalWorkingDays <= 0) {
            return 0.0;
        }
        
        $fullTransportAllowance = (float) ($this->employee->transport_benefit ?? 0);
        // Usar daysWorkedEffectively (EXCLUI férias) em vez de presentDays
        $proportionalAllowance = ($fullTransportAllowance / $this->totalWorkingDays) * $this->daysWorkedEffectively;
        
        $this->transportAllowance = $proportionalAllowance;
        return $proportionalAllowance;
    }
    
    /**
     * Obter subsídio de transporte completo
     */
    public function getFullTransportBenefit(): float
    {
        return (float) ($this->employee->transport_benefit ?? 0);
    }
    
    /**
     * Obter desconto de transporte por ausências
     */
    public function getTransportDiscountAmount(): float
    {
        return $this->getFullTransportBenefit() - $this->transportAllowance;
    }
    
    /**
     * Obter subsídio de transporte tributável (acima do limite de isenção)
     */
    public function getTaxableTransportAllowance(): float
    {
        $exemptLimit = $this->hrSettings['transport_tax_exempt'] ?? 30000.0;
        return max(0, $this->transportAllowance - $exemptLimit);
    }
    
    /**
     * Obter subsídio de transporte isento (até limite de isenção)
     */
    public function getExemptTransportAllowance(): float
    {
        $exemptLimit = $this->hrSettings['transport_tax_exempt'] ?? 30000.0;
        return min($this->transportAllowance, $exemptLimit);
    }
    
    /**
     * Obter subsídio de alimentação tributável (acima do limite de isenção)
     */
    public function getTaxableFoodAllowance(): float
    {
        $exemptLimit = $this->hrSettings['food_tax_exempt'] ?? 30000.0;
        return max(0, $this->mealAllowance - $exemptLimit);
    }
    
    /**
     * Obter subsídio de alimentação isento (até limite de isenção)
     */
    public function getExemptFoodAllowance(): float
    {
        $exemptLimit = $this->hrSettings['food_tax_exempt'] ?? 30000.0;
        return min($this->mealAllowance, $exemptLimit);
    }
    
    /**
     * Calcular valor do subsídio de Natal
     */
    public function getChristmasSubsidyAmount(): float
    {
        return $this->christmasSubsidy ? ($this->basicSalary * 0.5) : 0.0;
    }
    
    /**
     * Calcular valor do subsídio de férias
     */
    public function getVacationSubsidyAmount(): float
    {
        return $this->vacationSubsidy ? ($this->basicSalary * 0.5) : 0.0;
    }
    
    /**
     * Calcular Salário Bruto (Main Salary)
     * Inclui: salário base + alimentação + transporte + horas extras + bônus
     */
    public function calculateGrossSalary(): float
    {
        $basic = $this->basicSalary;
        $food = $this->mealAllowance;
        $transport = $this->transportAllowance;
        $overtime = $this->totalOvertimeAmount;
        $bonus = $this->bonusAmount;
        $additionalBonus = $this->additionalBonusAmount;
        $christmasAmount = $this->getChristmasSubsidyAmount();
        $vacationAmount = $this->getVacationSubsidyAmount();
        
        return $basic + $food + $transport + $overtime + $bonus + $additionalBonus + $christmasAmount + $vacationAmount;
    }
    
    /**
     * Calcular Salário Principal (Main Salary) - MATCH COM MODAL INDIVIDUAL
     * Base Salary + Food + Transport + Overtime + Bonus + Subsidies - ABSENCE
     * 
     * IMPORTANTE: Ausências SÃO deduzidas do Main Salary (igual modal individual)
     */
    public function calculateMainSalary(): float
    {
        $basic = $this->basicSalary;
        $food = $this->mealAllowance;
        $transport = $this->transportAllowance;
        $overtime = $this->totalOvertimeAmount;
        $bonus = $this->bonusAmount;
        $additionalBonus = $this->additionalBonusAmount;
        $christmasAmount = $this->getChristmasSubsidyAmount();
        $vacationAmount = $this->getVacationSubsidyAmount();
        
        // Deduzir ausências do main salary (igual modal individual)
        $absence = $this->absenceDeduction;
        
        return max(0.0, $basic + $food + $transport + $overtime + $bonus + $additionalBonus + $christmasAmount + $vacationAmount - $absence);
    }
    
    /**
     * Calcular Base Tributável para IRT (Matéria Coletável - MC)
     * Gross Salary - INSS - Isenções (30k transporte + 30k alimentação)
     */
    public function calculateIRTBase(): float
    {
        $grossSalary = $this->calculateGrossSalary();
        
        // Calcular INSS 3%
        $inss = $this->calculateINSS();
        
        // Isenções: até 30k de transporte e 30k de alimentação
        $exemptTransport = $this->getExemptTransportAllowance();
        $exemptFood = $this->getExemptFoodAllowance();
        
        // MC = Gross - INSS - Isenções
        $mc = $grossSalary - $inss - $exemptTransport - $exemptFood;
        
        return max(0.0, $mc);
    }
    
    /**
     * Calcular base do INSS - MATCH COM MODAL INDIVIDUAL
     * 
     * Base = Basic + Transport + Food + Overtime (SEM deduzir ausências)
     * Ausências NÃO afetam a base do INSS
     */
    public function calculateINSSBase(): float
    {
        $basic = $this->basicSalary;
        $transport = $this->transportAllowance;
        $food = $this->mealAllowance;
        $overtime = $this->totalOvertimeAmount;
        
        return $basic + $transport + $food + $overtime;
    }
    
    /**
     * Calcular INSS (3% sobre a base) - MATCH COM MODAL INDIVIDUAL
     * 
     * Base = Basic + Transport + Food + Overtime (sem deduzir ausências)
     */
    public function calculateINSS(): float
    {
        $inssBase = $this->calculateINSSBase();
        $rate = ($this->hrSettings['inss_employee_rate'] ?? 3) / 100;
        return round($inssBase * $rate, 2);
    }
    
    /**
     * Calcular INSS 8% (ilustrativo - pago pelo empregador)
     */
    public function calculateINSS8Percent(): float
    {
        $inssBase = $this->calculateINSSBase();
        $rate = ($this->hrSettings['inss_employer_rate'] ?? 8) / 100;
        return round($inssBase * $rate, 2);
    }
    
    /**
     * Calcular IRT usando escalões progressivos
     */
    public function calculateIRT(): float
    {
        $irtBase = $this->calculateIRTBase();
        return IRTTaxBracket::calculateIRT($irtBase);
    }
    
    /**
     * Obter detalhes do cálculo de IRT
     */
    public function getIRTCalculationDetails(): array
    {
        \Log::info('📊 getIRTCalculationDetails() INICIO');
        
        $mc = $this->calculateIRTBase();
        \Log::info('📊 MC calculado', ['mc' => $mc]);
        
        $bracket = IRTTaxBracket::getBracketForIncome($mc);
        \Log::info('📊 Bracket obtido', ['bracket' => $bracket ? $bracket->bracket_number : 'NULL']);
        
        if (!$bracket || $mc <= 0) {
            \Log::info('📊 Retornando isento (bracket null ou mc <= 0)');
            return [
                'mc' => $mc,
                'bracket' => null,
                'bracket_number' => 1,
                'excess' => 0,
                'fixed_amount' => 0,
                'tax_on_excess' => 0,
                'total_irt' => 0,
                'description' => 'Isento - Escalão 1'
            ];
        }
        
        $excess = max(0, $mc - $bracket->min);
        $taxOnExcess = $excess * ($bracket->tax_rate / 100);
        $totalIRT = (float) $bracket->fixed_amount + $taxOnExcess;
        \Log::info('📊 Valores calculados', ['excess' => $excess, 'taxOnExcess' => $taxOnExcess, 'totalIRT' => $totalIRT]);
        
        \Log::info('📊 Chamando getIRTBracketDescription()...');
        $description = $this->getIRTBracketDescription($bracket, $mc, $totalIRT);
        \Log::info('📊 Description obtida', ['description' => $description]);
        
        return [
            'mc' => $mc,
            'bracket' => $bracket,
            'bracket_number' => $bracket->bracket_number,
            'excess' => $excess,
            'fixed_amount' => (float) $bracket->fixed_amount,
            'tax_on_excess' => $taxOnExcess,
            'total_irt' => $totalIRT,
            'description' => $description
        ];
    }
    
    /**
     * Obter descrição do escalão de IRT
     */
    protected function getIRTBracketDescription($bracket, $mc, $totalIRT): string
    {
        if ($bracket->bracket_number == 1) {
            return "Escalão 1 - Isento";
        }
        
        // Calcular valores diretamente sem chamar getIRTCalculationDetails() (evita loop)
        $fixedAmount = (float) $bracket->fixed_amount;
        $excess = max(0, $mc - $bracket->min);
        $taxOnExcess = $excess * ($bracket->tax_rate / 100);
        
        if ($bracket->bracket_number == 2) {
            return "Escalão 2 - {$bracket->tax_rate}% | Total: " . number_format($totalIRT, 0) . " AOA";
        } else {
            return "Escalões 1-{$bracket->bracket_number} | Fixo: " . number_format($fixedAmount, 0) . " + Atual: " . number_format($taxOnExcess, 0) . " = " . number_format($totalIRT, 0) . " AOA";
        }
    }
    
    /**
     * Calcular total de deduções PARA CÁLCULO INTERNO
     * 
     * IMPORTANTE: Ausências NÃO entram aqui (já foram deduzidas no Main Salary)
     * Food NÃO entra aqui (deduzido separadamente no Net Salary)
     * Apenas: INSS + IRT + Advances + Discounts + Late
     */
    public function calculateTotalDeductions(): float
    {
        $inss = $this->calculateINSS();
        $irt = $this->calculateIRT();
        $advances = $this->advanceDeduction;
        $discounts = $this->totalSalaryDiscounts;
        $late = $this->lateDeduction;
        
        // ❌ NÃO incluir $absence aqui (já deduzido no Main Salary)
        // ❌ NÃO incluir $food aqui (deduzido separadamente no Net Salary)
        
        return $inss + $irt + $advances + $discounts + $late;
    }
    
    /**
     * Calcular total de deduções PARA EXIBIÇÃO - MATCH COM MODAL INDIVIDUAL
     * 
     * Inclui FOOD para mostrar o valor total deduzido na tela
     * Este é o valor que aparece como "Total Deductions" nas modals
     */
    public function calculateTotalDeductionsForDisplay(): float
    {
        $inss = $this->calculateINSS();
        $irt = $this->calculateIRT();
        $advances = $this->advanceDeduction;
        $discounts = $this->totalSalaryDiscounts;
        $late = $this->lateDeduction;
        
        // ✅ Incluir FOOD para exibição (igual modal individual)
        $food = $this->mealAllowance;
        
        return $inss + $irt + $advances + $discounts + $late + $food;
    }
    
    /**
     * Calcular salário líquido - MATCH COM MODAL INDIVIDUAL
     * 
     * FÓRMULA: Main Salary (já tem absence deduzida) - Total Deductions - Food
     * 
     * REGRA DE NEGÓCIO: Food benefit NUNCA é pago ao funcionário (apenas ilustrativo)
     */
    public function calculateNetSalary(): float
    {
        // Usar Main Salary que JÁ tem ausências deduzidas
        $mainSalary = $this->calculateMainSalary();
        $totalDeductions = $this->calculateTotalDeductions();
        
        // REGRA: Food SEMPRE é deduzido (não é pago ao funcionário)
        $foodDeduction = $this->mealAllowance;
        
        return max(0.0, $mainSalary - $totalDeductions - $foodDeduction);
    }
    
    /**
     * Calcular todos os componentes do payroll
     * Retorna array completo com todos os valores calculados
     */
    public function calculate(): array
    {
        \Log::info('🔢 Helper calculate() INICIO', [
            'christmas_subsidy' => $this->christmasSubsidy,
            'vacation_subsidy' => $this->vacationSubsidy,
        ]);
        
        // Calcular subsídio de transporte proporcional
        $this->calculateProportionalTransportAllowance();
        \Log::info('✅ Transport calculado');
        
        // Calcular todos os componentes
        $grossSalary = $this->calculateGrossSalary();
        \Log::info('✅ Gross Salary calculado', ['gross' => $grossSalary]);
        
        $mainSalary = $this->calculateMainSalary();
        \Log::info('✅ Main Salary calculado', ['main' => $mainSalary]);
        
        $irtBase = $this->calculateIRTBase();
        \Log::info('✅ IRT Base calculado', ['irtBase' => $irtBase]);
        
        $inss = $this->calculateINSS();
        \Log::info('✅ INSS calculado', ['inss' => $inss]);
        
        $inss8 = $this->calculateINSS8Percent();
        $irt = $this->calculateIRT();
        \Log::info('✅ IRT calculado', ['irt' => $irt]);
        
        try {
            $irtDetails = $this->getIRTCalculationDetails();
            \Log::info('✅ IRT Details obtido');
        } catch (\Exception $e) {
            \Log::error('❌ ERRO em getIRTCalculationDetails()', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            // Usar valores padrão
            $irtDetails = [
                'mc' => 0,
                'bracket' => null,
                'bracket_number' => 1,
                'excess' => 0,
                'fixed_amount' => 0,
                'tax_on_excess' => 0,
                'total_irt' => $irt,
                'description' => 'Erro ao calcular detalhes'
            ];
        }
        
        $totalDeductions = $this->calculateTotalDeductions(); // Para cálculo interno (sem food)
        $totalDeductionsDisplay = $this->calculateTotalDeductionsForDisplay(); // Para exibição (com food)
        \Log::info('✅ Total Deductions calculado', ['total' => $totalDeductions]);
        
        $netSalary = $this->calculateNetSalary();
        \Log::info('✅ Net Salary calculado', ['net' => $netSalary]);
        
        $this->calculationResults = [
            // Dados básicos
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->full_name,
            'period_start' => $this->startDate->format('Y-m-d'),
            'period_end' => $this->endDate->format('Y-m-d'),
            
            // Salário base
            'basic_salary' => $this->basicSalary,
            'hourly_rate' => $this->hourlyRate,
            'daily_rate' => $this->hourlyRate * 8,
            'monthly_working_days' => $this->hrSettings['monthly_working_days'] ?? 22,
            'attendance_hours' => $this->totalAttendanceHours,
            
            // Presença
            'total_working_days' => $this->totalWorkingDays,
            'present_days' => $this->presentDays, // Inclui férias (para salário)
            'days_worked_effectively' => $this->daysWorkedEffectively, // Exclui férias (para subsídios)
            'absent_days' => $this->absentDays,
            'late_arrivals' => $this->lateArrivals,
            'total_attendance_hours' => $this->totalAttendanceHours,
            'attendance_data' => $this->attendanceData,
            
            // Horas extras
            'total_overtime_hours' => $this->totalOvertimeHours,
            'total_overtime_amount' => $this->totalOvertimeAmount,
            'overtime_records' => $this->overtimeRecords,
            
            // Benefícios
            'food_benefit' => $this->mealAllowance,
            'transport_benefit_full' => $this->getFullTransportBenefit(),
            'transport_allowance' => $this->transportAllowance,
            'transport_discount' => $this->getTransportDiscountAmount(),
            'taxable_transport' => $this->getTaxableTransportAllowance(),
            'exempt_transport' => $this->getExemptTransportAllowance(),
            'taxable_food' => $this->getTaxableFoodAllowance(),
            'exempt_food' => $this->getExemptFoodAllowance(),
            
            // Bônus e subsídios
            'bonus_amount' => $this->bonusAmount,
            'position_subsidy' => (float) ($this->employee->position_subsidy ?? 0),
            'performance_subsidy' => (float) ($this->employee->performance_subsidy ?? 0),
            'additional_bonus_amount' => $this->additionalBonusAmount,
            'christmas_subsidy' => $this->christmasSubsidy,
            'christmas_subsidy_amount' => $this->getChristmasSubsidyAmount(),
            'vacation_subsidy' => $this->vacationSubsidy,
            'vacation_subsidy_amount' => $this->getVacationSubsidyAmount(),
            
            // Adiantamentos e descontos
            'total_salary_advances' => $this->totalSalaryAdvances,
            'advance_deduction' => $this->advanceDeduction,
            'salary_advances' => $this->salaryAdvances,
            'total_salary_discounts' => $this->totalSalaryDiscounts,
            'salary_discounts' => $this->salaryDiscounts,
            
            // Licenças
            'total_leave_days' => $this->totalLeaveDays,
            'unpaid_leave_days' => $this->unpaidLeaveDays,
            'leave_deduction' => $this->leaveDeduction,
            'leave_records' => $this->leaveRecords,
            
            // Deduções por presença
            'late_deduction' => $this->lateDeduction,
            'absence_deduction' => $this->absenceDeduction,
            
            // Cálculos de salário
            'gross_salary' => $grossSalary,
            'main_salary' => $mainSalary,
            'irt_base' => $irtBase,
            'base_irt_taxable_amount' => $irtBase, // Alias para compatibilidade
            
            // Impostos e contribuições
            'inss_3_percent' => $inss,
            'inss_8_percent' => $inss8,
            'irt' => $irt,
            'deductions_irt' => $irt, // Alias para compatibilidade
            'irt_details' => $irtDetails,
            
            // Cálculos intermediários (para modal individual)
            'inss_base' => $this->calculateINSSBase(), // Base para cálculo do INSS
            'gross_for_tax' => $irtBase, // Gross salary for tax calculation
            'calculated_inss' => $inss, // Alias
            'calculated_irt' => $irt, // Alias
            'income_tax' => $irt, // Alias
            'total_deductions_calculated' => $totalDeductionsDisplay, // Para compatibilidade com modal individual
            'calculated_net_salary' => $netSalary, // Alias
            
            // Totais
            'total_deductions' => $totalDeductionsDisplay, // ✅ Valor para exibição (com food)
            'total_deductions_internal' => $totalDeductions, // Valor para cálculo interno (sem food)
            'net_salary' => $netSalary,
            'absence_deduction_amount' => $this->absenceDeduction, // Alias para compatibilidade
            'late_days' => $this->lateArrivals, // Alias para compatibilidade
            'profile_bonus' => $this->bonusAmount, // Alias para compatibilidade
            'overtime_amount' => $this->totalOvertimeAmount, // Alias para compatibilidade
            
            // Dedução de alimentação (SEMPRE deduzido - regra de negócio)
            'food_deduction' => $this->mealAllowance,
            
            // Configurações
            'is_food_in_kind' => $this->isFoodInKind,
            'hr_settings' => $this->hrSettings,
        ];
        
        \Log::info('✅✅ Helper calculate() COMPLETO - Retornando array', [
            'gross_salary' => $this->calculationResults['gross_salary'],
            'net_salary' => $this->calculationResults['net_salary'],
            'christmas_amount' => $this->calculationResults['christmas_subsidy_amount'],
            'vacation_amount' => $this->calculationResults['vacation_subsidy_amount'],
        ]);
        
        return $this->calculationResults;
    }
    
    /**
     * Obter resultados do cálculo
     */
    public function getResults(): array
    {
        if (empty($this->calculationResults)) {
            return $this->calculate();
        }
        
        return $this->calculationResults;
    }
    
    /**
     * Obter dados de presença
     */
    public function getAttendanceData(): array
    {
        return [
            'total_working_days' => $this->totalWorkingDays,
            'present_days' => $this->presentDays, // Inclui férias
            'days_worked_effectively' => $this->daysWorkedEffectively, // Exclui férias
            'absent_days' => $this->absentDays,
            'late_arrivals' => $this->lateArrivals,
            'total_attendance_hours' => $this->totalAttendanceHours,
            'attendance_records' => $this->attendanceData,
        ];
    }
    
    /**
     * Obter dados de horas extras
     */
    public function getOvertimeData(): array
    {
        return [
            'total_overtime_hours' => $this->totalOvertimeHours,
            'total_overtime_amount' => $this->totalOvertimeAmount,
            'overtime_records' => $this->overtimeRecords,
        ];
    }
    
    /**
     * Obter dados de adiantamentos
     */
    public function getAdvancesData(): array
    {
        return [
            'total_salary_advances' => $this->totalSalaryAdvances,
            'advance_deduction' => $this->advanceDeduction,
            'salary_advances' => $this->salaryAdvances,
        ];
    }
    
    /**
     * Obter dados de descontos
     */
    public function getDiscountsData(): array
    {
        return [
            'total_salary_discounts' => $this->totalSalaryDiscounts,
            'salary_discounts' => $this->salaryDiscounts,
        ];
    }
    
    /**
     * Log de debug dos cálculos
     */
    public function logCalculations(): void
    {
        Log::info('PayrollCalculatorHelper - Cálculos completos', $this->getResults());
    }
}

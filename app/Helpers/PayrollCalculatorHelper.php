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
use App\Models\HR\Shift;
use App\Models\HR\ShiftAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * =====================================================
 * PayrollCalculatorHelper - ESTRUTURA DE CÁLCULOS
 * =====================================================
 * 
 * Helper centralizado para cálculos de folha de pagamento (payroll)
 * Contém toda a lógica de cálculo extraída da modal ProcessPayrollModal e componente Livewire
 * 
 * Este helper garante que os cálculos sejam consistentes entre:
 * - Pagamento individual de salário
 * - Pagamento em lote (batch)
 * - Processamento de payroll
 * 
 * =====================================================
 * ESTRUTURA DE CÁLCULOS (3 Colunas da Especificação)
 * =====================================================
 * 
 * COLUNA 1: GROSS SALARY
 * ----------------------
 *   + Basic Salary
 *   + Transport
 *   + Food allowance
 *   + Nigth Allowance
 *   + Total Over Time
 *   + Natal Allowance
 *   + Leave Allowance
 *   + Bonus
 *   - Absence
 * 
 * COLUNA 2: IRT TAXABLE AMOUNT
 * -----------------------------
 *   = Gross Salary
 *   - Food Payment >30
 *   - Transport >30
 *   - INSS 3%
 * 
 * COLUNA 3: NET AMOUNT
 * --------------------
 *   = Gross Salary
 *   - INSS 3%
 *   - IRT
 *   - Advance
 *   - Fund union
 *   - other Payments
 *   - Food allowance
 * 
 * =====================================================
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
    protected float $familyAllowance = 0.0;
    protected float $additionalBonusAmount = 0.0;
    protected bool $christmasSubsidy = false;
    protected bool $vacationSubsidy = false;
    protected bool $isFoodInKind = false;
    
    // Subsídio Noturno (Lei Angola Art. 102º - 25% adicional)
    protected float $nightShiftAllowance = 0.0;
    protected int $nightShiftDays = 0;
    
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
        $this->familyAllowance = (float) ($employee->family_allowance ?? 0);
        
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
            
            // Subsídio Noturno (Lei Angola Art. 102º)
            // Trabalho noturno (20h-06h) = +25% sobre remuneração
            'night_shift_percentage' => (float) HRSetting::get('night_shift_percentage', 25),
        ];
    }
    
    /**
     * ===============================================
     * CÁLCULO DO SUBSÍDIO NOTURNO
     * ===============================================
     * Lei Geral do Trabalho de Angola (Lei nº 7/15)
     * Artigo 102º: Trabalho noturno (20h-06h) = +25%
     * 
     * Para trabalhadores em rotação com turnos noturnos,
     * calcula-se o adicional de 25% sobre o salário
     * proporcional aos dias trabalhados em turno noturno.
     */
    public function loadNightShiftData(): self
    {
        // Buscar atribuições de turno do funcionário no período
        $shiftAssignments = ShiftAssignment::where('employee_id', $this->employee->id)
            ->where(function($query) {
                $query->where('start_date', '<=', $this->endDate->format('Y-m-d'))
                      ->where(function($q) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $this->startDate->format('Y-m-d'));
                      });
            })
            ->with('shift')
            ->get();
        
        if ($shiftAssignments->isEmpty()) {
            $this->nightShiftDays = 0;
            $this->nightShiftAllowance = 0.0;
            return $this;
        }
        
        $nightDays = 0;
        $current = $this->startDate->copy();
        
        while ($current <= $this->endDate) {
            foreach ($shiftAssignments as $assignment) {
                // Verificar se a atribuição é válida para esta data
                if ($current < $assignment->start_date) continue;
                if ($assignment->end_date && $current > $assignment->end_date) continue;
                
                // Se tem rotação, verificar qual turno está ativo nesta data
                if ($assignment->hasRotation()) {
                    $activeShiftId = $assignment->getActiveShiftForDate($current);
                    $activeShift = Shift::find($activeShiftId);
                    
                    if ($activeShift && $activeShift->is_night_shift) {
                        $nightDays++;
                    }
                } else {
                    // Turno fixo
                    if ($assignment->shift && $assignment->shift->is_night_shift) {
                        $nightDays++;
                    }
                }
                break; // Um funcionário só pode ter uma atribuição ativa por dia
            }
            $current->addDay();
        }
        
        $this->nightShiftDays = $nightDays;
        
        // Calcular o subsídio noturno
        // Fórmula: (Salário Base / Dias do Mês) × Dias Noturnos × 25%
        $workingDays = $this->hrSettings['monthly_working_days'] ?? 22;
        $nightPercentage = ($this->hrSettings['night_shift_percentage'] ?? 25) / 100;
        
        if ($workingDays > 0 && $nightDays > 0) {
            $dailySalary = $this->basicSalary / $workingDays;
            $this->nightShiftAllowance = $dailySalary * $nightDays * $nightPercentage;
        } else {
            $this->nightShiftAllowance = 0.0;
        }
        
        Log::info('🌙 Night Shift Calculation', [
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->full_name,
            'period' => $this->startDate->format('Y-m-d') . ' to ' . $this->endDate->format('Y-m-d'),
            'night_days' => $this->nightShiftDays,
            'basic_salary' => $this->basicSalary,
            'night_percentage' => $nightPercentage * 100 . '%',
            'night_allowance' => $this->nightShiftAllowance,
        ]);
        
        return $this;
    }
    
    /**
     * Obter o valor do subsídio noturno calculado
     */
    public function getNightShiftAllowance(): float
    {
        return $this->nightShiftAllowance;
    }
    
    /**
     * Obter o número de dias trabalhados em turno noturno
     */
    public function getNightShiftDays(): int
    {
        return $this->nightShiftDays;
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
        
        // Calcular faltas: se trabalhou mais que o esperado, não há faltas
        $this->absentDays = max(0, $this->totalWorkingDays - $this->presentDays);
        $this->lateArrivals = $attendances->where('status', 'late')->count();
        
        \Log::info('📊 Cálculo de Presença', [
            'employee' => $this->employee->full_name,
            'total_working_days' => $this->totalWorkingDays,
            'present_days' => $this->presentDays,
            'absent_days' => $this->absentDays,
            'days_worked_effectively' => $this->daysWorkedEffectively,
        ]);
        
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
        $this->loadNightShiftData(); // Calcular subsídio noturno (Lei Angola Art. 102º)
        
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
     * ==========================================
     * GROSS SALARY (Coluna 1 da Especificação)
     * ==========================================
     * Fórmula:
     *   + Basic Salary
     *   + Transport
     *   + Food allowance
     *   + Nigth Allowance
     *   + Total Over Time
     *   + Natal Allowance
     *   + Leave Allowance
     *   + Bonus
     *   - Absence
     */
    public function calculateGrossSalary(): float
    {
        // ADIÇÕES (+)
        $basicSalary      = $this->basicSalary;                              // Basic Salary
        $transport        = $this->transportAllowance;                       // Transport
        $foodAllowance    = $this->mealAllowance;                           // Food allowance
        $nightAllowance   = $this->nightShiftAllowance;                     // Night Allowance (Lei Angola Art. 102º - 25%)
        $totalOverTime    = $this->totalOvertimeAmount;                     // Total Over Time
        $natalAllowance   = $this->getChristmasSubsidyAmount();            // Natal Allowance
        $leaveAllowance   = $this->getVacationSubsidyAmount();             // Leave Allowance
        $familyAllowance  = $this->familyAllowance;                         // Family Allowance (Ajuda Familiar)
        $positionSubsidy  = (float) ($this->employee->position_subsidy ?? 0);   // Position Subsidy
        $performanceSubsidy = (float) ($this->employee->performance_subsidy ?? 0); // Performance Subsidy
        $additionalBonus  = $this->additionalBonusAmount;                   // Additional Bonus (batch)
        
        // DEDUÇÕES (-)
        $absence          = $this->absenceDeduction;                        // Absence
        
        $gross = $basicSalary 
             + $transport 
             + $foodAllowance 
             + $nightAllowance
             + $totalOverTime 
             + $natalAllowance 
             + $leaveAllowance 
             + $familyAllowance 
             + $positionSubsidy
             + $performanceSubsidy
             + $additionalBonus
             - $absence;
             
        \Log::info('📊 Gross Salary Calculation', [
            'basicSalary' => $basicSalary,
            'transport' => $transport,
            'foodAllowance' => $foodAllowance,
            'nightAllowance' => $nightAllowance,
            'night_shift_days' => $this->nightShiftDays,
            'totalOverTime' => $totalOverTime,
            'familyAllowance' => $familyAllowance,
            'additionalBonus' => $additionalBonus,
            'absence_deduction' => $absence,
            'gross_before_absence' => $basicSalary + $transport + $foodAllowance + $nightAllowance + $totalOverTime + $familyAllowance + $additionalBonus,
            'gross_final' => $gross,
        ]);
        
        return $gross;
    }
    
    /**
     * ALIAS: Main Salary = Gross Salary (para compatibilidade com código existente)
     * 
     * NOTA: Mantido apenas para não quebrar código que usa calculateMainSalary()
     *       A especificação usa apenas GROSS SALARY
     */
    public function calculateMainSalary(): float
    {
        return $this->calculateGrossSalary();
    }
    
    /**
     * ===============================================
     * IRT TAXABLE AMOUNT (Coluna 2 da Especificação)
     * ===============================================
     * FÓRMULA OFICIAL (NOVA LÓGICA):
     * 
     * 1. Base IRT ANTES do INSS = Gross Salary - Isenções (2 × 30.000)
     * 2. Base IRT APÓS o INSS = Base Antes INSS - INSS 3%
     * 
     * ISENÇÕES (até 30.000 AOA cada, baseado no VALOR REAL):
     * - Alimentação: isento = min(valor_real, 30.000)
     * - Transporte: isento = min(valor_real, 30.000)
     * 
     * Exemplo: Se Transport = 0, isenção = 0 (não 30k fixo)
     */
    public function calculateIRTBase(): float
    {
        // BASE
        $grossSalary = $this->calculateGrossSalary();                       // Gross Salary
        
        // ISENÇÕES (até 30.000 AOA cada, baseado no valor REAL)
        // Se Food = 50k, isento = min(50k, 30k) = 30k
        // Se Transport = 0, isento = min(0, 30k) = 0
        $foodExemption = min($this->mealAllowance, 30000);           // Isenção alimentação (até 30k)
        $transportExemption = min($this->transportAllowance, 30000); // Isenção transporte (até 30k)
        
        // Base IRT ANTES do INSS
        $baseBeforeINSS = $grossSalary - $foodExemption - $transportExemption;
        
        // INSS 3%
        $inss3Percent = $this->calculateINSS();
        
        // Base IRT APÓS o INSS (esta é a base tributável final)
        $irtBase = $baseBeforeINSS - $inss3Percent;
        
        return max(0.0, $irtBase);
    }
    
    /**
     * Calcular base do INSS
     * 
     * CONFORME ESPECIFICAÇÃO DA IMAGEM:
     * Base INSS = Gross Salary (JÁ COM Absence deduzida)
     * 
     * IMPORTANTE: Na especificação de Angola, a base do INSS é o Gross Salary
     * que já tem a ausência deduzida.
     */
    public function calculateINSSBase(): float
    {
        // Base INSS = Gross Salary (COM Absence já deduzida)
        return $this->calculateGrossSalary();
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
     * Calcular total de deduções (para cálculo interno do Net)
     * Food NÃO entra aqui (deduzido separadamente no Net Salary)
     * Inclui: INSS + IRT + Advances + Discounts
     * 
     * NOTA: Late e Absence JÁ foram deduzidos no Gross Salary, não entram aqui
     */
    public function calculateTotalDeductions(): float
    {
        $inss3Percent  = $this->calculateINSS();           // INSS 3%
        $irt           = $this->calculateIRT();            // IRT
        $advance       = $this->advanceDeduction;          // Advance
        $fundUnion     = 0.0;                              // Fund union (não implementado)
        $otherPayments = $this->totalSalaryDiscounts;     // other Payments
        
        return $inss3Percent + $irt + $advance + $fundUnion + $otherPayments;
    }
    
    /**
     * Calcular total de deduções PARA EXIBIÇÃO - MATCH COM MODAL INDIVIDUAL
     * 
     * Inclui FOOD para mostrar o valor total deduzido na tela
     * Este é o valor que aparece como "Total Deductions" nas modals
     * 
     * IMPORTANTE: Absence foi deduzida do Gross, MAS precisa aparecer na lista visual
     */
    public function calculateTotalDeductionsForDisplay(): float
    {
        $inss = $this->calculateINSS();
        $irt = $this->calculateIRT();
        $advances = $this->advanceDeduction;
        $discounts = $this->totalSalaryDiscounts;
        $absence = $this->absenceDeduction;  // Mostrar na lista mesmo tendo sido deduzida do Gross
        
        // ✅ Incluir FOOD para exibição (igual modal individual)
        $food = $this->mealAllowance;
        
        return $inss + $irt + $advances + $discounts + $absence + $food;
    }
    
    /**
     * ==========================================
     * NET AMOUNT (Coluna 3 da Especificação)
     * ==========================================
     * Fórmula:
     *   = Gross Salary (já tem absence/late descontado)
     *   - INSS 3%
     *   - IRT
     *   - Advance
     *   - Fund union
     *   - other Payments
     *   - Food allowance
     * 
     * NOTA: Absence e Late JÁ foram deduzidos no Gross Salary
     */
    public function calculateNetSalary(): float
    {
        // BASE
        $grossSalary = $this->calculateGrossSalary();                       // Gross Salary (já inclui deduções de presença)
        
        // DEDUÇÕES (-)
        $inss3Percent    = $this->calculateINSS();                          // INSS 3%
        $irt             = $this->calculateIRT();                           // IRT
        $advance         = $this->advanceDeduction;                         // Advance
        $fundUnion       = 0.0;                                             // Fund union (não implementado)
        $otherPayments   = $this->totalSalaryDiscounts;                    // other Payments (salary discounts)
        
        // IMPORTANTE: Food allowance está no Gross (para cálculo de impostos)
        // MAS não é pago em dinheiro ao funcionário, então deve ser DEDUZIDO do NET
        $foodAllowance   = $this->mealAllowance;                           // Food allowance (não pago em dinheiro)
        
        $net = max(0.0, $grossSalary 
                      - $inss3Percent 
                      - $irt 
                      - $advance 
                      - $fundUnion
                      - $otherPayments
                      - $foodAllowance);
        
        \Log::info('💰 Net Salary Calculation', [
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->full_name,
            'grossSalary' => $grossSalary,
            'inss3Percent' => $inss3Percent,
            'irt' => $irt,
            'advance' => $advance,
            'otherPayments' => $otherPayments,
            'foodAllowance' => $foodAllowance,
            'absence_deduction' => $this->absenceDeduction,
            'late_deduction' => $this->lateDeduction,
            'totalDeductions' => $inss3Percent + $irt + $advance + $otherPayments + $foodAllowance,
            'net_salary' => $net,
            'FORMULA' => "Gross({$grossSalary}) - INSS({$inss3Percent}) - IRT({$irt}) - Advance({$advance}) - Discounts({$otherPayments}) - Food({$foodAllowance}) = {$net}",
        ]);
        
        return $net;
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
        
        // Dias úteis do mês (para fórmulas visuais)
        $workingDays = $this->hrSettings['monthly_working_days'] ?? 22;
        $dailyRate = $this->hourlyRate * 8;
        
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
            'family_allowance' => $this->familyAllowance,
            'position_subsidy' => (float) ($this->employee->position_subsidy ?? 0),
            'performance_subsidy' => (float) ($this->employee->performance_subsidy ?? 0),
            'additional_bonus_amount' => $this->additionalBonusAmount,
            'christmas_subsidy' => $this->christmasSubsidy,
            'christmas_subsidy_amount' => $this->getChristmasSubsidyAmount(),
            'vacation_subsidy' => $this->vacationSubsidy,
            'vacation_subsidy_amount' => $this->getVacationSubsidyAmount(),
            
            // Subsídio Noturno (Lei Angola Art. 102º - 25%)
            'night_shift_days' => $this->nightShiftDays,
            'night_shift_allowance' => $this->nightShiftAllowance,
            
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
            
            // ========================================
            // COMPOSIÇÃO DO SALÁRIO BRUTO (EARNINGS)
            // ========================================
            'earnings_breakdown' => [
                ['label' => 'Salário Base', 'value' => $this->basicSalary, 'type' => 'add'],
                ['label' => 'Subsídio de Transporte', 'value' => $this->transportAllowance, 'type' => 'add'],
                ['label' => 'Subsídio de Alimentação', 'value' => $this->mealAllowance, 'type' => 'add'],
                ['label' => 'Subsídio Noturno (25%)', 'value' => $this->nightShiftAllowance, 'type' => 'add', 'days' => $this->nightShiftDays],
                ['label' => 'Horas Extras', 'value' => $this->totalOvertimeAmount, 'type' => 'add', 'hours' => $this->totalOvertimeHours],
                ['label' => 'Subsídio de Natal (50%)', 'value' => $this->getChristmasSubsidyAmount(), 'type' => 'add'],
                ['label' => 'Subsídio de Férias (50%)', 'value' => $this->getVacationSubsidyAmount(), 'type' => 'add'],
                ['label' => 'Abono de Família', 'value' => $this->familyAllowance, 'type' => 'add'],
                ['label' => 'Subsídio de Cargo', 'value' => (float) ($this->employee->position_subsidy ?? 0), 'type' => 'add'],
                ['label' => 'Subsídio de Desempenho', 'value' => (float) ($this->employee->performance_subsidy ?? 0), 'type' => 'add'],
                ['label' => 'Bónus Adicional', 'value' => $this->additionalBonusAmount, 'type' => 'add'],
                ['label' => 'Desconto por Faltas', 'value' => $this->absenceDeduction, 'type' => 'subtract', 'days' => $this->absentDays],
            ],
            'total_earnings' => $this->basicSalary + $this->transportAllowance + $this->mealAllowance + 
                               $this->nightShiftAllowance + $this->totalOvertimeAmount + 
                               $this->getChristmasSubsidyAmount() + $this->getVacationSubsidyAmount() +
                               $this->familyAllowance + (float) ($this->employee->position_subsidy ?? 0) +
                               (float) ($this->employee->performance_subsidy ?? 0) + $this->additionalBonusAmount,
            
            // ========================================
            // COMPOSIÇÃO DAS DEDUÇÕES (DEDUCTIONS)
            // ========================================
            'deductions_breakdown' => [
                ['label' => 'INSS (3%)', 'value' => $inss, 'type' => 'tax', 'rate' => '3%'],
                ['label' => 'IRT', 'value' => $irt, 'type' => 'tax', 'bracket' => $irtDetails['bracket_number'] ?? 1],
                ['label' => 'Adiantamentos', 'value' => $this->advanceDeduction, 'type' => 'advance'],
                ['label' => 'Descontos Salariais', 'value' => $this->totalSalaryDiscounts, 'type' => 'discount'],
                ['label' => 'Desconto por Atrasos', 'value' => $this->lateDeduction, 'type' => 'attendance', 'count' => $this->lateArrivals],
                ['label' => 'Subsídio Alimentação (em espécie)', 'value' => $this->mealAllowance, 'type' => 'food_deduction'],
            ],
            'total_deductions_sum' => $inss + $irt + $this->advanceDeduction + $this->totalSalaryDiscounts + $this->lateDeduction + $this->mealAllowance,
            
            // ========================================
            // RESUMO VISUAL (para exibição)
            // ========================================
            'salary_composition' => [
                'gross_formula' => 'Base + Transporte + Alimentação + Noturno + HorasExtras + Natal + Férias + Família + Cargo + Desempenho + Bónus - Faltas',
                'net_formula' => 'Bruto - INSS - IRT - Adiantamentos - Descontos - Atrasos - Alimentação',
                'night_shift_formula' => "(Salário Base ÷ {$workingDays} dias) × {$this->nightShiftDays} dias × 25%",
                'night_shift_daily_rate' => $this->hourlyRate * 8, // Valor diário correto
                'night_shift_calculation' => [
                    'daily_rate' => round($this->hourlyRate * 8, 2), // Valor diário base
                    'days' => $this->nightShiftDays,
                    'percentage' => 25,
                    'result' => $this->nightShiftAllowance,
                ],
            ],
            
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
            'profile_bonus' => $this->familyAllowance, // Alias para compatibilidade (antigo bonus_amount)
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

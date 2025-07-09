<?php

namespace App\Livewire\HR;

use App\Models\HR\Payroll as PayrollModel;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\PayrollItem;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Payroll extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'payment_date';
    public $sortDirection = 'desc';
    public $filters = [
        'department_id' => '',
        'payroll_period_id' => '',
        'status' => '',
    ];

    // Form properties
    public $payroll_id;
    public $employee_id;
    public $payroll_period_id;
    public $basic_salary;
    public $allowances;
    public $overtime;
    public $bonuses;
    public $deductions;
    public $tax;
    public $social_security;
    public $net_salary;
    public $payment_method = 'bank_transfer';
    public $bank_account;
    public $payment_date;
    public $status = 'draft';
    public $remarks;
    
    // Propriedades para integração com presença e licenças
    public $attendance_hours = 0; // Horas de presença
    public $base_hourly_rate = 0; // Taxa horária base
    public $total_hours_pay = 0; // Pagamento calculado com base nas horas
    public $leave_days = 0; // Dias de licença regular
    public $leave_deduction = 0; // Dedução calculada com base nas licenças
    public $maternity_days = 0; // Dias de licença maternidade
    public $special_leave_days = 0; // Dias de licença especial
    public $employee = null; // Para armazenar dados completos do funcionário
    
    // Payroll items
    public $payrollItems = [];

    // Modal flags
    public $showModal = false;
    public $showDeleteModal = false;
    public $showApproveModal = false;
    public $showPayModal = false;
    public $showGenerateModal = false;
    public $showViewModal = false;
    public $isEditing = false;
    
    // Current payroll for view modal
    public $currentPayroll = null;

    // Listeners
    protected $listeners = ['refreshPayrolls' => '$refresh'];

    // Rules
    protected function rules()
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'payroll_period_id' => 'required|exists:payroll_periods,id',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'required|numeric|min:0',
            'overtime' => 'required|numeric|min:0',
            'bonuses' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'social_security' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'payment_method' => 'required|in:bank_transfer,cash,check',
            'bank_account' => 'nullable|required_if:payment_method,bank_transfer',
            'payment_date' => 'nullable|date',
            'status' => 'required|in:draft,approved,paid,cancelled',
            'remarks' => 'nullable',
            // Novos campos de presença e licença
            'attendance_hours' => 'nullable|numeric|min:0',
            'base_hourly_rate' => 'nullable|numeric|min:0',
            'leave_days' => 'nullable|numeric|min:0',
            'maternity_days' => 'nullable|numeric|min:0',
            'special_leave_days' => 'nullable|numeric|min:0',
        ];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function updatedEmployeeId()
    {
        if ($this->employee_id) {
            $employee = Employee::find($this->employee_id);
            $this->employee = $employee; // Guardar objeto completo do funcionário
            
            if ($employee) {
                // Verificar se este funcionário já tem uma folha de pagamento com salário customizado
                $lastPayroll = PayrollModel::where('employee_id', $this->employee_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastPayroll) {
                    // Usar o último salário registrado para continuidade
                    $this->basic_salary = $lastPayroll->basic_salary;
                    
                    // Recuperar valores de presença e licença se disponíveis
                    $this->attendance_hours = $lastPayroll->attendance_hours ?? 0;
                    $this->leave_days = $lastPayroll->leave_days ?? 0;
                    $this->maternity_days = $lastPayroll->maternity_days ?? 0;
                    $this->special_leave_days = $lastPayroll->special_leave_days ?? 0;
                } else {
                    // Se existe posição, obter o salário padrão da posição
                    if ($employee->position) {
                        // Usar o ponto médio da faixa salarial como padrão em vez do mínimo
                        $minSalary = $employee->position->salary_range_min;
                        $maxSalary = $employee->position->salary_range_max;
                        
                        // Se o salário máximo está definido, usar o ponto médio, caso contrário usar mínimo
                        if ($maxSalary > $minSalary) {
                            $this->basic_salary = ($minSalary + $maxSalary) / 2;
                        } else {
                            $this->basic_salary = $minSalary;
                        }
                    } else {
                        // Valor padrão zero se não há posição
                        $this->basic_salary = 0;
                    }
                    
                    // Calcular taxa horária base com base no salário mensal (considera 22 dias e 8 horas por dia)
                    if ($this->basic_salary > 0) {
                        $this->base_hourly_rate = round($this->basic_salary / (8 * 22), 2);
                    }
                }

                // Definir conta bancária se disponível
                if ($employee->bank_account) {
                    $this->bank_account = $employee->bank_account;
                }

                // Calcular os valores com base nos novos dados
                $this->calculateAttendanceAndLeavePay();
                $this->calculatePayroll();
            }
        } else {
            // Redefinir valores se nenhum funcionário for selecionado
            $this->reset([
                'basic_salary',
                'bank_account',
                'tax',
                'social_security',
                'net_salary',
                'attendance_hours',
                'base_hourly_rate',
                'total_hours_pay',
                'leave_days',
                'leave_deduction',
                'maternity_days',
                'special_leave_days',
                'employee'
            ]);
        }
    }

    public function updatedBasicSalary()
    {
        $this->calculatePayroll();
    }

    public function updatedAllowances()
    {
        $this->calculatePayroll();
    }

    public function updatedOvertime()
    {
        $this->calculatePayroll();
    }

    public function updatedBonuses()
    {
        $this->calculatePayroll();
    }

    public function updatedDeductions()
    {
        $this->calculatePayroll();
    }

    /**
     * Calcula pagamentos baseados em horas de presença e dias de licença
     */
    public function calculateAttendanceAndLeavePay()
    {
        // Calcular o pagamento com base nas horas trabalhadas
        if ($this->attendance_hours > 0 && $this->base_hourly_rate > 0) {
            $this->total_hours_pay = round($this->attendance_hours * $this->base_hourly_rate, 2);
        } else {
            $this->total_hours_pay = 0;
        }
        
        // Calcular deduções baseadas em dias de licença
        // Considerar 22 dias úteis por mês como base para cálculo
        $workingDaysInMonth = 22;
        
        if ($this->leave_days > 0 && $this->basic_salary > 0) {
            // Cálculo da dedução por dias de licença (excluindo maternidade e licença especial)
            // Note que licença maternidade não gera dedução, conforme a legislação angolana
            $dailyRate = $this->basic_salary / $workingDaysInMonth;
            $this->leave_deduction = round($dailyRate * $this->leave_days, 2);
        } else {
            $this->leave_deduction = 0;
        }
    }
    
    /**
     * Método para atualizar cálculos quando há alteração nas horas de presença
     */
    public function updatedAttendanceHours()
    {
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    /**
     * Método para atualizar cálculos quando há alteração na taxa horária
     */
    public function updatedBaseHourlyRate()
    {
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    /**
     * Método para atualizar cálculos quando há alteração nos dias de licença
     */
    public function updatedLeaveDays()
    {
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    /**
     * Método para atualizar cálculos quando há alteração nos dias de maternidade
     */
    public function updatedMaternityDays()
    {
        // Apenas mulheres podem ter dias de maternidade
        if ($this->employee && $this->employee->gender !== 'female') {
            $this->maternity_days = 0;
            session()->flash('error', 'Licença maternidade só pode ser atribuída a funcionárias mulheres.');
        }
        
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    /**
     * Método para atualizar cálculos quando há alteração nos dias de licença especial
     */
    public function updatedSpecialLeaveDays()
    {
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
    }
    
    public function calculatePayroll()
    {
        // Calcular o total de ganhos
        // Se tiver horas de presença registradas, usar o total_hours_pay em vez do salário base
        $baseEarnings = $this->attendance_hours > 0 ? $this->total_hours_pay : $this->basic_salary;
        
        // Subtrair deduções por licenças não-especiais
        $baseEarnings = $baseEarnings - $this->leave_deduction;
        
        // Garantir que o salário base não seja negativo
        if ($baseEarnings < 0) {
            $baseEarnings = 0;
        }
        
        // Calcular o total de ganhos incluindo adicionais
        $totalEarnings = $baseEarnings + $this->allowances + $this->overtime + $this->bonuses;
        
        // Calcular imposto (15% flat rate, ajuste conforme necessário)
        $this->tax = $totalEarnings * 0.15;
        
        // Calcular seguro social (4% flat rate, ajuste conforme necessário)
        $this->social_security = $totalEarnings * 0.04;
        
        // Calcular salário líquido
        $totalDeductions = $this->tax + $this->social_security + $this->deductions;
        $this->net_salary = $totalEarnings - $totalDeductions;
        
        // Garantir que o salário líquido não seja negativo
        if ($this->net_salary < 0) {
            $this->net_salary = 0;
        }
    }

    private function calculateIncomeTax()
    {
        // Calculate taxable income (after INSS deduction)
        $taxableBase = $this->basic_salary + $this->allowances;
        $inss = $taxableBase * 0.03;
        $taxableIncome = $taxableBase - $inss;
        
        // Updated IRT calculation based on current Angolan tax brackets
        if ($taxableIncome <= 100000) {
            return 0; // Exempt
        } elseif ($taxableIncome <= 110000) {
            return 870.87;
        } elseif ($taxableIncome <= 120000) {
            return 2131.87;
        } elseif ($taxableIncome <= 150000) {
            return 5914.87;
        } elseif ($taxableIncome <= 175000) {
            return 15659.84;
        } elseif ($taxableIncome <= 200000) {
            return 19539.84;
        } elseif ($taxableIncome <= 250000) {
            return 38899.82;
        } elseif ($taxableIncome <= 350000) {
            return 56754.81;
        } else {
            return ($taxableIncome - 350000) * 0.19 + 56754.81;
        }
    }

    public function create()
    {
        $this->reset([
            'payroll_id', 'employee_id', 'payroll_period_id', 'basic_salary',
            'allowances', 'overtime', 'bonuses', 'deductions', 'tax',
            'social_security', 'net_salary', 'payment_method', 'bank_account',
            'payment_date', 'status', 'remarks', 'payrollItems',
            // Resetar campos de presença e licença
            'attendance_hours', 'base_hourly_rate', 'total_hours_pay',
            'leave_days', 'leave_deduction', 'maternity_days', 'special_leave_days',
            'employee'
        ]);
        
        // Inicialização dos campos financeiros
        $this->allowances = 0;
        $this->overtime = 0;
        $this->bonuses = 0;
        $this->deductions = 0;
        $this->tax = 0;
        $this->social_security = 0;
        $this->net_salary = 0;
        $this->payment_method = 'bank_transfer';
        $this->status = 'draft';
        
        // Inicialização dos campos de presença e licença
        $this->attendance_hours = 0;
        $this->base_hourly_rate = 0;
        $this->total_hours_pay = 0;
        $this->leave_days = 0;
        $this->leave_deduction = 0;
        $this->maternity_days = 0;
        $this->special_leave_days = 0;
        
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(PayrollModel $payroll)
    {
        // Carregar dados básicos da folha de pagamento
        $this->payroll_id = $payroll->id;
        $this->employee_id = $payroll->employee_id;
        $this->payroll_period_id = $payroll->payroll_period_id;
        $this->basic_salary = $payroll->basic_salary;
        $this->allowances = $payroll->allowances;
        $this->overtime = $payroll->overtime;
        $this->bonuses = $payroll->bonuses;
        $this->deductions = $payroll->deductions;
        $this->tax = $payroll->tax;
        $this->social_security = $payroll->social_security;
        $this->net_salary = $payroll->net_salary;
        $this->payment_method = $payroll->payment_method;
        $this->bank_account = $payroll->bank_account;
        $this->payment_date = $payroll->payment_date ? $payroll->payment_date->format('Y-m-d') : null;
        $this->status = $payroll->status;
        $this->remarks = $payroll->remarks;
        
        // Carregar campos de presença e licença
        $this->attendance_hours = $payroll->attendance_hours ?? 0;
        $this->base_hourly_rate = $payroll->base_hourly_rate ?? 0;
        $this->total_hours_pay = $payroll->total_hours_pay ?? 0;
        $this->leave_days = $payroll->leave_days ?? 0;
        $this->leave_deduction = $payroll->leave_deduction ?? 0;
        $this->maternity_days = $payroll->maternity_days ?? 0;
        $this->special_leave_days = $payroll->special_leave_days ?? 0;
        
        // Carregar dados completos do funcionário
        $this->employee = Employee::find($this->employee_id);

        // Carregar itens da folha
        $this->payrollItems = $payroll->payrollItems->toArray();

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function showApprove(PayrollModel $payroll)
    {
        $this->payroll_id = $payroll->id;
        $this->showApproveModal = true;
    }

    public function showPay(PayrollModel $payroll)
    {
        $this->payroll_id = $payroll->id;
        $this->payment_date = now()->format('Y-m-d');
        $this->showPayModal = true;
    }

    public function showGenerate()
    {
        $this->reset(['payroll_period_id']);
        $this->showGenerateModal = true;
    }

    public function confirmDelete(PayrollModel $payroll)
    {
        $this->payroll_id = $payroll->id;
        $this->showDeleteModal = true;
    }

    public function approve()
    {
        $payroll = PayrollModel::find($this->payroll_id);
        $payroll->status = 'approved';
        $payroll->approved_by = auth()->id();
        $payroll->save();

        $this->showApproveModal = false;
        session()->flash('message', 'Payroll approved successfully.');
    }

    public function pay()
    {
        $this->validate([
            'payment_date' => 'required|date',
        ]);

        $payroll = PayrollModel::find($this->payroll_id);
        $payroll->status = 'paid';
        $payroll->payment_date = $this->payment_date;
        $payroll->save();

        $this->showPayModal = false;
        session()->flash('message', 'Payroll marked as paid successfully.');
    }

    public function generatePayrolls()
    {
        $this->validate([
            'payroll_period_id' => 'required|exists:payroll_periods,id',
        ]);

        $period = PayrollPeriod::find($this->payroll_period_id);
        $employees = Employee::where('employment_status', 'active')->get();
        $count = 0;

        foreach ($employees as $employee) {
            // Skip if payroll already exists for this employee and period
            $exists = PayrollModel::where('employee_id', $employee->id)
                ->where('payroll_period_id', $this->payroll_period_id)
                ->exists();

            if (!$exists) {
                // Get basic salary from position or use latest payroll
                $lastPayroll = PayrollModel::where('employee_id', $employee->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastPayroll) {
                    $basicSalary = $lastPayroll->basic_salary;
                } else {
                    if ($employee->position) {
                        $minSalary = $employee->position->salary_range_min;
                        $maxSalary = $employee->position->salary_range_max;
                        
                        if ($maxSalary > $minSalary) {
                            $basicSalary = ($minSalary + $maxSalary) / 2;
                        } else {
                            $basicSalary = $minSalary;
                        }
                    } else {
                        $basicSalary = 0;
                    }
                }
                
                // Calculate INSS (3% of basic salary)
                $inssBase = $basicSalary;
                $socialSecurity = $inssBase * 0.03;
                
                // Calculate IRT (After INSS deduction)
                $taxableIncome = $basicSalary - $socialSecurity;
                
                // Updated IRT calculation
                if ($taxableIncome <= 100000) {
                    $tax = 0; // Exempt
                } elseif ($taxableIncome <= 110000) {
                    $tax = 870.87;
                } elseif ($taxableIncome <= 120000) {
                    $tax = 2131.87;
                } elseif ($taxableIncome <= 150000) {
                    $tax = 5914.87;
                } elseif ($taxableIncome <= 175000) {
                    $tax = 15659.84;
                } elseif ($taxableIncome <= 200000) {
                    $tax = 19539.84;
                } elseif ($taxableIncome <= 250000) {
                    $tax = 38899.82;
                } elseif ($taxableIncome <= 350000) {
                    $tax = 56754.81;
                } else {
                    $tax = ($taxableIncome - 350000) * 0.19 + 56754.81;
                }
                
                // Calcular salário líquido
                $netSalary = $basicSalary - ($tax + $socialSecurity);

                // Criar folha de pagamento com valores calculados
                PayrollModel::create([
                    'employee_id' => $employee->id,
                    'payroll_period_id' => $this->payroll_period_id,
                    'basic_salary' => $basicSalary,
                    'allowances' => 0,
                    'overtime' => 0,
                    'bonuses' => 0,
                    'deductions' => 0,
                    'tax' => $tax,
                    'social_security' => $socialSecurity,
                    'net_salary' => $netSalary,
                    'payment_method' => 'bank_transfer',
                    'bank_account' => $employee->bank_account,
                    'status' => 'draft',
                    'generated_by' => auth()->id(),
                ]);

                $count++;
            }
        }

        $this->showGenerateModal = false;
        session()->flash('message', "Geradas {$count} folhas de pagamento com sucesso.");
    }

    public function save()
    {
        $this->validate();
        
        // Verificar dias de maternidade para funcionários homens
        if ($this->employee && $this->employee->gender !== 'female' && $this->maternity_days > 0) {
            $this->addError('maternity_days', 'Licença maternidade só pode ser atribuída a funcionárias mulheres.');
            return;
        }
        
        // Atualizar cálculos finais antes de salvar
        $this->calculateAttendanceAndLeavePay();
        $this->calculatePayroll();
        
        $payrollData = [
            'employee_id' => $this->employee_id,
            'payroll_period_id' => $this->payroll_period_id,
            'basic_salary' => $this->basic_salary,
            'allowances' => $this->allowances,
            'overtime' => $this->overtime,
            'bonuses' => $this->bonuses,
            'deductions' => $this->deductions,
            'tax' => $this->tax,
            'social_security' => $this->social_security,
            'net_salary' => $this->net_salary,
            'payment_method' => $this->payment_method,
            'bank_account' => $this->bank_account,
            'payment_date' => $this->payment_date ? Carbon::parse($this->payment_date) : null,
            'status' => $this->status,
            'remarks' => $this->remarks,
            // Campos de presença e licença
            'attendance_hours' => $this->attendance_hours ?? 0,
            'base_hourly_rate' => $this->base_hourly_rate ?? 0,
            'total_hours_pay' => $this->total_hours_pay ?? 0,
            'leave_days' => $this->leave_days ?? 0,
            'leave_deduction' => $this->leave_deduction ?? 0,
            'maternity_days' => $this->maternity_days ?? 0,
            'special_leave_days' => $this->special_leave_days ?? 0
        ];
        
        if ($this->isEditing) {
            // Verificar se já existe um registo para este período
            $existing = PayrollModel::where('employee_id', $this->employee_id)
                ->where('payroll_period_id', $this->payroll_period_id)
                ->where('id', '!=', $this->payroll_id)
                ->exists();
                
            if ($existing) {
                session()->flash('error', 'Já existe um registo de folha de pagamento para este funcionário neste período.');
                return;
            }
            
            // Atualizar folha de pagamento existente
            $payroll = PayrollModel::find($this->payroll_id);
            $payroll->update($payrollData);
            session()->flash('message', 'Folha de pagamento atualizada com sucesso.');
        } else {
            // Verificar se já existe um registo para este período
            $exists = PayrollModel::where('employee_id', $this->employee_id)
                ->where('payroll_period_id', $this->payroll_period_id)
                ->exists();

            if ($exists) {
                session()->flash('error', 'Já existe um registo de folha de pagamento para este funcionário neste período.');
                return;
            }

            // Adicionar ID do utilizador que gerou a folha
            $payrollData['generated_by'] = auth()->id();
            
            // Criar nova folha de pagamento
            PayrollModel::create($payrollData);
            session()->flash('message', 'Folha de pagamento criada com sucesso.');
        }
        
        $this->closeModal();
        $this->reset([
            'attendance_hours',
            'base_hourly_rate',
            'total_hours_pay',
            'leave_days',
            'leave_deduction',
            'maternity_days',
            'special_leave_days',
            'employee'
        ]);
    }
    
    public function delete()
    {
        $payroll = PayrollModel::find($this->payroll_id);
        $payroll->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Folha de pagamento eliminada com sucesso.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->currentPayroll = null;
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    /**
     * View payroll details
     * 
     * @param int $payrollId
     * @return void
     */
    public function view($payrollId)
    {
        $this->payroll_id = $payrollId;
        $this->currentPayroll = PayrollModel::with(['employee', 'payrollPeriod', 'payrollItems', 'employee.department', 'employee.position'])
            ->findOrFail($payrollId);
        $this->showViewModal = true;
    }
    
    /**
     * Download employee payslip
     * 
     * @param int $payrollId
     * @return mixed
     */
    public function downloadPayslip($payrollId)
    {
        try {
            $payroll = PayrollModel::with([
                'employee', 
                'employee.department', 
                'employee.position', 
                'payrollPeriod',
                'payrollItems'
            ])->findOrFail($payrollId);
            
            // Obter dados da empresa dos settings do sistema
            $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
            $companyLogo = \App\Models\Setting::get('company_logo');
            $logoPath = $companyLogo ? public_path('storage/' . $companyLogo) : null;
            $hasLogo = $logoPath && file_exists($logoPath);
            
            // Preparar dados para o PDF
            $data = [
                'payroll' => $payroll,
                'companyName' => $companyName,
                'companyLogo' => $companyLogo,
                'companyAddress' => \App\Models\Setting::get('company_address', ''),
                'companyPhone' => \App\Models\Setting::get('company_phone', ''),
                'companyEmail' => \App\Models\Setting::get('company_email', ''),
                'hasLogo' => $hasLogo,
                'logoPath' => $logoPath,
                'date' => now()->format('d/m/Y H:i'),
                'title' => 'Contracheque - ' . $payroll->employee->full_name
            ];
            
            // Gerar PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('livewire.hr.payslip-pdf', $data);
            
            // Configura para UTF-8
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            
            // Nome do arquivo para download
            $fileName = 'contracheque_' . $payroll->employee->id . '_' . now()->format('Y-m-d') . '.pdf';
            
            // Retornar arquivo para download
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao gerar PDF: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Mark payroll as paid
     * 
     * @param int $payrollId
     * @return void
     */
    public function markAsPaid($payrollId)
    {
        $this->payroll_id = $payrollId;
        $this->payment_date = now()->format('Y-m-d');
        $this->showPayModal = true;
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = PayrollModel::query()
            ->with(['employee', 'payrollPeriod'])
            ->when($this->search, function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('full_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filters['department_id'], function ($query) {
                return $query->whereHas('employee', function ($query) {
                    $query->where('department_id', $this->filters['department_id']);
                });
            })
            ->when($this->filters['payroll_period_id'], function ($query) {
                return $query->where('payroll_period_id', $this->filters['payroll_period_id']);
            })
            ->when($this->filters['status'], function ($query) {
                return $query->where('status', $this->filters['status']);
            });

        $payrolls = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $employees = Employee::where('employment_status', 'active')->get();
        $payrollPeriods = PayrollPeriod::orderBy('start_date', 'desc')->get();
        $departments = Department::where('is_active', true)->get();

        return view('livewire.hr.payroll', [
            'payrolls' => $payrolls,
            'employees' => $employees,
            'payrollPeriods' => $payrollPeriods,
            'departments' => $departments,
        ]);
    }
}

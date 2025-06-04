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
            if ($employee) {
                // Check if this employee already has a payroll with a custom salary
                $lastPayroll = PayrollModel::where('employee_id', $this->employee_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastPayroll) {
                    // Use the last payroll's basic salary for continuity
                    $this->basic_salary = $lastPayroll->basic_salary;
                } else {
                    // If position exists, get the default salary from position
                    if ($employee->position) {
                        // Use the middle of the salary range as default instead of minimum
                        $minSalary = $employee->position->salary_range_min;
                        $maxSalary = $employee->position->salary_range_max;
                        
                        // If max salary is defined, use middle of range, otherwise use min
                        if ($maxSalary > $minSalary) {
                            $this->basic_salary = ($minSalary + $maxSalary) / 2;
                        } else {
                            $this->basic_salary = $minSalary;
                        }
                    } else {
                        $this->basic_salary = 0;
                    }
                }
                
                $this->bank_account = $employee->bank_account;
                
                // Initialize other fields with zero
                $this->allowances = 0;
                $this->overtime = 0;
                $this->bonuses = 0;
                $this->deductions = 0;
                
                $this->calculatePayroll();
            }
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

    private function calculatePayroll()
    {
        // Calculate gross salary (base + allowances + overtime + bonuses)
        $grossSalary = $this->basic_salary + $this->allowances + $this->overtime + $this->bonuses;
        
        // Calculate social security (INSS) - 3% of basic salary and allowances
        // Based on Angolan regulations 
        $inssBase = $this->basic_salary + $this->allowances;
        $this->social_security = $inssBase * 0.03;
        
        // Calculate tax (IRT) - Angolan Income Tax
        $this->tax = $this->calculateIncomeTax();
        
        // Calculate net salary
        $totalDeductions = $this->deductions + $this->tax + $this->social_security;
        $this->net_salary = $grossSalary - $totalDeductions;
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
            'payment_date', 'status', 'remarks', 'payrollItems'
        ]);
        $this->allowances = 0;
        $this->overtime = 0;
        $this->bonuses = 0;
        $this->deductions = 0;
        $this->tax = 0;
        $this->social_security = 0;
        $this->net_salary = 0;
        $this->payment_method = 'bank_transfer';
        $this->status = 'draft';
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(PayrollModel $payroll)
    {
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

        // Load payroll items
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
                
                // Calculate net salary
                $netSalary = $basicSalary - ($tax + $socialSecurity);

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
        session()->flash('message', "Generated {$count} payroll records successfully.");
    }

    public function save()
    {
        $validatedData = $this->validate();

        if ($this->isEditing) {
            $payroll = PayrollModel::find($this->payroll_id);
            $payroll->update($validatedData);
            session()->flash('message', 'Payroll updated successfully.');
        } else {
            // Check if there's already a payroll record for this employee and period
            $exists = PayrollModel::where('employee_id', $this->employee_id)
                ->where('payroll_period_id', $this->payroll_period_id)
                ->exists();

            if ($exists) {
                session()->flash('error', 'A payroll record already exists for this employee in this period.');
                return;
            }

            $validatedData['generated_by'] = auth()->id();
            PayrollModel::create($validatedData);
            session()->flash('message', 'Payroll created successfully.');
        }

        $this->showModal = false;
        $this->reset([
            'payroll_id', 'employee_id', 'payroll_period_id', 'basic_salary',
            'allowances', 'overtime', 'bonuses', 'deductions', 'tax',
            'social_security', 'net_salary', 'payment_method', 'bank_account',
            'payment_date', 'status', 'remarks', 'payrollItems'
        ]);
    }

    public function delete()
    {
        $payroll = PayrollModel::find($this->payroll_id);
        $payroll->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Payroll record deleted successfully.');
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

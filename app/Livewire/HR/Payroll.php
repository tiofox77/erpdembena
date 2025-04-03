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
    public $isEditing = false;

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
                $this->basic_salary = $employee->position ? $employee->position->salary_range_min : 0;
                $this->bank_account = $employee->bank_account;
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
        // Calculate tax (IRT) - Angolan Income Tax
        $this->tax = $this->calculateIncomeTax();
        
        // Calculate social security (INSS) - 3% employee contribution
        $this->social_security = ($this->basic_salary + $this->allowances) * 0.03;
        
        // Calculate net salary
        $grossSalary = $this->basic_salary + $this->allowances + $this->overtime + $this->bonuses;
        $totalDeductions = $this->deductions + $this->tax + $this->social_security;
        $this->net_salary = $grossSalary - $totalDeductions;
    }

    private function calculateIncomeTax()
    {
        $taxableIncome = $this->basic_salary + $this->allowances + $this->bonuses; // overtime is often not taxable
        
        // Simplified Angolan IRT calculation - these rates should be adjusted to match actual rates
        if ($taxableIncome <= 34450) {
            return 0; // exempt
        } elseif ($taxableIncome <= 35000) {
            return ($taxableIncome - 34450) * 0.07;
        } elseif ($taxableIncome <= 40000) {
            return (($taxableIncome - 35000) * 0.08) + 38.5;
        } elseif ($taxableIncome <= 45000) {
            return (($taxableIncome - 40000) * 0.09) + 438.5;
        } elseif ($taxableIncome <= 50000) {
            return (($taxableIncome - 45000) * 0.10) + 888.5;
        } elseif ($taxableIncome <= 70000) {
            return (($taxableIncome - 50000) * 0.11) + 1388.5;
        } elseif ($taxableIncome <= 90000) {
            return (($taxableIncome - 70000) * 0.13) + 3588.5;
        } elseif ($taxableIncome <= 110000) {
            return (($taxableIncome - 90000) * 0.16) + 6188.5;
        } elseif ($taxableIncome <= 140000) {
            return (($taxableIncome - 110000) * 0.18) + 9388.5;
        } elseif ($taxableIncome <= 170000) {
            return (($taxableIncome - 140000) * 0.19) + 14788.5;
        } elseif ($taxableIncome <= 200000) {
            return (($taxableIncome - 170000) * 0.20) + 20488.5;
        } else {
            return (($taxableIncome - 200000) * 0.21) + 26488.5;
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
                $basicSalary = $employee->position ? $employee->position->salary_range_min : 0;
                
                // Calculate tax and social security
                $tax = $basicSalary * 0.07; // Simplified calculation
                $socialSecurity = $basicSalary * 0.03; // 3% employee contribution
                
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

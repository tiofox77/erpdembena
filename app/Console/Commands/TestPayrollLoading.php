<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HR\Employee;
use App\Livewire\HR\Payroll;
use Illuminate\Console\Command;
use Carbon\Carbon;

class TestPayrollLoading extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:test-payroll-loading';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test payroll loading for Dinis Paulo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Testing payroll loading...');

        $employee = Employee::where('full_name', 'Dinis Paulo Loao Cahama')->first();
        
        if (!$employee) {
            $this->error('❌ Employee not found!');
            return Command::FAILURE;
        }

        $this->info('✅ Employee found: ' . $employee->full_name);

        // Create payroll component instance
        $payroll = new Payroll();
        
        // Set employee data
        $payroll->selectedEmployee = $employee;
        $payroll->employee_id = $employee->id;
        $payroll->basic_salary = (float) ($employee->base_salary ?? 200000); // Default salary
        $payroll->selected_month = '7';
        $payroll->selected_year = '2025';
        
        // Calculate hourly rate
        $payroll->hourly_rate = $payroll->basic_salary > 0 ? ($payroll->basic_salary / 160) : 0;
        
        $this->info('💰 Basic Salary: ' . number_format($payroll->basic_salary, 2) . ' AOA');
        $this->info('⏰ Hourly Rate: ' . number_format($payroll->hourly_rate, 2) . ' AOA/h');
        
        // Load payroll data
        $this->info('📊 Loading payroll data...');
        $payroll->loadEmployeePayrollData();
        
        // Display results
        $this->info('📋 Attendance Data:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Working Days', $payroll->total_working_days],
                ['Present Days', $payroll->present_days],
                ['Absent Days', $payroll->absent_days],
                ['Late Arrivals', $payroll->late_arrivals],
                ['Total Attendance Hours', number_format($payroll->total_attendance_hours, 1) . 'h'],
                ['Regular Hours Pay', number_format($payroll->regular_hours_pay, 2) . ' AOA'],
                ['Late Deduction', number_format($payroll->late_deduction, 2) . ' AOA'],
                ['Absence Deduction', number_format($payroll->absence_deduction, 2) . ' AOA'],
            ]
        );
        
        // Test payroll calculation
        $this->info('🧮 Calculating payroll components...');
        $payroll->calculatePayrollComponents();
        
        $this->info('💰 Final Payroll Summary:');
        $this->table(
            ['Component', 'Amount'],
            [
                ['Gross Salary', number_format($payroll->gross_salary, 2) . ' AOA'],
                ['Late Deduction', '-' . number_format($payroll->late_deduction, 2) . ' AOA'],
                ['Absence Deduction', '-' . number_format($payroll->absence_deduction, 2) . ' AOA'],
                ['Total Deductions', '-' . number_format($payroll->total_deductions, 2) . ' AOA'],
                ['Net Salary', number_format($payroll->net_salary, 2) . ' AOA'],
            ]
        );

        return Command::SUCCESS;
    }
}

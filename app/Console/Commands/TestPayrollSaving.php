<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\Employee;
use App\Models\HR\Payroll as PayrollModel;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\PayrollItem;
use Carbon\Carbon;

class TestPayrollSaving extends Command
{
    protected $signature = 'test:payroll-saving';
    protected $description = 'Test payroll saving functionality';

    public function handle(): int
    {
        $this->info('Testing Payroll Saving Functionality...');
        
        // Find employee "Dinis Paulo Loao Cahama"
        $employee = Employee::where('full_name', 'like', '%Dinis Paulo Loao Cahama%')
            ->first();
            
        if (!$employee) {
            $this->error('Employee "Dinis Paulo Loao Cahama" not found!');
            return 1;
        }
        
        $this->info("Found employee: {$employee->full_name} (ID: {$employee->id})");
        
        // Create or find payroll period for July 2025
        $startDate = Carbon::create(2025, 7, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $period = PayrollPeriod::firstOrCreate([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ], [
            'name' => $startDate->format('F Y'),
            'payment_date' => $endDate->copy()->addDays(5),
            'status' => PayrollPeriod::STATUS_OPEN,
            'remarks' => 'Test period for payroll saving'
        ]);
        
        $this->info("Payroll period: {$period->name} (ID: {$period->id})");
        
        // Check if payroll already exists
        $existingPayroll = PayrollModel::where('employee_id', $employee->id)
            ->where('payroll_period_id', $period->id)
            ->first();
            
        if ($existingPayroll) {
            $this->warn('Payroll already exists! Deleting for test...');
            $existingPayroll->payrollItems()->delete();
            $existingPayroll->delete();
        }
        
        // Create test payroll data
        $payrollData = [
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'basic_salary' => 200000.00,
            'allowances' => 70000.00, // Transport + Meal + Housing
            'overtime' => 15000.00,
            'bonuses' => 50000.00,
            'tax' => 23896.00, // IRT
            'social_security' => 7800.00, // INSS
            'deductions' => 5000.00, // Late/Absence deductions
            'net_salary' => 299104.00,
            'attendance_hours' => 176.0,
            'leave_days' => 0,
            'maternity_days' => 0,
            'special_leave_days' => 0,
            'payment_method' => 'bank_transfer',
            'bank_account' => $employee->bank_account,
            'payment_date' => null,
            'status' => PayrollModel::STATUS_DRAFT,
            'remarks' => 'Test payroll created by command',
            'generated_by' => 1, // Admin user
        ];
        
        // Create payroll
        $payroll = PayrollModel::create($payrollData);
        $this->info("Created payroll record (ID: {$payroll->id})");
        
        // Create payroll items
        $items = [
            [
                'payroll_id' => $payroll->id,
                'type' => 'earning',
                'name' => 'Salário Base',
                'description' => 'Salário base mensal do funcionário',
                'amount' => 200000.00,
                'is_taxable' => true,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'allowance',
                'name' => 'Subsídio de Transporte',
                'description' => 'Subsídio mensal de transporte',
                'amount' => 40000.00,
                'is_taxable' => true,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'allowance',
                'name' => 'Subsídio de Alimentação',
                'description' => 'Subsídio mensal de alimentação',
                'amount' => 15000.00,
                'is_taxable' => false,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'allowance',
                'name' => 'Subsídio de Moradia',
                'description' => 'Subsídio mensal de moradia',
                'amount' => 15000.00,
                'is_taxable' => true,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'earning',
                'name' => 'Horas Extras',
                'description' => 'Pagamento de 10 horas extras',
                'amount' => 15000.00,
                'is_taxable' => true,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'bonus',
                'name' => 'Bónus',
                'description' => 'Bónus de performance e outros',
                'amount' => 50000.00,
                'is_taxable' => true,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'deduction',
                'name' => 'Desconto por Atrasos',
                'description' => 'Desconto por 2 dias de atraso',
                'amount' => -2500.00,
                'is_taxable' => false,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'deduction',
                'name' => 'Desconto por Faltas',
                'description' => 'Desconto por 1 dia de falta',
                'amount' => -2500.00,
                'is_taxable' => false,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'tax',
                'name' => 'INSS',
                'description' => 'Contribuição para Segurança Social (3%)',
                'amount' => -7800.00,
                'is_taxable' => false,
            ],
            [
                'payroll_id' => $payroll->id,
                'type' => 'tax',
                'name' => 'IRT',
                'description' => 'Imposto sobre Rendimento do Trabalho',
                'amount' => -23896.00,
                'is_taxable' => false,
            ],
        ];
        
        foreach ($items as $item) {
            PayrollItem::create($item);
        }
        
        $this->info('Created ' . count($items) . ' payroll items');
        
        // Verify data
        $savedPayroll = PayrollModel::with('payrollItems')->find($payroll->id);
        $this->info("\n=== PAYROLL SUMMARY ===");
        $this->info("Employee: {$savedPayroll->employee->full_name}");
        $this->info("Period: {$savedPayroll->payrollPeriod->name}");
        $this->info("Basic Salary: " . number_format((float)$savedPayroll->basic_salary, 2) . " AOA");
        $this->info("Allowances: " . number_format((float)$savedPayroll->allowances, 2) . " AOA");
        $this->info("Overtime: " . number_format((float)$savedPayroll->overtime, 2) . " AOA");
        $this->info("Bonuses: " . number_format((float)$savedPayroll->bonuses, 2) . " AOA");
        $this->info("INSS: " . number_format((float)$savedPayroll->social_security, 2) . " AOA");
        $this->info("IRT: " . number_format((float)$savedPayroll->tax, 2) . " AOA");
        $this->info("Deductions: " . number_format((float)$savedPayroll->deductions, 2) . " AOA");
        $this->info("Net Salary: " . number_format((float)$savedPayroll->net_salary, 2) . " AOA");
        $this->info("Status: {$savedPayroll->status}");
        $this->info("Attendance Hours: {$savedPayroll->attendance_hours}h");
        
        $this->info("\n=== PAYROLL ITEMS ===");
        foreach ($savedPayroll->payrollItems as $item) {
            $this->info("{$item->type}: {$item->name} - " . number_format((float)$item->amount, 2) . " AOA");
        }
        
        $this->info("\n✅ Payroll saving test completed successfully!");
        $this->info("Payroll ID: {$payroll->id}");
        
        return 0;
    }
}

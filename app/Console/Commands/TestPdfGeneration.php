<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HR\Payroll as PayrollModel;

class TestPdfGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:pdf-generation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PDF generation for payroll';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing PDF Generation...');

        try {
            // Find a payroll record
            $payroll = PayrollModel::with([
                'employee', 
                'employee.department', 
                'employee.position', 
                'payrollPeriod',
                'payrollItems'
            ])->first();

            if (!$payroll) {
                $this->error('No payroll records found!');
                return;
            }

            $this->info("Found payroll for: {$payroll->employee->full_name}");

            // Prepare data for PDF (disable logo for testing)
            $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
            $companyLogo = null; // Disable logo for testing
            $logoPath = null;
            $hasLogo = false;
            
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
            
            // Test the new modern template
            // Prepare data like the new downloadPayslip method
            $employeeData = [
                'name' => $payroll->employee->full_name,
                'id' => $payroll->employee->employee_id,
                'department' => $payroll->employee->department->name ?? 'N/A',
                'position' => $payroll->employee->position->name ?? 'N/A',
                'bank_account' => $payroll->bank_account ?? 'N/A'
            ];
            
            $periodData = [
                'name' => $payroll->payrollPeriod->name ?? 'N/A',
                'start_date' => $payroll->payrollPeriod ? $payroll->payrollPeriod->start_date->format('d/m/Y') : 'N/A',
                'end_date' => $payroll->payrollPeriod ? $payroll->payrollPeriod->end_date->format('d/m/Y') : 'N/A'
            ];
            
            $companyData = [
                'name' => \App\Models\Setting::get('company_name', 'ERP DEMBENA'),
                'address' => \App\Models\Setting::get('company_address', ''),
                'phone' => \App\Models\Setting::get('company_phone', ''),
                'email' => \App\Models\Setting::get('company_email', ''),
                'nif' => \App\Models\Setting::get('company_nif', ''),
                'logo' => \App\Models\Setting::get('company_logo', '')
            ];
            
            // Process earnings and deductions
            $earnings = [];
            $deductions = [];
            $totalEarnings = 0;
            $totalDeductions = 0;
            
            if ($payroll->payrollItems && $payroll->payrollItems->count() > 0) {
                foreach ($payroll->payrollItems->whereIn('type', ['earning', 'allowance', 'bonus']) as $item) {
                    $amount = (float)$item->amount;
                    $totalEarnings += $amount;
                    $earnings[] = [
                        'name' => $item->name,
                        'description' => $item->description,
                        'type' => $item->type,
                        'is_taxable' => $item->is_taxable,
                        'amount' => $amount
                    ];
                }
                
                foreach ($payroll->payrollItems->whereIn('type', ['deduction', 'tax']) as $item) {
                    $amount = abs((float)$item->amount);
                    $totalDeductions += $amount;
                    $deductions[] = [
                        'name' => $item->name,
                        'description' => $item->description,
                        'type' => $item->type,
                        'amount' => $amount
                    ];
                }
            }
            
            $data = [
                'payroll' => $payroll,
                'employee' => $employeeData,
                'period' => $periodData,
                'company' => $companyData,
                'earnings' => $earnings,
                'deductions' => $deductions,
                'totals' => [
                    'earnings' => $totalEarnings,
                    'deductions' => $totalDeductions,
                    'net_salary' => (float)$payroll->net_salary
                ],
                'generated_at' => now()->format('d/m/Y H:i'),
                'generated_by' => 'Test Command'
            ];
            
            // Generate PDF with dual payslip template (2 vias)
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('livewire.hr.dual-payslip-pdf', $data);
            
            // Advanced UTF-8 and rendering configurations
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isPhpEnabled', true);
            $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
            $pdf->getDomPDF()->set_option('fontSubsetting', false);
            
            // Save to storage for testing
            $fileName = 'test_payslip_' . $payroll->id . '_' . time() . '.pdf';
            $filePath = storage_path('app/public/' . $fileName);
            
            $pdf->save($filePath);
            
            $this->info("✅ PDF generated successfully!");
            $this->info("File saved to: {$filePath}");
            $this->info("File size: " . number_format(filesize($filePath) / 1024, 2) . " KB");

        } catch (\Exception $e) {
            $this->error('❌ Error generating PDF: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}

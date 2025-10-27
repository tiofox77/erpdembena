<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\SalaryAdvance;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class SalaryAdvanceReportController extends Controller
{
    /**
     * Show the salary advance report view for printing
     */
    public function show(int $id)
    {
        $advance = SalaryAdvance::with([
            'employee.department',
            'payments',
            'createdBy'
        ])->findOrFail($id);

        // Company settings
        $companyName = Setting::get('company_name', 'ERP DEMBENA');
        $companyLogo = Setting::get('company_logo');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyTaxId = Setting::get('company_tax_id', '');

        return view('reports.salary-advance-report', compact(
            'advance',
            'companyName',
            'companyLogo',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxId'
        ));
    }

    /**
     * Generate PDF for salary advance report
     */
    public function pdf(int $id)
    {
        $advance = SalaryAdvance::with([
            'employee.department',
            'payments',
            'createdBy'
        ])->findOrFail($id);

        // Company settings
        $companyName = Setting::get('company_name', 'ERP DEMBENA');
        $companyLogo = Setting::get('company_logo');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyTaxId = Setting::get('company_tax_id', '');

        $pdf = Pdf::loadView('reports.salary-advance-pdf', compact(
            'advance',
            'companyName',
            'companyLogo',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxId'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('advance-' . $advance->id . '-' . $advance->employee->full_name . '.pdf');
    }
}

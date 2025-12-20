<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\OvertimeRecord;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class OvertimeReportController extends Controller
{
    /**
     * Show the overtime report view for printing
     */
    public function show(int $id)
    {
        $overtime = OvertimeRecord::with([
            'employee.department',
            'approver'
        ])->findOrFail($id);

        // Company settings
        $companyName = Setting::get('company_name', 'ERP DEMBENA');
        $companyLogo = Setting::get('company_logo');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyTaxId = Setting::get('company_tax_id', '');

        return view('reports.overtime-report', compact(
            'overtime',
            'companyName',
            'companyLogo',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxId'
        ));
    }

    /**
     * Generate PDF for overtime report
     */
    public function pdf(int $id)
    {
        $overtime = OvertimeRecord::with([
            'employee.department',
            'approver'
        ])->findOrFail($id);

        // Company settings
        $companyName = Setting::get('company_name', 'ERP DEMBENA');
        $companyLogo = Setting::get('company_logo');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyTaxId = Setting::get('company_tax_id', '');

        $pdf = Pdf::loadView('reports.overtime-pdf', compact(
            'overtime',
            'companyName',
            'companyLogo',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxId'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('overtime-' . $overtime->id . '-' . $overtime->employee->full_name . '.pdf');
    }
}

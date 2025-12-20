<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\DisciplinaryMeasure;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class DisciplinaryMeasureReportController extends Controller
{
    /**
     * Show the disciplinary measure report view for printing
     */
    public function show(int $id)
    {
        $measure = DisciplinaryMeasure::with([
            'employee.department',
            'appliedByUser'
        ])->findOrFail($id);

        // Company settings
        $companyName = Setting::get('company_name', 'ERP DEMBENA');
        $companyLogo = Setting::get('company_logo');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyTaxId = Setting::get('company_tax_id', '');

        return view('reports.disciplinary-measure-report', compact(
            'measure',
            'companyName',
            'companyLogo',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxId'
        ));
    }

    /**
     * Generate PDF for disciplinary measure report
     */
    public function pdf(int $id)
    {
        $measure = DisciplinaryMeasure::with([
            'employee.department',
            'appliedByUser'
        ])->findOrFail($id);

        // Company settings
        $companyName = Setting::get('company_name', 'ERP DEMBENA');
        $companyLogo = Setting::get('company_logo');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyTaxId = Setting::get('company_tax_id', '');

        $pdf = Pdf::loadView('reports.disciplinary-measure-pdf', compact(
            'measure',
            'companyName',
            'companyLogo',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxId'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('medida-disciplinar-' . $measure->id . '-' . $measure->employee->full_name . '.pdf');
    }
}

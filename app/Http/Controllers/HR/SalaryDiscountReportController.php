<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\SalaryDiscount;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class SalaryDiscountReportController extends Controller
{
    /**
     * Show the salary discount report view for printing
     */
    public function show(int $id)
    {
        $discount = SalaryDiscount::with([
            'employee.department',
            'payments',
            'approver'
        ])->findOrFail($id);

        // Company settings
        $companyName = Setting::get('company_name', 'ERP DEMBENA');
        $companyLogo = Setting::get('company_logo');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyTaxId = Setting::get('company_tax_id', '');

        return view('reports.salary-discount-report', compact(
            'discount',
            'companyName',
            'companyLogo',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxId'
        ));
    }

    /**
     * Generate PDF for salary discount report
     */
    public function pdf(int $id)
    {
        $discount = SalaryDiscount::with([
            'employee.department',
            'payments',
            'approver'
        ])->findOrFail($id);

        // Company settings
        $companyName = Setting::get('company_name', 'ERP DEMBENA');
        $companyLogo = Setting::get('company_logo');
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyTaxId = Setting::get('company_tax_id', '');

        $pdf = Pdf::loadView('reports.salary-discount-pdf', compact(
            'discount',
            'companyName',
            'companyLogo',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyTaxId'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('discount-' . $discount->id . '-' . $discount->employee->full_name . '.pdf');
    }
}

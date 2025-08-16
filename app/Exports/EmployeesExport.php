<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\HR\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query(): Builder
    {
        return Employee::with(['department', 'position', 'bank'])
            ->orderBy('full_name');
    }

    public function headings(): array
    {
        return [
            'ID',
            __('messages.full_name'),
            __('messages.id_card'),
            __('messages.tax_number'),
            __('messages.email_address'),
            __('messages.phone'),
            __('messages.date_of_birth'),
            __('messages.gender'),
            __('messages.marital_status'),
            __('messages.dependents'),
            __('messages.address'),
            __('messages.department'),
            __('messages.position'),
            __('messages.hire_date'),
            __('messages.employment_status'),
            __('messages.bank_name'),
            __('messages.bank_account'),
            __('messages.bank_iban'),
            __('messages.inss_number'),
            __('messages.base_salary'),
            __('messages.food_benefit'),
            __('messages.transport_benefit'),
            __('messages.bonus_amount'),
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->full_name,
            $employee->id_card,
            $employee->tax_number,
            $employee->email,
            $employee->phone,
            $employee->date_of_birth?->format('d/m/Y'),
            $this->translateGender($employee->gender),
            $this->translateMaritalStatus($employee->marital_status),
            $employee->dependents ?? 0,
            $employee->address,
            $employee->department?->name,
            $employee->position?->title,
            $employee->hire_date?->format('d/m/Y'),
            $this->translateEmploymentStatus($employee->employment_status),
            $employee->bank?->name ?? $employee->bank_name,
            $employee->bank_account,
            $employee->bank_iban,
            $employee->inss_number,
            $employee->base_salary ? number_format((float) $employee->base_salary, 2, ',', '.') : null,
            $employee->food_benefit ? number_format((float) $employee->food_benefit, 2, ',', '.') : null,
            $employee->transport_benefit ? number_format((float) $employee->transport_benefit, 2, ',', '.') : null,
            $employee->bonus_amount ? number_format((float) $employee->bonus_amount, 2, ',', '.') : null,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '4F46E5'],
                ],
            ],
        ];
    }

    private function translateGender(?string $gender): string
    {
        return match($gender) {
            'male' => __('messages.male'),
            'female' => __('messages.female'),
            'other' => __('messages.other'),
            default => '',
        };
    }

    private function translateMaritalStatus(?string $status): string
    {
        return match($status) {
            'single' => __('messages.single'),
            'married' => __('messages.married'),
            'divorced' => __('messages.divorced'),
            'widowed' => __('messages.widowed'),
            default => '',
        };
    }

    private function translateEmploymentStatus(string $status): string
    {
        return match($status) {
            'active' => __('messages.active'),
            'on_leave' => __('messages.on_leave'),
            'terminated' => __('messages.terminated'),
            'suspended' => __('messages.suspended'),
            'retired' => __('messages.retired'),
            default => $status,
        };
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceSampleExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['5', '20/12/2025', '08:00', '17:00', ''],
            ['10', '20/12/2025', '07:55', '17:05', ''],
            ['15', '20/12/2025', '08:10', '16:30', ''],
            ['20', '20/12/2025', '', '', '9:00'],
            ['25', '20/12/2025', '08:00', '12:00', ''],
        ];
    }

    public function headings(): array
    {
        return [
            'Emp ID',
            'Date',
            'Check In',
            'Check Out',
            'Absence',
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
                    'startColor' => ['argb' => '3B82F6'],
                ],
            ],
        ];
    }
}

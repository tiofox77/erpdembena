<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceZKTimeSampleExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['5', 'Joao.Silva', '20/12/2025 08:00:00', '', 'Check In', 'escritorio'],
            ['5', 'Joao.Silva', '20/12/2025 12:00:00', '', 'Check Out', 'escritorio'],
            ['5', 'Joao.Silva', '20/12/2025 13:00:00', '', 'Check In', 'escritorio'],
            ['5', 'Joao.Silva', '20/12/2025 17:00:00', '', 'Check Out', 'escritorio'],
            ['10', 'Maria.Santos', '20/12/2025 07:55:00', '', 'Check In', 'escritorio'],
            ['10', 'Maria.Santos', '20/12/2025 17:05:00', '', 'Check Out', 'escritorio'],
            ['15', 'Pedro.Costa', '20/12/2025 08:10:00', '', 'Check In', 'fabrica'],
            ['15', 'Pedro.Costa', '20/12/2025 16:30:00', '', 'Check Out', 'fabrica'],
        ];
    }

    public function headings(): array
    {
        return [
            'Emp ID',
            'Name',
            'Time',
            'Work Code',
            'Attendance State',
            'Device Name',
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
                    'startColor' => ['argb' => '10B981'],
                ],
            ],
        ];
    }
}

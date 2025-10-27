<?php

namespace App\Exports;

use App\Models\SupplyChain\Product;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RawMaterialReportExport implements FromView, ShouldAutoSize, WithTitle, WithEvents
{
    protected $materials;
    protected $startDate;
    protected $endDate;
    protected $search;

    public function __construct($materials, $startDate = null, $endDate = null, $search = '')
    {
        $this->materials = $materials;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->search = $search;
    }

    public function view(): View
    {
        return view('pdf.raw-material-report', [
            'materials' => $this->materials,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'search' => $this->search
        ]);
    }

    public function title(): string
    {
        return __('messages.raw_material_report');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                
                // Set page orientation to landscape
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                
                // Set print area
                $sheet->getPageSetup()->setPrintArea('A1:H' . (count($this->materials) + 1));
                
                // Set header/footer
                $sheet->getHeaderFooter()
                    ->setOddHeader('&C&H' . __('messages.raw_material_report') . ' - ' . now()->format('d/m/Y H:i'));
                $sheet->getHeaderFooter()
                    ->setOddFooter('&R' . __('messages.page') . ' &P / &N');
                
                // Style the header row
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E40AF']
                    ]
                ]);
                
                // Add borders to all cells
                $sheet->getStyle('A1:H' . (count($this->materials) + 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB']
                        ]
                    ]
                ]);
                
                // Set auto filter
                $sheet->setAutoFilter('A1:H1');
            },
        ];
    }
}

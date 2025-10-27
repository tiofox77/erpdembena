<?php

namespace App\Livewire;

use App\Models\MaintenancePlan;
use Livewire\Component;

class MaintenanceTypeChart extends Component
{
    protected $listeners = [
        'maintenancePlanUpdated' => '$refresh',
        'filterUpdated' => '$refresh'
    ];
    
    public $selectedYear;
    public $selectedMonth;

    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->selectedMonth = date('m');
    }

    public function render()
    {
        // Buscar os dados para o gráfico de tipo de manutenção
        $counts = [
            'preventive' => MaintenancePlan::where('type', 'preventive')->count(),
            'corrective' => MaintenancePlan::where('type', 'corrective')->count(),
            'predictive' => MaintenancePlan::where('type', 'predictive')->count(),
            'conditional' => MaintenancePlan::where('type', 'conditional')->count(),
            'other' => MaintenancePlan::where('type', 'other')->count(),
        ];
        
        $totalCount = array_sum($counts);
        
        $percentages = [];
        foreach ($counts as $type => $count) {
            $percentages[$type] = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0;
        }
        
        $chartData = [
            'labels' => [
                __('messages.preventive'),
                __('messages.corrective'),
                __('messages.predictive'),
                __('messages.conditional'),
                __('messages.other'),
            ],
            'datasets' => [
                [
                    'label' => __('messages.maintenance_by_type'),
                    'data' => array_values($counts),
                    'backgroundColor' => [
                        '#10B981', // verde para preventiva
                        '#F59E0B', // amarelo para corretiva
                        '#3B82F6', // azul para preditiva
                        '#8B5CF6', // roxo para condicional
                        '#6B7280', // cinza para outras
                    ],
                    'borderWidth' => 1,
                ]
            ]
        ];
        
        $chartOptions = [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ]
            ]
        ];
        
        return view('livewire.maintenance-type-chart', [
            'chartData' => json_encode($chartData),
            'chartOptions' => json_encode($chartOptions),
            'percentages' => $percentages,
            'counts' => $counts,
            'totalCount' => $totalCount
        ]);
    }
}

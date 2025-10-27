<?php

namespace App\Livewire;

use App\Models\MaintenancePlan;
use Livewire\Component;

class MaintenanceStatusChart extends Component
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
        // Buscar os dados para o gráfico de status de manutenção
        $counts = [
            'pending' => MaintenancePlan::where('status', 'pending')->count(),
            'in_progress' => MaintenancePlan::where('status', 'in_progress')->count(),
            'completed' => MaintenancePlan::where('status', 'completed')->count(),
            'cancelled' => MaintenancePlan::where('status', 'cancelled')->count(),
            'schedule' => MaintenancePlan::where('status', 'schedule')->count(),
        ];
        
        $totalCount = array_sum($counts);
        
        $percentages = [];
        foreach ($counts as $status => $count) {
            $percentages[$status] = $totalCount > 0 ? round(($count / $totalCount) * 100, 1) : 0;
        }
        
        $chartData = [
            'labels' => [
                __('messages.pending'),
                __('messages.in_progress'),
                __('messages.completed'),
                __('messages.cancelled'),
                __('messages.schedule')
            ],
            'datasets' => [
                [
                    'data' => array_values($counts),
                    'backgroundColor' => [
                        '#FCD34D', // amarelo para pendente
                        '#60A5FA', // azul para em progresso
                        '#34D399', // verde para completo
                        '#F87171', // vermelho para cancelado
                        '#A78BFA'  // roxo para programado
                    ],
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
        
        return view('livewire.maintenance-status-chart', [
            'chartData' => json_encode($chartData),
            'chartOptions' => json_encode($chartOptions),
            'percentages' => $percentages,
            'counts' => $counts,
            'totalCount' => $totalCount
        ]);
    }
}

<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\MaintenanceSchedule;
use Livewire\WithPagination;

class MaintenanceHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $dateRange = 'month';
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $history = MaintenanceSchedule::query()
            ->when($this->search, function ($query) {
                $query->whereHas('equipment', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('serial_number', 'like', '%' . $this->search . '%');
                });
            })
            ->whereBetween('last_maintenance_date', [$this->startDate, $this->endDate])
            ->with('equipment')
            ->orderBy('last_maintenance_date', 'desc')
            ->paginate(10);

        return view('livewire.reports.maintenance-history', [
            'history' => $history
        ]);
    }

    public function setDateRange($range)
    {
        $this->dateRange = $range;
        $now = now();

        switch ($range) {
            case 'week':
                $this->startDate = $now->startOfWeek()->format('Y-m-d');
                $this->endDate = $now->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = $now->startOfMonth()->format('Y-m-d');
                $this->endDate = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = $now->startOfQuarter()->format('Y-m-d');
                $this->endDate = $now->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = $now->startOfYear()->format('Y-m-d');
                $this->endDate = $now->endOfYear()->format('Y-m-d');
                break;
        }
    }
}

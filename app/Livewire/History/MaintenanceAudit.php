<?php

namespace App\Livewire\History;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenanceLog;
use App\Models\Equipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MaintenanceAudit extends Component
{
    use WithPagination;

    public $dateRange = 'last-month';
    public $startDate;
    public $endDate;
    public $userId;
    public $equipmentId;
    public $actionType;
    public $searchQuery = '';

    public $users = [];
    public $equipment = [];
    public $actionTypes = [
        'all' => 'All Actions',
        'task_created' => 'Task Created',
        'task_updated' => 'Task Updated',
        'task_completed' => 'Task Completed',
        'part_replaced' => 'Part Replaced',
        'checklist_completed' => 'Checklist Completed',
        'equipment_status_changed' => 'Equipment Status Changed',
        'downtime_recorded' => 'Downtime Recorded'
    ];

    public $auditLogs = [];
    public $totalActions = 0;
    public $criticalActions = 0;
    public $mostActiveUser = null;
    public $mostServicedEquipment = null;

    protected $queryString = [
        'dateRange' => ['except' => 'last-month'],
        'userId' => ['except' => ''],
        'equipmentId' => ['except' => ''],
        'actionType' => ['except' => ''],
        'searchQuery' => ['except' => '']
    ];

    public function mount()
    {
        // Set default date range
        $this->setDateRange($this->dateRange);
        $this->loadUsersList();
        $this->loadEquipmentList();
        $this->loadAuditData();
    }

    public function setDateRange($range)
    {
        $this->dateRange = $range;

        switch ($range) {
            case 'last-week':
                $this->startDate = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-quarter':
                $this->startDate = Carbon::now()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-year':
                $this->startDate = Carbon::now()->subYear()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'custom':
                // Keep existing custom dates if already set
                if (!$this->startDate) {
                    $this->startDate = Carbon::now()->subMonth()->format('Y-m-d');
                }
                if (!$this->endDate) {
                    $this->endDate = Carbon::now()->format('Y-m-d');
                }
                break;
        }

        $this->resetPage();
    }

    public function updatedDateRange($value)
    {
        $this->setDateRange($value);
        $this->loadAuditData();
    }

    public function updatedStartDate($value)
    {
        $this->resetPage();
        $this->loadAuditData();
    }

    public function updatedEndDate($value)
    {
        $this->resetPage();
        $this->loadAuditData();
    }

    public function updatedUserId($value)
    {
        $this->resetPage();
        $this->loadAuditData();
    }

    public function updatedEquipmentId($value)
    {
        $this->resetPage();
        $this->loadAuditData();
    }

    public function updatedActionType($value)
    {
        $this->resetPage();
        $this->loadAuditData();
    }

    public function updatedSearchQuery($value)
    {
        $this->resetPage();
        $this->loadAuditData();
    }

    public function loadUsersList()
    {
        try {
            $this->users = User::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error('Error loading users list: ' . $e->getMessage());
            $this->users = [];
        }
    }

    public function loadEquipmentList()
    {
        try {
            $this->equipment = Equipment::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error('Error loading equipment list: ' . $e->getMessage());
            $this->equipment = [];
        }
    }

    public function loadAuditData()
    {
        try {
            // Base query for audit logs
            $query = MaintenanceLog::query()
                ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
                ->with(['user', 'equipment']);

            // Apply filters
            if ($this->userId) {
                $query->where('user_id', $this->userId);
            }

            if ($this->equipmentId) {
                $query->where('equipment_id', $this->equipmentId);
            }

            if ($this->actionType && $this->actionType !== 'all') {
                $query->where('action_type', $this->actionType);
            }

            if ($this->searchQuery) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->searchQuery . '%')
                      ->orWhereHas('user', function ($uq) {
                          $uq->where('name', 'like', '%' . $this->searchQuery . '%');
                      })
                      ->orWhereHas('equipment', function ($eq) {
                          $eq->where('name', 'like', '%' . $this->searchQuery . '%');
                      });
                });
            }

            // Get paginated results
            $this->auditLogs = $query->orderBy('created_at', 'desc')
                                    ->paginate(20);

            // Calculate summary statistics
            $this->calculateSummaryStatistics();

        } catch (\Exception $e) {
            Log::error('Error loading audit data: ' . $e->getMessage());
            $this->auditLogs = [];
            $this->totalActions = 0;
            $this->criticalActions = 0;
            $this->mostActiveUser = null;
            $this->mostServicedEquipment = null;
        }
    }

    protected function calculateSummaryStatistics()
    {
        try {
            // Get date range for statistics
            $startDate = $this->startDate . ' 00:00:00';
            $endDate = $this->endDate . ' 23:59:59';

            // Total actions count
            $this->totalActions = MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])->count();

            // Critical actions (status changes, failures, repairs)
            $this->criticalActions = MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('action_type', ['equipment_status_changed', 'downtime_recorded', 'part_replaced'])
                ->count();

            // Most active user
            $mostActiveUserId = MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('user_id')
                ->selectRaw('COUNT(*) as action_count')
                ->groupBy('user_id')
                ->orderByDesc('action_count')
                ->first();

            if ($mostActiveUserId) {
                $this->mostActiveUser = User::find($mostActiveUserId->user_id);
            }

            // Most serviced equipment
            $mostServicedEquipmentId = MaintenanceLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('equipment_id')
                ->selectRaw('COUNT(*) as service_count')
                ->groupBy('equipment_id')
                ->orderByDesc('service_count')
                ->first();

            if ($mostServicedEquipmentId) {
                $this->mostServicedEquipment = Equipment::find($mostServicedEquipmentId->equipment_id);
            }
        } catch (\Exception $e) {
            Log::error('Error calculating summary statistics: ' . $e->getMessage());
        }
    }

    public function exportAuditLogs()
    {
        // Placeholder for export functionality
        $this->dispatchBrowserEvent('show-notification', [
            'type' => 'info',
            'message' => 'Export functionality will be implemented soon'
        ]);
    }

    public function render()
    {
        return view('livewire.history.maintenance-audit');
    }
}

<?php

namespace App\Livewire\History;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenanceTaskLog;
use App\Models\MaintenanceEquipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MaintenanceAudit extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'tailwind';

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
        'completed' => 'Completed',
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'failed' => 'Failed',
        'repaired' => 'Repaired',
        'scheduled' => 'Scheduled'
    ];

    // Summary statistics
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
        // Set default date range and load initial data
        $this->setDateRange($this->dateRange);
        
        // Load reference data
        $this->loadUsersList();
        $this->loadEquipmentList();
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
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function updatedUserId()
    {
        $this->resetPage();
    }

    public function updatedEquipmentId()
    {
        $this->resetPage();
    }

    public function updatedActionType()
    {
        $this->resetPage();
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
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
            $this->equipment = MaintenanceEquipment::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error('Error loading equipment list: ' . $e->getMessage());
            $this->equipment = [];
        }
    }

    public function getAuditLogsProperty()
    {
        try {
            // Base query for audit logs
            $query = MaintenanceTaskLog::query()
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
                $query->where('status', $this->actionType);
            }

            if ($this->searchQuery) {
                $query->where(function ($q) {
                    $q->where('notes', 'like', '%' . $this->searchQuery . '%')
                      ->orWhereHas('user', function ($uq) {
                          $uq->where('name', 'like', '%' . $this->searchQuery . '%');
                      })
                      ->orWhereHas('equipment', function ($eq) {
                          $eq->where('name', 'like', '%' . $this->searchQuery . '%');
                      });
                });
            }

            // Get paginated results
            return $query->orderBy('created_at', 'desc')->paginate(20);

        } catch (\Exception $e) {
            Log::error('Error loading audit data: ' . $e->getMessage());
            return collect([]);
        }
    }

    protected function calculateSummaryStatistics()
    {
        try {
            // Get date range for statistics
            $startDate = $this->startDate . ' 00:00:00';
            $endDate = $this->endDate . ' 23:59:59';

            // Total actions count
            $this->totalActions = MaintenanceTaskLog::whereBetween('created_at', [$startDate, $endDate])->count();

            // Critical actions (status changes, failures, repairs)
            $this->criticalActions = MaintenanceTaskLog::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'failed', 'repaired'])
                ->count();

            // Most active user
            $mostActiveUserId = MaintenanceTaskLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('user_id')
                ->selectRaw('COUNT(*) as action_count')
                ->groupBy('user_id')
                ->orderByDesc('action_count')
                ->first();

            if ($mostActiveUserId) {
                $this->mostActiveUser = User::find($mostActiveUserId->user_id);
            }

            // Most serviced equipment
            $mostServicedEquipmentId = MaintenanceTaskLog::whereBetween('created_at', [$startDate, $endDate])
                ->select('equipment_id')
                ->selectRaw('COUNT(*) as service_count')
                ->groupBy('equipment_id')
                ->orderByDesc('service_count')
                ->first();

            if ($mostServicedEquipmentId) {
                $this->mostServicedEquipment = MaintenanceEquipment::find($mostServicedEquipmentId->equipment_id);
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
        // Calculate summary statistics before rendering
        $this->calculateSummaryStatistics();
        
        return view('livewire.history.maintenance-audit');
    }
}

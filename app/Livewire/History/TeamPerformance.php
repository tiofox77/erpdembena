<?php

namespace App\Livewire\History;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\MaintenanceTask;
use App\Models\Equipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TeamPerformance extends Component
{
    use WithPagination;

    // Filters
    public $dateRange = 'last-month';
    public $startDate;
    public $endDate;
    public $userId;
    public $areaId;
    public $taskType;
    public $searchQuery = '';
    public $sortField = 'completed_tasks';
    public $sortDirection = 'desc';

    // Data collections
    public $users = [];
    public $areas = [];
    public $taskTypes = [
        'all' => 'All Task Types',
        'preventive' => 'Preventive Maintenance',
        'corrective' => 'Corrective Maintenance',
        'predictive' => 'Predictive Maintenance',
        'inspection' => 'Inspection'
    ];

    // Summary metrics
    public $totalCompletedTasks = 0;
    public $taskCompletionRate = 0;
    public $avgTaskDuration = 0;
    public $topPerformer = null;
    public $improvementAreas = [];

    protected $queryString = [
        'dateRange' => ['except' => 'last-month'],
        'userId' => ['except' => ''],
        'areaId' => ['except' => ''],
        'taskType' => ['except' => ''],
        'searchQuery' => ['except' => ''],
        'sortField' => ['except' => 'completed_tasks'],
        'sortDirection' => ['except' => 'desc']
    ];

    public function mount()
    {
        $this->setDateRange($this->dateRange);
        $this->loadUsersList();
        $this->loadAreasList();
        $this->loadPerformanceData();
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
        $this->loadPerformanceData();
    }

    public function updatedStartDate($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedEndDate($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedUserId($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedAreaId($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedTaskType($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function updatedSearchQuery($value)
    {
        $this->resetPage();
        $this->loadPerformanceData();
    }

    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function loadUsersList()
    {
        try {
            // Get users with maintenance roles
            $this->users = User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['technician', 'maintenance_manager', 'engineer']);
                })
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error loading users list: ' . $e->getMessage());
            $this->users = [];
        }
    }

    public function loadAreasList()
    {
        try {
            // Get unique areas from equipment table
            $this->areas = Equipment::select('area')
                ->distinct()
                ->whereNotNull('area')
                ->orderBy('area')
                ->pluck('area')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading areas list: ' . $e->getMessage());
            $this->areas = [];
        }
    }

    public function loadPerformanceData()
    {
        try {
            // Get date range for queries
            $startDateTime = $this->startDate . ' 00:00:00';
            $endDateTime = $this->endDate . ' 23:59:59';

            // Base query for completed tasks
            $completedTasksQuery = MaintenanceTask::whereBetween('completed_at', [$startDateTime, $endDateTime])
                ->where('status', 'completed');

            // Base query for all tasks
            $allTasksQuery = MaintenanceTask::where(function($query) use ($startDateTime, $endDateTime) {
                    $query->whereBetween('due_date', [$startDateTime, $endDateTime])
                        ->orWhereBetween('completed_at', [$startDateTime, $endDateTime]);
                });

            // Apply common filters to both queries
            if ($this->userId) {
                $completedTasksQuery->where('assigned_to', $this->userId);
                $allTasksQuery->where('assigned_to', $this->userId);
            }

            if ($this->areaId) {
                $completedTasksQuery->whereHas('equipment', function($query) {
                    $query->where('area', $this->areaId);
                });

                $allTasksQuery->whereHas('equipment', function($query) {
                    $query->where('area', $this->areaId);
                });
            }

            if ($this->taskType && $this->taskType !== 'all') {
                $completedTasksQuery->where('maintenance_type', $this->taskType);
                $allTasksQuery->where('maintenance_type', $this->taskType);
            }

            if ($this->searchQuery) {
                $completedTasksQuery->where(function($query) {
                    $query->whereHas('equipment', function($q) {
                            $q->where('name', 'like', '%' . $this->searchQuery . '%');
                        })
                        ->orWhereHas('assignedTo', function($q) {
                            $q->where('name', 'like', '%' . $this->searchQuery . '%');
                        })
                        ->orWhere('title', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('description', 'like', '%' . $this->searchQuery . '%');
                });

                $allTasksQuery->where(function($query) {
                    $query->whereHas('equipment', function($q) {
                            $q->where('name', 'like', '%' . $this->searchQuery . '%');
                        })
                        ->orWhereHas('assignedTo', function($q) {
                            $q->where('name', 'like', '%' . $this->searchQuery . '%');
                        })
                        ->orWhere('title', 'like', '%' . $this->searchQuery . '%')
                        ->orWhere('description', 'like', '%' . $this->searchQuery . '%');
                });
            }

            // Calculate summary metrics
            $completedTasksCount = $completedTasksQuery->count();
            $totalTasksCount = $allTasksQuery->count();

            $this->totalCompletedTasks = $completedTasksCount;
            $this->taskCompletionRate = $totalTasksCount > 0 ? round(($completedTasksCount / $totalTasksCount) * 100, 1) : 0;

            // Average task duration (in hours)
            $avgDuration = MaintenanceTask::whereBetween('completed_at', [$startDateTime, $endDateTime])
                ->where('status', 'completed')
                ->whereNotNull('started_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_duration'))
                ->first();

            $this->avgTaskDuration = $avgDuration ? round($avgDuration->avg_duration / 60, 1) : 0;

            // Get top performer
            $topPerformerQuery = MaintenanceTask::whereBetween('completed_at', [$startDateTime, $endDateTime])
                ->where('status', 'completed')
                ->select('assigned_to')
                ->selectRaw('COUNT(*) as completed_count')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_duration')
                ->whereNotNull('assigned_to')
                ->whereNotNull('started_at')
                ->groupBy('assigned_to')
                ->orderByDesc('completed_count')
                ->limit(1)
                ->first();

            if ($topPerformerQuery && $topPerformerQuery->assigned_to) {
                $this->topPerformer = User::find($topPerformerQuery->assigned_to);
            }

            // Calculate improvement areas
            $this->identifyImprovementAreas($startDateTime, $endDateTime);

        } catch (\Exception $e) {
            Log::error('Error loading performance data: ' . $e->getMessage());
            $this->totalCompletedTasks = 0;
            $this->taskCompletionRate = 0;
            $this->avgTaskDuration = 0;
            $this->topPerformer = null;
            $this->improvementAreas = [];
        }
    }

    protected function identifyImprovementAreas($startDateTime, $endDateTime)
    {
        try {
            $this->improvementAreas = [];

            // Identify areas with low completion rates
            $areaCompletionRates = MaintenanceTask::join('equipment', 'maintenance_tasks.equipment_id', '=', 'equipment.id')
                ->whereBetween(DB::raw('COALESCE(maintenance_tasks.completed_at, maintenance_tasks.due_date)'), [$startDateTime, $endDateTime])
                ->whereNotNull('equipment.area')
                ->select('equipment.area')
                ->selectRaw('COUNT(*) as total_tasks')
                ->selectRaw('SUM(CASE WHEN maintenance_tasks.status = "completed" THEN 1 ELSE 0 END) as completed_tasks')
                ->groupBy('equipment.area')
                ->having('total_tasks', '>', 5) // Only consider areas with enough tasks
                ->get();

            foreach ($areaCompletionRates as $areaRate) {
                $completionRate = ($areaRate->completed_tasks / $areaRate->total_tasks) * 100;

                if ($completionRate < 70) {
                    $this->improvementAreas[] = [
                        'type' => 'area',
                        'name' => $areaRate->area,
                        'metric' => 'completion_rate',
                        'value' => round($completionRate, 1),
                        'recommendation' => 'Review resource allocation for this area'
                    ];
                }
            }

            // Identify task types with long durations
            $taskTypeDurations = MaintenanceTask::whereBetween('completed_at', [$startDateTime, $endDateTime])
                ->where('status', 'completed')
                ->whereNotNull('started_at')
                ->select('maintenance_type')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_duration')
                ->selectRaw('COUNT(*) as task_count')
                ->groupBy('maintenance_type')
                ->having('task_count', '>', 5) // Only consider types with enough tasks
                ->get();

            foreach ($taskTypeDurations as $taskType) {
                $durationHours = $taskType->avg_duration / 60;

                if ($durationHours > 4) { // Tasks taking more than 4 hours on average
                    $this->improvementAreas[] = [
                        'type' => 'task_type',
                        'name' => $taskType->maintenance_type,
                        'metric' => 'duration',
                        'value' => round($durationHours, 1),
                        'recommendation' => 'Review procedures for ' . $taskType->maintenance_type . ' tasks'
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error identifying improvement areas: ' . $e->getMessage());
            $this->improvementAreas = [];
        }
    }

    public function exportPerformanceData()
    {
        // Placeholder for export functionality
        $this->dispatchBrowserEvent('show-notification', [
            'type' => 'info',
            'message' => 'Export functionality will be implemented soon'
        ]);
    }

    public function getUserPerformanceProperty()
    {
        try {
            $startDateTime = $this->startDate . ' 00:00:00';
            $endDateTime = $this->endDate . ' 23:59:59';

            $query = User::whereHas('tasks', function($query) use ($startDateTime, $endDateTime) {
                $query->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->whereBetween('due_date', [$startDateTime, $endDateTime])
                      ->orWhereBetween('completed_at', [$startDateTime, $endDateTime]);
                });

                if ($this->areaId) {
                    $query->whereHas('equipment', function($q) {
                        $q->where('area', $this->areaId);
                    });
                }

                if ($this->taskType && $this->taskType !== 'all') {
                    $query->where('maintenance_type', $this->taskType);
                }

                if ($this->searchQuery) {
                    $query->where(function($q) {
                        $q->whereHas('equipment', function($eq) {
                             $eq->where('name', 'like', '%' . $this->searchQuery . '%');
                          })
                         ->orWhere('title', 'like', '%' . $this->searchQuery . '%')
                         ->orWhere('description', 'like', '%' . $this->searchQuery . '%');
                    });
                }
            });

            if ($this->userId) {
                $query->where('id', $this->userId);
            }

            if ($this->searchQuery) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->searchQuery . '%')
                      ->orWhere('email', 'like', '%' . $this->searchQuery . '%');
                });
            }

            // Select users with their performance metrics
            $users = $query->withCount(['tasks as total_tasks' => function($query) use ($startDateTime, $endDateTime) {
                $query->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->whereBetween('due_date', [$startDateTime, $endDateTime])
                      ->orWhereBetween('completed_at', [$startDateTime, $endDateTime]);
                });

                if ($this->areaId) {
                    $query->whereHas('equipment', function($q) {
                        $q->where('area', $this->areaId);
                    });
                }

                if ($this->taskType && $this->taskType !== 'all') {
                    $query->where('maintenance_type', $this->taskType);
                }
            }])
            ->withCount(['tasks as completed_tasks' => function($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('completed_at', [$startDateTime, $endDateTime])
                    ->where('status', 'completed');

                if ($this->areaId) {
                    $query->whereHas('equipment', function($q) {
                        $q->where('area', $this->areaId);
                    });
                }

                if ($this->taskType && $this->taskType !== 'all') {
                    $query->where('maintenance_type', $this->taskType);
                }
            }])
            ->withCount(['tasks as overdue_tasks' => function($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('due_date', [$startDateTime, $endDateTime])
                    ->where('status', 'overdue');

                if ($this->areaId) {
                    $query->whereHas('equipment', function($q) {
                        $q->where('area', $this->areaId);
                    });
                }

                if ($this->taskType && $this->taskType !== 'all') {
                    $query->where('maintenance_type', $this->taskType);
                }
            }]);

            // Add average task duration
            $users = $users->with(['tasks' => function($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('completed_at', [$startDateTime, $endDateTime])
                    ->where('status', 'completed')
                    ->whereNotNull('started_at')
                    ->select('assigned_to',
                        DB::raw('TIMESTAMPDIFF(MINUTE, started_at, completed_at) as duration_minutes'));

                if ($this->areaId) {
                    $query->whereHas('equipment', function($q) {
                        $q->where('area', $this->areaId);
                    });
                }

                if ($this->taskType && $this->taskType !== 'all') {
                    $query->where('maintenance_type', $this->taskType);
                }
            }]);

            // Order by the selected sort field
            switch ($this->sortField) {
                case 'name':
                    $users = $users->orderBy('name', $this->sortDirection);
                    break;
                case 'completed_tasks':
                    $users = $users->orderBy('completed_tasks', $this->sortDirection);
                    break;
                case 'completion_rate':
                    // Will be sorted after collection is retrieved
                    break;
                case 'avg_duration':
                    // Will be sorted after collection is retrieved
                    break;
                case 'overdue_tasks':
                    $users = $users->orderBy('overdue_tasks', $this->sortDirection);
                    break;
                default:
                    $users = $users->orderBy('completed_tasks', 'desc');
            }

            $users = $users->get();

            // Calculate completion rate and average duration for each user
            $users->map(function($user) {
                $user->completion_rate = $user->total_tasks > 0
                    ? round(($user->completed_tasks / $user->total_tasks) * 100, 1)
                    : 0;

                $totalDuration = 0;
                $taskCount = 0;

                foreach ($user->tasks as $task) {
                    if (isset($task->duration_minutes) && $task->duration_minutes > 0) {
                        $totalDuration += $task->duration_minutes;
                        $taskCount++;
                    }
                }

                $user->avg_duration = $taskCount > 0
                    ? round($totalDuration / $taskCount / 60, 1)
                    : 0;

                return $user;
            });

            // Sort by calculated fields if needed
            if ($this->sortField === 'completion_rate') {
                $users = $this->sortDirection === 'asc'
                    ? $users->sortBy('completion_rate')
                    : $users->sortByDesc('completion_rate');
            } elseif ($this->sortField === 'avg_duration') {
                $users = $this->sortDirection === 'asc'
                    ? $users->sortBy('avg_duration')
                    : $users->sortByDesc('avg_duration');
            }

            return $users->paginate(10);

        } catch (\Exception $e) {
            Log::error('Error fetching user performance: ' . $e->getMessage());
            return collect([])->paginate(10);
        }
    }

    public function render()
    {
        return view('livewire.history.team-performance', [
            'userPerformance' => $this->getUserPerformanceProperty()
        ]);
    }
}

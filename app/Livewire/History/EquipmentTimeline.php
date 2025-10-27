<?php

namespace App\Livewire\History;

use Livewire\Component;
use App\Models\MaintenanceEquipment as Equipment;
use App\Models\MaintenanceTaskLog as TaskLog;
use App\Models\MaintenanceCorrective as Corrective;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquipmentTimeline extends Component
{
    public $equipmentId = null;
    public $equipment = [];
    public $equipmentList = [];
    public $timelineEvents = [];
    public $timelinePeriod = 'year';
    public $startDate;
    public $endDate;

    public function mount()
    {
        // Load equipment list
        $this->loadEquipmentList();

        // Set default date range
        $this->setTimelinePeriod($this->timelinePeriod);
    }

    public function updatedEquipmentId()
    {
        if ($this->equipmentId) {
            $this->loadEquipmentData();
            $this->loadTimelineEvents();
        } else {
            $this->equipment = [];
            $this->timelineEvents = [];
        }
    }

    public function updatedTimelinePeriod()
    {
        $this->setTimelinePeriod($this->timelinePeriod);
        if ($this->equipmentId) {
            $this->loadTimelineEvents();
        }
    }

    public function updatedStartDate()
    {
        if ($this->equipmentId) {
            $this->loadTimelineEvents();
        }
    }

    public function updatedEndDate()
    {
        if ($this->equipmentId) {
            $this->loadTimelineEvents();
        }
    }

    protected function loadEquipmentList()
    {
        try {
            $this->equipmentList = Equipment::orderBy('name')
                ->select('id', 'name', 'serial_number')
                ->with(['area', 'line'])
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'serial_number' => $item->serial_number,
                        'area' => $item->area ? $item->area->name : 'Unknown',
                        'line' => $item->line ? $item->line->name : 'Unknown',
                        'display_name' => $item->name . ' (' . ($item->serial_number ?? 'No SN') . ')'
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading equipment list: ' . $e->getMessage());
            $this->equipmentList = [];
        }
    }

    protected function loadEquipmentData()
    {
        try {
            $equipment = Equipment::with(['area', 'line'])
                ->find($this->equipmentId);

            if ($equipment) {
                $this->equipment = [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'serial_number' => $equipment->serial_number,
                    'status' => $equipment->status,
                    'purchase_date' => $equipment->purchase_date ? Carbon::parse($equipment->purchase_date)->format(\App\Models\Setting::getSystemDateFormat()) : 'Unknown',
                    'area' => $equipment->area ? $equipment->area->name : 'Unknown',
                    'line' => $equipment->line ? $equipment->line->name : 'Unknown',
                    'last_maintenance' => $equipment->last_maintenance ? Carbon::parse($equipment->last_maintenance)->format(\App\Models\Setting::getSystemDateFormat()) : 'None',
                    'next_maintenance' => $equipment->next_maintenance ? Carbon::parse($equipment->next_maintenance)->format(\App\Models\Setting::getSystemDateFormat()) : 'None scheduled',
                    'notes' => $equipment->notes ?? 'No notes'
                ];
            } else {
                $this->equipment = [];
            }
        } catch (\Exception $e) {
            Log::error('Error loading equipment data: ' . $e->getMessage());
            $this->equipment = [];
        }
    }

    protected function setTimelinePeriod($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'month':
                $this->startDate = $now->copy()->subMonth()->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = $now->copy()->subMonths(3)->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = $now->copy()->subYear()->format('Y-m-d');
                $this->endDate = $now->format('Y-m-d');
                break;
            case 'all':
                $this->startDate = null;
                $this->endDate = null;
                break;
            case 'custom':
                // Keep existing dates if set, otherwise set defaults
                if (!$this->startDate) {
                    $this->startDate = $now->copy()->subYear()->format('Y-m-d');
                }
                if (!$this->endDate) {
                    $this->endDate = $now->format('Y-m-d');
                }
                break;
        }
    }

    protected function loadTimelineEvents()
    {
        try {
            if (!$this->equipmentId) {
                $this->timelineEvents = [];
                return;
            }

            $events = [];

            // Load maintenance tasks
            $taskQuery = TaskLog::where('equipment_id', $this->equipmentId);

            if ($this->startDate && $this->endDate) {
                $taskQuery->where(function($query) {
                    $query->whereBetween('scheduled_date', [$this->startDate, $this->endDate])
                        ->orWhereBetween('completed_at', [$this->startDate, $this->endDate]);
                });
            }

            $tasks = $taskQuery->with(['task', 'assignedTo'])->get();

            foreach ($tasks as $task) {
                $status = '';
                $icon = '';

                switch ($task->status) {
                    case 'completed':
                        $status = 'Completed';
                        $icon = 'check-circle';
                        $colorClass = 'success';
                        break;
                    case 'in_progress':
                        $status = 'In Progress';
                        $icon = 'clock';
                        $colorClass = 'primary';
                        break;
                    case 'scheduled':
                        $status = 'Scheduled';
                        $icon = 'calendar';
                        $colorClass = 'info';
                        break;
                    case 'overdue':
                        $status = 'Overdue';
                        $icon = 'exclamation-circle';
                        $colorClass = 'warning';
                        break;
                    default:
                        $status = 'Unknown';
                        $icon = 'question-circle';
                        $colorClass = 'secondary';
                }

                $date = $task->completed_at ?? $task->scheduled_date;
                if (!$date) continue;

                $events[] = [
                    'id' => 'task-' . $task->id,
                    'date' => Carbon::parse($date)->format(\App\Models\Setting::getSystemDateFormat()),
                    'timestamp' => Carbon::parse($date)->timestamp,
                    'type' => 'Maintenance Task',
                    'title' => $task->task ? $task->task->title : 'Maintenance Task',
                    'description' => $task->task ? $task->task->description : '',
                    'status' => $status,
                    'icon' => $icon,
                    'color_class' => $colorClass,
                    'technician' => $task->assignedTo ? $task->assignedTo->name : 'Unassigned',
                    'details' => [
                        'Scheduled Date' => $task->scheduled_date ? Carbon::parse($task->scheduled_date)->format(\App\Models\Setting::getSystemDateFormat()) : 'N/A',
                        'Completed Date' => $task->completed_at ? Carbon::parse($task->completed_at)->format(\App\Models\Setting::getSystemDateFormat()) : 'N/A',
                        'Duration' => $task->actual_hours ? $task->actual_hours . ' hours' : 'N/A',
                        'Parts Used' => $task->parts_used ?? 'None',
                        'Notes' => $task->notes ?? 'None'
                    ]
                ];
            }

            // Load corrective maintenance records
            $correctiveQuery = Corrective::where('equipment_id', $this->equipmentId);

            if ($this->startDate && $this->endDate) {
                $correctiveQuery->whereBetween('start_time', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            }

            $correctives = $correctiveQuery->with(['failureMode', 'failureCause', 'reporter', 'resolver'])->get();

            foreach ($correctives as $corrective) {
                $status = '';
                $icon = '';

                switch ($corrective->status) {
                    case 'resolved':
                    case 'closed':
                        $status = 'Resolved';
                        $icon = 'check-circle';
                        $colorClass = 'success';
                        break;
                    case 'in_progress':
                        $status = 'In Progress';
                        $icon = 'wrench';
                        $colorClass = 'primary';
                        break;
                    case 'open':
                        $status = 'Open';
                        $icon = 'exclamation-triangle';
                        $colorClass = 'danger';
                        break;
                    default:
                        $status = 'Unknown';
                        $icon = 'question-circle';
                        $colorClass = 'secondary';
                }

                $date = $corrective->start_time;
                if (!$date) continue;

                $events[] = [
                    'id' => 'corrective-' . $corrective->id,
                    'date' => Carbon::parse($date)->format(\App\Models\Setting::getSystemDateFormat()),
                    'timestamp' => Carbon::parse($date)->timestamp,
                    'type' => 'Failure/Breakdown',
                    'title' => 'Failure: ' . ($corrective->failureMode ? $corrective->failureMode->name : 'Unknown'),
                    'description' => $corrective->description ?? '',
                    'status' => $status,
                    'icon' => $icon,
                    'color_class' => $colorClass,
                    'technician' => $corrective->resolver ? $corrective->resolver->name : 'Unassigned',
                    'details' => [
                        'Start Time' => Carbon::parse($corrective->start_time)->format(\App\Models\Setting::getSystemDateTimeFormat()),
                        'End Time' => $corrective->end_time ? Carbon::parse($corrective->end_time)->format(\App\Models\Setting::getSystemDateTimeFormat()) : 'N/A',
                        'Downtime' => $corrective->downtime_length ?? 'Unknown',
                        'Failure Mode' => $corrective->failureMode ? $corrective->failureMode->name : 'Unknown',
                        'Root Cause' => $corrective->failureCause ? $corrective->failureCause->name : 'Unknown',
                        'Reported By' => $corrective->reporter ? $corrective->reporter->name : 'Unknown',
                        'Actions Taken' => $corrective->actions_taken ?? 'None recorded'
                    ]
                ];
            }

            // Add purchase date if available and within range
            if (!empty($this->equipment['purchase_date']) && $this->equipment['purchase_date'] !== 'Unknown') {
                $purchaseDate = Carbon::parse($this->equipment['purchase_date']);
                $includeEvent = true;

                if ($this->startDate && $this->endDate) {
                    $startDate = Carbon::parse($this->startDate);
                    $endDate = Carbon::parse($this->endDate);
                    $includeEvent = $purchaseDate->between($startDate, $endDate);
                }

                if ($includeEvent) {
                    $events[] = [
                        'id' => 'purchase-' . $this->equipmentId,
                        'date' => $purchaseDate->format(\App\Models\Setting::getSystemDateFormat()),
                        'timestamp' => $purchaseDate->timestamp,
                        'type' => 'Equipment Purchase',
                        'title' => 'Equipment Purchased',
                        'description' => 'Equipment added to inventory',
                        'status' => 'Completed',
                        'icon' => 'shopping-cart',
                        'color_class' => 'info',
                        'technician' => 'N/A',
                        'details' => [
                            'Purchase Date' => $purchaseDate->format(\App\Models\Setting::getSystemDateFormat()),
                            'Notes' => $this->equipment['notes'] ?? 'None'
                        ]
                    ];
                }
            }

            // Sort by date, most recent first
            usort($events, function($a, $b) {
                return $b['timestamp'] <=> $a['timestamp'];
            });

            $this->timelineEvents = $events;

        } catch (\Exception $e) {
            Log::error('Error loading timeline events: ' . $e->getMessage());
            $this->timelineEvents = [];
        }
    }

    public function render()
    {
        return view('livewire.history.equipment-timeline', [
            'equipmentList' => $this->equipmentList,
            'equipment' => $this->equipment,
            'timelineEvents' => $this->timelineEvents
        ]);
    }
}

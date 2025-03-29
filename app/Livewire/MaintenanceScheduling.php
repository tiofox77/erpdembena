<?php

namespace App\Livewire;

use App\Models\MaintenanceSchedule;
use App\Models\Equipment;
use App\Models\Area;
use App\Models\Line;
use App\Models\MaintenanceTask;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class MaintenancePlan extends Component
{
    use WithPagination;

    public $showModal = false;
    public $isEditing = false;
    public $scheduleId;

    // Form fields
    public $task_id;
    public $equipment_id;
    public $line_id;
    public $area_id;
    public $scheduled_date;
    public $frequency_type = 'custom_days';
    public $custom_days;
    public $day_of_week;
    public $day_of_month;
    public $month;
    public $month_day;
    public $priority = 'medium';
    public $type = 'corrective';
    public $assigned_to;
    public $description;
    public $notes;
    public $status = 'scheduled';

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $frequencyFilter = '';

    protected $rules = [
        'task_id' => 'required|exists:maintenance_tasks,id',
        'equipment_id' => 'required|exists:equipment,id',
        'line_id' => 'required|exists:lines,id',
        'area_id' => 'required|exists:areas,id',
        'scheduled_date' => 'required|date',
        'frequency_type' => 'required|in:custom_days,weekly,monthly,yearly',
        'custom_days' => 'required_if:frequency_type,custom_days|nullable|integer|min:1',
        'day_of_week' => 'required_if:frequency_type,weekly|nullable|integer|between:0,6',
        'day_of_month' => 'required_if:frequency_type,monthly|nullable|integer|between:1,31',
        'month' => 'required_if:frequency_type,yearly|nullable|integer|between:1,12',
        'month_day' => 'required_if:frequency_type,yearly|nullable|integer|between:1,31',
        'priority' => 'required|in:low,medium,high',
        'type' => 'required|in:corrective,preventive',
        'assigned_to' => 'required|exists:users,id',
        'description' => 'nullable|string',
        'notes' => 'nullable|string',
        'status' => 'required|in:scheduled,in_progress,completed,cancelled',
    ];

    protected $messages = [
        'task_id.required' => 'Please select a task.',
        'task_id.exists' => 'The selected task is invalid.',
        'equipment_id.required' => 'Please select an equipment.',
        'equipment_id.exists' => 'The selected equipment is invalid.',
        'line_id.required' => 'Please select a line.',
        'line_id.exists' => 'The selected line is invalid.',
        'area_id.required' => 'Please select an area.',
        'area_id.exists' => 'The selected area is invalid.',
        'scheduled_date.required' => 'Please select a scheduled date.',
        'scheduled_date.date' => 'Please enter a valid date.',
        'frequency_type.required' => 'Please select a frequency type.',
        'frequency_type.in' => 'The selected frequency type is invalid.',
        'custom_days.required_if' => 'Please enter the number of days between maintenance.',
        'custom_days.integer' => 'Days must be a whole number.',
        'custom_days.min' => 'Days must be at least 1.',
        'day_of_week.required_if' => 'Please select a day of the week.',
        'day_of_week.between' => 'The selected day of week is invalid.',
        'day_of_month.required_if' => 'Please enter a day of the month.',
        'day_of_month.between' => 'Day of month must be between 1 and 31.',
        'month.required_if' => 'Please select a month.',
        'month.between' => 'The selected month is invalid.',
        'month_day.required_if' => 'Please enter a day of the month.',
        'month_day.between' => 'Day must be between 1 and 31.',
        'priority.required' => 'Please select a priority.',
        'priority.in' => 'The selected priority is invalid.',
        'type.required' => 'Please select a type.',
        'type.in' => 'The selected type is invalid.',
        'assigned_to.required' => 'Please select a technician.',
        'assigned_to.exists' => 'The selected technician is invalid.',
        'status.required' => 'Please select a status.',
        'status.in' => 'The selected status is invalid.',
    ];

    protected $listeners = [
        'edit' => 'editSchedule',
        'delete' => 'delete',
        'calendarEventClick' => 'editSchedule'
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'task_id',
            'equipment_id',
            'line_id',
            'area_id',
            'scheduled_date',
            'frequency_type',
            'custom_days',
            'day_of_week',
            'day_of_month',
            'month',
            'month_day',
            'priority',
            'type',
            'assigned_to',
            'description',
            'notes',
            'status',
            'scheduleId',
            'isEditing'
        ]);
        $this->resetValidation();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->dispatch('openModal');
    }

    public function closeModal()
    {
        $this->dispatch('closeModal');
        $this->resetForm();
    }

    public function editSchedule($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $this->scheduleId = $schedule->id;
        $this->equipment_id = $schedule->equipment_id;
        $this->frequency_type = $schedule->frequency_type;
        $this->custom_days = $schedule->custom_days;
        $this->day_of_week = $schedule->day_of_week;
        $this->day_of_month = $schedule->day_of_month;
        $this->month = $schedule->month;
        $this->month_day = $schedule->month_day;
        $this->scheduled_date = $schedule->scheduled_date ? $schedule->scheduled_date->format('Y-m-d') : null;
        $this->priority = $schedule->priority;
        $this->type = $schedule->type;
        $this->assigned_to = $schedule->assigned_to;
        $this->description = $schedule->description;
        $this->notes = $schedule->notes;
        $this->status = $schedule->status;
        $this->isEditing = true;
        $this->dispatch('openModal');
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEditing) {
                $schedule = MaintenanceSchedule::findOrFail($this->scheduleId);
            } else {
                $schedule = new MaintenanceSchedule();
            }

            $schedule->equipment_id = $this->equipment_id;
            $schedule->frequency_type = $this->frequency_type;
            $schedule->custom_days = $this->custom_days;
            $schedule->day_of_week = $this->day_of_week;
            $schedule->day_of_month = $this->day_of_month;
            $schedule->month = $this->month;
            $schedule->month_day = $this->month_day;
            $schedule->scheduled_date = $this->scheduled_date;
            $schedule->priority = $this->priority;
            $schedule->type = $this->type;
            $schedule->assigned_to = $this->assigned_to;
            $schedule->description = $this->description;
            $schedule->notes = $this->notes;
            $schedule->status = $this->status;

            // Calculate next maintenance date
            $schedule->next_maintenance_date = $this->calculateNextMaintenanceDate($schedule);

            $schedule->save();

            $this->dispatch('notify', [
                'type' => $this->isEditing ? 'info' : 'success',
                'title' => $this->isEditing ? 'Agendamento Atualizado' : 'Agendamento Criado',
                'message' => $this->isEditing ? 'O agendamento foi atualizado com sucesso.' : 'Um novo agendamento foi criado com sucesso.'
            ]);

            $this->updateCalendarEvents();
            $this->closeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Erro',
                'message' => 'Ocorreu um erro ao salvar o agendamento. Por favor, tente novamente.'
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $schedule = MaintenanceSchedule::findOrFail($id);
            $schedule->delete();

            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Schedule Deleted',
                'message' => 'The maintenance schedule has been deleted successfully.'
            ]);

            $this->updateCalendarEvents();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while deleting the schedule. Please try again.'
            ]);
        }
    }

    public function calculateNextMaintenanceDate($schedule)
    {
        $baseDate = $schedule->scheduled_date ? Carbon::parse($schedule->scheduled_date) : Carbon::now();

        return match ($schedule->frequency_type) {
            'custom_days' => $baseDate->copy()->addDays($schedule->custom_days),
            'weekly' => $baseDate->copy()->next((int)$schedule->day_of_week),
            'monthly' => $baseDate->copy()->addMonth()->setDay((int)$schedule->day_of_month),
            'yearly' => $baseDate->copy()->setMonth((int)$schedule->month)->setDay((int)$schedule->month_day),
            default => Carbon::now()->addDay()
        };
    }

    public function updateCalendarEvents()
    {
        $events = MaintenanceSchedule::with('equipment')
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->frequencyFilter, function ($query) {
                return $query->where('frequency_type', $this->frequencyFilter);
            })
            ->get()
            ->map(function ($schedule) {
                $color = match ($schedule->status) {
                    'scheduled' => '#10B981',
                    'in_progress' => '#4F46E5',
                    'completed' => '#059669',
                    'cancelled' => '#6B7280',
                    default => '#4F46E5'
                };

                $borderColor = match ($schedule->status) {
                    'scheduled' => '#059669',
                    'in_progress' => '#4338CA',
                    'completed' => '#059669',
                    'cancelled' => '#4B5563',
                    default => '#4338CA'
                };

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->equipment->name,
                    'start' => $schedule->next_maintenance_date->format('Y-m-d'),
                    'backgroundColor' => $color,
                    'borderColor' => $borderColor,
                    'textColor' => '#ffffff',
                    'extendedProps' => [
                        'equipment' => $schedule->equipment->name,
                        'serial_number' => $schedule->equipment->serial_number,
                        'frequency' => $this->getFrequencyText($schedule),
                        'lastMaintenance' => $schedule->scheduled_date ? $schedule->scheduled_date->format('d/m/Y') : 'Not set',
                        'description' => $schedule->description,
                        'status' => $schedule->status
                    ]
                ];
            });

        $this->dispatch('calendarUpdated', $events);
    }

    public function getFrequencyText($schedule)
    {
        return match ($schedule->frequency_type) {
            'custom_days' => "Every {$schedule->custom_days} days",
            'weekly' => "Weekly on " . Carbon::create()->dayOfWeek($schedule->day_of_week)->format('l'),
            'monthly' => "Monthly on day {$schedule->day_of_month}",
            'yearly' => "Yearly on " . Carbon::create()->month($schedule->month)->format('F') . " {$schedule->month_day}",
            default => "Unknown frequency"
        };
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->updateCalendarEvents();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->updateCalendarEvents();
    }

    public function updatedFrequencyFilter()
    {
        $this->resetPage();
        $this->updateCalendarEvents();
    }

    public function render()
    {
        $schedules = MaintenanceSchedule::with(['equipment', 'line', 'area', 'task', 'assignedTo'])
            ->when($this->search, function ($query) {
                return $query->whereHas('equipment', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('serial_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->frequencyFilter, function ($query) {
                return $query->where('frequency_type', $this->frequencyFilter);
            })
            ->orderBy('scheduled_date')
            ->paginate(10);

        return view('livewire.maintenance-plan', [
            'schedules' => $schedules,
            'equipment' => Equipment::orderBy('name')->get(),
            'lines' => Line::orderBy('name')->get(),
            'areas' => Area::orderBy('name')->get(),
            'tasks' => MaintenanceTask::orderBy('name')->get(),
            'technicians' => User::role('technician')->orderBy('name')->get(),
        ]);
    }

    public function updatedFrequencyType()
    {
        $this->reset(['custom_days', 'day_of_week', 'day_of_month', 'month_day', 'month']);
    }
}

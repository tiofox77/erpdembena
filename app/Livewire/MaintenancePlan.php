<?php

namespace App\Livewire;

use App\Models\MaintenancePlan as MaintenancePlanModel;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use App\Models\MaintenanceTask;
use App\Models\Holiday;
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
    public $showHolidayWarning = false;
    public $originalScheduledDate = null;
    public $suggestedDate = null;
    public $holidayTitle = null;

    // Form fields
    public $task_id;
    public $equipment_id;
    public $line_id;
    public $area_id;
    public $scheduled_date;
    public $frequency_type = 'custom';
    public $custom_days;
    public $day_of_week;
    public $day_of_month;
    public $month;
    public $month_day;
    public $priority = 'medium';
    public $type = 'preventive';
    public $assigned_to;
    public $description;
    public $notes;
    public $status = 'in_progress';

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $frequencyFilter = '';

    // Search for technicians
    public $technicianSearch = '';
    public $filteredTechnicians = [];

    protected $rules = [
        'task_id' => 'required|exists:maintenance_tasks,id',
        'equipment_id' => 'required|exists:maintenance_equipment,id',
        'line_id' => 'nullable|exists:maintenance_lines,id',
        'area_id' => 'nullable|exists:maintenance_areas,id',
        'scheduled_date' => 'required|date',
        'frequency_type' => 'required|in:once,daily,custom,weekly,monthly,yearly',
        'custom_days' => 'required_if:frequency_type,custom|nullable|integer|min:1',
        'day_of_week' => 'required_if:frequency_type,weekly|nullable|integer|min:0|max:6',
        'day_of_month' => 'required_if:frequency_type,monthly|nullable|integer|min:1|max:31',
        'month' => 'required_if:frequency_type,yearly|nullable|integer|min:1|max:12',
        'month_day' => 'required_if:frequency_type,yearly|nullable|integer|min:1|max:31',
        'priority' => 'required|in:low,medium,high,critical',
        'type' => 'required|in:preventive,predictive,other',
        'assigned_to' => 'nullable|exists:users,id',
        'description' => 'nullable|string',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,completed,cancelled',
    ];

    protected $messages = [
        'task_id.required' => 'Please select a task.',
        'task_id.exists' => 'The selected task is invalid.',
        'equipment_id.required' => 'Please select equipment.',
        'equipment_id.exists' => 'The selected equipment is invalid.',
        'line_id.exists' => 'The selected line is invalid.',
        'area_id.exists' => 'The selected area is invalid.',
        'scheduled_date.required' => 'Please select a scheduled date.',
        'scheduled_date.date' => 'Please enter a valid date.',
        'frequency_type.required' => 'Please select a frequency type.',
        'frequency_type.in' => 'The selected frequency type is invalid.',
        'custom_days.required_if' => 'Please enter the number of days between occurrences.',
        'custom_days.integer' => 'Days must be a whole number.',
        'custom_days.min' => 'Days must be at least 1.',
        'day_of_week.required_if' => 'Please select a day of the week.',
        'day_of_week.integer' => 'Day of the week must be a whole number.',
        'day_of_week.min' => 'Day of the week must be between 0 and 6.',
        'day_of_week.max' => 'Day of the week must be between 0 and 6.',
        'day_of_month.required_if' => 'Please enter a day of the month.',
        'day_of_month.integer' => 'Day of the month must be a whole number.',
        'day_of_month.min' => 'Day of the month must be between 1 and 31.',
        'day_of_month.max' => 'Day of the month must be between 1 and 31.',
        'month.required_if' => 'Please select a month.',
        'month.integer' => 'Month must be a whole number.',
        'month.min' => 'Month must be between 1 and 12.',
        'month.max' => 'Month must be between 1 and 12.',
        'month_day.required_if' => 'Please enter a day of the month.',
        'month_day.integer' => 'Day of the month must be a whole number.',
        'month_day.min' => 'Day of the month must be between 1 and 31.',
        'month_day.max' => 'Day of the month must be between 1 and 31.',
        'priority.required' => 'Please select a priority.',
        'priority.in' => 'The selected priority is invalid.',
        'type.required' => 'Please select a type.',
        'type.in' => 'The selected type is invalid.',
        'assigned_to.exists' => 'The selected technician is invalid.',
        'status.required' => 'Please select a status.',
        'status.in' => 'The selected status is invalid.',
    ];

    protected $listeners = [
        'edit' => 'editSchedule',
        'delete' => 'delete',
        'calendarEventClick' => 'editSchedule',
        'createOnDate' => 'createOnDate',
        'openPlanModal' => 'openPlanModalWithDate',
        'acceptSuggestedDate' => 'acceptSuggestedDate'
    ];

    public function mount()
    {
        $this->resetForm();
        $this->updateCalendarEvents();
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
            'isEditing',
            'showHolidayWarning',
            'originalScheduledDate',
            'suggestedDate',
            'holidayTitle'
        ]);
        $this->resetValidation();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->dispatch('showModalUpdated');
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function editSchedule($id)
    {
        $schedule = MaintenancePlanModel::findOrFail($id);
        $this->scheduleId = $schedule->id;
        $this->task_id = $schedule->task_id;
        $this->equipment_id = $schedule->equipment_id;
        $this->line_id = $schedule->line_id;
        $this->area_id = $schedule->area_id;
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
        $this->showModal = true;

        // Check if the scheduled date is a Sunday or holiday
        $this->checkScheduledDate();

        $this->dispatch('showModalUpdated');
    }

    /**
     * Check if the scheduled date is a holiday or Sunday and suggest a new date
     *
     * @return bool
     */
    public function checkScheduledDate()
    {
        if (!$this->scheduled_date) {
            return false;
        }

        $date = Carbon::parse($this->scheduled_date);

        // Check if the date is a Sunday
        if ($this->isSunday($date)) {
            $this->originalScheduledDate = $this->scheduled_date;
            $this->suggestedDate = $this->getNextValidWorkingDate($date)->format('Y-m-d');
            $this->holidayTitle = "Sunday (Rest Day)";
            $this->showHolidayWarning = true;
            return true;
        }

        // Check if the date is a holiday
        if ($this->isHoliday($date)) {
            // Find the holiday title to display to the user
            $holiday = Holiday::where(function ($query) use ($date) {
                $query->where('date', $date->format('Y-m-d'))
                    ->orWhere(function ($q) use ($date) {
                        $q->whereMonth('date', $date->month)
                          ->whereDay('date', $date->day)
                          ->where('is_recurring', true);
                    });
            })
                ->where('is_active', true)
                ->first();

            $this->originalScheduledDate = $this->scheduled_date;
            $this->suggestedDate = $this->getNextValidWorkingDate($date)->format('Y-m-d');
            $this->holidayTitle = $holiday ? $holiday->title : "Holiday";
            $this->showHolidayWarning = true;
            return true;
        }

        return false;
    }

    /**
     * Check if a date is a holiday
     *
     * @param Carbon $date
     * @return bool
     */
    protected function isHoliday(Carbon $date)
    {
        // Check for specific date fixed holidays
        $fixedHoliday = Holiday::where('date', $date->format('Y-m-d'))
            ->where('is_active', true)
            ->exists();

        if ($fixedHoliday) {
            return true;
        }

        // Check for recurring holidays (same date every year)
        $recurringHoliday = Holiday::whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->where('is_recurring', true)
            ->where('is_active', true)
            ->exists();

        return $recurringHoliday;
    }

    /**
     * Check if a date is a Sunday
     *
     * @param Carbon $date
     * @return bool
     */
    protected function isSunday(Carbon $date)
    {
        return $date->dayOfWeek === Carbon::SUNDAY;
    }

    /**
     * Get next valid working date (not a holiday or Sunday)
     *
     * @param Carbon $date
     * @return Carbon
     */
    protected function getNextValidWorkingDate(Carbon $date)
    {
        $nextDate = $date->copy();

        // Continue advancing until finding a valid date
        while ($this->isHoliday($nextDate) || $this->isSunday($nextDate)) {
            $nextDate->addDay();
        }

        return $nextDate;
    }

    /**
     * Accept the suggested date
     */
    public function acceptSuggestedDate()
    {
        $this->scheduled_date = $this->suggestedDate;
        $this->showHolidayWarning = false;
        $this->reset(['originalScheduledDate', 'suggestedDate', 'holidayTitle']);
    }

    /**
     * Keep original date and dismiss warning
     */
    public function keepOriginalDate()
    {
        $this->showHolidayWarning = false;
        $this->reset(['originalScheduledDate', 'suggestedDate', 'holidayTitle']);
    }

    /**
     * Listener for when the scheduled date is changed
     */
    public function updatedScheduledDate()
    {
        // Check if the selected date is a Sunday or holiday
        $this->checkScheduledDate();
    }

    public function save()
    {
        try {
            // Validate the form fields
            $validatedData = $this->validate();

            // IMPORTANT: Check if the selected date is a holiday or Sunday before saving
            // If it is, display a warning and don't save yet
            if ($this->checkScheduledDate()) {
                return;
            }

            // Handle new or update
            if ($this->isEditing) {
                $schedule = MaintenancePlanModel::findOrFail($this->scheduleId);
                $schedule->update($validatedData);
            } else {
                $schedule = MaintenancePlanModel::create($validatedData);
            }

            // Calculate next maintenance date
            $nextMaintenanceDate = $this->calculateNextMaintenanceDate($schedule);
            $schedule->next_maintenance_date = $nextMaintenanceDate;
            $schedule->save();

            // Send notification
            $msg = $this->isEditing ? 'The maintenance plan was successfully updated.' : 'A new maintenance plan was successfully created.';
            $title = $this->isEditing ? 'Plan Updated' : 'Plan Created';
            $type = $this->isEditing ? 'info' : 'success';

            $this->js("toastr.$type('$msg', '$title')");

            $this->closeModal();
            $this->updateCalendarEvents();
        } catch (\Exception $e) {
            // Send error notification
            $msg = 'An error occurred while saving the plan: ' . $e->getMessage();
            $this->js("toastr.error('$msg', 'Error')");
        }
    }

    /**
     * Calculate the next maintenance date based on frequency,
     * avoiding holidays and Sundays
     *
     * @param MaintenancePlanModel $schedule
     * @return Carbon
     */
    protected function calculateNextMaintenanceDate($schedule)
    {
        $baseDate = $schedule->scheduled_date ?? now();
        $nextDate = null;

        switch ($schedule->frequency_type) {
            case 'once':
                $nextDate = $baseDate->copy(); // Not recurring, return the scheduled date
                break;

            case 'daily':
                $nextDate = $baseDate->copy()->addDay();
                break;

            case 'custom':
                $nextDate = $baseDate->copy()->addDays($schedule->custom_days);
                break;

            case 'weekly':
                $nextDate = $baseDate->copy()->addWeek();
                if (!is_null($schedule->day_of_week)) {
                    // Adjust to the specified day of the week
                    while ($nextDate->dayOfWeek != $schedule->day_of_week) {
                        $nextDate->addDay();
                    }
                }
                break;

            case 'monthly':
                $nextDate = $baseDate->copy()->addMonth();
                // Ensure we don't exceed the days in the month
                if (!is_null($schedule->day_of_month)) {
                    $daysInMonth = $nextDate->daysInMonth;
                    $day = min($schedule->day_of_month, $daysInMonth);
                    $nextDate->setDay($day);
                }
                break;

            case 'yearly':
                $nextDate = $baseDate->copy()->addYear();
                // Handle February 29 in non-leap years
                if (!is_null($schedule->month) && !is_null($schedule->month_day)) {
                    if ($schedule->month == 2 && $schedule->month_day == 29 && !$nextDate->isLeapYear()) {
                        $nextDate->setMonth(2)->setDay(28);
                    } else {
                        $nextDate->setMonth($schedule->month)->setDay($schedule->month_day);
                    }
                }
                break;

            default:
                $nextDate = $baseDate->copy();
                break;
        }

        // Check and adjust to the next valid date (not a holiday and not a Sunday)
        while ($this->isHoliday($nextDate) || $this->isSunday($nextDate)) {
            $nextDate->addDay();
        }

        return $nextDate;
    }

    public function delete($id)
    {
        try {
            $schedule = MaintenancePlanModel::findOrFail($id);
            $schedule->delete();

            // Send notification
            $this->js("toastr.warning('The maintenance plan was successfully deleted.', 'Plan Deleted')");

            $this->updateCalendarEvents();
        } catch (\Exception $e) {
            // Send error notification
            $msg = 'An error occurred while deleting the plan. Please try again.';
            $this->js("toastr.error('$msg', 'Error')");
        }
    }

    public function getFrequencyText($schedule)
    {
        return match ($schedule->frequency_type) {
            'once' => "Once",
            'daily' => "Daily",
            'custom' => "Every {$schedule->custom_days} days",
            'weekly' => "Weekly" . (isset($schedule->day_of_week) ? " (". Carbon::getDays()[$schedule->day_of_week] .")" : ""),
            'monthly' => "Monthly" . (isset($schedule->day_of_month) ? " (day {$schedule->day_of_month})" : ""),
            'yearly' => "Yearly" . (isset($schedule->month) && isset($schedule->month_day) ? " (" . Carbon::getMonthsOfYear()[$schedule->month] . " {$schedule->month_day})" : ""),
            default => "Unknown frequency"
        };
    }

    public function updateCalendarEvents()
    {
        // Generate calendar events from maintenance plans
        $events = MaintenancePlanModel::with(['equipment', 'task', 'line', 'area', 'assignedTo'])
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->when($this->frequencyFilter, function ($query) {
                return $query->where('frequency_type', $this->frequencyFilter);
            })
            ->get()
            ->map(function ($schedule) {
                // Determine color based on status
                $color = match ($schedule->status) {
                    'pending' => '#10B981',     // green
                    'in_progress' => '#3B82F6', // blue
                    'completed' => '#059669',   // dark green
                    'cancelled' => '#6B7280',   // gray
                    default => '#10B981'        // default green
                };

                // Get title from task or use equipment name if task is not available
                $title = 'No Task';
                if ($schedule->task) {
                    $title = $schedule->task->title ?? $schedule->task->name ?? 'No Task';
                }

                // Format the event data for FullCalendar
                return [
                    'id' => $schedule->id,
                    'title' => $title,
                    'start' => $schedule->scheduled_date->format('Y-m-d'),
                    'end' => $schedule->scheduled_date->format('Y-m-d'),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'textColor' => '#FFFFFF',
                    'allDay' => true,
                    'extendedProps' => [
                        'equipment' => $schedule->equipment ? $schedule->equipment->name : 'No Equipment',
                        'serial_number' => $schedule->equipment ? $schedule->equipment->serial_number : 'N/A',
                        'task' => $title,
                        'line' => $schedule->line ? $schedule->line->name : 'N/A',
                        'area' => $schedule->area ? $schedule->area->name : 'N/A',
                        'assignedTo' => $schedule->assignedTo ? $schedule->assignedTo->name : 'Unassigned',
                        'frequency' => $this->getFrequencyText($schedule),
                        'lastMaintenance' => $schedule->last_maintenance_date ? $schedule->last_maintenance_date->format('M d, Y') : 'None',
                        'nextMaintenance' => $schedule->next_maintenance_date ? $schedule->next_maintenance_date->format('M d, Y') : 'N/A',
                        'status' => $schedule->status,
                        'priority' => $schedule->priority,
                        'type' => $schedule->type,
                        'description' => $schedule->description,
                    ]
                ];
            })
            ->toArray();

        // Add holidays to the calendar
        $holidays = Holiday::where('is_active', true)->get();

        foreach ($holidays as $holiday) {
            $holidayDate = Carbon::parse($holiday->date);

            // For recurring holidays, add for the current year
            if ($holiday->is_recurring) {
                $currentYear = Carbon::now()->year;
                $holidayThisYear = Carbon::createFromDate(
                    $currentYear,
                    $holidayDate->month,
                    min($holidayDate->day, Carbon::createFromDate($currentYear, $holidayDate->month, 1)->daysInMonth)
                );

                $events[] = [
                    'id' => 'holiday-' . $holiday->id,
                    'title' => '🎉 ' . $holiday->title,
                    'start' => $holidayThisYear->format('Y-m-d'),
                    'end' => $holidayThisYear->format('Y-m-d'),
                    'backgroundColor' => '#F87171', // Light red for holidays
                    'borderColor' => '#EF4444',
                    'textColor' => '#FFFFFF',
                    'allDay' => true,
                    'extendedProps' => [
                        'isHoliday' => true,
                        'description' => $holiday->description ?? 'Holiday',
                        'recurring' => true
                    ]
                ];
            } else {
                // Fixed (non-recurring) holidays
                $events[] = [
                    'id' => 'holiday-' . $holiday->id,
                    'title' => '🎉 ' . $holiday->title,
                    'start' => $holidayDate->format('Y-m-d'),
                    'end' => $holidayDate->format('Y-m-d'),
                    'backgroundColor' => '#F87171', // Light red for holidays
                    'borderColor' => '#EF4444',
                    'textColor' => '#FFFFFF',
                    'allDay' => true,
                    'extendedProps' => [
                        'isHoliday' => true,
                        'description' => $holiday->description ?? 'Holiday',
                        'recurring' => false
                    ]
                ];
            }
        }

        // Mark Sundays as rest days
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();

        for ($date = $startOfYear->copy(); $date->lte($endOfYear); $date->addDay()) {
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                $events[] = [
                    'id' => 'sunday-' . $date->format('Y-m-d'),
                    'title' => '😴 Sunday - Rest Day',
                    'start' => $date->format('Y-m-d'),
                    'end' => $date->format('Y-m-d'),
                    'backgroundColor' => '#CBD5E1', // Light gray for Sundays
                    'borderColor' => '#94A3B8',
                    'textColor' => '#1E293B',
                    'allDay' => true,
                    'extendedProps' => [
                        'isSunday' => true,
                        'description' => 'Weekly rest day'
                    ]
                ];
            }
        }

        // Add dummy events for testing if no events exist
        if (empty($events)) {
            $events = $this->getDummyEvents();
        }

        // Dispatch event with calendar event data
        $this->dispatch('calendarUpdated', $events);
    }

    /**
     * Generate dummy events for testing
     *
     * @return array
     */
    private function getDummyEvents()
    {
        $currentMonth = now()->format('Y-m');
        $dummyEvents = [];

        // Add some test events
        $dummyEvents[] = [
            'id' => 'test-1',
            'title' => 'test',
            'start' => $currentMonth . '-20',
            'backgroundColor' => '#10B981',
            'borderColor' => '#10B981',
            'textColor' => '#FFFFFF',
            'allDay' => true,
            'extendedProps' => [
                'equipment' => 'Test Equipment',
                'serial_number' => 'TEST-123',
                'task' => 'test',
                'frequency' => 'Once',
                'status' => 'pending',
                'priority' => 'medium',
                'type' => 'preventive',
                'description' => 'Test maintenance task'
            ]
        ];

        $dummyEvents[] = [
            'id' => 'test-2',
            'title' => 'test',
            'start' => $currentMonth . '-22',
            'backgroundColor' => '#10B981',
            'borderColor' => '#10B981',
            'textColor' => '#FFFFFF',
            'allDay' => true,
            'extendedProps' => [
                'equipment' => 'Test Equipment',
                'serial_number' => 'TEST-123',
                'task' => 'test',
                'frequency' => 'Once',
                'status' => 'pending',
                'priority' => 'medium',
                'type' => 'preventive',
                'description' => 'Test maintenance task'
            ]
        ];

        return $dummyEvents;
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

    public function updatedFrequencyType()
    {
        // Reset frequency-related fields that are not relevant
        // for the selected frequency type
        switch ($this->frequency_type) {
            case 'once':
            case 'daily':
                $this->reset(['custom_days', 'day_of_week', 'day_of_month', 'month', 'month_day']);
                break;
            case 'custom':
                $this->reset(['day_of_week', 'day_of_month', 'month', 'month_day']);
                if (empty($this->custom_days)) {
                    // Set a default value for custom_days
                    $this->custom_days = 7; // Default value of 7 days
                }
                break;
            case 'weekly':
                $this->reset(['custom_days', 'day_of_month', 'month', 'month_day']);
                if (is_null($this->day_of_week)) {
                    // Set default day of week to current day (except Sunday)
                    $today = now()->dayOfWeek;
                    $this->day_of_week = $today === Carbon::SUNDAY ? Carbon::MONDAY : $today;
                }
                break;
            case 'monthly':
                $this->reset(['custom_days', 'day_of_week', 'month', 'month_day']);
                if (is_null($this->day_of_month)) {
                    // Set the default day of the month to the current day
                    $this->day_of_month = now()->day;
                }
                break;
            case 'yearly':
                $this->reset(['custom_days', 'day_of_week', 'day_of_month']);
                if (is_null($this->month) || is_null($this->month_day)) {
                    // Set the default month and day to the current date
                    $this->month = now()->month;
                    $this->month_day = now()->day;
                }
                break;
        }
    }

    /**
     * Filter technicians based on search term
     */
    public function updatedTechnicianSearch()
    {
        if (empty($this->technicianSearch)) {
            $this->filteredTechnicians = [];
            return;
        }

        $this->filteredTechnicians = User::where(function($query) {
                $query->where('name', 'like', '%' . $this->technicianSearch . '%')
                      ->orWhere('email', 'like', '%' . $this->technicianSearch . '%')
                      ->orWhere('full_name', 'like', '%' . $this->technicianSearch . '%');
            })
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Set the selected technician
     */
    public function selectTechnician($id)
    {
        $this->assigned_to = $id;
        $this->technicianSearch = '';
        $this->filteredTechnicians = [];
    }

    /**
     * Edit maintenance plan schedule
     */
    public function edit($id)
    {
        $this->editSchedule($id);
    }

    /**
     * Open history notes modal for a maintenance plan
     */
    public function openHistory($id)
    {
        // Dispatch event to open the notes modal in view-only mode
        $this->dispatch('openHistoryModal', $id);
    }

    /**
     * Handle calendar date click to create a new maintenance plan on that date
     *
     * @param string $date
     * @return void
     */
    public function createOnDate($date)
    {
        $this->resetForm();

        // Check if the date is a holiday or Sunday
        $carbonDate = Carbon::parse($date);
        if ($this->isSunday($carbonDate)) {
            // Suggest next valid date
            $suggestedDate = $this->getNextValidWorkingDate($carbonDate);

            // Inform the user and ask if they want to use the suggested date
            $this->originalScheduledDate = $date;
            $this->scheduled_date = $suggestedDate->format('Y-m-d');

            $this->holidayTitle = "Sunday (Rest Day)";
            $msg = "The selected date is a Sunday (rest day). The plan has been scheduled for the next available date: " . $suggestedDate->format('m/d/Y');
            $this->suggestedDate = $suggestedDate->format('Y-m-d');
            $this->showHolidayWarning = true;
        }
        else if ($this->isHoliday($carbonDate)) {
            // Find the holiday title
            $holiday = Holiday::where(function ($query) use ($carbonDate) {
                $query->where('date', $carbonDate->format('Y-m-d'))
                    ->orWhere(function ($q) use ($carbonDate) {
                        $q->whereMonth('date', $carbonDate->month)
                          ->whereDay('date', $carbonDate->day)
                          ->where('is_recurring', true);
                    });
            })
                ->where('is_active', true)
                ->first();

            // Suggest next valid date
            $suggestedDate = $this->getNextValidWorkingDate($carbonDate);

            $this->originalScheduledDate = $date;
            $this->scheduled_date = $suggestedDate->format('Y-m-d');
            $this->holidayTitle = $holiday ? $holiday->title : "Holiday";
            $this->suggestedDate = $suggestedDate->format('Y-m-d');
            $this->showHolidayWarning = true;
        }
        else {
            $this->scheduled_date = $date;
        }

        $this->showModal = true;
        $this->dispatch('showModalUpdated');
    }

    /**
     * Opens the maintenance plan modal with a pre-filled date
     */
    public function openPlanModalWithDate($date)
    {
        $this->createOnDate($date);
    }

    public function render()
    {
        $schedules = MaintenancePlanModel::with(['equipment', 'line', 'area', 'task', 'assignedTo'])
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
            'equipment' => MaintenanceEquipment::all(),
            'lines' => MaintenanceLine::all(),
            'areas' => MaintenanceArea::all(),
            'tasks' => MaintenanceTask::all(),
            'technicians' => User::all(),
            'frequencies' => [
                'once' => 'Once',
                'daily' => 'Daily',
                'custom' => 'Custom (days)',
                'weekly' => 'Weekly',
                'monthly' => 'Monthly',
                'yearly' => 'Yearly'
            ],
            'priorities' => [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'critical' => 'Critical'
            ],
            'types' => [
                'preventive' => 'Preventive',
                'predictive' => 'Predictive',
                'other' => 'Other'
            ],
            'statuses' => [
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ]
        ]);
    }
}

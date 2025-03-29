<?php

namespace App\Livewire;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use App\Models\MaintenancePlan;
use App\Models\Holiday;
use Livewire\Component;

class MaintenanceScheduleCalendar extends Component
{
    public $currentMonth;
    public $currentYear;
    public $calendarTitle;
    public $calendarDays = [];
    public $events = [];
    public $selectedDate;
    public $selectedDateEvents = [];
    public $holidays = [];

    // Array of task colors
    private $taskColors = [
        'bg-blue-100 text-blue-800',
        'bg-green-100 text-green-800',
        'bg-yellow-100 text-yellow-800',
        'bg-red-100 text-red-800',
        'bg-purple-100 text-purple-800',
        'bg-pink-100 text-pink-800',
        'bg-indigo-100 text-indigo-800',
        'bg-cyan-100 text-cyan-800',
        'bg-teal-100 text-teal-800',
        'bg-orange-100 text-orange-800',
        'bg-amber-100 text-amber-800',
        'bg-lime-100 text-lime-800',
        'bg-emerald-100 text-emerald-800',
        'bg-sky-100 text-sky-800',
        'bg-fuchsia-100 text-fuchsia-800',
        'bg-rose-100 text-rose-800',
    ];

    // Cache of colors assigned by task ID
    private $assignedColors = [];

    protected $listeners = [
        'maintenanceUpdated' => 'loadEvents',
        'calendarUpdated' => 'receiveEvents'
    ];

    // Function to convert date to calendar format
    public function formatDate($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    // Component initialization
    public function mount()
    {
        $today = Carbon::today();
        $this->currentMonth = $today->month;
        $this->currentYear = $today->year;
        $this->selectedDate = $today->format('Y-m-d');

        $this->loadHolidays();
        $this->generateCalendar();
        $this->loadEvents();
    }

    /**
     * Load holidays for the current year
     */
    public function loadHolidays()
    {
        $this->holidays = [];

        // Get fixed date holidays
        $fixedHolidays = Holiday::where('is_active', true)
            ->whereYear('date', $this->currentYear)
            ->get();

        foreach ($fixedHolidays as $holiday) {
            $date = Carbon::parse($holiday->date);
            $this->holidays[$date->format('Y-m-d')] = [
                'title' => $holiday->title,
                'recurring' => $holiday->is_recurring
            ];
        }

        // Get recurring holidays for the current year
        $recurringHolidays = Holiday::where('is_active', true)
            ->where('is_recurring', true)
            ->get();

        foreach ($recurringHolidays as $holiday) {
            $originalDate = Carbon::parse($holiday->date);
            $thisYearDate = Carbon::createFromDate(
                $this->currentYear,
                $originalDate->month,
                min($originalDate->day, Carbon::createFromDate($this->currentYear, $originalDate->month, 1)->daysInMonth)
            );

            $this->holidays[$thisYearDate->format('Y-m-d')] = [
                'title' => $holiday->title,
                'recurring' => true
            ];
        }
    }

    /**
     * Generate the calendar for the current month
     */
    public function generateCalendar()
    {
        $this->calendarDays = [];

        // Set the first day of the month
        $firstDayOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        $lastDayOfMonth = $firstDayOfMonth->copy()->endOfMonth();

        // Set the calendar title in English (e.g., "March 2025")
        Carbon::setLocale('en');
        $this->calendarTitle = $firstDayOfMonth->format('F Y');

        // Get the day of the week for the first day (0 = Sunday, 6 = Saturday)
        $firstDayWeekday = $firstDayOfMonth->dayOfWeek;

        // Days from the previous month to fill the beginning of the calendar
        $prevMonth = $firstDayOfMonth->copy()->subMonth();
        $daysInPrevMonth = $prevMonth->daysInMonth;

        // Add days from the previous month if needed
        for ($i = $firstDayWeekday; $i > 0; $i--) {
            $day = $daysInPrevMonth - $i + 1;
            $date = Carbon::createFromDate($prevMonth->year, $prevMonth->month, $day);
            $dateStr = $date->format('Y-m-d');

            $this->calendarDays[] = [
                'date' => $dateStr,
                'day' => $day,
                'isCurrentMonth' => false,
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isSunday(),
                'isHoliday' => isset($this->holidays[$dateStr]),
                'holidayTitle' => isset($this->holidays[$dateStr]) ? $this->holidays[$dateStr]['title'] : null,
                'isRestDay' => $date->isSunday() || isset($this->holidays[$dateStr])
            ];
        }

        // Add days from the current month
        for ($day = 1; $day <= $lastDayOfMonth->day; $day++) {
            $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, $day);
            $dateStr = $date->format('Y-m-d');

            $this->calendarDays[] = [
                'date' => $dateStr,
                'day' => $day,
                'isCurrentMonth' => true,
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isSunday(),
                'isHoliday' => isset($this->holidays[$dateStr]),
                'holidayTitle' => isset($this->holidays[$dateStr]) ? $this->holidays[$dateStr]['title'] : null,
                'isRestDay' => $date->isSunday() || isset($this->holidays[$dateStr])
            ];
        }

        // Calculate how many days we need from the next month
        $daysFromNextMonth = 42 - count($this->calendarDays); // 42 = 6 weeks * 7 days

        // Add days from the next month if needed
        $nextMonth = $lastDayOfMonth->copy()->addMonth();
        for ($day = 1; $day <= $daysFromNextMonth; $day++) {
            $date = Carbon::createFromDate($nextMonth->year, $nextMonth->month, $day);
            $dateStr = $date->format('Y-m-d');

            $this->calendarDays[] = [
                'date' => $dateStr,
                'day' => $day,
                'isCurrentMonth' => false,
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isSunday(),
                'isHoliday' => isset($this->holidays[$dateStr]),
                'holidayTitle' => isset($this->holidays[$dateStr]) ? $this->holidays[$dateStr]['title'] : null,
                'isRestDay' => $date->isSunday() || isset($this->holidays[$dateStr])
            ];
        }
    }

    /**
     * Returns a color for a specific task
     * @param int $taskId Task ID
     * @param string $type Task type
     * @return string CSS class for color
     */
    private function getEventColor($taskId, $type)
    {
        // If we already have an assigned color for this task, return it
        if (isset($this->assignedColors[$taskId])) {
            return $this->assignedColors[$taskId];
        }

        // Choose a color based on a hash of the task ID
        $index = $taskId % count($this->taskColors);

        // Try to find a color not recently used
        $attempts = 0;
        $usedColors = array_values($this->assignedColors);

        while (in_array($this->taskColors[$index], $usedColors) && $attempts < 5) {
            $index = ($index + 1) % count($this->taskColors);
            $attempts++;
        }

        // Assign and store the selected color
        $this->assignedColors[$taskId] = $this->taskColors[$index];

        return $this->assignedColors[$taskId];
    }

    /**
     * Receive events from parent component
     */
    public function receiveEvents($events)
    {
        // Format all events based on dates and update display
        $this->processEvents($events);
        $this->updateSelectedDateEvents();
    }

    /**
     * Process events from an array into the calendar format
     */
    private function processEvents($eventsList)
    {
        $this->events = [];

        foreach ($eventsList as $event) {
            $formattedDate = isset($event['start']) ? $this->formatDate($event['start']) : null;

            if ($formattedDate) {
                // Check if this is a holiday or rest day marker
                if (isset($event['extendedProps']['isHoliday']) || isset($event['extendedProps']['isSunday'])) {
                    // These events are just markers, don't display as tasks
                    continue;
                }

                // Get color for this task
                $colorClass = isset($event['id']) ? $this->getEventColor($event['id'], $event['extendedProps']['type'] ?? 'default') : 'bg-gray-100 text-gray-800';

                $this->events[$formattedDate][] = [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'equipment' => $event['extendedProps']['equipment'] ?? 'Equipment',
                    'status' => $event['extendedProps']['status'] ?? 'pending',
                    'type' => $event['extendedProps']['type'] ?? 'default',
                    'priority' => $event['extendedProps']['priority'] ?? 'medium',
                    'description' => $event['extendedProps']['description'] ?? '',
                    'frequency' => $event['extendedProps']['frequency'] ?? 'once',
                    'color' => $colorClass,
                ];
            }
        }
    }

    // Load events for the current month
    public function loadEvents()
    {
        // Define the first and last day of the month to fetch events
        $startDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Fetch ALL active maintenance events
        try {
            // First load all active maintenance plans
            $maintenancePlans = MaintenancePlan::with(['equipment', 'task'])
                ->where('status', '!=', 'cancelled')
                ->get();

            // Clear existing events
            $this->events = [];

            foreach ($maintenancePlans as $plan) {
                // Generate occurrences for this plan based on frequency
                $occurrences = $this->generateOccurrences($plan, $startDate, $endDate);

                // Process each occurrence that falls in this month
                foreach ($occurrences as $date) {
                    $formattedDate = $this->formatDate($date);

                    // Skip if the date is a Sunday or a holiday (rest day)
                    $isRestDay = Carbon::parse($formattedDate)->isSunday() || isset($this->holidays[$formattedDate]);
                    if ($isRestDay) {
                        continue;
                    }

                    // Get color for this task
                    $colorClass = $this->getEventColor($plan->id, $plan->type);

                    $this->events[$formattedDate][] = [
                        'id' => $plan->id,
                        'title' => $plan->task ? $plan->task->title : 'Maintenance',
                        'equipment' => $plan->equipment ? $plan->equipment->name : 'Equipment',
                        'status' => $plan->status,
                        'type' => $plan->type,
                        'priority' => $plan->priority,
                        'description' => $plan->description,
                        'frequency' => $plan->frequency_type,
                        'color' => $colorClass,
                    ];
                }
            }

            // Load events for the selected date
            $this->updateSelectedDateEvents();

        } catch (\Exception $e) {
            // In case of error, log but don't display events
            // Log::error('Error loading events: ' . $e->getMessage());
        }
    }

    /**
     * Generates all occurrences of a maintenance plan within a period
     * based on the configured frequency type
     *
     * @param MaintenancePlan $plan The maintenance plan
     * @param Carbon $startDate Start date of the period
     * @param Carbon $endDate End date of the period
     * @return array Array of Carbon objects with occurrence dates
     */
    private function generateOccurrences($plan, $startDate, $endDate)
    {
        $occurrences = [];
        $scheduledDate = Carbon::parse($plan->scheduled_date);

        // If the scheduled date is outside the period and after the end of the period,
        // we won't have occurrences of this plan this month
        if ($scheduledDate->greaterThan($endDate)) {
            return $occurrences;
        }

        // For 'once' type plans (one-time)
        if ($plan->frequency_type === 'once') {
            // If it falls within the period, add it
            if ($scheduledDate->greaterThanOrEqualTo($startDate) && $scheduledDate->lessThanOrEqualTo($endDate)) {
                // Check if it's not a rest day
                $isRestDay = $scheduledDate->isSunday() || isset($this->holidays[$scheduledDate->format('Y-m-d')]);
                if (!$isRestDay) {
                    $occurrences[] = $scheduledDate;
                } else {
                    // If it's a rest day, find the next valid working day
                    $nextValidDay = $this->findNextValidWorkingDay($scheduledDate);
                    if ($nextValidDay->lessThanOrEqualTo($endDate)) {
                        $occurrences[] = $nextValidDay;
                    }
                }
            }
            return $occurrences;
        }

        // For recurring plans
        $currentDate = $scheduledDate->copy();

        // If the scheduled date is before the start of the period, we need to advance
        // to the first occurrence within the period
        while ($currentDate->lessThan($startDate)) {
            $currentDate = $this->getNextOccurrence($currentDate, $plan);
        }

        // Now add all occurrences within the period
        while ($currentDate->lessThanOrEqualTo($endDate)) {
            // Check if it's not a rest day
            $isRestDay = $currentDate->isSunday() || isset($this->holidays[$currentDate->format('Y-m-d')]);
            if (!$isRestDay) {
                $occurrences[] = $currentDate->copy();
            } else {
                // If it's a rest day, find the next valid working day
                $nextValidDay = $this->findNextValidWorkingDay($currentDate);
                if ($nextValidDay->lessThanOrEqualTo($endDate)) {
                    $occurrences[] = $nextValidDay->copy();
                }
            }
            $currentDate = $this->getNextOccurrence($currentDate, $plan);
        }

        return $occurrences;
    }

    /**
     * Find the next valid working day (not a Sunday or holiday)
     *
     * @param Carbon $date Starting date
     * @return Carbon Next valid working day
     */
    private function findNextValidWorkingDay($date)
    {
        $nextDate = $date->copy()->addDay();

        while ($nextDate->isSunday() || isset($this->holidays[$nextDate->format('Y-m-d')])) {
            $nextDate->addDay();
        }

        return $nextDate;
    }

    /**
     * Calculates the next occurrence based on frequency
     *
     * @param Carbon $currentDate Current date
     * @param MaintenancePlan $plan Maintenance plan
     * @return Carbon The next occurrence date
     */
    private function getNextOccurrence($currentDate, $plan)
    {
        $nextDate = $currentDate->copy();

        switch ($plan->frequency_type) {
            case 'daily':
                return $nextDate->addDay();

            case 'custom':
                // Advance the number of custom days
                return $nextDate->addDays($plan->custom_days ?? 1);

            case 'weekly':
                // If a day of the week is defined, advance to the next specific day
                if (!is_null($plan->day_of_week)) {
                    // First advance a week
                    $nextDate = $nextDate->addWeek();
                    // Then adjust to the desired day of the week
                    return $nextDate->startOfWeek()->addDays($plan->day_of_week);
                }
                // If no specific day, simply advance 7 days
                return $nextDate->addWeek();

            case 'monthly':
                $nextDate = $nextDate->addMonth();
                // If a day of the month is defined, adjust to that day
                if (!is_null($plan->day_of_month)) {
                    $daysInMonth = $nextDate->daysInMonth;
                    $day = min($plan->day_of_month, $daysInMonth);
                    return $nextDate->setDay($day);
                }
                return $nextDate;

            case 'yearly':
                $nextDate = $nextDate->addYear();
                // If month and day are defined, adjust to that date
                if (!is_null($plan->month) && !is_null($plan->month_day)) {
                    // Check for February 29 in non-leap years
                    if ($plan->month == 2 && $plan->month_day == 29 && !$nextDate->isLeapYear()) {
                        return $nextDate->setMonth(2)->setDay(28);
                    }
                    return $nextDate->setMonth($plan->month)->setDay($plan->month_day);
                }
                return $nextDate;

            default:
                return $nextDate->addDay(); // Fallback to daily
        }
    }

    // Update events for the selected date
    public function updateSelectedDateEvents()
    {
        $this->selectedDateEvents = $this->events[$this->selectedDate] ?? [];
    }

    // Navigate to the previous month
    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;

        $this->loadHolidays();
        $this->generateCalendar();
        $this->loadEvents();
    }

    // Navigate to the next month
    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;

        $this->loadHolidays();
        $this->generateCalendar();
        $this->loadEvents();
    }

    // Select a specific date
    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->updateSelectedDateEvents();
    }

    // Reset to the current month
    public function resetToday()
    {
        $today = Carbon::today();
        $this->currentMonth = $today->month;
        $this->currentYear = $today->year;
        $this->selectedDate = $today->format('Y-m-d');

        $this->loadHolidays();
        $this->generateCalendar();
        $this->loadEvents();
    }

    // Edit an event
    public function editEvent($eventId)
    {
        // Dispatch event to open the notes modal
        $this->dispatch('openNotesModal', $eventId);
    }

    // Create a new event on the selected date
    public function createEvent()
    {
        // Check if the selected date is a rest day
        $selectedDateObj = Carbon::parse($this->selectedDate);
        $isRestDay = $selectedDateObj->isSunday() || isset($this->holidays[$this->selectedDate]);

        if ($isRestDay) {
            // If it's a rest day, find the next valid working day
            $nextValidDay = $this->findNextValidWorkingDay($selectedDateObj);
            $this->dispatch('openPlanModal', $nextValidDay->format('Y-m-d'));
        } else {
            // If it's a normal day, just open the modal with the selected date
            $this->dispatch('openPlanModal', $this->selectedDate);
        }
    }

    // Render the component
    public function render()
    {
        return view('livewire.maintenance-schedule-calendar');
    }
}
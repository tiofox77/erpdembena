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

    // Propriedades para filtros
    public $planStatusFilter = 'all'; // all, pending, in-progress, completed, cancelled
    public $noteStatusFilter = 'all'; // all, pending, in-progress, completed, cancelled

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
        // Para uso interno do calendário, mantemos Y-m-d como formato padrão
        // Este formato é necessário para compatibilidade com JavaScript e SQL
        return Carbon::parse($date)->format('Y-m-d');
    }
    
    // Function to format date for display
    public function formatDateForDisplay($date)
    {
        return Carbon::parse($date)->format(\App\Models\Setting::getSystemDateFormat());
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
                    'equipment_id' => $event['extendedProps']['equipment_id'] ?? null, // Adicionado equipment_id
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

    // Método para atualizar o filtro de status do plano
    public function updatePlanStatusFilter($status = 'all')
    {
        $this->planStatusFilter = $status;
        $this->loadEvents();
    }
    
    // Método para atualizar o filtro de status das notas
    public function updateNoteStatusFilter($status = 'all')
    {
        $this->noteStatusFilter = $status;
        $this->loadEvents();
    }

    // Load events for the current month
    public function loadEvents()
    {
        // Define the first and last day of the month to fetch events
        $startDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Fetch maintenance events based on filters
        try {
            // Primeiro carregamos os planos de manutenção com o filtro de status do plano
            $query = MaintenancePlan::with(['equipment', 'task']);
            
            // Aplicar filtro de status do plano
            if ($this->planStatusFilter !== 'all') {
                $query->where('status', $this->planStatusFilter);
            } else {
                // Se não estivermos filtrando especificamente por 'completed' ou 'cancelled',
                // não queremos incluir planos cancelados por padrão
                $query->where('status', '!=', 'cancelled');
            }
            
            // Obter os planos
            $maintenancePlans = $query->get();

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
                    
                    // Skip if this plan has a completed or cancelled note for this date
                    if ($this->hasCompletedOrCancelledNote($plan->id, $formattedDate)) {
                        continue;
                    }

                    // Get color for this task
                    $colorClass = $this->getEventColor($plan->id, $plan->type);

                    // Obter o status da nota de manutenção para esta data
                    $noteStatus = $this->getMaintenanceNoteStatus($plan->id, $formattedDate, $plan->status);
                    
                    $this->events[$formattedDate][] = [
                        'id' => $plan->id,
                        'title' => $plan->task ? $plan->task->title : 'Maintenance',
                        'equipment' => $plan->equipment ? $plan->equipment->name : 'Equipment',
                        'equipment_id' => $plan->equipment_id, // Adicionado equipment_id para identificação única
                        'status' => $noteStatus, // Usando o status da nota em vez do status do plano
                        'plan_status' => $plan->status, // Mantendo o status do plano como referência
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
        $processedDates = []; // Rastreador para evitar duplicações do mesmo equipamento no mesmo dia

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
                $dateStr = $scheduledDate->format('Y-m-d');
                
                // Verificar se não tem nota com status completed ou cancelled para este plano nesta data
                $hasCompleted = $this->hasCompletedOrCancelledNote($plan->id, $dateStr);
                
                if (!$isRestDay && !$hasCompleted) {
                    $occurrences[] = $scheduledDate;
                    $processedDates[$dateStr] = true;
                } else if (!$hasCompleted) { // Só remarca se não estiver completado
                    // If it's a rest day, find the next valid working day
                    $nextValidDay = $this->findNextValidWorkingDay($scheduledDate);
                    $nextValidDayStr = $nextValidDay->format('Y-m-d');
                    
                    // Verificar se já existe alguma ocorrência no mesmo dia para o mesmo equipamento
                    // e se não tem nota com status completed ou cancelled para este plano na nova data
                    if ($nextValidDay->lessThanOrEqualTo($endDate) && 
                        !$this->hasExistingMaintenanceForDate($nextValidDayStr, $plan->equipment_id, $plan->id) &&
                        !$this->hasCompletedOrCancelledNote($plan->id, $nextValidDayStr)) {
                        $occurrences[] = $nextValidDay;
                        $processedDates[$nextValidDayStr] = true;
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
            $currentDateStr = $currentDate->format('Y-m-d');
            
            // Evitar duplicações: verificar se já processamos esta data
            if (isset($processedDates[$currentDateStr])) {
                $currentDate = $this->getNextOccurrence($currentDate, $plan);
                continue;
            }
            
            // Check if it's not a rest day
            $isRestDay = $currentDate->isSunday() || isset($this->holidays[$currentDateStr]);
            if (!$isRestDay) {
                // Verificar se já existe alguma ocorrência no mesmo dia para o mesmo equipamento
                // e também verificar se não tem uma nota com status completado ou cancelado para esta data
                if (!$this->hasExistingMaintenanceForDate($currentDateStr, $plan->equipment_id, $plan->id) &&
                    !$this->hasCompletedOrCancelledNote($plan->id, $currentDateStr)) {
                    $occurrences[] = $currentDate->copy();
                    $processedDates[$currentDateStr] = true;
                }
            } else {
                // If it's a rest day, find the next valid working day
                $nextValidDay = $this->findNextValidWorkingDay($currentDate);
                $nextValidDayStr = $nextValidDay->format('Y-m-d');
                
                // Verificar se já existe alguma ocorrência no próximo dia válido para o mesmo equipamento
                // e também verificar se não tem uma nota com status completado ou cancelado para esta data
                if ($nextValidDay->lessThanOrEqualTo($endDate) && 
                    !isset($processedDates[$nextValidDayStr]) && 
                    !$this->hasExistingMaintenanceForDate($nextValidDayStr, $plan->equipment_id, $plan->id) &&
                    !$this->hasCompletedOrCancelledNote($plan->id, $nextValidDayStr)) {
                    $occurrences[] = $nextValidDay->copy();
                    $processedDates[$nextValidDayStr] = true;
                }
            }
            
            $currentDate = $this->getNextOccurrence($currentDate, $plan);
        }

        return $occurrences;
    }
    
    /**
     * Verifica se já existe uma manutenção para o mesmo equipamento na data especificada
     * Usado para evitar duplicações no calendário
     * 
     * @param string $dateStr Data em formato Y-m-d
     * @param int $equipmentId ID do equipamento
     * @param int $currentPlanId ID do plano atual (para evitar contar o próprio plano)
     * @return bool Retorna true se já existe manutenção, false caso contrário
     */
    private function hasExistingMaintenanceForDate($dateStr, $equipmentId, $currentPlanId)
    {
        // Se não há eventos nesta data, retorna false
        if (!isset($this->events[$dateStr])) {
            return false;
        }
        
        // Verificar todos os eventos nesta data
        foreach ($this->events[$dateStr] as $event) {
            // Verificar se é o mesmo equipamento e não é o plano atual
            if (isset($event['equipment_id']) && $event['equipment_id'] == $equipmentId && $event['id'] != $currentPlanId) {
                return true; // Já existe uma manutenção para este equipamento nesta data
            }
        }
        
        return false;
    }

    /**
     * Verifica se já existe uma manutenção para o mesmo equipamento na data especificada
     * Usado para evitar duplicações no calendário
     * 
     * @param string $dateStr Data em formato Y-m-d
     * @param int $equipmentId ID do equipamento
     * @param int $currentPlanId ID do plano atual (para evitar contar o próprio plano)
     * @return bool Retorna true se já existe manutenção, false caso contrário
     */

/**
 * Verifica se um evento deve ser filtrado com base no status da nota e no filtro atual
 *
 * @param int $planId ID do plano de manutenção
 * @param string $dateStr Data em formato Y-m-d
 * @return bool Retorna true se o evento deve ser filtrado (removido), false se deve ser exibido
 */
    private function hasCompletedOrCancelledNote($planId, $dateStr)
{
    // Log para debug - início do método
    \Log::info('=== INÍCIO hasCompletedOrCancelledNote ===', [
        'planId' => $planId,
        'dateStr' => $dateStr,
        'noteStatusFilter' => $this->noteStatusFilter
    ]);
    
    try {
        // Converter a string de data para Carbon para manipulação segura
        $date = \Carbon\Carbon::parse($dateStr);
        
        // Buscar especificamente a nota mais recente para este plano E esta data específica
        $note = \App\Models\MaintenanceNote::where('maintenance_plan_id', $planId)
            ->whereDate('note_date', $date->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();
            
        // Log para registro da nota encontrada
        \Log::info('Nota encontrada para filtragem:', [
            'encontrou_nota' => $note ? 'sim' : 'não',
            'note_status' => $note ? $note->status : 'nenhum'
        ]);
        
        // Se não existe nota para esta data específica, verificamos o filtro
        if (!$note) {
            // Se estamos filtrando para mostrar apenas completed ou cancelled, não mostramos eventos sem notas
            if ($this->noteStatusFilter === 'completed' || $this->noteStatusFilter === 'cancelled') {
                \Log::info('Filtrando evento sem nota porque filtro é completed/cancelled');
                return true; // Filtrar (remover) o evento
            }
            \Log::info('Mantendo evento sem nota');
            return false; // Manter o evento
        }
        
        // Se o filtro for 'all', mostramos tudo
        if ($this->noteStatusFilter === 'all') {
            \Log::info('Filtro é all, mantendo evento');
            return false; // Mostrar tudo
        }
        
        // Se o filtro for específico, verificamos se corresponde ao status da nota
        if ($this->noteStatusFilter !== 'all') {
            // Se o status da nota não corresponder ao filtro, removemos o evento
            $shouldFilter = ($note->status !== $this->noteStatusFilter);
            \Log::info('Verificando filtro específico', [
                'note_status' => $note->status,
                'filtro' => $this->noteStatusFilter,
                'resultado' => $shouldFilter ? 'filtrar' : 'manter'
            ]);
            return $shouldFilter;
        }
        
        // Por padrão, se o status for 'completed' ou 'cancelled', filtramos
        $shouldFilter = in_array($note->status, ['completed', 'cancelled']);
        \Log::info('Verificação padrão de completed/cancelled', [
            'resultado' => $shouldFilter ? 'filtrar' : 'manter'
        ]);
        return $shouldFilter;
    } catch (\Exception $e) {
        \Log::error('Erro ao verificar nota para filtragem', [
            'error' => $e->getMessage()
        ]);
        return false; // Em caso de erro, melhor mostrar o evento
    } finally {
        \Log::info('=== FIM hasCompletedOrCancelledNote ===');
    }
    }
    
    private function getMaintenanceNoteStatus($planId, $dateStr, $defaultStatus = 'pending')
    {
        // Buscar a nota mais recente para este plano E para esta data específica
        // Isso garante que o status de uma data não afete as outras datas
        try {
            // Log para debug - início do método
            \Log::info('=== INÍCIO getMaintenanceNoteStatus ===', [
                'planId' => $planId,
                'dateStr' => $dateStr,
                'defaultStatus' => $defaultStatus
            ]);
            
            // Converter a string de data para Carbon para manipulação segura
            $date = \Carbon\Carbon::parse($dateStr);
            
            // Buscar as notas específicas para a data do calendário usando o campo note_date
            $note = \App\Models\MaintenanceNote::where('maintenance_plan_id', $planId)
                ->whereDate('note_date', $date->format('Y-m-d'))
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Se encontrou uma nota para esta data, retorna o status dela
            if ($note) {
                \Log::info('Nota encontrada para a data específica', [
                    'noteId' => $note->id,
                    'status' => $note->status,
                    'created_at' => $note->created_at
                ]);
                return $note->status;
            }
            
            // Se não encontrou nota para esta data, retorna o status padrão (do plano)
            \Log::info('Nenhuma nota encontrada para a data específica', [
                'usando_status_padrao' => $defaultStatus
            ]);
            return $defaultStatus;
        } catch (\Exception $e) {
            // Log de erro caso ocorra alguma exceção
            \Log::error('Erro ao obter status da nota de manutenção', [
                'error' => $e->getMessage(),
                'planId' => $planId,
                'dateStr' => $dateStr
            ]);
            return $defaultStatus;
        } finally {
            // Log para debug - fim do método
            \Log::info('=== FIM getMaintenanceNoteStatus ===');
        }
    }

    /**
     * Find the next valid working day (not a Sunday or holiday)
     *
     * @param Carbon $date Starting date
     * @return Carbon Next valid working day
     */
    private function findNextValidWorkingDay($date)
    {
        $nextDate = $date->copy();

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
        // Log para debug
        \Log::info('=== INÍCIO editEvent ===', [
            'eventId' => $eventId,
            'selectedDate' => $this->selectedDate
        ]);
        
        // Dispatch event to open the notes modal with the selected date
        $this->dispatch('openNotesModal', $eventId, $this->selectedDate);
        
        // Log para debug
        \Log::info('=== FIM editEvent ===');
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
    
    /**
     * Generate PDF of the maintenance plan calendar
     */
    public function generatePdf()
    {
        try {
            // Prepare the data for the PDF
            $data = [
                'title' => __('messages.maintenance_plan_calendar'),
                'month' => $this->calendarTitle,
                'calendarDays' => $this->calendarDays,
                'events' => $this->events,
                'holidays' => $this->holidays,
                'filters' => [
                    'planStatus' => $this->planStatusFilter,
                    'noteStatus' => $this->noteStatusFilter,
                ],
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ];
            
            // Load the PDF view
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.maintenance-calendar', $data);
            
            $filename = 'maintenance_calendar_' . $this->currentYear . '_' . $this->currentMonth . '.pdf';
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_generated_successfully')
            );
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            \Log::error('Error generating maintenance calendar PDF: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed')
            );
            return null;
        }
    }
}

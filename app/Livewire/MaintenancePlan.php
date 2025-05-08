<?php

namespace App\Livewire;

use App\Models\MaintenancePlan as MaintenancePlanModel;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use App\Models\MaintenanceTask;
use App\Models\Holiday;
use App\Models\Technician;
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
    public $showViewModal = false;

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
    public $status = 'pending';

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $frequencyFilter = '';
    public $equipment_filter = '';
    public $task_filter = '';
    public $perPage = 10;
    public $sortField = 'scheduled_date';
    public $sortDirection = 'desc';

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
        'type' => 'required|in:preventive,predictive,conditional,other',
        'assigned_to' => 'nullable|exists:technicians,id',
        'description' => 'nullable|string',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,completed,cancelled,schedule',
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
        'acceptSuggestedDate' => 'acceptSuggestedDate',
        'view' => 'viewSchedule'
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
            'holidayTitle',
            'showViewModal'
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
     * Also checks for existing maintenance on the suggested date
     *
     * @return bool
     */
    /**
     * Check if the scheduled date is a holiday or Sunday and suggest a new date
     * Verifica rigorosamente duplicações e interrompe o reagendamento se o mesmo ID já existe
     *
     * @return bool
     */
    public function checkScheduledDate()
    {
        if (!$this->scheduled_date || empty($this->equipment_id)) {
            return false;
        }

        // Obter o ID se estiver editando
        $maintenanceId = $this->isEditing ? $this->scheduleId : null;
        
        // NOTA: A verificação anterior que impedia edição de registros existentes foi removida
        // para permitir a edição normal de planos de manutenção
        // Se tivermos um ID de manutenção, significa que estamos editando um registro existente,
        // o que é completamente permitido

        // VERIFICAÇÃO 2: Se já existe manutenção semelhante, alertamos
        $existingMaintenance = $this->checkForDuplicateMaintenanceGlobal();
        if ($existingMaintenance) {
            // Já existe manutenção para este equipamento, avise o usuário e não tente reagendar
            return false;
        }

        $date = Carbon::parse($this->scheduled_date);

        // Check if the date is a Sunday
        if ($this->isSunday($date)) {
            // VERIFICAÇÃO ADICIONAL: Verificar se qualquer reagendamento causaria duplicação
            if ($this->wouldCauseDuplication($date, $maintenanceId)) {
                return false; // Interromper reagendamento para evitar duplicação
            }
            
            $this->originalScheduledDate = $this->scheduled_date;
            $suggestionDate = $this->getNextValidWorkingDate($date);
            $this->suggestedDate = $suggestionDate->format('Y-m-d');
            $this->holidayTitle = "Sunday (Rest Day)";
            
            // Verificar se já existe manutenção na data sugerida
            $this->checkForExistingMaintenanceOnDate($suggestionDate, $maintenanceId);
            
            $this->showHolidayWarning = true;
            return true;
        }

        // Check if the date is a holiday
        if ($this->isHoliday($date)) {
            // VERIFICAÇÃO ADICIONAL: Verificar se qualquer reagendamento causaria duplicação
            if ($this->wouldCauseDuplication($date, $maintenanceId)) {
                return false; // Interromper reagendamento para evitar duplicação
            }
            
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
            $suggestionDate = $this->getNextValidWorkingDate($date);
            $this->suggestedDate = $suggestionDate->format('Y-m-d');
            $this->holidayTitle = $holiday ? $holiday->title : "Holiday";
            
            // Verificar se já existe manutenção na data sugerida
            $this->checkForExistingMaintenanceOnDate($suggestionDate, $maintenanceId);
            
            $this->showHolidayWarning = true;
            return true;
        }

        return false;
    }
    
    /**
     * Verifica se qualquer reagendamento causaria duplicação (mesmo ID ou mesmo equipamento)
     * 
     * @param Carbon $date A data original (domingo ou feriado)
     * @param int|null $maintenanceId ID da manutenção atual, se estiver editando
     * @return bool True se causaria duplicação, False caso contrário
     */
    protected function wouldCauseDuplication($date, $maintenanceId = null)
    {
        // Se estamos editando uma manutenção existente, permitir continuar normalmente
        if ($maintenanceId) {
            // Edição de registro existente é permitida
            return false; // Não causaria duplicação, pois estamos simplesmente editando
        }
        
        // Verificar se já existe manutenção para o mesmo equipamento em outras datas
        // que não sejam a data original
        if ($this->equipment_id) {
            $query = MaintenancePlanModel::where('equipment_id', $this->equipment_id);
            
            // Se estiver editando, excluir o ID atual da busca
            if ($maintenanceId) {
                $query->where('id', '!=', $maintenanceId);
            }
            
            // Excluir a data original (domingo/feriado) da busca
            $query->whereDate('scheduled_date', '!=', $date->format('Y-m-d'));
            
            $existingMaintenance = $query->first();
            if ($existingMaintenance) {
                $equipment = MaintenanceEquipment::find($this->equipment_id);
                $equipmentName = $equipment ? $equipment->name : 'this equipment';
                
                $message = "AVISO: Não é possível reagendar pois já existe uma manutenção para {$equipmentName} ";
                $message .= "agendada para " . Carbon::parse($existingMaintenance->scheduled_date)->format('d/m/Y') . ".";
                
                $this->dispatch('notify', type: 'error', message: $message);
                return true; // Sim, causaria duplicação
            }
        }
        
        return false; // Não causaria duplicação
    }
    
    /**
     * Verifica se já existe manutenção para o mesmo equipamento em qualquer data
     * Para evitar duplicações no sistema
     * 
     * @return bool|object Retorna false se não existir ou o objeto da manutenção se existir
     */
    /**
     * Verificação rigorosa para identificar qualquer tipo de duplicação de manutenção no sistema
     * Considera equipamento, frequência e verificação especial para datas próximas
     *
     * @return bool|object False se não encontrar duplicação, ou o objeto MaintenancePlan se encontrar
     */
    protected function checkForExactDuplicate()
    {
        // Verificar se temos dados suficientes para verificar duplicações
        if (empty($this->equipment_id) || empty($this->frequency_type) || empty($this->scheduled_date)) {
            return false;
        }
        
        // Se estamos editando, não precisamos verificar duplicação exata (será feita de outra forma)
        if ($this->isEditing) {
            return false;
        }
        
        // 1. Verificar planos de manutenção com mesmo equipamento e frequência
        $query = MaintenancePlanModel::where('equipment_id', $this->equipment_id);
        
        // 2. Aplicar verificação de frequência
        $query->where('frequency_type', $this->frequency_type);
        
        // 3. Verificação adicional baseada no tipo de frequência
        switch ($this->frequency_type) {
            case 'custom':
                if (!empty($this->custom_days)) {
                    $query->where('custom_days', $this->custom_days);
                }
                break;
                
            case 'weekly':
                if (!empty($this->day_of_week)) {
                    $query->where('day_of_week', $this->day_of_week);
                }
                break;
                
            case 'monthly':
                if (!empty($this->day_of_month)) {
                    $query->where('day_of_month', $this->day_of_month);
                }
                break;
                
            case 'yearly':
                if (!empty($this->month) && !empty($this->month_day)) {
                    $query->where('month', $this->month)
                          ->where('month_day', $this->month_day);
                }
                break;
        }
        
        // Encontrar qualquer manutenção que corresponda aos critérios
        $existingMaintenance = $query->first();
        
        if ($existingMaintenance) {
            $equipment = MaintenanceEquipment::find($this->equipment_id);
            $equipmentName = $equipment ? $equipment->name : 'this equipment';
            
            // Mensagem detalhada sobre a duplicação encontrada
            $message = "DUPLICAÇÃO DETECTADA: Já existe um plano de manutenção para {$equipmentName} ";
            $message .= "com a mesma frequência " . $this->getFrequencyText($existingMaintenance) . ". ";
            $message .= "Agendado para " . Carbon::parse($existingMaintenance->scheduled_date)->format('d/m/Y') . ".";
            
            // Notificar o usuário com detalhes sobre a duplicação
            $this->dispatch('notify', 
                type: 'error', 
                message: $message
            );
            
            return $existingMaintenance;
        }
        
        // Verificação adicional: Agendamentos na mesma data para o mesmo equipamento
        $scheduledDate = Carbon::parse($this->scheduled_date)->format('Y-m-d');
        $duplicateOnDate = MaintenancePlanModel::where('equipment_id', $this->equipment_id)
            ->whereDate('scheduled_date', $scheduledDate)
            ->first();
            
        if ($duplicateOnDate) {
            $equipment = MaintenanceEquipment::find($this->equipment_id);
            $equipmentName = $equipment ? $equipment->name : 'this equipment';
            
            $message = "DUPLICAÇÃO NA MESMA DATA: Já existe um plano de manutenção para {$equipmentName} ";
            $message .= "agendado para " . Carbon::parse($scheduledDate)->format('d/m/Y') . ".";
            
            $this->dispatch('notify', 
                type: 'error', 
                message: $message
            );
            
            return $duplicateOnDate;
        }
        
        return false;
    }
    
    /**
     * Função antiga mantida por compatibilidade
     */
    protected function checkForDuplicateMaintenanceGlobal()
    {
        return $this->checkForExactDuplicate();
    }
    
    /**
     * Verifica se já existe manutenção para o mesmo equipamento na data sugerida
     * e adiciona aviso à mensagem do holiday title se existir
     *
     * @param Carbon $date
     * @param int|null $maintenanceId
     * @return bool
     */
    protected function checkForExistingMaintenanceOnDate(Carbon $date, $maintenanceId = null)
    {
        // Se ainda não há equipment_id selecionado, não podemos verificar duplicações
        if (empty($this->equipment_id)) {
            return false;
        }
        
        // Verificar se já existe uma manutenção na data sugerida para o mesmo equipamento
        $existingOnDate = MaintenancePlanModel::when($maintenanceId, function($query) use ($maintenanceId) {
                return $query->where('id', '!=', $maintenanceId);
            })
            ->where('equipment_id', $this->equipment_id)
            ->whereDate('scheduled_date', $date->format('Y-m-d'))
            ->first();
            
        // Se existir, adicionar aviso ao título do feriado/domingo
        if ($existingOnDate) {
            $equipment = MaintenanceEquipment::find($this->equipment_id);
            $equipmentName = $equipment ? $equipment->name : 'this equipment';
            
            $this->holidayTitle .= " - ATENÇÃO: Já existe manutenção para {$equipmentName} na data sugerida";
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
    /**
     * Get next valid working date (not a holiday or Sunday)
     * 
     * @param Carbon $date
     * @param int|null $maintenanceId ID of the current maintenance being scheduled
     * @return Carbon
     */
    protected function getNextValidWorkingDate(Carbon $date, $maintenanceId = null)
    {
        $nextDate = $date->copy();

        // Continue advancing until finding a valid working date (not holiday or Sunday)
        while ($this->isHoliday($nextDate) || $this->isSunday($nextDate)) {
            $nextDate->addDay();
        }
        
        // Apenas chegamos ao próximo dia útil (não domingo ou feriado)
        // Se já existir agendamento nessa data, não tentamos o próximo dia
        // conforme solicitado

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

    /**
     * Método para salvar o plano de manutenção com verificações rigorosas de duplicação
     * 
     * @return void
     */
    public function save()
    {
        $this->validate();
        
        try {
            // Verificar se estamos editando ou criando um novo agendamento
            if ($this->isEditing) {
                $schedule = MaintenancePlanModel::findOrFail($this->scheduleId);
            } else {
                // VERIFICAÇÃO RÍGIDA 1: Verificar se já existe uma manutenção idêntica no sistema
                // Usamos escopo de transação para garantir integridade
                $duplicate = $this->checkForExactDuplicate();
                if ($duplicate) {
                    // Notificar usuário e interromper o salvamento
                    return;
                }
                
                $schedule = new MaintenancePlanModel();
            }
            
            // VERIFICAÇÃO RÍGIDA 2: Garantir que a data não cai em domingo ou feriado sem confirmação
            if (!$this->isEditing && !$this->showHolidayWarning) {
                $date = Carbon::parse($this->scheduled_date);
                if ($this->isSunday($date) || $this->isHoliday($date)) {
                    $this->checkScheduledDate();
                    return; // Aguardar confirmação do usuário antes de prosseguir
                }
            }
            
            // VERIFICAÇÃO MODIFICADA: Verificar duplicação na mesma data para o mesmo equipamento
            // mas apenas se NÃO estivermos editando o registro original
            $scheduledDate = Carbon::parse($this->scheduled_date)->format('Y-m-d');
            
            // Apenas verificar duplicação se não estivermos editando OU
            // se estivermos editando com mudança de equipamento/data
            if (!$this->isEditing) {
                $duplicateOnDate = MaintenancePlanModel::where('equipment_id', $this->equipment_id)
                    ->whereDate('scheduled_date', $scheduledDate);
                
                // Verificar se existe duplicação
                $duplicateExists = $duplicateOnDate->exists();
                if ($duplicateExists) {
                    $equipment = MaintenanceEquipment::find($this->equipment_id);
                    $equipmentName = $equipment ? $equipment->name : 'this equipment';
                    
                    $message = "AVISO: Já existe um plano de manutenção agendado para {$equipmentName} em " . 
                              Carbon::parse($scheduledDate)->format('d/m/Y') . ". Selecione outra data ou equipamento.";
                    
                    $this->dispatch('notify', 
                        type: 'error', 
                        message: $message
                    );
                    
                    return; // Interromper o salvamento apenas para novos registros
                }
            } elseif ($this->isEditing) {
                // Se estiver editando, verificar apenas duplicações com outros registros (exceto o atual)
                $duplicateOnDate = MaintenancePlanModel::where('equipment_id', $this->equipment_id)
                    ->whereDate('scheduled_date', $scheduledDate)
                    ->where('id', '!=', $this->scheduleId)
                    ->exists();
                
                if ($duplicateOnDate) {
                    $equipment = MaintenanceEquipment::find($this->equipment_id);
                    $equipmentName = $equipment ? $equipment->name : 'this equipment';
                    
                    $message = "AVISO: A combinação de data e equipamento já está sendo usada em outro plano. Por favor, verifique.";
                    
                    $this->dispatch('notify', 
                        type: 'warning', // Mudamos para warning em vez de error
                        message: $message
                    );
                    
                    // Permitimos continuar mesmo com o aviso, já que estamos editando
                }
            }

            $schedule->task_id = $this->task_id;
            $schedule->equipment_id = $this->equipment_id;
            $schedule->line_id = $this->line_id;
            $schedule->area_id = $this->area_id;
            $schedule->scheduled_date = $this->scheduled_date;
            $schedule->frequency_type = $this->frequency_type;
            $schedule->custom_days = $this->frequency_type === 'custom' ? $this->custom_days : null;
            $schedule->day_of_week = $this->frequency_type === 'weekly' ? $this->day_of_week : null;
            $schedule->day_of_month = $this->frequency_type === 'monthly' ? $this->day_of_month : null;
            $schedule->month = $this->frequency_type === 'yearly' ? $this->month : null;
            $schedule->month_day = $this->frequency_type === 'yearly' ? $this->month_day : null;
            $schedule->priority = $this->priority;
            $schedule->type = $this->type;
            $schedule->assigned_to = $this->assigned_to;
            $schedule->description = $this->description;
            $schedule->notes = $this->notes;
            $schedule->status = $this->status;
            $schedule->save();

            $this->dispatch('refreshCalendar');
            
            $actionType = $this->isEditing ? 'updated' : 'created';
            $message = __('messages.maintenance_plan_' . $actionType);
            
            $this->dispatch('notify', 
                type: 'success', 
                message: $message
            );
            
            // Reset form and refresh data without redirecting
            $this->closeModal();
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            $errorMessage = __('messages.error_saving_schedule', ['error' => $e->getMessage()]);
            $this->dispatch('notify', type: 'error', message: $errorMessage);
        }
    }

    public function delete($id)
    {
        try {
            $schedule = MaintenancePlanModel::findOrFail($id);
            $schedule->delete();

            // Send notification with correct type
            $message = 'The maintenance plan was successfully deleted.';
            session()->flash('message', $message);
            $this->dispatch('notify', type: 'success', message: $message);

            $this->updateCalendarEvents();
        } catch (\Exception $e) {
            // Send error notification with correct type
            $message = 'An error occurred while deleting the plan. Please try again.';
            session()->flash('error', $message);
            $this->dispatch('notify', type: 'error', message: $message);
        }
    }

    public function getFrequencyText($schedule)
    {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March', 
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];
        
        return match ($schedule->frequency_type) {
            'once' => "Once",
            'daily' => "Daily",
            'custom' => "Every {$schedule->custom_days} days",
            'weekly' => "Weekly" . (isset($schedule->day_of_week) ? " (". Carbon::getDays()[$schedule->day_of_week] .")" : ""),
            'monthly' => "Monthly" . (isset($schedule->day_of_month) ? " (day {$schedule->day_of_month})" : ""),
            'yearly' => "Yearly" . (isset($schedule->month) && isset($schedule->month_day) ? " (" . $months[$schedule->month] . " {$schedule->month_day})" : ""),
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
                    'schedule' => '#F59E0B',    // orange
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

        $this->filteredTechnicians = Technician::where('name', 'like', '%' . $this->technicianSearch . '%')
            ->orWhere('phone_number', 'like', '%' . $this->technicianSearch . '%')
            ->limit(5)
            ->get();
    }

    /**
     * Set the selected technician
     */
    public function selectTechnician($id)
    {
        $this->assigned_to = $id;
        $this->technicianSearch = Technician::find($id)->name;
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
            $notificationType = 'info';
            $message = "The selected date is a Sunday (rest day). The plan has been scheduled for the next available date: " . $suggestedDate->format('m/d/Y');
            $this->dispatch('notify', type: $notificationType, message: $message);

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

            $notificationType = 'info';
            $message = "The selected date is a holiday (" . $this->holidayTitle . "). The plan has been scheduled for the next available date: " . $suggestedDate->format('m/d/Y');
            $this->dispatch('notify', type: $notificationType, message: $message);

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

    public function viewSchedule($id)
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

        $this->showViewModal = true;
    }

    /**
     * Close the view modal
     */
    public function closeViewModal()
    {
        $this->showViewModal = false;
    }

    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->frequencyFilter = '';
        $this->equipment_filter = '';
        $this->task_filter = '';
        $this->perPage = 10;
        $this->resetPage();

        $notificationType = 'info';
        $message = __('messages.filters_cleared');
        $this->dispatch('notify', type: $notificationType, message: $message);

        $this->updateCalendarEvents();
    }
    
    /**
     * Update equipment filter
     */
    public function updatedEquipment_filter()
    {
        $this->resetPage();
    }
    
    /**
     * Update task filter
     */
    public function updatedTask_filter()
    {
        $this->resetPage();
    }
    
    /**
     * Load filter options for the maintenance plan list
     */
    public function loadFilters()
    {
        // This method can be used to initialize filter values or preset filters
        // based on certain conditions (like user permissions, roles, etc.)
        $this->equipment_filter = $this->equipment_filter ?? '';
        $this->statusFilter = $this->statusFilter ?? '';
        $this->frequencyFilter = $this->frequencyFilter ?? '';
        $this->task_filter = $this->task_filter ?? '';
    }
    
    /**
     * Get filtered maintenance plans based on current filters
     * 
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getFilteredPlans()
    {
        $query = MaintenancePlanModel::query()
            ->with(['equipment', 'equipment.area', 'equipment.line', 'task', 'assignedTo']);
            
        // Apply search filter if provided
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('description', 'like', $search)
                  ->orWhereHas('equipment', function($eq) use ($search) {
                      $eq->where('name', 'like', $search);
                  })
                  ->orWhereHas('task', function($tq) use ($search) {
                      $tq->where('title', 'like', $search);
                  });
            });
        }
        
        // Apply equipment filter if provided
        if (!empty($this->equipment_filter)) {
            $query->where('equipment_id', $this->equipment_filter);
        }
        
        // Apply status filter if provided
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }
        
        // Apply frequency filter if provided
        if (!empty($this->frequencyFilter)) {
            $query->where('frequency_type', $this->frequencyFilter);
        }
        
        // Apply task filter if provided
        if (!empty($this->task_filter)) {
            $query->where('task_id', $this->task_filter);
        }
        
        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);
        
        // Paginate results
        return $query->paginate($this->perPage);
    }

    public function render()
    {
        $this->loadFilters();
        $plans = $this->getFilteredPlans();
        
        return view('livewire.maintenance-plan', [
            'plans' => $plans,
            'tasks' => MaintenanceTask::orderBy('title')->get(),
            'equipments' => MaintenanceEquipment::with('area', 'line')->orderBy('name')->get(),
            'areas' => MaintenanceArea::orderBy('name')->get(),
            'lines' => MaintenanceLine::orderBy('name')->get(),
            'technicians' => Technician::orderBy('name')->get(),
            'priorities' => [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'critical' => 'Critical'
            ],
            'types' => [
                'preventive' => 'Preventive',
                'predictive' => 'Predictive',
                'conditional' => 'Conditional',
                'other' => 'Other'
            ],
            'statuses' => [
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'schedule' => 'Schedule'
            ]
        ]);
    }
    
    /**
     * Generate PDF for an individual maintenance plan
     * 
     * @param int $id The maintenance plan ID
     * @return mixed Response with PDF download or null on error
     */
    public function generatePdf($id)
    {
        try {
            $plan = MaintenancePlanModel::with([
                'equipment', 
                'equipment.area',
                'equipment.line',
                'task',
                'assignedTo',
                'notes' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])->findOrFail($id);
            
            // Prepare the data for the PDF
            $data = [
                'plan' => $plan,
                'title' => __('messages.maintenance_plan_details'),
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ];
            
            // Load the PDF view
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.maintenance-plan', $data);
            
            $filename = 'maintenance_plan_' . $id . '.pdf';
            
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
            \Log::error('Error generating maintenance plan PDF: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed')
            );
            return null;
        }
    }
    
    /**
     * Generate PDF for a filtered list of maintenance plans
     * 
     * @return mixed Response with PDF download or null on error
     */
    public function generateListPdf()
    {
        try {
            // Get the filtered plans using the current filters
            $this->loadFilters();
            
            // Criar uma query com os mesmos filtros de getFilteredPlans()
            $query = MaintenancePlanModel::query();
            
            // Apply search filter if provided
            if (!empty($this->search)) {
                $search = '%' . $this->search . '%';
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', $search)
                      ->orWhere('description', 'like', $search)
                      ->orWhereHas('equipment', function($eq) use ($search) {
                          $eq->where('name', 'like', $search);
                      })
                      ->orWhereHas('task', function($tq) use ($search) {
                          $tq->where('title', 'like', $search);
                      });
                });
            }
            
            // Apply equipment filter if provided
            if (!empty($this->equipment_filter)) {
                $query->where('equipment_id', $this->equipment_filter);
            }
            
            // Apply status filter if provided
            if (!empty($this->statusFilter)) {
                $query->where('status', $this->statusFilter);
            }
            
            // Apply frequency filter if provided
            if (!empty($this->frequencyFilter)) {
                $query->where('frequency_type', $this->frequencyFilter);
            }
            
            // Apply task filter if provided
            if (!empty($this->task_filter)) {
                $query->where('task_id', $this->task_filter);
            }
            
            // Apply sorting
            $query->orderBy($this->sortField, $this->sortDirection);
            
            // Limit to 100 records for performance
            $plans = $query->with(['equipment', 'equipment.area', 'equipment.line', 'task', 'assignedTo'])
                            ->limit(100)
                            ->get();
            
            // Prepare the data for the PDF
            $data = [
                'plans' => $plans,
                'title' => __('messages.maintenance_plans_list'),
                'filters' => [
                    'status' => $this->statusFilter,
                    'equipment' => $this->equipment_filter ? MaintenanceEquipment::find($this->equipment_filter)?->name : null,
                    'task' => $this->task_filter ? MaintenanceTask::find($this->task_filter)?->title : null,
                    'frequency' => $this->frequencyFilter,
                    'search' => $this->search,
                ],
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ];
            
            // Load the PDF view
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.maintenance-plans-list', $data);
            
            $filename = 'maintenance_plans_list_' . now()->format('Y-m-d') . '.pdf';
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_list_generated_successfully')
            );
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            \Log::error('Error generating maintenance plans list PDF: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed')
            );
            return null;
        }
    }
}

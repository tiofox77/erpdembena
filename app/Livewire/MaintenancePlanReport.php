<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceTask;
use App\Models\Holiday;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use Illuminate\Pagination\LengthAwarePaginator;

class MaintenancePlanReport extends Component
{
    use WithPagination;

    // Filters
    public $selectedMonth; // Mês selecionado no formato 'YYYY-MM'
    public $reportType = 'month'; // Tipo de relatório: 'month', 'day', 'period'
    public $selectedDay; // Dia específico selecionado no formato 'YYYY-MM-DD'
    public $periodStart; // Data início do período no formato 'YYYY-MM-DD'
    public $periodEnd; // Data fim do período no formato 'YYYY-MM-DD'
    public $status = '';
    public $type = '';
    public $equipment_id = '';
    public $task_id = '';
    public $line_id = '';
    public $area_id = '';
    
    // Sorting
    public $sortField = 'scheduled_date';
    public $sortDirection = 'desc';
    
    // PDF generation
    public $generatingPdf = false;
    public $pdfUrl = null;
    
    // Holidays storage
    protected $holidays = [];

    public function mount()
    {
        // Default to current month (YYYY-MM format)
        $this->selectedMonth = Carbon::now()->format('Y-m');
        
        // Inicializa as novas propriedades de data
        $today = Carbon::today();
        $this->selectedDay = $today->format('Y-m-d');
        $this->periodStart = $today->startOfMonth()->format('Y-m-d');
        $this->periodEnd = $today->endOfMonth()->format('Y-m-d');
        
        // Load holidays for the current year
        $this->loadHolidays();
    }
    
    /**
     * Carrega os feriados do ano atual para uso na geração de ocorrências
     */
    protected function loadHolidays()
    {
        $this->holidays = [];
        
        // Extrai o ano do mês selecionado
        list($year, $month) = explode('-', $this->selectedMonth);
        
        // Carrega feriados fixos do ano
        $fixedHolidays = Holiday::where('is_active', true)
            ->whereYear('date', $year)
            ->get();
            
        foreach ($fixedHolidays as $holiday) {
            $date = Carbon::parse($holiday->date);
            $this->holidays[$date->format('Y-m-d')] = [
                'title' => $holiday->title,
                'recurring' => $holiday->is_recurring
            ];
        }
        
        // Carrega feriados recorrentes para o ano
        $recurringHolidays = Holiday::where('is_active', true)
            ->where('is_recurring', true)
            ->get();
            
        foreach ($recurringHolidays as $holiday) {
            $originalDate = Carbon::parse($holiday->date);
            $thisYearDate = Carbon::createFromDate(
                $year,
                $originalDate->month,
                min($originalDate->day, Carbon::createFromDate($year, $originalDate->month, 1)->daysInMonth)
            );
            
            $this->holidays[$thisYearDate->format('Y-m-d')] = [
                'title' => $holiday->title,
                'recurring' => true
            ];
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        // Reset date filters
        $today = Carbon::today();
        $this->selectedMonth = Carbon::now()->format('Y-m');
        $this->reportType = 'month';
        $this->selectedDay = $today->format('Y-m-d');
        $this->periodStart = $today->startOfMonth()->format('Y-m-d');
        $this->periodEnd = $today->endOfMonth()->format('Y-m-d');
        
        // Reset other filters
        $this->status = '';
        $this->type = '';
        $this->equipment_id = '';
        $this->task_id = '';
        $this->line_id = '';
        $this->area_id = '';
    }

    public function generatePdf()
    {
        $this->generatingPdf = true;
        
        // Obter todos os planos filtrados sem paginação
        $plans = $this->getFilteredPlans(false);
        
        // Definir período com base no tipo de relatório selecionado
        $startDate = null;
        $endDate = null;
        $periodTitle = '';
        
        switch ($this->reportType) {
            case 'day':
                // Relatório para um dia específico
                $startDate = Carbon::parse($this->selectedDay)->startOfDay();
                $endDate = Carbon::parse($this->selectedDay)->endOfDay();
                $periodTitle = $startDate->translatedFormat(Setting::getSystemDateFormat());
                break;
                
            case 'period':
                // Relatório para um período personalizado
                $startDate = Carbon::parse($this->periodStart)->startOfDay();
                $endDate = Carbon::parse($this->periodEnd)->endOfDay();
                $periodTitle = $startDate->translatedFormat(Setting::getSystemDateFormat()) . ' - ' . 
                              $endDate->translatedFormat(Setting::getSystemDateFormat());
                break;
                
            case 'month':
            default:
                // Relatório para o mês completo (padrão)
                list($year, $month) = explode('-', $this->selectedMonth);
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
                $periodTitle = $startDate->translatedFormat('F Y'); // Nome do mês e ano na língua atual
                break;
        }
        
        // Processa cada plano para calcular suas datas de manutenção no período
        $processedPlans = [];
        
        foreach ($plans as $plan) {
            // Calcular datas de ocorrência para este plano no período selecionado
            $occurrences = $this->generateOccurrences($plan, $startDate, $endDate);
            
            if (count($occurrences) > 0) {
                // Criar uma cópia do plano com suas datas de ocorrência
                $planWithDates = clone $plan;
                $planWithDates->occurrences = $occurrences;
                
                // Carregar as notas de manutenção associadas a este plano no período
                $notes = \App\Models\MaintenanceNote::where('maintenance_plan_id', $plan->id)
                    ->whereBetween('note_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->with('user')
                    ->orderBy('note_date', 'asc')
                    ->get();
                    
                $planWithDates->notes = $notes;
                $processedPlans[] = $planWithDates;
            }
        }
        
        // Organizar os planos processados por frequência
        $plansByFrequency = collect($processedPlans)->groupBy('frequency_type');
        
        // Prepare logo and company info
        $companyLogo = null;
        $logoPath = Setting::get('company_logo');
        if ($logoPath) {
            $companyLogo = 'storage/' . $logoPath;
        }
        $companyName = Setting::get('company_name', 'Company');
        
        // Prepare additional company info for report header
        $companyAddress = Setting::get('company_address', '');
        $companyPhone = Setting::get('company_phone', '');
        $companyEmail = Setting::get('company_email', '');
        $companyWebsite = Setting::get('company_website', '');
        $companyTaxId = Setting::get('company_tax_id', '');
        
        // Generate PDF
        $pdf = PDF::loadView('pdf.maintenance-plan-report', [
            'plans' => $processedPlans,
            'plansByFrequency' => $plansByFrequency,
            'reportType' => $this->reportType,
            'selectedMonth' => $this->selectedMonth,
            'selectedDay' => $this->selectedDay,
            'periodStart' => $this->periodStart,
            'periodEnd' => $this->periodEnd,
            'periodTitle' => $periodTitle,
            'startDate' => $startDate->format(Setting::getSystemDateFormat()),
            'endDate' => $endDate->format(Setting::getSystemDateFormat()),
            'generatedAt' => Carbon::now()->format(Setting::getSystemDateTimeFormat()),
            'companyName' => $companyName,
            'companyLogo' => $companyLogo,
            'companyAddress' => $companyAddress,
            'companyPhone' => $companyPhone,
            'companyEmail' => $companyEmail,
            'companyWebsite' => $companyWebsite,
            'companyTaxId' => $companyTaxId
        ]);
        
        // Generate a unique filename based on report type
        $reportTypeSuffix = '';
        switch ($this->reportType) {
            case 'day':
                $reportTypeSuffix = '_day_' . $this->selectedDay;
                break;
            case 'period':
                $reportTypeSuffix = '_period_' . $this->periodStart . '_' . $this->periodEnd;
                break;
            default:
                $reportTypeSuffix = '_month_' . $this->selectedMonth;
        }
        
        $filename = 'maintenance_plan_report' . $reportTypeSuffix . '_' . time() . '.pdf';
        
        // Save to storage
        Storage::disk('public')->put('reports/' . $filename, $pdf->output());
        
        // Set the URL for download
        $this->pdfUrl = Storage::disk('public')->url('reports/' . $filename);
        
        $this->generatingPdf = false;
        
        // Show success message using standard notification format
        $this->dispatch('notify', 
            type: 'success', 
            title: __('messages.success'),
            message: __('livewire/maintenance/plan-report.pdf_generated_successfully')
        );
        
        // Dispatch browser event to trigger download
        $this->dispatch('pdfGenerated', $this->pdfUrl);
    }
    
    /**
     * Gera todas as ocorrências de um plano de manutenção dentro do período mensal
     * com base no tipo de frequência configurado
     *
     * @param MaintenancePlan $plan O plano de manutenção
     * @param Carbon $startOfMonth Data de início do mês
     * @param Carbon $endOfMonth Data de fim do mês
     * @return array Array com objetos Carbon das datas de ocorrência
     */
    private function generateOccurrences($plan, $startOfMonth, $endOfMonth)
    {
        $occurrences = [];
        $scheduledDate = Carbon::parse($plan->scheduled_date);
        $processedDates = []; // Rastreador para evitar duplicações

        // Se a data agendada estiver fora do período e depois do fim do período,
        // não teremos ocorrências desse plano neste mês
        if ($scheduledDate->greaterThan($endOfMonth)) {
            return $occurrences;
        }

        // Para planos do tipo 'once' (única vez)
        if ($plan->frequency_type === 'once') {
            // Se cair dentro do período, adiciona
            if ($scheduledDate->greaterThanOrEqualTo($startOfMonth) && $scheduledDate->lessThanOrEqualTo($endOfMonth)) {
                // Ajustar para evitar domingos e feriados mesmo para planos de ocorrência única
                $adjustedDate = $this->adjustForHolidaysAndSundays($scheduledDate->copy());
                $occurrences[] = $adjustedDate;
            }
            return $occurrences;
        }

        // Para planos recorrentes
        $currentDate = $scheduledDate->copy();

        // Se a data agendada for anterior ao início do período, precisamos avançar
        // para a primeira ocorrência dentro do período
        while ($currentDate->lessThan($startOfMonth)) {
            $currentDate = $this->getNextOccurrence($currentDate, $plan, $processedDates);
        }

        // Limitar o número máximo de iterações para evitar loops infinitos em caso de erro
        $maxIterations = 100;
        $iteration = 0;
        
        // Agora adiciona todas as ocorrências dentro do período
        while ($currentDate->lessThanOrEqualTo($endOfMonth) && $iteration < $maxIterations) {
            $iteration++;
            $currentDateStr = $currentDate->format('Y-m-d');
            
            // Evitar duplicações: verificar se já processamos esta data
            if (isset($processedDates[$currentDateStr])) {
                $currentDate = $this->getNextOccurrence($currentDate, $plan, $processedDates);
                continue;
            }
            
            $occurrences[] = $currentDate->copy();
            $processedDates[$currentDateStr] = true;
            
            $currentDate = $this->getNextOccurrence($currentDate, $plan, $processedDates);
        }

        return $occurrences;
    }
    
    /**
     * Calcula a próxima ocorrência com base na frequência
     *
     * @param Carbon $currentDate Data atual
     * @param MaintenancePlan $plan Plano de manutenção
     * @param array &$processedDates Array para rastreamento de datas já processadas
     * @return Carbon A data da próxima ocorrência
     */
    private function getNextOccurrence($currentDate, $plan, &$processedDates = [])
    {
        $nextDate = $currentDate->copy();

        switch ($plan->frequency_type) {
            case 'daily':
                $nextDate = $nextDate->addDay();
                // Verifica se cai em domingo ou feriado e ajusta se necessário
                return $this->adjustForHolidaysAndSundays($nextDate);

            case 'custom':
                // Avança o número de dias personalizados
                $nextDate = $nextDate->addDays($plan->custom_days ?? 1);
                // Verifica se cai em domingo ou feriado e ajusta se necessário
                return $this->adjustForHolidaysAndSundays($nextDate);

            case 'weekly':
                // Se um dia da semana estiver definido, avança para o próximo dia específico
                if (!is_null($plan->day_of_week)) {
                    // Primeiro avança uma semana
                    $nextDate = $nextDate->addWeek();
                    // Depois ajusta para o dia da semana desejado
                    $nextDate = $nextDate->startOfWeek()->addDays($plan->day_of_week);
                    
                    // Verifica se o dia da semana selecionado não é domingo (0)
                    // Se for domingo, avança para segunda-feira
                    if ($plan->day_of_week === 0) {
                        $nextDate = $nextDate->addDay();
                    }
                    
                    // Verifica se cai em feriado e ajusta se necessário
                    return $this->adjustForHolidays($nextDate);
                }
                // Se nenhum dia específico, simplesmente avança 7 dias
                $nextDate = $nextDate->addWeek();
                // Verifica se cai em domingo ou feriado e ajusta se necessário
                return $this->adjustForHolidaysAndSundays($nextDate);

            case 'monthly':
                // Se um dia do mês estiver definido
                if (!is_null($plan->day_of_month)) {
                    $nextDate = $nextDate->addMonth();
                    $daysInMonth = $nextDate->daysInMonth;
                    
                    // Certifica-se de que o dia não excede o total de dias no mês
                    $dayOfMonth = min($plan->day_of_month, $daysInMonth);
                    
                    $nextDate = $nextDate->startOfMonth()->addDays($dayOfMonth - 1);
                    // Verifica se cai em domingo ou feriado e ajusta se necessário
                    return $this->adjustForHolidaysAndSundays($nextDate);
                }
                // Se nenhum dia específico, simplesmente avança um mês
                $nextDate = $nextDate->addMonth();
                // Verifica se cai em domingo ou feriado e ajusta se necessário
                return $this->adjustForHolidaysAndSundays($nextDate);

            case 'yearly':
                // Avança um ano
                $nextDate = $nextDate->addYear();
                // Verifica se cai em domingo ou feriado e ajusta se necessário
                return $this->adjustForHolidaysAndSundays($nextDate);

            case 'once':
            default:
                // Para planos que ocorrem apenas uma vez, não há próxima ocorrência
                return $nextDate->addYear(); // Avança um ano para garantir que saia do loop
        }
    }
    
    /**
     * Ajusta a data se cair em domingo ou feriado
     * 
     * @param Carbon $date Data a ser verificada
     * @param array &$processedDates Array para rastreamento de datas já processadas
     * @return Carbon Data ajustada
     */
    private function adjustForHolidaysAndSundays($date, &$processedDates = [])
    {
        // Primeiro ajusta para a próxima data disponível se for domingo
        if ($date->isSunday()) {
            $date = $date->addDay(); // Avança para segunda-feira
        }
        
        // Depois verifica se a nova data cai em um feriado
        return $this->adjustForHolidays($date, $processedDates);
    }
    
    /**
     * Ajusta a data se cair em um feriado ou se já tiver sido processada
     * 
     * @param Carbon $date Data a ser verificada
     * @param array &$processedDates Array para rastreamento de datas já processadas
     * @return Carbon Data ajustada
     */
    private function adjustForHolidays($date, &$processedDates = [])
    {
        $dateStr = $date->format('Y-m-d');
        $maxAdjustments = 20; // Limite de ajustes para evitar loops infinitos
        $adjustCount = 0;
        
        // Continua ajustando enquanto a data cair em um feriado ou já tiver sido processada
        while ((isset($this->holidays[$dateStr]) || isset($processedDates[$dateStr])) && $adjustCount < $maxAdjustments) {
            $adjustCount++;
            $date = $date->addDay();
            
            // Se o novo dia for domingo, avança mais um dia
            if ($date->isSunday()) {
                $date = $date->addDay();
            }
            
            $dateStr = $date->format('Y-m-d');
        }
        
        return $date;
    }


    /**
     * Atualiza os feriados quando o mês selecionado é alterado
     */
    public function updatedSelectedMonth($value)
    {
        // Recarregar os feriados para o novo mês selecionado
        $this->loadHolidays();
        // Resetar a paginação quando mudar o mês
        $this->resetPage();
    }

    public function getFilteredPlans($paginate = true)
    {
        // Determinar período com base no tipo de relatório selecionado
        $startDate = null;
        $endDate = null;
        
        switch ($this->reportType) {
            case 'day':
                // Dia específico
                $startDate = Carbon::parse($this->selectedDay)->startOfDay();
                $endDate = Carbon::parse($this->selectedDay)->endOfDay();
                break;
                
            case 'period':
                // Período personalizado
                $startDate = Carbon::parse($this->periodStart)->startOfDay();
                $endDate = Carbon::parse($this->periodEnd)->endOfDay();
                break;
                
            case 'month':
            default:
                // Mês completo (padrão)
                list($year, $month) = explode('-', $this->selectedMonth);
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
                break;
        }
        
        // Buscar todos os planos ativos
        $query = MaintenancePlan::with(['equipment', 'task', 'line', 'area', 'assignedTo'])
            ->where(function($query) use ($endDate) {
                // Incluir planos únicos com data até o fim do período selecionado
                $query->where(function($q) use ($endDate) {
                    $q->where('frequency_type', 'once')
                      ->where('scheduled_date', '<=', $endDate);
                });
                
                // OU planos recorrentes com data de início até o fim do período
                $query->orWhere(function($q) use ($endDate) {
                    $q->where('frequency_type', '!=', 'once')
                      ->where('scheduled_date', '<=', $endDate);
                });
            });
        
        // Aplicar filtros adicionais
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }
        
        if (!empty($this->type)) {
            $query->where('type', $this->type);
        }
        
        if (!empty($this->equipment_id)) {
            $query->where('equipment_id', $this->equipment_id);
        }
        
        if (!empty($this->task_id)) {
            $query->where('task_id', $this->task_id);
        }
        
        if (!empty($this->line_id)) {
            $query->whereHas('equipment', function($q) {
                $q->where('line_id', $this->line_id);
            });
        }
        
        if (!empty($this->area_id)) {
            $query->whereHas('equipment', function($q) {
                $q->where('area_id', $this->area_id);
            });
        }
        
        // Ordenar resultados
        $query->orderBy($this->sortField, $this->sortDirection);
        
        // Obter todos os planos que atendem aos filtros
        $allPlans = $query->get();
        
        // Array para armazenar planos com ocorrências no período selecionado
        $plansWithOccurrences = [];
        
        // Filtrar apenas os planos que têm ocorrências no período selecionado
        foreach ($allPlans as $plan) {
            $occurrences = $this->generateOccurrences($plan, $startDate, $endDate);
            if (count($occurrences) > 0) {
                // Adicionar as ocorrências calculadas ao plano
                $plan->calculatedOccurrences = $occurrences;
                $plansWithOccurrences[] = $plan;
            }
        }
        
        // Criar uma collection a partir do array filtrado
        $collection = collect($plansWithOccurrences);
        
        // Retornar com ou sem paginação
        if ($paginate) {
            $perPage = 15;
            return new LengthAwarePaginator(
                $collection->forPage($this->getPage(), $perPage),
                $collection->count(),
                $perPage,
                $this->getPage(),
                ['path' => request()->url()]
            );
        } else {
            return $collection;
        }
    }
    
    protected function getPage()
    {
        return request()->input('page', 1);
    }

    /**
     * Método estático para calcular ocorrências de um plano de manutenção em um período
     * Esta função é usada diretamente no template blade
     * 
     * @param MaintenancePlan $plan O plano de manutenção
     * @param Carbon $startOfMonth Início do período
     * @param Carbon $endOfMonth Fim do período
     * @return array Array de objetos Carbon com as datas de ocorrência
     */
    public static function generatePlannedOccurrences($plan, $startOfMonth, $endOfMonth)
    {
        $occurrences = [];
        $processedDates = []; // Rastreador para evitar duplicações
        $scheduledDate = Carbon::parse($plan->scheduled_date);
        
        // Se for um plano de ocorrência única, verificar se está no período
        if ($plan->frequency_type === 'once') {
            if ($scheduledDate->greaterThanOrEqualTo($startOfMonth) && $scheduledDate->lessThanOrEqualTo($endOfMonth)) {
                $occurrences[] = $scheduledDate->copy();
            }
            return $occurrences;
        }
        
        // Para planos recorrentes, calcular ocorrências
        $currentDate = $scheduledDate->copy();
        
        // Avançar até o início do mês
        while ($currentDate->lessThan($startOfMonth)) {
            // Calcular próxima data com base na frequência
            switch ($plan->frequency_type) {
                case 'daily':
                    $currentDate = $currentDate->addDay();
                    break;
                case 'weekly':
                    $targetDayOfWeek = $plan->frequency_day_of_week ?? 1; // Default: segunda
                    $currentDate = $currentDate->addWeek();
                    $currentDate = $currentDate->previous((int)$targetDayOfWeek);
                    break;
                case 'monthly':
                    $targetDay = $plan->frequency_day ?? 1; // Default: dia 1
                    $currentDate = $currentDate->addMonth();
                    $daysInMonth = $currentDate->daysInMonth;
                    $day = min($targetDay, $daysInMonth);
                    $currentDate->day = $day;
                    break;
                case 'yearly':
                    $currentDate = $currentDate->addYear();
                    break;
                case 'custom':
                    $customDays = $plan->custom_days ?? 7; // Default: 7 dias
                    $currentDate = $currentDate->addDays($customDays);
                    break;
                default:
                    $currentDate = $currentDate->addDay(); // Fallback
            }
            
            // Evitar loop infinito
            if ($currentDate->greaterThan($endOfMonth)) {
                return $occurrences;
            }
        }
        
        // Limite de iterações para evitar loops infinitos
        $maxIterations = 100;
        $iteration = 0;
        
        // Adicionar ocorrências dentro do período
        while ($currentDate->lessThanOrEqualTo($endOfMonth) && $iteration < $maxIterations) {
            $iteration++;
            $currentDateStr = $currentDate->format('Y-m-d');
            
            // Evitar duplicações
            if (isset($processedDates[$currentDateStr])) {
                continue;
            }
            
            // Verificar se é domingo ou feriado
            if (!$currentDate->isSunday()) {
                // Verificar feriados - simplificado pois não temos acesso aos feriados aqui
                // Em um ambiente real, isso seria feito com acesso ao banco de dados
                $occurrences[] = $currentDate->copy();
                $processedDates[$currentDateStr] = true;
            }
            
            // Avançar para a próxima data
            switch ($plan->frequency_type) {
                case 'daily':
                    $currentDate = $currentDate->addDay();
                    break;
                case 'weekly':
                    $currentDate = $currentDate->addWeek();
                    break;
                case 'monthly':
                    $currentDate = $currentDate->addMonth();
                    break;
                case 'yearly':
                    $currentDate = $currentDate->addYear();
                    break;
                case 'custom':
                    $customDays = $plan->custom_days ?? 7;
                    $currentDate = $currentDate->addDays($customDays);
                    break;
                default:
                    $currentDate = $currentDate->addDay();
            }
        }
        
        return $occurrences;
    }
    
    public function render()
    {
        $equipments = MaintenanceEquipment::orderBy('name')->get();
        $tasks = MaintenanceTask::orderBy('title')->get();
        
        // Get lines and areas for filters
        $lines = \App\Models\MaintenanceLine::orderBy('name')->get();
        $areas = \App\Models\MaintenanceArea::orderBy('name')->get();
        
        return view('livewire.maintenance-plan-report', [
            'plans' => $this->getFilteredPlans(),
            'equipments' => $equipments,
            'tasks' => $tasks,
            'lines' => $lines,
            'areas' => $areas,
            'statusOptions' => [
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'schedule' => 'Schedule'
            ],
            'typeOptions' => [
                'preventive' => 'Preventive',
                'predictive' => 'Predictive',
                'conditional' => 'Conditional',
                'other' => 'Other'
            ]
        ]);
    }
}

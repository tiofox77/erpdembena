<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceTask;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class MaintenancePlanReport extends Component
{
    use WithPagination;

    // Filters
    public $selectedMonth; // Mês selecionado no formato 'YYYY-MM'
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

    public function mount()
    {
        // Default to current month (YYYY-MM format)
        $this->selectedMonth = Carbon::now()->format('Y-m');
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
        $this->selectedMonth = Carbon::now()->format('Y-m');
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
        
        // Preparar informações de mês e período
        list($year, $month) = explode('-', $this->selectedMonth);
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();
        $monthTitle = $startOfMonth->translatedFormat('F Y'); // Nome do mês e ano na língua atual
        
        // Processa cada plano para calcular suas datas de manutenção no mês
        $processedPlans = [];
        
        foreach ($plans as $plan) {
            // Calcular datas de ocorrência para este plano no mês selecionado
            $occurrences = $this->generateOccurrences($plan, $startOfMonth, $endOfMonth);
            
            if (count($occurrences) > 0) {
                // Criar uma cópia do plano com suas datas de ocorrência
                $planWithDates = clone $plan;
                $planWithDates->occurrences = $occurrences;
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
        
        // Generate PDF
        $pdf = PDF::loadView('pdf.maintenance-plan-report', [
            'plans' => $processedPlans,
            'plansByFrequency' => $plansByFrequency,
            'selectedMonth' => $this->selectedMonth,
            'monthTitle' => $monthTitle,
            'startDate' => $startOfMonth->format(Setting::getSystemDateFormat()),
            'endDate' => $endOfMonth->format(Setting::getSystemDateFormat()),
            'generatedAt' => Carbon::now()->format(Setting::getSystemDateTimeFormat()),
            'companyName' => $companyName,
            'companyLogo' => $companyLogo
        ]);
        
        // Generate a unique filename
        $filename = 'maintenance_plan_report_' . time() . '.pdf';
        
        // Save to storage
        Storage::disk('public')->put('reports/' . $filename, $pdf->output());
        
        // Set the URL for download
        $this->pdfUrl = Storage::disk('public')->url('reports/' . $filename);
        
        $this->generatingPdf = false;
        
        // Show success message
        session()->flash('success', 'PDF report generated successfully!');
        
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
                $occurrences[] = $scheduledDate->copy();
            }
            return $occurrences;
        }

        // Para planos recorrentes
        $currentDate = $scheduledDate->copy();

        // Se a data agendada for anterior ao início do período, precisamos avançar
        // para a primeira ocorrência dentro do período
        while ($currentDate->lessThan($startOfMonth)) {
            $currentDate = $this->getNextOccurrence($currentDate, $plan);
        }

        // Agora adiciona todas as ocorrências dentro do período
        while ($currentDate->lessThanOrEqualTo($endOfMonth)) {
            $currentDateStr = $currentDate->format('Y-m-d');
            
            // Evitar duplicações: verificar se já processamos esta data
            if (isset($processedDates[$currentDateStr])) {
                $currentDate = $this->getNextOccurrence($currentDate, $plan);
                continue;
            }
            
            $occurrences[] = $currentDate->copy();
            $processedDates[$currentDateStr] = true;
            
            $currentDate = $this->getNextOccurrence($currentDate, $plan);
        }

        return $occurrences;
    }
    
    /**
     * Calcula a próxima ocorrência com base na frequência
     *
     * @param Carbon $currentDate Data atual
     * @param MaintenancePlan $plan Plano de manutenção
     * @return Carbon A data da próxima ocorrência
     */
    private function getNextOccurrence($currentDate, $plan)
    {
        $nextDate = $currentDate->copy();

        switch ($plan->frequency_type) {
            case 'daily':
                return $nextDate->addDay();

            case 'custom':
                // Avança o número de dias personalizados
                return $nextDate->addDays($plan->custom_days ?? 1);

            case 'weekly':
                // Se um dia da semana estiver definido, avança para o próximo dia específico
                if (!is_null($plan->day_of_week)) {
                    // Primeiro avança uma semana
                    $nextDate = $nextDate->addWeek();
                    // Depois ajusta para o dia da semana desejado
                    return $nextDate->startOfWeek()->addDays($plan->day_of_week);
                }
                // Se nenhum dia específico, simplesmente avança 7 dias
                return $nextDate->addWeek();

            case 'monthly':
                // Se um dia do mês estiver definido
                if (!is_null($plan->day_of_month)) {
                    $nextDate = $nextDate->addMonth();
                    $daysInMonth = $nextDate->daysInMonth;
                    
                    // Certifica-se de que o dia não excede o total de dias no mês
                    $dayOfMonth = min($plan->day_of_month, $daysInMonth);
                    
                    return $nextDate->startOfMonth()->addDays($dayOfMonth - 1);
                }
                // Se nenhum dia específico, simplesmente avança um mês
                return $nextDate->addMonth();

            case 'yearly':
                // Avança um ano
                return $nextDate->addYear();

            case 'once':
            default:
                // Para planos que ocorrem apenas uma vez, não há próxima ocorrência
                return $nextDate->addYear(); // Avança um ano para garantir que saia do loop
        }
    }

    public function getFilteredPlans($paginate = true)
    {
        $query = MaintenancePlan::query()
            ->with(['equipment', 'task', 'line', 'area', 'assignedTo'])
            ->when($this->selectedMonth, function ($query) {
                // Extrai ano e mês da string YYYY-MM
                list($year, $month) = explode('-', $this->selectedMonth);
                // Filtra planos para o mês selecionado
                return $query->whereDate('scheduled_date', '<=', Carbon::createFromDate($year, $month, 1)->endOfMonth());
            })
            ->when($this->status, function ($query) {
                return $query->where('status', $this->status);
            })
            ->when($this->type, function ($query) {
                return $query->where('type', $this->type);
            })
            ->when($this->equipment_id, function ($query) {
                return $query->where('equipment_id', $this->equipment_id);
            })
            ->when($this->task_id, function ($query) {
                return $query->where('task_id', $this->task_id);
            })
            ->when($this->line_id, function ($query) {
                return $query->where('line_id', $this->line_id);
            })
            ->when($this->area_id, function ($query) {
                return $query->where('area_id', $this->area_id);
            })
            ->orderBy($this->sortField, $this->sortDirection);
            
        return $paginate ? $query->paginate(15) : $query->get();
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

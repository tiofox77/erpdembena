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
    public $startDate;
    public $endDate;
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
        // Default to current month
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
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
        $this->reset(['status', 'type', 'equipment_id', 'task_id', 'line_id', 'area_id']);
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function generatePdf()
    {
        $this->generatingPdf = true;
        
        // Get the filtered data without pagination
        $plans = $this->getFilteredPlans(false);
        
        // Get company information
        $companyName = Setting::get('company_name', config('app.name'));
        $companyLogoPath = Setting::get('company_logo', null);
        $companyLogo = null;
        
        // Process logo for PDF if it exists
        if ($companyLogoPath && Storage::disk('public')->exists($companyLogoPath)) {
            $companyLogo = 'data:image/png;base64,' . base64_encode(
                Storage::disk('public')->get($companyLogoPath)
            );
        }
        
        // Generate PDF
        $pdf = PDF::loadView('pdf.maintenance-plan-report', [
            'plans' => $plans,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'generatedAt' => Carbon::now()->format('Y-m-d H:i:s'),
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
    
    public function getFilteredPlans($paginate = true)
    {
        $query = MaintenancePlan::query()
            ->with(['equipment', 'task', 'line', 'area', 'assignedTo'])
            ->when($this->startDate, function ($query) {
                return $query->whereDate('scheduled_date', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($query) {
                return $query->whereDate('scheduled_date', '<=', $this->endDate);
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

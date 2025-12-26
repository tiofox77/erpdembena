<?php

declare(strict_types=1);

namespace App\Livewire\HR\PerformanceEvaluations;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\HR\PerformanceEvaluation;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * Performance Evaluations Livewire Component
 * Based on Quarterly Performance Appraisal Form
 */
class PerformanceEvaluations extends Component
{
    use WithPagination;

    // Component state
    public $showModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    public $showEmployeeSearch = false;
    public $isEditing = false;

    // Form properties - Employee Details
    public $performanceEvaluationId;
    public $employee_id;
    public $supervisor_id;
    public $department_id;
    public $selectedEmployee;
    
    // Evaluation Period
    public $evaluation_quarter = 'Q4';
    public $evaluation_year;
    public $period_start;
    public $period_end;
    public $evaluation_date;
    
    // Performance Criteria (1-5 rating)
    public $productivity_output;
    public $productivity_output_remarks;
    public $quality_of_work;
    public $quality_of_work_remarks;
    public $attendance_punctuality;
    public $attendance_punctuality_remarks;
    public $safety_compliance;
    public $safety_compliance_remarks;
    public $machine_operation_skills;
    public $machine_operation_skills_remarks;
    public $teamwork_cooperation;
    public $teamwork_cooperation_remarks;
    public $adaptability_learning;
    public $adaptability_learning_remarks;
    public $housekeeping_5s;
    public $housekeeping_5s_remarks;
    public $discipline_attitude;
    public $discipline_attitude_remarks;
    public $initiative_responsibility;
    public $initiative_responsibility_remarks;
    
    // Overall Performance Summary
    public $average_score;
    public $performance_level;
    public $eligible_for_bonus = false;
    
    // Comments
    public $supervisor_comments;
    public $employee_comments;
    
    // Status
    public $status = 'draft';

    // Search and filter
    public $search = '';
    public $statusFilter = '';
    public $quarterFilter = '';
    public $yearFilter = '';
    public $perPage = 15;

    // Employee selection
    public $employeeSearch = '';
    public $employees = [];

    protected $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'supervisor_id' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'evaluation_quarter' => ['required', Rule::in(['Q1', 'Q2', 'Q3', 'Q4'])],
            'evaluation_year' => 'required|integer|min:2020|max:2050',
            'evaluation_date' => 'nullable|date',
            
            // Performance Criteria (0-5)
            'productivity_output' => 'nullable|integer|between:0,5',
            'quality_of_work' => 'nullable|integer|between:0,5',
            'attendance_punctuality' => 'nullable|integer|between:0,5',
            'safety_compliance' => 'nullable|integer|between:0,5',
            'machine_operation_skills' => 'nullable|integer|between:0,5',
            'teamwork_cooperation' => 'nullable|integer|between:0,5',
            'adaptability_learning' => 'nullable|integer|between:0,5',
            'housekeeping_5s' => 'nullable|integer|between:0,5',
            'discipline_attitude' => 'nullable|integer|between:0,5',
            'initiative_responsibility' => 'nullable|integer|between:0,5',
            
            // Remarks
            'productivity_output_remarks' => 'nullable|string|max:500',
            'quality_of_work_remarks' => 'nullable|string|max:500',
            'attendance_punctuality_remarks' => 'nullable|string|max:500',
            'safety_compliance_remarks' => 'nullable|string|max:500',
            'machine_operation_skills_remarks' => 'nullable|string|max:500',
            'teamwork_cooperation_remarks' => 'nullable|string|max:500',
            'adaptability_learning_remarks' => 'nullable|string|max:500',
            'housekeeping_5s_remarks' => 'nullable|string|max:500',
            'discipline_attitude_remarks' => 'nullable|string|max:500',
            'initiative_responsibility_remarks' => 'nullable|string|max:500',
            
            // Summary
            'eligible_for_bonus' => 'boolean',
            'supervisor_comments' => 'nullable|string|max:2000',
            'employee_comments' => 'nullable|string|max:2000',
            
            'status' => ['required', Rule::in(array_keys(PerformanceEvaluation::STATUSES))],
        ];
    }

    public function mount(): void
    {
        $this->evaluation_year = now()->year;
        $this->evaluation_date = now()->format('Y-m-d');
        $this->yearFilter = now()->year;
        $this->setQuarterDates();
    }

    public function render()
    {
        $evaluations = PerformanceEvaluation::with(['employee', 'supervisor', 'department', 'createdByUser'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('id_card', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->quarterFilter, function ($query) {
                $query->where('evaluation_quarter', $this->quarterFilter);
            })
            ->when($this->yearFilter, function ($query) {
                $query->where('evaluation_year', $this->yearFilter);
            })
            ->latest('created_at')
            ->paginate($this->perPage);

        return view('livewire.hr.performance-evaluations.performance-evaluations', [
            'evaluations' => $evaluations,
            'quarters' => PerformanceEvaluation::QUARTERS,
            'statuses' => PerformanceEvaluation::STATUSES,
            'ratings' => PerformanceEvaluation::RATINGS,
            'criteria' => PerformanceEvaluation::CRITERIA,
            'performanceLevels' => PerformanceEvaluation::PERFORMANCE_LEVELS,
            'departments' => Department::orderBy('name')->get(),
            'years' => range(now()->year, 2020),
        ]);
    }

    public function updatedEvaluationQuarter(): void
    {
        $this->setQuarterDates();
    }

    public function updatedEvaluationYear(): void
    {
        $this->setQuarterDates();
    }

    private function setQuarterDates(): void
    {
        if ($this->evaluation_quarter && $this->evaluation_year) {
            $dates = PerformanceEvaluation::getQuarterDates($this->evaluation_quarter, (int) $this->evaluation_year);
            $this->period_start = $dates['start']->format('Y-m-d');
            $this->period_end = $dates['end']->format('Y-m-d');
        }
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function openEmployeeSearch(): void
    {
        $this->showEmployeeSearch = true;
        $this->searchEmployees();
    }

    public function closeEmployeeSearch(): void
    {
        $this->showEmployeeSearch = false;
        $this->employeeSearch = '';
        $this->employees = [];
    }

    public function searchEmployees(): void
    {
        $this->employees = Employee::with(['department', 'position'])
            ->where(function($query) {
                $query->where('full_name', 'like', '%' . $this->employeeSearch . '%')
                      ->orWhere('id_card', 'like', '%' . $this->employeeSearch . '%')
                      ->orWhere('id', 'like', '%' . $this->employeeSearch . '%');
            })
            ->limit(50)
            ->get();
    }

    public function selectEmployee($employeeId): void
    {
        $employee = Employee::with(['department', 'position'])->find($employeeId);
        $this->selectedEmployee = $employee;
        $this->employee_id = $employeeId;
        $this->department_id = $employee->department_id;
        $this->supervisor_id = $employee->supervisor_id;
        $this->closeEmployeeSearch();
    }

    public function removeEmployee(): void
    {
        $this->selectedEmployee = null;
        $this->employee_id = null;
        $this->department_id = null;
        $this->supervisor_id = null;
    }

    public function calculateAverageScore(): void
    {
        $scores = [
            $this->productivity_output,
            $this->quality_of_work,
            $this->attendance_punctuality,
            $this->safety_compliance,
            $this->machine_operation_skills,
            $this->teamwork_cooperation,
            $this->adaptability_learning,
            $this->housekeeping_5s,
            $this->discipline_attitude,
            $this->initiative_responsibility,
        ];

        $validScores = array_filter($scores, fn($score) => $score !== null && $score !== '');
        
        if (!empty($validScores)) {
            $this->average_score = round(array_sum($validScores) / count($validScores), 2);
            $this->determinePerformanceLevel();
        }
    }

    public function determinePerformanceLevel(): void
    {
        if ($this->average_score === null) {
            $this->performance_level = null;
            return;
        }

        // Convert 0-5 scale to percentage (0-100%)
        $percentage = ($this->average_score / 5) * 100;

        $this->performance_level = match(true) {
            $percentage >= 80 => 'excellent',
            $percentage >= 74 => 'good',
            $percentage >= 62 => 'satisfactory',
            default => 'needs_improvement',
        };
    }

    public function save(): void
    {
        $this->validate();
        $this->calculateAverageScore();

        $data = [
            'employee_id' => $this->employee_id,
            'supervisor_id' => $this->supervisor_id,
            'department_id' => $this->department_id,
            'evaluation_quarter' => $this->evaluation_quarter,
            'evaluation_year' => $this->evaluation_year,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'evaluation_date' => $this->evaluation_date,
            
            // Criteria
            'productivity_output' => $this->productivity_output,
            'productivity_output_remarks' => $this->productivity_output_remarks,
            'quality_of_work' => $this->quality_of_work,
            'quality_of_work_remarks' => $this->quality_of_work_remarks,
            'attendance_punctuality' => $this->attendance_punctuality,
            'attendance_punctuality_remarks' => $this->attendance_punctuality_remarks,
            'safety_compliance' => $this->safety_compliance,
            'safety_compliance_remarks' => $this->safety_compliance_remarks,
            'machine_operation_skills' => $this->machine_operation_skills,
            'machine_operation_skills_remarks' => $this->machine_operation_skills_remarks,
            'teamwork_cooperation' => $this->teamwork_cooperation,
            'teamwork_cooperation_remarks' => $this->teamwork_cooperation_remarks,
            'adaptability_learning' => $this->adaptability_learning,
            'adaptability_learning_remarks' => $this->adaptability_learning_remarks,
            'housekeeping_5s' => $this->housekeeping_5s,
            'housekeeping_5s_remarks' => $this->housekeeping_5s_remarks,
            'discipline_attitude' => $this->discipline_attitude,
            'discipline_attitude_remarks' => $this->discipline_attitude_remarks,
            'initiative_responsibility' => $this->initiative_responsibility,
            'initiative_responsibility_remarks' => $this->initiative_responsibility_remarks,
            
            // Summary
            'average_score' => $this->average_score,
            'performance_level' => $this->performance_level,
            'eligible_for_bonus' => $this->eligible_for_bonus,
            'supervisor_comments' => $this->supervisor_comments,
            'employee_comments' => $this->employee_comments,
            
            'status' => $this->status,
            'created_by' => auth()->id(),
        ];

        if ($this->isEditing) {
            $evaluation = PerformanceEvaluation::find($this->performanceEvaluationId);
            $evaluation->update($data);
            session()->flash('message', 'Avaliação atualizada com sucesso!');
        } else {
            PerformanceEvaluation::create($data);
            session()->flash('message', 'Avaliação criada com sucesso!');
        }

        $this->closeModal();
    }

    public function edit($evaluationId): void
    {
        $evaluation = PerformanceEvaluation::with(['employee', 'supervisor', 'department'])->find($evaluationId);
        
        $this->performanceEvaluationId = $evaluation->id;
        $this->employee_id = $evaluation->employee_id;
        $this->selectedEmployee = $evaluation->employee;
        $this->supervisor_id = $evaluation->supervisor_id;
        $this->department_id = $evaluation->department_id;
        $this->evaluation_quarter = $evaluation->evaluation_quarter;
        $this->evaluation_year = $evaluation->evaluation_year;
        $this->period_start = $evaluation->period_start?->format('Y-m-d');
        $this->period_end = $evaluation->period_end?->format('Y-m-d');
        $this->evaluation_date = $evaluation->evaluation_date?->format('Y-m-d');
        
        // Criteria
        $this->productivity_output = $evaluation->productivity_output;
        $this->productivity_output_remarks = $evaluation->productivity_output_remarks;
        $this->quality_of_work = $evaluation->quality_of_work;
        $this->quality_of_work_remarks = $evaluation->quality_of_work_remarks;
        $this->attendance_punctuality = $evaluation->attendance_punctuality;
        $this->attendance_punctuality_remarks = $evaluation->attendance_punctuality_remarks;
        $this->safety_compliance = $evaluation->safety_compliance;
        $this->safety_compliance_remarks = $evaluation->safety_compliance_remarks;
        $this->machine_operation_skills = $evaluation->machine_operation_skills;
        $this->machine_operation_skills_remarks = $evaluation->machine_operation_skills_remarks;
        $this->teamwork_cooperation = $evaluation->teamwork_cooperation;
        $this->teamwork_cooperation_remarks = $evaluation->teamwork_cooperation_remarks;
        $this->adaptability_learning = $evaluation->adaptability_learning;
        $this->adaptability_learning_remarks = $evaluation->adaptability_learning_remarks;
        $this->housekeeping_5s = $evaluation->housekeeping_5s;
        $this->housekeeping_5s_remarks = $evaluation->housekeeping_5s_remarks;
        $this->discipline_attitude = $evaluation->discipline_attitude;
        $this->discipline_attitude_remarks = $evaluation->discipline_attitude_remarks;
        $this->initiative_responsibility = $evaluation->initiative_responsibility;
        $this->initiative_responsibility_remarks = $evaluation->initiative_responsibility_remarks;
        
        // Summary
        $this->average_score = $evaluation->average_score;
        $this->performance_level = $evaluation->performance_level;
        $this->eligible_for_bonus = $evaluation->eligible_for_bonus;
        $this->supervisor_comments = $evaluation->supervisor_comments;
        $this->employee_comments = $evaluation->employee_comments;
        $this->status = $evaluation->status;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function view($evaluationId): void
    {
        $this->performanceEvaluationId = $evaluationId;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->performanceEvaluationId = null;
    }

    public function confirmDelete($evaluationId): void
    {
        $evaluation = PerformanceEvaluation::with('employee')->find($evaluationId);
        $this->performanceEvaluationId = $evaluation->id;
        $this->selectedEmployee = $evaluation->employee;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        PerformanceEvaluation::find($this->performanceEvaluationId)->delete();
        session()->flash('message', 'Avaliação excluída com sucesso!');
        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->performanceEvaluationId = null;
        $this->selectedEmployee = null;
    }

    private function resetForm(): void
    {
        $this->performanceEvaluationId = null;
        $this->employee_id = null;
        $this->selectedEmployee = null;
        $this->supervisor_id = null;
        $this->department_id = null;
        $this->evaluation_quarter = 'Q4';
        $this->evaluation_year = now()->year;
        $this->evaluation_date = now()->format('Y-m-d');
        $this->setQuarterDates();
        
        // Reset criteria
        $this->productivity_output = null;
        $this->productivity_output_remarks = null;
        $this->quality_of_work = null;
        $this->quality_of_work_remarks = null;
        $this->attendance_punctuality = null;
        $this->attendance_punctuality_remarks = null;
        $this->safety_compliance = null;
        $this->safety_compliance_remarks = null;
        $this->machine_operation_skills = null;
        $this->machine_operation_skills_remarks = null;
        $this->teamwork_cooperation = null;
        $this->teamwork_cooperation_remarks = null;
        $this->adaptability_learning = null;
        $this->adaptability_learning_remarks = null;
        $this->housekeeping_5s = null;
        $this->housekeeping_5s_remarks = null;
        $this->discipline_attitude = null;
        $this->discipline_attitude_remarks = null;
        $this->initiative_responsibility = null;
        $this->initiative_responsibility_remarks = null;
        
        // Reset summary
        $this->average_score = null;
        $this->performance_level = null;
        $this->eligible_for_bonus = false;
        $this->supervisor_comments = null;
        $this->employee_comments = null;
        $this->status = 'draft';
        $this->isEditing = false;
    }

    public function updatedEmployeeSearch(): void
    {
        $this->searchEmployees();
    }

    // Auto-calculate on any criteria change
    public function updated($property): void
    {
        $criteriaFields = [
            'productivity_output', 'quality_of_work', 'attendance_punctuality',
            'safety_compliance', 'machine_operation_skills', 'teamwork_cooperation',
            'adaptability_learning', 'housekeeping_5s', 'discipline_attitude',
            'initiative_responsibility'
        ];

        if (in_array($property, $criteriaFields)) {
            $this->calculateAverageScore();
        }
    }
}

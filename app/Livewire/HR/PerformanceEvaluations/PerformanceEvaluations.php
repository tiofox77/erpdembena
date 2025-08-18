<?php

declare(strict_types=1);

namespace App\Livewire\HR\PerformanceEvaluations;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\HR\PerformanceEvaluation;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PerformanceEvaluations extends Component
{
    use WithPagination, WithFileUploads;

    // Component properties
    public $showModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    public $showEmployeeSearch = false;
    public $isEditing = false;

    // Form properties
    public $performanceEvaluationId;
    public $employee_id;
    public $evaluation_type = 'annual';
    public $period_start;
    public $period_end;
    public $overall_score;
    public $goals_achievement;
    public $technical_skills;
    public $soft_skills;
    public $attendance_punctuality;
    public $teamwork_collaboration;
    public $initiative_innovation;
    public $quality_of_work;
    public $strengths;
    public $areas_for_improvement;
    public $development_plan;
    public $additional_comments;
    public $status = 'draft';
    public $evaluation_date;
    public $next_evaluation_date;
    public $attachments = [];

    // Search and filter properties
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $dateFilter = '';
    public $perPage = 15;

    // Employee selection
    public $selectedEmployee;
    public $employeeSearch = '';
    public $employees = [];

    protected $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'evaluation_type' => ['required', Rule::in(array_keys(PerformanceEvaluation::EVALUATION_TYPES))],
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'overall_score' => 'nullable|numeric|between:0,10',
            'goals_achievement' => 'nullable|numeric|between:0,10',
            'technical_skills' => 'nullable|numeric|between:0,10',
            'soft_skills' => 'nullable|numeric|between:0,10',
            'attendance_punctuality' => 'nullable|numeric|between:0,10',
            'teamwork_collaboration' => 'nullable|numeric|between:0,10',
            'initiative_innovation' => 'nullable|numeric|between:0,10',
            'quality_of_work' => 'nullable|numeric|between:0,10',
            'strengths' => 'nullable|string|max:1000',
            'areas_for_improvement' => 'nullable|string|max:1000',
            'development_plan' => 'nullable|string|max:1000',
            'additional_comments' => 'nullable|string|max:1000',
            'status' => ['required', Rule::in(array_keys(PerformanceEvaluation::STATUSES))],
            'evaluation_date' => 'required|date',
            'next_evaluation_date' => 'nullable|date|after:evaluation_date',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }

    protected function messages(): array
    {
        return [
            'employee_id.required' => __('messages.employee_required'),
            'employee_id.exists' => __('messages.employee_invalid'),
            'evaluation_type.required' => __('messages.evaluation_type_required'),
            'period_start.required' => __('messages.period_start_required'),
            'period_start.date' => __('messages.period_start_invalid_date'),
            'period_end.required' => __('messages.period_end_required'),
            'period_end.date' => __('messages.period_end_invalid_date'),
            'period_end.after_or_equal' => __('messages.period_end_after_start'),
            'evaluation_date.required' => __('messages.evaluation_date_required'),
            'evaluation_date.date' => __('messages.evaluation_date_invalid'),
            'next_evaluation_date.after' => __('messages.next_evaluation_date_after'),
            '*.numeric' => __('messages.score_must_be_numeric'),
            '*.between' => __('messages.score_between_0_and_10'),
            'attachments.*.file' => __('messages.attachment_must_be_file'),
            'attachments.*.mimes' => __('messages.attachment_invalid_type'),
            'attachments.*.max' => __('messages.attachment_too_large'),
        ];
    }

    public function mount(): void
    {
        $this->evaluation_date = now()->format('Y-m-d');
    }

    public function render()
    {
        $evaluations = PerformanceEvaluation::with(['employee', 'evaluatedByUser'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('id_card', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('evaluation_type', $this->typeFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $query->whereDate('evaluation_date', '>=', $this->dateFilter);
            })
            ->latest('evaluation_date')
            ->paginate($this->perPage);

        return view('livewire.hr.performance-evaluations.performance-evaluations', [
            'evaluations' => $evaluations,
            'evaluationTypes' => PerformanceEvaluation::EVALUATION_TYPES,
            'statuses' => PerformanceEvaluation::STATUSES,
        ]);
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
            ->where('full_name', 'like', '%' . $this->employeeSearch . '%')
            ->orWhere('id_card', 'like', '%' . $this->employeeSearch . '%')
            ->limit(50)
            ->get();
    }

    public function selectEmployee($employeeId): void
    {
        $this->selectedEmployee = Employee::find($employeeId);
        $this->employee_id = $employeeId;
        $this->closeEmployeeSearch();
    }

    public function removeEmployee(): void
    {
        $this->selectedEmployee = null;
        $this->employee_id = null;
    }

    public function calculateOverallScore(): void
    {
        $scores = [
            $this->goals_achievement,
            $this->technical_skills,
            $this->soft_skills,
            $this->attendance_punctuality,
            $this->teamwork_collaboration,
            $this->initiative_innovation,
            $this->quality_of_work,
        ];

        $nonNullScores = array_filter($scores, fn($score) => $score !== null && $score !== '');
        
        if (!empty($nonNullScores)) {
            $this->overall_score = round(array_sum($nonNullScores) / count($nonNullScores), 2);
        }
    }

    public function save(): void
    {
        $this->validate();

        $attachmentPaths = [];
        if ($this->attachments) {
            foreach ($this->attachments as $attachment) {
                $attachmentPaths[] = [
                    'name' => $attachment->getClientOriginalName(),
                    'path' => $attachment->store('performance-evaluations', 'public'),
                    'size' => $attachment->getSize(),
                    'type' => $attachment->getClientMimeType(),
                ];
            }
        }

        $data = [
            'employee_id' => $this->employee_id,
            'evaluation_type' => $this->evaluation_type,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
            'overall_score' => $this->overall_score,
            'goals_achievement' => $this->goals_achievement,
            'technical_skills' => $this->technical_skills,
            'soft_skills' => $this->soft_skills,
            'attendance_punctuality' => $this->attendance_punctuality,
            'teamwork_collaboration' => $this->teamwork_collaboration,
            'initiative_innovation' => $this->initiative_innovation,
            'quality_of_work' => $this->quality_of_work,
            'strengths' => $this->strengths,
            'areas_for_improvement' => $this->areas_for_improvement,
            'development_plan' => $this->development_plan,
            'additional_comments' => $this->additional_comments,
            'status' => $this->status,
            'evaluation_date' => $this->evaluation_date,
            'next_evaluation_date' => $this->next_evaluation_date,
            'attachments' => $attachmentPaths,
            'evaluated_by' => auth()->id(),
        ];

        if ($this->isEditing) {
            $evaluation = PerformanceEvaluation::find($this->performanceEvaluationId);
            $evaluation->update($data);
            session()->flash('message', __('messages.evaluation_updated_successfully'));
        } else {
            PerformanceEvaluation::create($data);
            session()->flash('message', __('messages.evaluation_created_successfully'));
        }

        $this->closeModal();
    }

    public function edit($evaluationId): void
    {
        $evaluation = PerformanceEvaluation::with('employee')->find($evaluationId);
        
        $this->performanceEvaluationId = $evaluation->id;
        $this->employee_id = $evaluation->employee_id;
        $this->selectedEmployee = $evaluation->employee;
        $this->evaluation_type = $evaluation->evaluation_type;
        $this->period_start = $evaluation->period_start->format('Y-m-d');
        $this->period_end = $evaluation->period_end->format('Y-m-d');
        $this->overall_score = $evaluation->overall_score;
        $this->goals_achievement = $evaluation->goals_achievement;
        $this->technical_skills = $evaluation->technical_skills;
        $this->soft_skills = $evaluation->soft_skills;
        $this->attendance_punctuality = $evaluation->attendance_punctuality;
        $this->teamwork_collaboration = $evaluation->teamwork_collaboration;
        $this->initiative_innovation = $evaluation->initiative_innovation;
        $this->quality_of_work = $evaluation->quality_of_work;
        $this->strengths = $evaluation->strengths;
        $this->areas_for_improvement = $evaluation->areas_for_improvement;
        $this->development_plan = $evaluation->development_plan;
        $this->additional_comments = $evaluation->additional_comments;
        $this->status = $evaluation->status;
        $this->evaluation_date = $evaluation->evaluation_date->format('Y-m-d');
        $this->next_evaluation_date = $evaluation->next_evaluation_date?->format('Y-m-d');
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function view($evaluationId): void
    {
        $evaluation = PerformanceEvaluation::with(['employee.department', 'evaluatedByUser'])->find($evaluationId);
        $this->performanceEvaluationId = $evaluation->id;
        $this->selectedEmployee = $evaluation->employee;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->selectedEmployee = null;
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
        $evaluation = PerformanceEvaluation::find($this->performanceEvaluationId);
        
        // Delete attachments from storage
        if ($evaluation->attachments) {
            foreach ($evaluation->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }
        
        $evaluation->delete();
        
        session()->flash('message', __('messages.evaluation_deleted_successfully'));
        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->performanceEvaluationId = null;
        $this->selectedEmployee = null;
    }

    public function downloadAttachment($attachmentIndex)
    {
        $evaluation = PerformanceEvaluation::find($this->performanceEvaluationId);
        
        if ($evaluation && isset($evaluation->attachments[$attachmentIndex])) {
            $attachment = $evaluation->attachments[$attachmentIndex];
            return Storage::disk('public')->download($attachment['path'], $attachment['name']);
        }
    }

    private function resetForm(): void
    {
        $this->performanceEvaluationId = null;
        $this->employee_id = null;
        $this->selectedEmployee = null;
        $this->evaluation_type = 'annual';
        $this->period_start = '';
        $this->period_end = '';
        $this->overall_score = null;
        $this->goals_achievement = null;
        $this->technical_skills = null;
        $this->soft_skills = null;
        $this->attendance_punctuality = null;
        $this->teamwork_collaboration = null;
        $this->initiative_innovation = null;
        $this->quality_of_work = null;
        $this->strengths = '';
        $this->areas_for_improvement = '';
        $this->development_plan = '';
        $this->additional_comments = '';
        $this->status = 'draft';
        $this->evaluation_date = now()->format('Y-m-d');
        $this->next_evaluation_date = '';
        $this->attachments = [];
        $this->isEditing = false;
    }

    public function updatedEmployeeSearch(): void
    {
        $this->searchEmployees();
    }

    public function updatedGoalsAchievement(): void
    {
        $this->calculateOverallScore();
    }

    public function updatedTechnicalSkills(): void
    {
        $this->calculateOverallScore();
    }

    public function updatedSoftSkills(): void
    {
        $this->calculateOverallScore();
    }

    public function updatedAttendancePunctuality(): void
    {
        $this->calculateOverallScore();
    }

    public function updatedTeamworkCollaboration(): void
    {
        $this->calculateOverallScore();
    }

    public function updatedInitiativeInnovation(): void
    {
        $this->calculateOverallScore();
    }

    public function updatedQualityOfWork(): void
    {
        $this->calculateOverallScore();
    }
}

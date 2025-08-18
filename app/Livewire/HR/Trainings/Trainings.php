<?php

declare(strict_types=1);

namespace App\Livewire\HR\Trainings;

use App\Models\HR\Employee;
use App\Models\HR\Training;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class Trainings extends Component
{
    use WithPagination, WithFileUploads;

    // Modal control
    public $showCreateEditModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    public $showEmployeeSearch = false;
    public $isEditMode = false;

    // Training properties
    public $trainingId;
    public $employee_id;
    public $training_type = 'technical';
    public $training_title;
    public $training_description;
    public $provider;
    public $status = 'planned';
    public $start_date;
    public $end_date;
    public $duration_hours;
    public $location;
    public $cost;
    public $budget_approved = false;
    public $completion_status = 'not_started';
    public $completion_date;
    public $certification_received = false;
    public $certification_expiry_date;
    public $trainer_name;
    public $trainer_email;
    public $skills_acquired;
    public $evaluation_score;
    public $feedback;
    public $next_steps;
    public $notes;

    // File uploads
    public $attachments = [];
    public $existingAttachments = [];

    // Search and filters
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $dateFilter = '';

    // Employee selection
    public $selectedEmployee;
    public $employeeSearch = '';
    public $employees = [];

    protected $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'training_type' => ['required', Rule::in(array_keys(Training::TRAINING_TYPES))],
            'training_title' => 'required|string|max:255',
            'training_description' => 'nullable|string|max:1000',
            'provider' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(array_keys(Training::STATUSES))],
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration_hours' => 'nullable|numeric|min:0|max:999.99',
            'location' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0|max:999999.99',
            'budget_approved' => 'boolean',
            'completion_status' => ['required', Rule::in(array_keys(Training::COMPLETION_STATUSES))],
            'completion_date' => 'nullable|date',
            'certification_received' => 'boolean',
            'certification_expiry_date' => 'nullable|date|after:start_date',
            'trainer_name' => 'nullable|string|max:255',
            'trainer_email' => 'nullable|email|max:255',
            'skills_acquired' => 'nullable|string|max:1000',
            'evaluation_score' => 'nullable|numeric|min:0|max:10',
            'feedback' => 'nullable|string|max:1000',
            'next_steps' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];
    }

    protected function messages(): array
    {
        return [
            'employee_id.required' => __('messages.employee_required'),
            'employee_id.exists' => __('messages.employee_invalid'),
            'training_type.required' => __('messages.training_type_required'),
            'training_title.required' => __('messages.training_title_required'),
            'start_date.required' => __('messages.start_date_required'),
            'start_date.date' => __('messages.start_date_invalid'),
            'end_date.after_or_equal' => __('messages.end_date_after_start'),
            'cost.numeric' => __('messages.cost_must_be_numeric'),
            'duration_hours.numeric' => __('messages.duration_must_be_numeric'),
            'evaluation_score.numeric' => __('messages.score_must_be_numeric'),
            'evaluation_score.between' => __('messages.score_between_0_and_10'),
            'attachments.*.file' => __('messages.attachment_must_be_file'),
            'attachments.*.mimes' => __('messages.attachment_invalid_type'),
            'attachments.*.max' => __('messages.attachment_too_large'),
        ];
    }

    public function mount(): void
    {
        $this->start_date = now()->format('Y-m-d');
    }

    public function render()
    {
        $trainings = Training::with(['employee', 'createdByUser'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('id_card', 'like', '%' . $this->search . '%');
                })->orWhere('training_title', 'like', '%' . $this->search . '%')
                  ->orWhere('provider', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('training_type', $this->typeFilter);
            })
            ->when($this->dateFilter, function ($query) {
                $query->where('start_date', '>=', $this->dateFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.hr.trainings.trainings', [
            'trainings' => $trainings,
            'statuses' => Training::STATUSES,
            'trainingTypes' => Training::TRAINING_TYPES,
            'completionStatuses' => Training::COMPLETION_STATUSES,
        ]);
    }

    public function updatedEmployeeSearch(): void
    {
        $this->searchEmployees();
    }

    public function openEmployeeSearch(): void
    {
        $this->showEmployeeSearch = true;
        $this->employees = [];
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

    public function create(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showCreateEditModal = true;
    }

    public function edit($trainingId): void
    {
        $training = Training::with('employee')->findOrFail($trainingId);
        
        $this->trainingId = $training->id;
        $this->employee_id = $training->employee_id;
        $this->selectedEmployee = $training->employee;
        $this->training_type = $training->training_type;
        $this->training_title = $training->training_title;
        $this->training_description = $training->training_description;
        $this->provider = $training->provider;
        $this->status = $training->status;
        $this->start_date = $training->start_date?->format('Y-m-d');
        $this->end_date = $training->end_date?->format('Y-m-d');
        $this->duration_hours = $training->duration_hours;
        $this->location = $training->location;
        $this->cost = $training->cost;
        $this->budget_approved = $training->budget_approved;
        $this->completion_status = $training->completion_status;
        $this->completion_date = $training->completion_date?->format('Y-m-d');
        $this->certification_received = $training->certification_received;
        $this->certification_expiry_date = $training->certification_expiry_date?->format('Y-m-d');
        $this->trainer_name = $training->trainer_name;
        $this->trainer_email = $training->trainer_email;
        $this->skills_acquired = $training->skills_acquired;
        $this->evaluation_score = $training->evaluation_score;
        $this->feedback = $training->feedback;
        $this->next_steps = $training->next_steps;
        $this->notes = $training->notes;
        $this->existingAttachments = $training->attachments ?? [];
        
        $this->isEditMode = true;
        $this->showCreateEditModal = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            $data = [
                'employee_id' => $this->employee_id,
                'training_type' => $this->training_type,
                'training_title' => $this->training_title,
                'training_description' => $this->training_description,
                'provider' => $this->provider,
                'status' => $this->status,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'duration_hours' => $this->duration_hours,
                'location' => $this->location,
                'cost' => $this->cost,
                'budget_approved' => $this->budget_approved,
                'completion_status' => $this->completion_status,
                'completion_date' => $this->completion_date,
                'certification_received' => $this->certification_received,
                'certification_expiry_date' => $this->certification_expiry_date,
                'trainer_name' => $this->trainer_name,
                'trainer_email' => $this->trainer_email,
                'skills_acquired' => $this->skills_acquired,
                'evaluation_score' => $this->evaluation_score,
                'feedback' => $this->feedback,
                'next_steps' => $this->next_steps,
                'notes' => $this->notes,
            ];

            if ($this->isEditMode) {
                $training = Training::findOrFail($this->trainingId);
                $training->update($data);
                $message = __('messages.training_updated_successfully');
            } else {
                $data['created_by'] = auth()->id();
                $training = Training::create($data);
                $message = __('messages.training_created_successfully');
            }

            // Handle file uploads
            if (!empty($this->attachments)) {
                $this->handleFileUploads($training);
            }

            session()->flash('success', $message);
            $this->closeCreateEditModal();

        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    private function handleFileUploads(Training $training): void
    {
        $uploadedFiles = [];
        $existingFiles = $training->attachments ?? [];

        foreach ($this->attachments as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs("training_attachments/{$training->id}", $filename, 'private');
            $uploadedFiles[] = $filename;
        }

        // Merge existing and new files
        $allFiles = array_merge($existingFiles, $uploadedFiles);
        $training->update(['attachments' => $allFiles]);
        
        $this->attachments = [];
    }

    public function view($trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->showViewModal = true;
    }

    public function delete($trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        try {
            $training = Training::findOrFail($this->trainingId);
            
            // Delete attachments from storage
            if ($training->attachments) {
                foreach ($training->attachments as $filename) {
                    Storage::disk('private')->delete("training_attachments/{$training->id}/{$filename}");
                }
                // Remove directory if empty
                Storage::disk('private')->deleteDirectory("training_attachments/{$training->id}");
            }

            $training->delete();
            
            session()->flash('success', __('messages.training_deleted_successfully'));
            $this->closeDeleteModal();
            
        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    public function downloadAttachment($trainingId, $filename)
    {
        $training = Training::findOrFail($trainingId);
        return $training->downloadAttachment($filename);
    }

    public function deleteAttachment($filename): void
    {
        if ($this->trainingId) {
            $training = Training::findOrFail($this->trainingId);
            
            if ($training->deleteAttachment($filename)) {
                $this->existingAttachments = array_filter($this->existingAttachments, fn($file) => $file !== $filename);
                session()->flash('success', __('messages.attachment_deleted_successfully'));
            }
        }
    }

    public function closeCreateEditModal(): void
    {
        $this->showCreateEditModal = false;
        $this->resetForm();
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->trainingId = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'trainingId', 'employee_id', 'training_type', 'training_title', 'training_description',
            'provider', 'status', 'start_date', 'end_date', 'duration_hours', 'location', 'cost',
            'budget_approved', 'completion_status', 'completion_date', 'certification_received',
            'certification_expiry_date', 'trainer_name', 'trainer_email', 'skills_acquired',
            'evaluation_score', 'feedback', 'next_steps', 'notes', 'attachments', 'existingAttachments',
            'selectedEmployee'
        ]);

        $this->training_type = 'technical';
        $this->status = 'planned';
        $this->completion_status = 'not_started';
        $this->start_date = now()->format('Y-m-d');
        $this->budget_approved = false;
        $this->certification_received = false;
    }

    public function getSelectedTrainingProperty()
    {
        if ($this->trainingId) {
            return Training::with(['employee', 'createdByUser'])->find($this->trainingId);
        }
        return null;
    }
}

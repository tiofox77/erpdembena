<?php

declare(strict_types=1);

namespace App\Livewire\HR\DisciplinaryMeasures;

use App\Models\HR\Employee;
use App\Models\HR\DisciplinaryMeasure;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Medidas Disciplinares')]
class DisciplinaryMeasures extends Component
{
    use WithPagination, WithFileUploads;

    // Modal states
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;
    public bool $showEmployeeSearch = false;
    public bool $isEditing = false;

    // Form fields
    public ?int $disciplinaryMeasureId = null;
    public ?int $employeeId = null;
    public ?Employee $selectedEmployee = null;
    public string $measureType = '';
    public string $reason = '';
    public string $description = '';
    public string $appliedDate = '';
    public string $effectiveDate = '';
    public string $status = 'active';
    public ?string $notes = null;
    
    // File uploads
    public $attachments = [];
    public array $existingAttachments = [];

    // Search and filters
    public string $search = '';
    public string $employeeSearch = '';
    public string $filterStatus = '';
    public string $filterMeasureType = '';
    public string $sortField = 'applied_date';
    public string $sortDirection = 'desc';

    // Pagination
    public int $perPage = 10;

    // Employee search results
    public Collection $searchResults;
    public int $totalResults = 0;

    protected array $rules = [
        'employeeId' => 'required|exists:employees,id',
        'measureType' => 'required|string|in:verbal_warning,written_warning,suspension,termination,fine,other',
        'reason' => 'required|string|max:255',
        'description' => 'required|string',
        'appliedDate' => 'required|date',
        'effectiveDate' => 'nullable|date|after_or_equal:appliedDate',
        'status' => 'required|string|in:active,completed,cancelled',
        'notes' => 'nullable|string',
        'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
    ];

    protected array $messages = [
        'employeeId.required' => 'Por favor selecione um funcionário.',
        'employeeId.exists' => 'Funcionário selecionado não é válido.',
        'measureType.required' => 'Tipo de medida disciplinar é obrigatório.',
        'reason.required' => 'Razão da medida disciplinar é obrigatória.',
        'description.required' => 'Descrição da medida disciplinar é obrigatória.',
        'appliedDate.required' => 'Data de aplicação é obrigatória.',
        'appliedDate.date' => 'Data de aplicação deve ser uma data válida.',
        'effectiveDate.date' => 'Data de vigência deve ser uma data válida.',
        'effectiveDate.after_or_equal' => 'Data de vigência deve ser igual ou posterior à data de aplicação.',
        'status.required' => 'Status da medida é obrigatório.',
        'attachments.*.mimes' => 'Apenas arquivos PDF, DOC, DOCX, JPG, JPEG e PNG são permitidos.',
        'attachments.*.max' => 'Cada arquivo deve ter no máximo 10MB.',
    ];

    public function mount(): void
    {
        $this->appliedDate = now()->format('Y-m-d');
        $this->searchResults = collect();
    }

    public function render()
    {
        $measures = DisciplinaryMeasure::query()
            ->with(['employee'])
            ->when($this->search, function ($query) {
                $query->whereHas('employee', function ($q) {
                    $q->where('full_name', 'like', '%' . $this->search . '%')
                      ->orWhere('id_card', 'like', '%' . $this->search . '%');
                })
                ->orWhere('reason', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterMeasureType, function ($query) {
                $query->where('measure_type', $this->filterMeasureType);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.hr.disciplinary-measures.disciplinary-measures', [
            'measures' => $measures,
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $measure = DisciplinaryMeasure::with('employee')->findOrFail($id);
        
        $this->disciplinaryMeasureId = $measure->id;
        $this->employeeId = $measure->employee_id;
        $this->selectedEmployee = $measure->employee;
        $this->measureType = $measure->measure_type;
        $this->reason = $measure->reason;
        $this->description = $measure->description;
        $this->appliedDate = $measure->applied_date->format('Y-m-d');
        $this->effectiveDate = $measure->effective_date?->format('Y-m-d') ?? '';
        $this->status = $measure->status;
        $this->notes = $measure->notes;
        $this->existingAttachments = $measure->attachments ?? [];
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            $attachmentPaths = $this->processAttachments();

            $data = [
                'employee_id' => $this->employeeId,
                'measure_type' => $this->measureType,
                'reason' => $this->reason,
                'description' => $this->description,
                'applied_date' => $this->appliedDate,
                'effective_date' => $this->effectiveDate ?: null,
                'status' => $this->status,
                'notes' => $this->notes,
                'attachments' => array_merge($this->existingAttachments, $attachmentPaths),
                'applied_by' => auth()->id(),
            ];

            if ($this->isEditing) {
                DisciplinaryMeasure::findOrFail($this->disciplinaryMeasureId)->update($data);
                session()->flash('message', __('messages.updated_successfully'));
            } else {
                DisciplinaryMeasure::create($data);
                session()->flash('message', __('messages.created_successfully'));
            }

            $this->closeModal();
            $this->resetPage();

        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    private function processAttachments(): array
    {
        $attachmentPaths = [];
        
        foreach ($this->attachments as $attachment) {
            if ($attachment) {
                $filename = time() . '_' . $attachment->getClientOriginalName();
                $path = $attachment->storeAs('disciplinary-measures', $filename, 'public');
                $attachmentPaths[] = [
                    'path' => $path,
                    'original_name' => $attachment->getClientOriginalName(),
                    'size' => $attachment->getSize(),
                    'mime_type' => $attachment->getMimeType(),
                ];
            }
        }

        return $attachmentPaths;
    }

    public function delete(int $id): void
    {
        $this->disciplinaryMeasureId = $id;
        $this->showDeleteModal = true;
    }

    public function confirmDelete(): void
    {
        try {
            DisciplinaryMeasure::findOrFail($this->disciplinaryMeasureId)->delete();
            session()->flash('message', __('messages.deleted_successfully'));
            $this->showDeleteModal = false;
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_occurred') . ': ' . $e->getMessage());
        }
    }

    public function view(int $id): void
    {
        $measure = DisciplinaryMeasure::with('employee', 'appliedByUser')->findOrFail($id);
        
        $this->disciplinaryMeasureId = $measure->id;
        $this->selectedEmployee = $measure->employee;
        $this->measureType = $measure->measure_type;
        $this->reason = $measure->reason;
        $this->description = $measure->description;
        $this->appliedDate = $measure->applied_date->format('Y-m-d');
        $this->effectiveDate = $measure->effective_date?->format('Y-m-d') ?? '';
        $this->status = $measure->status;
        $this->notes = $measure->notes;
        $this->existingAttachments = $measure->attachments ?? [];
        
        $this->showViewModal = true;
    }

    public function openEmployeeSearch(): void
    {
        $this->employeeSearch = '';
        $this->searchResults = collect();
        $this->showEmployeeSearch = true;
    }

    public function searchEmployees(): void
    {
        if (strlen($this->employeeSearch) >= 2) {
            $this->searchResults = Employee::where('employment_status', 'active')
                ->where(function ($query) {
                    $query->where('full_name', 'like', '%' . $this->employeeSearch . '%')
                          ->orWhere('id_card', 'like', '%' . $this->employeeSearch . '%')
                          ->orWhere('email', 'like', '%' . $this->employeeSearch . '%');
                })
                ->limit(10)
                ->get();
            
            $this->totalResults = $this->searchResults->count();
        } else {
            $this->searchResults = collect();
            $this->totalResults = 0;
        }
    }

    public function updatedEmployeeSearch(): void
    {
        $this->searchEmployees();
    }

    public function selectEmployee(int $employeeId): void
    {
        $this->selectedEmployee = Employee::findOrFail($employeeId);
        $this->employeeId = $employeeId;
        $this->showEmployeeSearch = false;
    }

    public function closeEmployeeSearch(): void
    {
        $this->showEmployeeSearch = false;
    }

    public function removeAttachment(int $index): void
    {
        unset($this->existingAttachments[$index]);
        $this->existingAttachments = array_values($this->existingAttachments);
    }

    public function resetForm(): void
    {
        $this->reset([
            'disciplinaryMeasureId',
            'employeeId', 
            'selectedEmployee',
            'measureType',
            'reason',
            'description',
            'effectiveDate',
            'status',
            'notes',
            'attachments',
            'existingAttachments',
        ]);
        
        $this->appliedDate = now()->format('Y-m-d');
        $this->status = 'active';
        $this->isEditing = false;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->disciplinaryMeasureId = null;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterStatus', 'filterMeasureType']);
        $this->resetPage();
    }
}

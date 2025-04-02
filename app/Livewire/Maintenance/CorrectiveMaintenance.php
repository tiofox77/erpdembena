<?php

namespace App\Livewire\Maintenance;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\MaintenanceCorrective;
use App\Models\MaintenanceTask;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\FailureMode;
use App\Models\FailureMode as Mode;
use App\Models\FailureModeCategory as ModeCategory;
use App\Models\FailureCause;
use App\Models\FailureCause as Cause;
use App\Models\FailureCauseCategory as CauseCategory;

class CorrectiveMaintenance extends Component
{
    use WithPagination;

    // Search and filter properties
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $filterStatus = '';

    #[Url(history: true)]
    public $filterEquipment = '';

    #[Url(history: true)]
    public $filterYear = '';

    #[Url(history: true)]
    public $filterMonth = '';

    #[Url(history: true)]
    public $sortField = 'start_time';

    #[Url(history: true)]
    public $sortDirection = 'desc';

    public $perPage = 10;

    // Modal properties
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $isEditing = false;
    public $deleteId = null;
    public $viewingCorrective = null;

    // Form data
    public $corrective = [
        'year' => '',
        'month' => '',
        'week' => '',
        'system_process' => '',
        'equipment_id' => '',
        'failure_mode_id' => '',
        'failure_mode_category_id' => '',
        'failure_cause_id' => '',
        'failure_cause_category_id' => '',
        'start_time' => '',
        'end_time' => '',
        'downtime_length' => '',
        'description' => '',
        'actions_taken' => '',
        'reported_by' => '',
        'resolved_by' => '',
        'status' => 'open',
    ];

    // Validation rules
    protected function rules()
    {
        return [
            'corrective.year' => 'required|numeric|digits:4',
            'corrective.month' => 'required|numeric|min:1|max:12',
            'corrective.week' => 'nullable|numeric|min:1|max:53',
            'corrective.system_process' => 'required|string|max:255',
            'corrective.equipment_id' => 'required|exists:maintenance_equipment,id',
            'corrective.failure_mode_id' => 'required|exists:failure_modes,id',
            'corrective.failure_mode_category_id' => 'required|exists:failure_mode_categories,id',
            'corrective.failure_cause_id' => 'required|exists:failure_causes,id',
            'corrective.failure_cause_category_id' => 'required|exists:failure_cause_categories,id',
            'corrective.start_time' => 'required|date',
            'corrective.end_time' => 'nullable|date|after_or_equal:corrective.start_time',
            'corrective.downtime_length' => 'nullable|string',
            'corrective.description' => 'nullable|string',
            'corrective.actions_taken' => 'nullable|string',
            'corrective.reported_by' => 'nullable|exists:users,id',
            'corrective.resolved_by' => 'nullable|exists:users,id',
            'corrective.status' => 'required|in:open,in_progress,resolved,closed',
        ];
    }

    // Custom error messages
    protected function messages()
    {
        return [
            'corrective.year.required' => 'Year is required',
            'corrective.month.required' => 'Month is required',
            'corrective.system_process.required' => 'System/Process is required',
            'corrective.equipment_id.required' => 'Equipment is required',
            'corrective.equipment_id.exists' => 'Selected equipment is invalid',
            'corrective.failure_mode_id.required' => 'Failure Mode is required',
            'corrective.failure_mode_id.exists' => 'Selected failure mode is invalid',
            'corrective.failure_mode_category_id.required' => 'Failure Mode Category is required',
            'corrective.failure_mode_category_id.exists' => 'Selected failure mode category is invalid',
            'corrective.failure_cause_id.required' => 'Failure Cause is required',
            'corrective.failure_cause_id.exists' => 'Selected failure cause is invalid',
            'corrective.failure_cause_category_id.required' => 'Failure Cause Category is required',
            'corrective.failure_cause_category_id.exists' => 'Selected failure cause category is invalid',
            'corrective.start_time.required' => 'Start time is required',
            'corrective.end_time.after_or_equal' => 'End time must be after start time',
            'corrective.status.required' => 'Status is required',
        ];
    }

    public function mount()
    {
        // Set default year and month to current date if not set
        if (empty($this->filterYear)) {
            $this->filterYear = now()->year;
        }

        if (empty($this->filterMonth)) {
            $this->filterMonth = now()->month;
        }

        // Set current user as reporter by default
        $this->corrective['reported_by'] = Auth::id();
        $this->corrective['year'] = now()->year;
        $this->corrective['month'] = now()->month;
        $this->corrective['week'] = now()->week;
    }

    // Reset pagination when filters change
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterEquipment()
    {
        $this->resetPage();
    }

    public function updatedFilterYear()
    {
        $this->resetPage();
    }

    public function updatedFilterMonth()
    {
        $this->resetPage();
    }

    /**
     * Clear all filters and reset to default values
     */
    public function clearFilters()
    {
        // Reset all filter values
        $this->reset(['search', 'filterStatus', 'filterEquipment', 'filterMonth']);

        // Set default year to current year
        $this->filterYear = now()->year;

        // Reset pagination
        $this->resetPage();

        // Dispatch an event to force UI refresh
        $this->dispatch('filters-cleared');
    }

    // Real-time validation
    public function updated($propertyName)
    {
        if (strpos($propertyName, 'corrective.') === 0) {
            $this->validateOnly($propertyName);
        }

        // Auto-calculate downtime when start and end time are set
        if ($propertyName === 'corrective.start_time' || $propertyName === 'corrective.end_time') {
            $this->calculateDowntime();
        }
    }

    // Calculate downtime based on start and end time
    private function calculateDowntime()
    {
        if (!empty($this->corrective['start_time']) && !empty($this->corrective['end_time'])) {
            $start = Carbon::parse($this->corrective['start_time']);
            $end = Carbon::parse($this->corrective['end_time']);

            if ($end->gt($start)) {
                $diffInHours = $end->diffInSeconds($start) / 3600; // Convert seconds to hours
                $this->corrective['downtime_length'] = number_format($diffInHours, 2);
            }
        }
    }

    // Sorting
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // Open modal for creating a new corrective record
    public function openModal()
    {
        $this->resetValidation();
        $this->reset('corrective');
        $this->isEditing = false;

        // Set default values
        $this->corrective = [
            'year' => now()->year,
            'month' => now()->month,
            'week' => now()->weekOfYear,
            'system_process' => '',
            'equipment_id' => '',
            'failure_mode_id' => null,
            'failure_mode_category_id' => null,
            'failure_cause_id' => null,
            'failure_cause_category_id' => null,
            'start_time' => now()->format('Y-m-d H:i'),
            'end_time' => null,
            'downtime_length' => '',
            'description' => '',
            'actions_taken' => '',
            'reported_by' => Auth::id(),
            'resolved_by' => null,
            'status' => 'open',
        ];

        $this->showModal = true;
    }

    // Open modal for editing a corrective record
    public function edit($id)
    {
        $this->resetValidation();
        $this->isEditing = true;

        $correctiveRecord = MaintenanceCorrective::with(['failureMode.category', 'failureCause.category'])->findOrFail($id);

        $this->corrective = [
            'id' => $correctiveRecord->id,
            'year' => $correctiveRecord->year,
            'month' => $correctiveRecord->month,
            'week' => $correctiveRecord->week,
            'system_process' => $correctiveRecord->system_process,
            'equipment_id' => $correctiveRecord->equipment_id,
            'failure_mode_id' => $correctiveRecord->failure_mode_id,
            'failure_mode_category_id' => is_object($correctiveRecord->failureMode) ? $correctiveRecord->failureMode->category_id : null,
            'failure_cause_id' => $correctiveRecord->failure_cause_id,
            'failure_cause_category_id' => is_object($correctiveRecord->failureCause) ? $correctiveRecord->failureCause->category_id : null,
            'start_time' => $correctiveRecord->start_time ? $correctiveRecord->start_time->format('Y-m-d H:i') : null,
            'end_time' => $correctiveRecord->end_time ? $correctiveRecord->end_time->format('Y-m-d H:i') : null,
            'downtime_length' => $correctiveRecord->downtime_length,
            'description' => $correctiveRecord->description,
            'actions_taken' => $correctiveRecord->actions_taken,
            'reported_by' => $correctiveRecord->reported_by,
            'resolved_by' => $correctiveRecord->resolved_by,
            'status' => $correctiveRecord->status,
        ];

        $this->showModal = true;
    }

    // View details of a corrective record
    public function view($id)
    {
        try {
            Log::info("View method called with ID: $id");

            // Reset any existing states
            $this->showModal = false;
            $this->showDeleteModal = false;

            $record = MaintenanceCorrective::with([
                'equipment',
                'reporter',
                'resolver',
                'failureMode.category',
                'failureCause.category'
            ])->findOrFail($id);

            Log::info("Record found, ID: " . $record->id);

            $this->viewingCorrective = $record;
            $this->showViewModal = true;

            Log::info("View modal opened, showViewModal = " . ($this->showViewModal ? 'true' : 'false'));
        } catch (\Exception $e) {
            Log::error("Error in view method: " . $e->getMessage());
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Error loading record: ' . $e->getMessage()
            );
        }
    }

    // Add a specific close view modal method
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingCorrective = null;
    }

    // Close all modals
    public function closeModal()
    {
        $this->showModal = false;
        $this->showViewModal = false;
        $this->showDeleteModal = false;
        $this->isEditing = false;
        $this->viewingCorrective = null;
        $this->deleteId = null;
        $this->resetValidation();
    }

    // Save or update a corrective record
    public function save()
    {
        // Validate the form data
        $this->validate();

        try {
            // Calculate downtime if both start and end time are set
            if (!empty($this->corrective['start_time']) && !empty($this->corrective['end_time'])) {
                $this->calculateDowntime();
            }

            $isCreate = !isset($this->corrective['id']);
            $id = $this->corrective['id'] ?? null;

            // Get or create the corrective maintenance record
            $correctiveRecord = $isCreate
                ? new MaintenanceCorrective()
                : MaintenanceCorrective::findOrFail($id);

            // Set the fields
            $correctiveRecord->year = $this->corrective['year'];
            $correctiveRecord->month = $this->corrective['month'];
            $correctiveRecord->week = $this->corrective['week'];
            $correctiveRecord->system_process = $this->corrective['system_process'];
            $correctiveRecord->equipment_id = $this->corrective['equipment_id'];

            // Directly use selected failure mode and cause without automatic linking
            $correctiveRecord->failure_mode_id = $this->corrective['failure_mode_id'];
            $correctiveRecord->failure_cause_id = $this->corrective['failure_cause_id'];

            $correctiveRecord->start_time = $this->corrective['start_time'];
            $correctiveRecord->end_time = $this->corrective['end_time'];
            $correctiveRecord->downtime_length = $this->corrective['downtime_length'];
            $correctiveRecord->description = $this->corrective['description'];
            $correctiveRecord->actions_taken = $this->corrective['actions_taken'];
            $correctiveRecord->reported_by = $this->corrective['reported_by'];
            $correctiveRecord->resolved_by = $this->corrective['status'] === 'resolved' || $this->corrective['status'] === 'closed'
                ? ($this->corrective['resolved_by'] ?? Auth::id())
                : null;
            $correctiveRecord->status = $this->corrective['status'];

            // Save the record
            $correctiveRecord->save();

            // Close the modal
            $this->showModal = false;

            // Show success message using named parameters
            $this->dispatch(
                'notify',
                type: $isCreate ? 'success' : 'info',
                message: $isCreate
                    ? 'Equipment downtime reported successfully!'
                    : 'Equipment downtime updated successfully!'
            );

        } catch (\Exception $e) {
            Log::error('Error saving corrective maintenance: ' . $e->getMessage());

            // Show error message using named parameters
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Error: ' . $e->getMessage()
            );
        }
    }

    // Confirm deletion of a corrective record
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    // Delete a corrective record
    public function delete()
    {
        try {
            $corrective = MaintenanceCorrective::findOrFail($this->deleteId);
            $corrective->delete();

            $this->dispatch('notify', type: 'warning', message: 'Corrective maintenance record deleted successfully');
            $this->closeModal();

        } catch (\Exception $e) {
            Log::error('Error deleting corrective maintenance record: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting record: ' . $e->getMessage());
        }
    }

    // Get equipment options for dropdown
    public function getEquipmentOptions()
    {
        return MaintenanceEquipment::orderBy('name')->get();
    }

    // Get users for reporter/resolver dropdowns
    public function getUserOptions()
    {
        return User::orderBy('name')->get();
    }

    // Get available years for filtering
    public function getYearOptions()
    {
        $currentYear = now()->year;
        $years = [];

        for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
            $years[$i] = $i;
        }

        return $years;
    }

    // Get months for filtering
    public function getMonthOptions()
    {
        return [
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
            12 => 'December',
        ];
    }

    // Get statuses for filtering
    public function getStatusOptions()
    {
        return MaintenanceCorrective::getStatuses();
    }

    // Computed property to get corrective records with filters applied
    public function getCorrectiveRecordsProperty()
    {
        return MaintenanceCorrective::with([
            'equipment',
            'reporter',
            'resolver',
            'failureMode.category',
            'failureCause.category'
        ])
            ->search($this->search)
            ->filterByStatus($this->filterStatus)
            ->filterByEquipment($this->filterEquipment)
            ->filterByYear($this->filterYear)
            ->filterByMonth($this->filterMonth)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    // Add these methods before the render() method to get failure modes and causes
    public function getFailureModes()
    {
        return FailureMode::with('category')->where('is_active', true)->orderBy('name')->get();
    }

    public function getFailureCauses()
    {
        return FailureCause::with('category')->where('is_active', true)->orderBy('name')->get();
    }

    public function getModeCategories()
    {
        return ModeCategory::where('is_active', true)->orderBy('name')->get();
    }

    public function getCauseCategories()
    {
        return CauseCategory::where('is_active', true)->orderBy('name')->get();
    }

    // Modify the render method to use all available modes and causes regardless of category
    public function render()
    {
        return view('livewire.maintenance.corrective-maintenance', [
            'correctiveRecords' => $this->correctiveRecords,
            'equipment' => $this->getEquipmentOptions(),
            'users' => $this->getUserOptions(),
            'years' => $this->getYearOptions(),
            'months' => $this->getMonthOptions(),
            'statuses' => $this->getStatusOptions(),
            'failureModes' => $this->getFailureModes(), // Use all failure modes regardless of category
            'failureCauses' => $this->getFailureCauses(), // Use all failure causes regardless of category
            'modeCategories' => $this->getModeCategories(),
            'causeCategories' => $this->getCauseCategories(),
        ]);
    }

    // Complete removal of any filtering or resetting logic
    public function updatedCorrectiveFailureModeCategoryId()
    {
        // Completely removed - no action needed
    }

    public function updatedCorrectiveFailureCauseCategoryId()
    {
        // Completely removed - no action needed
    }
}

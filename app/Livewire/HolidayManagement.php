<?php

namespace App\Livewire;

use App\Models\Holiday;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HolidayManagement extends Component
{
    use WithPagination;

    // URL parameters
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $filterRecurring = '';

    #[Url(history: true)]
    public $filterStatus = '';

    #[Url(history: true)]
    public $sortField = 'date';

    #[Url(history: true)]
    public $sortDirection = 'asc';

    // Modal states
    public $showModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;
    public $deleteHolidayId = null;

    // Holiday data for create/edit form
    public $holiday = [
        'title' => '',
        'description' => '',
        'date' => '',
        'is_recurring' => false,
        'is_active' => true
    ];

    // Reset pagination when filters change
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterRecurring()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    // Validation rules
    protected function rules()
    {
        return [
            'holiday.title' => 'required|string|max:255',
            'holiday.description' => 'nullable|string',
            'holiday.date' => 'required|date',
            'holiday.is_recurring' => 'boolean',
            'holiday.is_active' => 'boolean'
        ];
    }

    // Custom validation messages
    protected function messages()
    {
        return [
            'holiday.title.required' => 'Holiday title is required',
            'holiday.date.required' => 'Holiday date is required',
            'holiday.date.date' => 'Please enter a valid date',
        ];
    }

    // Real-time validation
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
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

    // Open modal for creating a new holiday
    public function openModal()
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->holiday = [
            'title' => '',
            'description' => '',
            'date' => Carbon::now()->format('Y-m-d'),
            'is_recurring' => false,
            'is_active' => true
        ];
        $this->showModal = true;
    }

    // Open modal for editing a holiday
    public function editHoliday($id)
    {
        $this->resetValidation();
        $this->isEditing = true;

        $holidayToEdit = Holiday::findOrFail($id);
        $this->holiday = [
            'id' => $holidayToEdit->id,
            'title' => $holidayToEdit->title,
            'description' => $holidayToEdit->description,
            'date' => $holidayToEdit->date->format('Y-m-d'),
            'is_recurring' => $holidayToEdit->is_recurring,
            'is_active' => $holidayToEdit->is_active
        ];

        $this->showModal = true;
    }

    // Save holiday (create or update)
    public function saveHoliday()
    {
        $validatedData = $this->validate();

        try {
            if ($this->isEditing) {
                // Update existing holiday
                $holiday = Holiday::findOrFail($this->holiday['id']);
                $holiday->update([
                    'title' => $this->holiday['title'],
                    'description' => $this->holiday['description'],
                    'date' => $this->holiday['date'],
                    'is_recurring' => $this->holiday['is_recurring'],
                    'is_active' => $this->holiday['is_active']
                ]);

                $message = 'Holiday updated successfully';
                $notificationType = 'info';
            } else {
                // Create new holiday
                Holiday::create([
                    'title' => $this->holiday['title'],
                    'description' => $this->holiday['description'],
                    'date' => $this->holiday['date'],
                    'is_recurring' => $this->holiday['is_recurring'],
                    'is_active' => $this->holiday['is_active']
                ]);

                $message = 'Holiday created successfully';
                $notificationType = 'success';
            }

            // Send notification
            $this->dispatch('notify', type: $notificationType, message: $message);

            // Close modal and reset form
            $this->showModal = false;
            $this->reset('holiday');

        } catch (\Exception $e) {
            Log::error('Error saving holiday: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error saving holiday: ' . $e->getMessage());
        }
    }

    // Confirm holiday deletion
    public function confirmDelete($holidayId)
    {
        $this->deleteHolidayId = $holidayId;
        $this->showDeleteModal = true;
    }

    // Delete a holiday
    public function deleteHoliday()
    {
        try {
            $holiday = Holiday::findOrFail($this->deleteHolidayId);
            $holiday->delete();

            $this->dispatch('notify', type: 'warning', message: 'Holiday deleted successfully');

            $this->showDeleteModal = false;
            $this->deleteHolidayId = null;

        } catch (\Exception $e) {
            Log::error('Error deleting holiday: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error deleting holiday: ' . $e->getMessage());
        }
    }

    // Toggle holiday active status
    public function toggleHolidayStatus($holidayId)
    {
        try {
            $holiday = Holiday::findOrFail($holidayId);
            $holiday->is_active = !$holiday->is_active;
            $holiday->save();

            $status = $holiday->is_active ? 'activated' : 'deactivated';
            $this->dispatch('notify', type: 'info', message: "Holiday {$status} successfully");

        } catch (\Exception $e) {
            Log::error('Error toggling holiday status: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error changing holiday status: ' . $e->getMessage());
        }
    }

    // Close modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->reset(['holiday', 'deleteHolidayId']);
        $this->resetValidation();
    }

    // Get holidays with filters and sorting
    public function getHolidaysProperty()
    {
        return Holiday::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterRecurring !== '', function ($query) {
                return $query->where('is_recurring', $this->filterRecurring);
            })
            ->when($this->filterStatus !== '', function ($query) {
                return $query->where('is_active', $this->filterStatus);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.holiday-management', [
            'holidays' => $this->holidays
        ]);
    }
}

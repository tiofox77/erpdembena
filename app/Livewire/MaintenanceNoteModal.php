<?php

namespace App\Livewire;

use App\Models\MaintenancePlan;
use App\Models\MaintenanceNote;
use Livewire\Component;
use Livewire\Attributes\On;

class MaintenanceNoteModal extends Component
{
    public $showModal = false;
    public $task;
    public $maintenancePlanId;
    public $currentStatus;
    public $notes = '';
    public $history = [];
    public $viewOnly = false; // Flag to determine if the modal is in view-only mode

    protected $rules = [
        'notes' => 'required|string|min:5',
    ];

    #[On('openNotesModal')]
    public function openModal($eventId)
    {
        $this->viewOnly = false;
        $this->maintenancePlanId = $eventId;
        $plan = MaintenancePlan::with(['task', 'equipment', 'notes'])->findOrFail($eventId);

        $this->task = [
            'id' => $plan->id,
            'title' => $plan->task ? $plan->task->title : 'Task',
            'equipment' => $plan->equipment ? $plan->equipment->name : 'Equipment',
            'status' => $plan->status,
        ];

        $this->currentStatus = $plan->status;
        $this->notes = '';

        // Carregar histórico de notas
        $this->loadHistory();

        $this->showModal = true;
    }

    #[On('openHistoryModal')]
    public function openHistoryModal($eventId)
    {
        $this->viewOnly = true;
        $this->maintenancePlanId = $eventId;
        $plan = MaintenancePlan::with(['task', 'equipment', 'notes'])->findOrFail($eventId);

        $this->task = [
            'id' => $plan->id,
            'title' => $plan->task ? $plan->task->title : 'Task',
            'equipment' => $plan->equipment ? $plan->equipment->name : 'Equipment',
            'status' => $plan->status,
        ];

        $this->currentStatus = $plan->status;

        // Load maintenance notes history
        $this->loadHistory();

        $this->showModal = true;
    }

    public function loadHistory()
    {
        $this->history = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($note) {
                return [
                    'id' => $note->id,
                    'status' => $note->status,
                    'notes' => $note->notes,
                    'user' => $note->user ? $note->user->name : 'System',
                    'created_at' => $note->created_at->format('m/d/Y H:i'),
                ];
            })
            ->toArray();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['task', 'maintenancePlanId', 'notes', 'currentStatus', 'viewOnly']);
    }

    public function saveNote()
    {
        $this->validate();

        try {
            // Save the note
            MaintenanceNote::create([
                'maintenance_plan_id' => $this->maintenancePlanId,
                'status' => $this->currentStatus,
                'notes' => $this->notes,
                'user_id' => auth()->id(),
            ]);

            // Reload history
            $this->loadHistory();

            // Clear note field
            $this->notes = '';

            // Send notification
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Note Added',
                'message' => 'The note has been added to the history successfully.'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while saving the note: ' . $e->getMessage()
            ]);
        }
    }

    public function updateStatus($status)
    {
        try {
            // Update maintenance plan status
            $plan = MaintenancePlan::findOrFail($this->maintenancePlanId);
            $oldStatus = $plan->status;
            $plan->status = $status;
            $plan->save();

            // Record status change in history
            MaintenanceNote::create([
                'maintenance_plan_id' => $this->maintenancePlanId,
                'status' => $status,
                'notes' => "Status changed from '$oldStatus' to '$status'",
                'user_id' => auth()->id(),
            ]);

            // Update current status in component
            $this->currentStatus = $status;
            $this->task['status'] = $status;

            // Reload history
            $this->loadHistory();

            // Send notification
            $this->dispatch('notify', [
                'type' => 'info',
                'title' => 'Status Updated',
                'message' => 'The task status has been updated successfully.'
            ]);

            // Emit event to update calendar
            $this->dispatch('maintenanceUpdated');

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'An error occurred while updating the status: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.maintenance-note-modal');
    }
}

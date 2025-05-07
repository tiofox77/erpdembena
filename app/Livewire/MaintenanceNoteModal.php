<?php

namespace App\Livewire;

use App\Models\MaintenancePlan;
use App\Models\MaintenanceNote;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MaintenanceNoteModal extends Component
{
    use WithFileUploads;

    public $showModal = false;
    public $task;
    public $maintenancePlanId;
    public $currentStatus;
    public $notes = '';
    public $workFile; // Nova propriedade para o arquivo
    public $history = [];
    public $viewOnly = false; // Flag to determine if the modal is in view-only mode
    public $uploadError = null;

    protected $rules = [
        'notes' => 'required|string|min:5',
        'workFile' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // Max 10MB, documentos comuns
    ];

    protected $listeners = [
        'openNotesModal' => 'openModal',
        'openHistoryModal' => 'openHistoryModal'
    ];

    public function cleanupOldUploads()
    {
        if (method_exists(parent::class, 'cleanupOldUploads')) {
            parent::cleanupOldUploads();
        }
    }

    #[On('openNotesModal')]
    public function openModal($eventId)
    {
        $this->reset(['notes', 'workFile', 'uploadError']);

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
        $this->workFile = null; // Limpar o arquivo

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
                    'file_name' => $note->file_name,
                    'file_path' => $note->file_path,
                    'user' => $note->user ? $note->user->name : 'System',
                    'created_at' => $note->created_at->format(\App\Models\Setting::getSystemDateTimeFormat()),
                ];
            })
            ->toArray();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['task', 'maintenancePlanId', 'notes', 'workFile', 'currentStatus', 'viewOnly']);
    }

    public function updatedWorkFile()
    {
        $this->uploadError = null;
        try {
            $this->validateOnly('workFile');

            // Verificar se é um arquivo válido
            if ($this->workFile && !$this->workFile->isValid()) {
                $this->uploadError = 'O arquivo não é válido. Tente novamente.';
                $this->reset('workFile');
            }
        } catch (\Exception $e) {
            $this->uploadError = 'Erro ao carregar o arquivo: ' . $e->getMessage();
            $this->reset('workFile');
        }
    }

    public function saveNote()
    {
        if ($this->uploadError) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Erro',
                'message' => $this->uploadError
            ]);
            return;
        }

        $this->validate();

        try {
            $filePath = null;
            $fileName = null;

            // Processar o upload do arquivo, se fornecido
            if ($this->workFile) {
                // Verificar se é um arquivo válido antes de salvar
                if (!$this->workFile->isValid()) {
                    throw new \Exception('O arquivo não é válido ou está corrompido.');
                }

                $originalName = $this->workFile->getClientOriginalName();
                $extension = $this->workFile->getClientOriginalExtension();

                // Criar um nome seguro usando ID único e nome original
                $safeName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\-\._]/', '', $originalName);

                // Armazenar o arquivo
                try {
                    $filePath = $this->workFile->storeAs('maintenance-files', $safeName, 'public');
                    $fileName = $originalName;
                } catch (\Exception $e) {
                    Log::error('Erro ao salvar arquivo: ' . $e->getMessage());
                    throw new \Exception('Não foi possível salvar o arquivo. Tente novamente.');
                }
            }

            // Save the note
            MaintenanceNote::create([
                'maintenance_plan_id' => $this->maintenancePlanId,
                'status' => $this->currentStatus,
                'notes' => $this->notes,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'user_id' => auth()->id(),
            ]);

            // Reload history
            $this->loadHistory();

            // Clear note field and file
            $this->reset(['notes', 'workFile', 'uploadError']);

            // Send notification
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Nota Adicionada',
                'message' => 'A nota foi adicionada ao histórico com sucesso.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar nota: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Erro',
                'message' => 'Ocorreu um erro ao salvar a nota: ' . $e->getMessage()
            ]);
        }
    }

    public function updateStatus($status)
    {
        try {
            // Buscar o plano para obter o status atual (apenas para referência)
            $plan = MaintenancePlan::findOrFail($this->maintenancePlanId);
            $oldStatus = $plan->status;

            // NÃO atualizar o status do plano de manutenção
            // Apenas registrar o status na nota

            // Registrar a mudança de status no histórico
            MaintenanceNote::create([
                'maintenance_plan_id' => $this->maintenancePlanId,
                'status' => $status, // Status da nota (não altera o plano)
                'notes' => "Status da atividade alterado de '$oldStatus' para '$status'",
                'user_id' => auth()->id(),
            ]);

            // Atualizar apenas o status atual no componente para exibição
            $this->currentStatus = $status;
            $this->task['status'] = $status;

            // Recarregar histórico
            $this->loadHistory();

            // Enviar notificação
            $this->dispatch('notify', [
                'type' => 'info',
                'title' => 'Status Atualizado',
                'message' => 'O status da atividade foi atualizado com sucesso no histórico.'
            ]);

            // Não é necessário emitir evento para atualizar o calendário
            // já que o status do plano original não foi alterado

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Erro',
                'message' => 'Ocorreu um erro ao atualizar o status: ' . $e->getMessage()
            ]);
        }
    }

    // Método para download do arquivo
    public function downloadFile($noteId)
    {
        $note = MaintenanceNote::findOrFail($noteId);

        if ($note->file_path) {
            return response()->download(storage_path('app/public/' . $note->file_path), $note->file_name);
        }

        $this->dispatch('notify', [
            'type' => 'error',
            'title' => 'Erro',
            'message' => 'Arquivo não encontrado'
        ]);
    }

    public function render()
    {
        return view('livewire.maintenance-note-modal');
    }
}

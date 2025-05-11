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
    public $selectedStatus; // Novo campo para armazenar o status selecionado pelo usuário
    public $notes = '';
    public $workFile; // Nova propriedade para o arquivo
    public $history = [];
    public $viewOnly = false; // Flag to determine if the modal is in view-only mode
    public $uploadError = null;

    protected $rules = [
        'notes' => 'required|string|min:5',
        'selectedStatus' => 'required|string|in:in_progress,completed,cancelled,pending,schedule',
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
        $this->reset(['notes', 'workFile', 'uploadError', 'selectedStatus']);

        $this->maintenancePlanId = $eventId;
        $plan = MaintenancePlan::with(['task', 'equipment', 'notes'])->findOrFail($eventId);

        $this->task = [
            'id' => $plan->id,
            'title' => $plan->task ? $plan->task->title : 'Task',
            'equipment' => $plan->equipment ? $plan->equipment->name : 'Equipment',
            'status' => $plan->status,
        ];

        $this->currentStatus = $plan->status;
        $this->selectedStatus = $plan->status; // Inicializa o status selecionado com o status atual

        // Verifica se a tarefa está com status 'completed'. Se estiver, abrir no modo somente visualização
        $this->viewOnly = ($plan->status === 'completed');
        
        // Se a tarefa estiver concluída, avisar o usuário
        if ($this->viewOnly) {
            $this->dispatch('notify', 
                type: 'info', 
                title: __('messages.completed_task'),
                message: __('messages.completed_task_edit_disabled')
            );
        }

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
        $this->selectedStatus = $plan->status; // Inicializa o status selecionado com o status atual
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
        $this->reset(['task', 'maintenancePlanId', 'notes', 'workFile', 'currentStatus', 'selectedStatus', 'viewOnly', 'uploadError']);
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

        try {
            // Validar entradas incluindo selectedStatus
            $this->validate();

            // Buscar o plano para obter o status atual (para referência no histórico se necessário)
            $plan = MaintenancePlan::findOrFail($this->maintenancePlanId);
            $oldStatus = $plan->status;
            
            // Verificar se a tarefa já está com status 'completed'
            // Isso é uma segunda camada de proteção, além da interface
            if ($plan->status === 'completed') {
                // Se a tarefa já estiver concluída, não permitir alterações
                $this->dispatch('notify', 
                    type: 'error', 
                    title: __('messages.action_not_allowed'),
                    message: __('messages.completed_task_cannot_be_modified')
                );
                return;
            }

            // Inicializar variáveis do arquivo
            $filePath = null;
            $fileName = null;

            if ($this->workFile) {
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

            // Preparar mensagem da nota
            $noteContent = $this->notes;
            
            // Adicionar informação sobre mudança de status na nota, se o status foi alterado
            if ($this->selectedStatus !== $this->currentStatus) {
                // Gravar automaticamente a alteração de status no conteúdo da nota
                $noteContent = $this->notes . "\n\n[Status atualizado de '" . ucfirst($this->currentStatus) . "' para '" . ucfirst($this->selectedStatus) . "']";
            }

            // Salvar a nota com o status selecionado
            MaintenanceNote::create([
                'maintenance_plan_id' => $this->maintenancePlanId,
                'status' => $this->selectedStatus, // Usar o status selecionado pelo usuário
                'notes' => $noteContent,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'user_id' => auth()->id(),
            ]);

            // Atualizar o status atual para fins de interface
            $this->currentStatus = $this->selectedStatus;
            $this->task['status'] = $this->selectedStatus;

            // Recarregar histórico
            $this->loadHistory();

            // Limpar campo de nota e arquivo
            $this->reset(['notes', 'workFile', 'uploadError']);

            // Enviar notificação usando padrão recomendado
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.note_added'),
                message: __('messages.note_added_successfully')
            );

        } catch (\Exception $e) {
            Log::error('Erro ao salvar nota: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: __('messages.error_saving_note') . ': ' . $e->getMessage()
            );
        }
    }

    // Método updateStatus removido pois agora o status é atualizado apenas quando o formulário é submetido

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

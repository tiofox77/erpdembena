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
    public $selectedStatus; // Campo para armazenar o status selecionado pelo usuário
    public $notes = '';
    public $workFile; // Propriedade para o arquivo
    public $history = [];
    public $viewOnly = false; // Flag para determinar se o modal está em modo somente visualização
    public $uploadError = null;
    public $selectedDate; // Nova propriedade para armazenar a data selecionada no calendário

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
    public function openModal($eventId, $selectedDate = null)
    {
        $this->reset(['notes', 'workFile', 'uploadError', 'selectedStatus', 'selectedDate']);

        $this->maintenancePlanId = $eventId;
        $this->selectedDate = $selectedDate ?? now()->format('Y-m-d');
        
        // Log para debug
        Log::info('=== INÍCIO openModal ===', [
            'eventId' => $eventId,
            'selectedDate' => $this->selectedDate
        ]);
        
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
        $hasCompletedNote = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
            ->where('status', 'completed')
            ->exists();
        
        // Define o modo somente visualização se o plano estiver marcado como concluído OU
        // se existir alguma nota com status 'completed'
        $this->viewOnly = ($plan->status === 'completed' || $hasCompletedNote);
        
        // Se a tarefa estiver concluída ou tiver uma nota completada, avisar o usuário
        if ($this->viewOnly) {
            if ($hasCompletedNote && $plan->status !== 'completed') {
                $this->dispatch('notify', 
                    type: 'info', 
                    title: __('messages.completed_task'),
                    message: __('messages.task_with_completed_note_cannot_be_modified')
                );
            } else {
                $this->dispatch('notify', 
                    type: 'info', 
                    title: __('messages.completed_task'),
                    message: __('messages.completed_task_edit_disabled')
                );
            }
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
        try {
            // Log para debug
            Log::info('=== INÍCIO loadHistory ===', [
                'planId' => $this->maintenancePlanId,
                'selectedDate' => $this->selectedDate
            ]);
            
            // Converter a string de data para Carbon para manipulação segura
            $date = null;
            if ($this->selectedDate) {
                $date = \Carbon\Carbon::parse($this->selectedDate);
                Log::info('Data selecionada parseada', ['date' => $date->format('Y-m-d')]);
            }
            
            // Iniciar a query
            $query = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId);
            
            // Filtrar por data se uma data foi selecionada
            if ($date) {
                $query->where(function($q) use ($date) {
                    $q->where('note_date', $date->format('Y-m-d'))
                      ->orWhereNull('note_date'); // Para compatibilidade com notas antigas
                });
            }
            
            // Buscar as notas e transformá-las
            $this->history = $query->with('user')
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
                        'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                        'note_date' => $note->note_date ? $note->note_date->format('Y-m-d') : null,
                    ];
                })->toArray();
                
            Log::info('Histórico carregado', ['count' => count($this->history)]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar histórico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->history = [];
        } finally {
            Log::info('=== FIM loadHistory ===');
        }
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

            // Verificar se já existe alguma nota anterior com status 'completed'
            $hasCompletedNote = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
                ->where('status', 'completed')
                ->exists();
                
            if ($hasCompletedNote) {
                // Se já existe uma nota com status completed, não permitir alterações
                $this->dispatch('notify', 
                    type: 'error', 
                    title: __('messages.action_not_allowed'),
                    message: __('messages.task_with_completed_note_cannot_be_modified')
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

            // Log para debug
            Log::info('Salvando nota para data específica', [
                'maintenance_plan_id' => $this->maintenancePlanId,
                'status' => $this->selectedStatus,
                'selectedDate' => $this->selectedDate
            ]);
            
            // Salvar a nota com o status selecionado E a data específica
            MaintenanceNote::create([
                'maintenance_plan_id' => $this->maintenancePlanId,
                'status' => $this->selectedStatus, // Usar o status selecionado pelo usuário
                'note_date' => $this->selectedDate, // Usar a data selecionada no calendário
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

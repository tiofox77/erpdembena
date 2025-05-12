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

        // Verifica se existe nota com status 'completed' APENAS para a data selecionada
        // Ao invés de verificar todas as notas do plano
        $hasCompletedNote = false;
        
        if ($this->selectedDate) {
            $selectedDateObj = \Carbon\Carbon::parse($this->selectedDate);
            
            // Verificar se existe uma nota com status 'completed' APENAS para a data selecionada
            $hasCompletedNote = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
                ->where('status', 'completed')
                ->whereDate('note_date', $selectedDateObj->format('Y-m-d'))
                ->exists();
                
            Log::info('Verificando nota completed para data específica', [
                'date' => $this->selectedDate,
                'hasCompletedNote' => $hasCompletedNote ? 'sim' : 'não'
            ]);
        }
        
        // Define o modo somente visualização se o plano estiver marcado como concluído
        // OU se existir uma nota com status 'completed' APENAS para a data selecionada
        $this->viewOnly = ($plan->status === 'completed' || $hasCompletedNote);
        
        // Agora a mensagem é específica para a data selecionada
        if ($this->viewOnly) {
            if ($hasCompletedNote && $plan->status !== 'completed') {
                $this->dispatch('notify', 
                    type: 'info', 
                    title: __('messages.completed_task'),
                    message: __('messages.task_completed_for_specific_date', ['date' => $this->selectedDate])
                );
            } else if ($plan->status === 'completed') {
                $this->dispatch('notify', 
                    type: 'info', 
                    title: __('messages.completed_task'),
                    message: __('messages.completed_plan_edit_disabled')
                );
            }
        }

        // Carregar histórico de notas
        $this->loadHistory();

        $this->showModal = true;
    }

    #[On('openHistoryModal')]
    public function openHistoryModal($eventId, $selectedDate = null)
    {
        // Limpar campos e definir variáveis
        $this->reset(['workFile', 'uploadError', 'selectedDate']);
        $this->maintenancePlanId = $eventId;
        $this->selectedDate = $selectedDate ?? now()->format('Y-m-d');
        
        // Buscar o plano de manutenção
        $plan = MaintenancePlan::with(['task', 'equipment', 'notes'])->findOrFail($eventId);

        // Definir os detalhes da tarefa
        $this->task = [
            'id' => $plan->id,
            'title' => $plan->task ? $plan->task->title : 'Task',
            'equipment' => $plan->equipment ? $plan->equipment->name : 'Equipment',
            'status' => $plan->status,
        ];

        // Definir status
        $this->currentStatus = $plan->status;
        $this->selectedStatus = $plan->status; // Inicializa o status selecionado com o status atual
        
        // Por padrão, o modo de visualização da história é somente leitura
        $this->viewOnly = true;
        
        // Carregar histórico de notas
        $this->loadHistory();

        // Mostrar o modal
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

            // Buscar o plano para obter o status atual
            $plan = MaintenancePlan::findOrFail($this->maintenancePlanId);
            
            // Verificar se existe nota com status 'completed' APENAS para a data selecionada
            // Em vez de verificar todas as notas do plano
            $selectedDateObj = \Carbon\Carbon::parse($this->selectedDate);
            $hasCompletedNote = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
                ->where('status', 'completed')
                ->whereDate('note_date', $selectedDateObj->format('Y-m-d'))
                ->exists();
                
            if ($hasCompletedNote) {
                // Se já existe uma nota com status 'completed' para ESTA DATA, não permitir alterações
                $this->dispatch('notify', 
                    type: 'error', 
                    title: __('messages.action_not_allowed'),
                    message: __('messages.task_completed_for_specific_date', ['date' => $this->selectedDate])
                );
                return;
            }
            
            // Preparar conteúdo da nota
            $noteContent = $this->notes;
            
            // Adicionar informação de mudança de status na nota, se aplicável
            if ($this->selectedStatus !== $this->currentStatus) {
                $noteContent .= "\n\n[Status atualizado de '" . ucfirst($this->currentStatus) . "' para '" . ucfirst($this->selectedStatus) . "']"; 
            }
            
            // Preparar variáveis para o arquivo
            $filePath = null;
            $fileName = null;
            
            // Processar arquivo anexado, se existir
            if ($this->workFile) {
                $fileName = $this->workFile->getClientOriginalName();
                $filePath = $this->workFile->store('maintenance-notes', 'public');
            }
            
            // Log para debug
            \Log::info('Salvando nota para data específica', [
                'plan_id' => $this->maintenancePlanId,
                'note_date' => $this->selectedDate,
                'status' => $this->selectedStatus
            ]);
            
            // Criar a nota com data específica
            $note = new MaintenanceNote([
                'maintenance_plan_id' => $this->maintenancePlanId,
                'status' => $this->selectedStatus,
                'note_date' => $this->selectedDate, // Data específica selecionada no calendário
                'notes' => $noteContent,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'user_id' => auth()->id(),
            ]);
            
            $note->save();
            
            // IMPORTANTE: Apenas atualizar o plano para 'in_progress' se estiver pendente
            // O status 'completed' do plano só deve ser alterado manualmente pelo usuário
            if ($plan->status === 'pending') {
                $plan->status = 'in_progress';
                $plan->save();
            }
            
            // Recarregar histórico
            $this->loadHistory();
            
            // Limpar campos
            $this->reset(['notes', 'workFile', 'uploadError']);
            
            // Notificar sucesso
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.note_saved_successfully')
            );
            
            // Atualizar o calendário
            $this->dispatch('refreshCalendar');
            
        } catch (\Exception $e) {
            // Log de erro detalhado
            \Log::error('Erro ao salvar nota de manutenção', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Notificar erro
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.error'),
                message: $e->getMessage()
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

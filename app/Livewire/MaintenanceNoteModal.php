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
    
    // Remover validação em tempo real para evitar salvamento automático
    protected function validationAttributes()
    {
        return [
            'notes' => __('messages.notes'),
            'selectedStatus' => __('messages.status'),
            'workFile' => __('messages.attachment'),
        ];
    }

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
        // Log antes de qualquer operação
        Log::info('=== RECEBENDO PARÂMETROS NO MODAL ===', [
            'eventId_recebido' => $eventId,
            'selectedDate_recebido' => $selectedDate,
            'tipo_selectedDate' => gettype($selectedDate),
            'é_null' => $selectedDate === null ? 'SIM' : 'NÃO',
            'é_string_vazia' => $selectedDate === '' ? 'SIM' : 'NÃO',
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)
        ]);
        
        // Definir primeiro a data selecionada ANTES de fazer o reset
        // Certifique-se que não aceita valores vazios
        if (empty($selectedDate)) {
            Log::warning('Data selecionada vazia ou null, usando data atual');
            $this->selectedDate = now()->format('Y-m-d');
        } else {
            $this->selectedDate = $selectedDate;
        }
        
        $this->maintenancePlanId = $eventId;
        
        // Agora fazer o reset dos outros campos, preservando selectedDate
        $this->reset(['notes', 'workFile', 'uploadError', 'selectedStatus']);
        
        // Log para debug
        Log::info('=== INÍCIO openModal ===', [
            'eventId' => $eventId,
            'selectedDate_parameter' => $selectedDate,
            'selectedDate_final' => $this->selectedDate,
            'maintenancePlanId' => $this->maintenancePlanId,
            'data_atual' => now()->format('Y-m-d'),
            'data_passou_parametro' => $selectedDate ? 'SIM' : 'NÃO'
        ]);
        
        $plan = MaintenancePlan::with(['task', 'equipment', 'notes'])->findOrFail($eventId);

        $this->task = [
            'id' => $plan->id,
            'title' => $plan->task ? $plan->task->title : 'Task',
            'equipment' => $plan->equipment ? $plan->equipment->name : 'Equipment',
            'status' => $plan->status,
        ];

        // Obter o status específico para a data selecionada
        $this->currentStatus = $this->getStatusForDate($this->selectedDate);
        $this->selectedStatus = $this->currentStatus;

        // Verificar se existe nota com status 'completed' APENAS para a data selecionada usando note_date
        $hasCompletedNote = false;
        
        if ($this->selectedDate) {
            $selectedDateObj = \Carbon\Carbon::parse($this->selectedDate);
            
            // Query para debug
            $completedNotesQuery = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
                ->where('status', 'completed')
                ->whereDate('note_date', $selectedDateObj->format('Y-m-d'));
                
            $hasCompletedNote = $completedNotesQuery->exists();
            
            // Log detalhado para debug
            Log::info('=== VERIFICAÇÃO DE NOTA COMPLETED ===', [
                'maintenancePlanId' => $this->maintenancePlanId,
                'selectedDate' => $this->selectedDate,
                'selectedDateFormatted' => $selectedDateObj->format('Y-m-d'),
                'hasCompletedNote' => $hasCompletedNote ? 'SIM' : 'NÃO',
                'currentStatus' => $this->currentStatus,
                'sqlQuery' => $completedNotesQuery->toSql(),
                'queryBindings' => $completedNotesQuery->getBindings()
            ]);
            
            // Verificar todas as notas para esta data (para debug)
            $allNotesForDate = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
                ->whereDate('note_date', $selectedDateObj->format('Y-m-d'))
                ->get(['id', 'status', 'note_date', 'created_at']);
                
            Log::info('Todas as notas para esta data:', [
                'count' => $allNotesForDate->count(),
                'notes' => $allNotesForDate->toArray()
            ]);
        }
        
        // Define o modo somente visualização APENAS se existir uma nota com status 'completed' para a data específica
        $this->viewOnly = $hasCompletedNote;
        
        Log::info('Definindo viewOnly', [
            'viewOnly' => $this->viewOnly ? 'SIM' : 'NÃO',
            'hasCompletedNote' => $hasCompletedNote ? 'SIM' : 'NÃO'
        ]);
        
        // Mensagem específica para a data selecionada
        if ($this->viewOnly && $hasCompletedNote) {
            $this->dispatch('notify', 
                type: 'info', 
                title: __('messages.completed_task'),
                message: __('messages.task_completed_for_specific_date', ['date' => $this->selectedDate])
            );
        }

        // Carregar histórico de notas filtrado pela data
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
        
        // Iniciamos definindo viewOnly como false para permitir edição
        // Isso será alterado para true pelo loadHistory se encontrar uma nota
        // com status 'completed' para a data específica selecionada
        $this->viewOnly = false;
        
        // Carregar histórico de notas e definir viewOnly com base nos resultados
        $this->loadHistory();

        // Mostrar o modal
        $this->showModal = true;
    }

    public function loadHistory()
    {
        try {
            // Log para debug com mais detalhes
            Log::info('=== INÍCIO loadHistory ===', [
                'planId' => $this->maintenancePlanId,
                'selectedDate' => $this->selectedDate,
                'tipo_selectedDate' => gettype($this->selectedDate),
                'selectedDate_vazio' => empty($this->selectedDate) ? 'SIM' : 'NÃO'
            ]);
            
            // Converter a string de data para Carbon para manipulação segura
            $date = null;
            if ($this->selectedDate) {
                try {
                    $date = \Carbon\Carbon::parse($this->selectedDate);
                    Log::info('Data selecionada parseada com sucesso', [
                        'date_formatada' => $date->format('Y-m-d'),
                        'date_original' => $this->selectedDate
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erro ao fazer parse da data selecionada', [
                        'erro' => $e->getMessage(),
                        'selectedDate' => $this->selectedDate
                    ]);
                    // Em caso de erro, usar a data atual como fallback
                    $date = now();
                }
            } else {
                Log::warning('Nenhuma data selecionada disponível');
            }
            
            // Iniciar a query - FILTRAR APENAS PELA DATA ESPECÍFICA
            $query = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId);
            
            // IMPORTANTE: Filtrar APENAS por data específica, sem incluir notas antigas
            if ($date) {
                $dateString = $date->format('Y-m-d');
                $query->whereDate('note_date', $dateString);
                
                // Log da query SQL para depuração
                $sql = $query->toSql();
                $bindings = $query->getBindings();
                Log::info('SQL da consulta de histórico', [
                    'sql' => $sql, 
                    'bindings' => $bindings,
                    'data_filtro' => $dateString
                ]);
            } else {
                // Se não há data selecionada, retornar histórico vazio
                Log::warning('Retornando histórico vazio por falta de data válida');
                $this->history = [];
                return;
            }
            
            // Buscar as notas e transformá-las
            $notes = $query->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
                
            Log::info('Notas encontradas para a data', [
                'quantidade' => $notes->count(),
                'data_filtro' => $date->format('Y-m-d'),
                'plan_id' => $this->maintenancePlanId
            ]);
            
            $this->history = $notes->map(function($note) {
                return [
                    'id' => $note->id,
                    'notes' => $note->notes,
                    'status' => $note->status,
                    'user_name' => optional($note->user)->name ?? 'System',
                    'created_at' => $note->created_at->format('Y-m-d H:i:s'),
                    'work_file' => $note->work_file,
                ];
            })->toArray();

            // Verificar se há alguma nota na data selecionada com status completed
            $dateForStatus = $date->format('Y-m-d');
            
            // Query para verificar status completed
            $statusQuery = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
                ->whereDate('note_date', $dateForStatus)
                ->where('status', 'completed');
                
            // Log para debug da query de status
            Log::info('Verificando status completed para a data', [
                'sql' => $statusQuery->toSql(),
                'bindings' => $statusQuery->getBindings(),
                'data_verificacao' => $dateForStatus
            ]);
            
            $statusCompleted = $statusQuery->exists();
            
            // IMPORTANTE: Definir o modo viewOnly com base na verificação de statusCompleted
            // Se houver uma nota com status 'completed' para esta data específica,
            // o modal deve estar em modo somente leitura
            $this->viewOnly = $statusCompleted;
            
            Log::info('Resultado da verificação de status completed', [
                'statusCompleted' => $statusCompleted ? 'SIM' : 'NÃO',
                'data_verificada' => $dateForStatus,
                'viewOnly_definido' => $this->viewOnly ? 'MODO SOMENTE LEITURA' : 'MODO EDIÇÃO'
            ]);
            Log::info('Histórico carregado para data específica', [
                'count' => count($this->history),
                'date' => $date ? $date->format('Y-m-d') : 'sem data'
            ]);
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

    /**
     * Obter status específico para uma data
     */
    private function getStatusForDate($selectedDate)
    {
        if (!$selectedDate) {
            Log::info('getStatusForDate: Sem data selecionada, retornando pending');
            return 'pending';
        }

        $date = \Carbon\Carbon::parse($selectedDate);
        
        Log::info('=== getStatusForDate ===', [
            'selectedDate' => $selectedDate,
            'dateFormatted' => $date->format('Y-m-d'),
            'maintenancePlanId' => $this->maintenancePlanId
        ]);
        
        // Buscar a última nota para a data específica usando note_date
        $lastNote = MaintenanceNote::where('maintenance_plan_id', $this->maintenancePlanId)
            ->whereDate('note_date', $date->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();

        $status = $lastNote ? $lastNote->status : 'pending';
        
        Log::info('Status para data específica', [
            'lastNote' => $lastNote ? $lastNote->toArray() : null,
            'status' => $status
        ]);

        // Se existe nota para a data, retornar seu status, senão retornar 'pending'
        return $status;
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

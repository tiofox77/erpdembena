<?php

return [
    // Page Headers
    'maintenance_schedules' => 'Cronogramas de Manutenção',
    'create_schedule' => 'Criar Cronograma',
    
    // Form Fields and Labels
    'search' => 'Pesquisar',
    'search_schedules' => 'Pesquisar cronogramas...',
    'show' => 'Mostrar',
    'id' => 'ID',
    'title' => 'Título',
    'equipment' => 'Equipamento',
    'frequency' => 'Frequência',
    'next_due_date' => 'Próxima Data Prevista',
    'last_completed' => 'Última Conclusão',
    'status' => 'Status',
    'assigned_to' => 'Atribuído A',
    'priority' => 'Prioridade',
    'duration' => 'Duração Estimada',
    'actions' => 'Ações',
    'created_at' => 'Criado Em',
    'updated_at' => 'Atualizado Em',
    'description' => 'Descrição',
    'instructions' => 'Instruções',
    'tasks' => 'Tarefas',
    'attachments' => 'Anexos',
    'notes' => 'Observações',
    'filter_status' => 'Filtrar por Status',
    'filter_priority' => 'Filtrar por Prioridade',
    'filter_equipment' => 'Filtrar por Equipamento',
    'filter_assigned' => 'Filtrar por Técnico Atribuído',
    'filter_frequency' => 'Filtrar por Frequência',
    
    // Schedule Status
    'active' => 'Ativo',
    'inactive' => 'Inativo',
    'on_hold' => 'Em Espera',
    'completed' => 'Concluído',
    'overdue' => 'Atrasado',
    
    // Priority Levels
    'low' => 'Baixa',
    'medium' => 'Média',
    'high' => 'Alta',
    'critical' => 'Crítica',
    
    // Frequency Options
    'daily' => 'Diária',
    'weekly' => 'Semanal',
    'bi_weekly' => 'Quinzenal',
    'monthly' => 'Mensal',
    'quarterly' => 'Trimestral',
    'semi_annually' => 'Semestral',
    'annually' => 'Anual',
    'custom' => 'Personalizada',
    
    // Modal Titles
    'create_new_schedule' => 'Criar Novo Cronograma de Manutenção',
    'edit_schedule' => 'Editar Cronograma de Manutenção',
    'view_schedule' => 'Visualizar Detalhes do Cronograma de Manutenção',
    'confirm_deletion' => 'Confirmar Exclusão',
    'generate_work_order' => 'Gerar Ordem de Serviço',
    'add_tasks' => 'Adicionar Tarefas',
    'upload_attachments' => 'Carregar Anexos',
    'schedule_history' => 'Histórico do Cronograma',
    
    // Form Fields
    'schedule_title' => 'Título do Cronograma',
    'select_equipment' => 'Selecionar Equipamento',
    'select_frequency' => 'Selecionar Frequência',
    'custom_frequency' => 'Configurações de Frequência Personalizada',
    'interval_value' => 'Valor do Intervalo',
    'interval_unit' => 'Unidade do Intervalo',
    'start_date' => 'Data de Início',
    'end_date' => 'Data de Término (Opcional)',
    'select_priority' => 'Selecionar Prioridade',
    'select_technician' => 'Selecionar Técnico',
    'estimated_duration' => 'Duração Estimada (horas)',
    'schedule_description' => 'Descrição do Cronograma',
    'maintenance_instructions' => 'Instruções de Manutenção',
    'safety_instructions' => 'Instruções de Segurança',
    'schedule_notes' => 'Observações do Cronograma',
    'upload_files' => 'Carregar Arquivos',
    
    // Interval Units
    'days' => 'Dias',
    'weeks' => 'Semanas',
    'months' => 'Meses',
    'years' => 'Anos',
    
    // Tasks
    'task_name' => 'Nome da Tarefa',
    'task_description' => 'Descrição da Tarefa',
    'task_order' => 'Ordem da Tarefa',
    'estimated_time' => 'Tempo Estimado (minutos)',
    'add_task' => 'Adicionar Tarefa',
    'remove_task' => 'Remover Tarefa',
    
    // Form Validation Messages
    'title_required' => 'O título do cronograma é obrigatório',
    'equipment_required' => 'O equipamento é obrigatório',
    'frequency_required' => 'A frequência é obrigatória',
    'start_date_required' => 'A data de início é obrigatória',
    'duration_numeric' => 'A duração deve ser um número',
    'tasks_required' => 'Pelo menos uma tarefa é obrigatória',
    
    // Button Labels
    'save' => 'Salvar',
    'create' => 'Criar',
    'update' => 'Atualizar',
    'cancel' => 'Cancelar',
    'delete' => 'Excluir',
    'close' => 'Fechar',
    'edit' => 'Editar',
    'view' => 'Visualizar',
    'generate' => 'Gerar',
    'add' => 'Adicionar',
    'upload' => 'Carregar',
    'view_history' => 'Ver Histórico',
    'print' => 'Imprimir',
    'export' => 'Exportar',
    
    // Confirmation Messages
    'delete_schedule_confirmation' => 'Tem certeza que deseja excluir este cronograma de manutenção? Esta ação não pode ser desfeita.',
    
    // Notifications
    'schedule_created' => 'Cronograma de manutenção criado com sucesso',
    'schedule_updated' => 'Cronograma de manutenção atualizado com sucesso',
    'schedule_deleted' => 'Cronograma de manutenção excluído com sucesso',
    'work_order_generated' => 'Ordem de serviço gerada com sucesso',
    'tasks_added' => 'Tarefas adicionadas com sucesso',
    'attachments_uploaded' => 'Anexos carregados com sucesso',
    'error_occurred' => 'Ocorreu um erro',
    
    // Empty States
    'no_schedules_found' => 'Nenhum cronograma de manutenção encontrado',
    'create_first_schedule' => 'Crie seu primeiro cronograma de manutenção',
    'no_tasks_added' => 'Nenhuma tarefa adicionada a este cronograma',
    'no_attachments' => 'Nenhum anexo adicionado a este cronograma',
    'no_history_found' => 'Nenhum histórico encontrado para este cronograma',
    
    // Schedule History
    'history_date' => 'Data',
    'history_event' => 'Evento',
    'history_user' => 'Usuário',
    'history_notes' => 'Observações',
    'event_created' => 'Cronograma Criado',
    'event_updated' => 'Cronograma Atualizado',
    'event_completed' => 'Manutenção Concluída',
    'event_work_order' => 'Ordem de Serviço Gerada',
    'event_status_change' => 'Status Alterado',
    
    // Calendar View
    'calendar_view' => 'Visualização de Calendário',
    'list_view' => 'Visualização de Lista',
    'today' => 'Hoje',
    'month' => 'Mês',
    'week' => 'Semana',
    'day' => 'Dia',
    'previous' => 'Anterior',
    'next' => 'Próximo',
];

<?php

return [
    // Page Headers
    'maintenance_plan_management' => 'Gerenciamento de Planos de Manutenção',
    'add_plan' => 'Adicionar Plano',
    
    // Form Fields and Labels
    'search' => 'Pesquisar',
    'search_plans' => 'Pesquisar planos...',
    'show' => 'Mostrar',
    'id' => 'ID',
    'name' => 'Nome',
    'description' => 'Descrição',
    'equipment' => 'Equipamento',
    'frequency' => 'Frequência',
    'next_due' => 'Próximo Vencimento',
    'last_performed' => 'Última Execução',
    'status' => 'Status',
    'assigned_to' => 'Atribuído Para',
    'created_by' => 'Criado Por',
    'estimated_hours' => 'Horas Estimadas',
    'actions' => 'Ações',
    'created_at' => 'Criado Em',
    'updated_at' => 'Atualizado Em',
    'category' => 'Categoria',
    'start_date' => 'Data de Início',
    'end_date' => 'Data de Término',
    'notes' => 'Observações',
    'filter_category' => 'Filtrar por Categoria',
    'filter_status' => 'Filtrar por Status',
    'filter_equipment' => 'Filtrar por Equipamento',
    'filter_assigned' => 'Filtrar por Atribuição',
    
    // Plan Status
    'active' => 'Ativo',
    'inactive' => 'Inativo',
    'completed' => 'Concluído',
    'overdue' => 'Atrasado',
    
    // Frequency Types
    'daily' => 'Diário',
    'weekly' => 'Semanal',
    'monthly' => 'Mensal',
    'quarterly' => 'Trimestral',
    'biannually' => 'Semestral',
    'annually' => 'Anual',
    'custom' => 'Personalizado',
    'hours_operation' => 'Horas de Operação',
    
    // Modal Titles
    'add_new_plan' => 'Adicionar Novo Plano de Manutenção',
    'edit_plan' => 'Editar Plano de Manutenção',
    'view_plan' => 'Visualizar Detalhes do Plano',
    'confirm_deletion' => 'Confirmar Exclusão',
    'generate_tasks' => 'Gerar Tarefas',
    'add_task_to_plan' => 'Adicionar Tarefa ao Plano',
    
    // Form Fields
    'plan_name' => 'Nome do Plano',
    'plan_description' => 'Descrição do Plano',
    'select_equipment' => 'Selecionar Equipamento',
    'select_category' => 'Selecionar Categoria',
    'select_frequency' => 'Selecionar Frequência',
    'select_start_date' => 'Selecionar Data de Início',
    'select_end_date' => 'Selecionar Data de Término',
    'select_technician' => 'Selecionar Técnico',
    'custom_days' => 'Dias Personalizados',
    'operation_hours' => 'Horas de Operação',
    'task_description' => 'Descrição da Tarefa',
    'procedures' => 'Procedimentos',
    'safety_instructions' => 'Instruções de Segurança',
    'required_tools' => 'Ferramentas Necessárias',
    'required_parts' => 'Peças Necessárias',
    
    // Form Validation Messages
    'name_required' => 'O nome do plano é obrigatório',
    'name_max' => 'O nome do plano não pode exceder 100 caracteres',
    'equipment_required' => 'O equipamento é obrigatório',
    'frequency_required' => 'A frequência é obrigatória',
    'start_date_required' => 'A data de início é obrigatória',
    
    // Button Labels
    'save' => 'Salvar',
    'create' => 'Criar',
    'update' => 'Atualizar',
    'cancel' => 'Cancelar',
    'delete' => 'Excluir',
    'close' => 'Fechar',
    'edit' => 'Editar',
    'view' => 'Visualizar',
    'generate' => 'Gerar Tarefas',
    'add_task' => 'Adicionar Tarefa',
    'print' => 'Imprimir',
    'export' => 'Exportar',
    'generate_schedule' => 'Gerar Cronograma',
    
    // Confirmation Messages
    'delete_plan_confirmation' => 'Tem certeza que deseja excluir este plano de manutenção? Esta ação não pode ser desfeita.',
    'plan_has_tasks' => 'Não é possível excluir plano com tarefas associadas',
    'generate_tasks_confirmation' => 'Tem certeza que deseja gerar tarefas para este plano?',
    
    // Notifications
    'plan_created' => 'Plano de manutenção criado com sucesso',
    'plan_updated' => 'Plano de manutenção atualizado com sucesso',
    'plan_deleted' => 'Plano de manutenção excluído com sucesso',
    'tasks_generated' => 'Tarefas de manutenção geradas com sucesso',
    'task_added' => 'Tarefa adicionada ao plano com sucesso',
    'error_occurred' => 'Ocorreu um erro',
    
    // Empty States
    'no_plans_found' => 'Nenhum plano de manutenção encontrado',
    'create_first_plan' => 'Crie seu primeiro plano',
    'no_tasks_found' => 'Nenhuma tarefa associada a este plano',
    
    // Schedule
    'maintenance_schedule' => 'Cronograma de Manutenção',
    'upcoming_maintenance' => 'Manutenções Próximas',
    'overdue_maintenance' => 'Manutenções Atrasadas',
    'today' => 'Hoje',
    'this_week' => 'Esta Semana',
    'this_month' => 'Este Mês',
    'calendar_view' => 'Visualização de Calendário',
    'list_view' => 'Visualização de Lista',
];

<?php

return [
    // Page Headers
    'page_title' => 'Lotes de Folha de Pagamento',
    'page_description' => 'Gerencie processamento em lote de folhas de pagamento',
    'create_new_batch' => 'Criar Novo Lote',
    'clear_filters' => 'Limpar Filtros',
    'debug' => 'Debug',
    
    // Stats Cards
    'total_batches' => 'Total de Lotes',
    'processing_batches' => 'Em Processamento',
    'completed_batches' => 'Concluídos',
    'approved_batches' => 'Aprovados',
    
    // Filters
    'filters_and_search' => 'Filtros e Pesquisa',
    'search_batch' => 'Pesquisar Lote',
    'batch_name_placeholder' => 'Nome ou descrição do lote...',
    'all_status' => 'Todos os Status',
    'all_departments' => 'Todos os Departamentos',
    'all_periods' => 'Todos os Períodos',
    
    // Status Labels
    'draft' => 'Rascunho',
    'ready_to_process' => 'Pronto para Processar',
    'processing' => 'Processando',
    'completed' => 'Concluído',
    'failed' => 'Falhado',
    'approved' => 'Aprovado',
    'paid' => 'Pago',
    
    // Batch Status Labels
    'status_draft' => 'Rascunho',
    'status_ready_to_process' => 'Pronto para Processar',
    'status_processing' => 'Processando',
    'status_completed' => 'Concluído',
    'status_failed' => 'Falhado',
    'status_approved' => 'Aprovado',
    'status_paid' => 'Pago',
    
    // Table Headers
    'batch_info' => 'Informações do Lote',
    'period_info' => 'Período',
    'employee_count' => 'Funcionários',
    'financial_summary' => 'Resumo Financeiro',
    'status' => 'Status',
    'actions' => 'Ações',
    
    // Financial Labels
    'gross_value' => 'Valor Bruto',
    'net_value' => 'Valor Líquido',
    'total_gross_amount' => 'Valor Bruto Total',
    'total_net_amount' => 'Valor Líquido Total',
    'total_deductions' => 'Total de Deduções',
    
    // Progress
    'employees_text' => 'funcionários',
    'processed_text' => 'processados',
    'progress_complete' => 'completo',
    'processing_progress' => 'Progresso do Processamento',
    
    // Action Tooltips
    'view_details' => 'Ver Detalhes',
    'process_batch' => 'Processar Lote',
    'delete_batch' => 'Excluir Lote',
    
    // Empty State
    'no_batches_found' => 'Nenhum lote encontrado',
    'create_first_batch' => 'Comece criando seu primeiro lote de folha de pagamento.',
    'create_first_batch_button' => 'Criar Primeiro Lote',
    
    // Create Batch Modal
    'create_batch_title' => 'Criar Novo Lote de Folha de Pagamento',
    'create_batch_description' => 'Selecione funcionários e configure o processamento em lote',
    'basic_settings' => 'Configurações Básicas',
    'employee_selection' => 'Seleção de Funcionários',
    
    // Form Fields
    'batch_name' => 'Nome do Lote',
    'batch_name_required' => 'Nome do Lote *',
    'batch_name_placeholder' => 'Ex: Folha Janeiro 2025 - Administrativo',
    'batch_date' => 'Data do Lote',
    'batch_date_required' => 'Data do Lote *',
    'payroll_period' => 'Período de Pagamento',
    'payroll_period_required' => 'Período de Pagamento *',
    'select_period' => 'Selecione o período...',
    'payment_method' => 'Método de Pagamento',
    'bank_transfer' => 'Transferência Bancária',
    'cash' => 'Dinheiro',
    'check' => 'Cheque',
    'filter_by_department' => 'Filtrar por Departamento (Opcional)',
    'all_departments_option' => 'Todos os departamentos',
    'description' => 'Descrição (Opcional)',
    'description_placeholder' => 'Descrição adicional sobre este lote de processamento...',
    
    // Employee Selection
    'eligible_employees' => 'Funcionários Elegíveis',
    'employees_found' => 'encontrados',
    'select_all' => 'Selecionar Todos',
    'deselect_all' => 'Desmarcar Todos',
    'employees_selected' => 'funcionário(s) selecionado(s) para processamento',
    'no_eligible_employees' => 'Nenhum funcionário elegível encontrado',
    'no_employees_message' => 'Não há funcionários disponíveis para processamento no período selecionado. Verifique se já não foram processados ou se há funcionários ativos no departamento.',
    
    // Form Actions
    'cancel' => 'Cancelar',
    'create_batch' => 'Criar Lote',
    'creating' => 'Criando...',
    
    // Validation Messages
    'batch_name_required_msg' => 'Nome do lote é obrigatório',
    'payroll_period_required_msg' => 'Período de pagamento é obrigatório',
    'batch_date_required_msg' => 'Data do lote é obrigatória',
    'select_employees_msg' => 'Selecione pelo menos um funcionário',
    'min_employees_msg' => 'Selecione pelo menos um funcionário',
    
    // View Batch Modal
    'batch_details' => 'Detalhes do Lote',
    'basic_information' => 'Informações Básicas',
    'name' => 'Nome',
    'description' => 'Descrição',
    'period' => 'Período',
    'department' => 'Departamento',
    'batch_date_label' => 'Data do Lote',
    'processing_timeline' => 'Timeline de Processamento',
    'created_at' => 'Criado em',
    'processing_started' => 'Processamento iniciado',
    'processing_completed' => 'Processamento concluído',
    'duration' => 'Duração',
    'duration_minutes' => 'minutos',
    
    // Employee List in Batch
    'employees_in_batch' => 'Funcionários no Lote',
    'employee' => 'Funcionário',
    'gross_salary' => 'Salário Bruto',
    'net_salary' => 'Salário Líquido',
    'processed_at' => 'Processado em',
    'no_employees_in_batch' => 'Nenhum funcionário encontrado neste lote.',
    
    // Notes
    'notes' => 'Observações',
    
    // Actions in View Modal
    'close' => 'Fechar',
    'process_batch_button' => 'Processar Lote',
    
    // Delete Modal
    'confirm_deletion' => 'Confirmar Exclusão',
    'action_cannot_be_undone' => 'Esta ação não pode ser desfeita',
    'delete_batch_title' => 'Excluir Lote de Folha de Pagamento',
    'delete_batch_message' => 'Você está prestes a excluir o lote',
    'employees_will_be_removed' => 'funcionário(s) serão removidos do lote',
    'period_label' => 'Período',
    'department_label' => 'Departamento',
    'attention_processing' => 'Atenção: Este lote está sendo processado no momento!',
    'employees_already_processed' => 'funcionário(s) já foram processados neste lote.',
    'what_will_happen' => 'O que acontecerá:',
    'batch_removed_permanently' => '• O lote será removido permanentemente',
    'employee_records_deleted' => '• Registros de funcionários no lote serão excluídos',
    'processed_payrolls_not_affected' => '• Folhas de pagamento já processadas NÃO serão afetadas',
    'action_logged' => '• Esta ação aparecerá nos logs do sistema',
    'batch_summary' => 'Resumo do Lote',
    'processed_employees' => 'Processados',
    'created_by' => 'Criado por',
    
    // Success/Error Messages
    'batch_created_success' => 'Lote de folha de pagamento criado com sucesso!',
    'batch_deleted_success' => 'Lote \':name\' excluído com sucesso!',
    'processing_started_success' => 'Processamento do lote iniciado. Você será notificado quando concluído.',
    'batch_processed_success' => 'Lote processado com sucesso!',
    'debug_executed' => 'Debug test executado - verifique os logs!',
    
    // Error Messages
    'batch_creation_error' => 'Erro ao criar lote: :error',
    'batch_not_found' => 'Lote não encontrado.',
    'cannot_process_batch' => 'Lote não pode ser processado no momento. Status: :status',
    'processing_start_error' => 'Erro ao iniciar processamento: :error',
    'batch_deletion_error' => 'Lote não pode ser excluído.',
    'batch_delete_failed' => 'Erro ao excluir lote: :error',
];

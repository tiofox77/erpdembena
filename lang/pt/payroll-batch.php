<?php

return [
    // Page Title
    'page_title' => 'Lotes de Folha de Pagamento',
    'page_description' => 'Gerir e processar lotes de folha de pagamento',
    
    // Actions
    'create_batch' => 'Criar Novo Lote',
    'view_batch' => 'Ver Detalhes',
    'edit_batch' => 'Editar Lote',
    'delete_batch' => 'Eliminar Lote',
    'process_batch' => 'Processar Lote',
    'reprocess_batch' => 'Reprocessar Lote',
    'export_batch' => 'Exportar',
    'edit_item' => 'Editar',
    'view_receipt' => 'Recibo',
    
    // Table Headers
    'batch_name' => 'Nome do Lote',
    'period' => 'Período',
    'department' => 'Departamento',
    'employees' => 'Funcionários',
    'total_amount' => 'Valor Total',
    'status' => 'Estado',
    'created_at' => 'Criado em',
    'actions' => 'Ações',
    
    // Batch Details
    'batch_info' => 'Informações do Lote',
    'batch_summary' => 'Resumo do Lote',
    'employees_in_batch' => 'Funcionários no Lote',
    'total_employees' => 'Total de Funcionários',
    'total_gross_amount' => 'Total Bruto',
    'total_net_amount' => 'Total Líquido',
    'total_deductions' => 'Total de Deduções',
    'processed_employees' => 'Funcionários Processados',
    'batch_date' => 'Data do Lote',
    'payment_method' => 'Método de Pagamento',
    'payment_methods' => [
        'cash' => 'Dinheiro',
        'bank_transfer' => 'Transferência Bancária',
        'check' => 'Cheque',
    ],
    
    // Status
    'status_draft' => 'Rascunho',
    'status_ready_to_process' => 'Pronto para Processar',
    'status_processing' => 'Processando',
    'status_completed' => 'Concluído',
    'status_failed' => 'Falhado',
    'status_cancelled' => 'Cancelado',
    
    // Item Status
    'item_status_pending' => 'Pendente',
    'item_status_processing' => 'Processando',
    'item_status_completed' => 'Completado',
    'item_status_failed' => 'Falhado',
    'item_status_skipped' => 'Ignorado',
    
    // Create Batch Modal
    'create_batch_title' => 'Criar Novo Lote de Folha de Pagamento',
    'create_batch_description' => 'Preencha os detalhes para criar um novo lote',
    'batch_name_label' => 'Nome do Lote',
    'batch_name_placeholder' => 'Ex: Folha de Junho 2025',
    'batch_description_label' => 'Descrição',
    'batch_description_placeholder' => 'Descrição opcional do lote',
    'payroll_period_label' => 'Período de Pagamento',
    'payroll_period_placeholder' => 'Selecione o período',
    'department_label' => 'Departamento (Opcional)',
    'department_placeholder' => 'Todos os departamentos',
    'batch_date_label' => 'Data do Lote',
    'payment_method_label' => 'Método de Pagamento',
    'select_employees' => 'Selecionar Funcionários',
    'all_employees' => 'Todos os Funcionários',
    'selected_employees' => 'funcionário(s) selecionado(s)',
    'no_employees_selected' => 'Nenhum funcionário selecionado',
    'search_employees' => 'Pesquisar funcionários...',
    'select_all' => 'Selecionar Todos',
    'deselect_all' => 'Desselecionar Todos',
    
    // View Batch Modal
    'batch_details' => 'Detalhes do Lote',
    'processing_information' => 'Informações de Processamento',
    'processing_started_at' => 'Início do Processamento',
    'processing_completed_at' => 'Fim do Processamento',
    'processing_duration' => 'Duração',
    'duration_minutes' => 'minutos',
    'created_by' => 'Criado por',
    'approved_by' => 'Aprovado por',
    
    // Employee List
    'employee' => 'Funcionário',
    'gross_salary' => 'Salário Bruto',
    'deductions' => 'Deduções',
    'net_salary' => 'Salário Líquido',
    'processed_at' => 'Processado em',
    'no_employees_in_batch' => 'Nenhum funcionário neste lote',
    
    // Edit Item Modal
    'edit_item_title' => 'Editar Item do Lote',
    'employee_information' => 'Informações do Funcionário',
    'editable_fields' => 'Campos Editáveis - Ajuste o Pagamento',
    'additional_bonus' => 'Bónus Adicional',
    'additional_bonus_placeholder' => '0.00 AOA',
    'additional_bonus_help' => 'Bónus extra para este pagamento',
    'christmas_subsidy' => 'Subsídio de Natal (50%)',
    'christmas_subsidy_label' => 'Incluir Subsídio de Natal',
    'christmas_subsidy_help' => '50% do salário base',
    'vacation_subsidy' => 'Subsídio de Férias (50%)',
    'vacation_subsidy_label' => 'Incluir Subsídio de Férias',
    'vacation_subsidy_help' => '50% do salário base',
    'recalculation_notice' => 'Os valores serão recalculados automaticamente ao alterar os campos acima.',
    
    // Employee Details Sections
    'basic_info' => 'Informações do Funcionário',
    'basic_salary' => 'Salário Base',
    'hourly_rate' => 'Taxa Horária',
    'daily_rate' => 'Taxa Diária',
    'working_days' => 'Dias Úteis',
    
    'attendance_summary' => 'Resumo de Presença',
    'hours_worked' => 'Horas Trabalhadas',
    'present_days' => 'Dias Presentes',
    'absent_days' => 'Faltas',
    'late_arrivals' => 'Atrasos',
    
    'overtime_records' => 'Horas Extras',
    'overtime_amount' => 'Valor de Horas Extras',
    
    'salary_advances' => 'Adiantamentos Salariais',
    'monthly_deduction' => 'Dedução Mensal',
    'non_taxable_note' => 'Até 30k não tributável',
    
    'salary_discounts' => 'Descontos Salariais',
    'total_discounts' => 'Total de Descontos',
    
    'benefits_allowances' => 'Benefícios e Subsídios',
    'food_subsidy' => 'Subsídio de Alimentação',
    'transport_subsidy' => 'Subsídio de Transporte',
    'profile_bonus' => 'Bónus do Perfil',
    'non_taxable' => 'Não tributável',
    'proportional' => 'Proporcional',
    
    // Payroll Summary
    'payroll_summary' => 'Resumo da Folha de Pagamento',
    'base_salary' => 'Salário Base',
    'employee_profile_bonus' => 'Bónus do Perfil',
    'additional_payroll_bonus' => 'Bónus Adicional',
    'gross_salary_label' => 'Salário Bruto (Gross Salary)',
    
    // Deductions Section
    'deductions_section' => 'Deduções',
    'irt_label' => 'IRT (Imposto sobre Rendimento do Trabalho)',
    'irt_base' => 'Base',
    'inss_employee' => 'INSS (3% - Funcionário)',
    'inss_employer' => 'INSS (8% - Empresa)',
    'inss_illustrative' => 'Apenas ilustrativo - pago pela empresa',
    'advance_deductions' => 'Adiantamentos Salariais',
    'discount_deductions' => 'Descontos Salariais',
    'absence_deductions' => 'Dedução por Faltas',
    'late_deductions' => 'Dedução por Atrasos',
    'total_deductions_label' => 'Total de Deduções',
    
    // Net Salary
    'final_net_salary' => 'Salário Líquido Final',
    
    // Notes
    'notes' => 'Observações',
    'notes_placeholder' => 'Adicione observações sobre as alterações (opcional)',
    
    // Status Alerts
    'ready_to_process_title' => 'Lote Pronto para Processar',
    'ready_to_process_message' => 'Este lote contém :count funcionários e está pronto para ser processado.',
    'ready_to_process_tip' => 'Você pode editar os salários dos itens Pendentes antes de processar.',
    'processing_failed_title' => 'Processamento Falhou',
    'processing_failed_message' => 'O processamento deste lote encontrou erros. Reveja os detalhes abaixo e clique em "Reprocessar Lote" para tentar novamente.',
    'processing_success_title' => 'Processamento Concluído',
    'processing_success_message' => 'O lote foi processado com sucesso. Todos os registros de folha de pagamento foram criados.',
    
    // Buttons
    'close' => 'Fechar',
    'cancel' => 'Cancelar',
    'save' => 'Salvar',
    'save_changes' => 'Salvar Alterações',
    'create' => 'Criar',
    'process' => 'Processar',
    'confirm' => 'Confirmar',
    'back' => 'Voltar',
    
    // Messages
    'batch_created_success' => 'Lote criado com sucesso!',
    'batch_updated_success' => 'Lote atualizado com sucesso!',
    'batch_deleted_success' => 'Lote eliminado com sucesso!',
    'item_updated_success' => 'Item do lote atualizado com sucesso!',
    'processing_started_success' => 'Processamento do lote iniciado com sucesso!',
    'batch_not_found' => 'Lote não encontrado.',
    'cannot_process_batch' => 'Não é possível processar o lote com estado: :status',
    'batch_creation_error' => 'Erro ao criar lote: :error',
    'processing_start_error' => 'Erro ao iniciar processamento: :error',
    'item_not_found' => 'Item não encontrado.',
    'item_update_error' => 'Erro ao atualizar item: :error',
    
    // Validation Messages
    'batch_name_required_msg' => 'O nome do lote é obrigatório.',
    'payroll_period_required_msg' => 'O período de pagamento é obrigatório.',
    'batch_date_required_msg' => 'A data do lote é obrigatória.',
    'select_employees_msg' => 'Selecione pelo menos um funcionário.',
    'min_employees_msg' => 'Selecione pelo menos um funcionário para criar o lote.',
    
    // Delete Confirmation
    'delete_confirmation_title' => 'Confirmar Eliminação',
    'delete_confirmation_message' => 'Tem certeza que deseja eliminar o lote ":name"?',
    'delete_warning' => 'Esta ação não pode ser desfeita. Todos os itens do lote serão removidos.',
    
    // Empty States
    'no_batches_found' => 'Nenhum lote de pagamento encontrado.',
    'create_first_batch' => 'Crie o seu primeiro lote de pagamento.',
    
    // Filter Labels
    'filter_by_status' => 'Filtrar por Estado',
    'filter_by_department' => 'Filtrar por Departamento',
    'filter_by_period' => 'Filtrar por Período',
    'all_statuses' => 'Todos os Estados',
    'all_departments' => 'Todos os Departamentos',
    'all_periods' => 'Todos os Períodos',
    
    // Currency
    'currency' => 'AOA',
];

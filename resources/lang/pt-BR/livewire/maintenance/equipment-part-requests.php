<?php

return [
    // Page Headers
    'part_requests_management' => 'Gerenciamento de Solicitações de Peças',
    'create_request' => 'Criar Solicitação',
    
    // Form Fields and Labels
    'search' => 'Pesquisar',
    'search_requests' => 'Pesquisar solicitações...',
    'show' => 'Mostrar',
    'id' => 'ID',
    'request_number' => 'Solicitação #',
    'equipment' => 'Equipamento',
    'task' => 'Tarefa Relacionada',
    'requester' => 'Solicitante',
    'request_date' => 'Data da Solicitação',
    'required_date' => 'Data Necessária',
    'priority' => 'Prioridade',
    'status' => 'Status',
    'parts' => 'Peças',
    'quantity' => 'Quantidade',
    'actions' => 'Ações',
    'created_at' => 'Criado Em',
    'updated_at' => 'Atualizado Em',
    'approved_by' => 'Aprovado Por',
    'issued_by' => 'Emitido Por',
    'notes' => 'Observações',
    'filter_status' => 'Filtrar por Status',
    'filter_priority' => 'Filtrar por Prioridade',
    'filter_equipment' => 'Filtrar por Equipamento',
    'filter_requester' => 'Filtrar por Solicitante',
    
    // Request Status
    'pending' => 'Pendente',
    'approved' => 'Aprovado',
    'rejected' => 'Rejeitado',
    'issued' => 'Emitido',
    'cancelled' => 'Cancelado',
    'partially_issued' => 'Parcialmente Emitido',
    
    // Request Priority
    'low' => 'Baixa',
    'medium' => 'Média',
    'high' => 'Alta',
    'urgent' => 'Urgente',
    
    // Modal Titles
    'create_part_request' => 'Criar Solicitação de Peça',
    'edit_request' => 'Editar Solicitação',
    'view_request' => 'Visualizar Detalhes da Solicitação',
    'confirm_deletion' => 'Confirmar Exclusão',
    'approve_request' => 'Aprovar Solicitação',
    'reject_request' => 'Rejeitar Solicitação',
    'issue_parts' => 'Emitir Peças',
    'add_parts' => 'Adicionar Peças',
    
    // Form Fields
    'select_equipment' => 'Selecionar Equipamento',
    'select_task' => 'Selecionar Tarefa Relacionada',
    'select_priority' => 'Selecionar Prioridade',
    'select_requester' => 'Selecionar Solicitante',
    'select_part' => 'Selecionar Peça',
    'request_reason' => 'Motivo da Solicitação',
    'required_by_date' => 'Data Necessária',
    'request_notes' => 'Observações da Solicitação',
    'approval_notes' => 'Observações de Aprovação',
    'rejection_reason' => 'Motivo da Rejeição',
    'issuance_notes' => 'Observações de Emissão',
    
    // Form Validation Messages
    'equipment_required' => 'O equipamento é obrigatório',
    'requester_required' => 'O solicitante é obrigatório',
    'parts_required' => 'Pelo menos uma peça é obrigatória',
    'quantity_required' => 'A quantidade é obrigatória',
    'quantity_numeric' => 'A quantidade deve ser um número',
    'quantity_min' => 'A quantidade deve ser pelo menos 1',
    'quantity_insufficient' => 'Estoque insuficiente disponível',
    
    // Button Labels
    'save' => 'Salvar',
    'submit' => 'Enviar',
    'update' => 'Atualizar',
    'cancel' => 'Cancelar',
    'delete' => 'Excluir',
    'close' => 'Fechar',
    'edit' => 'Editar',
    'view' => 'Visualizar',
    'approve' => 'Aprovar',
    'reject' => 'Rejeitar',
    'issue' => 'Emitir',
    'add_part' => 'Adicionar Peça',
    'remove_part' => 'Remover Peça',
    'print' => 'Imprimir',
    'export' => 'Exportar',
    
    // Confirmation Messages
    'delete_request_confirmation' => 'Tem certeza que deseja excluir esta solicitação de peça? Esta ação não pode ser desfeita.',
    'approve_request_confirmation' => 'Tem certeza que deseja aprovar esta solicitação de peça?',
    'reject_request_confirmation' => 'Tem certeza que deseja rejeitar esta solicitação de peça?',
    'issue_request_confirmation' => 'Tem certeza que deseja emitir peças para esta solicitação?',
    
    // Notifications
    'request_created' => 'Solicitação de peça criada com sucesso',
    'request_updated' => 'Solicitação de peça atualizada com sucesso',
    'request_deleted' => 'Solicitação de peça excluída com sucesso',
    'request_approved' => 'Solicitação de peça aprovada com sucesso',
    'request_rejected' => 'Solicitação de peça rejeitada com sucesso',
    'parts_issued' => 'Peças emitidas com sucesso',
    'error_occurred' => 'Ocorreu um erro',
    
    // Empty States
    'no_requests_found' => 'Nenhuma solicitação de peça encontrada',
    'create_first_request' => 'Crie sua primeira solicitação',
    'no_parts_added' => 'Nenhuma peça adicionada a esta solicitação',
    
    // Part Details
    'part_details' => 'Detalhes da Peça',
    'part_name' => 'Nome da Peça',
    'part_number' => 'Número da Peça',
    'requested_quantity' => 'Quantidade Solicitada',
    'issued_quantity' => 'Quantidade Emitida',
    'available_quantity' => 'Quantidade Disponível',
];

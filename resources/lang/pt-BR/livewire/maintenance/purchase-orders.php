<?php

return [
    // Page Headers
    'purchase_orders_management' => 'Gerenciamento de Ordens de Compra',
    'create_purchase_order' => 'Criar Ordem de Compra',
    
    // Form Fields and Labels
    'search' => 'Pesquisar',
    'search_purchase_orders' => 'Pesquisar ordens de compra...',
    'show' => 'Mostrar',
    'id' => 'ID',
    'po_number' => 'OC #',
    'title' => 'Título',
    'supplier' => 'Fornecedor',
    'date_issued' => 'Data de Emissão',
    'expected_delivery' => 'Entrega Prevista',
    'delivery_date' => 'Data de Entrega',
    'total_amount' => 'Valor Total',
    'currency' => 'Moeda',
    'payment_terms' => 'Condições de Pagamento',
    'status' => 'Status',
    'created_by' => 'Criado Por',
    'approved_by' => 'Aprovado Por',
    'actions' => 'Ações',
    'created_at' => 'Criado Em',
    'updated_at' => 'Atualizado Em',
    'items' => 'Itens',
    'notes' => 'Observações',
    'attachments' => 'Anexos',
    'shipping_address' => 'Endereço de Entrega',
    'billing_address' => 'Endereço de Cobrança',
    'tax_amount' => 'Valor de Imposto',
    'shipping_cost' => 'Custo de Envio',
    'discount' => 'Desconto',
    'subtotal' => 'Subtotal',
    'filter_status' => 'Filtrar por Status',
    'filter_supplier' => 'Filtrar por Fornecedor',
    'filter_date_range' => 'Filtrar por Intervalo de Data',
    'filter_amount' => 'Filtrar por Valor',
    
    // Purchase Order Status
    'draft' => 'Rascunho',
    'pending_approval' => 'Aguardando Aprovação',
    'approved' => 'Aprovado',
    'sent' => 'Enviado',
    'partially_received' => 'Parcialmente Recebido',
    'received' => 'Recebido',
    'cancelled' => 'Cancelado',
    'on_hold' => 'Em Espera',
    'rejected' => 'Rejeitado',
    
    // Modal Titles
    'create_new_po' => 'Criar Nova Ordem de Compra',
    'edit_po' => 'Editar Ordem de Compra',
    'view_po' => 'Visualizar Detalhes da Ordem de Compra',
    'confirm_deletion' => 'Confirmar Exclusão',
    'add_items' => 'Adicionar Itens',
    'receive_items' => 'Receber Itens',
    'approve_po' => 'Aprovar Ordem de Compra',
    'reject_po' => 'Rejeitar Ordem de Compra',
    'upload_attachments' => 'Carregar Anexos',
    'print_po' => 'Imprimir Ordem de Compra',
    
    // Form Fields
    'po_title' => 'Título da Ordem de Compra',
    'select_supplier' => 'Selecionar Fornecedor',
    'issue_date' => 'Data de Emissão',
    'delivery_date' => 'Data de Entrega Prevista',
    'select_payment_terms' => 'Selecionar Condições de Pagamento',
    'select_status' => 'Selecionar Status',
    'shipping_information' => 'Informações de Envio',
    'billing_information' => 'Informações de Cobrança',
    'po_notes' => 'Observações da Ordem de Compra',
    'internal_notes' => 'Observações Internas',
    'upload_files' => 'Carregar Arquivos',
    'approval_notes' => 'Observações de Aprovação',
    'rejection_reason' => 'Motivo da Rejeição',
    
    // Items
    'item_name' => 'Nome do Item',
    'item_description' => 'Descrição',
    'part_number' => 'Número da Peça',
    'quantity' => 'Quantidade',
    'unit_price' => 'Preço Unitário',
    'unit' => 'Unidade',
    'total_price' => 'Preço Total',
    'select_item' => 'Selecionar Item',
    'add_item' => 'Adicionar Item',
    'remove_item' => 'Remover Item',
    'received_quantity' => 'Quantidade Recebida',
    'pending_quantity' => 'Quantidade Pendente',
    
    // Form Validation Messages
    'supplier_required' => 'O fornecedor é obrigatório',
    'issue_date_required' => 'A data de emissão é obrigatória',
    'delivery_date_required' => 'A data de entrega prevista é obrigatória',
    'items_required' => 'Pelo menos um item é obrigatório',
    'quantity_required' => 'A quantidade é obrigatória',
    'quantity_numeric' => 'A quantidade deve ser um número',
    'quantity_min' => 'A quantidade deve ser pelo menos 1',
    'price_required' => 'O preço unitário é obrigatório',
    'price_numeric' => 'O preço unitário deve ser um número',
    
    // Button Labels
    'save' => 'Salvar',
    'save_as_draft' => 'Salvar como Rascunho',
    'create' => 'Criar',
    'update' => 'Atualizar',
    'cancel' => 'Cancelar',
    'delete' => 'Excluir',
    'close' => 'Fechar',
    'edit' => 'Editar',
    'view' => 'Visualizar',
    'send' => 'Enviar ao Fornecedor',
    'approve' => 'Aprovar',
    'reject' => 'Rejeitar',
    'receive' => 'Receber Itens',
    'add' => 'Adicionar',
    'upload' => 'Carregar',
    'print' => 'Imprimir',
    'export' => 'Exportar',
    'generate_pdf' => 'Gerar PDF',
    
    // Confirmation Messages
    'delete_po_confirmation' => 'Tem certeza que deseja excluir esta ordem de compra? Esta ação não pode ser desfeita.',
    'approve_po_confirmation' => 'Tem certeza que deseja aprovar esta ordem de compra?',
    'reject_po_confirmation' => 'Tem certeza que deseja rejeitar esta ordem de compra?',
    'cancel_po_confirmation' => 'Tem certeza que deseja cancelar esta ordem de compra?',
    
    // Notifications
    'po_created' => 'Ordem de compra criada com sucesso',
    'po_updated' => 'Ordem de compra atualizada com sucesso',
    'po_deleted' => 'Ordem de compra excluída com sucesso',
    'po_approved' => 'Ordem de compra aprovada com sucesso',
    'po_rejected' => 'Ordem de compra rejeitada com sucesso',
    'po_sent' => 'Ordem de compra enviada ao fornecedor com sucesso',
    'items_received' => 'Itens recebidos com sucesso',
    'attachments_uploaded' => 'Anexos carregados com sucesso',
    'error_occurred' => 'Ocorreu um erro',
    
    // Empty States
    'no_purchase_orders_found' => 'Nenhuma ordem de compra encontrada',
    'create_first_po' => 'Crie sua primeira ordem de compra',
    'no_items_added' => 'Nenhum item adicionado a esta ordem de compra',
    'no_attachments' => 'Nenhum anexo adicionado a esta ordem de compra',
    
    // Receiving
    'receiving' => 'Recebimento',
    'receive_date' => 'Data de Recebimento',
    'received_by' => 'Recebido Por',
    'receiving_notes' => 'Observações de Recebimento',
    'partial_receiving' => 'Recebimento Parcial',
    'complete_receiving' => 'Recebimento Completo',
];

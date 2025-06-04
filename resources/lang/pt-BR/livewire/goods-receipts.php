<?php

return [
    // Headers
    'goods_receipts_management' => 'Gerenciamento de Recebimentos de Mercadorias',
    'goods_receipt_details' => 'Detalhes do Recebimento',
    'goods_receipt_items' => 'Itens do Recebimento',
    
    // Form Labels
    'receipt_number' => 'Número do Recebimento',
    'purchase_order' => 'Pedido de Compra',
    'supplier' => 'Fornecedor',
    'location' => 'Local',
    'receipt_date' => 'Data do Recebimento',
    'status' => 'Status',
    'notes' => 'Observações',
    'received_by' => 'Recebido Por',
    
    // Item Form Labels
    'product' => 'Produto',
    'purchase_order_item' => 'Item do Pedido',
    'expected_quantity' => 'Quantidade Esperada',
    'received_quantity' => 'Quantidade Recebida',
    'accepted_quantity' => 'Quantidade Aceita',
    'rejected_quantity' => 'Quantidade Rejeitada',
    'unit_cost' => 'Custo Unitário',
    'line_total' => 'Total da Linha',
    'rejection_reason' => 'Motivo da Rejeição',
    
    // Status Options
    'pending' => 'Pendente',
    'processing' => 'Em Processamento',
    'completed' => 'Concluído',
    'cancelled' => 'Cancelado',
    
    // Actions
    'create_goods_receipt' => 'Criar Recebimento',
    'edit_goods_receipt' => 'Editar Recebimento',
    'view_goods_receipt' => 'Visualizar Recebimento',
    'delete_goods_receipt' => 'Excluir Recebimento',
    'add_item' => 'Adicionar Item',
    'remove_item' => 'Remover Item',
    'complete_receipt' => 'Concluir Recebimento',
    'cancel_receipt' => 'Cancelar Recebimento',
    'process_receipt' => 'Processar Recebimento',
    'view_purchase_order' => 'Visualizar Pedido de Compra',
    
    // Confirmations
    'confirm_delete_receipt' => 'Tem certeza que deseja excluir este recebimento?',
    'confirm_cancel_receipt' => 'Tem certeza que deseja cancelar este recebimento?',
    'confirm_complete_receipt' => 'Tem certeza que deseja concluir este recebimento? Isso atualizará as quantidades no inventário.',
    'delete_warning' => 'Esta ação não pode ser desfeita.',
    
    // Messages
    'receipt_created' => 'Recebimento criado com sucesso',
    'receipt_updated' => 'Recebimento atualizado com sucesso',
    'receipt_deleted' => 'Recebimento excluído com sucesso',
    'receipt_processed' => 'Recebimento processado com sucesso',
    'receipt_completed' => 'Recebimento concluído com sucesso',
    'receipt_cancelled' => 'Recebimento cancelado com sucesso',
    'error' => 'Erro',
    'cannot_edit_completed_cancelled_receipt' => 'Não é possível editar um recebimento concluído ou cancelado',
    'cannot_delete_processed_receipt' => 'Não é possível excluir um recebimento processado',
    'goods_receipt_not_found' => 'Recebimento não encontrado',
    'select_purchase_order' => 'Por favor, selecione um pedido de compra',
    'select_supplier' => 'Por favor, selecione um fornecedor',
    'select_location' => 'Por favor, selecione um local',
];

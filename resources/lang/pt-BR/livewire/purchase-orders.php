<?php

return [
    // Headers
    'purchase_orders_management' => 'Gerenciamento de Pedidos de Compra',
    'purchase_order_details' => 'Detalhes do Pedido de Compra',
    'purchase_order_items' => 'Itens do Pedido de Compra',
    
    // Form Labels
    'order_number' => 'Número do Pedido',
    'supplier' => 'Fornecedor',
    'order_date' => 'Data do Pedido',
    'expected_delivery_date' => 'Data Prevista de Entrega',
    'delivery_date' => 'Data de Entrega Real',
    'shipping_address' => 'Endereço de Entrega',
    'status' => 'Status',
    'subtotal' => 'Subtotal',
    'tax_amount' => 'Valor de Impostos',
    'shipping_cost' => 'Custo de Frete',
    'discount_amount' => 'Valor de Desconto',
    'total_amount' => 'Valor Total',
    'notes' => 'Observações',
    'created_by' => 'Criado Por',
    'active' => 'Ativo',
    'inactive' => 'Inativo',
    
    // Item Form Labels
    'product' => 'Produto',
    'quantity' => 'Quantidade',
    'unit_price' => 'Preço Unitário',
    'line_total' => 'Total da Linha',
    
    // Status Options
    'draft' => 'Rascunho',
    'pending_approval' => 'Aguardando Aprovação',
    'approved' => 'Aprovado',
    'ordered' => 'Pedido Efetuado',
    'partially_received' => 'Parcialmente Recebido',
    'completed' => 'Concluído',
    'cancelled' => 'Cancelado',
    
    // Actions
    'create_purchase_order' => 'Criar Pedido de Compra',
    'edit_purchase_order' => 'Editar Pedido de Compra',
    'view_purchase_order' => 'Visualizar Pedido de Compra',
    'delete_purchase_order' => 'Excluir Pedido de Compra',
    'add_item' => 'Adicionar Item',
    'remove_item' => 'Remover Item',
    'approve_order' => 'Aprovar Pedido',
    'cancel_order' => 'Cancelar Pedido',
    'mark_as_ordered' => 'Marcar como Pedido',
    'create_goods_receipt' => 'Criar Recebimento de Mercadoria',
    
    // Confirmations
    'confirm_delete_order' => 'Tem certeza que deseja excluir este pedido de compra?',
    'confirm_cancel_order' => 'Tem certeza que deseja cancelar este pedido de compra?',
    'delete_warning' => 'Esta ação não pode ser desfeita.',
    
    // Messages
    'order_created' => 'Pedido de compra criado com sucesso',
    'order_updated' => 'Pedido de compra atualizado com sucesso',
    'order_deleted' => 'Pedido de compra excluído com sucesso',
    'order_approved' => 'Pedido de compra aprovado com sucesso',
    'order_cancelled' => 'Pedido de compra cancelado com sucesso',
    'order_marked_as_ordered' => 'Pedido de compra marcado como pedido com sucesso',
    'error' => 'Erro',
    'cannot_delete_order_with_receipts' => 'Não é possível excluir pedido com recebimentos',
    'cannot_edit_completed_cancelled_order' => 'Não é possível editar um pedido concluído ou cancelado',
    'select_product' => 'Por favor, selecione um produto',
    'select_supplier' => 'Por favor, selecione um fornecedor',
];

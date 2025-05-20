<?php

return [
    // Headers
    'purchase_orders_management' => 'Purchase Orders Management',
    'purchase_order_details' => 'Purchase Order Details',
    'purchase_order_items' => 'Purchase Order Items',
    
    // Form Labels
    'order_number' => 'Order Number',
    'supplier' => 'Supplier',
    'order_date' => 'Order Date',
    'expected_delivery_date' => 'Expected Delivery Date',
    'delivery_date' => 'Actual Delivery Date',
    'shipping_address' => 'Shipping Address',
    'status' => 'Status',
    'subtotal' => 'Subtotal',
    'tax_amount' => 'Tax Amount',
    'shipping_cost' => 'Shipping Cost',
    'discount_amount' => 'Discount Amount',
    'total_amount' => 'Total Amount',
    'notes' => 'Notes',
    'created_by' => 'Created By',
    
    // Item Form Labels
    'product' => 'Product',
    'quantity' => 'Quantity',
    'unit_price' => 'Unit Price',
    'line_total' => 'Line Total',
    'received_quantity' => 'Received Quantity',
    'remaining_quantity' => 'Remaining Quantity',
    
    // Status Options
    'draft' => 'Draft',
    'pending_approval' => 'Pending Approval',
    'approved' => 'Approved',
    'ordered' => 'Ordered',
    'partially_received' => 'Partially Received',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
    
    // Actions
    'create_purchase_order' => 'Create Purchase Order',
    'edit_purchase_order' => 'Edit Purchase Order',
    'view_purchase_order' => 'View Purchase Order',
    'delete_purchase_order' => 'Delete Purchase Order',
    'add_item' => 'Add Item',
    'remove_item' => 'Remove Item',
    'approve_order' => 'Approve Order',
    'cancel_order' => 'Cancel Order',
    'mark_as_ordered' => 'Mark as Ordered',
    'create_goods_receipt' => 'Create Goods Receipt',
    
    // Confirmations
    'confirm_delete_order' => 'Are you sure you want to delete this purchase order?',
    'confirm_cancel_order' => 'Are you sure you want to cancel this purchase order?',
    'delete_warning' => 'This action cannot be undone.',
    
    // Messages
    'order_created' => 'Purchase order created successfully',
    'order_updated' => 'Purchase order updated successfully',
    'order_deleted' => 'Purchase order deleted successfully',
    'order_approved' => 'Purchase order approved successfully',
    'order_cancelled' => 'Purchase order cancelled successfully',
    'order_marked_as_ordered' => 'Purchase order marked as ordered successfully',
    'error' => 'Error',
    'cannot_delete_order_with_receipts' => 'Cannot delete purchase order with goods receipts',
    'cannot_edit_completed_cancelled_order' => 'Cannot edit a completed or cancelled purchase order',
    'select_product' => 'Please select a product',
    'select_supplier' => 'Please select a supplier',
    
    // Product Selection Modal
    'select_products' => 'Select Products',
    'search_products_placeholder' => 'Search products by name, code or description...',
    'code' => 'Code',
    'price' => 'Price',
    'add_to_order' => 'Add to order',
    'no_products_found_for' => 'No products found for',
    'no_products_available' => 'No products available',
    'try_different_search' => 'Try a different search term',
    'all_products_loaded' => 'All products loaded',
    'products_selected' => '{0} No products selected|{1} 1 product selected|[2,*] :count products selected',
    'showing_products' => 'Showing :from to :to of :total products',
    'done' => 'Done',
];

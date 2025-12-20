<?php

return [
    // Page Headers
    'page_title' => 'Batch Payroll',
    'page_description' => 'Manage batch payroll processing',
    'create_new_batch' => 'Create New Batch',
    'clear_filters' => 'Clear Filters',
    'debug' => 'Debug',
    
    // Stats Cards
    'total_batches' => 'Total Batches',
    'processing_batches' => 'Processing',
    'completed_batches' => 'Completed',
    'approved_batches' => 'Approved',
    
    // Filters
    'filters_and_search' => 'Filters and Search',
    'search_batch' => 'Search batch...',
    'batch_name_placeholder' => 'Batch name or description...',
    'all_status' => 'All Status',
    'all_departments' => 'All Departments',
    'all_periods' => 'All Periods',
    
    // Status Labels
    'draft' => 'Draft',
    'ready_to_process' => 'Ready to Process',
    'processing' => 'Processing',
    'completed' => 'Completed',
    'failed' => 'Failed',
    'approved' => 'Approved',
    'paid' => 'Paid',
    
    // Batch Status Labels
    'status_draft' => 'Draft',
    'status_ready_to_process' => 'Ready to Process', 
    'status_processing' => 'Processing',
    'status_completed' => 'Completed',
    'status_failed' => 'Failed',
    'status_approved' => 'Approved',
    'status_paid' => 'Paid',
    
    // Table Headers
    'batch_info' => 'Batch Info',
    'period_info' => 'Period',
    'employee_count' => 'Employees',
    'financial_summary' => 'Financial Summary',
    'status' => 'Status',
    'actions' => 'Actions',
    
    // Financial Labels
    'gross_value' => 'Gross Value',
    'net_value' => 'Net Value',
    'total_gross_amount' => 'Total Gross Amount',
    'total_net_amount' => 'Total Net Amount',
    'total_deductions' => 'Total Deductions',
    
    // Progress
    'employees_text' => 'employees',
    'processed_text' => 'processed',
    'progress_complete' => 'complete',
    'processing_progress' => 'Processing Progress',
    
    // Action Tooltips
    'view_details' => 'View Details',
    'process_batch' => 'Process Batch',
    'delete_batch' => 'Delete Batch',
    
    // Empty State
    'no_batches_found' => 'No batches found',
    'create_first_batch' => 'Start by creating your first batch payroll.',
    'create_first_batch_button' => 'Create First Batch',
    
    // Create Batch Modal
    'create_batch_title' => 'Create New Batch Payroll',
    'create_batch_description' => 'Select employees and configure batch processing',
    'basic_settings' => 'Basic Settings',
    'employee_selection' => 'Employee Selection',
    
    // Form Fields
    'batch_name' => 'Batch Name',
    'batch_name_required' => 'Batch Name *',
    'batch_name_placeholder' => 'Ex: January 2025 Payroll - Administrative',
    'batch_date' => 'Batch Date',
    'batch_date_required' => 'Batch Date *',
    'payroll_period' => 'Payroll Period',
    'payroll_period_required' => 'Payroll Period *',
    'select_period' => 'Select period...',
    'payment_method' => 'Payment Method',
    'bank_transfer' => 'Bank Transfer',
    'cash' => 'Cash',
    'check' => 'Check',
    'filter_by_department' => 'Filter by Department (Optional)',
    'all_departments_option' => 'All departments',
    'description' => 'Description (Optional)',
    'description_placeholder' => 'Additional description about this batch processing...',
    
    // Employee Selection
    'eligible_employees' => 'Eligible Employees',
    'employees_found' => 'found',
    'select_all' => 'Select All',
    'deselect_all' => 'Deselect All',
    'employees_selected' => 'employee(s) selected for processing',
    'no_eligible_employees' => 'No eligible employees found',
    'no_employees_message' => 'There are no employees available for processing in the selected period. Check if they have already been processed or if there are active employees in the department.',
    
    // Form Actions
    'cancel' => 'Cancel',
    'create_batch' => 'Create Batch',
    'creating' => 'Creating...',
    
    // Validation Messages
    'batch_name_required_msg' => 'Batch name is required',
    'payroll_period_required_msg' => 'Payroll period is required',  
    'batch_date_required_msg' => 'Batch date is required',
    'select_employees_msg' => 'Select at least one employee',
    'min_employees_msg' => 'Select at least one employee',
    
    // View Batch Modal
    'batch_details' => 'Batch Details',
    'basic_information' => 'Basic Information',
    'name' => 'Name',
    'description' => 'Description',
    'period' => 'Period',
    'department' => 'Department',
    'batch_date_label' => 'Batch Date',
    'processing_timeline' => 'Processing Timeline',
    'created_at' => 'Created at',
    'processing_started' => 'Processing started',
    'processing_completed' => 'Processing completed',
    'duration' => 'Duration',
    'duration_minutes' => 'minutes',
    
    // Employee List in Batch
    'employees_in_batch' => 'Employees in Batch',
    'employee' => 'Employee',
    'gross_salary' => 'Gross Salary',
    'net_salary' => 'Net Salary',
    'processed_at' => 'Processed at',
    'no_employees_in_batch' => 'No employees found in this batch.',
    
    // Notes
    'notes' => 'Notes',
    
    // Actions in View Modal
    'close' => 'Close',
    'process_batch_button' => 'Process Batch',
    
    // Delete Modal
    'confirm_deletion' => 'Confirm Deletion',
    'action_cannot_be_undone' => 'This action cannot be undone',
    'delete_batch_title' => 'Delete Batch Payroll',
    'delete_batch_message' => 'You are about to delete the batch',
    'employees_will_be_removed' => 'employee(s) will be removed from the batch',
    'period_label' => 'Period',
    'department_label' => 'Department',
    'attention_processing' => 'Attention: This batch is currently being processed!',
    'employees_already_processed' => 'employee(s) have already been processed in this batch.',
    'what_will_happen' => 'What will happen:',
    'batch_removed_permanently' => '• The batch will be removed permanently',
    'employee_records_deleted' => '• Employee records in the batch will be deleted',
    'processed_payrolls_not_affected' => '• Already processed payrolls will NOT be affected',
    'action_logged' => '• This action will appear in system logs',
    'batch_summary' => 'Batch Summary',
    'processed_employees' => 'Processed',
    'created_by' => 'Created by',
    
    // Success/Error Messages
    'batch_created_success' => 'Batch payroll created successfully!',
    'batch_deleted_success' => 'Batch \':name\' deleted successfully!',
    'processing_started_success' => 'Batch processing started. You will be notified when completed.',
    'batch_processed_success' => 'Batch processed successfully!',
    'debug_executed' => 'Debug test executed - check the logs!',
    
    // Error Messages
    'batch_creation_error' => 'Error creating batch: :error',
    'batch_not_found' => 'Batch not found.',
    'cannot_process_batch' => 'Batch cannot be processed at the moment. Status: :status',
    'processing_start_error' => 'Error starting processing: :error',
    'batch_deletion_error' => 'Batch cannot be deleted.',
    'batch_delete_failed' => 'Error deleting batch: :error',
];

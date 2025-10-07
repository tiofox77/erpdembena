<?php

return [
    // Page Title
    'page_title' => 'Payroll Batches',
    'page_description' => 'Manage and process payroll batches',
    
    // Actions
    'create_batch' => 'Create New Batch',
    'view_batch' => 'View Details',
    'edit_batch' => 'Edit Batch',
    'delete_batch' => 'Delete Batch',
    'process_batch' => 'Process Batch',
    'reprocess_batch' => 'Reprocess Batch',
    'export_batch' => 'Export',
    'edit_item' => 'Edit',
    'view_receipt' => 'Receipt',
    
    // Table Headers
    'batch_name' => 'Batch Name',
    'period' => 'Period',
    'department' => 'Department',
    'employees' => 'Employees',
    'total_amount' => 'Total Amount',
    'status' => 'Status',
    'created_at' => 'Created At',
    'actions' => 'Actions',
    
    // Batch Details
    'batch_info' => 'Batch Information',
    'batch_summary' => 'Batch Summary',
    'employees_in_batch' => 'Employees in Batch',
    'total_employees' => 'Total Employees',
    'total_gross_amount' => 'Total Gross',
    'total_net_amount' => 'Total Net',
    'total_deductions' => 'Total Deductions',
    'processed_employees' => 'Processed Employees',
    'batch_date' => 'Batch Date',
    'payment_method' => 'Payment Method',
    'payment_methods' => [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'check' => 'Check',
    ],
    
    // Status
    'status_draft' => 'Draft',
    'status_ready_to_process' => 'Ready to Process',
    'status_processing' => 'Processing',
    'status_completed' => 'Completed',
    'status_failed' => 'Failed',
    'status_cancelled' => 'Cancelled',
    
    // Item Status
    'item_status_pending' => 'Pending',
    'item_status_processing' => 'Processing',
    'item_status_completed' => 'Completed',
    'item_status_failed' => 'Failed',
    'item_status_skipped' => 'Skipped',
    
    // Create Batch Modal
    'create_batch_title' => 'Create New Payroll Batch',
    'create_batch_description' => 'Fill in the details to create a new batch',
    'batch_name_label' => 'Batch Name',
    'batch_name_placeholder' => 'E.g., June 2025 Payroll',
    'batch_description_label' => 'Description',
    'batch_description_placeholder' => 'Optional batch description',
    'payroll_period_label' => 'Payroll Period',
    'payroll_period_placeholder' => 'Select period',
    'department_label' => 'Department (Optional)',
    'department_placeholder' => 'All departments',
    'batch_date_label' => 'Batch Date',
    'payment_method_label' => 'Payment Method',
    'select_employees' => 'Select Employees',
    'all_employees' => 'All Employees',
    'selected_employees' => 'employee(s) selected',
    'no_employees_selected' => 'No employees selected',
    'search_employees' => 'Search employees...',
    'select_all' => 'Select All',
    'deselect_all' => 'Deselect All',
    
    // View Batch Modal
    'batch_details' => 'Batch Details',
    'processing_information' => 'Processing Information',
    'processing_started_at' => 'Processing Started',
    'processing_completed_at' => 'Processing Completed',
    'processing_duration' => 'Duration',
    'duration_minutes' => 'minutes',
    'created_by' => 'Created By',
    'approved_by' => 'Approved By',
    
    // Employee List
    'employee' => 'Employee',
    'gross_salary' => 'Gross Salary',
    'deductions' => 'Deductions',
    'net_salary' => 'Net Salary',
    'processed_at' => 'Processed At',
    'no_employees_in_batch' => 'No employees in this batch',
    
    // Edit Item Modal
    'edit_item_title' => 'Edit Batch Item',
    'employee_information' => 'Employee Information',
    'editable_fields' => 'Editable Fields - Adjust Payment',
    'additional_bonus' => 'Additional Bonus',
    'additional_bonus_placeholder' => '0.00 AOA',
    'additional_bonus_help' => 'Extra bonus for this payment',
    'christmas_subsidy' => 'Christmas Subsidy (50%)',
    'christmas_subsidy_label' => 'Include Christmas Subsidy',
    'christmas_subsidy_help' => '50% of base salary',
    'vacation_subsidy' => 'Vacation Subsidy (50%)',
    'vacation_subsidy_label' => 'Include Vacation Subsidy',
    'vacation_subsidy_help' => '50% of base salary',
    'recalculation_notice' => 'Values will be automatically recalculated when you change the fields above.',
    
    // Employee Details Sections
    'basic_info' => 'Employee Information',
    'basic_salary' => 'Base Salary',
    'hourly_rate' => 'Hourly Rate',
    'daily_rate' => 'Daily Rate',
    'working_days' => 'Working Days',
    
    'attendance_summary' => 'Attendance Summary',
    'hours_worked' => 'Hours Worked',
    'present_days' => 'Present Days',
    'absent_days' => 'Absences',
    'late_arrivals' => 'Late Arrivals',
    
    'overtime_records' => 'Overtime',
    'overtime_amount' => 'Overtime Amount',
    
    'salary_advances' => 'Salary Advances',
    'monthly_deduction' => 'Monthly Deduction',
    'non_taxable_note' => 'Up to 30k non-taxable',
    
    'salary_discounts' => 'Salary Discounts',
    'total_discounts' => 'Total Discounts',
    
    'benefits_allowances' => 'Benefits & Allowances',
    'food_subsidy' => 'Food Benefit',
    'transport_subsidy' => 'Transport Allowance',
    'profile_bonus' => 'Profile Bonus',
    'non_taxable' => 'Non-taxable',
    'proportional' => 'Proportional',
    
    // Payroll Summary
    'payroll_summary' => 'Payroll Summary',
    'base_salary' => 'Base Salary',
    'employee_profile_bonus' => 'Profile Bonus',
    'additional_payroll_bonus' => 'Additional Bonus',
    'gross_salary_label' => 'Gross Salary',
    
    // Deductions Section
    'deductions_section' => 'Deductions',
    'irt_label' => 'IRT (Income Tax)',
    'irt_base' => 'Base',
    'inss_employee' => 'INSS (3% - Employee)',
    'inss_employer' => 'INSS (8% - Employer)',
    'inss_illustrative' => 'Illustrative only - paid by employer',
    'advance_deductions' => 'Salary Advances',
    'discount_deductions' => 'Salary Discounts',
    'absence_deductions' => 'Absence Deduction',
    'late_deductions' => 'Late Arrival Deduction',
    'total_deductions_label' => 'Total Deductions',
    
    // Net Salary
    'final_net_salary' => 'Final Net Salary',
    
    // Notes
    'notes' => 'Notes',
    'notes_placeholder' => 'Add notes about changes (optional)',
    
    // Status Alerts
    'ready_to_process_title' => 'Batch Ready to Process',
    'ready_to_process_message' => 'This batch contains :count employees and is ready to be processed.',
    'ready_to_process_tip' => 'You can edit Pending items before processing.',
    'processing_failed_title' => 'Processing Failed',
    'processing_failed_message' => 'Batch processing encountered errors. Review the details below and click "Reprocess Batch" to try again.',
    'processing_success_title' => 'Processing Completed',
    'processing_success_message' => 'Batch processed successfully. All payroll records have been created.',
    
    // Buttons
    'close' => 'Close',
    'cancel' => 'Cancel',
    'save' => 'Save',
    'save_changes' => 'Save Changes',
    'create' => 'Create',
    'process' => 'Process',
    'confirm' => 'Confirm',
    'back' => 'Back',
    
    // Messages
    'batch_created_success' => 'Batch created successfully!',
    'batch_updated_success' => 'Batch updated successfully!',
    'batch_deleted_success' => 'Batch deleted successfully!',
    'item_updated_success' => 'Batch item updated successfully!',
    'processing_started_success' => 'Batch processing started successfully!',
    'batch_not_found' => 'Batch not found.',
    'cannot_process_batch' => 'Cannot process batch with status: :status',
    'batch_creation_error' => 'Error creating batch: :error',
    'processing_start_error' => 'Error starting processing: :error',
    'item_not_found' => 'Item not found.',
    'item_update_error' => 'Error updating item: :error',
    
    // Validation Messages
    'batch_name_required_msg' => 'Batch name is required.',
    'payroll_period_required_msg' => 'Payroll period is required.',
    'batch_date_required_msg' => 'Batch date is required.',
    'select_employees_msg' => 'Please select at least one employee.',
    'min_employees_msg' => 'Select at least one employee to create the batch.',
    
    // Delete Confirmation
    'delete_confirmation_title' => 'Confirm Deletion',
    'delete_confirmation_message' => 'Are you sure you want to delete the batch ":name"?',
    'delete_warning' => 'This action cannot be undone. All batch items will be removed.',
    
    // Empty States
    'no_batches_found' => 'No payroll batches found.',
    'create_first_batch' => 'Create your first payroll batch.',
    
    // Filter Labels
    'filter_by_status' => 'Filter by Status',
    'filter_by_department' => 'Filter by Department',
    'filter_by_period' => 'Filter by Period',
    'all_statuses' => 'All Statuses',
    'all_departments' => 'All Departments',
    'all_periods' => 'All Periods',
    
    // Currency
    'currency' => 'AOA',
];

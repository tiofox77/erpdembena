<?php

return [
    // Page Headers
    'payroll_management' => 'Payroll Management',
    'process_payroll' => 'Process Payroll',
    
    // Form Fields and Labels
    'search' => 'Search',
    'search_payroll' => 'Search payroll records...',
    'show' => 'Show',
    'id' => 'ID',
    'employee' => 'Employee',
    'period' => 'Pay Period',
    'salary' => 'Base Salary',
    'gross_pay' => 'Gross Pay',
    'deductions' => 'Deductions',
    'net_pay' => 'Net Pay',
    'payment_date' => 'Payment Date',
    'payment_method' => 'Payment Method',
    'status' => 'Status',
    'actions' => 'Actions',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'created_by' => 'Created By',
    'approved_by' => 'Approved By',
    'filter_period' => 'Filter by Period',
    'filter_employee' => 'Filter by Employee',
    'filter_status' => 'Filter by Status',
    
    // Table Status Items
    'draft' => 'Draft',
    'pending' => 'Pending',
    'approved' => 'Approved',
    'paid' => 'Paid',
    'rejected' => 'Rejected',
    
    // Payment Methods
    'bank_transfer' => 'Bank Transfer',
    'check' => 'Check',
    'cash' => 'Cash',
    'other' => 'Other',
    
    // Modal Titles
    'process_new_payroll' => 'Process New Payroll',
    'edit_payroll' => 'Edit Payroll',
    'view_payroll' => 'View Payroll Details',
    'approve_payroll' => 'Approve Payroll',
    'reject_payroll' => 'Reject Payroll',
    'confirm_deletion' => 'Confirm Deletion',
    
    // Tabs
    'basic_info' => 'Basic Information',
    'earnings' => 'Earnings',
    'deductions_tab' => 'Deductions',
    'summary' => 'Summary',
    
    // Form Fields
    'select_employee' => 'Select Employee',
    'select_period' => 'Select Pay Period',
    'select_payment_method' => 'Select Payment Method',
    'select_payment_date' => 'Select Payment Date',
    'base_salary' => 'Base Salary',
    'hourly_rate' => 'Hourly Rate',
    'worked_hours' => 'Worked Hours',
    'overtime_hours' => 'Overtime Hours',
    'bonuses' => 'Bonuses',
    'allowances' => 'Allowances',
    'tax' => 'Tax',
    'insurance' => 'Insurance',
    'retirement' => 'Retirement',
    'other_deductions' => 'Other Deductions',
    'notes' => 'Notes',
    
    // Form Validation Messages
    'employee_required' => 'Employee is required',
    'period_required' => 'Pay period is required',
    'payment_date_required' => 'Payment date is required',
    'payment_method_required' => 'Payment method is required',
    
    // Button Labels
    'save' => 'Save',
    'save_as_draft' => 'Save as Draft',
    'submit' => 'Submit for Approval',
    'update' => 'Update',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'approve' => 'Approve',
    'reject' => 'Reject',
    'close' => 'Close',
    'edit' => 'Edit',
    'print' => 'Print',
    'generate_payslip' => 'Generate Payslip',
    'bulk_process' => 'Bulk Process',
    
    // Confirmation Messages
    'delete_payroll_confirmation' => 'Are you sure you want to delete this payroll record? This action cannot be undone.',
    'approve_payroll_confirmation' => 'Are you sure you want to approve this payroll record?',
    'reject_payroll_confirmation' => 'Are you sure you want to reject this payroll record?',
    
    // Notifications
    'payroll_created' => 'Payroll record created successfully',
    'payroll_updated' => 'Payroll record updated successfully',
    'payroll_deleted' => 'Payroll record deleted successfully',
    'payroll_approved' => 'Payroll record approved successfully',
    'payroll_rejected' => 'Payroll record rejected successfully',
    'payslip_generated' => 'Payslip generated successfully',
    'bulk_payroll_processed' => 'Bulk payroll processed successfully',
    'error_occurred' => 'An error occurred',
    
    // Empty States
    'no_payroll_found' => 'No payroll records found',
    'create_first_payroll' => 'Process your first payroll',
    
    // Earnings and Deductions
    'earnings_type' => 'Earnings Type',
    'earnings_amount' => 'Amount',
    'deduction_type' => 'Deduction Type',
    'deduction_amount' => 'Amount',
    'add_earning' => 'Add Earning',
    'add_deduction' => 'Add Deduction',
    'total_earnings' => 'Total Earnings',
    'total_deductions' => 'Total Deductions',
    
    // View Modal Specific Keys
    'detailed_breakdown_components' => 'Detailed Component Breakdown',
    'taxable' => 'Taxable',
    'exempt' => 'Exempt',
    'currency' => 'AOA',
    'final_summary' => 'Final Summary',
    'gross_total' => 'Gross Total',
    'net_salary' => 'Net Salary',
    'observations' => 'Observations',
    'generated_on' => 'Generated on',
    'download_payslip' => 'Download Payslip',
    'deductions' => 'Deductions',
    
    // Detailed Payroll Components
    'basic_salary_description' => 'Monthly basic salary of the employee',
    'transport_allowance' => 'Transport Allowance',
    'transport_allowance_description' => 'Monthly transport allowance',
    'meal_allowance' => 'Meal Allowance',
    'meal_allowance_description' => 'Monthly meal allowance',
    'overtime_hours' => 'Overtime Hours',
    'overtime_payment_description' => 'Payment for :hours overtime hours',
    'performance_bonus_description' => 'Performance and other bonuses',
    'christmas_subsidy_description' => 'Christmas Subsidy (13th salary)',
    'vacation_subsidy_description' => 'Vacation Subsidy (14th salary)',
    
    // Payment Types
    'earning' => 'Earning',
    'allowance' => 'Allowance',
    'bonus' => 'Bonus',
    'deduction' => 'Deduction',
    'tax' => 'Tax',
    
    // Deductions
    'late_deduction' => 'Late Penalty',
    'late_deduction_description' => 'Deduction for :days late days',
    'absence_deduction' => 'Absence Penalty',
    'absence_deduction_description' => 'Deduction for :days absent days',
    'social_security' => 'Social Security',
    'social_security_description' => 'Social Security Contribution (3%)',
    'income_tax' => 'Income Tax',
    'income_tax_description' => 'Income Tax on Labor',
    'salary_discounts' => 'Salary Discounts',
    'salary_discounts_description' => 'Various discounts applied to salary',
    'salary_advances' => 'Salary Advances',
    'salary_advances_description' => 'Deduction for granted salary advances',
    'included_in_main_salary' => 'included in main salary',
];

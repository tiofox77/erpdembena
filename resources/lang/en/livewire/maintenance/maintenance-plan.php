<?php

return [
    // Page Headers
    'maintenance_plan_management' => 'Maintenance Plan Management',
    'add_plan' => 'Add Plan',
    
    // Form Fields and Labels
    'search' => 'Search',
    'search_plans' => 'Search plans...',
    'show' => 'Show',
    'id' => 'ID',
    'name' => 'Name',
    'description' => 'Description',
    'equipment' => 'Equipment',
    'frequency' => 'Frequency',
    'next_due' => 'Next Due',
    'last_performed' => 'Last Performed',
    'status' => 'Status',
    'assigned_to' => 'Assigned To',
    'created_by' => 'Created By',
    'estimated_hours' => 'Estimated Hours',
    'actions' => 'Actions',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'category' => 'Category',
    'start_date' => 'Start Date',
    'end_date' => 'End Date',
    'notes' => 'Notes',
    'filter_category' => 'Filter by Category',
    'filter_status' => 'Filter by Status',
    'filter_equipment' => 'Filter by Equipment',
    'filter_assigned' => 'Filter by Assignment',
    
    // Plan Status
    'active' => 'Active',
    'inactive' => 'Inactive',
    'completed' => 'Completed',
    'overdue' => 'Overdue',
    
    // Frequency Types
    'daily' => 'Daily',
    'weekly' => 'Weekly',
    'monthly' => 'Monthly',
    'quarterly' => 'Quarterly',
    'biannually' => 'Biannually',
    'annually' => 'Annually',
    'custom' => 'Custom',
    'hours_operation' => 'Hours of Operation',
    
    // Modal Titles
    'add_new_plan' => 'Add New Maintenance Plan',
    'edit_plan' => 'Edit Maintenance Plan',
    'view_plan' => 'View Plan Details',
    'confirm_deletion' => 'Confirm Deletion',
    'generate_tasks' => 'Generate Tasks',
    'add_task_to_plan' => 'Add Task to Plan',
    
    // Form Fields
    'plan_name' => 'Plan Name',
    'plan_description' => 'Plan Description',
    'select_equipment' => 'Select Equipment',
    'select_category' => 'Select Category',
    'select_frequency' => 'Select Frequency',
    'select_start_date' => 'Select Start Date',
    'select_end_date' => 'Select End Date',
    'select_technician' => 'Select Technician',
    'custom_days' => 'Custom Days',
    'operation_hours' => 'Operation Hours',
    'task_description' => 'Task Description',
    'procedures' => 'Procedures',
    'safety_instructions' => 'Safety Instructions',
    'required_tools' => 'Required Tools',
    'required_parts' => 'Required Parts',
    
    // Form Validation Messages
    'name_required' => 'Plan name is required',
    'name_max' => 'Plan name cannot exceed 100 characters',
    'equipment_required' => 'Equipment is required',
    'frequency_required' => 'Frequency is required',
    'start_date_required' => 'Start date is required',
    
    // Button Labels
    'save' => 'Save',
    'create' => 'Create',
    'update' => 'Update',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'close' => 'Close',
    'edit' => 'Edit',
    'view' => 'View',
    'generate' => 'Generate Tasks',
    'add_task' => 'Add Task',
    'print' => 'Print',
    'export' => 'Export',
    'generate_schedule' => 'Generate Schedule',
    
    // Confirmation Messages
    'delete_plan_confirmation' => 'Are you sure you want to delete this maintenance plan? This action cannot be undone.',
    'plan_has_tasks' => 'Cannot delete plan with associated tasks',
    'generate_tasks_confirmation' => 'Are you sure you want to generate tasks for this plan?',
    
    // Notifications
    'plan_created' => 'Maintenance plan created successfully',
    'plan_updated' => 'Maintenance plan updated successfully',
    'plan_deleted' => 'Maintenance plan deleted successfully',
    'tasks_generated' => 'Maintenance tasks generated successfully',
    'task_added' => 'Task added to plan successfully',
    'error_occurred' => 'An error occurred',
    
    // Empty States
    'no_plans_found' => 'No maintenance plans found',
    'create_first_plan' => 'Create your first plan',
    'no_tasks_found' => 'No tasks associated with this plan',
    
    // Schedule
    'maintenance_schedule' => 'Maintenance Schedule',
    'upcoming_maintenance' => 'Upcoming Maintenance',
    'overdue_maintenance' => 'Overdue Maintenance',
    'today' => 'Today',
    'this_week' => 'This Week',
    'this_month' => 'This Month',
    'calendar_view' => 'Calendar View',
    'list_view' => 'List View',
];

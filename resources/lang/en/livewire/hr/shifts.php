<?php

return [
    // PDF notifications
    'pdf_generating' => 'The PDF is being prepared for download...',
    'pdf_list_generating' => 'The List PDF is being prepared for download...',
    'pdf_error' => 'Error generating PDF: ',
    'pdf_list_error' => 'Error generating list PDF: ',
    'assignments_pdf_generating' => 'The assignments PDF is being prepared for download...',
    'assignments_pdf_error' => 'Error generating assignments PDF: ',
    
    // CRUD notifications
    'created_success' => 'Shift created successfully',
    'updated_success' => 'Shift updated successfully',
    'deleted_success' => 'Shift deleted successfully',
    'created_error' => 'Error creating shift: ',
    'updated_error' => 'Error updating shift: ',
    'deleted_error' => 'Error deleting shift: ',
    
    // Assignment notifications
    'assignment_created_success' => 'Shift assignment created successfully',
    'assignment_updated_success' => 'Shift assignment updated successfully',
    'assignment_deleted_success' => 'Shift assignment deleted successfully',
    'assignment_created_error' => 'Error creating shift assignment: ',
    'assignment_updated_error' => 'Error updating shift assignment: ',
    'assignment_deleted_error' => 'Error deleting shift assignment: ',
    
    // Page Headers
    'shifts_management' => 'Shifts Management',
    'add_shift' => 'Add Shift',
    
    // Form Fields and Labels
    'search' => 'Search',
    'search_shifts' => 'Search shifts...',
    'show' => 'Show',
    'id' => 'ID',
    'name' => 'Name',
    'start_time' => 'Start Time',
    'end_time' => 'End Time',
    'hours' => 'Hours',
    'break_time' => 'Break Time',
    'color' => 'Color',
    'status' => 'Status',
    'actions' => 'Actions',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'description' => 'Description',
    'days' => 'Working Days',
    
    // Days of Week
    'monday' => 'Monday',
    'tuesday' => 'Tuesday',
    'wednesday' => 'Wednesday',
    'thursday' => 'Thursday',
    'friday' => 'Friday',
    'saturday' => 'Saturday',
    'sunday' => 'Sunday',
    
    // Table Status Items
    'active' => 'Active',
    'inactive' => 'Inactive',
    
    // Modal Titles
    'add_new_shift' => 'Add New Shift',
    'edit_shift' => 'Edit Shift',
    'view_shift' => 'View Shift Details',
    'confirm_deletion' => 'Confirm Deletion',
    
    // Form Fields
    'shift_name' => 'Shift Name',
    'shift_description' => 'Shift Description',
    'select_start_time' => 'Select Start Time',
    'select_end_time' => 'Select End Time',
    'break_minutes' => 'Break (minutes)',
    'select_color' => 'Select Color',
    'select_days' => 'Select Working Days',
    'is_active' => 'Is Active',
    'is_night_shift' => 'Is Night Shift',
    
    // Form Validation Messages
    'name_required' => 'Shift name is required',
    'name_max' => 'Shift name cannot exceed 100 characters',
    'name_unique' => 'Shift name already exists',
    'start_time_required' => 'Start time is required',
    'end_time_required' => 'End time is required',
    'break_time_numeric' => 'Break time must be a number',
    'break_time_min' => 'Break time must be at least 0',
    'color_required' => 'Color is required',
    
    // Button Labels
    'save' => 'Save',
    'create' => 'Create',
    'update' => 'Update',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'close' => 'Close',
    'edit' => 'Edit',
    
    // Confirmation Messages
    'delete_shift_confirmation' => 'Are you sure you want to delete this shift? This action cannot be undone.',
    'shift_in_use' => 'Cannot delete shift that is assigned to employees',
    
    // Notifications
    'shift_created' => 'Shift created successfully',
    'shift_updated' => 'Shift updated successfully',
    'shift_deleted' => 'Shift deleted successfully',
    'error_occurred' => 'An error occurred',
    
    // Empty States
    'no_shifts_found' => 'No shifts found',
    'create_first_shift' => 'Create your first shift',
];

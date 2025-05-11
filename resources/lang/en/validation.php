<?php

return [
    // Resource validation messages
    'resource_name_required' => 'The resource name field is required.',
    'resource_name_max' => 'The resource name must not be longer than 255 characters.',
    'resource_type_required' => 'The resource type field is required.',
    'resource_type_exists' => 'The selected resource type does not exist.',
    'department_exists' => 'The selected department does not exist.',
    'location_exists' => 'The selected location does not exist.',
    'capacity_required' => 'The capacity field is required.',
    'capacity_numeric' => 'The capacity must be a number.',
    'capacity_min' => 'The capacity must be at least 0.',
    'capacity_uom_required' => 'The capacity unit of measure field is required.',
    'capacity_uom_in' => 'The selected capacity unit of measure is invalid.',
    'efficiency_factor_required' => 'The efficiency factor field is required.',
    'efficiency_factor_numeric' => 'The efficiency factor must be a number.',
    'efficiency_factor_min' => 'The efficiency factor must be at least 1.',
    'efficiency_factor_max' => 'The efficiency factor may not be greater than 200.',
];

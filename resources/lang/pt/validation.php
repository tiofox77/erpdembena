<?php

return [
    // Resource validation messages
    'resource_name_required' => 'O campo nome do recurso é obrigatório.',
    'resource_name_max' => 'O nome do recurso não pode ter mais de 255 caracteres.',
    'resource_type_required' => 'O campo tipo de recurso é obrigatório.',
    'resource_type_exists' => 'O tipo de recurso selecionado não existe.',
    'department_exists' => 'O departamento selecionado não existe.',
    'location_exists' => 'A localização selecionada não existe.',
    'capacity_required' => 'O campo capacidade é obrigatório.',
    'capacity_numeric' => 'A capacidade deve ser um número.',
    'capacity_min' => 'A capacidade deve ser pelo menos 0.',
    'capacity_uom_required' => 'O campo unidade de medida da capacidade é obrigatório.',
    'capacity_uom_in' => 'A unidade de medida da capacidade selecionada é inválida.',
    'efficiency_factor_required' => 'O campo fator de eficiência é obrigatório.',
    'efficiency_factor_numeric' => 'O fator de eficiência deve ser um número.',
    'efficiency_factor_min' => 'O fator de eficiência deve ser pelo menos 1.',
    'efficiency_factor_max' => 'O fator de eficiência não pode ser maior que 200.',
];

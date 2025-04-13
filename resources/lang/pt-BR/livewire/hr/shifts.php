<?php

return [
    // Page Headers
    'shifts_management' => 'Gerenciamento de Turnos',
    'add_shift' => 'Adicionar Turno',
    
    // Form Fields and Labels
    'search' => 'Pesquisar',
    'search_shifts' => 'Pesquisar turnos...',
    'show' => 'Mostrar',
    'id' => 'ID',
    'name' => 'Nome',
    'start_time' => 'Hora de Início',
    'end_time' => 'Hora de Término',
    'hours' => 'Horas',
    'break_time' => 'Tempo de Intervalo',
    'color' => 'Cor',
    'status' => 'Status',
    'actions' => 'Ações',
    'created_at' => 'Criado Em',
    'updated_at' => 'Atualizado Em',
    'description' => 'Descrição',
    'days' => 'Dias de Trabalho',
    
    // Days of Week
    'monday' => 'Segunda-feira',
    'tuesday' => 'Terça-feira',
    'wednesday' => 'Quarta-feira',
    'thursday' => 'Quinta-feira',
    'friday' => 'Sexta-feira',
    'saturday' => 'Sábado',
    'sunday' => 'Domingo',
    
    // Table Status Items
    'active' => 'Ativo',
    'inactive' => 'Inativo',
    
    // Modal Titles
    'add_new_shift' => 'Adicionar Novo Turno',
    'edit_shift' => 'Editar Turno',
    'view_shift' => 'Visualizar Detalhes do Turno',
    'confirm_deletion' => 'Confirmar Exclusão',
    
    // Form Fields
    'shift_name' => 'Nome do Turno',
    'shift_description' => 'Descrição do Turno',
    'select_start_time' => 'Selecionar Hora de Início',
    'select_end_time' => 'Selecionar Hora de Término',
    'break_minutes' => 'Intervalo (minutos)',
    'select_color' => 'Selecionar Cor',
    'select_days' => 'Selecionar Dias de Trabalho',
    'is_active' => 'Está Ativo',
    'is_night_shift' => 'É Turno Noturno',
    
    // Form Validation Messages
    'name_required' => 'O nome do turno é obrigatório',
    'name_max' => 'O nome do turno não pode exceder 100 caracteres',
    'name_unique' => 'Este nome de turno já existe',
    'start_time_required' => 'A hora de início é obrigatória',
    'end_time_required' => 'A hora de término é obrigatória',
    'break_time_numeric' => 'O tempo de intervalo deve ser um número',
    'break_time_min' => 'O tempo de intervalo deve ser pelo menos 0',
    'color_required' => 'A cor é obrigatória',
    
    // Button Labels
    'save' => 'Salvar',
    'create' => 'Criar',
    'update' => 'Atualizar',
    'cancel' => 'Cancelar',
    'delete' => 'Excluir',
    'close' => 'Fechar',
    'edit' => 'Editar',
    
    // Confirmation Messages
    'delete_shift_confirmation' => 'Tem certeza que deseja excluir este turno? Esta ação não pode ser desfeita.',
    'shift_in_use' => 'Não é possível excluir turno que está atribuído a funcionários',
    
    // Notifications
    'shift_created' => 'Turno criado com sucesso',
    'shift_updated' => 'Turno atualizado com sucesso',
    'shift_deleted' => 'Turno excluído com sucesso',
    'error_occurred' => 'Ocorreu um erro',
    
    // Empty States
    'no_shifts_found' => 'Nenhum turno encontrado',
    'create_first_shift' => 'Crie seu primeiro turno',
];

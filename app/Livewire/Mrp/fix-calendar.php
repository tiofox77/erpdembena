<?php

/**
 * Método para testar se o calendário está carregando eventos
 */
public function reloadCalendar()
{
    // Forçar recarregamento de eventos do calendário
    \Illuminate\Support\Facades\Log::info('Forçando recarregamento de eventos do calendário');
    $this->loadCalendarEvents();
    
    // Notificar usuário
    $this->dispatch('notify',
        type: 'success',
        title: 'Calendário atualizado',
        message: 'Os eventos do calendário foram recarregados.'
    );
}

/**
 * Método que será chamado quando um evento é escutado
 * Adicione este listener no método mount() ou boot() da sua classe Livewire
 */
public function calendarUpdated()
{
    $this->loadCalendarEvents();
}

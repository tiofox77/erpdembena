<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\Attributes\On;

class ModalComponent extends Component
{
    // Propriedades que podem ser passadas para o modal
    public $isOpen = false;
    public $modalId = '';
    public $title = '';
    public $modalSize = 'md';  // sm, md, lg, xl
    public $showCloseButton = true;

    // No Livewire 3, usamos o sistema de eventos baseado em dispatch/on
    // em vez do antigo listeners
    public function mount($modalId = '')
    {
        $this->modalId = $modalId;
    }

    // Método para abrir o modal
    #[On('openModal')]
    public function open($params = [])
    {
        // Só abrir este modal específico se o modalId corresponder
        if (!isset($params['modalId']) || $this->modalId === $params['modalId']) {
            $this->isOpen = true;

            if (isset($params['title'])) {
                $this->title = $params['title'];
            }

            if (isset($params['modalSize'])) {
                $this->modalSize = $params['modalSize'];
            }

            if (isset($params['showCloseButton'])) {
                $this->showCloseButton = $params['showCloseButton'];
            }
        }
    }

    // Método para fechar o modal
    #[On('closeModal')]
    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.components.modal-component');
    }
}

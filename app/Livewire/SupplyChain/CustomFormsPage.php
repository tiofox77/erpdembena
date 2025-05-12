<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;

class CustomFormsPage extends Component
{
    public function render()
    {
        return view('livewire.supply-chain.custom-forms-page')
            ->layout('layouts.livewire');
    }
}

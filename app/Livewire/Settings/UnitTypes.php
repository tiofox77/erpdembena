<?php

namespace App\Livewire\Settings;

use App\Models\UnitType;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class UnitTypes extends Component
{
    use WithPagination;

    public $name;
    public $symbol;
    public $description;
    public $category;
    public $is_active = true;
    public $unit_type_id;

    public $isOpen = false;
    public $isDeleteModalOpen = false;
    public $deleteId;
    public $searchTerm = '';
    public $filter = 'all';

    public $categories = [
        'quantity' => 'Quantity',
        'length' => 'Length',
        'mass' => 'Mass/Weight',
        'volume' => 'Volume',
        'area' => 'Area',
        'time' => 'Time',
        'electrical' => 'Electrical',
        'temperature' => 'Temperature',
        'pressure' => 'Pressure',
        'other' => 'Other'
    ];

    protected $rules = [
        'name' => 'required|string|max:50',
        'symbol' => 'required|string|max:10',
        'description' => 'nullable|string',
        'category' => 'required|string',
        'is_active' => 'boolean'
    ];

    public function render()
    {
        return view('livewire.settings.unit-types', [
            'unitTypes' => $this->getUnitTypes(),
        ])->layout('layouts.livewire', ['title' => trans('messages.unit_types')]);
    }

    public function getUnitTypes()
    {
        $query = UnitType::query();
        
        if ($this->searchTerm) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('symbol', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        if ($this->filter !== 'all') {
            if ($this->filter === 'active') {
                $query->where('is_active', true);
            } elseif ($this->filter === 'inactive') {
                $query->where('is_active', false);
            } else {
                $query->where('category', $this->filter);
            }
        }
        
        return $query->orderBy('name', 'asc')->paginate(10);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->symbol = '';
        $this->description = '';
        $this->category = 'quantity';
        $this->is_active = true;
        $this->unit_type_id = null;
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();
        
        try {
            UnitType::updateOrCreate(
                ['id' => $this->unit_type_id],
                [
                    'name' => $this->name,
                    'symbol' => $this->symbol,
                    'description' => $this->description,
                    'category' => $this->category,
                    'is_active' => $this->is_active
                ]
            );
            
            $this->closeModal();
            $this->resetInputFields();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => trans('messages.success'),
                'message' => $this->unit_type_id 
                    ? trans('messages.unit_type_updated') 
                    : trans('messages.unit_type_created')
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating/updating unit type: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => trans('messages.error'),
                'message' => trans('messages.operation_failed')
            ]);
        }
    }

    public function edit($id)
    {
        try {
            $unitType = UnitType::findOrFail($id);
            $this->unit_type_id = $id;
            $this->name = $unitType->name;
            $this->symbol = $unitType->symbol;
            $this->description = $unitType->description;
            $this->category = $unitType->category;
            $this->is_active = $unitType->is_active;
            
            $this->openModal();
        } catch (\Exception $e) {
            Log::error('Error editing unit type: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => trans('messages.error'),
                'message' => trans('messages.operation_failed')
            ]);
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->isDeleteModalOpen = true;
    }

    public function closeDeleteModal()
    {
        $this->isDeleteModalOpen = false;
    }

    public function delete()
    {
        try {
            UnitType::find($this->deleteId)->delete();
            $this->closeDeleteModal();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => trans('messages.success'),
                'message' => trans('messages.unit_type_deleted')
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting unit type: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => trans('messages.error'),
                'message' => trans('messages.operation_failed')
            ]);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $unitType = UnitType::findOrFail($id);
            $unitType->is_active = !$unitType->is_active;
            $unitType->save();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => trans('messages.success'),
                'message' => $unitType->is_active 
                    ? trans('messages.unit_type_activated') 
                    : trans('messages.unit_type_deactivated')
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling unit type status: ' . $e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => trans('messages.error'),
                'message' => trans('messages.operation_failed')
            ]);
        }
    }
}

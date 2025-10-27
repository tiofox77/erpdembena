<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\InventoryLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryLocations extends Component
{
    use WithPagination;

    public $location_id;
    public $location = [
        'name' => '',
        'location_code' => '',
        'description' => '',
        'address' => '',
        'city' => '',
        'postal_code' => '',
        'phone' => '',
        'contact_person' => '',
        'notes' => '',
        'is_active' => true,
        'is_raw_material_warehouse' => false,
    ];
    public $is_active = true;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $statusFilter = ''; // Filtro de status (ativo/inativo)
    public $typeFilter = ''; // Filtro de tipo (raw_material_warehouse)
    public $showModal = false;
    public $showConfirmDelete = false;
    public $showViewModal = false;
    public $itemToDelete = null;
    public $deleteLocationName = null;
    public $deleteHasItems = false;
    public $editMode = false;
    public $viewLocation = null;

    protected $paginationTheme = 'tailwind';

    protected $listeners = ['refresh' => '$refresh', 'closeModal' => 'closeModal', 'closeViewModal' => 'closeViewModal'];

    protected function rules()
    {
        return [
            'location.name' => 'required|string|max:255',
            'location.location_code' => ['required', 'string', 'max:50', 
                Rule::unique('sc_inventory_locations', 'code')->ignore($this->location_id)],
            'location.description' => 'nullable|string',
            'location.address' => 'nullable|string|max:255',
            'location.city' => 'nullable|string|max:100',
            'location.postal_code' => 'nullable|string|max:20',
            'location.phone' => 'nullable|string|max:50',
            'location.contact_person' => 'nullable|string|max:100',
            'location.is_active' => 'boolean',
            'location.is_raw_material_warehouse' => 'boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function openAddModal()
    {
        $this->create();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->generateLocationCode();
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->location_id = $id;
        $locationModel = InventoryLocation::findOrFail($id);
        
        $this->location['name'] = $locationModel->name;
        $this->location['location_code'] = $locationModel->code;
        $this->location['description'] = $locationModel->description;
        $this->location['address'] = $locationModel->address;
        $this->location['city'] = $locationModel->city;
        $this->location['postal_code'] = $locationModel->postal_code;
        $this->location['phone'] = $locationModel->phone;
        $this->location['contact_person'] = $locationModel->manager;
        $this->location['is_active'] = $locationModel->is_active;
        $this->location['is_raw_material_warehouse'] = $locationModel->is_raw_material_warehouse;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        try {
            $locationModel = $this->location_id ? 
                InventoryLocation::findOrFail($this->location_id) : 
                new InventoryLocation();
            
            $locationModel->name = $this->location['name'];
            $locationModel->code = $this->location['location_code'];
            $locationModel->description = $this->location['description'] ?? null;
            $locationModel->address = $this->location['address'] ?? null;
            $locationModel->city = $this->location['city'] ?? null;
            $locationModel->postal_code = $this->location['postal_code'] ?? null;
            $locationModel->phone = $this->location['phone'] ?? null;
            $locationModel->manager = $this->location['contact_person'] ?? null;
            $locationModel->is_active = $this->location['is_active'] ?? true;
            $locationModel->is_raw_material_warehouse = $this->location['is_raw_material_warehouse'] ?? false;
            
            $locationModel->save();
            
            DB::commit();
            
            $this->resetForm();
            $this->showModal = false;
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('livewire/supply-chain/locations.success'), 
                message: $this->location_id ? 
                    __('livewire/supply-chain/locations.location_updated') : 
                    __('livewire/supply-chain/locations.location_created')
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', 
                type: 'error', 
                title: __('livewire/supply-chain/locations.error'), 
                message: $e->getMessage()
            );
        }
    }

    public function view($id)
    {
        $this->viewLocation = InventoryLocation::findOrFail($id);
        $this->showViewModal = true;
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewLocation = null;
    }

    public function confirmDelete($id)
    {
        $location = InventoryLocation::findOrFail($id);
        $this->deleteLocationName = $location->name;
        $this->itemToDelete = $id;
        $this->deleteHasItems = ($location->inventoryItems()->count() > 0 || $location->goodsReceipts()->count() > 0);
        $this->showConfirmDelete = true;
    }

    public function delete()
    {
        try {
            $location = InventoryLocation::findOrFail($this->itemToDelete);
            
            // Check if location has inventory items
            if ($location->inventoryItems()->count() > 0) {
                throw new \Exception(__('livewire/supply-chain/locations.cannot_delete_with_inventory_items'));
            }
            
            // Check if location has goods receipts
            if ($location->goodsReceipts()->count() > 0) {
                throw new \Exception(__('livewire/supply-chain/locations.cannot_delete_with_goods_receipts'));
            }
            
            $location->delete();
            
            $this->dispatch('notify', 
                type: 'success', 
                title: __('livewire/supply-chain/locations.success'), 
                message: __('livewire/supply-chain/locations.location_deleted')
            );
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                title: __('livewire/supply-chain/locations.error'), 
                message: $e->getMessage()
            );
        }
        
        $this->showConfirmDelete = false;
        $this->itemToDelete = null;
        $this->deleteLocationName = null;
        $this->deleteHasItems = false;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'sortField', 'sortDirection', 'statusFilter', 'typeFilter']);
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['location_id', 'editMode']);
        
        $this->location = [
            'name' => '',
            'location_code' => '',
            'description' => '',
            'address' => '',
            'city' => '',
            'postal_code' => '',
            'phone' => '',
            'contact_person' => '',
            'notes' => '',
            'is_active' => true,
            'is_raw_material_warehouse' => false,
        ];
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function cancelDelete()
    {
        $this->showConfirmDelete = false;
        $this->itemToDelete = null;
    }

    protected function generateLocationCode()
    {
        $prefix = 'LOC';
        $lastLocation = InventoryLocation::orderBy('id', 'desc')->first();
        
        if ($lastLocation) {
            $lastCode = $lastLocation->code;
            if (strpos($lastCode, $prefix) === 0) {
                $number = intval(substr($lastCode, strlen($prefix)));
                $newNumber = $number + 1;
            } else {
                $newNumber = 1;
            }
        } else {
            $newNumber = 1;
        }
        
        $this->code = $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function openEditModal($id)
    {
        $this->edit($id);
        $this->editMode = true;
    }

    public function render()
    {
        $query = InventoryLocation::query();
        
        // Aplicar filtro de busca
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%')
                  ->orWhere('manager', 'like', '%' . $this->search . '%');
            });
        }
        
        // Aplicar filtro de status
        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter == 'active');
        }
        
        // Aplicar filtro de tipo
        if ($this->typeFilter !== '') {
            if ($this->typeFilter == 'raw_material') {
                $query->where('is_raw_material_warehouse', true);
            } else if ($this->typeFilter == 'normal') {
                $query->where('is_raw_material_warehouse', false);
            }
        }
        
        $locations = $query->withCount('inventoryItems')
                           ->orderBy($this->sortField, $this->sortDirection)
                           ->paginate($this->perPage);
        
        return view('livewire.supply-chain.inventory-locations', [
            'locations' => $locations
        ]);
    }
}

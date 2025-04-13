<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Suppliers extends Component
{
    use WithPagination;

    public $supplier_id;
    public $name;
    public $code;
    public $contact_person;
    public $email;
    public $phone;
    public $address;
    public $city;
    public $state;
    public $country;
    public $postal_code;
    public $notes;
    public $status = 'active';
    public $tax_id;
    public $website;
    public $payment_terms = 30;
    public $credit_limit;
    public $bank_name;
    public $bank_account;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $showConfirmDelete = false;
    public $itemToDelete = null;
    public $editMode = false;
    public $viewingSupplier = null;
    public $viewSupplier = null;
    public $deleteSupplierName = null;
    public $deleteSupplier = null;

    protected $paginationTheme = 'tailwind';

    protected $listeners = ['refresh' => '$refresh'];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', 
                Rule::unique('sc_suppliers', 'code')->ignore($this->supplier_id)],
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'tax_id' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'credit_limit' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
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
        $this->generateSupplierCode();
        
        // Emitir evento para abrir o modal
        $this->dispatch('showModal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->supplier_id = $id;
        $supplier = Supplier::findOrFail($id);
        
        $this->name = $supplier->name;
        $this->code = $supplier->code;
        $this->contact_person = $supplier->contact_person;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->city = $supplier->city;
        $this->state = $supplier->state;
        $this->country = $supplier->country;
        $this->postal_code = $supplier->postal_code;
        $this->notes = $supplier->notes;
        $this->status = $supplier->status;
        $this->tax_id = $supplier->tax_id;
        $this->website = $supplier->website;
        $this->payment_terms = $supplier->payment_terms;
        $this->credit_limit = $supplier->credit_limit;
        $this->bank_name = $supplier->bank_name;
        $this->bank_account = $supplier->bank_account;
        
        $this->showModal = true;
        
        // Emitir evento para abrir o modal
        $this->dispatch('showModal');
    }

    public function view($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $this->viewingSupplier = [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'supplier_code' => $supplier->code, // Atualizado para supplier_code para compatibilidade com o novo modal
            'contact_person' => $supplier->contact_person,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'address' => $supplier->address,
            'city' => $supplier->city,
            'state' => $supplier->state,
            'country' => $supplier->country,
            'postal_code' => $supplier->postal_code,
            'notes' => $supplier->notes,
            'is_active' => $supplier->status === 'active',
            'tax_id' => $supplier->tax_id,
            'website' => $supplier->website,
            'payment_terms' => $supplier->payment_terms,
            'credit_limit' => $supplier->credit_limit,
            'bank_name' => $supplier->bank_name,
            'bank_account' => $supplier->bank_account,
            'position' => $supplier->position,
        ];
        
        // Configurando viewSupplier como objeto para compatibilidade com o novo modal
        $this->viewSupplier = (object) $this->viewingSupplier;
        $this->showViewModal = true;
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingSupplier = null;
        $this->viewSupplier = null;
    }

    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        try {
            $supplier = $this->supplier_id ? 
                Supplier::findOrFail($this->supplier_id) : 
                new Supplier();
            
            $supplier->name = $this->name;
            $supplier->code = $this->code;
            $supplier->contact_person = $this->contact_person;
            $supplier->email = $this->email;
            $supplier->phone = $this->phone;
            $supplier->address = $this->address;
            $supplier->city = $this->city;
            $supplier->state = $this->state;
            $supplier->country = $this->country;
            $supplier->postal_code = $this->postal_code;
            $supplier->notes = $this->notes;
            $supplier->status = $this->status;
            $supplier->tax_id = $this->tax_id;
            $supplier->website = $this->website;
            $supplier->payment_terms = $this->payment_terms;
            $supplier->credit_limit = $this->credit_limit;
            $supplier->bank_name = $this->bank_name;
            $supplier->bank_account = $this->bank_account;
            
            $supplier->save();
            
            DB::commit();
            
            $this->resetForm();
            $this->showModal = false;
            
            // Fechar o modal
            $this->dispatch('hideModal');
            
            // Notificação toast
            $this->dispatch('notify', 
                type: 'success', 
                title: __('livewire/suppliers.success'), 
                message: $this->supplier_id ? 
                    __('livewire/suppliers.supplier_updated') : 
                    __('livewire/suppliers.supplier_created')
            );

        } catch (\Exception $e) {
            DB::rollBack();
            // Notificação toast de erro
            $this->dispatch('notify', 
                type: 'error', 
                title: __('livewire/suppliers.error'), 
                message: $e->getMessage()
            );
        }
    }

    public function confirmDelete($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->deleteSupplierName = $supplier->name;
        $this->deleteSupplier = $supplier; // Adicionado para compatibilidade com o novo modal
        $this->showConfirmDelete = true;
        $this->itemToDelete = $id;
        $this->showDeleteModal = true; // Adicionado para compatibilidade com o novo modal
    }

    public function delete()
    {
        try {
            $supplier = Supplier::findOrFail($this->itemToDelete);
            $supplier->delete();
            
            // Notificação toast
            $this->dispatch('notify', 
                type: 'success', 
                title: __('livewire/suppliers.success'), 
                message: __('livewire/suppliers.supplier_deleted')
            );
            
        } catch (\Exception $e) {
            // Notificação toast de erro
            $this->dispatch('notify', 
                type: 'error', 
                title: __('livewire/suppliers.error'), 
                message: $e->getMessage()
            );
        }
        
        // Limpar propriedades e fechar o modal
        $this->closeDeleteModal();
    }

    public function resetForm()
    {
        $this->reset([
            'supplier_id', 'name', 'code', 'contact_person', 'email',
            'phone', 'address', 'city', 'state', 'country',
            'postal_code', 'notes', 'tax_id', 'website',
            'bank_name', 'bank_account', 'credit_limit'
        ]);
        
        $this->status = 'active';
        $this->payment_terms = 30;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showViewModal = false;
        $this->resetForm();
        
        // Emitir eventos para fechar os modais
        $this->dispatch('hideModal');
        $this->dispatch('hideViewModal');
    }

    public function cancelDelete()
    {
        $this->closeDeleteModal();
    }
    
    public function closeDeleteModal()
    {
        $this->showConfirmDelete = false;
        $this->showDeleteModal = false;
        $this->itemToDelete = null;
        $this->deleteSupplierName = null;
        $this->deleteSupplier = null;
    }

    protected function generateSupplierCode()
    {
        $prefix = 'SUP';
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();
        
        if ($lastSupplier) {
            $lastCode = $lastSupplier->code;
            if (strpos($lastCode, $prefix) === 0) {
                $number = intval(substr($lastCode, strlen($prefix)));
                $newNumber = $number + 1;
            } else {
                $newNumber = 1;
            }
        } else {
            $newNumber = 1;
        }
        
        $this->code = $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $query = Supplier::query();
        
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%')
                  ->orWhere('country', 'like', '%' . $this->search . '%');
            });
        }
        
        $suppliers = $query->orderBy($this->sortField, $this->sortDirection)
                           ->paginate(10);
        
        return view('livewire.supply-chain.suppliers', [
            'suppliers' => $suppliers
        ]);
    }
}

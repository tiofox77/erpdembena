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

    public $perPage = 10;
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
    public $status = '';
    public $tax_id;
    public $website;
    public $payment_terms = 30;
    public $credit_limit;
    public $bank_name;
    public $bank_account;
    public $category_id = '';
    public $position;

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
            'code' => [
                'required', 
                'string', 
                'max:50',
                Rule::unique('sc_suppliers', 'code')->ignore($this->supplier_id)
            ],
            'category_id' => 'nullable|exists:sc_supplier_categories,id',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'tax_id' => 'nullable|string|max:50',
            'website' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(https?:\/\/)?(www\.)?[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+(\/[^\s]*)?$/',
            ],
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
        ];
    }

    protected function messages()
    {
        return [
            'website.regex' => __('validation.website_format', [
                'format' => 'exemplo.com, www.exemplo.com, http://exemplo.com ou https://www.exemplo.com'
            ]),
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
        $supplier = Supplier::findOrFail($id);
        
        $this->supplier_id = $supplier->id;
        $this->name = $supplier->name;
        $this->code = $supplier->code;
        $this->category_id = $supplier->category_id;
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
        $this->position = $supplier->position;
        
        $this->showModal = true;
        $this->editMode = true;
    }

    public function view($id)
    {
        $supplier = Supplier::with('category')->findOrFail($id);
        $this->viewingSupplier = [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'code' => $supplier->code,
            'category' => $supplier->category ? $supplier->category->name : '--',
            'contact_person' => $supplier->contact_person,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'address' => $supplier->address,
            'city' => $supplier->city,
            'state' => $supplier->state,
            'country' => $supplier->country,
            'postal_code' => $supplier->postal_code,
            'notes' => $supplier->notes,
            'status' => $supplier->status,
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

    public function clearFilters()
    {
        $this->reset(['search', 'sortField', 'sortDirection', 'status', 'category_id', 'perPage']);
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function save()
    {
        try {
            // Validate the form data
            $validatedData = $this->validate();
            
            DB::beginTransaction();
            
            // Prepare the data for saving
            $data = [
                'name' => $this->name,
                'code' => $this->code,
                'category_id' => $this->category_id,
                'contact_person' => $this->contact_person,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'notes' => $this->notes,
                'status' => $this->status,
                'tax_id' => $this->tax_id,
                'website' => $this->website,
                'payment_terms' => $this->payment_terms,
                'credit_limit' => $this->credit_limit,
                'bank_name' => $this->bank_name,
                'bank_account' => $this->bank_account,
                'position' => $this->position,
                'updated_by' => auth()->id(),
            ];
            
            if (!$this->editMode) {
                // Create new supplier
                $data['created_by'] = auth()->id();
                $supplier = Supplier::create($data);
                $this->dispatch('notify', 
                    type: 'success',
                    message: __('messages.supplier_created_successfully')
                );
            } else {
                // Update existing supplier
                $supplier = Supplier::findOrFail($this->supplier_id);
                $supplier->update($data);
                $this->dispatch('notify', 
                    type: 'warning',
                    message: __('messages.supplier_updated_successfully')
                );
            }
            
            DB::commit();
            
            // Close modal and reset form
            $this->closeModal();
            $this->resetForm();
            $this->dispatch('refresh');
            
            return;
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation error in save method', [
                'errors' => $e->errors(),
                'input' => $this->all()
            ]);
            throw $e;
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving supplier', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.error_saving_supplier', ['error' => $e->getMessage()])
            );
            
            // Re-throw the exception to see it in the browser console
            throw $e;
        }
    }

    public function confirmDelete($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->deleteSupplierName = $supplier->name;
        $this->deleteSupplier = $supplier; 
        $this->showConfirmDelete = true;
        $this->itemToDelete = $id;
        $this->showDeleteModal = true; 
    }

    public function delete()
    {
        try {
            $supplier = Supplier::findOrFail($this->itemToDelete);
            $supplier->delete();
            
            $this->dispatch('notify', 
                type: 'success',
                message: __('messages.supplier_deleted_successfully')
            );
            
            // Emit event to refresh the table
            $this->dispatch('supplierDeleted');
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.error_deleting_supplier', ['error' => $e->getMessage()])
            );
        }
        
        $this->closeDeleteModal();
    }

    public function resetForm()
    {
        $this->reset([
            'supplier_id', 'name', 'code', 'category_id', 'contact_person', 'email',
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

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        $query = Supplier::with('category');
        
        // Apply search filter
        if (!empty($this->search)) {
            $search = '%' . $this->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('code', 'like', $search)
                  ->orWhere('contact_person', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('phone', 'like', $search)
                  ->orWhere('tax_id', 'like', $search)
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'like', $search);
                  });
            });
        }
        
        // Apply status filter
        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }
        
        // Apply category filter
        if (!empty($this->category_id)) {
            $query->where('category_id', $this->category_id);
        }
        
        // Apply sorting and pagination
        $suppliers = $query->orderBy($this->sortField, $this->sortDirection)
                         ->paginate($this->perPage);

        $categories = \App\Models\SupplyChain\SupplierCategory::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.supply-chain.suppliers', [
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Bank;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class Banks extends Component
{
    use WithPagination, WithFileUploads;

    // Search and Filters
    public string $search = '';
    public string $statusFilter = 'all'; // all, active, inactive
    public int $perPage = 10;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    // Modal States
    public bool $showModal = false;
    public bool $isEditing = false;
    public bool $showDeleteModal = false;

    // Form Properties
    public ?int $bank_id = null;
    public string $name = '';
    public string $short_name = '';
    public string $code = '';
    public string $swift_code = '';
    public string $country = 'Angola';
    public bool $is_active = true;
    public ?string $website = null;
    public ?string $phone = null;
    public ?string $address = null;
    public ?string $description = null;
    public $logo = null;
    public ?string $current_logo = null;

    // Delete confirmation
    public ?int $deleteId = null;

    protected array $rules = [
        'name' => 'required|string|max:255',
        'short_name' => 'nullable|string|max:50',
        'code' => 'nullable|string|max:10|unique:banks,code',
        'swift_code' => 'nullable|string|max:11',
        'country' => 'required|string|max:100',
        'is_active' => 'boolean',
        'website' => 'nullable|url|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
        'description' => 'nullable|string|max:1000',
        'logo' => 'nullable|image|max:2048', // 2MB max
    ];

    public function mount(): void
    {
        $this->country = 'Angola';
    }

    public function render()
    {
        $banks = Bank::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('short_name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter === 'active', function ($query) {
                $query->active();
            })
            ->when($this->statusFilter === 'inactive', function ($query) {
                $query->inactive();
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.hr.banks', [
            'banks' => $banks,
            'totalBanks' => Bank::count(),
            'activeBanks' => Bank::active()->count(),
            'inactiveBanks' => Bank::inactive()->count(),
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create(): void
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $bank = Bank::findOrFail($id);
        
        $this->bank_id = $bank->id;
        $this->name = $bank->name;
        $this->short_name = $bank->short_name ?? '';
        $this->code = $bank->code ?? '';
        $this->swift_code = $bank->swift_code ?? '';
        $this->country = $bank->country;
        $this->is_active = $bank->is_active;
        $this->website = $bank->website;
        $this->phone = $bank->phone;
        $this->address = $bank->address;
        $this->description = $bank->description;
        $this->current_logo = $bank->logo;
        
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function updatedName(): void
    {
        $this->resetValidation('name');
        $this->generateCode();
    }

    public function updatedShortName(): void
    {
        $this->resetValidation('short_name');
        $this->generateCode();
    }

    private function generateCode(): void
    {
        if (!empty($this->name)) {
            // Generate code from name or short_name
            $baseCode = !empty($this->short_name) ? $this->short_name : $this->name;
            
            // Convert to uppercase and remove special characters
            $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $baseCode));
            
            // Take first 6 characters or pad with zeros
            $code = substr($code . '000000', 0, 6);
            
            // Check if code already exists and make unique
            $originalCode = $code;
            $counter = 1;
            
            while (Bank::where('code', $code)->where('id', '!=', $this->bank_id)->exists()) {
                $code = $originalCode . str_pad($counter, 2, '0', STR_PAD_LEFT);
                $counter++;
            }
            
            $this->code = $code;
        }
    }

    public function save(): void
    {
        // Update validation rule for editing
        if ($this->isEditing && $this->bank_id) {
            $this->rules['code'] = 'nullable|string|max:10|unique:banks,code,' . $this->bank_id;
        }

        $this->validate();

        $data = [
            'name' => trim($this->name ?? ''),
            'short_name' => !empty($this->short_name) ? trim($this->short_name) : null,
            'code' => !empty($this->code) ? trim($this->code) : null,
            'swift_code' => !empty($this->swift_code) ? trim($this->swift_code) : null,
            'country' => $this->country ?? 'Angola',
            'is_active' => $this->is_active,
            'website' => !empty($this->website) ? trim($this->website) : null,
            'phone' => !empty($this->phone) ? trim($this->phone) : null,
            'address' => !empty($this->address) ? trim($this->address) : null,
            'description' => !empty($this->description) ? trim($this->description) : null,
        ];

        // Handle logo upload
        if ($this->logo) {
            // Delete old logo if editing
            if ($this->isEditing && $this->current_logo) {
                Storage::disk('public')->delete($this->current_logo);
            }
            
            $logoPath = $this->logo->store('banks/logos', 'public');
            $data['logo'] = $logoPath;
        }

        if ($this->isEditing) {
            $bank = Bank::findOrFail($this->bank_id);
            $bank->update($data);
            session()->flash('success', __('messages.bank_updated_successfully'));
        } else {
            Bank::create($data);
            session()->flash('success', __('messages.bank_created_successfully'));
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $bank = Bank::findOrFail($this->deleteId);
            
            // Check if bank is being used by employees
            if ($bank->employees()->count() > 0) {
                session()->flash('error', __('messages.cannot_delete_bank_in_use'));
                $this->showDeleteModal = false;
                return;
            }

            // Delete logo if exists
            if ($bank->logo) {
                Storage::disk('public')->delete($bank->logo);
            }

            $bank->delete();
            session()->flash('success', __('messages.bank_deleted_successfully'));
        }

        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function toggleStatus(int $id): void
    {
        $bank = Bank::findOrFail($id);
        $bank->update(['is_active' => !$bank->is_active]);
        
        $status = $bank->is_active ? __('messages.activated') : __('messages.deactivated');
        session()->flash('success', __('messages.bank_status_updated', ['status' => $status]));
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->bank_id = null;
        $this->name = '';
        $this->short_name = '';
        $this->code = '';
        $this->swift_code = '';
        $this->country = 'Angola';
        $this->is_active = true;
        $this->website = null;
        $this->phone = null;
        $this->address = null;
        $this->description = null;
        $this->logo = null;
        $this->current_logo = null;
    }
}

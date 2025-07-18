<?php

namespace App\Livewire\Stocks;

use App\Models\EquipmentPart;
use App\Models\MaintenanceEquipment;
use App\Models\StockTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class StockIn extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $equipmentId = null;
    
    // Modals control
    public $showSearchModal = false;
    public $partSearch = '';
    public $selectedPart = null;

    public $perPage = 10;
    public $showModal = false;
    public $equipmentPartId = null;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Form data
    public $stockIn = [
        'equipment_part_id' => null,
        'quantity' => 1,
        'unit_cost' => null,
        'supplier' => '',
        'invoice_number' => '',
        'received_date' => '',
        'notes' => '',
    ];

    /**
     * Validation rules
     */
    protected function rules()
    {
        return [
            'stockIn.equipment_part_id' => 'required|exists:equipment_parts,id',
            'stockIn.quantity' => 'required|integer|min:1',
            'stockIn.unit_cost' => 'nullable|numeric|min:0',
            'stockIn.supplier' => 'nullable|string|max:255',
            'stockIn.invoice_number' => 'nullable|string|max:255',
            'stockIn.received_date' => 'nullable|date',
            'stockIn.notes' => 'nullable|string',
        ];
    }

    /**
     * Custom validation messages
     */
    protected function messages()
    {
        return [
            'stockIn.equipment_part_id.required' => 'Please select a part.',
            'stockIn.quantity.required' => 'The quantity is required.',
            'stockIn.quantity.min' => 'The quantity must be at least 1.',
        ];
    }

    /**
     * Lifecycle hook that runs once on component initialization
     */
    public function mount($equipmentId = null)
    {
        if ($equipmentId) {
            $this->equipmentId = $equipmentId;
        }
        $this->stockIn['received_date'] = Carbon::now()->format('Y-m-d');
        
        // Reset selected part when mounting
        if (!empty($this->stockIn['equipment_part_id'])) {
            $this->selectedPart = EquipmentPart::with('equipment')
                ->find($this->stockIn['equipment_part_id']);
        }
    }

    /**
     * Get all equipment for dropdown selection
     */
    #[Computed]
    public function getEquipmentListProperty()
    {
        return MaintenanceEquipment::orderBy('name')->get();
    }

    /**
     * Get equipment parts based on equipment filter and search term
     */
    #[Computed]
    public function getPartsForEquipmentProperty()
    {
        return EquipmentPart::when($this->equipmentId, function ($query) {
                return $query->where('maintenance_equipment_id', $this->equipmentId);
            })
            ->when($this->partSearch, function ($query) {
                $searchTerm = '%' . $this->partSearch . '%';
                return $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                      ->orWhere('part_number', 'like', $searchTerm)
                      ->orWhere('bar_code', 'like', $searchTerm);
                });
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get stock transactions with pagination and filtering
     */
    #[Computed]
    public function stockTransactions()
    {
        return StockTransaction::with(['part', 'createdBy'])
            ->where('type', 'stock_in')
            ->when($this->search, function($query) {
                $searchTerm = '%' . $this->search . '%';
                return $query->where(function($q) use ($searchTerm) {
                    // Busca em partes relacionadas
                    $q->whereHas('part', function($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', $searchTerm)
                                ->orWhere('part_number', 'like', $searchTerm);
                    })
                    // Busca em campos diretos da transação
                    ->orWhere('supplier', 'like', $searchTerm)
                    ->orWhere('invoice_number', 'like', $searchTerm);
                });
            })
            ->when($this->equipmentId, function($query) {
                return $query->whereHas('part', function($q) {
                    $q->where('maintenance_equipment_id', $this->equipmentId);
                });
            })
            ->when($this->equipmentPartId, function($query) {
                return $query->where('equipment_part_id', $this->equipmentPartId);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /**
     * Get equipment parts for the dropdown
     */
    #[Computed]
    public function equipmentParts()
    {
        return EquipmentPart::query()
            ->when($this->equipmentId, function($query) {
                return $query->where('maintenance_equipment_id', $this->equipmentId);
            })
            ->when($this->partSearch, function($query) {
                $search = '%' . $this->partSearch . '%';
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', $search)
                      ->orWhere('part_number', 'like', $search)
                      ->orWhere('bar_code', 'like', $search);
                });
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Open the modal for creating a new stock in entry
     */
    public function openCreateModal($partId = null)
    {
        $this->reset('stockIn', 'selectedPart', 'partSearch');
        $this->stockIn['received_date'] = Carbon::now()->format('Y-m-d');
        
        if ($partId) {
            $this->selectPart($partId);
        } else {
            $this->showSearchModal = true;
        }
    }
    
    public function openSearchModal()
    {
        $this->showSearchModal = true;
    }
    
    public function closeSearchModal()
    {
        $this->showSearchModal = false;
        $this->partSearch = '';
    }
    
    public function selectPart($partId)
    {
        $this->selectedPart = EquipmentPart::with('equipment')->find($partId);
        
        if ($this->selectedPart) {
            $this->stockIn['equipment_part_id'] = $this->selectedPart->id;
            
            // Set unit cost if available
            if ($this->selectedPart->unit_cost) {
                $this->stockIn['unit_cost'] = $this->selectedPart->unit_cost;
            }
            
            // Only show the main modal after selection
            $this->showSearchModal = false;
            $this->showModal = true;
        }
    }

    /**
     * Process stock in
     */
    public function processStockIn()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $part = EquipmentPart::findOrFail($this->stockIn['equipment_part_id']);
            $newQuantity = $part->stock_quantity + $this->stockIn['quantity'];
            
            // Update the part's stock quantity and unit cost if provided
            $updateData = ['stock_quantity' => $newQuantity, 'last_restock_date' => now()];
            
            if ($this->stockIn['unit_cost']) {
                $updateData['unit_cost'] = $this->stockIn['unit_cost'];
            }
            
            $part->update($updateData);
            
            // Create a stock transaction record
            StockTransaction::create([
                'equipment_part_id' => $this->stockIn['equipment_part_id'],
                'quantity' => $this->stockIn['quantity'],
                'type' => 'stock_in',
                'unit_cost' => $this->stockIn['unit_cost'],
                'supplier' => $this->stockIn['supplier'],
                'invoice_number' => $this->stockIn['invoice_number'],
                'transaction_date' => $this->stockIn['received_date'] 
                    ? Carbon::parse($this->stockIn['received_date']) 
                    : now(),
                'notes' => $this->stockIn['notes'],
                'created_by' => auth()->id(),
            ]);
            
            DB::commit();
            
            $notificationType = 'success';
            $message = "Successfully added {$this->stockIn['quantity']} items to stock for {$part->name}.";
            $this->dispatch('notify', type: $notificationType, message: $message);
            
            $this->showModal = false;
            $this->reset('stockIn');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error processing stock in: ' . $e->getMessage());
            $notificationType = 'error';
            $message = 'Error processing stock in: ' . $e->getMessage();
            $this->dispatch('notify', type: $notificationType, message: $message);
        }
    }

    /**
     * Close any modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset('stockIn');
    }

    /**
     * Real-time validation
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Sort transactions by a specific field
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }

        // Reset to the first page when sorting
        $this->resetPage();
    }

    /**
     * Get the name of the selected equipment 
     */
    public function getSelectedEquipmentName()
    {
        if (empty($this->equipmentId)) {
            return '';
        }
        
        $equipment = MaintenanceEquipment::find($this->equipmentId);
        return $equipment ? $equipment->name : '';
    }
    
    /**
     * Select equipment in the filter dropdown
     */
    public function selectEquipmentId($id)
    {
        $this->equipmentId = $id;
        $this->resetPage();
    }
    
    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->reset('search', 'equipmentId');
        $this->resetPage();
    }

    /**
     * Generate PDF for stock in transactions
     * 
     * @param int|null $id ID of specific stock in transaction or null for all filtered transactions
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generatePdf($id = null)
    {
        try {
            if ($id) {
                // Mostrar notificação antes de iniciar o download
                $this->dispatch('notify', type: 'success', message: __('livewire/stocks/stock-in.pdf_generating'));
                
                // Generate PDF for a specific stock in transaction
                $transaction = StockTransaction::with(['part', 'createdBy'])
                    ->where('type', 'stock_in')
                    ->findOrFail($id);
                
                $pdf = Pdf::loadView('livewire.stocks.stock-in-pdf', [
                    'transaction' => $transaction
                ]);
                
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, 'stock-in-' . $transaction->id . '.pdf');
            } else {
                // Mostrar notificação antes de iniciar o download
                $this->dispatch('notify', type: 'success', message: __('livewire/stocks/stock-in.pdf_list_generating'));
                
                // Generate PDF for filtered stock in transactions
                $transactions = StockTransaction::with(['part', 'createdBy'])
                    ->where('type', 'stock_in')
                    ->when($this->search, function($query) {
                        $searchTerm = '%' . $this->search . '%';
                        return $query->where(function($q) use ($searchTerm) {
                            // Busca em partes relacionadas
                            $q->whereHas('part', function($subQuery) use ($searchTerm) {
                                $subQuery->where('name', 'like', $searchTerm)
                                        ->orWhere('part_number', 'like', $searchTerm);
                            })
                            // Busca em campos diretos da transação
                            ->orWhere('supplier', 'like', $searchTerm)
                            ->orWhere('invoice_number', 'like', $searchTerm);
                        });
                    })
                    ->when($this->equipmentId, function($query) {
                        return $query->whereHas('part', function($q) {
                            $q->where('maintenance_equipment_id', $this->equipmentId);
                        });
                    })
                    ->when($this->equipmentPartId, function($query) {
                        return $query->where('equipment_part_id', $this->equipmentPartId);
                    })
                    ->orderBy($this->sortField, $this->sortDirection)
                    ->get();
                
                $pdf = Pdf::loadView('livewire.stocks.stock-in-list-pdf', [
                    'transactions' => $transactions
                ]);
                
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, 'stock-in-report-' . date('Y-m-d') . '.pdf');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('livewire/stocks/stock-in.pdf_error') . $e->getMessage());
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        $equipmentParts = EquipmentPart::when($this->equipmentId, function($query) {
            return $query->where('maintenance_equipment_id', $this->equipmentId);
        })->orderBy('name')->get();

        $stockTransactions = StockTransaction::with(['part', 'createdBy'])
            ->where('type', 'stock_in')
            ->when($this->search, function($query) {
                $searchTerm = '%' . $this->search . '%';
                return $query->where(function($q) use ($searchTerm) {
                    // Busca em partes relacionadas
                    $q->whereHas('part', function($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', $searchTerm)
                                ->orWhere('part_number', 'like', $searchTerm);
                    })
                    // Busca em campos diretos da transação
                    ->orWhere('supplier', 'like', $searchTerm)
                    ->orWhere('invoice_number', 'like', $searchTerm);
                });
            })
            ->when($this->equipmentId, function($query) {
                return $query->whereHas('part', function($q) {
                    $q->where('maintenance_equipment_id', $this->equipmentId);
                });
            })
            ->when($this->equipmentPartId, function($query) {
                return $query->where('equipment_part_id', $this->equipmentPartId);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.stocks.stock-in', [
            'equipmentParts' => $equipmentParts,
            'equipment' => MaintenanceEquipment::pluck('name', 'id')->toArray(),
            'stockTransactions' => $stockTransactions,
        ]);
    }
}

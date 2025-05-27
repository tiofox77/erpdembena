<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockOut as StockOutModel;
use App\Models\StockOutItem;
use App\Models\StockTransaction;
use App\Models\EquipmentPart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class StockOut extends Component
{
    use WithPagination;

    // Search and filtering properties
    public $search = '';
    public $perPage = 10;
    public $sortField = 'date';
    public $sortDirection = 'desc';
    public $equipmentPartId = '';
    
    // Modal control properties
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $showSearchModal = false;
    public $isEditing = false;
    public $stockOutId = null;
    public $viewingStockOut = null;
    public $partSearch = '';
    
    // Form data
    public $stockOut = [
        'date' => '',
        'reason' => '',
        'notes' => '',
        'reference_number' => '',
    ];
    
    // Multiple parts selection
    public $selectedParts = [];
    public $newPart = [
        'equipment_part_id' => '',
        'quantity' => 1
    ];
    
    // Part for selection in the modal
    public $selectedPartForModal = null;

    protected $listeners = ['refresh' => '$refresh'];

    protected function rules() {
        return [
            'stockOut.date' => 'required|date',
            'stockOut.reason' => 'required|string|max:255',
            'stockOut.notes' => 'nullable|string',
            'stockOut.reference_number' => 'nullable|string|max:50',
            'selectedParts' => 'required|array|min:1',
            'selectedParts.*.equipment_part_id' => 'required|exists:equipment_parts,id',
            'selectedParts.*.quantity' => 'required|integer|min:1',
        ];
    }

    protected $messages = [
        'selectedParts.required' => 'You must select at least one part.',
        'selectedParts.min' => 'You must select at least one part.',
        'selectedParts.*.equipment_part_id.required' => 'Please select a part.',
        'selectedParts.*.quantity.required' => 'Quantity is required.',
        'selectedParts.*.quantity.integer' => 'Quantity must be a whole number.',
        'selectedParts.*.quantity.min' => 'Quantity must be at least 1.',
    ];

    public function mount() {
        $this->stockOut['date'] = date('Y-m-d');
        $this->stockOut['reference_number'] = 'SO-' . date('YmdHis');
        $this->resetNewPart();
    }

    public function resetNewPart() {
        $this->newPart = [
            'equipment_part_id' => '',
            'quantity' => 1
        ];
        $this->selectedPartForModal = null;
    }

    public function openSearchModal() {
        $this->showSearchModal = true;
        $this->partSearch = '';
    }
    
    public function closeSearchModal() {
        $this->showSearchModal = false;
        $this->partSearch = '';
        $this->selectedPartForModal = null;
    }
    
    public function selectPart($partId) {
        $this->selectedPartForModal = EquipmentPart::find($partId);
        
        if ($this->selectedPartForModal) {
            $this->newPart['equipment_part_id'] = $this->selectedPartForModal->id;
            $this->showSearchModal = false;
        }
    }
    
    public function addPart() {
        // Validate the new part
        $this->validate([
            'newPart.equipment_part_id' => 'required|exists:equipment_parts,id',
            'newPart.quantity' => 'required|integer|min:1',
        ]);

        // Get part details to display in the list
        $part = EquipmentPart::find($this->newPart['equipment_part_id']);
        
        // Check if part exists and has stock
        if (!$part) {
            $this->dispatch('notify', 
                type: 'error',
                message: __('livewire/stocks/stock-out.part_not_found')
            );
            return;
        }
        
        if ($part->stock_quantity < $this->newPart['quantity']) {
            $this->dispatch('notify', 
                type: 'error',
                message: __('livewire/stocks/stock-out.insufficient_stock', ['quantity' => $part->stock_quantity])
            );
            return;
        }

        // Check if part already exists in the list
        foreach ($this->selectedParts as $index => $selectedPart) {
            if ($selectedPart['equipment_part_id'] == $this->newPart['equipment_part_id']) {
                // Just update the quantity
                $this->selectedParts[$index]['quantity'] += $this->newPart['quantity'];
                
                // Check if the new quantity exceeds stock
                if ($this->selectedParts[$index]['quantity'] > $part->stock_quantity) {
                    $this->selectedParts[$index]['quantity'] = $part->stock_quantity;
                    $this->dispatch('notify', 
                        type: 'warning',
                        message: __('livewire/stocks/stock-out.quantity_adjusted_to_max')
                    );
                }
                
                $this->resetNewPart();
                return;
            }
        }

        // Add new part with details
        $this->selectedParts[] = [
            'equipment_part_id' => $part->id,
            'part_name' => $part->name,
            'part_number' => $part->part_number,
            'bac_code' => $part->bac_code,
            'quantity' => $this->newPart['quantity'],
            'available_stock' => $part->stock_quantity
        ];

        $this->resetNewPart();
    }

    public function removePart($index) {
        unset($this->selectedParts[$index]);
        $this->selectedParts = array_values($this->selectedParts);
    }

    public function saveStockOut() {
        $this->validate();
        
        DB::beginTransaction();
        
        try {
            // If editing, we need to process differently - restore previous quantities first
            if ($this->isEditing) {
                $stockOut = StockOutModel::with('items.equipmentPart')->findOrFail($this->stockOutId);
                
                // First return all parts to inventory
                foreach ($stockOut->items as $item) {
                    $part = $item->equipmentPart;
                    $part->stock_quantity += $item->quantity;
                    $part->save();
                }
                
                // Delete all previous items
                $stockOut->items()->delete();
                
                // Update the main record
                $stockOut->update([
                    'date' => $this->stockOut['date'],
                    'reason' => $this->stockOut['reason'],
                    'notes' => $this->stockOut['notes'],
                ]);
            } else {
                // Create new stock out record
                $stockOut = StockOutModel::create([
                    'date' => $this->stockOut['date'],
                    'reason' => $this->stockOut['reason'],
                    'notes' => $this->stockOut['notes'],
                    'reference_number' => $this->stockOut['reference_number'],
                    'user_id' => Auth::id(),
                ]);
            }
            
            // Process all selected parts
            foreach ($this->selectedParts as $selectedPart) {
                // Create the stock out item
                StockOutItem::create([
                    'stock_out_id' => $stockOut->id,
                    'equipment_part_id' => $selectedPart['equipment_part_id'],
                    'quantity' => $selectedPart['quantity'],
                ]);
                
                // Update the part's stock quantity
                $part = EquipmentPart::findOrFail($selectedPart['equipment_part_id']);
                $part->stock_quantity -= $selectedPart['quantity'];
                
                // Ensure we don't have negative stock
                if ($part->stock_quantity < 0) {
                    throw new \Exception("Cannot have negative stock for part: {$part->name}");
                }
                
                $part->save();
                
                // Create a new stock transaction
                StockTransaction::create([
                    'equipment_part_id' => $selectedPart['equipment_part_id'],
                    'quantity' => $selectedPart['quantity'],
                    'type' => 'stock_out',
                    'transaction_date' => Carbon::parse($this->stockOut['date']) ?? Carbon::now(),
                    'notes' => $this->stockOut['reason'] . ($this->stockOut['notes'] ? ' - ' . $this->stockOut['notes'] : ''),
                    'invoice_number' => $this->stockOut['reference_number'],
                    'created_by' => Auth::id(),
                ]);
            }
            
            DB::commit();
            
            $this->closeModal();
            $this->dispatch('notify', 
                type: 'success',
                message: $this->isEditing ? __('livewire/stocks/stock-out.updated_success') : __('livewire/stocks/stock-out.created_success')
            );
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function getPaginatedStockOutsProperty() {
        return StockOutModel::with(['items.equipmentPart', 'user'])
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('reference_number', 'like', '%' . $this->search . '%')
                      ->orWhere('reason', 'like', '%' . $this->search . '%')
                      ->orWhereHas('items.equipmentPart', function($qr) {
                          $qr->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('part_number', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->equipmentPartId, function($query) {
                return $query->whereHas('items', function($q) {
                    $q->where('equipment_part_id', $this->equipmentPartId);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getPartsListProperty()
    {
        return EquipmentPart::with('equipment')
            ->orderBy('name')
            ->get();
    }

    public function getPartsListForSearchProperty()
    {
        return EquipmentPart::with('equipment')
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

    public function getStockOutsProperty() {
        return $this->getPaginatedStockOutsProperty();
    }

    public function editStockOut($id) {
        $this->stockOutId = $id;
        $this->isEditing = true;
        
        $stockOut = StockOutModel::with('items.equipmentPart')->findOrFail($id);
        
        $this->stockOut = [
            'date' => $stockOut->date,
            'reason' => $stockOut->reason,
            'notes' => $stockOut->notes,
            'reference_number' => $stockOut->reference_number,
        ];
        
        $this->selectedParts = [];
        foreach ($stockOut->items as $item) {
            $this->selectedParts[] = [
                'equipment_part_id' => $item->equipment_part_id,
                'part_name' => $item->equipmentPart->name,
                'part_number' => $item->equipmentPart->part_number,
                'bac_code' => $item->equipmentPart->bac_code,
                'quantity' => $item->quantity,
                'available_stock' => $item->equipmentPart->stock_quantity + $item->quantity, // Add back the current quantity for proper validation
            ];
        }
        
        $this->showModal = true;
        $this->showViewModal = false;
    }

    public function viewStockOut($id) {
        $this->viewingStockOut = StockOutModel::with(['items.equipmentPart', 'user'])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal() {
        $this->showViewModal = false;
        $this->viewingStockOut = null;
    }

    public function openCreateModal() {
        $this->reset(['stockOut', 'selectedParts', 'stockOutId', 'isEditing']);
        $this->stockOut['date'] = date('Y-m-d');
        $this->stockOut['reference_number'] = 'SO-' . date('YmdHis');
        $this->resetNewPart();
        $this->showModal = true;
    }

    public function confirmDelete($id) {
        $this->stockOutId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteStockOut() {
        try {
            DB::beginTransaction();
            
            $stockOut = StockOutModel::with('items.equipmentPart')->findOrFail($this->stockOutId);
            
            // Return quantities to stock for each item
            foreach ($stockOut->items as $item) {
                $part = $item->equipmentPart;
                $part->stock_quantity += $item->quantity;
                $part->save();
                
                // Delete the corresponding stock transaction
                StockTransaction::where('equipment_part_id', $item->equipment_part_id)
                    ->where('invoice_number', $stockOut->reference_number)
                    ->where('type', 'stock_out')
                    ->delete();
            }
            
            // Delete the stock out (cascade will delete items)
            $stockOut->delete();
            
            DB::commit();
            
            $this->showDeleteModal = false;
            $this->dispatch('notify', 
                type: 'success',
                message: __('livewire/stocks/stock-out.deleted_success')
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showToast', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function generatePdf($id = null) {
        try {
            // Mostrar notificação antes de iniciar o download
            if ($id) {
                $this->dispatch('notify', type: 'success', message: __('livewire/stocks/stock-out.pdf_generating'));
                
                // Generate PDF for a specific stock out
                $stockOut = StockOutModel::with(['items.equipmentPart', 'user'])->findOrFail($id);
                $pdf = Pdf::loadView('livewire.stocks.stock-out-pdf', [
                    'stockOut' => $stockOut
                ]);
                
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, $stockOut->reference_number . '.pdf');
            } else {
                $this->dispatch('notify', type: 'success', message: __('livewire/stocks/stock-out.pdf_list_generating'));
                
                // Generate PDF for filtered stock outs
                $stockOuts = StockOutModel::with(['items.equipmentPart', 'user'])
                    ->when($this->search, function($query) {
                        return $query->whereHas('items.equipmentPart', function($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('part_number', 'like', '%' . $this->search . '%')
                              ->orWhere('bac_code', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('reference_number', 'like', '%' . $this->search . '%')
                        ->orWhere('reason', 'like', '%' . $this->search . '%');
                    })
                    ->when($this->equipmentPartId, function($query) {
                        return $query->whereHas('items', function($q) {
                            $q->where('equipment_part_id', $this->equipmentPartId);
                        });
                    })
                    ->orderBy($this->sortField, $this->sortDirection)
                    ->get();
                
                $pdf = Pdf::loadView('livewire.stocks.stock-out-list-pdf', [
                    'stockOuts' => $stockOuts
                ]);
                
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, 'stock-outs-report-' . date('Y-m-d') . '.pdf');
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('livewire/stocks/stock-out.pdf_error') . $e->getMessage());
        }
    }

    public function closeModal() {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetValidation();
    }

    public function sortBy($field) {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters() {
        $this->search = '';
        $this->equipmentPartId = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.stocks.stock-out');
    }
}

<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockOut as StockOutModel;
use App\Models\StockOutItem;
use App\Models\EquipmentPart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
    public $isEditing = false;
    public $stockOutId = null;
    public $viewingStockOut = null;
    
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
        $this->resetNewPart();
    }

    public function resetNewPart() {
        $this->newPart = [
            'equipment_part_id' => '',
            'quantity' => 1
        ];
    }

    public function addPart() {
        // Validate the new part
        $this->validate([
            'newPart.equipment_part_id' => 'required|exists:equipment_parts,id',
            'newPart.quantity' => 'required|integer|min:1',
        ]);

        // Check if part already exists in the list
        foreach ($this->selectedParts as $index => $part) {
            if ($part['equipment_part_id'] == $this->newPart['equipment_part_id']) {
                // Just update the quantity
                $this->selectedParts[$index]['quantity'] += $this->newPart['quantity'];
                $this->resetNewPart();
                return;
            }
        }

        // Get part details to display in the list
        $part = EquipmentPart::find($this->newPart['equipment_part_id']);
        
        // Add the new part to the list
        $this->selectedParts[] = [
            'equipment_part_id' => $this->newPart['equipment_part_id'],
            'quantity' => $this->newPart['quantity'],
            'part_name' => $part->name,
            'part_number' => $part->part_number,
            'bac_code' => $part->bac_code,
            'stock_quantity' => $part->stock_quantity
        ];

        // Reset the new part form
        $this->resetNewPart();
    }

    public function removePart($index) {
        unset($this->selectedParts[$index]);
        $this->selectedParts = array_values($this->selectedParts);
    }

    public function getStockOutsProperty() {
        return StockOutModel::with(['items.equipmentPart', 'user'])
            ->when($this->search, function($query) {
                return $query->whereHas('items.equipmentPart', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('part_number', 'like', '%' . $this->search . '%')
                      ->orWhere('bac_code', 'like', '%' . $this->search . '%');
                })
                ->orWhere('reason', 'like', '%' . $this->search . '%')
                ->orWhere('reference_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->equipmentPartId, function($query) {
                return $query->whereHas('items', function($q) {
                    $q->where('equipment_part_id', $this->equipmentPartId);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getPartsListProperty() {
        return EquipmentPart::orderBy('name')->get();
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

    public function openCreateModal() {
        $this->isEditing = false;
        $this->stockOutId = null;
        $this->stockOut = [
            'date' => date('Y-m-d'),
            'reason' => '',
            'notes' => '',
            'reference_number' => 'SO-' . date('YmdHis'),
        ];
        $this->selectedParts = [];
        $this->showModal = true;
    }

    public function editStockOut($id) {
        $this->isEditing = true;
        $this->stockOutId = $id;
        
        $stockOut = StockOutModel::with('items.equipmentPart')->findOrFail($id);
        
        $this->stockOut = [
            'date' => $stockOut->date,
            'reason' => $stockOut->reason,
            'notes' => $stockOut->notes,
            'reference_number' => $stockOut->reference_number,
        ];
        
        // Load the related parts
        $this->selectedParts = [];
        foreach ($stockOut->items as $item) {
            $this->selectedParts[] = [
                'equipment_part_id' => $item->equipment_part_id,
                'quantity' => $item->quantity,
                'part_name' => $item->equipmentPart->name,
                'part_number' => $item->equipmentPart->part_number,
                'bac_code' => $item->equipmentPart->bac_code,
                'stock_quantity' => $item->equipmentPart->stock_quantity
            ];
        }
        
        $this->showViewModal = false;
        $this->showModal = true;
    }

    public function closeModal() {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetValidation();
    }

    public function saveStockOut() {
        $this->validate();
        
        try {
            DB::beginTransaction();
            
            if ($this->isEditing) {
                // Update existing stock out
                $stockOut = StockOutModel::findOrFail($this->stockOutId);
                $stockOut->update([
                    'date' => $this->stockOut['date'],
                    'reason' => $this->stockOut['reason'],
                    'notes' => $this->stockOut['notes'],
                    'reference_number' => $this->stockOut['reference_number'],
                ]);
                
                // Handle returning quantities from old items
                foreach ($stockOut->items as $item) {
                    $part = $item->equipmentPart;
                    $part->stock_quantity += $item->quantity;
                    $part->save();
                }
                
                // Delete old items
                $stockOut->items()->delete();
            } else {
                // Create new stock out
                $stockOut = StockOutModel::create([
                    'date' => $this->stockOut['date'],
                    'reason' => $this->stockOut['reason'],
                    'notes' => $this->stockOut['notes'],
                    'reference_number' => $this->stockOut['reference_number'],
                    'user_id' => Auth::id()
                ]);
            }
            
            // Process each selected part
            foreach ($this->selectedParts as $partData) {
                // Create stock out item
                StockOutItem::create([
                    'stock_out_id' => $stockOut->id,
                    'equipment_part_id' => $partData['equipment_part_id'],
                    'quantity' => $partData['quantity']
                ]);
                
                // Update equipment part stock quantity
                $part = EquipmentPart::find($partData['equipment_part_id']);
                $part->stock_quantity -= $partData['quantity'];
                
                // Ensure stock doesn't go negative
                if ($part->stock_quantity < 0) {
                    throw new \Exception("Cannot have negative stock for part: {$part->name}");
                }
                
                $part->save();
            }
            
            DB::commit();
            
            $this->showModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $this->isEditing ? 'Stock out updated successfully!' : 'Stock out recorded successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function viewStockOut($id) {
        $this->viewingStockOut = StockOutModel::with(['items.equipmentPart', 'user'])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal() {
        $this->showViewModal = false;
        $this->viewingStockOut = null;
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
            }
            
            // Delete the stock out (cascade will delete items)
            $stockOut->delete();
            
            DB::commit();
            
            $this->showDeleteModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Stock out deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    public function generatePdf($id = null) {
        try {
            if ($id) {
                // Generate PDF for a specific stock out
                $stockOut = StockOutModel::with(['items.equipmentPart', 'user'])->findOrFail($id);
                $pdf = Pdf::loadView('livewire.stocks.stock-out-pdf', [
                    'stockOut' => $stockOut
                ]);
                
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, $stockOut->reference_number . '.pdf');
            } else {
                // Generate PDF for filtered stock outs
                $stockOuts = StockOutModel::with(['items.equipmentPart', 'user'])
                    ->when($this->search, function($query) {
                        return $query->whereHas('items.equipmentPart', function($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('part_number', 'like', '%' . $this->search . '%')
                              ->orWhere('bac_code', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('reason', 'like', '%' . $this->search . '%')
                        ->orWhere('reference_number', 'like', '%' . $this->search . '%');
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
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.stocks.stock-out');
    }
}

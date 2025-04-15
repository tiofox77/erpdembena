<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\SupplyChain\ShippingNote;
use App\Models\SupplyChain\PurchaseOrder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ShippingNotes extends Component
{
    use WithFileUploads;

    public $purchaseOrder;
    public $purchase_order_id;
    public $status;
    public $note;
    public $attachment;
    public $existingAttachment;
    public $currentShippingNotes = [];
    
    public $showAddModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $editId = null;

    public $listeners = ['refreshShippingNotes' => '$refresh'];

    protected $rules = [
        'status' => 'required|string',
        'note' => 'nullable|string',
        'attachment' => 'nullable|file|max:10240', // 10MB máximo
    ];

    public function mount($purchase_order_id = null)
    {
        $this->purchase_order_id = $purchase_order_id;
        
        if ($this->purchase_order_id) {
            $this->purchaseOrder = PurchaseOrder::findOrFail($this->purchase_order_id);
            $this->refreshNotes();
        }
    }

    public function refreshNotes()
    {
        if ($this->purchase_order_id) {
            $this->currentShippingNotes = ShippingNote::where('purchase_order_id', $this->purchase_order_id)
                ->with('updatedByUser')
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }

    public function openAddModal()
    {
        // Reseta os campos do formulário
        $this->resetForm();
        
        // Inicializa com primeiro status se estiver vazio
        if (empty($this->currentShippingNotes->count())) {
            $this->status = 'order_placed';
        }
        
        $this->showAddModal = true;
        $this->editMode = false;
        $this->editId = null;
    }

    public function openEditModal($id)
    {
        $this->resetForm();
        
        $shippingNote = ShippingNote::findOrFail($id);
        $this->editId = $id;
        $this->status = $shippingNote->status;
        $this->note = $shippingNote->note;
        $this->existingAttachment = $shippingNote->attachment_url;
        
        $this->showAddModal = true;
        $this->editMode = true;
    }

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showViewModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->status = '';
        $this->note = '';
        $this->attachment = null;
        $this->existingAttachment = null;
        $this->editId = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();
        
        try {
            if ($this->editMode && $this->editId) {
                $shippingNote = ShippingNote::findOrFail($this->editId);
            } else {
                $shippingNote = new ShippingNote();
                $shippingNote->purchase_order_id = $this->purchase_order_id;
            }
            
            $shippingNote->status = $this->status;
            $shippingNote->note = $this->note;
            $shippingNote->updated_by = Auth::id();
            
            // Handle attachment upload
            if ($this->attachment) {
                // Delete previous attachment if exists
                if ($shippingNote->attachment_url) {
                    Storage::delete($shippingNote->attachment_url);
                }
                
                $path = $this->attachment->store('shipping-notes');
                $shippingNote->attachment_url = $path;
            }
            
            $shippingNote->save();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => $this->editMode 
                    ? __('messages.shipping_note_updated') 
                    : __('messages.shipping_note_added')
            ]);
            
            $this->closeModal();
            $this->refreshNotes();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $shippingNote = ShippingNote::findOrFail($id);
            
            // Delete attachment if exists
            if ($shippingNote->attachment_url) {
                Storage::delete($shippingNote->attachment_url);
            }
            
            $shippingNote->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'title' => __('messages.success'),
                'message' => __('messages.shipping_note_deleted')
            ]);
            
            $this->refreshNotes();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => $e->getMessage()
            ]);
        }
    }

    public function downloadAttachment($id)
    {
        $shippingNote = ShippingNote::findOrFail($id);
        
        if ($shippingNote->attachment_url) {
            return Storage::download($shippingNote->attachment_url);
        }
        
        $this->dispatch('notify', [
            'type' => 'error',
            'title' => __('messages.error'),
            'message' => __('messages.attachment_not_found')
        ]);
    }

    public function render()
    {
        return view('livewire.supply-chain.shipping-notes', [
            'statusList' => ShippingNote::$statusList,
            'statusColors' => ShippingNote::$statusColors,
            'statusIcons' => ShippingNote::$statusIcons,
            'purchaseOrder' => $this->purchaseOrder,
            'shippingNotes' => $this->currentShippingNotes
        ]);
    }
}

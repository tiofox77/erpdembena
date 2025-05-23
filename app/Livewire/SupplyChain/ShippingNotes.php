<?php

namespace App\Livewire\SupplyChain;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\SupplyChain\ShippingNote;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\CustomForm;
use App\Models\SupplyChain\CustomFormSubmission;
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
    
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    public $showAddModal = false;
    public $showViewModal = false;
    public $showCustomFormModal = false;
    public $editMode = false;
    public $editId = null;
    public $selectedFormId = null;
    public $selectedNoteId = null;
    public $viewingNote = null;

    public $listeners = ['refreshShippingNotes' => '$refresh', 'refreshComponent' => '$refresh'];

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
                ->orderBy($this->sortField, $this->sortDirection)
                ->get();
        }
    }
    
    /**
     * Ordena a tabela pelo campo especificado
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->refreshNotes();
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
        $this->showCustomFormModal = false;
        $this->resetForm();
    }
    
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingNote = null;
    }

    public function resetForm()
    {
        $this->status = '';
        $this->note = '';
        $this->attachment = null;
        $this->existingAttachment = null;
        $this->editId = null;
        $this->selectedFormId = null;
        $this->selectedNoteId = null;
        $this->viewingNote = null;
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
            
            // Check if status is 'completed' (prevent deletion in this case)
            $currentStatus = null;
            
            // Check regular status field
            if ($shippingNote->status === 'delivered') {
                $currentStatus = 'completed';
            }
            
            // If using custom form, check the current status from the form
            if ($shippingNote->status === 'custom_form' && $shippingNote->custom_form_id) {
                $customStatus = $shippingNote->currentStatus();
                if ($customStatus === 'completed') {
                    $currentStatus = 'completed';
                }
            }
            
            // Prevent deletion if status is 'completed'
            if ($currentStatus === 'completed') {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => __('messages.error'),
                    'message' => __('messages.cannot_delete_completed_shipping_note')
                ]);
                return;
            }
            
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

    /**
     * Abre um formulário personalizado para o status da nota de envio
     */
    public function openCustomForm($noteId)
    {
        $this->selectedNoteId = $noteId;
        
        // Buscar formulários disponíveis
        $availableForms = CustomForm::where('entity_type', 'shipping_note')
            ->where('is_active', true)
            ->get();
            
        if ($availableForms->isEmpty()) {
            $this->dispatch('notify',
                type: 'error',
                message: __('messages.no_custom_forms_available')
            );
            return;
        }
        
        // Se tiver apenas um formulário, seleciona automaticamente
        if ($availableForms->count() === 1) {
            $this->selectedFormId = $availableForms->first()->id;
            $this->dispatch('openFormSubmission', $noteId, $this->selectedFormId);
        } else {
            // Se tiver mais de um, abre modal para escolha
            $this->showCustomFormModal = true;
        }
    }
    
    /**
     * Seleciona um formulário personalizado e abre para preenchimento
     */
    public function selectCustomForm()
    {
        if (!$this->selectedFormId || !$this->selectedNoteId) {
            $this->dispatch('notify',
                type: 'error',
                message: __('messages.select_form_first')
            );
            return;
        }
        
        $this->dispatch('openFormSubmission', $this->selectedNoteId, $this->selectedFormId);
        $this->showCustomFormModal = false;
    }
    
    /**
     * Visualiza uma nota de envio específica
     */
    public function viewNote($noteId)
    {
        try {
            $this->viewingNote = ShippingNote::with(['updatedByUser', 'customForm.fields'])
                ->findOrFail($noteId);
            
            $this->showViewModal = true;
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => __('messages.error'),
                'message' => __('messages.shipping_note_not_found')
            ]);
        }
    }
    
    public function viewFormSubmissions($noteId)
    {
        $note = ShippingNote::findOrFail($noteId);
        $submissions = CustomFormSubmission::where('entity_id', $noteId)->get();
        
        if ($submissions->isEmpty()) {
            $this->dispatch('notify',
                type: 'info',
                message: __('messages.no_submissions_found')
            );
            return;
        }
        
        // Se tiver apenas uma submissão, visualiza diretamente
        if ($submissions->count() === 1) {
            $this->dispatch('viewFormSubmission', $submissions->first()->id);
        } else {
            // TODO: Implementar visualização de múltiplas submissões 
            $this->dispatch('viewFormSubmission', $submissions->first()->id);
        }
    }

    public function render()
    {
        // Carregar formulários personalizados disponíveis
        $customForms = CustomForm::where('entity_type', 'shipping_note')
            ->where('is_active', true)
            ->get();
            
        return view('livewire.supply-chain.shipping-notes', [
            'statusList' => ShippingNote::$statusList,
            'statusColors' => ShippingNote::$statusColors,
            'statusIcons' => ShippingNote::$statusIcons,
            'purchaseOrder' => $this->purchaseOrder,
            'shippingNotes' => $this->currentShippingNotes,
            'customForms' => $customForms
        ]);
    }
}

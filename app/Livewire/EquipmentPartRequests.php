<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Url;
use App\Models\EquipmentPartRequest;
use App\Models\EquipmentPartRequestImage;
use App\Models\EquipmentPartRequestItem;
use App\Models\EquipmentPart;
use App\Models\UnitType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EquipmentPartRequests extends Component
{
    use WithPagination, WithFileUploads;

    // Search and filters
    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $status = '';

    #[Url(history: true)]
    public $sortField = 'created_at';

    #[Url(history: true)]
    public $sortDirection = 'desc';

    public $perPage = 10;

    // Form data
    public $showModal = false;
    public $isEditing = false;
    public $requestId = null;
    public $showDeleteModal = false;
    public $showViewModal = false;
    public $viewRequest = null;
    public $unitTypes = [];

    public $request = [
        'reference_number' => '',
        'equipment_part_id' => '',
        'supplier_reference' => '',
        'quantity_required' => 1,
        'unit' => 'pcs',
        'suggested_vendor' => '',
        'delivery_date' => '',
        'remarks' => '',
    ];
    
    public $requestItems = [];
    
    // Part search properties
    public $partSearch = '';
    public $showPartResults = false;
    public $currentItemIndex = null;
    public $filteredParts = [];
    public $selectedPart = null;

    // Image uploads
    public $images = [];
    public $imageCaptions = [];
    public $existingImages = [];
    public $maxImages = 10;

    // Removido o método mount duplicado
    
    // Função helper para criar um novo item vazio
    protected function getEmptyRequestItem()
    {
        return [
            'equipment_part_id' => '',
            'supplier_reference' => '',
            'quantity_required' => 1,
            'unit' => 'pcs',
            'part_details' => null
        ];
    }
    
    // Validation rules
    protected function rules()
    {
        $requestId = isset($this->requestId) ? $this->requestId : '';
        
        return [
            'request.reference_number' => 'required|string|max:255|unique:equipment_part_requests,reference_number,'.$requestId,
            'request.suggested_vendor' => 'nullable|string|max:255',
            'request.delivery_date' => 'nullable|date',
            'request.remarks' => 'nullable|string',
            'requestItems.*.equipment_part_id' => 'required|exists:equipment_parts,id',
            'requestItems.*.supplier_reference' => 'nullable|string|max:255',
            'requestItems.*.quantity_required' => 'required|integer|min:1',
            'requestItems.*.unit' => 'required|string|max:50',
            'images.*' => 'nullable|image|max:5120', // 5MB max per image
            'imageCaptions.*' => 'nullable|string|max:255',
        ];
    }

    // Custom error messages
    protected function messages()
    {
        return [
            'request.reference_number.required' => 'The reference number is required',
            'request.reference_number.unique' => 'This reference number is already in use',
            'requestItems.*.equipment_part_id.required' => 'Please select a part',
            'requestItems.*.equipment_part_id.exists' => 'The selected part is invalid',
            'requestItems.*.quantity_required.required' => 'Quantity is required',
            'requestItems.*.quantity_required.integer' => 'Quantity must be a number',
            'requestItems.*.quantity_required.min' => 'Quantity must be at least 1',
            'images.*.image' => 'The file must be an image',
            'images.*.max' => 'The image size must not exceed 5MB',
            'imageCaptions.*.max' => 'Image caption cannot exceed 255 characters',
        ];
    }

    public function mount()
    {
        $this->resetForm();
        
        // Carregar os tipos de unidades ativos
        $this->loadUnitTypes();
        
        // Garantir que requestItems tenha pelo menos um item vazio quando o componente é inicializado
        if (empty($this->requestItems)) {
            $this->requestItems = [
                $this->getEmptyRequestItem()
            ];
        }
    }
    
    /**
     * Carrega os tipos de unidades ativos do banco de dados
     */
    private function loadUnitTypes()
    {
        $this->unitTypes = UnitType::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    // Real-time validation
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // Reset pagination when filters change
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Reset all filters
    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    // Reset form data
    public function resetForm()
    {
        $this->request = [
            'reference_number' => 'REQ-' . strtoupper(Str::random(8)),
            'suggested_vendor' => '',
            'delivery_date' => '',
            'remarks' => '',
        ];
        $this->requestItems = [
            $this->getEmptyRequestItem()
        ];
        $this->images = [];
        $this->imageCaptions = [];
        $this->selectedPart = null;
        $this->showPartResults = false;
        $this->existingImages = [];
        $this->filteredParts = [];
        $this->partSearch = '';
        $this->currentItemIndex = null;
    }
    
    // Adicionar um novo item à lista de itens do pedido
    public function addItem()
    {
        $this->requestItems[] = $this->getEmptyRequestItem();
    }
    
    // Remover um item específico da lista
    public function removeItem($index)
    {
        if (count($this->requestItems) > 1) {
            // Remover apenas se houver mais de um item
            unset($this->requestItems[$index]);
            // Reindexar o array para evitar índices não sequenciais
            $this->requestItems = array_values($this->requestItems);
        } else {
            // Se for o último item, apenas limpar os valores
            $this->requestItems[0] = $this->getEmptyRequestItem();
        }
    }
    
    // Abrir a modal de pesquisa de peças para um item específico
    public function openPartSearch($index)
    {
        $this->currentItemIndex = $index;
        $this->partSearch = '';
        $this->filteredParts = [];
        $this->showPartResults = true;
    }

    // Search for parts
    public function updatedPartSearch()
    {
        $this->showPartResults = true;
        if (strlen($this->partSearch) >= 2) {
            $this->filteredParts = EquipmentPart::where(function($query) {
                $query->where('name', 'like', '%' . $this->partSearch . '%')
                    ->orWhere('part_number', 'like', '%' . $this->partSearch . '%')
                    ->orWhere('description', 'like', '%' . $this->partSearch . '%')
                    ->orWhere('bar_code', 'like', '%' . $this->partSearch . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
        } else {
            $this->filteredParts = [];
        }
    }

    // Select a part from search results
    public function selectPart($id)
    {
        if ($this->currentItemIndex === null) {
            return;
        }
        
        $this->selectedPart = EquipmentPart::find($id);
        if ($this->selectedPart) {
            $this->requestItems[$this->currentItemIndex]['equipment_part_id'] = $this->selectedPart->id;
            $this->requestItems[$this->currentItemIndex]['part_details'] = [
                'name' => $this->selectedPart->name,
                'part_number' => $this->selectedPart->part_number,
                'description' => $this->selectedPart->description
            ];
            $this->partSearch = $this->selectedPart->name;
            $this->filteredParts = [];
            $this->showPartResults = false;
        }
    }
    
    // Iniciar a busca de uma peça para um item específico
    public function searchForItem($index)
    {
        $this->currentItemIndex = $index;
        $this->partSearch = '';
        $this->filteredParts = [];
        $this->showPartResults = true;
    }

    // Open modal for creating a new request
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->isEditing = false;
        $this->requestId = null;
    }

    // Open modal for editing an existing request
    public function edit($id)
    {
        $this->resetForm();
        $partRequest = EquipmentPartRequest::findOrFail($id);
        
        $this->request = [
            'reference_number' => $partRequest->reference_number,
            'suggested_vendor' => $partRequest->suggested_vendor,
            'delivery_date' => $partRequest->delivery_date ? $partRequest->delivery_date->format('Y-m-d') : null,
            'remarks' => $partRequest->remarks,
        ];
        
        // Carregar os itens do pedido
        $this->requestItems = [];
        foreach ($partRequest->items as $item) {
            $part = $item->part;
            $this->requestItems[] = [
                'equipment_part_id' => $item->equipment_part_id,
                'supplier_reference' => $item->supplier_reference,
                'quantity_required' => $item->quantity_required,
                'unit' => $item->unit,
                'part_details' => $part ? [
                    'name' => $part->name,
                    'part_number' => $part->part_number,
                    'description' => $part->description
                ] : null
            ];
        }
        
        // Se não houver itens, criar um item vazio
        if (empty($this->requestItems)) {
            $this->requestItems = [
                [
                    'equipment_part_id' => '',
                    'supplier_reference' => '',
                    'quantity_required' => 1,
                    'unit' => 'pcs',
                    'part_details' => null
                ]
            ];
        }
        
        $this->existingImages = $partRequest->images()->orderBy('order')->get();
        
        $this->showModal = true;
        $this->isEditing = true;
        $this->requestId = $id;
    }

    // Close modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    // Remove an image from the upload list
    public function removeImage($index)
    {
        if (isset($this->images[$index])) {
            unset($this->images[$index]);
            if (isset($this->imageCaptions[$index])) {
                unset($this->imageCaptions[$index]);
            }
            $this->images = array_values($this->images);
            $this->imageCaptions = array_values($this->imageCaptions);
        }
    }

    // Método chamado quando imagens são carregadas via wire:model
    public function updatedImages()
    {
        // Validar imagens quando forem carregadas
        $this->validate([
            'images.*' => 'image|max:5120', // 5MB max por imagem
        ], [
            'images.*.image' => 'O arquivo deve ser uma imagem',
            'images.*.max' => 'O tamanho da imagem não pode exceder 5MB',
        ]);
        
        // Inicializar legendas para as novas imagens
        foreach ($this->images as $index => $image) {
            if (!isset($this->imageCaptions[$index])) {
                // Usar o nome do arquivo como legenda inicial
                $this->imageCaptions[$index] = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            }
        }
    }
    
    // Remove an existing image
    public function removeExistingImage($id)
    {
        $image = EquipmentPartRequestImage::find($id);
        if ($image) {
            // Delete the file from storage
            Storage::delete('public/' . $image->image_path);
            $image->delete();
            
            // Refresh the list of existing images
            if ($this->requestId) {
                $this->existingImages = EquipmentPartRequestImage::where('request_id', $this->requestId)
                    ->orderBy('order')
                    ->get();
            }
        }
    }

    // Save request
    public function save()
    {
        $this->validate();
        
        try {
            if ($this->isEditing) {
                $partRequest = EquipmentPartRequest::findOrFail($this->requestId);
                $partRequest->update([
                    'reference_number' => $this->request['reference_number'],
                    'suggested_vendor' => $this->request['suggested_vendor'],
                    'delivery_date' => !empty($this->request['delivery_date']) ? $this->request['delivery_date'] : null,
                    'remarks' => $this->request['remarks'],
                ]);
                
                // Excluir os itens existentes e adicionar os novos
                $partRequest->items()->delete();
                
                foreach ($this->requestItems as $item) {
                    EquipmentPartRequestItem::create([
                        'request_id' => $partRequest->id,
                        'equipment_part_id' => $item['equipment_part_id'],
                        'supplier_reference' => $item['supplier_reference'],
                        'quantity_required' => $item['quantity_required'],
                        'unit' => $item['unit'],
                    ]);
                }
                
                $message = 'Part request updated successfully!';
            } else {
                $partRequest = EquipmentPartRequest::create([
                    'reference_number' => $this->request['reference_number'],
                    'suggested_vendor' => $this->request['suggested_vendor'],
                    'delivery_date' => !empty($this->request['delivery_date']) ? $this->request['delivery_date'] : null,
                    'remarks' => $this->request['remarks'],
                    'equipment_part_id' => null, // Agora o campo é nullable
                    'requested_by' => Auth::id(),
                    'status' => 'pending',
                ]);
                
                // Criar os itens do pedido
                foreach ($this->requestItems as $item) {
                    EquipmentPartRequestItem::create([
                        'request_id' => $partRequest->id,
                        'equipment_part_id' => $item['equipment_part_id'],
                        'supplier_reference' => $item['supplier_reference'],
                        'quantity_required' => $item['quantity_required'],
                        'unit' => $item['unit'],
                    ]);
                }
                
                $message = 'Part request created successfully!';
            }
            
            // Handle image uploads
            if (!empty($this->images)) {
                $order = count($this->existingImages);
                foreach ($this->images as $index => $image) {
                    $path = $image->store('part_requests/' . $partRequest->id, 'public');
                    
                    EquipmentPartRequestImage::create([
                        'request_id' => $partRequest->id,
                        'image_path' => $path,
                        'original_filename' => $image->getClientOriginalName(),
                        'caption' => $this->imageCaptions[$index] ?? null,
                        'file_size' => $image->getSize(),
                        'order' => $order++,
                    ]);
                }
            }
            
            $this->dispatch('notify', type: 'success', message: $message);
            $this->closeModal();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    // Confirm deletion
    public function confirmDelete($id)
    {
        $this->requestId = $id;
        $this->showDeleteModal = true;
    }

    // Delete request
    public function delete()
    {
        try {
            $partRequest = EquipmentPartRequest::findOrFail($this->requestId);
            
            // Delete associated images from storage
            foreach ($partRequest->images as $image) {
                Storage::delete('public/' . $image->image_path);
            }
            
            // Delete the request (images will be deleted via cascade)
            $partRequest->delete();
            
            $this->dispatch('notify', type: 'success', message: 'Part request deleted successfully!');
            $this->closeModal();
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    // View request details
    public function viewDetails($id)
    {
        try {
            $this->viewRequest = EquipmentPartRequest::with(['items.part', 'requester', 'approver', 'images'])
                ->findOrFail($id);
            $this->showViewModal = true;
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    // Close view modal
    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewRequest = null;
    }

    // Generate PDF for a single request
    public function generatePDF($id)
    {
        try {
            // Mostrar notificação toastr antes de iniciar o download
            $this->dispatch('notify', type: 'success', message: __('livewire/maintenance/equipment-part-requests.pdf_generating'));
            
            $request = EquipmentPartRequest::with(['items.part', 'requester', 'approver', 'images'])
                ->findOrFail($id);
            
            $pdf = \PDF::loadView('livewire.equipment-part-requests.pdf.single', [
                'request' => $request
            ]);
            
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'request-' . $request->reference_number . '.pdf');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('livewire/maintenance/equipment-part-requests.pdf_error') . $e->getMessage());
        }
    }

    // Generate PDF for all filtered requests
    public function generateListPDF()
    {
        try {
            // Mostrar notificação toastr antes de iniciar o download
            $this->dispatch('notify', type: 'success', message: __('livewire/maintenance/equipment-part-requests.pdf_list_generating'));
            
            $requests = EquipmentPartRequest::with(['items.part', 'requester'])
                ->when($this->search, function ($query) {
                    return $query->where(function ($q) {
                        $q->where('reference_number', 'like', '%' . $this->search . '%')
                          ->orWhere('suggested_vendor', 'like', '%' . $this->search . '%')
                          ->orWhereHas('items', function ($q) {
                              $q->where('supplier_reference', 'like', '%' . $this->search . '%')
                                ->orWhereHas('part', function ($q) {
                                    $q->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('part_number', 'like', '%' . $this->search . '%');
                                });
                          });
                    });
                })
                ->when($this->status, function ($query) {
                    return $query->where('status', $this->status);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->get();
            
            $pdf = \PDF::loadView('livewire.equipment-part-requests.pdf.list', [
                'requests' => $requests,
                'statusOptions' => [
                    'pending' => __('livewire/maintenance/equipment-part-requests.pending'),
                    'approved' => __('livewire/maintenance/equipment-part-requests.approved'),
                    'rejected' => __('livewire/maintenance/equipment-part-requests.rejected'),
                    'ordered' => __('livewire/maintenance/equipment-part-requests.ordered'),
                    'received' => __('livewire/maintenance/equipment-part-requests.received'),
                ],
                'filters' => [
                    'search' => $this->search,
                    'status' => $this->status,
                ]
            ]);
            
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'part-requests-list.pdf');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('livewire/maintenance/equipment-part-requests.pdf_list_error') . $e->getMessage());
        }
    }
    
    // Change status
    public function changeStatus($id, $status)
    {
        try {
            $partRequest = EquipmentPartRequest::findOrFail($id);
            $partRequest->update([
                'status' => $status,
                'approved_by' => ($status === 'approved') ? Auth::id() : $partRequest->approved_by,
                'approved_at' => ($status === 'approved') ? now() : $partRequest->approved_at,
            ]);
            
            $this->dispatch('notify', type: 'success', message: 'Status updated successfully!');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error: ' . $e->getMessage());
        }
    }

    // Sorting
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $requests = EquipmentPartRequest::with(['items.part', 'requester', 'approver', 'images'])
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('reference_number', 'like', '%' . $this->search . '%')
                      ->orWhere('suggested_vendor', 'like', '%' . $this->search . '%')
                      ->orWhereHas('items', function ($q) {
                          $q->where('supplier_reference', 'like', '%' . $this->search . '%')
                            ->orWhereHas('part', function ($q) {
                                $q->where('name', 'like', '%' . $this->search . '%')
                                  ->orWhere('part_number', 'like', '%' . $this->search . '%');
                            });
                      });
                });
            })
            ->when($this->status, function ($query) {
                return $query->where('status', $this->status);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.equipment-part-requests', [
            'requests' => $requests,
            'statusOptions' => [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'ordered' => 'Ordered',
                'received' => 'Received',
            ],
        ]);
    }
}

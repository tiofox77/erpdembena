<?php

namespace App\Livewire\Mrp;

use App\Models\MaintenanceArea;
use App\Models\SupplyChain\InventoryLocation as Location;
use App\Models\Mrp\Resource;
use App\Models\Mrp\ResourceType;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class ResourcesManagement extends Component
{
    use WithPagination;

    // Properties for filters and search
    public $search = '';
    public $typeFilter = '';
    public $departmentFilter = '';
    public $locationFilter = '';
    public $statusFilter = '';
    
    // Sorting properties
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Properties for resource form
    public $resource = [];
    public $resourceType = [];
    public $resourceToDelete;
    public $resourceTypeToDelete;
    public $selectedResource;
    
    // Modal controls
    public $showModal = false;
    public $showResourceTypeModal = false;
    public $showDeleteModal = false; 
    public $showDeleteTypeModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $editTypeMode = false;
    
    // Pagination configuration
    protected $paginationTheme = 'tailwind';
    
    // Listening for events
    protected $listeners = [
        'refresh' => '$refresh'
    ];
    
    // Reset properties when component initialized
    public function mount()
    {
        Log::info('=== INÍCIO DO MÉTODO MOUNT EM ResourcesManagement ===');
        
        // Certifique-se de que as propriedades de ordenação estão definidas
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
        
        // Reset dos filtros
        $this->search = '';
        $this->typeFilter = '';
        $this->departmentFilter = '';
        $this->locationFilter = '';
        $this->statusFilter = '';
        
        $this->resetResource();
        $this->resetResourceType();
        
        Log::info('=== FIM DO MÉTODO MOUNT EM ResourcesManagement ===', [
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection
        ]);
    }
    
    // Reset pagination when filtering
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingTypeFilter()
    {
        $this->resetPage();
    }
    
    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }
    
    public function updatingLocationFilter()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    // Reset all filters
    public function resetFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->departmentFilter = '';
        $this->locationFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }
    
    // Reset resource form
    public function resetResource()
    {
        $this->resource = [
            'id' => null,
            'name' => '',
            'description' => '',
            'resource_type_id' => '',
            'department_id' => null,
            'location_id' => null,
            'capacity' => 0,
            'capacity_uom' => 'hours',
            'efficiency_factor' => 100,
            'active' => true,
        ];
        $this->editMode = false;
    }
    
    // Reset resource type form
    public function resetResourceType()
    {
        $this->resourceType = [
            'id' => null,
            'name' => '',
            'description' => '',
            'active' => true,
        ];
        $this->editTypeMode = false;
    }
    
    // Open modal to create a new resource
    public function create()
    {
        $this->resetResource();
        $this->showModal = true;
    }
    
    // Open modal to create a new resource type
    public function createResourceType()
    {
        $this->resetResourceType();
        $this->showResourceTypeModal = true;
    }
    
    // Open modal to edit a resource
    public function edit($id)
    {
        $resource = Resource::findOrFail($id);
        $this->resource = $resource->toArray();
        $this->editMode = true;
        $this->showModal = true;
        if ($this->showViewModal) {
            $this->closeViewModal();
        }
    }
    
    // Open modal to edit a resource type
    public function editResourceType($id)
    {
        $resourceType = ResourceType::findOrFail($id);
        $this->resourceType = $resourceType->toArray();
        $this->editTypeMode = true;
        $this->showResourceTypeModal = true;
    }
    
    // Validation rules for resource
    protected function rules()
    {
        return [
            'resource.name' => 'required|string|max:255',
            'resource.description' => 'nullable|string|max:1000',
            'resource.resource_type_id' => 'required|exists:resource_types,id',
            'resource.department_id' => 'nullable',
            'resource.location_id' => 'nullable',
            'resource.capacity' => 'required|numeric|min:0',
            'resource.capacity_uom' => 'required|string|in:hours,units,volume,weight',
            'resource.efficiency_factor' => 'required|numeric|min:1|max:200',
            'resource.active' => 'boolean',
        ];
    }

    /**
     * Custom validation messages
     */
    protected function messages()
    {
        return [
            'resource.name.required' => trans('validation.resource_name_required'),
            'resource.name.max' => trans('validation.resource_name_max'),
            'resource.resource_type_id.required' => trans('validation.resource_type_required'),
            'resource.resource_type_id.exists' => trans('validation.resource_type_exists'),
            'resource.department_id.exists' => trans('validation.department_exists'),
            'resource.location_id.exists' => trans('validation.location_exists'),
            'resource.capacity.required' => trans('validation.capacity_required'),
            'resource.capacity.numeric' => trans('validation.capacity_numeric'),
            'resource.capacity.min' => trans('validation.capacity_min'),
            'resource.capacity_uom.required' => trans('validation.capacity_uom_required'),
            'resource.capacity_uom.in' => trans('validation.capacity_uom_in'),
            'resource.efficiency_factor.required' => trans('validation.efficiency_factor_required'),
            'resource.efficiency_factor.numeric' => trans('validation.efficiency_factor_numeric'),
            'resource.efficiency_factor.min' => trans('validation.efficiency_factor_min'),
            'resource.efficiency_factor.max' => trans('validation.efficiency_factor_max'),
        ];
    }
    
    // Validation rules for resource type
    protected function rulesForResourceType()
    {
        return [
            'resourceType.name' => 'required|string|max:255',
            'resourceType.description' => 'nullable|string',
            'resourceType.active' => 'boolean',
        ];
    }
    
    // Save resource
    public function save()
    {
        Log::info('=== INÍCIO DO MÉTODO save EM ResourcesManagement ===');
        
        // Validate form data with custom messages
        $this->validate($this->rules(), $this->messages());
        
        try {
            // Ensure department_id and location_id are null if empty
            if (empty($this->resource['department_id'])) {
                $this->resource['department_id'] = null;
            }
            
            if (empty($this->resource['location_id'])) {
                $this->resource['location_id'] = null;
            }
            
            Log::info('Dados do recurso antes de salvar:', [
                'resource' => $this->resource
            ]);
            
            if (isset($this->resource['id'])) {
                $resource = Resource::findOrFail($this->resource['id']);
                $resource->update($this->resource);
                $this->dispatch('notify',
                    type: 'success',
                    title: trans('messages.success'),
                    message: trans('messages.resource_updated_successfully')
                );
            } else {
                Resource::create($this->resource);
                $this->dispatch('notify',
                    type: 'success',
                    title: trans('messages.success'),
                    message: trans('messages.resource_created_successfully')
                );
            }
            
            $this->resetResource();
            $this->showModal = false;
            
            Log::info('=== FIM DO MÉTODO save EM ResourcesManagement ===', [
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('=== ERRO NO MÉTODO save EM ResourcesManagement ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify',
                type: 'error',
                title: trans('messages.error'),
                message: trans('messages.error_saving_resource')
            );
        }
    }
    
    // Save resource type
    public function saveResourceType()
    {
        Log::info('=== INÍCIO DO MÉTODO saveResourceType EM ResourcesManagement ===');
        
        $this->validate($this->rulesForResourceType());
        
        try {
            if (isset($this->resourceType['id'])) {
                $resourceType = ResourceType::findOrFail($this->resourceType['id']);
                $resourceType->update($this->resourceType);
                $this->dispatch('notify',
                    type: 'success',
                    title: trans('messages.success'),
                    message: trans('messages.resource_type_updated_successfully')
                );
            } else {
                ResourceType::create($this->resourceType);
                $this->dispatch('notify',
                    type: 'success',
                    title: trans('messages.success'),
                    message: trans('messages.resource_type_created_successfully')
                );
            }
            
            $this->resetResourceType();
            $this->showResourceTypeModal = false;
            
            Log::info('=== FIM DO MÉTODO saveResourceType EM ResourcesManagement ===', [
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('=== ERRO NO MÉTODO saveResourceType EM ResourcesManagement ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify',
                type: 'error',
                title: trans('messages.error'),
                message: trans('messages.error_saving_resource_type')
            );
        }
    }
    
    // Confirm delete resource
    public function confirmDelete($id)
    {
        $this->resourceToDelete = Resource::findOrFail($id);
        $this->showDeleteModal = true;
    }
    
    // Confirm delete resource type
    public function confirmDeleteResourceType($id)
    {
        $this->resourceTypeToDelete = ResourceType::findOrFail($id);
        $this->showDeleteTypeModal = true;
    }
    
    // Delete resource
    public function delete()
    {
        Log::info('=== INÍCIO DO MÉTODO delete EM ResourcesManagement ===', [
            'resourceToDelete' => $this->resourceToDelete
        ]);
        
        if ($this->resourceToDelete) {
            try {
                $resource = Resource::findOrFail($this->resourceToDelete);
                $resource->delete();
                
                $this->dispatch('notify',
                    type: 'success',
                    title: trans('messages.success'),
                    message: trans('messages.resource_deleted_successfully')
                );
                
                Log::info('=== FIM DO MÉTODO delete EM ResourcesManagement ===', [
                    'status' => 'success'
                ]);
            } catch (\Exception $e) {
                Log::error('=== ERRO NO MÉTODO delete EM ResourcesManagement ===', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->dispatch('notify',
                    type: 'error',
                    title: trans('messages.error'),
                    message: trans('messages.error_deleting_resource')
                );
            }
            $this->resourceToDelete = null;
            $this->showDeleteModal = false;
        }
    }
    
    // Delete resource type
    public function deleteResourceType()
    {
        Log::info('=== INÍCIO DO MÉTODO deleteResourceType EM ResourcesManagement ===', [
            'resourceTypeToDelete' => $this->resourceTypeToDelete
        ]);
        
        if ($this->resourceTypeToDelete) {
            try {
                $resourceType = ResourceType::findOrFail($this->resourceTypeToDelete);
                
                // Check if this type is being used by any resources
                $resourcesCount = Resource::where('resource_type_id', $this->resourceTypeToDelete)->count();
                
                if ($resourcesCount > 0) {
                    $this->dispatch('notify',
                        type: 'error',
                        title: trans('messages.error'),
                        message: trans('messages.resource_type_in_use', ['count' => $resourcesCount])
                    );
                    
                    Log::warning('=== AVISO NO MÉTODO deleteResourceType EM ResourcesManagement ===', [
                        'message' => "Tipo de recurso em uso",
                        'resourcesCount' => $resourcesCount
                    ]);
                } else {
                    $resourceType->delete();
                    
                    $this->dispatch('notify',
                        type: 'success',
                        title: trans('messages.success'),
                        message: trans('messages.resource_type_deleted_successfully')
                    );
                    
                    Log::info('=== FIM DO MÉTODO deleteResourceType EM ResourcesManagement ===', [
                        'status' => 'success'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('=== ERRO NO MÉTODO deleteResourceType EM ResourcesManagement ===', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->dispatch('notify',
                    type: 'error',
                    title: trans('messages.error'),
                    message: trans('messages.error_deleting_resource_type')
                );
            }
            $this->resourceTypeToDelete = null;
            $this->showDeleteTypeModal = false;
        }
    }
    
    // View resource details
    public function view($id)
    {
        $this->selectedResource = Resource::with(['resourceType', 'department', 'location'])
            ->findOrFail($id);
        $this->showViewModal = true;
    }
    
    // Close view modal
    public function closeViewModal()
    {
        $this->selectedResource = null;
        $this->showViewModal = false;
    }
    
    // Close create/edit modal
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetResource();
    }
    
    // Close resource type modal
    public function closeResourceTypeModal()
    {
        $this->showResourceTypeModal = false;
        $this->resetResourceType();
    }
    
    // Toggle resource status
    public function toggleStatus($id)
    {
        Log::info('=== INÍCIO DO MÉTODO toggleStatus EM ResourcesManagement ===', [
            'id' => $id
        ]);
        
        try {
            $resource = Resource::findOrFail($id);
            $resource->active = !$resource->active;
            $resource->save();
            
            if ($this->showViewModal && $this->selectedResource && $this->selectedResource->id === $id) {
                $this->selectedResource = $resource->fresh(['resourceType', 'department', 'location', 'createdBy', 'updatedBy']);
            }
            
            $statusMessage = $resource->active ? 
                trans('messages.resource_activated_successfully') : 
                trans('messages.resource_deactivated_successfully');
                
            $this->dispatch('notify',
                type: 'success',
                title: trans('messages.success'),
                message: $statusMessage
            );
            
            Log::info('=== FIM DO MÉTODO toggleStatus EM ResourcesManagement ===', [
                'status' => 'success',
                'new_status' => $resource->active
            ]);
        } catch (\Exception $e) {
            Log::error('=== ERRO NO MÉTODO toggleStatus EM ResourcesManagement ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify',
                type: 'error',
                title: trans('messages.error'),
                message: trans('messages.error_updating_resource_status')
            );
        }
    }
    
    // Toggle resource type status
    public function toggleResourceTypeStatus($id)
    {
        Log::info('=== INÍCIO DO MÉTODO toggleResourceTypeStatus EM ResourcesManagement ===', [
            'id' => $id
        ]);
        
        try {
            $resourceType = ResourceType::findOrFail($id);
            $resourceType->active = !$resourceType->active;
            $resourceType->save();
            
            $statusMessage = $resourceType->active ? 
                trans('messages.resource_type_activated_successfully') : 
                trans('messages.resource_type_deactivated_successfully');
                
            $this->dispatch('notify',
                type: 'success',
                title: trans('messages.success'),
                message: $statusMessage
            );
            
            Log::info('=== FIM DO MÉTODO toggleResourceTypeStatus EM ResourcesManagement ===', [
                'status' => 'success',
                'new_status' => $resourceType->active
            ]);
        } catch (\Exception $e) {
            Log::error('=== ERRO NO MÉTODO toggleResourceTypeStatus EM ResourcesManagement ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify',
                type: 'error',
                title: trans('messages.error'),
                message: trans('messages.error_updating_resource_type_status')
            );
        }
    }
    
    // Get resource types for dropdown
    public function getResourceTypesProperty()
    {
        return ResourceType::where('active', true)->orderBy('name', 'asc')->get();
    }
    
    // Get departments for dropdown (using maintenance areas)
    public function getDepartmentsProperty()
    {
        return MaintenanceArea::orderBy('name', 'asc')->get();
    }
    
    // Get locations for dropdown
    public function getLocationsProperty()
    {
        return Location::orderBy('name', 'asc')->get();
    }
    
    // Get resources for filter dropdown
    public function getResourceTypesForFilterProperty()
    {
        return ResourceType::orderBy('name', 'asc')->get();
    }
    
    // Get departments for filter dropdown (using maintenance areas)
    public function getDepartmentsForFilterProperty()
    {
        return MaintenanceArea::orderBy('name', 'asc')->get();
    }
    
    // Get locations for filter dropdown
    public function getLocationsForFilterProperty()
    {
        return Location::orderBy('name', 'asc')->get();
    }
    
    // Handle column sorting
    public function sortBy($field)
    {
        Log::info('=== INÍCIO DO MÉTODO sortBy EM ResourcesManagement ===', [
            'field' => $field,
            'previous_sortField' => $this->sortField,
            'previous_sortDirection' => $this->sortDirection
        ]);
        
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        Log::info('=== FIM DO MÉTODO sortBy EM ResourcesManagement ===', [
            'new_sortField' => $this->sortField,
            'new_sortDirection' => $this->sortDirection
        ]);
    }
    
    // Render the component
    public function render()
    {
        Log::info('=== INÍCIO DO MÉTODO render EM ResourcesManagement ===', [
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'filters' => [
                'search' => $this->search,
                'typeFilter' => $this->typeFilter,
                'departmentFilter' => $this->departmentFilter,
                'locationFilter' => $this->locationFilter,
                'statusFilter' => $this->statusFilter
            ]
        ]);
        
        // Para garantir que as propriedades de ordenação existam
        if (!isset($this->sortField)) {
            $this->sortField = 'name';
        }
        
        if (!isset($this->sortDirection)) {
            $this->sortDirection = 'asc';
        }
        
        $query = Resource::with(['resourceType', 'department', 'location'])
            ->when($this->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('resourceType', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($this->typeFilter, function ($query, $type) {
                $query->where('resource_type_id', $type);
            })
            ->when($this->departmentFilter, function ($query, $department) {
                $query->where('department_id', $department);
            })
            ->when($this->locationFilter, function ($query, $location) {
                $query->where('location_id', $location);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter === '1');
            })
            ->orderBy($this->sortField, $this->sortDirection);
            
        $resources = $query->paginate(10);
        
        // Ensure departments and locations data is loaded and logged
        $departments = $this->departments;
        $locations = $this->locations;
        
        Log::info('=== INFO SOBRE DEPARTAMENTOS E LOCALIZAÇÕES ===', [
            'departments_count' => count($departments),
            'locations_count' => count($locations)
        ]);
        
        $viewData = [
            'resources' => $resources,
            'resourceTypesFilter' => $this->resourceTypesForFilter,
            'departmentsFilter' => $this->departmentsForFilter,
            'locationsFilter' => $this->locationsForFilter,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'resourceTypes' => $this->resourceTypes,
            'departments' => $departments,
            'locations' => $locations,
        ];
        
        Log::info('=== FIM DO MÉTODO render EM ResourcesManagement ===', [
            'viewData' => array_keys($viewData)
        ]);
        
        return view('livewire.mrp.resources-management', $viewData);
    }
}

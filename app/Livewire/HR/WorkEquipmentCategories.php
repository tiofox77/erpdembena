<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\WorkEquipmentCategory;
use Livewire\Component;
use Livewire\WithPagination;

class WorkEquipmentCategories extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    // Form properties
    public ?int $category_id = null;
    public string $name = '';
    public ?string $description = null;
    public string $color = '#3498db';
    public bool $is_active = true;

    // Modal flags
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $isEditing = false;

    // Lifecycle hooks
    public function hydrate(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Listeners
    protected $listeners = ['refreshWorkEquipmentCategories' => '$refresh'];

    // Rules
    protected function rules(): array
    {
        return [
            'name' => 'required|min:3|max:255',
            'description' => 'nullable|max:1000',
            'color' => 'required|string|max:20',
            'is_active' => 'boolean',
        ];
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset([
            'category_id', 'name', 'description'
        ]);
        $this->color = '#3498db';
        $this->is_active = true;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(WorkEquipmentCategory $category): void
    {
        $this->category_id = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->color = $category->color ?? '#3498db';
        $this->is_active = $category->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function confirmDelete(WorkEquipmentCategory $category): void
    {
        $this->category_id = $category->id;
        $this->showDeleteModal = true;
    }

    public function save(): void
    {
        $validatedData = $this->validate();

        if ($this->isEditing) {
            $category = WorkEquipmentCategory::find($this->category_id);
            if ($category) {
                $category->update($validatedData);
                $this->dispatch('notify', 
                    type: 'warning', 
                    title: __('messages.success'),
                    message: __('messages.work_equipment_category_updated')
                );
            }
        } else {
            WorkEquipmentCategory::create($validatedData);
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: __('messages.work_equipment_category_created')
            );
        }

        $this->showModal = false;
        $this->reset([
            'category_id', 'name', 'description', 'color'
        ]);
        $this->is_active = true;
    }

    public function delete()
    {
        $category = WorkEquipmentCategory::find($this->category_id);
        if ($category) {
            // Nota: A verificação de equipamentos associados está comentada até que o modelo WorkEquipment seja implementado
            // @todo Descomente quando o modelo WorkEquipment for implementado
            // $equipmentCount = $category->workEquipment()->count();
            // if ($equipmentCount > 0) {
            //     $this->dispatch('notify', 
            //         type: 'error', 
            //         title: __('messages.error'),
            //         message: __('messages.work_equipment_category_in_use')
            //     );
            //     $this->showDeleteModal = false;
            //     return;
            // }
            
            $category->delete();
            $this->dispatch('notify', 
                type: 'error', 
                title: __('messages.success'),
                message: __('messages.work_equipment_category_deleted')
            );
        }
        $this->showDeleteModal = false;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }
    
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }
    
    public function resetFilters(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Calcula a cor de contraste ideal (preto ou branco) baseado na cor de fundo
     *
     * @param string $hexColor Cor em formato hexadecimal
     * @return string Retorna '#ffffff' (branco) ou '#000000' (preto)
     */
    public function getContrastColor(string $hexColor): string
    {
        // Remover o # se presente
        $hex = ltrim($hexColor, '#');
        
        // Converter para RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Calcular a luminância (percepção humana de brilho)
        // Fórmula baseada em WCAG 2.0
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        // Se a luminância for maior que 0.5, a cor é clara, então use texto preto
        // Caso contrário, use texto branco para melhor contraste
        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }
    
    public function render()
    {
        $categories = WorkEquipmentCategory::where('name', 'like', "%{$this->search}%")
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.hr.work-equipment-categories', [
            'categories' => $categories,
        ]);
    }
}

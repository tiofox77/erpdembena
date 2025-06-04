<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenanceCategory as Category;

class MaintenanceCategory extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Para criação/edição de categorias
    public $categoryId;
    public $name;
    public $description;
    public $color;

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'description' => 'nullable|max:1000',
        'color' => 'nullable|max:7',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->description = '';
        $this->color = '#3b82f6'; // Blue default
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->categoryId) {
            $category = Category::find($this->categoryId);
            if ($category) {
                $category->update([
                    'name' => $this->name,
                    'description' => $this->description,
                    'color' => $this->color,
                ]);
                session()->flash('message', 'Categoria atualizada com sucesso!');
            }
        } else {
            Category::create([
                'name' => $this->name,
                'description' => $this->description,
                'color' => $this->color,
            ]);
            session()->flash('message', 'Categoria criada com sucesso!');
        }

        $this->resetForm();
        $this->dispatch('close-modal');
    }

    public function edit($id)
    {
        $category = Category::find($id);
        if ($category) {
            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->color = $category->color;
        }
    }

    public function delete($id)
    {
        $category = Category::find($id);
        if ($category) {
            // Verificar se existem equipamentos ou tarefas vinculados a esta categoria
            if ($category->equipment()->count() > 0 || $category->tasks()->count() > 0) {
                session()->flash('error', 'Não é possível excluir esta categoria porque existem equipamentos ou tarefas vinculados a ela.');
                return;
            }

            $category->delete();
            session()->flash('message', 'Categoria removida com sucesso!');
        }
    }

    public function render()
    {
        $query = Category::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $categories = $query->paginate($this->perPage);

        return view('livewire.maintenance-category', [
            'categories' => $categories
        ]);
    }
}

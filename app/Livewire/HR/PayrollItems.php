<?php

namespace App\Livewire\HR;

use App\Models\HR\Payroll;
use App\Models\HR\PayrollItem;
use App\Models\HR\PayrollPeriod;
use Livewire\Component;
use Livewire\WithPagination;

class PayrollItems extends Component
{
    use WithPagination;

    // Propriedades para filtros e ordenação
    public $searchQuery = '';
    public $sortField = 'type';
    public $sortDirection = 'asc';
    public $filters = [
        'type' => '',
        'payroll_id' => '',
        'period_id' => '',
    ];

    // Propriedades para formulário
    public $item_id;
    public $payroll_id;
    public $type;
    public $description;
    public $amount;
    public $is_taxable = false;

    // Estados da UI
    public $showItemModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;

    protected $listeners = ['refreshItems' => '$refresh'];

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'sortField' => ['except' => 'type'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $rules = [
        'payroll_id' => 'required|exists:payrolls,id',
        'type' => 'required|string|in:allowance,bonus,overtime,deduction,tax,social_security',
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'is_taxable' => 'boolean',
    ];

    public function mount()
    {
        $this->resetData();
    }

    public function render()
    {
        $query = PayrollItem::query()
            ->with('payroll', 'payroll.employee')
            ->when($this->searchQuery, function ($query) {
                $query->where('description', 'like', '%' . $this->searchQuery . '%')
                    ->orWhereHas('payroll.employee', function ($q) {
                        $q->where(function ($q) {
                            $q->where('first_name', 'like', '%' . $this->searchQuery . '%')
                              ->orWhere('last_name', 'like', '%' . $this->searchQuery . '%');
                        });
                    });
            })
            ->when($this->filters['type'], function ($query) {
                $query->where('type', $this->filters['type']);
            })
            ->when($this->filters['payroll_id'], function ($query) {
                $query->where('payroll_id', $this->filters['payroll_id']);
            })
            ->when($this->filters['period_id'], function ($query) {
                $query->whereHas('payroll', function ($q) {
                    $q->where('payroll_period_id', $this->filters['period_id']);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $items = $query->paginate(10);
        $payrolls = Payroll::with('employee')->get();
        $periods = PayrollPeriod::orderBy('start_date', 'desc')->get();

        return view('livewire.hr.payroll-items', [
            'items' => $items,
            'payrolls' => $payrolls,
            'periods' => $periods,
        ]);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->filters = [
            'type' => '',
            'payroll_id' => '',
            'period_id' => '',
        ];
    }

    public function openItemModal()
    {
        $this->resetData();
        $this->showItemModal = true;
    }

    public function editItem($id)
    {
        $this->resetData();
        $this->isEditing = true;
        $this->item_id = $id;
        
        $item = PayrollItem::findOrFail($id);
        
        $this->payroll_id = $item->payroll_id;
        $this->type = $item->type;
        $this->description = $item->description;
        $this->amount = $item->amount;
        $this->is_taxable = $item->is_taxable;
        
        $this->showItemModal = true;
    }

    public function closeModal()
    {
        $this->showItemModal = false;
        $this->showDeleteModal = false;
    }

    public function confirmDelete($id)
    {
        $this->item_id = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem()
    {
        $item = PayrollItem::findOrFail($this->item_id);
        
        // Verificar se o período está fechado
        $payroll = $item->payroll;
        $period = $payroll->payrollPeriod;
        
        if ($period && $period->status === PayrollPeriod::STATUS_CLOSED) {
            session()->flash('error', 'Cannot delete item from a closed payroll period.');
            $this->closeModal();
            return;
        }
        
        $item->delete();
        session()->flash('message', 'Payroll item deleted successfully.');
        $this->closeModal();
    }

    public function saveItem()
    {
        $this->validate();
        
        // Verificar se o período está fechado
        $payroll = Payroll::findOrFail($this->payroll_id);
        $period = $payroll->payrollPeriod;
        
        if ($period && $period->status === PayrollPeriod::STATUS_CLOSED) {
            session()->flash('error', 'Cannot add or edit items in a closed payroll period.');
            $this->closeModal();
            return;
        }
        
        $data = [
            'payroll_id' => $this->payroll_id,
            'type' => $this->type,
            'description' => $this->description,
            'amount' => $this->amount,
            'is_taxable' => $this->is_taxable,
        ];
        
        if ($this->isEditing) {
            $item = PayrollItem::findOrFail($this->item_id);
            $item->update($data);
            session()->flash('message', 'Payroll item updated successfully.');
        } else {
            PayrollItem::create($data);
            session()->flash('message', 'Payroll item created successfully.');
        }
        
        $this->closeModal();
        $this->resetData();
    }

    private function resetData()
    {
        $this->item_id = null;
        $this->payroll_id = '';
        $this->type = '';
        $this->description = '';
        $this->amount = '';
        $this->is_taxable = false;
        $this->isEditing = false;
    }
}

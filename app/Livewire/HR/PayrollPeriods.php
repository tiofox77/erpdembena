<?php

namespace App\Livewire\HR;

use App\Models\HR\PayrollPeriod;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class PayrollPeriods extends Component
{
    use WithPagination;

    // Propriedades para filtros e ordenação
    public $searchQuery = '';
    public $sortField = 'start_date';
    public $sortDirection = 'desc';
    public $filters = [
        'status' => '',
        'date_range' => '',
    ];

    // Propriedades para formulário
    public $period_id;
    public $name;
    public $start_date;
    public $end_date;
    public $payment_date;
    public $status;
    public $remarks;

    // Estados da UI
    public $showPeriodModal = false;
    public $showDeleteModal = false;
    public $isEditing = false;

    protected $listeners = ['refreshPeriods' => '$refresh'];

    protected $queryString = [
        'searchQuery' => ['except' => ''],
        'sortField' => ['except' => 'start_date'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $rules = [
        'name' => 'required|string|max:100',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'payment_date' => 'required|date|after_or_equal:end_date',
        'status' => 'required|string|in:open,processing,closed',
        'remarks' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $this->resetData();
    }

    public function render()
    {
        $query = PayrollPeriod::query()
            ->when($this->searchQuery, function ($query) {
                $query->where('name', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('remarks', 'like', '%' . $this->searchQuery . '%');
            })
            ->when($this->filters['status'], function ($query) {
                $query->where('status', $this->filters['status']);
            })
            ->when($this->filters['date_range'], function ($query) {
                $dates = explode(' to ', $this->filters['date_range']);
                if (count($dates) == 2) {
                    $start = Carbon::parse($dates[0]);
                    $end = Carbon::parse($dates[1]);
                    $query->where(function ($q) use ($start, $end) {
                        $q->whereBetween('start_date', [$start, $end])
                            ->orWhereBetween('end_date', [$start, $end]);
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $periods = $query->paginate(10);

        return view('livewire.hr.payroll-periods', [
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
            'status' => '',
            'date_range' => '',
        ];
    }

    public function openPeriodModal()
    {
        $this->resetData();
        $this->status = PayrollPeriod::STATUS_OPEN;
        $this->showPeriodModal = true;
    }

    public function editPeriod($id)
    {
        $this->resetData();
        $this->isEditing = true;
        $this->period_id = $id;
        
        $period = PayrollPeriod::findOrFail($id);
        
        $this->name = $period->name;
        $this->start_date = $period->start_date->format('Y-m-d');
        $this->end_date = $period->end_date->format('Y-m-d');
        $this->payment_date = $period->payment_date->format('Y-m-d');
        $this->status = $period->status;
        $this->remarks = $period->remarks;
        
        $this->showPeriodModal = true;
    }

    public function closeModal()
    {
        $this->showPeriodModal = false;
        $this->showDeleteModal = false;
    }

    public function confirmDelete($id)
    {
        $this->period_id = $id;
        $this->showDeleteModal = true;
    }

    public function deletePeriod()
    {
        $period = PayrollPeriod::findOrFail($this->period_id);
        
        // Verificar se existem folhas de pagamento associadas
        if ($period->payrolls()->count() > 0) {
            session()->flash('error', 'Cannot delete this period as it has payrolls attached.');
            $this->closeModal();
            return;
        }
        
        $period->delete();
        session()->flash('message', 'Payroll period deleted successfully.');
        $this->closeModal();
    }

    public function savePeriod()
    {
        $this->validate();
        
        $data = [
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'payment_date' => $this->payment_date,
            'status' => $this->status,
            'remarks' => $this->remarks,
        ];
        
        if ($this->isEditing) {
            $period = PayrollPeriod::findOrFail($this->period_id);
            $period->update($data);
            session()->flash('message', 'Payroll period updated successfully.');
        } else {
            PayrollPeriod::create($data);
            session()->flash('message', 'Payroll period created successfully.');
        }
        
        $this->closeModal();
        $this->resetData();
    }

    private function resetData()
    {
        $this->period_id = null;
        $this->name = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->payment_date = '';
        $this->status = '';
        $this->remarks = '';
        $this->isEditing = false;
    }
}

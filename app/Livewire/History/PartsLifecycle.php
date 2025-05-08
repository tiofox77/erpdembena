<?php

namespace App\Livewire\History;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EquipmentPart;
use App\Models\StockTransaction;
use App\Models\SupplyChain\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PartsLifecycle extends Component
{
    use WithPagination;

    // Filters
    public $partId;
    public $supplierId;
    public $partCategory;
    public $dateRange = 'last-month';
    public $startDate;
    public $endDate;
    public $transactionType;
    public $searchQuery = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Data collections
    public $parts = [];
    public $suppliers = [];
    public $categories = [];
    public $transactionTypes = [
        'all' => 'All Transactions',
        'purchase' => 'Purchase',
        'usage' => 'Usage in Maintenance',
        'return' => 'Return to Supplier',
        'adjustment' => 'Inventory Adjustment',
        'transfer' => 'Location Transfer'
    ];

    // Summary data
    public $totalParts = 0;
    public $totalValue = 0;
    public $totalTransactions = 0;
    public $mostUsedPart = null;
    public $topSupplier = null;

    protected $queryString = [
        'partId' => ['except' => ''],
        'supplierId' => ['except' => ''],
        'partCategory' => ['except' => ''],
        'dateRange' => ['except' => 'last-month'],
        'transactionType' => ['except' => ''],
        'searchQuery' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc']
    ];

    public function mount()
    {
        $this->setDateRange($this->dateRange);
        $this->loadCategories();
        $this->loadParts();
        $this->loadSuppliers();
        $this->loadSummaryData();
    }

    public function setDateRange($range)
    {
        $this->dateRange = $range;

        switch ($range) {
            case 'last-week':
                $this->startDate = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-quarter':
                $this->startDate = Carbon::now()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'last-year':
                $this->startDate = Carbon::now()->subYear()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->format('Y-m-d');
                break;
            case 'custom':
                // Keep existing custom dates if already set
                if (!$this->startDate) {
                    $this->startDate = Carbon::now()->subMonth()->format('Y-m-d');
                }
                if (!$this->endDate) {
                    $this->endDate = Carbon::now()->format('Y-m-d');
                }
                break;
        }

        $this->resetPage();
    }

    public function updatedDateRange($value)
    {
        $this->setDateRange($value);
        $this->loadSummaryData();
    }

    public function updatedStartDate($value)
    {
        $this->resetPage();
        $this->loadSummaryData();
    }

    public function updatedEndDate($value)
    {
        $this->resetPage();
        $this->loadSummaryData();
    }

    public function updatedPartId($value)
    {
        $this->resetPage();
        $this->loadSummaryData();
    }

    public function updatedSupplierId($value)
    {
        $this->resetPage();
        $this->loadSummaryData();
    }

    public function updatedPartCategory($value)
    {
        $this->resetPage();
        $this->partId = ''; // Reset part selection when category changes
        $this->loadParts(); // Reload parts based on new category
        $this->loadSummaryData();
    }

    public function updatedTransactionType($value)
    {
        $this->resetPage();
        $this->loadSummaryData();
    }

    public function updatedSearchQuery($value)
    {
        $this->resetPage();
        $this->loadSummaryData();
    }

    public function sort($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function loadCategories()
    {
        try {
            // Obter equipamentos únicos agrupados por maintenance_equipment_id
            // Precisamos incluir name no SELECT para usá-lo no ORDER BY
            $parts = EquipmentPart::select('name', 'maintenance_equipment_id')
                ->orderBy('name')
                ->get()
                ->map(function($part) {
                    // Criar o identificador composto depois de buscar dados
                    return $part->maintenance_equipment_id 
                        ? $part->maintenance_equipment_id . '-' . $part->name 
                        : '0-' . $part->name;
                })
                ->unique() // Remover duplicatas depois de buscar todos os dados
                ->values() // Reindexar o array
                ->toArray();
                
            $this->categories = $parts;
        } catch (\Exception $e) {
            Log::error('Error loading part equipment groups: ' . $e->getMessage());
            $this->categories = [];
        }
    }

    public function loadParts()
    {
        try {
            $query = EquipmentPart::query()->orderBy('name');

            if ($this->partCategory) {
                // Usamos o formato 'maintenance_equipment_id-name' para filtrar
                $parts = explode('-', $this->partCategory);
                if (count($parts) == 2 && is_numeric($parts[0])) {
                    $query->where('maintenance_equipment_id', $parts[0])
                          ->where('name', 'like', '%' . $parts[1] . '%');
                }
            }

            $this->parts = $query->get();
        } catch (\Exception $e) {
            Log::error('Error loading parts: ' . $e->getMessage());
            $this->parts = [];
        }
    }

    public function loadSuppliers()
    {
        try {
            $this->suppliers = Supplier::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error('Error loading suppliers: ' . $e->getMessage());
            $this->suppliers = [];
        }
    }

    public function loadSummaryData()
    {
        try {
            // Get start and end dates for transaction queries
            $startDateTime = $this->startDate . ' 00:00:00';
            $endDateTime = $this->endDate . ' 23:59:59';

            // Total parts in inventory
            $this->totalParts = EquipmentPart::count();

            // Total inventory value
            $this->totalValue = EquipmentPart::sum(\DB::raw('stock_quantity * unit_cost'));

            // Total transactions in the period
            $transactionQuery = StockTransaction::whereBetween('created_at', [$startDateTime, $endDateTime]);

            if ($this->partId) {
                $transactionQuery->where('equipment_part_id', $this->partId);
            }

            if ($this->supplierId) {
                $transactionQuery->where('supplier_id', $this->supplierId);
            }

            if ($this->partCategory) {
                // Usamos o formato 'maintenance_equipment_id-name' para filtrar
                $parts = explode('-', $this->partCategory);
                if (count($parts) == 2 && is_numeric($parts[0])) {
                    $equipment_id = $parts[0];
                    $name = $parts[1];
                    
                    $transactionQuery->whereHas('part', function($query) use ($equipment_id, $name) {
                        $query->where('maintenance_equipment_id', $equipment_id)
                              ->where('name', 'like', '%' . $name . '%');
                    });
                }
            }

            if ($this->transactionType && $this->transactionType !== 'all') {
                $transactionQuery->where('type', $this->transactionType);
            }

            if ($this->searchQuery) {
                $transactionQuery->where(function($query) {
                    $query->whereHas('part', function($q) {
                        $q->where('name', 'like', '%' . $this->searchQuery . '%')
                          ->orWhere('part_number', 'like', '%' . $this->searchQuery . '%');
                    })
                    ->orWhereHas('supplier', function($q) {
                        $q->where('name', 'like', '%' . $this->searchQuery . '%');
                    })
                    ->orWhere('reference_number', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('notes', 'like', '%' . $this->searchQuery . '%');
                });
            }

            $this->totalTransactions = $transactionQuery->count();

            // Most used part in the period
            $mostUsedPartId = StockTransaction::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->where('type', 'stock_out')
                ->select('equipment_part_id')
                ->selectRaw('SUM(quantity) as total_used')
                ->groupBy('equipment_part_id')
                ->orderByDesc('total_used')
                ->first();

            if ($mostUsedPartId) {
                $this->mostUsedPart = EquipmentPart::find($mostUsedPartId->equipment_part_id);
            }

            // Top supplier by purchase value
            $topSupplier = StockTransaction::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->where('type', 'stock_in')
                ->select('supplier')
                ->selectRaw('SUM(quantity * unit_cost) as total_value')
                ->whereNotNull('supplier')
                ->groupBy('supplier')
                ->orderByDesc('total_value')
                ->first();

            if ($topSupplier && $topSupplier->supplier) {
                // Armazenamos o nome do fornecedor diretamente
                $this->topSupplier = (object)['name' => $topSupplier->supplier];
            }

        } catch (\Exception $e) {
            Log::error('Error loading summary data: ' . $e->getMessage());
            $this->totalParts = 0;
            $this->totalValue = 0;
            $this->totalTransactions = 0;
            $this->mostUsedPart = null;
            $this->topSupplier = null;
        }
    }

    public function exportTransactionHistory()
    {
        // Placeholder for export functionality
        $this->dispatchBrowserEvent('show-notification', [
            'type' => 'info',
            'message' => 'Export functionality will be implemented soon'
        ]);
    }

    public function getPartTransactionsProperty()
    {
        try {
            $startDateTime = $this->startDate . ' 00:00:00';
            $endDateTime = $this->endDate . ' 23:59:59';

            $query = StockTransaction::with(['part', 'user'])
                ->whereBetween('created_at', [$startDateTime, $endDateTime]);

            if ($this->partId) {
                $query->where('equipment_part_id', $this->partId);
            }

            if ($this->supplierId) {
                $query->where('supplier_id', $this->supplierId);
            }

            if ($this->partCategory) {
                $query->whereHas('part', function($q) {
                    $q->where('category', $this->partCategory);
                });
            }

            if ($this->transactionType && $this->transactionType !== 'all') {
                $query->where('type', $this->transactionType);
            }

            if ($this->searchQuery) {
                $query->where(function($q) {
                    $q->whereHas('part', function($pq) {
                        $pq->where('name', 'like', '%' . $this->searchQuery . '%')
                          ->orWhere('part_number', 'like', '%' . $this->searchQuery . '%');
                    })
                    ->orWhere('supplier', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('invoice_number', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('notes', 'like', '%' . $this->searchQuery . '%');
                });
            }

            return $query->orderBy($this->sortField, $this->sortDirection)
                        ->paginate(15);

        } catch (\Exception $e) {
            Log::error('Error fetching part transactions: ' . $e->getMessage());
            return \Illuminate\Pagination\Paginator::resolveCurrentPath()
                ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15)
                : collect([]);
        }
    }

    public function render()
    {
        return view('livewire.history.parts-lifecycle', [
            'partTransactions' => $this->getPartTransactionsProperty()
        ]);
    }
}

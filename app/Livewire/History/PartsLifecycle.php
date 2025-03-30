<?php

namespace App\Livewire\History;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Part;
use App\Models\PartTransaction;
use App\Models\Supplier;
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
            // Get unique categories from parts table
            $this->categories = Part::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->orderBy('category')
                ->pluck('category')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading part categories: ' . $e->getMessage());
            $this->categories = [];
        }
    }

    public function loadParts()
    {
        try {
            $query = Part::query()->orderBy('name');

            if ($this->partCategory) {
                $query->where('category', $this->partCategory);
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
            $this->totalParts = Part::count();

            // Total inventory value
            $this->totalValue = Part::sum(\DB::raw('quantity * unit_cost'));

            // Total transactions in the period
            $transactionQuery = PartTransaction::whereBetween('created_at', [$startDateTime, $endDateTime]);

            if ($this->partId) {
                $transactionQuery->where('part_id', $this->partId);
            }

            if ($this->supplierId) {
                $transactionQuery->where('supplier_id', $this->supplierId);
            }

            if ($this->partCategory) {
                $transactionQuery->whereHas('part', function($query) {
                    $query->where('category', $this->partCategory);
                });
            }

            if ($this->transactionType && $this->transactionType !== 'all') {
                $transactionQuery->where('transaction_type', $this->transactionType);
            }

            if ($this->searchQuery) {
                $transactionQuery->where(function($query) {
                    $query->whereHas('part', function($q) {
                        $q->where('name', 'like', '%' . $this->searchQuery . '%')
                          ->orWhere('sku', 'like', '%' . $this->searchQuery . '%');
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
            $mostUsedPartId = PartTransaction::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->where('transaction_type', 'usage')
                ->select('part_id')
                ->selectRaw('SUM(quantity) as total_used')
                ->groupBy('part_id')
                ->orderByDesc('total_used')
                ->first();

            if ($mostUsedPartId) {
                $this->mostUsedPart = Part::find($mostUsedPartId->part_id);
            }

            // Top supplier by purchase value
            $topSupplierId = PartTransaction::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->where('transaction_type', 'purchase')
                ->select('supplier_id')
                ->selectRaw('SUM(quantity * unit_cost) as total_value')
                ->groupBy('supplier_id')
                ->orderByDesc('total_value')
                ->first();

            if ($topSupplierId && $topSupplierId->supplier_id) {
                $this->topSupplier = Supplier::find($topSupplierId->supplier_id);
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

            $query = PartTransaction::with(['part', 'supplier', 'maintenance'])
                ->whereBetween('created_at', [$startDateTime, $endDateTime]);

            if ($this->partId) {
                $query->where('part_id', $this->partId);
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
                $query->where('transaction_type', $this->transactionType);
            }

            if ($this->searchQuery) {
                $query->where(function($q) {
                    $q->whereHas('part', function($pq) {
                        $pq->where('name', 'like', '%' . $this->searchQuery . '%')
                          ->orWhere('sku', 'like', '%' . $this->searchQuery . '%');
                    })
                    ->orWhereHas('supplier', function($sq) {
                        $sq->where('name', 'like', '%' . $this->searchQuery . '%');
                    })
                    ->orWhere('reference_number', 'like', '%' . $this->searchQuery . '%')
                    ->orWhere('notes', 'like', '%' . $this->searchQuery . '%');
                });
            }

            return $query->orderBy($this->sortField, $this->sortDirection)
                        ->paginate(15);

        } catch (\Exception $e) {
            Log::error('Error fetching part transactions: ' . $e->getMessage());
            return [];
        }
    }

    public function render()
    {
        return view('livewire.history.parts-lifecycle', [
            'partTransactions' => $this->getPartTransactionsProperty()
        ]);
    }
}

<?php

namespace App\Livewire\Stocks;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockTransaction;
use App\Models\EquipmentPart;
use App\Models\MaintenanceEquipment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class StockHistory extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $equipmentId = null;
    
    #[Url]
    public $partId = null;
    
    #[Url]
    public $transactionType = '';
    
    #[Url]
    public $dateFrom = '';
    
    #[Url]
    public $dateTo = '';

    public $perPage = 15;
    public $sortField = 'transaction_date';
    public $sortDirection = 'desc';

    /**
     * Lifecycle hook that runs once on component initialization
     */
    public function mount()
    {
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');
    }

    /**
     * Get all equipment for dropdown selection
     */
    public function getEquipmentListProperty()
    {
        return MaintenanceEquipment::orderBy('name')->get();
    }

    /**
     * Get parts based on selected equipment
     */
    public function getPartsListProperty()
    {
        return EquipmentPart::when($this->equipmentId, function ($query) {
                return $query->where('maintenance_equipment_id', $this->equipmentId);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Get stock transactions based on filters
     */
    public function getStockTransactionsProperty()
    {
        return StockTransaction::with(['part', 'part.equipment', 'createdBy'])
            ->when($this->search, function ($query) {
                return $query->whereHas('part', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('part_number', 'like', '%' . $this->search . '%');
                })
                ->orWhere('supplier', 'like', '%' . $this->search . '%')
                ->orWhere('invoice_number', 'like', '%' . $this->search . '%')
                ->orWhere('notes', 'like', '%' . $this->search . '%');
            })
            ->when($this->equipmentId, function ($query) {
                return $query->whereHas('part', function($q) {
                    $q->where('maintenance_equipment_id', $this->equipmentId);
                });
            })
            ->when($this->partId, function ($query) {
                return $query->where('equipment_part_id', $this->partId);
            })
            ->when($this->transactionType, function ($query) {
                return $query->where('type', $this->transactionType);
            })
            ->when($this->dateFrom, function ($query) {
                return $query->whereDate('transaction_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                return $query->whereDate('transaction_date', '<=', $this->dateTo);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /**
     * Sort transactions by a specific field
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'desc';
        }

        // Reset to the first page when sorting
        $this->resetPage();
    }

    /**
     * Get the name of the selected equipment 
     */
    public function getSelectedEquipmentName()
    {
        if (empty($this->equipmentId)) {
            return '';
        }
        
        $equipment = MaintenanceEquipment::find($this->equipmentId);
        return $equipment ? $equipment->name : '';
    }
    
    /**
     * Get the name of the selected part
     */
    public function getSelectedPartName()
    {
        if (empty($this->partId)) {
            return '';
        }
        
        $part = EquipmentPart::find($this->partId);
        return $part ? $part->name : '';
    }

    /**
     * Select equipment in the filter dropdown
     */
    public function selectEquipmentId($id)
    {
        $this->equipmentId = $id;
        $this->partId = null; // Reset part selection when equipment changes
        $this->resetPage();
    }
    
    /**
     * Select part in the filter dropdown
     */
    public function selectPartId($id)
    {
        $this->partId = $id;
        $this->resetPage();
    }
    
    /**
     * Clear all filters
     */
    public function clearFilters()
    {
        $this->reset(['search', 'equipmentId', 'partId', 'transactionType']);
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->dateFrom = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->resetPage();
    }

    /**
     * Calculate inventory value and other statistics
     */
    public function getInventoryStatsProperty()
    {
        // Para estatísticas filtradas (mostradas na interface)
        $filteredQuery = StockTransaction::query()
            ->when($this->equipmentId, function ($query) {
                return $query->whereHas('part', function($q) {
                    $q->where('maintenance_equipment_id', $this->equipmentId);
                });
            })
            ->when($this->partId, function ($query) {
                return $query->where('equipment_part_id', $this->partId);
            })
            ->when($this->transactionType, function ($query) {
                return $query->where('type', $this->transactionType);
            })
            ->when($this->dateFrom, function ($query) {
                return $query->whereDate('transaction_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                return $query->whereDate('transaction_date', '<=', $this->dateTo);
            });

        // Para estatísticas totais do sistema (independente dos filtros)
        $totalStockOutCount = StockTransaction::where('type', 'stock_out')->count();

        // Cálculo correto do valor total do inventário atual
        $totalInventoryValue = DB::table('equipment_parts')
            ->leftJoin(DB::raw('(
                SELECT 
                    equipment_part_id,
                    SUM(CASE WHEN type = "stock_in" THEN quantity ELSE -quantity END) as current_qty,
                    SUM(CASE WHEN type = "stock_in" THEN quantity * unit_cost ELSE 0 END) / 
                    NULLIF(SUM(CASE WHEN type = "stock_in" THEN quantity ELSE 0 END), 0) as avg_cost
                FROM stock_transactions
                GROUP BY equipment_part_id
            ) as inventory'), 'equipment_parts.id', '=', 'inventory.equipment_part_id')
            ->select(DB::raw('SUM(COALESCE(inventory.current_qty, 0) * COALESCE(inventory.avg_cost, 0)) as total_value'))
            ->first()->total_value ?? 0;

        return [
            'total_transactions' => $filteredQuery->count(),
            'stock_in_count' => $filteredQuery->where('type', 'stock_in')->count(),
            'stock_out_count' => $totalStockOutCount,
            'total_value' => $totalInventoryValue,
        ];
    }

    /**
     * Render the component
     */
    public function render()
    {
        // Obter transações filtradas
        $query = StockTransaction::with(['part', 'part.equipment', 'createdBy'])
            ->when($this->search, function($query) {
                return $query->whereHas('part', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('part_number', 'like', '%' . $this->search . '%');
                })
                ->orWhere('supplier', 'like', '%' . $this->search . '%')
                ->orWhere('invoice_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->equipmentId, function($query) {
                return $query->whereHas('part', function($q) {
                    $q->where('maintenance_equipment_id', $this->equipmentId);
                });
            })
            ->when($this->partId, function($query) {
                return $query->where('equipment_part_id', $this->partId);
            })
            ->when($this->transactionType, function($query) {
                return $query->where('type', $this->transactionType);
            })
            ->when($this->dateFrom, function($query) {
                return $query->whereDate('transaction_date', '>=', Carbon::parse($this->dateFrom));
            })
            ->when($this->dateTo, function($query) {
                return $query->whereDate('transaction_date', '<=', Carbon::parse($this->dateTo));
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $stockTransactions = $query->paginate($this->perPage);
        
        // Obter lista de equipamentos para o filtro
        $equipmentList = MaintenanceEquipment::orderBy('name')->pluck('name', 'id');
        
        // Obter lista de peças para o filtro
        $partsQuery = EquipmentPart::query();
        if ($this->equipmentId) {
            $partsQuery->where('maintenance_equipment_id', $this->equipmentId);
        }
        $partsList = $partsQuery->orderBy('name')->pluck('name', 'id');

        return view('livewire.stocks.stock-history', [
            'stockTransactions' => $stockTransactions,
            'equipmentList' => $equipmentList,
            'partsList' => $partsList,
        ]);
    }

    /**
     * Generate PDF report of stock transaction history
     * 
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generatePdf()
    {
        try {
            // Mostrar notificação antes de iniciar o download
            $this->dispatch('notify', type: 'success', message: __('livewire/stocks/stock-history.pdf_generating'));
            
            // Apply all current filters to get transactions
            $query = StockTransaction::with(['part', 'part.equipment', 'createdBy'])
                ->when($this->search, function($query) {
                    return $query->whereHas('part', function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('part_number', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('supplier', 'like', '%' . $this->search . '%')
                    ->orWhere('invoice_number', 'like', '%' . $this->search . '%');
                })
                ->when($this->equipmentId, function($query) {
                    return $query->whereHas('part', function($q) {
                        $q->where('maintenance_equipment_id', $this->equipmentId);
                    });
                })
                ->when($this->partId, function($query) {
                    return $query->where('equipment_part_id', $this->partId);
                })
                ->when($this->transactionType, function($query) {
                    return $query->where('type', $this->transactionType);
                })
                ->when($this->dateFrom, function($query) {
                    return $query->whereDate('transaction_date', '>=', Carbon::parse($this->dateFrom));
                })
                ->when($this->dateTo, function($query) {
                    return $query->whereDate('transaction_date', '<=', Carbon::parse($this->dateTo));
                });
            
            // Get report title based on filters
            $reportTitle = 'Stock Transaction History';
            if ($this->transactionType) {
                $typeLabels = [
                    'stock_in' => 'Stock In',
                    'stock_out' => 'Stock Out',
                    'adjustment' => 'Adjustment'
                ];
                $reportTitle = ($typeLabels[$this->transactionType] ?? ucfirst($this->transactionType)) . ' History';
            }
            
            // Sorted data
            $transactions = $query->orderBy($this->sortField, $this->sortDirection)->get();
            
            // Load PDF view
            $pdf = Pdf::loadView('livewire.stocks.stock-history-pdf', [
                'transactions' => $transactions,
                'filters' => [
                    'search' => $this->search,
                    'equipmentId' => $this->equipmentId ? MaintenanceEquipment::find($this->equipmentId)?->name : null,
                    'partId' => $this->partId ? EquipmentPart::find($this->partId)?->name : null,
                    'transactionType' => $this->transactionType,
                    'dateFrom' => $this->dateFrom ? Carbon::parse($this->dateFrom)->format('d/m/Y') : null,
                    'dateTo' => $this->dateTo ? Carbon::parse($this->dateTo)->format('d/m/Y') : null,
                ],
                'reportTitle' => $reportTitle
            ]);
            
            // Generate filename based on filters
            $filename = 'stock-history';
            if ($this->transactionType) {
                $filename = strtolower(str_replace('_', '-', $this->transactionType)) . '-history';
            }
            $filename .= '-' . date('Y-m-d') . '.pdf';
            
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, $filename);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: __('livewire/stocks/stock-history.pdf_error') . $e->getMessage());
        }
    }
}

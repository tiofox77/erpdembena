<?php

namespace App\Livewire\SupplyChain\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\InventoryTransaction;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation;
use Carbon\Carbon;

class StockMovementReport extends Component
{
    use WithPagination;

    // Filtros
    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $productId = '';
    public $locationId = '';
    public $transactionType = '';
    public $perPage = 25;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Opções para filtros
    public $products = [];
    public $locations = [];
    public $transactionTypes = [
        'purchase_receipt' => 'Recebimento de Compra',
        'production_consumption' => 'Consumo de Produção',
        'production_output' => 'Saída de Produção',
        'inventory_adjustment' => 'Ajuste de Estoque',
        'inventory_transfer' => 'Transferência de Estoque',
        'sales_delivery' => 'Entrega de Venda',
        'return_from_customer' => 'Retorno de Cliente',
        'scrap' => 'Sucata',
    ];

    public function mount()
    {
        $this->products = Product::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');
            
        $this->locations = InventoryLocation::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');
            
        $this->endDate = now()->format('Y-m-d');
        $this->startDate = now()->subMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
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
        $this->reset(['search', 'productId', 'locationId', 'transactionType', 'startDate', 'endDate']);
        $this->startDate = now()->subMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        try {
            // Base query with only essential relationships
            $query = InventoryTransaction::query()
                ->with([
                    'product',
                    'sourceLocation',
                    'destinationLocation',
                    'creator'
                ]);

            // Apply filters
            $query->when($this->search, function($q) {
                $q->whereHas('product', function($productQuery) {
                    $productQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            });

            $query->when($this->productId, function($q) {
                $q->where('product_id', $this->productId);
            });

            $query->when($this->locationId, function($q) {
                $q->where(function($query) {
                    $query->where('source_location_id', $this->locationId)
                          ->orWhere('destination_location_id', $this->locationId);
                });
            });

            $query->when($this->transactionType, function($q) {
                $q->where('transaction_type', $this->transactionType);
            });

            $query->when($this->startDate, function($q) {
                $q->whereDate('created_at', '>=', $this->startDate);
            });

            $query->when($this->endDate, function($q) {
                $q->whereDate('created_at', '<=', $this->endDate);
            });

            // Calculate totals
            $totalIn = (clone $query)->where('quantity', '>', 0)->sum('quantity');
            $totalOut = abs((clone $query)->where('quantity', '<', 0)->sum('quantity'));
            $netMovement = $totalIn - $totalOut;

            // Apply sorting and pagination
            $transactions = $query->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage);

            return view('livewire.supply-chain.reports.stock-movement-report', [
                'transactions' => $transactions,
                'totalIn' => $totalIn,
                'totalOut' => $totalOut,
                'netMovement' => $netMovement,
            ]);
            
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in StockMovementReport: ' . $e->getMessage());
            
            // Return empty results on error
            return view('livewire.supply-chain.reports.stock-movement-report', [
                'transactions' => collect([]),
                'totalIn' => 0,
                'totalOut' => 0,
                'netMovement' => 0,
                'error' => 'Error loading stock movement data. Please try again.'
            ]);
        }
    }

    public function exportToExcel()
    {
        // Implementar exportação para Excel
        return redirect()->back()->with('success', 'Exportação para Excel em desenvolvimento');
    }

    public function exportToPdf()
    {
        // Implementar exportação para PDF
        return redirect()->back()->with('success', 'Exportação para PDF em desenvolvimento');
    }
}

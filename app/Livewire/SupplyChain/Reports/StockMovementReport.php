<?php

namespace App\Livewire\SupplyChain\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\InventoryTransaction;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public $perPage = 50;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Opções para filtros
    public $products = [];
    public $locations = [];
    public $transactionTypes = [];

    protected $listeners = [
        'refreshInventoryData' => '$refresh'
    ];
    
    /**
     * Gera um PDF do relatório de movimentação de estoque com os filtros aplicados
     */
    public function generatePdf()
    {
        try {
            // Preparar a consulta usando os filtros atuais sem paginação
            $query = InventoryTransaction::query();
            
            // Aplicar todos os filtros existentes
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
            
            // Ordenar os resultados
            $transactions = $query->with(['product', 'sourceLocation', 'destinationLocation', 'creator'])
                            ->orderBy($this->sortField, $this->sortDirection)
                            ->get();
            
            // Calcular totais
            $totalIn = (clone $query)->where('quantity', '>', 0)->sum('quantity');
            $totalOut = abs((clone $query)->where('quantity', '<', 0)->sum('quantity'));
            $netMovement = $totalIn - $totalOut;
            
            // Carregar produtos e localizações para o cabeçalho do relatório
            $selectedProduct = $this->productId ? Product::find($this->productId) : null;
            $selectedLocation = $this->locationId ? InventoryLocation::find($this->locationId) : null;
            
            // Criar o PDF
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.stock-movement-report', [
                'transactions' => $transactions,
                'totalIn' => $totalIn,
                'totalOut' => $totalOut,
                'netMovement' => $netMovement,
                'filters' => [
                    'product' => $selectedProduct,
                    'location' => $selectedLocation,
                    'transactionType' => $this->transactionType,
                    'startDate' => $this->startDate,
                    'endDate' => $this->endDate,
                    'search' => $this->search
                ],
                'date' => now(),
                'user' => auth()->user()
            ]);
            
            $filename = 'stock_movement_report_' . now()->format('Y-m-d_H-i') . '.pdf';
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_generated_successfully')
            );
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error generating stock movement PDF: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed')
            );
            return null;
        }
    }

    /**
     * Get the transaction types that exist in the database
     * 
     * @return array
     */
    protected function getTransactionTypes()
    {
        $types = [];
        
        // Get unique transaction types from the database
        $dbTypes = InventoryTransaction::select('transaction_type')
            ->distinct()
            ->orderBy('transaction_type')
            ->pluck('transaction_type')
            ->filter()
            ->toArray();
            
        // Use translation keys for each type
        foreach ($dbTypes as $type) {
            $translationKey = 'transaction_type_' . $type;
            $translated = __($translationKey);
            
            // If translation doesn't exist, use a fallback
            $types[$type] = $translated === $translationKey 
                ? ucfirst(str_replace('_', ' ', $type))
                : $translated;
        }
        
        return $types;
    }

    public function mount()
    {
        $this->products = Product::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');
            
        $this->locations = InventoryLocation::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');
            
        // Load transaction types that exist in the database
        $this->transactionTypes = $this->getTransactionTypes();
            
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
            // Base query with essential relationships
            $query = InventoryTransaction::query()
                ->with([
                    'product',
                    'sourceLocation',
                    'destinationLocation',
                    'creator'
                ]);
                
            // We'll load the reference relationship separately after getting the results
            // to avoid issues with the polymorphic relationship

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

            // Nota: Esta tabela usa created_at como data da transação
            
            // Apply sorting and pagination
            $transactions = $query->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage);

            // Prepare to load production data for daily production transactions
            $dailyProductions = collect();
            
            // Get all daily production transactions
            $dailyProdTransactions = $transactions->filter(function($transaction) {
                return in_array($transaction->transaction_type, [
                    InventoryTransaction::TYPE_DAILY_PRODUCTION,
                    InventoryTransaction::TYPE_DAILY_PRODUCTION_FG
                ]);
            });
            
            if ($dailyProdTransactions->isNotEmpty()) {
                // Get unique reference IDs
                $referenceIds = $dailyProdTransactions->pluck('reference_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
                
                if (!empty($referenceIds)) {
                    // Load production plans with their relationships
                    $dailyProductions = ProductionDailyPlan::with(['schedule.product'])
                        ->whereIn('id', $referenceIds)
                        ->get()
                        ->keyBy('id');
                }
            }

            // Prepare view data
            $viewData = [
                'transactions' => $transactions,
                'totalIn' => $totalIn,
                'totalOut' => $totalOut,
                'netMovement' => $netMovement,
                'dailyProductions' => $dailyProductions,
            ];
            
            return view('livewire.supply-chain.reports.stock-movement-report', $viewData);
            
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
    
    /**
     * Gera os dados necessários para o PDF do relatório de movimentação de estoque
     * 
     * @return array
     */
    protected function generatePdfData()
    {
        // Preparar a consulta usando os filtros atuais sem paginação
        $query = InventoryTransaction::query();
        
        // Aplicar todos os filtros existentes
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
        
        // Ordenar os resultados
        $transactions = $query->with(['product', 'sourceLocation', 'destinationLocation', 'creator'])
                        ->orderBy($this->sortField, $this->sortDirection)
                        ->get();
        
        // Calcular totais
        $totalIn = (clone $query)->where('quantity', '>', 0)->sum('quantity');
        $totalOut = abs((clone $query)->where('quantity', '<', 0)->sum('quantity'));
        $netMovement = $totalIn - $totalOut;
        
        // Carregar produtos e localizações para o cabeçalho do relatório
        $selectedProduct = $this->productId ? Product::find($this->productId) : null;
        $selectedLocation = $this->locationId ? InventoryLocation::find($this->locationId) : null;
        
        return [
            'transactions' => $transactions,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'netMovement' => $netMovement,
            'filters' => [
                'product' => $selectedProduct,
                'location' => $selectedLocation,
                'transactionType' => $this->transactionType,
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'search' => $this->search
            ],
            'date' => now(),
            'user' => auth()->user()
        ];
    }

    public function exportToPdf()
    {
        try {
            // Gerar dados do PDF usando o método existente
            $data = $this->generatePdfData();
            
            // Criar o PDF com orientação landscape
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.stock-movement-report', $data);
            $pdf->setPaper('a4', 'landscape');
            
            // Nome do arquivo
            $fileName = 'stock_movement_report_' . now()->format('YmdHis') . '.pdf';
            
            // Salvar temporariamente
            $tempPath = storage_path('app/public/reports/' . $fileName);
            $pdf->save($tempPath);
            
            // Retornar arquivo para download
            return response()->download($tempPath, $fileName, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend();
            
        } catch (\Exception $e) {
            Log::error('Error exporting PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', __('messages.error_generating_pdf'));
        }
    }
}

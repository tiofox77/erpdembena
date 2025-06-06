<?php

namespace App\Livewire\Mrp\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupplyChain\Product;
use App\Models\SupplyChain\PurchaseOrder;
use App\Models\SupplyChain\PurchaseOrderItem;
use App\Models\SupplyChain\InventoryItem;
use App\Models\SupplyChain\InventoryTransaction;
use App\Models\SupplyChain\InventoryLocation;
use App\Models\Mrp\ProductionSchedule;
use App\Models\Mrp\ProductionDailyPlan;
use App\Models\Mrp\BomHeader;
use App\Models\Mrp\BomDetail;
use App\Exports\RawMaterialReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RawMaterialReport extends Component
{
    use WithPagination;
    // Date range filter properties
    public $startDate = '';
    public $endDate = '';
    
    // Pagination
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Search filter
    public $search = '';
    
    // Modal properties
    public $showDetailsModal = false;
    public $selectedMaterial = null;
    public $warehouseStockDetails = [];
    public $purchaseOrderDetails = [];
    public $productionScheduleDetails = [];
    public $activeTab = 'warehouses';  // Options: warehouses, purchase_orders, production_schedules
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStartDate()
    {
        $this->resetPage();
    }
    
    public function updatingEndDate()
    {
        $this->resetPage();
    }
    
    public function updatingPerPage()
    {
        $this->resetPage();
    }
    
    public function exportPdf()
    {
        $materials = $this->getFilteredMaterials();
        
        // Generate a unique filename
        $filename = 'raw-material-report-' . now()->format('Y-m-d-His') . '.pdf';
        $path = 'reports/' . $filename;
        
        // Create the PDF
        $pdf = Pdf::loadView('pdf.raw-material-report', [
            'materials' => $materials,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'search' => $this->search
        ]);
        
        // Create directory if it doesn't exist
        Storage::makeDirectory('public/reports');
        
        // Save the PDF to storage
        Storage::put('public/' . $path, $pdf->output());
        
        // Return the file path for download
        return response()->download(storage_path('app/public/' . $path))->deleteFileAfterSend(true);
    }
    
    /**
     * Get the total quantity of a material that's currently on order
     *
     * @param int $materialId
     * @return float
     */
    protected function getOnOrderQuantity($materialId)
    {
        $query = PurchaseOrderItem::where('product_id', $materialId)
            ->whereHas('purchaseOrder', function($q) {
                $q->whereIn('status', ['draft', 'pending', 'approved', 'in_transit', 'partially_received'])
                  ->where('is_active', true);
            });
            
        // Apply date filters if set
        if ($this->startDate) {
            $query->whereHas('purchaseOrder', function($q) {
                $q->where('order_date', '>=', $this->startDate);
            });
        }
        
        if ($this->endDate) {
            $query->whereHas('purchaseOrder', function($q) {
                $q->where('order_date', '<=', $this->endDate);
            });
        }
        
        // Calculate the total ordered quantity
        $totalOrdered = (clone $query)->sum('quantity');
        
        // Get the total received quantity from inventory transactions
        $totalReceived = DB::table('sc_inventory_transactions')
            ->where('product_id', $materialId)
            ->where('transaction_type', 'purchase_receipt')
            ->when($this->startDate, function($q) {
                $q->where('transaction_date', '>=', $this->startDate);
            })
            ->when($this->endDate, function($q) {
                $q->where('transaction_date', '<=', $this->endDate);
            })
            ->sum('quantity');
            
        // Return the difference (on order = ordered - received)
        return max(0, $totalOrdered - $totalReceived);
    }
    
    /**
     * Get the total quantity of a material that's currently in production
     *
     * @param int $materialId
     * @return float
     */
    protected function getInProductionQuantity($materialId)
    {
        // Get all active production schedules that use this material
        $query = DB::table('production_schedule_items as psi')
            ->join('production_schedules as ps', 'psi.production_schedule_id', '=', 'ps.id')
            ->join('bom_details as bd', 'psi.bom_detail_id', '=', 'bd.id')
            ->where('bd.product_id', $materialId)
            ->whereIn('ps.status', ['planned', 'in_progress', 'on_hold'])
            ->where('ps.is_active', true);
            
        // Apply date filters if set
        if ($this->startDate) {
            $query->where('ps.planned_start_date', '>=', $this->startDate);
        }
        
        if ($this->endDate) {
            $query->where('ps.planned_start_date', '<=', $this->endDate);
        }
        
        // Return the sum of quantities in production
        return $query->sum(DB::raw('(bd.quantity * ps.quantity) - COALESCE(psi.quantity_used, 0)'));
    }
    
    /**
     * Get filtered materials with additional calculated fields
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getFilteredMaterials()
    {
        // Base query for raw material products
        $query = Product::where('product_type', 'raw_material')
            ->where('is_active', true)
            ->where('is_stockable', true)
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);
            
        // Execute the query and get raw materials
        $materials = $query->get();
        
        // Prepare date range for filtering PO and Production data
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        
        // Enhance materials with additional data
        foreach ($materials as $material) {
            // Get current inventory stock across all warehouses
            $material->current_stock = $material->getTotalQuantityAttribute();
            
            // Get stock in purchase orders
            $material->on_order = $this->getOnOrderQuantity($material->id);
            
            // Get stock in production
            $material->in_production = $this->getInProductionQuantity($material->id);
            
            // Calculate required quantity (safety stock - current stock - on order + in production)
            $material->required_quantity = max(0, 
                ($material->safety_stock ?? 0) - 
                $material->current_stock - 
                $material->on_order + 
                $material->in_production
            );
            
            // Set default values if not set
            $material->min_stock_level = $material->min_stock_level ?? 0;
            $material->max_stock_level = $material->max_stock_level ?? 0;
            $material->safety_stock = $material->safety_stock ?? 0;
            $material->unit_of_measure = $material->unit_of_measure ?? 'UN';
            
            // Add planned quantity (you may need to adjust this based on your business logic)
            $material->planned_quantity = 0; // Add your logic to calculate planned quantity
        }
        
        return $materials;
    }
    
    public function resetFilters()
    {
        $this->startDate = '';
        $this->endDate = '';
        $this->search = '';
        $this->resetPage();
    }
    
    /**
     * Generate the raw material report data
     */
    public function getRawMaterialReportData()
    {
        // Base query for raw material products
        $rawMaterialsQuery = Product::where('product_type', 'raw_material')
            ->where('is_active', true)
            ->where('is_stockable', true)
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);
            
        // Execute the query and get raw materials
        $rawMaterials = $rawMaterialsQuery->paginate($this->perPage);
        
        // Prepare date range for filtering PO and Production data
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        
        // Enhance raw materials with additional data
        foreach ($rawMaterials as $material) {
            // Get current inventory stock across all warehouses
            $material->current_stock = $material->getTotalQuantityAttribute();
            
            // Get stock in purchase orders
            $poStockQuery = PurchaseOrderItem::where('product_id', $material->id)
                ->whereHas('purchaseOrder', function($query) use ($startDate, $endDate) {
                    $query->whereIn('status', ['approved', 'ordered', 'partially_received'])
                          ->where('is_active', true);
                          
                    // Apply date range filter if provided
                    if ($startDate) {
                        $query->where('order_date', '>=', $startDate);
                    }
                    
                    if ($endDate) {
                        $query->where('order_date', '<=', $endDate);
                    }
                });
            
            // Calculate PO stock (ordered but not yet received)
            $material->po_stock = $poStockQuery->sum('quantity');
            
            // Get the BOM components that use this raw material to calculate planned quantity
            $bomComponents = DB::table('mrp_bom_details')
                ->where('component_id', $material->id)
                ->get();
                
            // Initialize planned quantity
            $plannedQuantity = 0;
            
            // Calculate planned quantity based on BOM components
            foreach ($bomComponents as $component) {
                // Get production schedules that use the product containing this component
                $productionSchedules = ProductionSchedule::whereHas('product.bomHeaders', function($query) use ($component) {
                    $query->where('id', $component->bom_header_id);
                });
                
                // Apply date range filter if provided
                if ($startDate || $endDate) {
                    $productionSchedules->where(function($query) use ($startDate, $endDate) {
                        if ($startDate) {
                            $query->where('start_date', '>=', $startDate)
                                  ->orWhere('end_date', '>=', $startDate);
                        }
                        
                        if ($endDate) {
                            $query->where('start_date', '<=', $endDate)
                                  ->orWhere('end_date', '<=', $endDate);
                        }
                    });
                }
                
                // Get the schedules
                $schedules = $productionSchedules->get();
                
                // For each production schedule
                foreach ($schedules as $schedule) {
                    // Calculate planned quantity based on component quantity and planned quantity
                    $plannedQuantity += $component->quantity * $schedule->planned_quantity;
                }
            }
            
            // Calculate consumed quantity from inventory transactions
            $consumedQuantityQuery = InventoryTransaction::where('product_id', $material->id)
                ->where('transaction_type', InventoryTransaction::TYPE_DAILY_PRODUCTION);
                
            // Apply date range filter if provided
            if ($startDate || $endDate) {
                if ($startDate) {
                    $consumedQuantityQuery->where('created_at', '>=', $startDate);
                }
                
                if ($endDate) {
                    $consumedQuantityQuery->where('created_at', '<=', $endDate);
                }
            }
            
            // Sum the quantities from all daily production transactions for this material
            $consumedQuantity = $consumedQuantityQuery->sum('quantity');
            
            // Store calculated values
            $material->consumed_quantity = abs($consumedQuantity); // Ensure positive value as consumption is typically recorded as negative
            $material->planned_quantity = $plannedQuantity;
            
            // Calculate reorder quantity if below reorder point
            if ($material->current_stock <= $material->reorder_point) {
                $material->reorder_quantity = max(0, $material->reorder_point - $material->current_stock + $material->consumed_quantity - $material->po_stock);
            } else {
                $material->reorder_quantity = 0;
            }
        }
        
        return $rawMaterials;
    }
    
    /**
     * Load warehouse stock details for the selected material
     */
    public function loadWarehouseDetails($materialId)
    {
        $this->warehouseStockDetails = [];
        
        // Get all inventory locations with stock for this material
        $inventoryItems = InventoryItem::where('product_id', $materialId)
            ->with('location')
            ->get();
            
        foreach ($inventoryItems as $item) {
            $this->warehouseStockDetails[] = [
                'location_name' => $item->location->name,
                'location_code' => $item->location->code,
                'quantity' => $item->quantity_on_hand,
                'quantity_allocated' => $item->quantity_allocated,
                'quantity_available' => $item->quantity_available,
                'last_movement_date' => $item->updated_at->format('Y-m-d H:i'),
            ];
        }
        
        return $this->warehouseStockDetails;
    }
    
    /**
     * Load purchase order details for the selected material
     */
    public function loadPurchaseOrderDetails($materialId)
    {
        $this->purchaseOrderDetails = [];
        
        // Get date range filters if set
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        
        // Get purchase orders with this material
        $purchaseOrderItems = PurchaseOrderItem::where('product_id', $materialId)
            ->with(['purchaseOrder' => function($query) use ($startDate, $endDate) {
                $query->whereIn('status', ['draft', 'approved', 'ordered', 'partially_received', 'completed'])
                      ->where('is_active', true);
                      
                if ($startDate) {
                    $query->where('order_date', '>=', $startDate);
                }
                
                if ($endDate) {
                    $query->where('order_date', '<=', $endDate);
                }
            }])
            ->get()
            ->filter(function($item) {
                return $item->purchaseOrder != null;
            });
            
        foreach ($purchaseOrderItems as $item) {
            // Debug: Log the purchase order data
            \Log::info('Purchase Order Data:', [
                'po_number' => $item->purchaseOrder->order_number,
                'expected_delivery_date' => $item->purchaseOrder->expected_delivery_date,
                'expected_date' => $item->purchaseOrder->expected_date ?? 'not set',
                'attributes' => $item->purchaseOrder->getAttributes()
            ]);
            
            $this->purchaseOrderDetails[] = [
                'po_number' => $item->purchaseOrder->order_number,
                'supplier' => $item->purchaseOrder->supplier->name,
                'status' => $item->purchaseOrder->status,
                'order_date' => $item->purchaseOrder->order_date->format('Y-m-d'),
                'expected_delivery_date' => $item->purchaseOrder->expected_delivery_date ? $item->purchaseOrder->expected_delivery_date->format('d/m/Y') : 'N/A',
                'quantity_ordered' => $item->quantity,
                'quantity_received' => $item->quantity_received,
                'quantity_pending' => $item->quantity - $item->quantity_received,
                'unit_price' => $item->unit_price,
                'total_price' => $item->quantity * $item->unit_price,
                'created_at' => $item->created_at->format('Y-m-d'),
            ];
        }
        
        return $this->purchaseOrderDetails;
    }
    
    /**
     * Load production schedule details for the selected material
     */
    public function loadProductionScheduleDetails($materialId)
    {
        $this->productionScheduleDetails = [];
        
        // Get date range filters if set
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        
        // Get BOM details that use this material as a component
        $bomDetails = BomDetail::where('component_id', $materialId)
            ->with('bomHeader.product')
            ->get();
        
        foreach ($bomDetails as $detail) {
            // Get production schedules for products that use this component
            $schedules = ProductionSchedule::where('product_id', $detail->bomHeader->product_id)
                ->when($startDate, function($query) use ($startDate) {
                    return $query->where(function($q) use ($startDate) {
                        $q->where('start_date', '>=', $startDate)
                          ->orWhere('end_date', '>=', $startDate);
                    });
                })
                ->when($endDate, function($query) use ($endDate) {
                    return $query->where(function($q) use ($endDate) {
                        $q->where('start_date', '<=', $endDate)
                          ->orWhere('end_date', '<=', $endDate);
                    });
                })
                ->get();
            
            foreach ($schedules as $schedule) {
                $materialQuantityNeeded = $detail->quantity * $schedule->planned_quantity;
                
                $this->productionScheduleDetails[] = [
                    'schedule_id' => $schedule->id,
                    'product_name' => $detail->bomHeader->product->name,
                    'product_sku' => $detail->bomHeader->product->sku,
                    'bom_number' => $detail->bomHeader->bom_number,
                    'schedule_status' => $schedule->status,
                    'start_date' => $schedule->start_date->format('Y-m-d'),
                    'end_date' => $schedule->end_date->format('Y-m-d'),
                    'planned_quantity' => $schedule->planned_quantity,
                    'actual_quantity' => $schedule->actual_quantity,
                    'material_per_unit' => $detail->quantity,
                    'total_material_needed' => $materialQuantityNeeded,
                    'priority' => $schedule->priority,
                ];
            }
        }
        
        return $this->productionScheduleDetails;
    }
    
    /**
     * Show material details modal
     */
    public function showDetails($materialId)
    {
        $this->selectedMaterial = Product::findOrFail($materialId);
        $this->loadWarehouseDetails($materialId);
        $this->loadPurchaseOrderDetails($materialId);
        $this->loadProductionScheduleDetails($materialId);
        $this->showDetailsModal = true;
    }
    
    /**
     * Close modal and reset data
     */
    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedMaterial = null;
        $this->warehouseStockDetails = [];
        $this->purchaseOrderDetails = [];
        $this->productionScheduleDetails = [];
    }
    
    /**
     * Switch between tabs in the details modal
     */
    public function switchTab($tabName)
    {
        $this->activeTab = $tabName;
    }
    
    public function render()
    {
        $rawMaterials = $this->getRawMaterialReportData();
        
        return view('livewire.mrp.reports.raw-material-report', [
            'rawMaterials' => $rawMaterials
        ]);
    }
}

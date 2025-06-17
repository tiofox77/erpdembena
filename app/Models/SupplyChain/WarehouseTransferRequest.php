<?php

namespace App\Models\SupplyChain;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransferRequest extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'sc_warehouse_transfer_requests';
    
    protected $fillable = [
        'request_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'requested_by',
        'approved_by',
        'status',
        'priority',
        'notes',
        'requested_date',
        'required_date',
        'approved_at',
        'approval_notes'
    ];
    
    protected $casts = [
        'requested_date' => 'datetime',
        'required_by_date' => 'datetime',
        'completion_date' => 'datetime'
    ];
    
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    
    /**
     * Get the source location for this transfer request
     */
    public function sourceLocation()
    {
        return $this->belongsTo(InventoryLocation::class, 'from_warehouse_id');
    }
    
    /**
     * Get the destination location for this transfer request
     */
    public function destinationLocation()
    {
        return $this->belongsTo(InventoryLocation::class, 'to_warehouse_id');
    }
    
    /**
     * Get the user who requested this transfer
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    /**
     * Get the user who approved/rejected this transfer
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get all transfer request items for this request
     */
    public function items()
    {
        return $this->hasMany(WarehouseTransferRequestItem::class, 'transfer_request_id');
    }
    
    /**
     * Get inventory transactions related to this transfer request
     */
    public function transactions()
    {
        return $this->morphMany(InventoryTransaction::class, 'reference');
    }
    
    /**
     * Generate a unique transfer request number
     */
    public static function generateRequestNumber()
    {
        $prefix = 'TR';
        $date = now()->format('ymd');
        
        // Get the highest number for today to prevent duplicates
        $pattern = $prefix . $date . '%';
        $lastRequest = self::where('request_number', 'like', $pattern)
            ->orderBy('request_number', 'desc')
            ->first();
        
        if ($lastRequest) {
            // Extract the numeric part and increment
            $lastNumber = substr($lastRequest->request_number, 8);
            $nextNumber = intval($lastNumber) + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format with leading zeros (4 digits)
        return $prefix . $date . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Scope for pending requests that need approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED
        ]);
    }
    
    /**
     * Scope for active (non-completed) requests
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_IN_PROGRESS
        ]);
    }
    
    /**
     * Get color class based on priority
     */
    public function getPriorityColorClass()
    {
        switch ($this->priority) {
            case self::PRIORITY_LOW:
                return 'bg-gray-100 text-gray-800';
            case self::PRIORITY_NORMAL:
                return 'bg-blue-100 text-blue-800';
            case self::PRIORITY_HIGH:
                return 'bg-orange-100 text-orange-800';
            case self::PRIORITY_URGENT:
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
    
    /**
     * Get icon based on priority
     */
    public function getPriorityIcon()
    {
        switch ($this->priority) {
            case self::PRIORITY_LOW:
                return 'fa-angle-down';
            case self::PRIORITY_NORMAL:
                return 'fa-equals';
            case self::PRIORITY_HIGH:
                return 'fa-angle-up';
            case self::PRIORITY_URGENT:
                return 'fa-exclamation';
            default:
                return 'fa-equals';
        }
    }
    
    /**
     * Get color class based on status
     */
    public function getStatusColorClass()
    {
        switch ($this->status) {
            case self::STATUS_DRAFT:
                return 'bg-gray-100 text-gray-800';
            case self::STATUS_PENDING:
                return 'bg-yellow-100 text-yellow-800';
            case self::STATUS_APPROVED:
                return 'bg-green-100 text-green-800';
            case self::STATUS_REJECTED:
                return 'bg-red-100 text-red-800';
            case self::STATUS_IN_PROGRESS:
                return 'bg-blue-100 text-blue-800';
            case self::STATUS_COMPLETED:
                return 'bg-indigo-100 text-indigo-800';
            case self::STATUS_CANCELLED:
                return 'bg-gray-100 text-gray-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
    
    /**
     * Get icon based on status
     */
    public function getStatusIcon()
    {
        switch ($this->status) {
            case self::STATUS_DRAFT:
                return 'fa-edit';
            case self::STATUS_PENDING:
                return 'fa-clock';
            case self::STATUS_APPROVED:
                return 'fa-check-circle';
            case self::STATUS_REJECTED:
                return 'fa-times-circle';
            case self::STATUS_IN_PROGRESS:
                return 'fa-spinner fa-spin';
            case self::STATUS_COMPLETED:
                return 'fa-check-double';
            case self::STATUS_CANCELLED:
                return 'fa-ban';
            default:
                return 'fa-question-circle';
        }
    }
    
    /**
     * Check if this transfer request can be approved
     */
    public function canBeApproved()
    {
        return in_array($this->status, ['pending', 'pending_approval']);
    }
    
    /**
     * Check if this transfer request can be rejected
     */
    public function canBeRejected()
    {
        return $this->status === self::STATUS_PENDING;
    }
    
    /**
     * Check if this transfer request is editable
     */
    public function isEditable()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }
}

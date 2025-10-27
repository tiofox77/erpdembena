<?php

namespace App\Models\SupplyChain;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransferRequest extends Model
{
    use HasFactory;
    
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
    const STATUS_PENDING = 'pending_approval';
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
     * Conta o número de pedidos pendentes de aprovação
     * 
     * @return int Número de pedidos pendentes
     */
    public static function countPendingApproval(): int
    {
        try {
            \Illuminate\Support\Facades\Log::info('=== INÍCIO countPendingApproval() ===', [
                'status_valor' => self::STATUS_PENDING,
            ]);
            
            $count = static::where('status', self::STATUS_PENDING)->count();
            
            \Illuminate\Support\Facades\Log::info('=== FIM countPendingApproval() ===', [
                'total_pedidos_pendentes' => $count
            ]);
            
            return $count;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao contar pedidos pendentes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }
    
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
        // Formato fixo: TR-YYYYMMDD-NNNN
        $prefix = 'TR';
        $date = now()->format('Ymd');
        $formatoBase = $prefix . '-' . $date . '-';
        
        // Iniciar um log detalhado
        \Illuminate\Support\Facades\Log::channel('daily')->info('===== INICIANDO GERAÇÃO DE NÚMERO =====', [
            'data' => $date,
            'formato_base' => $formatoBase,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Buscar EXPLICITAMENTE o maior número na base de dados, incluindo registros excluídos (soft deleted)
        $maxNumberQuery = "SELECT MAX(CAST(SUBSTRING_INDEX(request_number, '-', -1) AS UNSIGNED)) as max_num "
                      . "FROM sc_warehouse_transfer_requests "
                      . "WHERE request_number LIKE '{$formatoBase}%'";
        
        // Consulta direta via DB para incluir registros excluídos
        $maxNumber = \Illuminate\Support\Facades\DB::selectOne($maxNumberQuery);
        $nextNumber = ($maxNumber && $maxNumber->max_num) ? $maxNumber->max_num + 1 : 1;
        
        // Com exclusão permanente, não há mais necessidade de verificar registros com soft delete
        // pois todos os registros excluídos já foram removidos da base de dados
        
        \Illuminate\Support\Facades\Log::channel('daily')->info('Consulta SQL direta:', [
            'query' => $maxNumberQuery,
            'resultado' => $maxNumber ? $maxNumber->max_num : 'null',
            'próximo_número' => $nextNumber
        ]);
        
        // Também buscar TODOS os números existentes para verificação adicional
        $existingNumbers = self::where('request_number', 'like', $formatoBase . '%')
            ->pluck('request_number')
            ->toArray();
        
        \Illuminate\Support\Facades\Log::channel('daily')->info('Números existentes na base de dados:', [
            'total' => count($existingNumbers),
            'números' => $existingNumbers
        ]);
            
        // Caso especial - verificar explicitamente TR-20250618-0004
        $specialCheck = self::where('request_number', 'TR-20250618-0004')->exists();
        \Illuminate\Support\Facades\Log::channel('daily')->info('Verificando TR-20250618-0004 explicitamente:', [
            'existe' => $specialCheck ? 'SIM' : 'NÃO' 
        ]);
        
        // Forçar o próximo número a ser maior que 4 caso TR-20250618-0004 já exista
        if ($specialCheck && $formatoBase === 'TR-20250618-' && $nextNumber <= 4) {
            $nextNumber = 5;
            \Illuminate\Support\Facades\Log::channel('daily')->warning('Forçando número para 5 devido a conflito conhecido!');
        }
        
        // Gerar o número com o próximo valor sequencial
        $requestNumber = $formatoBase . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        // TRIPLA verificação para garantir unicidade
        $attempts = 0;
        $maxAttempts = 100; // Limite de segurança para evitar loop infinito
        
        while ((in_array($requestNumber, $existingNumbers) || self::where('request_number', $requestNumber)->exists()) 
               && $attempts < $maxAttempts) {
            $nextNumber++;
            $requestNumber = $formatoBase . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $attempts++;
            
            \Illuminate\Support\Facades\Log::channel('daily')->warning('Conflito detectado, incrementando número:', [
                'tentativa' => $attempts,
                'novo_número' => $requestNumber
            ]);
        }
        
        if ($attempts >= $maxAttempts) {
            \Illuminate\Support\Facades\Log::channel('daily')->error('ERRO GRAVE: Excedido limite de tentativas para gerar número único!');
            throw new \RuntimeException('Não foi possível gerar um número de pedido único após ' . $maxAttempts . ' tentativas.');
        }
        
        // Verificação final simples (agora que eliminamos permanentemente)
        $exists = self::where('request_number', $requestNumber)->exists();
        
        \Illuminate\Support\Facades\Log::channel('daily')->info('Número final gerado:', [
            'request_number' => $requestNumber,
            'já_existe' => $exists ? 'SIM - ERRO!' : 'NÃO - OK'
        ]);
        
        // Se ainda existe, algo está muito errado, lançar erro
        if ($exists) {
            \Illuminate\Support\Facades\Log::channel('daily')->emergency('ERRO CRÍTICO: Número gerado já existe na base de dados!');
            throw new \RuntimeException('Falha grave na geração de número único. Por favor contacte o suporte técnico.');
        }
        
        return $requestNumber;
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
        return in_array($this->status, ['pending', 'pending_approval']);
    }
    
    /**
     * Check if this transfer request is editable
     */
    public function isEditable()
    {
        // Permitir edição também para pedidos em aprovação pendente
        return in_array($this->status, [
            self::STATUS_DRAFT, 
            self::STATUS_REJECTED, 
            self::STATUS_PENDING, 
            'pending_approval' // Status usado no sistema
        ]);
    }
}

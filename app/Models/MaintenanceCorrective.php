<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Technician;
use App\Models\User;

class MaintenanceCorrective extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The "booted" method of the model.
     * Garante que todos os relacionamentos também filtrem registros excluídos
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function($builder) {
            $builder->whereNull('deleted_at');
        });
    }

    protected $table = 'maintenance_correctives';

    protected $fillable = [
        'year',
        'month',
        'week',
        'system_process',
        'equipment_id',
        'failure_mode_id',
        'failure_cause_id',
        'start_time',
        'end_time',
        'downtime_length',
        'description',
        'actions_taken',
        'reported_by',
        'resolved_by',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_downtime',
    ];

    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    /**
     * Get list of possible statuses with translations
     * 
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_OPEN => __('messages.open'),
            self::STATUS_IN_PROGRESS => __('messages.in_progress'),
            self::STATUS_RESOLVED => __('messages.resolved'),
            self::STATUS_CLOSED => __('messages.closed'),
        ];
    }

    // Relationships
    public function equipment()
    {
        return $this->belongsTo(MaintenanceEquipment::class, 'equipment_id');
    }

    public function failureMode()
    {
        return $this->belongsTo(FailureMode::class, 'failure_mode_id');
    }

    public function failureCause()
    {
        return $this->belongsTo(FailureCause::class, 'failure_cause_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver()
    {
        return $this->belongsTo(Technician::class, 'resolved_by');
    }

    public function tasks()
    {
        return $this->hasMany(MaintenanceTask::class, 'corrective_id');
    }

    // Scopes
    public function scopeFilterByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }

        return $query;
    }

    public function scopeFilterByEquipment($query, $equipmentId)
    {
        if ($equipmentId) {
            return $query->where('equipment_id', $equipmentId);
        }

        return $query;
    }

    public function scopeFilterByYear($query, $year)
    {
        if ($year) {
            return $query->where('year', $year);
        }

        return $query;
    }

    /**
     * Gera um PDF de relatório para manutenção corretiva
     *
     * @param \App\Models\MaintenanceCorrective|null $corrective Registro específico de manutenção corretiva (opcional)
     * @param array $filters Filtros para relatório de múltiplos registros (opcional)
     * @return string Caminho do arquivo PDF gerado
     */
    public static function generatePdf($corrective = null, $filters = [])
    {
        // Se um registro específico foi fornecido, gera relatório para apenas esse registro
        if ($corrective) {
            return self::generateSinglePdf($corrective);
        }
        
        // Caso contrário, gera relatório para múltiplos registros com base nos filtros
        return self::generateMultiplePdf($filters);
    }
    
    /**
     * Gera um PDF de relatório para um único registro de manutenção corretiva
     *
     * @param \App\Models\MaintenanceCorrective $corrective Registro de manutenção corretiva
     * @return string Caminho do arquivo PDF gerado
     */
    private static function generateSinglePdf($corrective)
    {
        try {
            // Garantir que todos os relacionamentos estejam carregados
            if (!$corrective->relationLoaded('equipment')) {
                $corrective->load(['equipment', 'failureMode', 'failureCause', 'reporter', 'resolver']);
            }
            
            // Registrar informações para debugging
            \Log::info('Gerando PDF para manutenção corretiva:', [
                'id' => $corrective->id,
                'equipment' => $corrective->equipment ? $corrective->equipment->name : 'N/A',
                'status' => $corrective->status,
                'start_time' => $corrective->start_time
            ]);
            
            $pdf = \PDF::loadView('pdf.maintenance-corrective-report', [
                'corrective' => $corrective,
                'isSingleReport' => true,
                'generatedAt' => now(),
            ]);
            
            $fileName = 'maintenance_corrective_' . $corrective->id . '_' . date('YmdHis') . '.pdf';
            $storagePath = 'reports/' . $fileName;
            
            // Salvar o PDF no storage
            \Storage::put('public/' . $storagePath, $pdf->output());
            
            return $storagePath;
        } catch (\Exception $e) {
            // Registrar o erro com detalhes completos
            \Log::error('Erro ao gerar PDF individual: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Relançar a exceção para ser tratada pelo controlador
            throw new \Exception('Erro ao gerar PDF: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Gera um PDF de relatório para múltiplos registros de manutenção corretiva
     *
     * @param array $filters Filtros para os registros
     * @return string Caminho do arquivo PDF gerado
     */
    private static function generateMultiplePdf($filters = [])
    {
        try {
            \Log::info('Iniciando geração de PDF de relatório múltiplo com filtros:', $filters);
            
            $query = self::query();
            
            // Aplicar filtros
            if (!empty($filters['year'])) {
                $query->filterByYear($filters['year']);
                \Log::debug('Aplicado filtro de ano: ' . $filters['year']);
            }
            
            if (!empty($filters['month'])) {
                $query->filterByMonth($filters['month']);
                \Log::debug('Aplicado filtro de mês: ' . $filters['month']);
            }
            
            if (!empty($filters['status'])) {
                $query->filterByStatus($filters['status']);
                \Log::debug('Aplicado filtro de status: ' . $filters['status']);
            }
            
            if (!empty($filters['equipment_id'])) {
                $query->filterByEquipment($filters['equipment_id']);
                \Log::debug('Aplicado filtro de equipamento ID: ' . $filters['equipment_id']);
            }
            
            // Carregar relacionamentos necessários
            $correctives = $query->with(['equipment', 'failureMode', 'failureCause', 'reporter', 'resolver'])
                                ->orderBy('start_time', 'desc')
                                ->get();
            
            \Log::info('Registros encontrados para o relatório: ' . $correctives->count());
            
            // Preparar título do período baseado nos filtros
            $periodTitle = '';
            
            if (!empty($filters['year'])) {
                $periodTitle .= $filters['year'];
                
                if (!empty($filters['month'])) {
                    $monthName = \Carbon\Carbon::create()->month($filters['month'])->translatedFormat('F');
                    $periodTitle .= ' - ' . $monthName;
                }
            } else {
                $periodTitle = __('messages.all_time');
            }
            
            if (!empty($filters['equipment_id']) && $equipment = \App\Models\MaintenanceEquipment::find($filters['equipment_id'])) {
                $periodTitle .= ' | ' . $equipment->name;
            }
            
            // Verificar a existência do template antes de carregar
            $viewPath = 'pdf.maintenance-corrective-report';
            if (!view()->exists($viewPath)) {
                throw new \Exception("Template de PDF não encontrado: {$viewPath}");
            }
            
            \Log::debug('Carregando template PDF com período: ' . $periodTitle);
            
            $pdf = \PDF::loadView($viewPath, [
                'correctives' => $correctives,
                'periodTitle' => $periodTitle,
                'generatedAt' => now(),
                'filters' => $filters
            ]);
            
            $fileName = 'maintenance_correctives_report_' . date('YmdHis') . '.pdf';
            $storagePath = 'reports/' . $fileName;
            
            // Verificar pasta de armazenamento
            $directory = 'public/reports';
            if (!\Storage::exists($directory)) {
                \Storage::makeDirectory($directory);
                \Log::info('Diretório de relatórios criado: ' . $directory);
            }
            
            // Salvar o PDF no storage
            \Storage::put('public/' . $storagePath, $pdf->output());
            \Log::info('PDF salvo com sucesso em: ' . $storagePath);
            
            return $storagePath;
        } catch (\Exception $e) {
            // Registrar erro com detalhes completos
            \Log::error('Erro ao gerar PDF múltiplo: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Filtros utilizados: ' . json_encode($filters));
            
            // Relançar a exceção para ser tratada pelo controlador
            throw new \Exception('Erro ao gerar relatório PDF: ' . $e->getMessage(), 0, $e);
        }
    }
    
    // O método getFormattedDowntimeAttribute já deve estar definido em outra parte da classe

    public function scopeFilterByMonth($query, $month)
    {
        if ($month) {
            return $query->where('month', $month);
        }

        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('system_process', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('actions_taken', 'like', "%{$search}%")
                  ->orWhereHas('failureMode', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('failureCause', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    // Accessors and Mutators
    public function getFormattedDowntimeAttribute()
    {
        if (!$this->downtime_length) {
            return '00:00:00';
        }

        // If downtime_length is already in hours:minutes:seconds format, return it
        if (strpos($this->downtime_length, ':') !== false) {
            return $this->downtime_length;
        }

        // If downtime_length is a number (hours), convert to hours:minutes:seconds
        $hours = floor($this->downtime_length);
        $minutes = floor(($this->downtime_length - $hours) * 60);
        $seconds = floor((($this->downtime_length - $hours) * 60 - $minutes) * 60);

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function calculateDowntimeLength()
    {
        if ($this->start_time && $this->end_time) {
            $start = Carbon::parse($this->start_time);
            $end = Carbon::parse($this->end_time);
            $diffInHours = $end->diffInSeconds($start) / 3600; // Convert seconds to hours
            $this->downtime_length = number_format($diffInHours, 2);
        }
    }

    // Status helpers
    public function isOpen()
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isClosed()
    {
        return $this->status === self::STATUS_CLOSED;
    }

    // Helper accessors for backward compatibility
    public function getFailureModeAttribute()
    {
        if ($this->relationLoaded('failureMode')) {
            $mode = $this->getRelation('failureMode');
            return $mode ? $mode->name : null;
        }

        $mode = $this->failureMode()->first();
        return $mode ? $mode->name : null;
    }

    public function getFailureModeNameAttribute()
    {
        if ($this->relationLoaded('failureMode')) {
            $mode = $this->getRelation('failureMode');
            return $mode ? $mode->name : null;
        }

        $mode = $this->failureMode()->first();
        return $mode ? $mode->name : null;
    }

    public function getFailureModeCategoryAttribute()
    {
        if ($this->relationLoaded('failureMode')) {
            $mode = $this->getRelation('failureMode');
            if ($mode && $mode->relationLoaded('category')) {
                $category = $mode->getRelation('category');
                return $category ? $category->name : null;
            }
        }

        $mode = $this->failureMode()->with('category')->first();
        return ($mode && $mode->category) ? $mode->category->name : null;
    }

    public function getFailureCauseNameAttribute()
    {
        if ($this->relationLoaded('failureCause')) {
            $cause = $this->getRelation('failureCause');
            return $cause ? $cause->name : null;
        }

        $cause = $this->failureCause()->first();
        return $cause ? $cause->name : null;
    }

    public function getFailureCauseCategoryAttribute()
    {
        if ($this->relationLoaded('failureCause')) {
            $cause = $this->getRelation('failureCause');
            if ($cause && $cause->relationLoaded('category')) {
                $category = $cause->getRelation('category');
                return $category ? $category->name : null;
            }
        }

        $cause = $this->failureCause()->with('category')->first();
        return ($cause && $cause->category) ? $cause->category->name : null;
    }
}
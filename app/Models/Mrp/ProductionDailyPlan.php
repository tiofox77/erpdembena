<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Mrp\FailureCategory;
use App\Models\Mrp\FailureRootCause;

class ProductionDailyPlan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_production_daily_plans';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'schedule_id',
        'production_date',
        'start_time',
        'end_time',
        'planned_quantity',
        'actual_quantity',
        'defect_quantity',
        'has_breakdown',
        'breakdown_minutes',
        'failure_category_id',
        'failure_root_causes',
        'status',
        'notes',
        'created_by',
        'updated_by',
        'shift_id',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'production_date' => 'date',
        'planned_quantity' => 'decimal:2',
        'actual_quantity' => 'decimal:2',
        'defect_quantity' => 'decimal:2',
        'has_breakdown' => 'boolean',
        'failure_root_causes' => 'array',
    ];

    /**
     * Get the production schedule this daily plan belongs to.
     */
    public function schedule()
    {
        return $this->belongsTo(ProductionSchedule::class, 'schedule_id');
    }

    /**
     * Get the user that created the daily plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the daily plan.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Get the failure category associated with this daily plan.
     */
    public function failureCategory()
    {
        return $this->belongsTo(FailureCategory::class, 'failure_category_id');
    }
    
    /**
     * Get the related failure root causes (accessor)
     * 
     * This will retrieve the root causes objects from the stored IDs
     */
    public function getFailureRootCausesObjectsAttribute()
    {
        if (!$this->failure_root_causes) {
            return collect([]);
        }
        
        return FailureRootCause::whereIn('id', $this->failure_root_causes)->get();
    }
}

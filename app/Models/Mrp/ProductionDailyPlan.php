<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

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
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'production_date' => 'date',
        'planned_quantity' => 'decimal:3',
        'actual_quantity' => 'decimal:3',
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
}

<?php

namespace App\Models\Mrp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class FinancialReport extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'mrp_financial_reports';

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'report_number',
        'title',
        'report_type',
        'start_date',
        'end_date',
        'total_material_cost',
        'total_labor_cost',
        'total_overhead_cost',
        'total_cost',
        'average_inventory_value',
        'inventory_turnover_rate',
        'cost_breakdown',
        'status',
        'notes',
        'created_by',
        'approved_by',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_material_cost' => 'decimal:2',
        'total_labor_cost' => 'decimal:2',
        'total_overhead_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'average_inventory_value' => 'decimal:2',
        'inventory_turnover_rate' => 'decimal:2',
        'cost_breakdown' => 'json',
    ];

    /**
     * Get the user that created the financial report.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that approved the financial report.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

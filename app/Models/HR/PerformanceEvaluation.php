<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HR\Employee;
use App\Models\HR\Department;
use App\Models\User;
use Carbon\Carbon;

/**
 * Performance Evaluation Model
 * Based on Quarterly Performance Appraisal Form
 */
class PerformanceEvaluation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Employee Details
        'employee_id',
        'supervisor_id',
        'department_id',
        
        // Evaluation Period
        'evaluation_quarter',
        'evaluation_year',
        'period_start',
        'period_end',
        'evaluation_date',
        
        // Performance Criteria (1-5)
        'productivity_output',
        'productivity_output_remarks',
        'quality_of_work',
        'quality_of_work_remarks',
        'attendance_punctuality',
        'attendance_punctuality_remarks',
        'safety_compliance',
        'safety_compliance_remarks',
        'machine_operation_skills',
        'machine_operation_skills_remarks',
        'teamwork_cooperation',
        'teamwork_cooperation_remarks',
        'adaptability_learning',
        'adaptability_learning_remarks',
        'housekeeping_5s',
        'housekeeping_5s_remarks',
        'discipline_attitude',
        'discipline_attitude_remarks',
        'initiative_responsibility',
        'initiative_responsibility_remarks',
        
        // Overall Performance Summary
        'average_score',
        'performance_level',
        'eligible_for_bonus',
        
        // Comments
        'supervisor_comments',
        'employee_comments',
        
        // Signatures
        'supervisor_signed_by',
        'supervisor_signed_at',
        'department_head_signed_by',
        'department_head_signed_at',
        'employee_signed_at',
        
        // Status & Metadata
        'status',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'evaluation_date' => 'date',
        'evaluation_year' => 'integer',
        'average_score' => 'decimal:2',
        'eligible_for_bonus' => 'boolean',
        'supervisor_signed_at' => 'datetime',
        'department_head_signed_at' => 'datetime',
        'employee_signed_at' => 'datetime',
        // Criteria as integers (1-5)
        'productivity_output' => 'integer',
        'quality_of_work' => 'integer',
        'attendance_punctuality' => 'integer',
        'safety_compliance' => 'integer',
        'machine_operation_skills' => 'integer',
        'teamwork_cooperation' => 'integer',
        'adaptability_learning' => 'integer',
        'housekeeping_5s' => 'integer',
        'discipline_attitude' => 'integer',
        'initiative_responsibility' => 'integer',
    ];

    // Rating Scale Constants (0-5)
    public const RATING_POOR = 0;
    public const RATING_UNSATISFACTORY = 1;
    public const RATING_SATISFACTORY = 2;
    public const RATING_GOOD = 3;
    public const RATING_VERY_GOOD = 4;
    public const RATING_EXCELLENT = 5;

    public const RATINGS = [
        self::RATING_POOR => 'POOR',
        self::RATING_UNSATISFACTORY => 'Unsatisfactory',
        self::RATING_SATISFACTORY => 'SATISFACTORY',
        self::RATING_GOOD => 'GOOD',
        self::RATING_VERY_GOOD => 'V.GOOD',
        self::RATING_EXCELLENT => 'EXCELLENT',
    ];

    // Periods (Semesters + Special)
    public const QUARTERS = [
        'S1' => '1º Semestre (Jan-Jun)',
        'S2' => '2º Semestre (Jul-Dez)',
        'SPECIAL' => 'Avaliação Especial',
    ];

    // Performance Levels
    public const LEVEL_NEEDS_IMPROVEMENT = 'needs_improvement';
    public const LEVEL_SATISFACTORY = 'satisfactory';
    public const LEVEL_GOOD = 'good';
    public const LEVEL_EXCELLENT = 'excellent';

    public const PERFORMANCE_LEVELS = [
        self::LEVEL_NEEDS_IMPROVEMENT => 'Needs Improvement',
        self::LEVEL_SATISFACTORY => 'Satisfactory',
        self::LEVEL_GOOD => 'Good',
        self::LEVEL_EXCELLENT => 'Excellent',
    ];

    // Status Constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_SUPERVISOR = 'pending_supervisor';
    public const STATUS_PENDING_DEPARTMENT_HEAD = 'pending_department_head';
    public const STATUS_PENDING_EMPLOYEE = 'pending_employee';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Rascunho',
        self::STATUS_PENDING_SUPERVISOR => 'Aguardando Supervisor',
        self::STATUS_PENDING_DEPARTMENT_HEAD => 'Aguardando Chefe Depto',
        self::STATUS_PENDING_EMPLOYEE => 'Aguardando Funcionário',
        self::STATUS_COMPLETED => 'Concluído',
        self::STATUS_CANCELLED => 'Cancelado',
    ];

    // Performance Criteria with descriptions
    public const CRITERIA = [
        'productivity_output' => [
            'name' => 'Productivity / Output',
            'name_pt' => 'Produtividade / Resultado',
            'description' => 'Meets or exceeds daily/weekly on production targets.',
        ],
        'quality_of_work' => [
            'name' => 'Quality of Work',
            'name_pt' => 'Qualidade do Trabalho',
            'description' => 'Produces (contributes) work that meets quality standards with minimal rework or defects.',
        ],
        'attendance_punctuality' => [
            'name' => 'Attendance & Punctuality',
            'name_pt' => 'Assiduidade e Pontualidade',
            'description' => 'Reports to work on time; follows shift schedules and break times.',
        ],
        'safety_compliance' => [
            'name' => 'Safety Compliance',
            'name_pt' => 'Conformidade de Segurança',
            'description' => 'Follows all safety procedures, uses PPE correctly, reports hazards.',
        ],
        'machine_operation_skills' => [
            'name' => 'Machine Operation Skills',
            'name_pt' => 'Habilidades de Operação de Máquinas',
            'description' => 'Efficient in operating assigned machines/equipment.',
        ],
        'teamwork_cooperation' => [
            'name' => 'Teamwork & Cooperation',
            'name_pt' => 'Trabalho em Equipa e Cooperação',
            'description' => 'Works well with team members, supports others, communicates effectively.',
        ],
        'adaptability_learning' => [
            'name' => 'Adaptability & Learning',
            'name_pt' => 'Adaptabilidade e Aprendizagem',
            'description' => 'Responds positively to new tasks, instructions, and training.',
        ],
        'housekeeping_5s' => [
            'name' => 'Housekeeping (5S)',
            'name_pt' => 'Organização (5S)',
            'description' => 'Keeps workstation clean and organized, follows 5S principles.',
        ],
        'discipline_attitude' => [
            'name' => 'Discipline & Attitude',
            'name_pt' => 'Disciplina e Atitude',
            'description' => 'Follows company rules, shows positive attitude and respects.',
        ],
        'initiative_responsibility' => [
            'name' => 'Initiative & Responsibility',
            'name_pt' => 'Iniciativa e Responsabilidade',
            'description' => 'Takes ownership of tasks, suggests improvements.',
        ],
    ];

    // ==================== RELATIONSHIPS ====================

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function supervisorSignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_signed_by');
    }

    public function departmentHeadSignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'department_head_signed_by');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== ACCESSORS ====================

    /**
     * Get quarter display name.
     */
    public function getQuarterNameAttribute(): string
    {
        return self::QUARTERS[$this->evaluation_quarter] ?? $this->evaluation_quarter;
    }

    /**
     * Get full period display (e.g., "4º Trimestre 2025").
     */
    public function getPeriodDisplayAttribute(): string
    {
        $quarterNum = match($this->evaluation_quarter) {
            'Q1' => '1º',
            'Q2' => '2º',
            'Q3' => '3º',
            'Q4' => '4º',
            default => '',
        };
        return "{$quarterNum} Trimestre {$this->evaluation_year}";
    }

    /**
     * Get status name.
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get status color class.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_PENDING_SUPERVISOR => 'bg-yellow-100 text-yellow-800',
            self::STATUS_PENDING_DEPARTMENT_HEAD => 'bg-orange-100 text-orange-800',
            self::STATUS_PENDING_EMPLOYEE => 'bg-blue-100 text-blue-800',
            self::STATUS_COMPLETED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get performance level name.
     */
    public function getPerformanceLevelNameAttribute(): string
    {
        return self::PERFORMANCE_LEVELS[$this->performance_level] ?? 'Not Rated';
    }

    /**
     * Get performance level color.
     */
    public function getPerformanceLevelColorAttribute(): string
    {
        return match($this->performance_level) {
            self::LEVEL_EXCELLENT => 'bg-green-100 text-green-800',
            self::LEVEL_GOOD => 'bg-blue-100 text-blue-800',
            self::LEVEL_SATISFACTORY => 'bg-yellow-100 text-yellow-800',
            self::LEVEL_NEEDS_IMPROVEMENT => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // ==================== METHODS ====================

    /**
     * Calculate and update average score.
     */
    public function calculateAverageScore(): float
    {
        $criteria = [
            $this->productivity_output,
            $this->quality_of_work,
            $this->attendance_punctuality,
            $this->safety_compliance,
            $this->machine_operation_skills,
            $this->teamwork_cooperation,
            $this->adaptability_learning,
            $this->housekeeping_5s,
            $this->discipline_attitude,
            $this->initiative_responsibility,
        ];

        $validCriteria = array_filter($criteria, fn($value) => $value !== null);
        
        if (empty($validCriteria)) {
            return 0.0;
        }

        return round(array_sum($validCriteria) / count($validCriteria), 2);
    }

    /**
     * Determine performance level based on average score percentage.
     * Converts 0-5 scale to percentage and determines level:
     * 0-36% = Poor (Needs Improvement)
     * 37-61% = Unsatisfactory
     * 62-73% = Satisfactory
     * 74-79% = Good
     * 80%+ = Excellent
     */
    public function determinePerformanceLevel(): string
    {
        $score = $this->average_score ?? $this->calculateAverageScore();
        
        // Convert 0-5 scale to percentage (0-100%)
        $percentage = ($score / 5) * 100;

        return match(true) {
            $percentage >= 80 => self::LEVEL_EXCELLENT,
            $percentage >= 74 => self::LEVEL_GOOD,
            $percentage >= 62 => self::LEVEL_SATISFACTORY,
            default => self::LEVEL_NEEDS_IMPROVEMENT,
        };
    }

    /**
     * Update scores and performance level.
     */
    public function updateScores(): void
    {
        $this->average_score = $this->calculateAverageScore();
        $this->performance_level = $this->determinePerformanceLevel();
        $this->save();
    }

    /**
     * Get rating label for a score.
     */
    public static function getRatingLabel(int $rating): string
    {
        return self::RATINGS[$rating] ?? 'N/A';
    }

    /**
     * Get rating color for a score (matches Excel color scheme).
     */
    public static function getRatingColor(int $rating): string
    {
        return match($rating) {
            5 => 'bg-blue-100 text-blue-800',          // Blue - EXCELLENT
            4 => 'bg-green-700 text-white',            // Dark Green - V.GOOD
            3 => 'bg-green-300 text-green-900',        // Light Green - GOOD
            2 => 'bg-yellow-200 text-yellow-900',      // Yellow - SATISFACTORY
            1 => 'bg-orange-300 text-orange-900',      // Orange - Unsatisfactory
            0 => 'bg-red-300 text-red-900',            // Red - POOR
            default => 'bg-purple-100 text-purple-800', // Purple - NO RATE
        };
    }

    /**
     * Check if all criteria are filled.
     */
    public function isComplete(): bool
    {
        return $this->productivity_output !== null
            && $this->quality_of_work !== null
            && $this->attendance_punctuality !== null
            && $this->safety_compliance !== null
            && $this->machine_operation_skills !== null
            && $this->teamwork_cooperation !== null
            && $this->adaptability_learning !== null
            && $this->housekeeping_5s !== null
            && $this->discipline_attitude !== null
            && $this->initiative_responsibility !== null;
    }

    /**
     * Get period dates based on quarter and year.
     */
    public static function getQuarterDates(string $quarter, int $year): array
    {
        return match($quarter) {
            'Q1' => [
                'start' => Carbon::create($year, 1, 1),
                'end' => Carbon::create($year, 3, 31),
            ],
            'Q2' => [
                'start' => Carbon::create($year, 4, 1),
                'end' => Carbon::create($year, 6, 30),
            ],
            'Q3' => [
                'start' => Carbon::create($year, 7, 1),
                'end' => Carbon::create($year, 9, 30),
            ],
            'Q4' => [
                'start' => Carbon::create($year, 10, 1),
                'end' => Carbon::create($year, 12, 31),
            ],
            default => [
                'start' => Carbon::create($year, 1, 1),
                'end' => Carbon::create($year, 3, 31),
            ],
        };
    }
}

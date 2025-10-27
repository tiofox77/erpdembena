<?php

declare(strict_types=1);

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HR\Employee;
use App\Models\User;
use Carbon\Carbon;

class PerformanceEvaluation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'evaluation_type',
        'period_start',
        'period_end',
        'overall_score',
        'goals_achievement',
        'technical_skills',
        'soft_skills',
        'attendance_punctuality',
        'teamwork_collaboration',
        'initiative_innovation',
        'quality_of_work',
        'strengths',
        'areas_for_improvement',
        'development_plan',
        'additional_comments',
        'status',
        'evaluation_date',
        'next_evaluation_date',
        'attachments',
        'evaluated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'evaluation_date' => 'date',
        'next_evaluation_date' => 'date',
        'overall_score' => 'decimal:2',
        'goals_achievement' => 'decimal:2',
        'technical_skills' => 'decimal:2',
        'soft_skills' => 'decimal:2',
        'attendance_punctuality' => 'decimal:2',
        'teamwork_collaboration' => 'decimal:2',
        'initiative_innovation' => 'decimal:2',
        'quality_of_work' => 'decimal:2',
        'attachments' => 'array',
    ];

    // Evaluation Types Constants
    public const TYPE_ANNUAL = 'annual';
    public const TYPE_SEMI_ANNUAL = 'semi_annual';
    public const TYPE_QUARTERLY = 'quarterly';
    public const TYPE_PROBATIONARY = 'probationary';
    public const TYPE_PROJECT_BASED = 'project_based';
    public const TYPE_PERFORMANCE_IMPROVEMENT = 'performance_improvement';

    public const EVALUATION_TYPES = [
        self::TYPE_ANNUAL => 'Annual Review',
        self::TYPE_SEMI_ANNUAL => 'Semi-Annual Review',
        self::TYPE_QUARTERLY => 'Quarterly Review', 
        self::TYPE_PROBATIONARY => 'Probationary Review',
        self::TYPE_PROJECT_BASED => 'Project-Based Review',
        self::TYPE_PERFORMANCE_IMPROVEMENT => 'Performance Improvement Review',
    ];

    // Status Constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PENDING => 'Pending Review',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    /**
     * Get the employee that owns the performance evaluation.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who conducted the evaluation.
     */
    public function evaluatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Get evaluation type name.
     */
    public function getEvaluationTypeNameAttribute(): string
    {
        return self::EVALUATION_TYPES[$this->evaluation_type] ?? $this->evaluation_type;
    }

    /**
     * Get evaluation type color class.
     */
    public function getEvaluationTypeColorAttribute(): string
    {
        return match($this->evaluation_type) {
            self::TYPE_ANNUAL => 'bg-blue-100 text-blue-800',
            self::TYPE_SEMI_ANNUAL => 'bg-green-100 text-green-800',
            self::TYPE_QUARTERLY => 'bg-yellow-100 text-yellow-800',
            self::TYPE_PROBATIONARY => 'bg-orange-100 text-orange-800',
            self::TYPE_PROJECT_BASED => 'bg-purple-100 text-purple-800',
            self::TYPE_PERFORMANCE_IMPROVEMENT => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
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
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_COMPLETED => 'bg-blue-100 text-blue-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get the overall performance rating based on score.
     */
    public function getPerformanceRatingAttribute(): string
    {
        if (!$this->overall_score) {
            return 'Not Rated';
        }

        return match(true) {
            $this->overall_score >= 9.0 => 'Outstanding',
            $this->overall_score >= 8.0 => 'Excellent',
            $this->overall_score >= 7.0 => 'Good',
            $this->overall_score >= 6.0 => 'Satisfactory',
            $this->overall_score >= 5.0 => 'Needs Improvement',
            default => 'Unsatisfactory',
        };
    }

    /**
     * Get the performance rating color.
     */
    public function getPerformanceRatingColorAttribute(): string
    {
        if (!$this->overall_score) {
            return 'bg-gray-100 text-gray-800';
        }

        return match(true) {
            $this->overall_score >= 9.0 => 'bg-green-100 text-green-800',
            $this->overall_score >= 8.0 => 'bg-blue-100 text-blue-800',
            $this->overall_score >= 7.0 => 'bg-indigo-100 text-indigo-800',
            $this->overall_score >= 6.0 => 'bg-yellow-100 text-yellow-800',
            $this->overall_score >= 5.0 => 'bg-orange-100 text-orange-800',
            default => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Calculate average skill scores.
     */
    public function getAverageSkillScoreAttribute(): float
    {
        $skills = [
            $this->technical_skills,
            $this->soft_skills,
            $this->attendance_punctuality,
            $this->teamwork_collaboration,
            $this->initiative_innovation,
            $this->quality_of_work,
        ];

        $nonNullSkills = array_filter($skills, fn($skill) => $skill !== null);
        
        if (empty($nonNullSkills)) {
            return 0.0;
        }

        return round(array_sum($nonNullSkills) / count($nonNullSkills), 2);
    }

    /**
     * Check if evaluation is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== self::STATUS_COMPLETED 
            && $this->status !== self::STATUS_APPROVED 
            && $this->evaluation_date 
            && Carbon::parse($this->evaluation_date)->isPast();
    }

    /**
     * Get days until evaluation due date.
     */
    public function getDaysUntilDueAttribute(): int
    {
        if (!$this->evaluation_date) {
            return 0;
        }

        return Carbon::now()->diffInDays(Carbon::parse($this->evaluation_date), false);
    }
}

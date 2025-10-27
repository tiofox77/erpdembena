<?php

declare(strict_types=1);

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'training_type',
        'training_title',
        'training_description',
        'provider',
        'status',
        'start_date',
        'end_date',
        'duration_hours',
        'location',
        'cost',
        'budget_approved',
        'completion_status',
        'completion_date',
        'certification_received',
        'certification_expiry_date',
        'trainer_name',
        'trainer_email',
        'skills_acquired',
        'evaluation_score',
        'feedback',
        'next_steps',
        'attachments',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completion_date' => 'date',
        'certification_expiry_date' => 'date',
        'duration_hours' => 'decimal:2',
        'cost' => 'decimal:2',
        'evaluation_score' => 'decimal:2',
        'budget_approved' => 'boolean',
        'certification_received' => 'boolean',
        'attachments' => 'array',
    ];

    /**
     * Training type constants
     */
    const TRAINING_TYPES = [
        'technical' => 'Technical Training',
        'soft_skills' => 'Soft Skills',
        'leadership' => 'Leadership',
        'safety' => 'Safety Training',
        'compliance' => 'Compliance Training',
        'orientation' => 'New Employee Orientation',
        'certification' => 'Professional Certification',
        'workshop' => 'Workshop/Seminar',
        'conference' => 'Conference',
        'online' => 'Online Course',
        'mentoring' => 'Mentoring Program',
        'cross_training' => 'Cross Training',
    ];

    /**
     * Training status constants
     */
    const STATUSES = [
        'planned' => 'Planned',
        'approved' => 'Approved',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'postponed' => 'Postponed',
        'failed' => 'Failed',
    ];

    /**
     * Completion status constants
     */
    const COMPLETION_STATUSES = [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'withdrawn' => 'Withdrawn',
    ];

    /**
     * Get the employee that owns the training
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created this training
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get status color for UI display
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'bg-blue-100 text-blue-800',
            'approved' => 'bg-green-100 text-green-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'postponed' => 'bg-orange-100 text-orange-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get completion status color for UI display
     */
    public function getCompletionStatusColorAttribute(): string
    {
        return match ($this->completion_status) {
            'not_started' => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'withdrawn' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get training type color for UI display
     */
    public function getTrainingTypeColorAttribute(): string
    {
        return match ($this->training_type) {
            'technical' => 'bg-purple-100 text-purple-800',
            'soft_skills' => 'bg-pink-100 text-pink-800',
            'leadership' => 'bg-indigo-100 text-indigo-800',
            'safety' => 'bg-red-100 text-red-800',
            'compliance' => 'bg-yellow-100 text-yellow-800',
            'orientation' => 'bg-green-100 text-green-800',
            'certification' => 'bg-blue-100 text-blue-800',
            'workshop' => 'bg-orange-100 text-orange-800',
            'conference' => 'bg-teal-100 text-teal-800',
            'online' => 'bg-cyan-100 text-cyan-800',
            'mentoring' => 'bg-emerald-100 text-emerald-800',
            'cross_training' => 'bg-lime-100 text-lime-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get evaluation rating based on score
     */
    public function getEvaluationRatingAttribute(): string
    {
        if (!$this->evaluation_score) return '';
        
        return match (true) {
            $this->evaluation_score >= 9.0 => 'Excellent',
            $this->evaluation_score >= 8.0 => 'Very Good',
            $this->evaluation_score >= 7.0 => 'Good',
            $this->evaluation_score >= 6.0 => 'Satisfactory',
            $this->evaluation_score >= 5.0 => 'Needs Improvement',
            default => 'Poor',
        };
    }

    /**
     * Get evaluation color based on score
     */
    public function getEvaluationColorAttribute(): string
    {
        if (!$this->evaluation_score) return 'bg-gray-100 text-gray-800';
        
        return match (true) {
            $this->evaluation_score >= 8.0 => 'bg-green-100 text-green-800',
            $this->evaluation_score >= 7.0 => 'bg-blue-100 text-blue-800',
            $this->evaluation_score >= 6.0 => 'bg-yellow-100 text-yellow-800',
            $this->evaluation_score >= 5.0 => 'bg-orange-100 text-orange-800',
            default => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Check if training is expired (for certifications)
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->certification_expiry_date && 
               $this->certification_expiry_date->isPast();
    }

    /**
     * Check if training is expiring soon (within 30 days)
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->certification_expiry_date && 
               $this->certification_expiry_date->diffInDays(now()) <= 30 &&
               !$this->is_expired;
    }

    /**
     * Get progress percentage for in-progress trainings
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->completion_status === 'completed') return 100;
        if ($this->completion_status === 'not_started') return 0;
        if ($this->completion_status === 'failed' || $this->completion_status === 'withdrawn') return 0;
        
        // For in-progress, calculate based on dates
        if ($this->start_date && $this->end_date) {
            $totalDays = $this->start_date->diffInDays($this->end_date);
            $passedDays = $this->start_date->diffInDays(now());
            
            if ($totalDays > 0) {
                return min(100, max(0, (int) (($passedDays / $totalDays) * 100)));
            }
        }
        
        return 50; // Default for in-progress without dates
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(string $filename): mixed
    {
        $filePath = "training_attachments/{$this->id}/{$filename}";
        
        if (Storage::disk('private')->exists($filePath)) {
            return Storage::disk('private')->download($filePath);
        }
        
        return null;
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(string $filename): bool
    {
        $filePath = "training_attachments/{$this->id}/{$filename}";
        
        if (Storage::disk('private')->exists($filePath)) {
            Storage::disk('private')->delete($filePath);
            
            // Update attachments array
            $attachments = $this->attachments ?? [];
            $attachments = array_filter($attachments, fn($attachment) => $attachment !== $filename);
            $this->update(['attachments' => array_values($attachments)]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by training type
     */
    public function scopeByTrainingType($query, $type)
    {
        return $query->where('training_type', $type);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate = null)
    {
        $query->where('start_date', '>=', $startDate);
        
        if ($endDate) {
            $query->where('start_date', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * Scope for upcoming trainings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())
                    ->whereIn('status', ['planned', 'approved']);
    }

    /**
     * Scope for expired certifications
     */
    public function scopeExpiredCertifications($query)
    {
        return $query->where('certification_received', true)
                    ->where('certification_expiry_date', '<', now());
    }

    /**
     * Scope for expiring soon certifications
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('certification_received', true)
                    ->where('certification_expiry_date', '>', now())
                    ->where('certification_expiry_date', '<=', now()->addDays($days));
    }
}

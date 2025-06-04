<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'document_type',
        'title',
        'file_path',
        'expiry_date',
        'is_verified',
        'verified_by',
        'verification_date',
        'remarks',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'verification_date' => 'date',
        'is_verified' => 'boolean',
    ];

    /**
     * Document type constants
     */
    const TYPE_ID_CARD = 'id_card';
    const TYPE_CERTIFICATE = 'certificate';
    const TYPE_PROFESSIONAL_CARD = 'professional_card';
    const TYPE_CONTRACT = 'contract';
    const TYPE_OTHER = 'other';

    /**
     * Get all document types as array
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::TYPE_ID_CARD => 'ID Card',
            self::TYPE_CERTIFICATE => 'Certificate',
            self::TYPE_PROFESSIONAL_CARD => 'Professional Card',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get the employee that the document belongs to
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the verifier
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'verified_by');
    }
    
    /**
     * Get the full storage path for the document
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/public/' . $this->file_path);
    }
    
    /**
     * Get the public URL for the document
     */
    public function getPublicUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
    
    /**
     * Get formatted document type for display
     */
    public function getFormattedTypeAttribute(): string
    {
        return self::getDocumentTypes()[$this->document_type] ?? $this->document_type;
    }
}

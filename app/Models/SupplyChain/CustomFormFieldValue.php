<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFormFieldValue extends Model
{
    use HasFactory;

    protected $table = 'sc_custom_form_field_values';

    protected $fillable = [
        'submission_id',
        'field_id',
        'value',
    ];

    /**
     * Obtém a submissão à qual este valor pertence
     */
    public function submission()
    {
        return $this->belongsTo(CustomFormSubmission::class, 'submission_id');
    }

    /**
     * Obtém o campo ao qual este valor está associado
     */
    public function field()
    {
        return $this->belongsTo(CustomFormField::class, 'field_id');
    }

    /**
     * Obtém os arquivos anexados a este valor de campo (se for do tipo file)
     */
    public function attachments()
    {
        return $this->hasMany(CustomFormAttachment::class, 'field_value_id');
    }

    /**
     * Determina se o valor tem anexos
     */
    public function hasAttachments()
    {
        return $this->attachments()->exists();
    }

    /**
     * Formata o valor para exibição, dependendo do tipo do campo
     */
    public function getFormattedValueAttribute()
    {
        if (!$this->field) {
            return $this->value;
        }

        switch ($this->field->type) {
            case 'file':
                if ($this->hasAttachments()) {
                    $attachments = $this->attachments;
                    $fileLinks = [];
                    foreach ($attachments as $attachment) {
                        $fileLinks[] = $attachment->original_filename;
                    }
                    return implode(', ', $fileLinks);
                }
                return '';
            
            case 'date':
                return !empty($this->value) ? date('d/m/Y', strtotime($this->value)) : '';
                
            case 'select':
                // Para campos select, podemos exibir o label em vez do valor
                if (!empty($this->field->options) && !empty($this->value)) {
                    foreach ($this->field->options as $option) {
                        if (isset($option['value']) && $option['value'] == $this->value) {
                            return $option['label'] ?? $this->value;
                        }
                    }
                }
                return $this->value;
                
            default:
                return $this->value;
        }
    }
}

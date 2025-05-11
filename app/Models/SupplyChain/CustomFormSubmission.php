<?php

namespace App\Models\SupplyChain;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFormSubmission extends Model
{
    use HasFactory;

    protected $table = 'sc_custom_form_submissions';

    protected $fillable = [
        'form_id',
        'entity_id',
        'created_by',
    ];

    /**
     * Obtém o formulário ao qual esta submissão pertence
     */
    public function form()
    {
        return $this->belongsTo(CustomForm::class, 'form_id');
    }

    /**
     * Obtém a entidade (shipping note) relacionada a esta submissão
     */
    public function shippingNote()
    {
        return $this->belongsTo(ShippingNote::class, 'entity_id');
    }

    /**
     * Obtém o usuário que criou esta submissão
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtém os valores dos campos nesta submissão
     */
    public function fieldValues()
    {
        return $this->hasMany(CustomFormFieldValue::class, 'submission_id');
    }

    /**
     * Obtém um valor específico pela ID do campo
     */
    public function getValueByFieldId($fieldId)
    {
        return $this->fieldValues()->where('field_id', $fieldId)->first();
    }

    /**
     * Obtém um valor específico pelo nome do campo
     */
    public function getValueByFieldName($fieldName)
    {
        $field = CustomFormField::where('form_id', $this->form_id)
            ->where('name', $fieldName)
            ->first();
            
        if (!$field) {
            return null;
        }
        
        return $this->getValueByFieldId($field->id);
    }
}

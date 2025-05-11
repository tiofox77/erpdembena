<?php

namespace App\Models\SupplyChain;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFormField extends Model
{
    use HasFactory;

    protected $table = 'sc_custom_form_fields';

    protected $fillable = [
        'form_id',
        'label',
        'name',
        'type',
        'options',
        'validation_rules',
        'description',
        'is_required',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'validation_rules' => 'array',
    ];

    /**
     * Obtém o formulário ao qual este campo pertence
     */
    public function form()
    {
        return $this->belongsTo(CustomForm::class, 'form_id');
    }

    /**
     * Obtém os valores deste campo em submissões
     */
    public function values()
    {
        return $this->hasMany(CustomFormFieldValue::class, 'field_id');
    }

    /**
     * Determina se o campo é do tipo upload de arquivo
     */
    public function isFileUpload()
    {
        return $this->type === 'file';
    }

    /**
     * Gera as regras de validação para este campo
     */
    public function getValidationRules()
    {
        $rules = [];
        
        // Regra básica baseada no tipo do campo
        if ($this->type === 'file') {
            $rules[] = 'file';
            $rules[] = 'max:10240'; // Máximo 10MB por padrão
        } elseif ($this->type === 'date') {
            $rules[] = 'date';
        } elseif ($this->type === 'number') {
            $rules[] = 'numeric';
        } elseif ($this->type === 'email') {
            $rules[] = 'email';
        }
        
        // Adicionar regra de obrigatoriedade se o campo for obrigatório
        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        
        // Adicionar regras personalizadas definidas ao criar o campo
        if (!empty($this->validation_rules)) {
            $rules = array_merge($rules, $this->validation_rules);
        }
        
        return $rules;
    }
}

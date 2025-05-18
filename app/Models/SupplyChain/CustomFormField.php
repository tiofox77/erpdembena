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
        'relationship_config',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
        'validation_rules' => 'array',
        'relationship_config' => 'array',
    ];
    
    protected $attributes = [
        'relationship_config' => '{"model":null,"display_field":"name","relationship_type":"belongsTo"}',
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
     * Get the relationship configuration as an array
     *
     * @return array
     */
    public function getRelationshipConfigAttribute($value)
    {
        if (is_null($value)) {
            return [
                'model' => null,
                'display_field' => 'name',
                'relationship_type' => 'belongsTo',
            ];
        }
        
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        
        return $value;
    }
    
    /**
     * Set the relationship configuration
     *
     * @param  mixed  $value
     * @return void
     */
    public function setRelationshipConfigAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['relationship_config'] = json_encode($value);
        } elseif (is_string($value)) {
            $this->attributes['relationship_config'] = $value;
        } else {
            $this->attributes['relationship_config'] = json_encode([]);
        }
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

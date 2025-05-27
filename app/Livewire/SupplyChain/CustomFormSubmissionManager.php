<?php

namespace App\Livewire\SupplyChain;

use App\Models\SupplyChain\CustomForm;
use App\Models\SupplyChain\CustomFormAttachment;
use App\Models\SupplyChain\CustomFormField;
use App\Models\SupplyChain\CustomFormFieldValue;
use App\Models\SupplyChain\CustomFormSubmission;
use App\Models\SupplyChain\ShippingNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomFormSubmissionManager extends Component
{
    use WithFileUploads;
    
    public $shippingNoteId;
    public $customFormId;
    public $formData = [];
    public $fileUploads = [];
    
    public $showFormModal = false;
    public $showSubmissionDetailModal = false;
    public $submissionId = null;
    public $formField = [];
    
    protected $listeners = [
        'openFormSubmission', 
        'viewFormSubmission',
        'updatedCheckbox' => 'handleUpdatedCheckbox',
    ];
    
    /**
     * Atualiza o formData quando um checkbox é alterado via JavaScript
     * 
     * @param string $fieldName
     * @param string $jsonValue
     * @return void
     */
    public function handleUpdatedCheckbox($fieldName, $jsonValue)
    {
        try {
            logger()->debug('Atualizando checkbox via Livewire', [
                'field' => $fieldName,
                'raw_value' => $jsonValue
            ]);
            
            // Verificar se o campo existe no formulário
            if (!isset($this->formData[$fieldName])) {
                logger()->warning('Campo não encontrado no formData', ['field' => $fieldName]);
                return;
            }
            
            // Decodificar o JSON para garantir que é válido
            $decodedValue = null;
            if (!empty($jsonValue) && $jsonValue !== 'null') {
                $decodedValue = json_decode($jsonValue, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    logger()->error('Erro ao decodificar valor do checkbox', [
                        'field' => $fieldName,
                        'value' => $jsonValue,
                        'error' => json_last_error_msg()
                    ]);
                    return;
                }
                
                // Se for um array, garantir que os valores sejam booleanos
                if (is_array($decodedValue)) {
                    $normalizedValues = [];
                    foreach ($decodedValue as $key => $value) {
                        $normalizedValues[$key] = $this->normalizeCheckboxValue($value);
                    }
                    $decodedValue = $normalizedValues;
                } else {
                    // Para valores únicos, normalizar para booleano
                    $decodedValue = $this->normalizeCheckboxValue($decodedValue);
                }
            } else {
                // Se o valor for nulo ou vazio, definir como falso para checkbox simples ou array vazio para múltiplos
                $decodedValue = is_array($this->formData[$fieldName]) ? [] : false;
            }
            
            // Atualizar o formData com o novo valor
            $this->formData[$fieldName] = $decodedValue;
            
            logger()->debug('Checkbox atualizado com sucesso', [
                'field' => $fieldName,
                'normalized_value' => $decodedValue,
                'raw_value' => $jsonValue
            ]);
            
            // Disparar evento para atualizar a UI se necessário
            $this->dispatch('checkbox-updated', 
                field: $fieldName, 
                value: $decodedValue
            );
            
        } catch (\Exception $e) {
            logger()->error('Erro ao processar atualização de checkbox', [
                'field' => $fieldName,
                'value' => $jsonValue,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Disparar notificação de erro para o usuário
            $this->dispatch('notify', 
                type: 'error',
                title: 'Erro',
                message: 'Ocorreu um erro ao atualizar o campo. Por favor, tente novamente.'
            );
        }
    }
    
    /**
     * Prepara e inicializa o formulário para submissão
     * 
     * @param int $shippingNoteId
     * @param int $customFormId
     * @return void
     */
    public function openFormSubmission($shippingNoteId, $customFormId)
    {
        // Log de criação do modal de shipping note
        logger()->info('Criando modal de shipping note', [
            'shippingNoteId' => $shippingNoteId,
            'customFormId' => $customFormId,
            'user_id' => auth()->id() ?? 'não autenticado'
        ]);
        
        $this->reset('formData', 'fileUploads');
        
        $this->shippingNoteId = $shippingNoteId;
        $this->customFormId = $customFormId;
        
        // Verificar se o formulário existe
        $form = CustomForm::findOrFail($customFormId);
        
        // Verificar se existe uma submissão anterior para preencher o formulário
        $existingSubmission = CustomFormSubmission::where('form_id', $customFormId)
            ->where('shipping_note_id', $shippingNoteId)
            ->latest()
            ->first();
        
        // Inicializar os campos do formulário
        foreach ($form->fields as $field) {
            // Verificar se o campo tem configuração de relacionamento, independente do tipo declarado
            $hasRelationshipConfig = !empty($field->relationship_config['model']);
            $isRelationshipField = $field->type === 'relationship' || $hasRelationshipConfig;
            
            // Valor padrão para o campo
            $defaultValue = '';
            
            // Se temos uma submissão existente, tentar recuperar o valor
            if ($existingSubmission) {
                $existingValue = $this->getFieldValueFromSubmission($existingSubmission, $field);
                if ($existingValue !== null) {
                    $defaultValue = $existingValue;
                }
            }
            
            if ($isRelationshipField) {
                // Se tem configuração de relacionamento mas não é do tipo relationship, logamos a conversão
                if ($hasRelationshipConfig && $field->type !== 'relationship') {
                    logger()->info('Campo ' . $field->name . ' tem configuração de relacionamento mas não é do tipo relationship. Tratando como relacionamento.');
                }
                
                // Determinar se é um relacionamento de múltiplos valores ou valor único
                $isHasMany = ($field->relationship_config['relationship_type'] ?? 'belongsTo') === 'hasMany';
                $this->formData[$field->name] = $isHasMany ? [] : $defaultValue;
            } else if ($field->type === 'file') {
                $this->formData[$field->name] = $defaultValue;
                $this->fileUploads[$field->name] = null;
            } else if ($field->type === 'checkbox') {
                // Para checkboxes, garantir que o valor seja tratado corretamente
                if (!empty($field->options)) {
                    // Checkbox múltiplo
                    if (empty($defaultValue)) {
                        $this->formData[$field->name] = new \stdClass();
                    } elseif (is_string($defaultValue) && $this->isJson($defaultValue)) {
                        // Se for uma string JSON, decodificar para objeto
                        $this->formData[$field->name] = json_decode($defaultValue);
                    } else {
                        $this->formData[$field->name] = $defaultValue;
                    }
                } else {
                    // Checkbox simples
                    $this->formData[$field->name] = $this->normalizeCheckboxValue($defaultValue);
                }
            } else {
                $this->formData[$field->name] = $defaultValue;
            }
        }
        
        $this->showFormModal = true;
    
        // Log quando o modal é exibido
        logger()->info('Modal de shipping note foi criado com sucesso', [
            'shippingNoteId' => $this->shippingNoteId,
            'customFormId' => $this->customFormId
        ]);
    }
    
    /**
     * Recupera o valor de um campo de uma submissão anterior
     *
     * @param CustomFormSubmission $submission
     * @param CustomFormField $field
     * @return mixed
     */
    protected function getFieldValueFromSubmission($submission, $field)
    {
        $fieldValue = $submission->values->where('field_id', $field->id)->first();
        
        if (!$fieldValue) {
            return null;
        }
        
        logger()->debug('Obtendo valor de campo de submissão existente', [
            'field_id' => $field->id,
            'field_name' => $field->name,
            'field_type' => $field->type,
            'raw_value' => $fieldValue->value,
            'has_options' => !empty($field->options)
        ]);
        
        // Se for um campo de arquivo, retornar o caminho do arquivo
        if ($field->type === 'file') {
            return $fieldValue->file_path ? Storage::url($fieldValue->file_path) : null;
        }
        
        // Se for um checkbox, retornar o valor processado
        if ($field->type === 'checkbox') {
            if (!empty($field->options)) {
                // Checkbox múltiplo
                if (empty($fieldValue->value) || $fieldValue->value === '{}') {
                    return [];
                }
                
                if (is_string($fieldValue->value) && $this->isJson($fieldValue->value)) {
                    $decoded = json_decode($fieldValue->value, true);
                    // Garantir que temos um array associativo com valores booleanos
                    if (is_array($decoded)) {
                        $result = [];
                        foreach ($decoded as $key => $value) {
                            $result[$key] = $this->normalizeCheckboxValue($value);
                        }
                        return $result;
                    }
                    return $decoded;
                }
                
                return [];
            } else {
                // Checkbox simples
                return $fieldValue->value === '1' || $fieldValue->value === 'true' || $fieldValue->value === true;
            }
        } elseif ($field->type === 'relationship') {
            return $fieldValue->related_id;
        } else {
            return $fieldValue->value;
        }
    }
    
    /**
     * Normaliza um valor de checkbox para booleano
     * 
     * @param mixed $value
     * @return bool
     */
    protected function normalizeCheckboxValue($value)
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            return (int)$value === 1;
        }
        
        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['true', '1', 'on', 'yes', 'y']);
        }
        
        return false;
    }
    
    /**
     * Verifica se uma string é um JSON válido
     *
     * @param string $string
     * @return boolean
     */
    protected function isJson($string)
    {
        if (!is_string($string) || empty($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    public function viewFormSubmission($submissionId)
    {
        $this->submissionId = $submissionId;
        $this->showSubmissionDetailModal = true;
    }
    
    /**
     * Fecha o modal e reseta os valores
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showSubmissionDetailModal = false;
        $this->reset('formData', 'fileUploads');
    }
    
    /**
     * Processa a submissão do formulário
     * 
     * @return void
     */
    public function submitForm()
    {
        logger()->info('Iniciando submissão do formulário', [
            'form_id' => $this->customFormId,
            'shipping_note_id' => $this->shippingNoteId,
            'formData' => $this->formData
        ]);
        
        try {
            // Validação básica
            $this->validate([
                'shippingNoteId' => 'required',
                'customFormId' => 'required',
            ]);
            
            // Carregar o formulário e seus campos
            $form = CustomForm::findOrFail($this->customFormId);
            $fields = $form->fields;
            
            // Preparar regras de validação dinâmicas
            $validationRules = [];
            foreach ($fields as $field) {
                if ($field->is_required && $field->type !== 'file') {
                    $validationRules['formData.' . $field->name] = 'required';
                }
                
                // Regras específicas por tipo
                if ($field->type === 'email') {
                    $validationRules['formData.' . $field->name] .= '|email';
                } elseif ($field->type === 'number') {
                    $validationRules['formData.' . $field->name] .= '|numeric';
                }
            }
            
            // Aplicar validação
            $this->validate($validationRules);
            
            // Criar submissão no banco de dados
            DB::beginTransaction();
            
            $submission = CustomFormSubmission::create([
                'form_id' => $this->customFormId,
                'shipping_note_id' => $this->shippingNoteId,
                'user_id' => auth()->id(),
                'status' => 'completed',
            ]);
            
            // Salvar valores dos campos
            foreach ($fields as $field) {
                $fieldName = $field->name;
                $fieldValue = $this->formData[$fieldName] ?? null;
                
                // Processar campos checkbox de forma especial
                if ($field->type === 'checkbox') {
                    logger()->debug('Processando campo checkbox', [
                        'field' => $fieldName,
                        'raw_value' => $fieldValue,
                        'field_type' => gettype($fieldValue),
                        'has_options' => !empty($field->options)
                    ]);

                    // Verificar se é um checkbox múltiplo (tem opções definidas)
                    $hasOptions = !empty($field->options);
                    
                    // Se for um checkbox com múltiplas opções
                    if ($hasOptions) {
                        $cleanedValues = [];
                        
                        // Se o valor for uma string JSON, decodificar primeiro
                        if (is_string($fieldValue) && $this->isJson($fieldValue)) {
                            $fieldValue = json_decode($fieldValue, true);
                            logger()->debug('Valor JSON decodificado', [
                                'field' => $fieldName,
                                'decoded' => $fieldValue
                            ]);
                        } elseif ($fieldValue === null || $fieldValue === '') {
                            // Se o valor for nulo ou vazio, inicializar como array vazio
                            $fieldValue = [];
                        }
                        
                        // Se for array ou objeto, processar cada item
                        if (is_array($fieldValue) || is_object($fieldValue)) {
                            // Inicializar array para valores limpos
                            $cleanedValues = [];
                            
                            // Processar cada opção disponível
                            foreach ($field->options as $option) {
                                $optionKey = $option['value'];
                                $isChecked = false;
                                
                                // Verificar se a opção está marcada nos valores fornecidos
                                if (is_array($fieldValue) && array_key_exists($optionKey, $fieldValue)) {
                                    $isChecked = $this->normalizeCheckboxValue($fieldValue[$optionKey]);
                                } elseif (is_object($fieldValue) && property_exists($fieldValue, $optionKey)) {
                                    $isChecked = $this->normalizeCheckboxValue($fieldValue->$optionKey);
                                }
                                
                                // Adicionar apenas se estiver marcado
                                if ($isChecked) {
                                    $cleanedValues[$optionKey] = true;
                                }
                            }
                            
                            // Se não houver valores selecionados, retornar objeto vazio
                            $fieldValue = empty($cleanedValues) ? '{}' : json_encode($cleanedValues);
                            
                            logger()->debug('Checkbox múltiplo processado', [
                                'field' => $fieldName,
                                'cleaned_values' => $cleanedValues,
                                'final_value' => $fieldValue
                            ]);
                        } else {
                            // Se não for array/objeto, inicializar com objeto vazio
                            $fieldValue = '{}';
                            logger()->debug('Valor inválido para checkbox múltiplo, usando objeto vazio', [
                                'field' => $fieldName,
                                'value' => $fieldValue
                            ]);
                        }
                    } 
                    // Checkbox simples
                    else {
                        $isChecked = $this->normalizeCheckboxValue($fieldValue);
                        $fieldValue = $isChecked ? 'true' : 'false';
                        
                        logger()->debug('Checkbox simples processado', [
                            'field' => $fieldName,
                            'raw_value' => $fieldValue,
                            'is_checked' => $isChecked,
                            'final_value' => $fieldValue
                        ]);
                    }
                }
                
                // Processar campo de arquivo
                if ($field->type === 'file' && !empty($this->fileUploads[$fieldName])) {
                    $file = $this->fileUploads[$fieldName];
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('public/custom_forms', $fileName);
                    
                    // Armazenar informações do arquivo
                    CustomFormAttachment::create([
                        'submission_id' => $submission->id,
                        'field_id' => $field->id,
                        'file_path' => $fileName,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ]);
                    
                    // Não é necessário salvar o caminho do arquivo em CustomFormFieldValue
                    continue;
                }
                
                // Processar campos relacionais
                if ($field->type === 'relationship' && !empty($field->relationship_config['model'])) {
                    $relationType = $field->relationship_config['relationship_type'] ?? 'belongsTo';
                    
                    if ($relationType === 'hasMany' && is_array($fieldValue)) {
                        // Para relacionamentos de muitos, criar múltiplos registros
                        foreach ($fieldValue as $relatedId) {
                            if (!empty($relatedId)) {
                                CustomFormFieldValue::create([
                                    'submission_id' => $submission->id,
                                    'field_id' => $field->id,
                                    'value' => '',
                                    'related_id' => $relatedId,
                                ]);
                            }
                        }
                    } else {
                        // Para relacionamentos de um-para-um
                        if (!empty($fieldValue)) {
                            CustomFormFieldValue::create([
                                'submission_id' => $submission->id,
                                'field_id' => $field->id,
                                'value' => '',
                                'related_id' => $fieldValue,
                            ]);
                        }
                    }
                } else {
                    // Para todos os outros tipos de campos
                    if ($fieldValue !== null) {
                        // Se for um checkbox múltiplo, já está em formato JSON
                        if ($field->type === 'checkbox' && !empty($field->options)) {
                            $storedValue = $fieldValue; // Já está em formato JSON
                        } 
                        // Se for array, converter para JSON
                        elseif (is_array($fieldValue)) {
                            $storedValue = json_encode($fieldValue);
                        } 
                        // Para outros tipos, converter para string
                        else {
                            $storedValue = (string) $fieldValue;
                        }
                        
                        logger()->debug('Salvando valor do campo', [
                            'field' => $field->name,
                            'type' => $field->type,
                            'value' => $storedValue
                        ]);
                        
                        CustomFormFieldValue::updateOrCreate(
                            [
                                'submission_id' => $submission->id,
                                'field_id' => $field->id
                            ],
                            ['value' => $storedValue]
                        );
                    }
                }
            }
            
            DB::commit();
            
            // Atualizar status da shipping note
            $shippingNote = ShippingNote::find($this->shippingNoteId);
            if ($shippingNote) {
                $shippingNote->status = 'form_completed';
                $shippingNote->save();
            }
            
            // Fechar modal e enviar notificação
            $this->closeModal();
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.form_submitted_successfully')
            );
            
            // Atualizar a lista de shipping notes se necessário
            $this->dispatch('refreshShippingNotes');
            
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Erro ao submeter formulário', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.form_submission_error') . ': ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Carrega os dados relacionados para um campo de formulário
     */
    public function loadRelatedData()
    {
        try {
            $this->relatedData = [];
            
            if ($this->formField['relationship'] ?? false) {
                $model = app($this->formField['model']);
                $query = $model->query();
                
                // Apply filters
                if (!empty($this->formField['filter_field']) && !empty($this->formField['filter_value'])) {
                    if ($this->formField['filter_value'] === 'auth_user') {
                        $query->where($this->formField['filter_field'], auth()->id());
                    } else {
                        $query->where($this->formField['filter_field'], $this->formField['filter_value']);
                    }
                }
                
                // Query related data
                if (method_exists($model, 'scopeActive')) {
                    $query->active();
                }
                
                $this->relatedData = $query->get()->mapWithKeys(function ($item) {
                    return [$item->id => $item->{$this->formField['display_field']}];
                })->toArray();
            }
        } catch (\Exception $e) {
            logger('Error loading related data: ' . $e->getMessage());
        }
    }
    
    /**
     * Carrega dados relacionados para todos os campos de relacionamento no formulário
     */
    public function loadRelatedDataForForm($form)
    {
        $relatedData = [];
        
        try {
            // Percorre todos os campos do formulário
            foreach ($form->fields as $field) {
                // Verifica se é um campo de relacionamento
                if ($field->type === 'relationship' && !empty($field->relationship_config['model'])) {
                    logger()->debug('Carregando dados relacionados para o campo: ' . $field->name);
                    
                    $model = app($field->relationship_config['model']);
                    $query = $model->query();
                    
                    // Aplicar filtros se existirem
                    if (!empty($field->relationship_config['filter_field']) && !empty($field->relationship_config['filter_value'])) {
                        if ($field->relationship_config['filter_value'] === 'auth_user') {
                            $query->where($field->relationship_config['filter_field'], auth()->id());
                        } else {
                            $query->where($field->relationship_config['filter_field'], $field->relationship_config['filter_value']);
                        }
                    }
                    
                    // Usar o método scopeActive se existir
                    if (method_exists($model, 'scopeActive')) {
                        $query->active();
                    }
                    
                    // Campo a ser usado para exibição
                    $displayField = $field->relationship_config['display_field'] ?? 'name';
                    
                    // Obter os resultados e transformar em array id => valor
                    $relatedData[$field->name] = $query->get()->mapWithKeys(function ($item) use ($displayField) {
                        return [$item->id => $item->{$displayField}];
                    })->toArray();
                    
                    logger()->debug('Dados carregados para ' . $field->name . ': ' . count($relatedData[$field->name]) . ' itens');
                }
            }
        } catch (\Exception $e) {
            logger()->error('Erro ao carregar dados relacionados: ' . $e->getMessage());
        }
        
        return $relatedData;
    }
    
    public function render()
    {
        logger()->debug('======== RENDERIZANDO FORMULÁRIO ========');
        
        // Log quando o componente é renderizado com status do modal
        logger()->info('Renderizando componente CustomFormSubmissionManager', [
            'showFormModal' => $this->showFormModal ? 'Aberto' : 'Fechado',
            'showSubmissionDetailModal' => $this->showSubmissionDetailModal ? 'Aberto' : 'Fechado',
            'shippingNoteId' => $this->shippingNoteId,
            'customFormId' => $this->customFormId,
            'submissionId' => $this->submissionId
        ]);

        $form = $this->customFormId ? CustomForm::find($this->customFormId) : null;
        $fields = $form ? $form->fields()->orderBy('order', 'asc')->get() : collect();
        $submission = $this->submissionId ? CustomFormSubmission::find($this->submissionId) : null;
        
        // Verificando se há campos com problemas no formulário
        foreach ($fields as $field) {
            // Debug para campos específicos se necessário
            if ($field->type === 'relationship') {
                logger()->debug('Campo de relacionamento encontrado na renderização: ' . json_encode([
                    'id' => $field->id,
                    'name' => $field->name,
                    'label' => $field->label,
                    'type' => $field->type,
                    'relationship_config' => $field->relationship_config,
                    'formData' => $this->formData[$field->name] ?? 'não definido'
                ]));
            }
        }
        
        // Carregar dados relacionados para campos do tipo relationship
        $relatedData = [];
        if ($form) {
            $relatedData = $this->loadRelatedDataForForm($form);
            
            // Log para debug
            foreach ($fields as $field) {
                logger()->debug('Verificando campo no render: ' . $field->name . ', Tipo: ' . $field->type);
                if ($field->type === 'relationship') {
                    logger()->debug('Campo de relacionamento encontrado: ' . $field->name);
                    logger()->debug('Configuração: ' . json_encode($field->relationship_config));
                    logger()->debug('Dados relacionados: ' . json_encode($relatedData[$field->name] ?? []));
                }
            }
        }
        
        return view('livewire.supply-chain.custom-form-submission-manager', [
            'form' => $form,
            'fields' => $fields,
            'submission' => $submission,
            'relatedData' => $relatedData,
        ]);
    }
}

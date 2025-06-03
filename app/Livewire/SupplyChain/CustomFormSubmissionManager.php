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
     * Manipula a atualização de um checkbox via JavaScript
     *
     * @param string $fieldName Nome do campo
     * @param mixed $value Valor do checkbox
     */
    public function handleUpdatedCheckbox($fieldName, $value)
    {
        try {
            logger()->debug('handleUpdatedCheckbox chamado:', [
                'fieldName' => $fieldName,
                'value' => $value,
                'type' => gettype($value)
            ]);
            
            // Se for string JSON, decodificar
            if (is_string($value) && $this->isJson($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Filtrar apenas valores true para garantir consistência
                    $filteredValues = [];
                    if (is_array($decoded)) {
                        foreach ($decoded as $key => $val) {
                            if ($this->normalizeCheckboxValue($val)) {
                                $filteredValues[$key] = true; // Sempre true para selecionados
                            }
                        }
                    }
                    
                    // Atualizar formData com string JSON ou objeto vazio
                    $this->formData[$fieldName] = !empty($filteredValues) ? json_encode($filteredValues) : '{}';
                } else {
                    logger()->error('Erro no JSON decode:', ['value' => $value, 'error' => json_last_error_msg()]);
                    $this->formData[$fieldName] = '{}';
                }
            } 
            // Se for array ou objeto, processar diretamente
            elseif (is_array($value) || is_object($value)) {
                $array = is_object($value) ? (array)$value : $value;
                $filteredValues = [];
                foreach ($array as $key => $val) {
                    if ($this->normalizeCheckboxValue($val)) {
                        $filteredValues[$key] = true; // Sempre true para selecionados
                    }
                }
                $this->formData[$fieldName] = !empty($filteredValues) ? json_encode($filteredValues) : '{}';
            } 
            // Para valores simples (checkbox único)
            else {
                $this->formData[$fieldName] = $this->normalizeCheckboxValue($value);
            }
            
            // Emitir evento para atualizar a UI
            $this->dispatch('checkbox-updated', [
                'field' => $fieldName,
                'value' => $this->formData[$fieldName]
            ]);
            
            logger()->debug('Checkbox atualizado com sucesso:', [
                'fieldName' => $fieldName,
                'finalValue' => $this->formData[$fieldName]
            ]);
            
        } catch (\Exception $e) {
            logger()->error('Erro ao processar checkbox:', [
                'fieldName' => $fieldName,
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            
            // Garantir que o campo tenha um valor válido em caso de erro
            $this->formData[$fieldName] = '{}';
            
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Erro',
                'message' => 'Erro ao atualizar campo checkbox. Por favor, tente novamente.'
            ]);
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
        $this->reset('formData', 'fileUploads');
        $this->shippingNoteId = $shippingNoteId;
        $this->customFormId = $customFormId;
        
        $form = CustomForm::findOrFail($customFormId);
        $existingSubmission = CustomFormSubmission::where('form_id', $customFormId)
            ->where('shipping_note_id', $shippingNoteId)
            ->latest()
            ->first();
        
        foreach ($form->fields as $field) {
            $defaultValue = '';
            if ($existingSubmission) {
                $existingValue = $this->getFieldValueFromSubmission($existingSubmission, $field);
                if ($existingValue !== null) {
                    $defaultValue = $existingValue;
                }
            }
            
            if ($field->type === 'checkbox') {
                if (!empty($field->options)) {
                    // Checkbox múltiplo - garantir que seja um objeto/array com apenas valores true
                    if (empty($defaultValue) || $defaultValue === '{}' || $defaultValue === '[]') {
                        $this->formData[$field->name] = '{}'; // String JSON vazia
                    } elseif (is_string($defaultValue) && $this->isJson($defaultValue)) {
                        // Se for JSON válido, decodificar e filtrar apenas valores true
                        $decoded = json_decode($defaultValue, true);
                        if (is_array($decoded)) {
                            $filteredValues = [];
                            foreach ($decoded as $key => $value) {
                                if ($this->normalizeCheckboxValue($value)) {
                                    $filteredValues[$key] = true; // Sempre true para selecionados
                                }
                            }
                            $this->formData[$field->name] = !empty($filteredValues) ? json_encode($filteredValues) : '{}';
                        } else {
                            $this->formData[$field->name] = '{}';
                        }
                    } elseif (is_array($defaultValue) || is_object($defaultValue)) {
                        // Se for array/objeto, processar e filtrar
                        $array = is_object($defaultValue) ? (array)$defaultValue : $defaultValue;
                        $filteredValues = [];
                        foreach ($array as $key => $value) {
                            if ($this->normalizeCheckboxValue($value)) {
                                $filteredValues[$key] = true; // Sempre true para selecionados
                            }
                        }
                        $this->formData[$field->name] = !empty($filteredValues) ? json_encode($filteredValues) : '{}';
                    } else {
                        $this->formData[$field->name] = '{}';
                    }
                } else {
                    // Checkbox simples
                    $this->formData[$field->name] = $this->normalizeCheckboxValue($defaultValue);
                }
            } else {
                $this->formData[$field->name] = $defaultValue;
            }
        }
        
        logger()->debug('Form data inicializado:', [
            'shipping_note_id' => $shippingNoteId,
            'form_id' => $customFormId,
            'formData' => $this->formData
        ]);
        
        $this->showFormModal = true;
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
                            $decodedValue = json_decode($fieldValue, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedValue)) {
                                // Filtrar apenas valores true
                                foreach ($decodedValue as $key => $value) {
                                    if ($this->normalizeCheckboxValue($value)) {
                                        $cleanedValues[$key] = true;
                                    }
                                }
                            }
                            logger()->debug('Valor JSON decodificado e filtrado', [
                                'field' => $fieldName,
                                'decoded' => $decodedValue,
                                'cleaned' => $cleanedValues
                            ]);
                        } elseif ($fieldValue === null || $fieldValue === '' || $fieldValue === '{}') {
                            // Se o valor for nulo, vazio ou objeto JSON vazio, não há valores selecionados
                            $cleanedValues = [];
                        } elseif (is_array($fieldValue) || is_object($fieldValue)) {
                            // Se for array ou objeto, processar diretamente
                            $array = is_object($fieldValue) ? (array)$fieldValue : $fieldValue;
                            foreach ($array as $key => $value) {
                                if ($this->normalizeCheckboxValue($value)) {
                                    $cleanedValues[$key] = true;
                                }
                            }
                        }
                        
                        // Salvar como JSON (objeto vazio se nenhuma opção selecionada)
                        $fieldValue = empty($cleanedValues) ? '{}' : json_encode($cleanedValues);
                        
                        logger()->debug('Checkbox múltiplo processado', [
                            'field' => $fieldName,
                            'cleaned_values' => $cleanedValues,
                            'final_json' => $fieldValue
                        ]);
                    } else {
                        // Checkbox simples - normalizar para string 'true' ou 'false'
                        $isChecked = $this->normalizeCheckboxValue($fieldValue);
                        $fieldValue = $isChecked ? 'true' : 'false';
                        
                        logger()->debug('Checkbox simples processado', [
                            'field' => $fieldName,
                            'normalized_value' => $isChecked,
                            'stored_value' => $fieldValue
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

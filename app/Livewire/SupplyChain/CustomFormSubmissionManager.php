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
        'updatedFormData',
    ];
    
    public function mount()
    {
        // Inicializar formData como array vazio
        $this->formData = [];
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
        // LOG PARA VERIFICAR ABERTURA DO FORMULÁRIO
        logger()->error('📂 FORMULÁRIO ABERTO - SE VOCÊ VÊ ESTA MENSAGEM, O MODAL ESTÁ SENDO CHAMADO!', [
            'shipping_note_id' => $shippingNoteId,
            'custom_form_id' => $customFormId,
            'timestamp' => now()
        ]);
        
        $this->reset('formData', 'fileUploads');
        $this->shippingNoteId = $shippingNoteId;
        $this->customFormId = $customFormId;
        
        $form = CustomForm::findOrFail($customFormId);
        $existingSubmission = CustomFormSubmission::where('form_id', $customFormId)
            ->where('shipping_note_id', $shippingNoteId)
            ->latest()
            ->first();
        
        // Initialize is_completed from existing submission or default to false
        $this->formData['is_completed'] = $existingSubmission ? (bool)$existingSubmission->is_completed : false;
        
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
                    // Checkbox múltiplo - inicializar como array para wire:model
                    $checkboxArray = [];
                    
                    if (!empty($defaultValue) && $defaultValue !== '{}' && $defaultValue !== '[]') {
                        if (is_string($defaultValue) && $this->isJson($defaultValue)) {
                            // Se for JSON válido, decodificar e filtrar apenas valores true
                            $decoded = json_decode($defaultValue, true);
                            if (is_array($decoded)) {
                                foreach ($field->options as $option) {
                                    $optionKey = $option['value'];
                                    $checkboxArray[$optionKey] = isset($decoded[$optionKey]) && $this->normalizeCheckboxValue($decoded[$optionKey]);
                                }
                            }
                        } elseif (is_array($defaultValue)) {
                            // Se for array, processar diretamente
                            foreach ($field->options as $option) {
                                $optionKey = $option['value'];
                                $checkboxArray[$optionKey] = isset($defaultValue[$optionKey]) && $this->normalizeCheckboxValue($defaultValue[$optionKey]);
                            }
                        }
                    } else {
                        // Inicializar todas as opções como false
                        foreach ($field->options as $option) {
                            $checkboxArray[$option['value']] = false;
                        }
                    }
                    
                    $this->formData[$field->name] = $checkboxArray;
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
        // LOG SIMPLES PARA VERIFICAR SE O MÉTODO ESTÁ SENDO CHAMADO
        logger()->error('🚀 SUBMIT FORM CHAMADO - SE VOCÊ VÊ ESTA MENSAGEM, O MÉTODO ESTÁ FUNCIONANDO!', [
            'timestamp' => now(),
            'user_id' => auth()->id(),
            'session_id' => session()->getId()
        ]);
        
        logger()->debug('=== INICIANDO SUBMIT FORM ===', [
            'customFormId' => $this->customFormId,
            'shippingNoteId' => $this->shippingNoteId,
            'formData_raw' => $this->formData,
            'formData_count' => count($this->formData ?? [])
        ]);
        
        if (!$this->customFormId || !$this->shippingNoteId) {
            logger()->error('ERRO: IDs obrigatórios não definidos', [
                'customFormId' => $this->customFormId,
                'shippingNoteId' => $this->shippingNoteId
            ]);
            session()->flash('error', 'Erro: Formulário ou nota fiscal não identificados.');
            return;
        }

        logger()->info('Iniciando submissão do formulário', [
            'form_id' => $this->customFormId,
            'shipping_note_id' => $this->shippingNoteId,
            'formData' => $this->formData
        ]);
        
        // DEBUG: Log detalhado dos dados recebidos
        logger()->debug('=== DEBUG SUBMIT FORM ===');
        logger()->debug('FormData completo:', $this->formData);
        foreach ($this->formData as $fieldName => $fieldValue) {
            logger()->debug("Campo: {$fieldName}", [
                'value' => $fieldValue,
                'type' => gettype($fieldValue),
                'is_array' => is_array($fieldValue),
                'content' => is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue
            ]);
        }
        logger()->debug('=== FIM DEBUG ===');
        
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
            
            // Iniciar transação
            DB::beginTransaction();
            
            // Extrair is_completed do formData e converter explicitamente para booleano
            $isCompleted = filter_var($this->formData['is_completed'] ?? false, FILTER_VALIDATE_BOOLEAN);
            
            // Log do estado de is_completed antes da criação
            logger()->debug('=== IS COMPLETED VALUE ===', [
                'raw_value' => $this->formData['is_completed'] ?? 'não definido',
                'processed_value' => $isCompleted,
                'is_boolean' => is_bool($isCompleted),
                'value_as_int' => (int)$isCompleted
            ]);
            
            // Criar uma nova submissão
            $submission = CustomFormSubmission::create([
                'form_id' => $this->customFormId,
                'entity_id' => $this->shippingNoteId,
                'shipping_note_id' => $this->shippingNoteId,
                'data' => json_encode($this->formData),
                'is_completed' => $isCompleted,
                'created_by' => auth()->id(),
            ]);
            
            // Verificar se o valor foi salvo corretamente
            logger()->debug('=== SUBMISSION APÓS SALVAR ===', [
                'submission_id' => $submission->id,
                'is_completed_raw' => $submission->getRawOriginal('is_completed'),
                'is_completed_cast' => $submission->is_completed,
            ]);
            
            logger()->debug('=== SUBMISSION CRIADA ===', [
                'submission_id' => $submission->id,
                'form_id' => $this->customFormId,
                'shipping_note_id' => $this->shippingNoteId
            ]);
            
            // Salvar cada campo individualmente
            foreach ($fields as $field) {
                $fieldName = $field->name;
                $fieldValue = $this->formData[$fieldName] ?? null;
                
                logger()->debug('=== PROCESSANDO CAMPO ===', [
                    'field_name' => $fieldName,
                    'field_type' => $field->type,
                    'field_value' => $fieldValue,
                    'field_value_type' => gettype($fieldValue),
                    'is_checkbox' => $field->type === 'checkbox',
                    'has_options' => !empty($field->options),
                    'field_options' => $field->options ?? null
                ]);
                
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
                        // Se for um checkbox, vamos ver se é múltiplo
                        if ($field->type === 'checkbox' && !empty($field->options)) {
                            logger()->debug('=== PROCESSANDO CHECKBOX MÚLTIPLO ===', [
                                'field_name' => $fieldName,
                                'field_value' => $fieldValue,
                                'field_value_type' => gettype($fieldValue),
                                'field_options' => $field->options
                            ]);
                            
                            // Se chegou como array (do wire:model), precisa processar
                            if (is_array($fieldValue)) {
                                $cleanedValues = [];
                                foreach ($fieldValue as $key => $value) {
                                    logger()->debug("Verificando opção checkbox {$key} = " . var_export($value, true) . " (tipo: " . gettype($value) . ")");
                                    if ($this->normalizeCheckboxValue($value)) {
                                        $cleanedValues[$key] = true;
                                        logger()->debug("✓ Opção {$key} INCLUÍDA");
                                    } else {
                                        logger()->debug("✗ Opção {$key} IGNORADA");
                                    }
                                }
                                $storedValue = empty($cleanedValues) ? '{}' : json_encode($cleanedValues);
                                logger()->debug('=== RESULTADO FINAL CHECKBOX ===', [
                                    'field_name' => $fieldName,
                                    'cleanedValues' => $cleanedValues,
                                    'storedValue' => $storedValue,
                                    'isEmpty' => empty($cleanedValues)
                                ]);
                            } else {
                                // Se já está em JSON ou string, manter como está
                                $storedValue = $fieldValue;
                                logger()->debug('Checkbox - valor já processado:', ['storedValue' => $storedValue]);
                            }
                        } 
                        // Se for array, converter para JSON
                        elseif (is_array($fieldValue)) {
                            $storedValue = json_encode($fieldValue);
                        } 
                        // Para outros tipos, converter para string
                        else {
                            $storedValue = (string) $fieldValue;
                        }
                        
                        logger()->debug('=== TENTANDO SALVAR CAMPO ===', [
                            'field_id' => $field->id,
                            'field_name' => $fieldName,
                            'field_type' => $field->type,
                            'original_value' => $this->formData[$fieldName],
                            'processed_value' => $storedValue,
                            'submission_id' => $submission->id
                        ]);
                        
                        try {
                            $fieldValue = CustomFormFieldValue::create([
                                'submission_id' => $submission->id,
                                'field_id' => $field->id,
                                'value' => $storedValue
                            ]);
                        } catch (\Exception $e) {
                            logger()->error('Erro ao salvar campo', [
                                'field_id' => $field->id,
                                'field_name' => $fieldName,
                                'field_type' => $field->type,
                                'original_value' => $this->formData[$fieldName],
                                'processed_value' => $storedValue,
                                'submission_id' => $submission->id,
                                'error' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine(),
                            ]);
                            throw $e;
                        }
                        
                        logger()->debug('=== CAMPO SALVO NO BANCO ===', [
                            'field_id' => $field->id,
                            'field_name' => $fieldName,
                            'field_type' => $field->type,
                            'original_value' => $this->formData[$fieldName],
                            'processed_value' => $storedValue,
                            'saved_record_id' => $fieldValue->id,
                            'saved_value_in_db' => $fieldValue->value
                        ]);
                    }
                }
            }
            
            // Atualizar status da shipping note
            $shippingNote = ShippingNote::find($this->shippingNoteId);
            if ($shippingNote) {
                $shippingNote->status = 'form_completed';
                $shippingNote->form_completed = $isCompleted; // Atualiza o status de conclusão
                $shippingNote->save();
                
                logger()->info('Status da nota de envio atualizado', [
                    'shipping_note_id' => $shippingNote->id,
                    'status' => 'form_completed',
                    'form_completed' => $isCompleted
                ]);
            }
            
            // Confirmar transação
            DB::commit();
            
            // Fechar modal e enviar notificação
            $this->closeModal();
            
            // Mensagem personalizada baseada no status de conclusão
            $message = $isCompleted 
                ? __('messages.form_submitted_and_completed_successfully') 
                : __('messages.form_submitted_successfully');
                
            $this->dispatch('notify', 
                type: 'success', 
                title: __('messages.success'),
                message: $message
            );
            
            // Atualizar a lista de shipping notes se necessário
            $this->dispatch('refreshShippingNotes');
            
        } catch (\Exception $e) {
            logger()->error('Erro ao submeter formulário', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Reverter transação
            DB::rollBack();
            
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
    
    public function debugFormData()
    {
        // LOG SIMPLES PARA VERIFICAR SE O DEBUG ESTÁ SENDO CHAMADO
        logger()->error('🔍 DEBUG FORM DATA CHAMADO - SE VOCÊ VÊ ESTA MENSAGEM, O LIVEWIRE ESTÁ FUNCIONANDO!', [
            'timestamp' => now(),
            'user_id' => auth()->id()
        ]);
        
        logger()->debug('=== DEBUG FORM DATA ATUAL ===', [
            'formData' => $this->formData,
            'customFormId' => $this->customFormId,
            'shippingNoteId' => $this->shippingNoteId
        ]);
        
        if ($this->customFormId) {
            $form = CustomForm::find($this->customFormId);
            if ($form) {
                foreach ($form->fields as $field) {
                    if ($field->type === 'checkbox' && !empty($field->options)) {
                        logger()->debug("Checkbox field {$field->name}:", [
                            'current_value' => $this->formData[$field->name] ?? 'NÃO DEFINIDO',
                            'options' => $field->options,
                            'is_array' => isset($this->formData[$field->name]) ? is_array($this->formData[$field->name]) : false
                        ]);
                    }
                }
            }
        }
        
        // Mostrar notificação para o usuário
        session()->flash('message', 'Debug executado! Verifique os logs do Laravel.');
    }
    
    public function updatedFormData($value, $key)
    {
        logger()->debug('=== FORM DATA UPDATED ===', [
            'key' => $key,
            'value' => $value,
            'type' => gettype($value),
            'all_form_data' => $this->formData
        ]);
        
        // Se for um checkbox, vamos ver se é múltiplo
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $fieldName = $parts[0];
            
            // Buscar o campo no formulário
            if ($this->customFormId) {
                $form = CustomForm::find($this->customFormId);
                if ($form) {
                    $field = $form->fields->where('name', $fieldName)->first();
                    if ($field && $field->type === 'checkbox' && !empty($field->options)) {
                        logger()->debug('=== CHECKBOX MÚLTIPLO ALTERADO ===', [
                            'field_name' => $fieldName,
                            'option_key' => $parts[1] ?? 'unknown',
                            'new_value' => $value,
                            'current_field_data' => $this->formData[$fieldName] ?? 'undefined'
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * Renderiza o componente
     */
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

<?php

namespace App\Livewire\SupplyChain;

use App\Models\SupplyChain\CustomForm;
use App\Models\SupplyChain\CustomFormAttachment;
use App\Models\SupplyChain\CustomFormField;
use App\Models\SupplyChain\CustomFormFieldValue;
use App\Models\SupplyChain\CustomFormSubmission;
use App\Models\SupplyChain\ShippingNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
    
    protected $listeners = ['openFormSubmission', 'viewFormSubmission'];
    
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
        
        // Inicializar os campos do formulário
        foreach ($form->fields as $field) {
            // Verificar se o campo tem configuração de relacionamento, independente do tipo declarado
            $hasRelationshipConfig = !empty($field->relationship_config['model']);
            $isRelationshipField = $field->type === 'relationship' || $hasRelationshipConfig;
            
            if ($isRelationshipField) {
                // Se tem configuração de relacionamento mas não é do tipo relationship, logamos a conversão
                if ($hasRelationshipConfig && $field->type !== 'relationship') {
                    logger()->info('Campo ' . $field->name . ' tem configuração de relacionamento mas não é do tipo relationship. Tratando como relacionamento.');
                }
                
                // Determinar se é um relacionamento de múltiplos valores ou valor único
                $isHasMany = ($field->relationship_config['relationship_type'] ?? 'belongsTo') === 'hasMany';
                $this->formData[$field->name] = $isHasMany ? [] : '';
            } else if ($field->type === 'file') {
                $this->formData[$field->name] = '';
                $this->fileUploads[$field->name] = null;
            } else {
                $this->formData[$field->name] = '';
            }
        }
        
        $this->showFormModal = true;
    
        // Log quando o modal é exibido
        logger()->info('Modal de shipping note foi criado com sucesso', [
            'shippingNoteId' => $this->shippingNoteId,
            'customFormId' => $this->customFormId
        ]);
    }
    
    public function viewFormSubmission($submissionId)
    {
        $this->submissionId = $submissionId;
        $this->showSubmissionDetailModal = true;
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

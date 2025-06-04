<?php

namespace App\Livewire\SupplyChain;

use App\Models\SupplyChain\CustomForm;
use App\Models\SupplyChain\CustomFormField;
use App\Models\SupplyChain\ShippingNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CustomFormBuilder extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'updated_at';
    public $sortDirection = 'desc';
    
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $showFieldModal = false;
    public $showPreviewModal = false;
    public $showImportModal = false;
    
    // Nova solução simplificada para importação/exportação
    public $importFile = null;
    public $exportUrl = null;
    
    public $currentFormId = null;
    public $currentForm = [
        'name' => '',
        'description' => '',
        'entity_type' => 'shipping_note',
        'is_active' => true,
        'status_display_config' => [
            'enabled' => false,
            'field_id' => null
        ],
    ];
    
    public $currentFieldId = null;
    public $currentField = [
        'label' => '',
        'name' => '',
        'type' => 'text',
        'options' => [],
        'relationship_config' => [
            'model' => null,
            'display_field' => null,
            'relationship_type' => 'belongsTo',
        ],
        'validation_rules' => [],
        'description' => '',
        'is_required' => false,
        'order' => 0,
    ];
    
    public $tempOptionLabel = '';
    public $tempOptionValue = '';
    
    // Tipos de campo disponíveis
    public $fieldTypes = [
        'text' => 'Texto simples',
        'textarea' => 'Área de texto',
        'number' => 'Número',
        'email' => 'Email',
        'date' => 'Data',
        'select' => 'Lista de seleção',
        'checkbox' => 'Caixa de seleção',
        'radio' => 'Botões de opção',
        'file' => 'Upload de arquivo',
        'relationship' => 'Relação com outro modelo',
    ];
    
    // Available models for relationship fields
    public $relationshipModels = [
        'App\\Models\\SupplyChain\\Supplier' => 'Fornecedor',
        'App\\Models\\SupplyChain\\SupplierCategory' => 'Categoria de Fornecedor',
        // Add more models as needed
    ];
    
    // Display fields for relationship models
    public $relationshipDisplayFields = [
        'App\\Models\\SupplyChain\\Supplier' => 'name',
        'App\\Models\\SupplyChain\\SupplierCategory' => 'name',
        // Add more display fields as needed
    ];
    
    // Current relationship configuration
    public $relationshipConfig = [
        'model' => null,
        'display_field' => null,
        'relationship_type' => 'belongsTo',
    ];
    
    // Available columns for the selected model
    public $availableColumns = [];
    
    // Sample data for relationship preview
    public $relationshipSampleData = [];
    
    
    /**
     * Update available columns when the relationship model changes
     *
     * @param string|null $modelClass The fully qualified model class name
     * @return void
     */
    public function updateAvailableColumns($modelClass = null)
    {
        if (!$modelClass && !empty($this->relationshipConfig['model'])) {
            $modelClass = $this->relationshipConfig['model'];
        }
        
        if (empty($modelClass) || !class_exists($modelClass)) {
            $this->availableColumns = [];
            return;
        }
        
        try {
            // Create an instance of the model to get the table name
            $model = new $modelClass();
            $table = $model->getTable();
            
            // Get the columns from the database
            $this->availableColumns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            
            // Set default display field if not set
            if (empty($this->relationshipConfig['display_field']) && in_array('name', $this->availableColumns)) {
                $this->relationshipConfig['display_field'] = 'name';
            } elseif (empty($this->relationshipConfig['display_field']) && !empty($this->availableColumns)) {
                $this->relationshipConfig['display_field'] = $this->availableColumns[0];
            }
            
            // Log the available columns for debugging
            \Log::info('Available columns updated:', [
                'model' => $modelClass,
                'table' => $table,
                'columns' => $this->availableColumns,
                'selected_display_field' => $this->relationshipConfig['display_field']
            ]);
            
        } catch (\Exception $e) {
            $this->availableColumns = [];
            \Log::error('Error updating available columns: ' . $e->getMessage());
        }
    }
    
    // Regras de validação para seleção
    public $commonValidationRules = [
        'max:255' => 'Máximo 255 caracteres',
        'min:3' => 'Mínimo 3 caracteres',
        'numeric' => 'Apenas números',
        'email' => 'Formato de email válido',
        'date' => 'Data válida',
        'regex:/^[A-Za-z0-9\s]+$/' => 'Apenas letras, números e espaços',
        'image' => 'Apenas imagens',
        'mimes:pdf,doc,docx,jpg,jpeg,png' => 'PDF, DOC, DOCX, JPG, PNG',
        'max:10240' => 'Máximo 10MB',
    ];
    
    protected $listeners = [
        'refreshComponent' => '$refresh',
        'relationshipModelUpdated' => 'updateAvailableColumns',
    ];
    
    /**
     * Handle updates to relationship configuration
     *
     * @param mixed $value The new value
     * @param string $key The configuration key that was updated
     * @return void
     */
    public function updatedRelationshipConfig($value, $key)
    {
        try {
            // Reset previous errors
            $this->resetErrorBag('relationshipConfig.*');
            
            // Log the update for debugging
            \Log::info('Relationship config updated:', [
                'key' => $key,
                'value' => $value,
                'config' => $this->relationshipConfig
            ]);
            
            // Handle different types of updates
            if ($key === 'model' && !empty($value)) {
                $this->handleModelUpdate($value);
                
                // Log the automatic display field setting
                if (isset($this->relationshipConfig['display_field'])) {
                    \Log::info('Automatically set display field:', [
                        'model' => $value,
                        'display_field' => $this->relationshipConfig['display_field'],
                        'available_columns' => $this->availableColumns
                    ]);
                    
                    // Load sample data for preview with the new display field
                    $this->loadRelationshipSampleData($value);
                }
            } 
            // If display field changes, update the preview
            elseif ($key === 'display_field' && !empty($value) && !empty($this->relationshipConfig['model'])) {
                $this->loadRelationshipSampleData($this->relationshipConfig['model']);
                
                // Update the currentField relationship config to keep in sync
                if (isset($this->currentField['relationship_config'])) {
                    $this->currentField['relationship_config']['display_field'] = $value;
                }
            }
            // If relationship type changes, update the currentField config
            elseif ($key === 'relationship_type' && isset($this->currentField['relationship_config'])) {
                $this->currentField['relationship_config']['relationship_type'] = $value;
            }
            
            // Update the currentField relationship config to keep in sync
            $this->syncCurrentFieldWithRelationshipConfig();
            
        } catch (\Exception $e) {
            $errorMsg = 'Erro ao atualizar configuração de relacionamento: ' . $e->getMessage();
            $this->addError('relationshipConfig', $errorMsg);
            \Log::error($errorMsg, ['exception' => $e]);
        }
    }
    
    /**
     * Handle model update in relationship config
     * 
     * @param string $modelClass
     * @return void
     */
    protected function handleModelUpdate($modelClass)
    {
        // Validate if the model class exists
        if (!class_exists($modelClass)) {
            $this->addError('relationshipConfig.model', 'A classe do modelo não existe: ' . $modelClass);
            return;
        }
        
        // Update available columns first
        $this->updateAvailableColumns($modelClass);
        
        // Get the model instance
        $model = new $modelClass();
        
        // Validate if we can get the table
        if (!method_exists($model, 'getTable')) {
            $this->addError('relationshipConfig.model', 'O modelo não possui um método getTable() válido.');
            return;
        }
        
        $table = $model->getTable();
        
        // Get all columns from the table
        $columns = Schema::getColumnListing($table);
        
        if (empty($columns)) {
            $this->addError('relationshipConfig.model', 'Não foi possível obter as colunas da tabela do modelo.');
            return;
        }
        
        // Find a suitable display field
        $displayField = $this->findSuitableDisplayField($columns);
        
        // Set the display field
        if ($displayField) {
            $this->relationshipConfig['display_field'] = $displayField;
            
            // Load sample data for the new model
            $this->loadRelationshipSampleData($modelClass);
        } else {
            $this->addError('relationshipConfig.model', 'Não foi possível determinar um campo de exibição adequado.');
        }
    }
    
    /**
     * Find a suitable display field from the available columns
     * 
     * @param array $columns
     * @return string|null
     */
    protected function findSuitableDisplayField($columns)
    {
        // Common display field names to check for (in order of preference)
        $commonDisplayFields = [
            'name', 'title', 'label', 'description', 
            'nome', 'titulo', 'descricao', 'code', 'codigo',
            'email', 'username', 'full_name', 'razao_social'
        ];
        
        // First, try to find a common display field
        foreach ($commonDisplayFields as $field) {
            if (in_array($field, $columns)) {
                return $field;
            }
        }
        
        // If no common field found, try to find a text-like column
        $textLikeColumns = array_filter($columns, function($column) {
            return !in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at']) && 
                   !str_ends_with($column, '_id');
        });
        
        if (!empty($textLikeColumns)) {
            return reset($textLikeColumns);
        }
        
        // If all else fails, return the first column that's not an ID
        foreach ($columns as $column) {
            if ($column !== 'id') {
                return $column;
            }
        }
        
        // Last resort, return the first column
        return !empty($columns) ? $columns[0] : null;
    }
    
    /**
     * Sync the current field's relationship config with the relationshipConfig property
     * 
     * @return void
     */
    protected function syncCurrentFieldWithRelationshipConfig() {
        if (isset($this->currentField['relationship_config'])) {
            $this->currentField['relationship_config'] = array_merge(
                $this->currentField['relationship_config'],
                $this->relationshipConfig
            );
        }
    }
    

    
    /**
     * Load sample data for relationship preview
     *
     * @param string $modelClass
     * @return void
     */
    protected function loadRelationshipSampleData($modelClass)
    {
        $this->relationshipSampleData = [];
        $this->resetErrorBag('relationship_sample_data');
        
        try {
            // Validate model class
            if (empty($modelClass)) {
                throw new \Exception('Nenhum modelo foi selecionado.');
            }
            
            if (!class_exists($modelClass)) {
                throw new \Exception(sprintf('A classe do modelo "%s" não foi encontrada.', $modelClass));
            }
            
            // Create model instance
            $model = new $modelClass();
            
            // Validate model instance
            if (!($model instanceof \Illuminate\Database\Eloquent\Model)) {
                throw new \Exception(sprintf('A classe "%s" não é um modelo Eloquent válido.', $modelClass));
            }
            
            // Ensure display field is set
            if (empty($this->relationshipConfig['display_field'])) {
                $this->relationshipConfig['display_field'] = $this->findSuitableDisplayField(
                    Schema::getColumnListing($model->getTable())
                );
            }
            
            $displayField = $this->relationshipConfig['display_field'];
            
            // Verify if display field exists in the table
            $table = $model->getTable();
            $columns = Schema::getColumnListing($table);
            
            if (empty($columns)) {
                throw new \Exception(sprintf('Não foi possível obter as colunas da tabela "%s".', $table));
            }
            
            // If display field doesn't exist, try to find a suitable one
            if (!in_array($displayField, $columns)) {
                $newDisplayField = $this->findSuitableDisplayField($columns);
                $this->relationshipConfig['display_field'] = $newDisplayField;
                $displayField = $newDisplayField;
                
                \Log::warning(sprintf(
                    'Campo de exibição "%s" não encontrado na tabela "%s". Usando "%s" como alternativa.',
                    $this->relationshipConfig['display_field'],
                    $table,
                    $displayField
                ));
            }
            
            // Build query to get sample data
            $query = $model->newQuery();
            
            // Select only necessary columns
            $selectColumns = array_unique(['id', $displayField]);
            
            // Ensure columns exist before selecting
            $validColumns = array_intersect($selectColumns, $columns);
            
            if (empty($validColumns)) {
                throw new \Exception('Nenhuma coluna válida encontrada para seleção.');
            }
            
            // Execute query with error handling
            try {
                $results = $query
                    ->select($validColumns)
                    ->whereNotNull($displayField)
                    ->orderBy($displayField)
                    ->limit(5)
                    ->get()
                    ->toArray();
                
                $this->relationshipSampleData = $results;
                
                // Log successful data loading
                \Log::info('Dados de amostra carregados com sucesso', [
                    'model' => $modelClass,
                    'table' => $table,
                    'display_field' => $displayField,
                    'sample_count' => count($results),
                    'sample_data' => $results
                ]);
                
            } catch (\Exception $queryException) {
                throw new \Exception(sprintf(
                    'Erro ao executar consulta no modelo "%s": %s',
                    $modelClass,
                    $queryException->getMessage()
                ), 0, $queryException);
            }
            
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            $logContext = [
                'model' => $modelClass ?? null,
                'display_field' => $this->relationshipConfig['display_field'] ?? null,
                'exception' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ];
            
            // Log the error with more context
            if ($e->getPrevious()) {
                $logContext['previous_exception'] = [
                    'message' => $e->getPrevious()->getMessage(),
                    'file' => $e->getPrevious()->getFile(),
                    'line' => $e->getPrevious()->getLine()
                ];
            }
            
            \Log::error('Falha ao carregar dados de amostra', $logContext);
            
            // Set user-friendly error message
            $this->addError('relationship_sample_data', 'Não foi possível carregar os dados de amostra. ' . $errorMsg);
            
            // Reset sample data
            $this->relationshipSampleData = [];
        }
    }
    
    /**
     * Reset the current field form and relationship configuration
     *
     * @return void
     */
    public function resetField()
    {
        $this->currentFieldId = null;
        $this->currentField = [
            'label' => '',
            'name' => '',
            'type' => 'text',
            'options' => [],
            'relationship_config' => [
                'model' => null,
                'display_field' => null,
                'relationship_type' => 'belongsTo',
            ],
            'validation_rules' => [],
            'description' => '',
            'is_required' => false,
            'order' => 0,
        ];
        
        // Reset relationship configuration
        $this->relationshipConfig = [
            'model' => null,
            'display_field' => null,
            'relationship_type' => 'belongsTo',
        ];
        
        // Reset available columns
        $this->availableColumns = [];
        
        // Reset any validation errors
        $this->resetErrorBag();
        $this->resetValidation();
        
        // Log the reset
        \Log::info('Field form and relationship configuration reset');
    }
    
    // updateAvailableColumns method is defined above
    
    protected function rules()
    {
        return [
            'currentForm.name' => 'required|string|max:255',
            'currentForm.description' => 'nullable|string|max:1000',
            'currentForm.entity_type' => 'required|string',
            'currentForm.is_active' => 'boolean',
            
            'currentField.label' => 'required|string|max:255',
            'currentField.name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'currentField.type' => 'required|string|in:' . implode(',', array_keys($this->fieldTypes)),
            'currentField.description' => 'nullable|string|max:1000',
            'currentField.is_required' => 'boolean',
            'currentField.order' => 'integer|min:0',
        ];
    }
    
    protected $validationAttributes = [
        'currentForm.name' => 'nome do formulário',
        'currentForm.description' => 'descrição',
        'currentField.label' => 'rótulo do campo',
        'currentField.name' => 'nome do campo',
        'currentField.type' => 'tipo do campo',
        'currentField.description' => 'descrição do campo',
    ];
    
    protected $messages = [
        'currentField.name.regex' => 'O nome do campo deve conter apenas letras, números e sublinhados (sem espaços ou caracteres especiais).',
    ];

    /**
     * Mount the component
     */
    public function mount()
    {
        // Initialization
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function create()
    {
        $this->resetForm();
        $this->showFormModal = true;
    }
    
    public function edit($id)
    {
        $this->resetForm();
        $form = CustomForm::findOrFail($id);
        
        $this->currentFormId = $form->id;
        $this->currentForm = [
            'name' => $form->name,
            'description' => $form->description,
            'entity_type' => $form->entity_type,
            'is_active' => $form->is_active,
            'status_display_config' => $form->status_display_config ?? [
                'enabled' => false,
                'field_id' => null
            ],
        ];
        
        $this->showFormModal = true;
    }
    
    public function save()
    {
        $this->validate([
            'currentForm.name' => 'required|string|max:255',
            'currentForm.description' => 'nullable|string|max:1000',
            'currentForm.entity_type' => 'required|string',
            'currentForm.is_active' => 'boolean',
            'currentForm.status_display_config.enabled' => 'boolean',
            'currentForm.status_display_config.field_id' => 'nullable|integer',
        ]);
        
        if ($this->currentFormId) {
            // Atualizar formulário existente
            $form = CustomForm::findOrFail($this->currentFormId);
            $form->update([
                'name' => $this->currentForm['name'],
                'description' => $this->currentForm['description'],
                'entity_type' => $this->currentForm['entity_type'],
                'is_active' => $this->currentForm['is_active'],
                'status_display_config' => $this->currentForm['status_display_config'],
            ]);
            
            $this->dispatch('notify', 
                type: 'warning', 
                message: __('messages.form_updated')
            );
        } else {
            // Criar novo formulário
            CustomForm::create([
                'name' => $this->currentForm['name'],
                'description' => $this->currentForm['description'],
                'entity_type' => $this->currentForm['entity_type'],
                'is_active' => $this->currentForm['is_active'],
                'status_display_config' => $this->currentForm['status_display_config'],
                'created_by' => Auth::id(),
            ]);
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.form_created')
            );
        }
        
        $this->resetForm();
        $this->showFormModal = false;
    }
    
    public function confirmDelete($id)
    {
        $this->currentFormId = $id;
        $this->showDeleteModal = true;
    }
    
    public function delete()
    {
        $form = CustomForm::findOrFail($this->currentFormId);
        
        // Verificar se existem notas de envio usando este formulário
        $hasShippingNotes = ShippingNote::where('custom_form_id', $this->currentFormId)->exists();
        
        if ($hasShippingNotes) {
            $this->dispatch('notify', 
                type: 'error',
                message: __('messages.cannot_delete_form_in_use')
            );
            $this->showDeleteModal = false;
            return;
        }
        
        // Excluir formulário e seus campos
        $form->delete();
        
        $this->dispatch('notify', 
            type: 'warning',
            message: __('messages.form_deleted')
        );
        
        $this->resetForm();
        $this->showDeleteModal = false;
    }
    
    public function createField($formId)
    {
        $this->resetField();
        $this->currentFormId = $formId;
        
        // Definir a ordem como a próxima na sequência
        $lastField = CustomFormField::where('form_id', $formId)->orderBy('order', 'desc')->first();
        $this->currentField['order'] = $lastField ? $lastField->order + 1 : 0;
        
        $this->showFieldModal = true;
    }
    
    public function editField($fieldId)
    {
        $this->resetField();
        $this->currentFieldId = $fieldId;
        $field = CustomFormField::findOrFail($fieldId);
        $this->currentFormId = $field->form_id;
        
        $this->currentField = [
            'label' => $field->label,
            'name' => $field->name,
            'type' => $field->type,
            'options' => $field->options ?? [],
            'validation_rules' => $field->validation_rules ?? [],
            'description' => $field->description,
            'is_required' => $field->is_required,
            'order' => $field->order,
        ];
        
        // Carregar configuração de relacionamento se for um campo de relacionamento
        if ($field->type === 'relationship') {
            $this->currentField['relationship_config'] = [
                'model' => $field->relationship_config['model'] ?? null,
                'display_field' => $field->relationship_config['display_field'] ?? 'name',
                'relationship_type' => $field->relationship_config['relationship_type'] ?? 'belongsTo',
            ];
            
            // Set the relationship configuration to the component property
            $this->relationshipConfig = [
                'model' => $field->relationship_config['model'] ?? null,
                'display_field' => $field->relationship_config['display_field'] ?? 'name',
                'relationship_type' => $field->relationship_config['relationship_type'] ?? 'belongsTo',
            ];
            
            // Update available columns for the selected model
            if (!empty($this->relationshipConfig['model'])) {
                $this->updateAvailableColumns($this->relationshipConfig['model']);
            }
        }
        
        $this->showFieldModal = true;
    }
    
    /**
     * Save the current field configuration
     *
     * @return void
     */
    public function saveField()
    {
        try {
            // Reset previous errors
            $this->resetErrorBag();
            
            // Update currentField with relationship config before validation
            if ($this->currentField['type'] === 'relationship') {
                $this->syncCurrentFieldWithRelationshipConfig();
                
                // Validate relationship configuration
                $this->validateRelationshipConfig();
                
                // If there are validation errors, stop here
                if ($this->getErrorBag()->isNotEmpty()) {
                    return;
                }
            }
            
            // Define validation rules for the field
            $validationRules = $this->getFieldValidationRules();
            
            // Validate field data
            $this->validate($validationRules);
            
            // Generate field name from label if not provided
            $this->ensureFieldName();
            
            // Save the field
            $this->saveFieldToDatabase();
            
            // Close the modal and show success message
            $this->showFieldModal = false;
            session()->flash('message', 'Campo salvo com sucesso!');
            
            // Reset the form
            $this->resetField();
            
        } catch (\Exception $e) {
            $errorMessage = 'Erro ao salvar o campo: ' . $e->getMessage();
            \Log::error($errorMessage, [
                'exception' => $e,
                'currentField' => $this->currentField
            ]);
            
            $this->addError('save_error', $errorMessage);
        }
    }
    
    /**
     * Validate relationship configuration
     * 
     * @throws \Exception If validation fails
     * @return void
     */
    protected function validateRelationshipConfig()
    {
        $modelClass = $this->relationshipConfig['model'] ?? null;
        $displayField = $this->relationshipConfig['display_field'] ?? null;
        
        // Validate model class exists and is accessible
        if (empty($modelClass)) {
            $this->addError('relationshipConfig.model', 'O modelo é obrigatório para campos de relacionamento.');
            return;
        }
        
        if (!class_exists($modelClass)) {
            $this->addError('relationshipConfig.model', 'A classe do modelo não existe: ' . $modelClass);
            return;
        }
        
        // Validate display field
        if (empty($displayField)) {
            $this->addError('relationshipConfig.display_field', 'O campo de exibição é obrigatório.');
            return;
        }
        
        // Validate model and display field
        try {
            $model = new $modelClass();
            
            if (!($model instanceof \Illuminate\Database\Eloquent\Model)) {
                $this->addError('relationshipConfig.model', 'A classe fornecida não é um modelo Eloquent válido.');
                return;
            }
            
            // Get table columns
            $table = $model->getTable();
            $columns = Schema::getColumnListing($table);
            
            if (empty($columns)) {
                $this->addError('relationshipConfig.model', 'Não foi possível obter as colunas da tabela do modelo.');
                return;
            }
            
            // Check if display field exists in the table
            if (!in_array($displayField, $columns)) {
                $this->addError('relationshipConfig.display_field', 
                    sprintf('O campo de exibição "%s" não existe na tabela "%s".', 
                        $displayField, 
                        $table
                    )
                );
                
                // Suggest alternative fields
                $suggestedField = $this->findSuitableDisplayField($columns);
                if ($suggestedField) {
                    $this->addError('relationshipConfig.display_field', 
                        sprintf('Sugestão: utilize o campo "%s" como alternativa.', $suggestedField)
                    );
                }
            }
            
        } catch (\Exception $e) {
            $errorMessage = 'Erro ao validar a configuração do relacionamento: ' . $e->getMessage();
            $this->addError('relationshipConfig.model', $errorMessage);
            \Log::error($errorMessage, ['exception' => $e]);
        }
    }
    
    /**
     * Get validation rules for the current field
     * 
     * @return array
     */
    protected function getFieldValidationRules()
    {
        $rules = [
            'currentField.label' => 'required|string|max:255',
            'currentField.name' => [
                'required', 
                'string', 
                'max:255', 
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
                'not_in:created_at,updated_at,deleted_at,id',
            ],
            'currentField.type' => 'required|string|in:' . implode(',', array_keys($this->fieldTypes)),
            'currentField.description' => 'nullable|string|max:1000',
            'currentField.is_required' => 'boolean',
            'currentField.order' => 'integer|min:0',
            'currentField.validation_rules' => 'nullable|array',
        ];
        
        // Add relationship-specific validation rules
        if ($this->currentField['type'] === 'relationship') {
            $rules['currentField.relationship_config'] = 'required|array';
            $rules['currentField.relationship_config.model'] = 'required|string';
            $rules['currentField.relationship_config.display_field'] = 'required|string';
            $rules['currentField.relationship_config.relationship_type'] = 'required|in:belongsTo,hasMany';
        }
        
        return $rules;
    }
    
    /**
     * Ensure the field has a valid name, generating one from the label if needed
     * 
     * @return void
     */
    protected function ensureFieldName()
    {
        if (empty($this->currentField['name'])) {
            $this->currentField['name'] = Str::snake($this->currentField['label']);
            
            // Ensure the generated name is valid
            $this->currentField['name'] = preg_replace('/[^a-zA-Z0-9_]/', '_', $this->currentField['name']);
            
            // Ensure it starts with a letter
            if (!empty($this->currentField['name']) && !preg_match('/^[a-zA-Z]/', $this->currentField['name'])) {
                $this->currentField['name'] = 'field_' . $this->currentField['name'];
            }
        }
    }
    
    /**
     * Save the field to the database
     * 
     * @return void
     * @throws \Exception If saving fails
     */
    protected function saveFieldToDatabase()
    {
        // Check if we're updating an existing field or creating a new one
        if ($this->currentFieldId) {
            $field = CustomFormField::findOrFail($this->currentFieldId);
            $field->update($this->prepareFieldData());
        } else {
            $field = CustomFormField::create($this->prepareFieldData());
        }
        
        // Update the current field ID for subsequent operations
        $this->currentFieldId = $field->id;
    }
    
    /**
     * Prepare field data for saving
     * 
     * @return array
     */
    protected function prepareFieldData()
    {
        $data = [
            'form_id' => $this->currentFormId,
            'label' => $this->currentField['label'],
            'name' => $this->currentField['name'],
            'type' => $this->currentField['type'],
            'description' => $this->currentField['description'] ?? null,
            'is_required' => $this->currentField['is_required'] ?? false,
            'order' => $this->currentField['order'] ?? 0,
            'options' => $this->currentField['options'] ?? [],
            'validation_rules' => $this->currentField['validation_rules'] ?? [],
        ];
        
        // Add relationship configuration if this is a relationship field
        if ($this->currentField['type'] === 'relationship') {
            $data['relationship_config'] = [
                'model' => $this->relationshipConfig['model'],
                'display_field' => $this->relationshipConfig['display_field'],
                'relationship_type' => $this->relationshipConfig['relationship_type'],
            ];
        }
        
        return $data;
    }
    
    /**
     * Delete a field from the form
     *
     * @param int $fieldId
     * @return void
     */
    public function deleteField($fieldId)
    {
        $field = CustomFormField::findOrFail($fieldId);
        $field->delete();
        
        $this->dispatch('notify', 
            type: 'warning', 
            message: __('messages.field_deleted')
        );
    }
    
    public function addOption()
    {
        if (empty($this->tempOptionLabel) || empty($this->tempOptionValue)) {
            $this->addError('tempOptionLabel', 'Ambos os campos são obrigatórios');
            return;
        }
        
        // Inicializar o array de opções se necessário
        if (!isset($this->currentField['options']) || !is_array($this->currentField['options'])) {
            $this->currentField['options'] = [];
        }
        
        // Verificar se o valor já existe
        foreach ($this->currentField['options'] as $option) {
            if ($option['value'] == $this->tempOptionValue) {
                $this->addError('tempOptionValue', 'Este valor já existe');
                return;
            }
        }
        
        $this->currentField['options'][] = [
            'label' => $this->tempOptionLabel,
            'value' => $this->tempOptionValue,
        ];
        
        $this->tempOptionLabel = '';
        $this->tempOptionValue = '';
        
        // Mostrar confirmação visual
        $this->dispatch('notify', 
            type: 'success', 
            message: 'Opção "' . $this->currentField['options'][count($this->currentField['options'])-1]['label'] . '" adicionada com sucesso!'
        );
    }
    
    public function removeOption($index)
    {
        unset($this->currentField['options'][$index]);
        $this->currentField['options'] = array_values($this->currentField['options']);
    }
    
    public function toggleValidationRule($rule)
    {
        $index = array_search($rule, $this->currentField['validation_rules']);
        
        if ($index !== false) {
            // Remover regra
            unset($this->currentField['validation_rules'][$index]);
            $this->currentField['validation_rules'] = array_values($this->currentField['validation_rules']);
        } else {
            // Adicionar regra
            $this->currentField['validation_rules'][] = $rule;
        }
    }
    
    public function hasValidationRule($rule)
    {
        return in_array($rule, $this->currentField['validation_rules']);
    }
    
    public function previewForm($id)
    {
        $this->currentFormId = $id;
        $this->showPreviewModal = true;
    }
    
    public function resetForm()
    {
        $this->currentFormId = null;
        $this->currentForm = [
            'name' => '',
            'description' => '',
            'entity_type' => 'shipping_note',
            'is_active' => true,
            'status_display_config' => [
                'enabled' => false,
                'field_id' => null
            ],
        ];
        
        // Reset relationship configuration
        $this->relationshipConfig = [
            'model' => null,
            'display_field' => 'name',
            'relationship_type' => 'belongsTo',
        ];
        
        $this->tempOptionLabel = '';
        $this->tempOptionValue = '';
        $this->resetErrorBag();
    }
    
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->showFieldModal = false;
        $this->showPreviewModal = false;
        $this->showImportModal = false;
        
        // Limpar dados de importação/exportação
        $this->importFile = null;
        $this->exportUrl = null;
        
        $this->resetErrorBag();
    }
    
    /**
     * Exportar um formulário diretamente
     * 
     * @param int $id ID do formulário a ser exportado
     */
    public function exportForm($id)
    {
        try {
            // Buscar o formulário com seus campos
            $form = CustomForm::with('fields')->findOrFail($id);
            
            // Preparar os dados para exportação
            $exportData = [
                'form' => [
                    'name' => $form->name,
                    'description' => $form->description,
                    'entity_type' => $form->entity_type,
                    'is_active' => $form->is_active,
                    'export_date' => now()->format('Y-m-d H:i:s'),
                    'exporter' => auth()->user()->name,
                ],
                'fields' => [],
            ];
            
            // Adicionar campos
            foreach ($form->fields as $field) {
                $exportData['fields'][] = [
                    'label' => $field->label,
                    'name' => $field->name,
                    'type' => $field->type,
                    'options' => $field->options,
                    'validation_rules' => $field->validation_rules,
                    'description' => $field->description,
                    'is_required' => $field->is_required,
                    'order' => $field->order,
                ];
            }
            
            // Gerar nome do arquivo
            $fileName = Str::slug($form->name) . '-' . date('Ymd-His') . '.json';
            
            // Converter para JSON
            $jsonContent = json_encode($exportData, JSON_PRETTY_PRINT);
            
            // Criar arquivo temporário
            $tempFilePath = 'form-exports/' . $fileName;
            Storage::put('public/' . $tempFilePath, $jsonContent);
            
            // Gerar URL para download
            $url = Storage::url('public/' . $tempFilePath);
            
            // Disparar evento para download
            $this->dispatch('download-file', url: $url);
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.form_exported_successfully')
            );
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.form_export_error') . ': ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Abrir modal de importação
     */
    public function openImportModal()
    {
        // Fechar outros modais primeiro
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->showFieldModal = false;
        $this->showPreviewModal = false;
        $this->showExportModal = false;
        
        // Limpar arquivo e erros
        $this->importFile = null;
        $this->resetErrorBag();
        
        // Abrir modal
        $this->showImportModal = true;
        
        // Log para debug
        \Illuminate\Support\Facades\Log::info('Modal de importação aberto', [
            'modal_state' => $this->showImportModal
        ]);
    }
    
    /**
     * Importar um formulário de um arquivo JSON
     */
    public function importForm()
    {
        try {
            $this->validate([
                'importFile' => 'required|file|mimes:json|max:1024', // máximo 1MB
            ]);
            
            // Ler o conteúdo do arquivo
            $content = file_get_contents($this->importFile->getRealPath());
            $importData = json_decode($content, true);
            
            // Validar estrutura do JSON
            if (!isset($importData['form']) || !isset($importData['fields'])) {
                throw new \Exception(__('messages.invalid_form_structure'));
            }
            
            // Iniciar transação do banco de dados
            DB::beginTransaction();
            
            // Criar o formulário
            $form = new CustomForm();
            $form->name = $importData['form']['name'];
            $form->description = $importData['form']['description'] ?? null;
            $form->entity_type = $importData['form']['entity_type'];
            $form->is_active = $importData['form']['is_active'] ?? true;
            $form->created_by = auth()->id();
            $form->save();
            
            // Criar os campos
            foreach ($importData['fields'] as $index => $fieldData) {
                $field = new CustomFormField();
                $field->form_id = $form->id;
                $field->label = $fieldData['label'];
                $field->name = $fieldData['name'];
                $field->type = $fieldData['type'];
                $field->options = $fieldData['options'] ?? [];
                $field->validation_rules = $fieldData['validation_rules'] ?? [];
                $field->description = $fieldData['description'] ?? null;
                $field->is_required = $fieldData['is_required'] ?? false;
                $field->order = $fieldData['order'] ?? $index; // Usar índice como ordem se não fornecido
                $field->save();
            }
            
            // Confirmar a transação
            DB::commit();
            
            // Fechar modal e notificar sucesso
            $this->closeModal();
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.form_imported_successfully')
            );
            
        } catch (\Exception $e) {
            // Reverter transação em caso de erro
            DB::rollBack();
            
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.form_import_error') . ': ' . $e->getMessage()
            );
        }
    }
    
    // Método chamado quando o valor de currentField.type é alterado
    public function updatedCurrentFieldType($value)
    {
        // Se o tipo de campo mudar para select, checkbox ou radio, inicializar opções automaticamente
        if (in_array($value, ['select', 'checkbox', 'radio'])) {
            if (!isset($this->currentField['options']) || !is_array($this->currentField['options']) || empty($this->currentField['options'])) {
                // Adicionar opções padrão para facilitar
                $this->currentField['options'] = [
                    ['label' => 'Opção 1', 'value' => 'opcao_1'],
                    ['label' => 'Opção 2', 'value' => 'opcao_2'],
                ];
                
                $this->dispatch('notify', 
                    type: 'info', 
                    message: 'Opções padrão foram adicionadas. Você pode modificar ou adicionar mais opções abaixo.'
                );
            }
        } 
        // Se o tipo de campo mudar para relationship, inicializar a configuração
        elseif ($value === 'relationship') {
            $this->currentField['relationship_config'] = [
                'model' => null,
                'display_field' => 'name',
                'relationship_type' => 'belongsTo',
            ];
            
            // Sincronizar com a propriedade relationshipConfig
            $this->relationshipConfig = $this->currentField['relationship_config'];
            
            // Limpar dados de amostra
            $this->relationshipSampleData = [];
            $this->availableColumns = [];
            
            $this->dispatch('notify', 
                type: 'info', 
                message: 'Selecione o modelo relacionado e o campo de exibição.'
            );
        }
    }
    
    public function render()
    {
        $query = CustomForm::query()
            ->where('entity_type', 'shipping_note')
            ->when($this->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);
            
        $forms = $query->paginate($this->perPage);
        
        $formFields = [];
        if ($this->currentFormId) {
            $formFields = CustomFormField::where('form_id', $this->currentFormId)
                ->orderBy('order')
                ->get();
        }
        
        // Prepara os dados para a view
        $data = [
            'forms' => $forms,
            'formFields' => $formFields,
        ];
        
        // O componente sempre renderiza sua própria view
        return view('livewire.supply-chain.custom-form-builder', $data);
    }
}

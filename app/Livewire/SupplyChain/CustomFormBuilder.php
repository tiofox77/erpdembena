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
    ];
    
    public $currentFieldId = null;
    public $currentField = [
        'label' => '',
        'name' => '',
        'type' => 'text',
        'options' => [],
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
    ];
    
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
    
    protected $listeners = ['refreshComponent' => '$refresh'];
    
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
    
    public function mount()
    {
        // Inicialização
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
        $this->currentFormId = $id;
        $form = CustomForm::findOrFail($id);
        
        $this->currentForm = [
            'name' => $form->name,
            'description' => $form->description,
            'entity_type' => $form->entity_type,
            'is_active' => $form->is_active,
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
        ]);
        
        if ($this->currentFormId) {
            // Atualizar formulário existente
            $form = CustomForm::findOrFail($this->currentFormId);
            $form->update([
                'name' => $this->currentForm['name'],
                'description' => $this->currentForm['description'],
                'entity_type' => $this->currentForm['entity_type'],
                'is_active' => $this->currentForm['is_active'],
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
        
        $this->showFieldModal = true;
    }
    
    public function saveField()
    {
        // Validar dados do campo
        $this->validate([
            'currentField.label' => 'required|string|max:255',
            'currentField.name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'currentField.type' => 'required|string|in:' . implode(',', array_keys($this->fieldTypes)),
            'currentField.description' => 'nullable|string|max:1000',
            'currentField.is_required' => 'boolean',
            'currentField.order' => 'integer|min:0',
        ]);
        
        // Gerar o nome do campo a partir do rótulo se não for fornecido
        if (empty($this->currentField['name'])) {
            $this->currentField['name'] = Str::snake($this->currentField['label']);
        }
        
        // Validar unicidade do nome do campo no formulário
        $query = CustomFormField::where('form_id', $this->currentFormId)
            ->where('name', $this->currentField['name']);
            
        if ($this->currentFieldId) {
            $query->where('id', '!=', $this->currentFieldId);
        }
        
        if ($query->exists()) {
            $this->addError('currentField.name', 'Este nome de campo já está em uso neste formulário.');
            return;
        }
        
        if ($this->currentFieldId) {
            // Atualizar campo existente
            $field = CustomFormField::findOrFail($this->currentFieldId);
            $field->update([
                'label' => $this->currentField['label'],
                'name' => $this->currentField['name'],
                'type' => $this->currentField['type'],
                'options' => $this->currentField['options'],
                'validation_rules' => $this->currentField['validation_rules'],
                'description' => $this->currentField['description'],
                'is_required' => $this->currentField['is_required'],
                'order' => $this->currentField['order'],
            ]);
            
            $this->dispatch('notify', 
                type: 'warning', 
                message: __('messages.field_updated')
            );
        } else {
            // Criar novo campo
            CustomFormField::create([
                'form_id' => $this->currentFormId,
                'label' => $this->currentField['label'],
                'name' => $this->currentField['name'],
                'type' => $this->currentField['type'],
                'options' => $this->currentField['options'],
                'validation_rules' => $this->currentField['validation_rules'],
                'description' => $this->currentField['description'],
                'is_required' => $this->currentField['is_required'],
                'order' => $this->currentField['order'],
            ]);
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.field_created')
            );
        }
        
        $this->resetField();
        $this->showFieldModal = false;
    }
    
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
        ];
        $this->resetErrorBag();
    }
    
    public function resetField()
    {
        $this->currentFieldId = null;
        $this->currentField = [
            'label' => '',
            'name' => '',
            'type' => 'text',
            'options' => [],
            'validation_rules' => [],
            'description' => '',
            'is_required' => false,
            'order' => 0,
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

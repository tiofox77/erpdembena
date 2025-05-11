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
    
    protected $listeners = ['openFormSubmission', 'viewFormSubmission'];
    
    public function openFormSubmission($shippingNoteId, $customFormId)
    {
        $this->reset('formData', 'fileUploads');
        
        $this->shippingNoteId = $shippingNoteId;
        $this->customFormId = $customFormId;
        
        // Verificar se o formulário existe
        $form = CustomForm::findOrFail($customFormId);
        
        // Inicializar os campos do formulário
        foreach ($form->fields as $field) {
            $this->formData[$field->name] = '';
            if ($field->type === 'file') {
                $this->fileUploads[$field->name] = null;
            }
        }
        
        $this->showFormModal = true;
    }
    
    public function viewFormSubmission($submissionId)
    {
        $this->submissionId = $submissionId;
        $this->showSubmissionDetailModal = true;
    }
    
    public function submitForm()
    {
        $form = CustomForm::findOrFail($this->customFormId);
        $fields = $form->fields;
        
        // Construir regras de validação com base nos campos do formulário
        $rules = [];
        $messages = [];
        
        foreach ($fields as $field) {
            $fieldRules = [];
            
            // Regra básica com base no tipo do campo
            if ($field->is_required) {
                if ($field->type === 'file') {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'required';
                }
            } else {
                if ($field->type === 'file') {
                    $fieldRules[] = 'nullable';
                } else {
                    $fieldRules[] = 'nullable';
                }
            }
            
            // Regras adicionais com base no tipo
            if ($field->type === 'email') {
                $fieldRules[] = 'email';
            } elseif ($field->type === 'number') {
                $fieldRules[] = 'numeric';
            } elseif ($field->type === 'date') {
                $fieldRules[] = 'date';
            } elseif ($field->type === 'file') {
                $fieldRules[] = 'file';
                $fieldRules[] = 'max:10240'; // 10MB max por padrão
            }
            
            // Adicionar regras de validação personalizadas
            if (!empty($field->validation_rules)) {
                $fieldRules = array_merge($fieldRules, $field->validation_rules);
            }
            
            if ($field->type === 'file') {
                $rules['fileUploads.' . $field->name] = $fieldRules;
                $messages['fileUploads.' . $field->name . '.required'] = 'O campo ' . $field->label . ' é obrigatório.';
            } else {
                $rules['formData.' . $field->name] = $fieldRules;
                $messages['formData.' . $field->name . '.required'] = 'O campo ' . $field->label . ' é obrigatório.';
            }
        }
        
        // Validar dados
        $this->validate($rules, $messages);
        
        // Criar a submissão
        $submission = CustomFormSubmission::create([
            'form_id' => $this->customFormId,
            'entity_id' => $this->shippingNoteId,
            'created_by' => Auth::id(),
        ]);
        
        // Salvar os valores dos campos
        foreach ($fields as $field) {
            if ($field->type === 'file') {
                if (isset($this->fileUploads[$field->name]) && $this->fileUploads[$field->name]) {
                    // Criar o valor do campo
                    $fieldValue = CustomFormFieldValue::create([
                        'submission_id' => $submission->id,
                        'field_id' => $field->id,
                        'value' => 'file_upload', // Valor padrão para uploads
                    ]);
                    
                    // Processar o upload do arquivo
                    $file = $this->fileUploads[$field->name];
                    $filename = md5($file->getClientOriginalName() . time()) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('form-attachments', $filename, 'public');
                    
                    // Criar o registro de anexo
                    CustomFormAttachment::create([
                        'field_value_id' => $fieldValue->id,
                        'filename' => $filename,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'path' => $path,
                        'size' => $file->getSize(),
                    ]);
                }
            } else {
                if (isset($this->formData[$field->name])) {
                    CustomFormFieldValue::create([
                        'submission_id' => $submission->id,
                        'field_id' => $field->id,
                        'value' => $this->formData[$field->name],
                    ]);
                }
            }
        }
        
        // Atualizar a nota de envio com o ID do formulário personalizado
        $shippingNote = ShippingNote::findOrFail($this->shippingNoteId);
        $shippingNote->update([
            'custom_form_id' => $this->customFormId,
        ]);
        
        $this->dispatch('notify', 
            type: 'success', 
            message: __('messages.form_submitted_successfully')
        );
        
        // Limpar formulário e fechar modal
        $this->reset('formData', 'fileUploads');
        $this->showFormModal = false;
        
        // Atualizar a lista de notas de envio
        $this->dispatch('refreshComponent');
    }
    
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showSubmissionDetailModal = false;
    }
    
    public function downloadAttachment($attachmentId)
    {
        $attachment = CustomFormAttachment::findOrFail($attachmentId);
        return Storage::disk('public')->download($attachment->path, $attachment->original_filename);
    }
    
    public function render()
    {
        $form = null;
        $fields = [];
        $submission = null;
        $fieldValues = [];
        
        if ($this->customFormId) {
            $form = CustomForm::with('fields')->find($this->customFormId);
            if ($form) {
                $fields = $form->fields;
            }
        }
        
        if ($this->submissionId) {
            $submission = CustomFormSubmission::with(['fieldValues.field', 'fieldValues.attachments'])->find($this->submissionId);
            if ($submission) {
                foreach ($submission->fieldValues as $value) {
                    $fieldValues[$value->field->name] = $value;
                }
            }
        }
        
        return view('livewire.supply-chain.custom-form-submission-manager', [
            'form' => $form,
            'fields' => $fields,
            'submission' => $submission,
            'fieldValues' => $fieldValues,
        ]);
    }
}

<?php

namespace App\Livewire\HR;

use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeDocument;
use App\Models\HR\JobPosition;
use App\Models\HR\Bank;
use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Employees extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'full_name';
    public $sortDirection = 'asc';
    public $filters = [
        'department_id' => '',
        'position_id' => '',
        'employment_status' => '',
        'gender' => '',
        'hire_date_from' => '',
        'salary_range' => '',
        'has_advances' => '',
    ];

    // Form properties
    public $employee_id;
    public $full_name;
    public $date_of_birth;
    public $gender;
    public $id_card;
    public $tax_number;
    public $address;
    public $phone;
    public $email;
    public $marital_status;
    public $dependents;
    public $photo;
    public $bank_id;
    public $bank_name;
    public $bank_account;
    public $bank_iban;
    public $position_id;
    public $department_id;
    public $hire_date;
    public $employment_status;
    public $inss_number;
    public $base_salary;
    public $food_benefit;
    public $transport_benefit;
    public $bonus_amount;

    // Document management
    public $newDocumentType = '';
    public $newDocumentTitle = '';
    public $newDocumentFile = null;
    public $newDocumentExpiryDate = '';
    public $newDocumentRemarks = '';
    public $employeeDocuments = [];
    public $showDocumentModal = false;
    public $documentToDelete = null;
    public $showDuplicateConfirmation = false;
    public $existingDocument = null;
    public $pendingUploadData = [];

    // Modal control
    public $showModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    public ?int $deleteId = null;
    public $viewEmployee = null;
    public $expiringDocuments = [];

    // Import/Export
    public $importFile = null;
    public $showImportModal = false;
    public array $importResults = [];

    public $employeeToDelete = null;
    public $isEditing = false;

    // Listeners
    protected $listeners = ['refreshEmployees' => '$refresh'];

    // Rules
    protected function rules()
    {
        return [
            'full_name' => 'required|min:3|max:255',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'id_card' => 'nullable|unique:employees,id_card,' . $this->employee_id,
            'tax_number' => 'nullable|unique:employees,tax_number,' . $this->employee_id,
            'address' => 'nullable',
            'phone' => 'nullable',
            'email' => 'nullable|email|unique:employees,email,' . $this->employee_id,
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'dependents' => 'nullable|integer|min:0',
            'photo' => $this->isEditing ? 'nullable|image|max:1024' : 'nullable|image|max:1024',
            'bank_id' => 'nullable|exists:banks,id',
            'bank_name' => 'nullable',
            'bank_account' => 'nullable',
            'bank_iban' => 'nullable|string|max:34',
            'position_id' => 'nullable|exists:job_positions,id',
            'department_id' => 'nullable|exists:departments,id',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:active,on_leave,terminated,suspended,retired',
            'inss_number' => 'nullable|string|max:30',
            'base_salary' => 'nullable|numeric|min:0',
            'food_benefit' => 'nullable|numeric|min:0',
            'transport_benefit' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
        ];
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset([
            'employee_id', 'full_name', 'date_of_birth', 'gender', 'id_card', 'tax_number',
            'address', 'phone', 'email', 'marital_status', 'dependents', 'photo',
            'bank_id', 'bank_name', 'bank_account', 'bank_iban', 'position_id', 'department_id', 'hire_date',
            'employment_status', 'inss_number', 'base_salary', 'food_benefit', 
            'transport_benefit', 'bonus_amount'
        ]);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(Employee $employee)
    {
        $this->employee_id = $employee->id;
        $this->full_name = $employee->full_name;
        $this->date_of_birth = $employee->date_of_birth;
        $this->gender = $employee->gender;
        $this->id_card = $employee->id_card;
        $this->tax_number = $employee->tax_number;
        $this->address = $employee->address;
        $this->phone = $employee->phone;
        $this->email = $employee->email;
        $this->marital_status = $employee->marital_status;
        $this->dependents = $employee->dependents;
        // Photo is a file upload so we don't set it
        $this->bank_id = $employee->bank_id;
        $this->bank_name = $employee->bank_name;
        $this->bank_account = $employee->bank_account;
        $this->bank_iban = $employee->bank_iban;
        $this->position_id = $employee->position_id;
        $this->department_id = $employee->department_id;
        $this->hire_date = $employee->hire_date;
        $this->employment_status = $employee->employment_status;
        $this->inss_number = $employee->inss_number;
        $this->base_salary = $employee->base_salary;
        $this->food_benefit = $employee->food_benefit;
        $this->transport_benefit = $employee->transport_benefit;
        $this->bonus_amount = $employee->bonus_amount;

        // Load employee documents if available
        $this->employeeDocuments = $employee->documents()->latest()->get();

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function confirmDelete(Employee $employee)
    {
        $this->employee_id = $employee->id;
        $this->showDeleteModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();
        
        // Ensure numeric fields have default values instead of null
        $numericFields = ['dependents', 'base_salary', 'food_benefit', 'transport_benefit', 'bonus_amount'];
        foreach ($numericFields as $field) {
            if (is_null($validatedData[$field]) || $validatedData[$field] === '') {
                $validatedData[$field] = 0;
            }
        }

        if ($this->isEditing) {
            $employee = Employee::find($this->employee_id);
            
            // Check if a new photo was uploaded
            if ($this->photo && is_object($this->photo)) {
                // Create folder path with employee's name (sanitized)
                $folderName = 'employee-photos/' . $this->employee_id . '-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $this->full_name));
                
                // Get file extension and create a unique filename with employee name
                $extension = $this->photo->getClientOriginalExtension();
                $fileName = 'photo-' . \Str::slug($this->full_name) . '-' . time() . '.' . $extension;
                
                // Store the new photo in the employee's folder with custom name
                $validatedData['photo'] = $this->photo->storeAs($folderName, $fileName, 'public');
                
                // Delete the old photo if it exists
                if ($employee->photo && \Storage::disk('public')->exists($employee->photo)) {
                    \Storage::disk('public')->delete($employee->photo);
                }
            } else {
                // If no new photo was uploaded, remove photo from validated data
                // to avoid overwriting the existing photo with null
                unset($validatedData['photo']);
            }
            
            $employee->update($validatedData);
            $this->dispatch('notify', 
                type: 'warning',
                title: __('messages.success'),
                message: __('messages.employee_updated')
            );
        } else {
            // For new employees creating a record first to get the ID
            $employee = Employee::create($validatedData);
            
            // Store the photo if provided using employee ID for organization
            if ($this->photo && is_object($this->photo)) {
                // Create folder path with employee's name and ID
                $folderName = 'employee-photos/' . $employee->id . '-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $this->full_name));
                
                // Get file extension and create a unique filename
                $extension = $this->photo->getClientOriginalExtension();
                $fileName = 'photo-' . \Str::slug($this->full_name) . '-' . time() . '.' . $extension;
                
                // Store the photo
                $photoPath = $this->photo->storeAs($folderName, $fileName, 'public');
                
                // Update the employee record with the photo path
                $employee->update(['photo' => $photoPath]);
            }
            
            $this->dispatch('notify', 
                type: 'success',
                title: __('messages.success'),
                message: __('messages.employee_created')
            );
        }

        $this->showModal = false;
        $this->reset([
            'employee_id', 'full_name', 'date_of_birth', 'gender', 'id_card', 'tax_number',
            'address', 'phone', 'email', 'marital_status', 'dependents', 'photo',
            'bank_name', 'bank_account', 'position_id', 'department_id', 'hire_date',
            'employment_status', 'inss_number', 'base_salary', 'food_benefit', 
            'transport_benefit', 'bonus_amount'
        ]);
    }

    public function delete()
    {
        $employee = Employee::find($this->employee_id);
        $employee->delete();
        $this->showDeleteModal = false;
        $this->dispatch('notify', 
            type: 'error',
            title: __('messages.success'),
            message: __('messages.employee_deleted')
        );
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
    }
    
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
    }
    
    public function resetFilters()
    {
        $this->reset('filters');
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Show document upload modal
     */
    public function showDocumentUploadModal()
    {
        $this->reset(['newDocumentType', 'newDocumentTitle', 'newDocumentFile', 'newDocumentExpiryDate', 'newDocumentRemarks']);
        $this->showDocumentModal = true;
    }

    /**
     * Close document upload modal
     */
    public function closeDocumentModal()
    {
        $this->showDocumentModal = false;
    }

    /**
     * Upload a new document for an employee
     */
    public function uploadDocument()
    {
        $this->validate([
            'newDocumentType' => 'required|string|max:255',
            'newDocumentTitle' => 'required|string|max:255',
            'newDocumentFile' => 'required|file|max:10240', // Max 10MB
            'newDocumentExpiryDate' => 'nullable|date',
            'newDocumentRemarks' => 'nullable|string',
        ]);

        if (!$this->employee_id) {
            session()->flash('error', 'Employee ID is required.');
            return;
        }

        // Check if document of same type already exists
        $existingDocument = \App\Models\HR\EmployeeDocument::where('employee_id', $this->employee_id)
            ->where('document_type', $this->newDocumentType)
            ->first();

        if ($existingDocument) {
            // Store pending upload data for later use
            $this->pendingUploadData = [
                'type' => $this->newDocumentType,
                'title' => $this->newDocumentTitle,
                'file' => $this->newDocumentFile,
                'expiry_date' => $this->newDocumentExpiryDate,
                'remarks' => $this->newDocumentRemarks,
            ];
            
            $this->existingDocument = $existingDocument;
            $this->showDuplicateConfirmation = true;
            return;
        }

        // Proceed with normal upload
        $this->processDocumentUpload();
    }

    /**
     * Process the actual document upload
     */
    private function processDocumentUpload($data = null)
    {
        // Use pending data if replacing, otherwise use current form data
        $uploadData = $data ?? [
            'type' => $this->newDocumentType,
            'title' => $this->newDocumentTitle,
            'file' => $this->newDocumentFile,
            'expiry_date' => $this->newDocumentExpiryDate,
            'remarks' => $this->newDocumentRemarks,
        ];

        // Store the file
        $filePath = $uploadData['file']->store('employee-documents', 'public');
        
        // Store the filename in the title if not provided
        if (empty($uploadData['title'])) {
            $uploadData['title'] = $uploadData['file']->getClientOriginalName();
        }

        // Create the document record
        $document = \App\Models\HR\EmployeeDocument::create([
            'employee_id' => $this->employee_id,
            'document_type' => $uploadData['type'],
            'title' => $uploadData['title'],
            'file_path' => $filePath,
            'expiry_date' => $uploadData['expiry_date'],
            'is_verified' => false,
            'remarks' => $uploadData['remarks'],
        ]);

        // Refresh documents list
        $this->employeeDocuments = Employee::find($this->employee_id)->documents()->latest()->get();
        
        // Reset form and close modal
        $this->reset(['newDocumentType', 'newDocumentTitle', 'newDocumentFile', 'newDocumentExpiryDate', 'newDocumentRemarks']);
        $this->showDocumentModal = false;
        
        session()->flash('message', __('messages.document_uploaded_successfully'));
    }

    /**
     * Confirm replacement of existing document
     */
    public function confirmDocumentReplacement()
    {
        if (!$this->existingDocument || empty($this->pendingUploadData)) {
            return;
        }

        // Delete the old document file
        if (Storage::disk('public')->exists($this->existingDocument->file_path)) {
            Storage::disk('public')->delete($this->existingDocument->file_path);
        }

        // Delete the old document record
        $this->existingDocument->delete();

        // Process the new upload
        $this->processDocumentUpload($this->pendingUploadData);

        // Reset confirmation state
        $this->showDuplicateConfirmation = false;
        $this->existingDocument = null;
        $this->pendingUploadData = [];

        session()->flash('message', __('messages.document_replaced_successfully'));
    }

    /**
     * Cancel document replacement
     */
    public function cancelDocumentReplacement()
    {
        $this->showDuplicateConfirmation = false;
        $this->existingDocument = null;
        $this->pendingUploadData = [];
    }

    /**
     * Download an employee document
     */
    public function downloadDocument($documentId)
    {
        $document = \App\Models\HR\EmployeeDocument::findOrFail($documentId);
        
        // Check if user has permission to download this document
        if ($document->employee_id != $this->employee_id) {
            session()->flash('error', 'You do not have permission to download this document.');
            return;
        }
        
        // Get the file name from the path if not available
        $fileName = $document->title;
        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
        if ($extension) {
            $fileName .= '.' . $extension;
        }
        
        return response()->download(storage_path('app/public/' . $document->file_path), $fileName);
    }

    /**
     * Confirm delete for a document
     */
    public function confirmDeleteDocument($documentId)
    {
        $this->documentToDelete = $documentId;
        // We'll use JavaScript confirm dialog in blade file
    }

    /**
     * Delete an employee document
     */
    public function deleteDocument()
    {
        if (!$this->documentToDelete) {
            return;
        }
        
        $document = \App\Models\HR\EmployeeDocument::findOrFail($this->documentToDelete);
        
        // Check if user has permission to delete this document
        if ($document->employee_id != $this->employee_id) {
            session()->flash('error', 'You do not have permission to delete this document.');
            return;
        }
        
        // Delete the file from storage
        if (\Storage::disk('public')->exists($document->file_path)) {
            \Storage::disk('public')->delete($document->file_path);
        }
        
        // Delete the record
        $document->delete();
        
        // Refresh documents list
        $this->employeeDocuments = Employee::find($this->employee_id)->documents()->latest()->get();
        
        // Reset
        $this->documentToDelete = null;
        
        session()->flash('message', 'Document deleted successfully.');
    }

    /**
     * Toggle document verification status
     */
    public function toggleDocumentVerification($documentId)
    {
        $document = \App\Models\HR\EmployeeDocument::findOrFail($documentId);
        
        // Check if user has permission to verify this document
        if ($document->employee_id != $this->employee_id) {
            session()->flash('error', 'You do not have permission to verify this document.');
            return;
        }
        
        // Toggle verification status
        $document->is_verified = !$document->is_verified;
        
        // If now verified, set verification date and verifier
        if ($document->is_verified) {
            $document->verification_date = now();
            $document->verified_by = auth()->id(); // Assumes logged in user is an employee
        } else {
            $document->verification_date = null;
            $document->verified_by = null;
        }
        
        $document->save();
        
        // Refresh documents list
        $this->employeeDocuments = Employee::find($this->employee_id)->documents()->latest()->get();
        
        session()->flash('message', $document->is_verified ? 
            'Document verified successfully.' : 
            'Document verification removed.');
    }

    /**
     * View employee details
     */
    public function viewEmployee(Employee $employee)
    {
        $this->employee_id = $employee->id;
        $this->full_name = $employee->full_name;
        $this->date_of_birth = $employee->date_of_birth;
        $this->gender = $employee->gender;
        $this->id_card = $employee->id_card;
        $this->tax_number = $employee->tax_number;
        $this->address = $employee->address;
        $this->phone = $employee->phone;
        $this->email = $employee->email;
        $this->marital_status = $employee->marital_status;
        $this->dependents = $employee->dependents;
        $this->photo = null; // Reset photo to prevent old photo from being updated
        $this->bank_name = $employee->bank_name;
        $this->bank_account = $employee->bank_account;
        $this->position_id = $employee->position_id;
        $this->department_id = $employee->department_id;
        $this->hire_date = $employee->hire_date;
        $this->employment_status = $employee->employment_status;
        $this->inss_number = $employee->inss_number;
        $this->base_salary = $employee->base_salary;
        
        // Load employee documents
        $this->employeeDocuments = $employee->documents()->latest()->get();
        
        $this->showViewModal = true;
    }

    public function render()
    {
        $employees = Employee::with(['department', 'position', 'bank', 'salaryAdvances'])
            ->where('full_name', 'like', "%{$this->search}%")
            ->when($this->filters['department_id'], function ($query) {
                return $query->where('department_id', $this->filters['department_id']);
            })
            ->when($this->filters['position_id'], function ($query) {
                return $query->where('position_id', $this->filters['position_id']);
            })
            ->when($this->filters['employment_status'], function ($query) {
                return $query->where('employment_status', $this->filters['employment_status']);
            })
            ->when($this->filters['gender'], function ($query) {
                return $query->where('gender', $this->filters['gender']);
            })
            ->when($this->filters['hire_date_from'], function ($query) {
                return $query->where('hire_date', '>=', $this->filters['hire_date_from']);
            })
            ->when($this->filters['salary_range'], function ($query) {
                $range = explode('-', $this->filters['salary_range']);
                if (count($range) === 2) {
                    if ($range[1] === '+') {
                        return $query->where('base_salary', '>=', (int)$range[0]);
                    } else {
                        return $query->whereBetween('base_salary', [(int)$range[0], (int)$range[1]]);
                    }
                }
                return $query;
            })
            ->when($this->filters['has_advances'], function ($query) {
                switch ($this->filters['has_advances']) {
                    case 'with_advances':
                        return $query->whereHas('salaryAdvances');
                    case 'without_advances':
                        return $query->whereDoesntHave('salaryAdvances');
                    case 'pending_advances':
                        return $query->whereHas('salaryAdvances', function ($q) {
                            $q->where('status', 'pending');
                        });
                    default:
                        return $query;
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $departments = Department::where('is_active', true)->get();
        $positions = JobPosition::where('is_active', true)->get();
        $banks = Bank::where('is_active', true)->orderBy('name')->get();

        return view('livewire.hr.employees', [
            'employees' => $employees,
            'departments' => $departments,
            'positions' => $positions,
            'banks' => $banks,
        ])->layout('layouts.livewire', [
            'title' => __('employees.employee_management')
        ]);
    }

    public function view($employeeId)
    {
        $this->viewEmployee = Employee::with(['department', 'position', 'documents'])->find($employeeId);
        
        if ($this->viewEmployee) {
            $this->employeeDocuments = $this->viewEmployee->documents;
            
            // Check for documents expiring within 30 days
            $this->expiringDocuments = $this->employeeDocuments->filter(function ($document) {
                if ($document->expiry_date) {
                    $expiryDate = \Carbon\Carbon::parse($document->expiry_date);
                    $now = \Carbon\Carbon::now();
                    $thirtyDaysFromNow = $now->copy()->addDays(30);
                    
                    return $expiryDate->between($now, $thirtyDaysFromNow);
                }
                return false;
            });
            
            $this->showViewModal = true;
        }
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewEmployee = null;
        $this->employeeDocuments = [];
        $this->expiringDocuments = [];
    }

    public function manageDocuments($employeeId)
    {
        $this->employee_id = $employeeId;
        $employee = Employee::find($employeeId);
        
        if ($employee) {
            // Load employee documents
            $this->employeeDocuments = $employee->documents()->latest()->get();
            
            // Reset document form fields
            $this->reset(['newDocumentType', 'newDocumentTitle', 'newDocumentFile', 'newDocumentExpiryDate', 'newDocumentRemarks']);
            
            // Show document management modal
            $this->showDocumentModal = true;
        }
    }

    public function exportToExcel()
    {
        try {
            // Log início do processo
            \Log::info('Iniciando exportação Excel de funcionários');
            
            // Verificar se classe Excel existe
            if (!class_exists('Maatwebsite\Excel\Facades\Excel')) {
                throw new \Exception('Pacote maatwebsite/excel não está carregado');
            }
            
            // Verificar se EmployeesExport existe
            if (!class_exists('App\Exports\EmployeesExport')) {
                throw new \Exception('Classe EmployeesExport não encontrada');
            }
            
            // Verificar permissões do diretório temporário
            $tempPath = storage_path('framework/cache/laravel-excel');
            if (!is_dir($tempPath)) {
                \Log::info('Criando diretório temporário: ' . $tempPath);
                mkdir($tempPath, 0755, true);
            }
            
            if (!is_writable($tempPath)) {
                throw new \Exception('Diretório temporário não tem permissões de escrita: ' . $tempPath);
            }
            
            $fileName = 'funcionarios_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            \Log::info('Exportando arquivo: ' . $fileName);
            
            $export = new EmployeesExport();
            $result = Excel::download($export, $fileName);
            
            \Log::info('Exportação Excel concluída com sucesso');
            
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('Erro na exportação Excel: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('notify', type: 'error', message: 'Erro ao exportar: ' . $e->getMessage());
            session()->flash('error', __('messages.export_failed') . ': ' . $e->getMessage());
        }
    }

    public function openImportModal()
    {
        $this->reset(['importFile', 'importResults']);
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'importResults']);
    }

    public function importFromExcel()
    {
        \Log::info('DEBUG: importFromExcel method called');
        \Log::info('DEBUG: importFile value:', ['file' => $this->importFile]);
        
        try {
            $this->validate([
                'importFile' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
            ]);
            \Log::info('DEBUG: Validation passed');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('DEBUG: Validation failed:', $e->errors());
            session()->flash('error', 'Erro de validação: ' . implode(', ', $e->validator->errors()->all()));
            return;
        }

        try {
            \Log::info('DEBUG: Starting import process');
            $import = new EmployeesImport();
            Excel::import($import, $this->importFile->getRealPath());
            \Log::info('DEBUG: Import completed successfully');
            
            $this->importResults = [
                'success' => true,
                'message' => __('messages.import_completed_successfully'),
                'details' => [
                    'total_processed' => 0, // You can enhance this by tracking in the import class
                    'created' => 0,
                    'updated' => 0,
                ]
            ];

            session()->flash('success', __('messages.employees_imported_successfully'));
            $this->closeImportModal();
            
        } catch (\Exception $e) {
            \Log::error('DEBUG: Import failed with exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->importResults = [
                'success' => false,
                'message' => __('messages.import_failed') . ': ' . $e->getMessage(),
                'details' => []
            ];
            
            session()->flash('error', __('messages.import_failed') . ': ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            // Create empty export with just headers
            $templateData = collect([
                // Empty row with all the expected column structure
                [
                    'full_name' => '',
                    'id_card' => '',
                    'tax_number' => '',
                    'email' => '',
                    'phone' => '',
                    'date_of_birth' => '',
                    'gender' => '',
                    'marital_status' => '',
                    'dependents' => '',
                    'address' => '',
                    'department' => '',
                    'position' => '',
                    'hire_date' => '',
                    'employment_status' => '',
                    'bank_name' => '',
                    'bank_account' => '',
                    'bank_iban' => '',
                    'inss_number' => '',
                    'base_salary' => '',
                    'food_benefit' => '',
                    'transport_benefit' => '',
                    'bonus_amount' => '',
                ]
            ]);

            $fileName = 'template_funcionarios.xlsx';
            
            return Excel::download(new EmployeesExport, $fileName);
            
        } catch (\Exception $e) {
            session()->flash('error', __('messages.template_download_failed') . ': ' . $e->getMessage());
        }
    }
}

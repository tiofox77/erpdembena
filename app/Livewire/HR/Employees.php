<?php

namespace App\Livewire\HR;

use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeDocument;
use App\Models\HR\JobPosition;
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
    public $bank_name;
    public $bank_account;
    public $position_id;
    public $department_id;
    public $hire_date;
    public $employment_status;
    public $inss_number;
    public $base_salary;

    // Document management
    public $newDocumentType = '';
    public $newDocumentTitle = '';
    public $newDocumentFile = null;
    public $newDocumentExpiryDate = null;
    public $newDocumentRemarks = '';
    public $employeeDocuments = [];
    public $showDocumentModal = false;
    public $documentToDelete = null;

    // Modal flags
    public $showModal = false;
    public $showDeleteModal = false;
    public $showViewModal = false;
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
            'bank_name' => 'nullable',
            'bank_account' => 'nullable',
            'position_id' => 'nullable|exists:job_positions,id',
            'department_id' => 'nullable|exists:departments,id',
            'hire_date' => 'required|date',
            'employment_status' => 'required|in:active,on_leave,terminated,suspended,retired',
            'inss_number' => 'nullable|string|max:30',
            'base_salary' => 'nullable|numeric|min:0',
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
            'bank_name', 'bank_account', 'position_id', 'department_id', 'hire_date',
            'employment_status', 'inss_number', 'base_salary'
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
        $this->bank_name = $employee->bank_name;
        $this->bank_account = $employee->bank_account;
        $this->position_id = $employee->position_id;
        $this->department_id = $employee->department_id;
        $this->hire_date = $employee->hire_date;
        $this->employment_status = $employee->employment_status;
        $this->inss_number = $employee->inss_number;
        $this->base_salary = $employee->base_salary;

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
            'employment_status'
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

        // Store the file
        $filePath = $this->newDocumentFile->store('employee-documents', 'public');
        // Store the filename in the title if not provided
        if (empty($this->newDocumentTitle)) {
            $this->newDocumentTitle = $this->newDocumentFile->getClientOriginalName();
        }

        // Create the document record
        $document = \App\Models\HR\EmployeeDocument::create([
            'employee_id' => $this->employee_id,
            'document_type' => $this->newDocumentType,
            'title' => $this->newDocumentTitle,
            'file_path' => $filePath,
            'expiry_date' => $this->newDocumentExpiryDate,
            'is_verified' => false,
            'remarks' => $this->newDocumentRemarks,
        ]);

        // Refresh documents list
        $this->employeeDocuments = Employee::find($this->employee_id)->documents()->latest()->get();
        
        // Reset form and close modal
        $this->reset(['newDocumentType', 'newDocumentTitle', 'newDocumentFile', 'newDocumentExpiryDate', 'newDocumentRemarks']);
        $this->showDocumentModal = false;
        
        session()->flash('message', 'Document uploaded successfully.');
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
    
    /**
     * Close the view modal
     */
    public function closeViewModal()
    {
        $this->showViewModal = false;
    }

    public function render()
    {
        $employees = Employee::where('full_name', 'like', "%{$this->search}%")
            ->when($this->filters['department_id'], function ($query) {
                return $query->where('department_id', $this->filters['department_id']);
            })
            ->when($this->filters['position_id'], function ($query) {
                return $query->where('position_id', $this->filters['position_id']);
            })
            ->when($this->filters['employment_status'], function ($query) {
                return $query->where('employment_status', $this->filters['employment_status']);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $departments = Department::where('is_active', true)->get();
        $positions = JobPosition::where('is_active', true)->get();

        return view('livewire.hr.employees', [
            'employees' => $employees,
            'departments' => $departments,
            'positions' => $positions,
        ]);
    }
}

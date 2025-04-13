<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h2 class="text-xl font-semibold text-gray-800">
                                <i class="fas fa-users mr-2 text-gray-600"></i>
                                {{ __('messages.employees_management') }}
                            </h2>
                            <x-hr-guide-link />
                        </div>
                        <button
                            wire:click="create"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <i class="fas fa-plus mr-1"></i>
                            {{ __('messages.new_employee') }}
                        </button>
                    </div>

                    <!-- Filters and Search -->
                    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label for="search" class="sr-only">{{ __('messages.search') }}</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input
                                        type="text"
                                        id="search"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="{{ __('messages.search_employees') }}"
                                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-2 sm:text-sm border-gray-300 rounded-md"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="filterDepartment" class="sr-only">{{ __('messages.department') }}</label>
                                <select
                                    id="filterDepartment"
                                    wire:model.live="filters.department_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">{{ __('messages.all_departments') }}</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="filterPosition" class="sr-only">{{ __('messages.position') }}</label>
                                <select
                                    id="filterPosition"
                                    wire:model.live="filters.position_id"
                                    class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                                    <option value="">{{ __('messages.all_positions') }}</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Employees Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('name')">
                                                <span>{{ __('messages.name') }}</span>
                                                @if($sortField === 'name')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>{{ __('messages.email') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1 cursor-pointer" wire:click="sortBy('department_id')">
                                                <span>{{ __('messages.department') }}</span>
                                                @if($sortField === 'department_id')
                                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-1">
                                                <span>{{ __('messages.position') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('messages.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($employees as $employee)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $employee->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $employee->department->name ?? __('messages.not_assigned') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $employee->position->title ?? __('messages.not_assigned') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button
                                                    wire:click="viewEmployee({{ $employee->id }})"
                                                    class="text-blue-600 hover:text-blue-900 mr-3"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button
                                                    wire:click="edit({{ $employee->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $employee->id }})"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i class="fas fa-briefcase text-gray-400 text-4xl mb-4"></i>
                                                    <span class="text-lg font-medium">{{ __('messages.no_employees_found') }}</span>
                                                    <p class="text-sm mt-2">
                                                        @if($search || !empty($filters['department_id']) || !empty($filters['position_id']))
                                                            {{ __('messages.no_employees_match') }}
                                                            <button
                                                                wire:click="resetFilters"
                                                                class="text-blue-500 hover:text-blue-700 underline ml-1"
                                                            >
                                                                {{ __('messages.clear_all_filters') }}
                                                            </button>
                                                        @else
                                                            {{ __('messages.no_employees_yet') }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                            @if(method_exists($employees, 'links'))
                                {{ $employees->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Employee Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto m-4">
                <div class="flex justify-between items-center mb-4 p-6 pb-3 border-b sticky top-0 bg-white z-10">
                    <h3 class="text-lg font-medium">
                        <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                        {{ $isEditing ? __('messages.edit') : __('messages.create') }} {{ __('messages.employee') }}
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ __('messages.please_correct_errors') }}
                        </p>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="save">
                    <div class="px-6 py-4">
                        <h4 class="text-md font-medium text-gray-700 mb-2 border-b pb-1">{{ __('messages.personal_information') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Full Name -->
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700">{{ __('messages.full_name') }} <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="text" id="full_name"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('full_name') border-red-300 text-red-900 @enderror"
                                        wire:model.live="full_name" placeholder="Full name">
                                    @error('full_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Date of Birth -->
                            <div>
                                <label for="date_of_birth" class="block text-sm font-medium text-gray-700">{{ __('messages.date_of_birth') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="date" id="date_of_birth"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('date_of_birth') border-red-300 text-red-900 @enderror"
                                        wire:model.live="date_of_birth">
                                    @error('date_of_birth')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700">{{ __('messages.gender') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <select id="gender"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('gender') border-red-300 text-red-900 @enderror"
                                        wire:model.live="gender">
                                        <option value="">{{ __('messages.select_gender') }}</option>
                                        <option value="male">{{ __('messages.male') }}</option>
                                        <option value="female">{{ __('messages.female') }}</option>
                                        <option value="other">{{ __('messages.other') }}</option>
                                    </select>
                                    @error('gender')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- ID Card -->
                            <div>
                                <label for="id_card" class="block text-sm font-medium text-gray-700">{{ __('messages.id_card') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="text" id="id_card"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('id_card') border-red-300 text-red-900 @enderror"
                                        wire:model.live="id_card" placeholder="{{ __('messages.id_card_number') }}">
                                    @error('id_card')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tax Number -->
                            <div>
                                <label for="tax_number" class="block text-sm font-medium text-gray-700">{{ __('messages.tax_number') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="text" id="tax_number"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('tax_number') border-red-300 text-red-900 @enderror"
                                        wire:model.live="tax_number" placeholder="{{ __('messages.tax_identification_number') }}">
                                    @error('tax_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Marital Status -->
                            <div>
                                <label for="marital_status" class="block text-sm font-medium text-gray-700">{{ __('messages.marital_status') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <select id="marital_status"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('marital_status') border-red-300 text-red-900 @enderror"
                                        wire:model.live="marital_status">
                                        <option value="">{{ __('messages.select_status') }}</option>
                                        <option value="single">{{ __('messages.single') }}</option>
                                        <option value="married">{{ __('messages.married') }}</option>
                                        <option value="divorced">{{ __('messages.divorced') }}</option>
                                        <option value="widowed">{{ __('messages.widowed') }}</option>
                                    </select>
                                    @error('marital_status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Dependents -->
                            <div>
                                <label for="dependents" class="block text-sm font-medium text-gray-700">{{ __('messages.dependents') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" min="0" id="dependents"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('dependents') border-red-300 text-red-900 @enderror"
                                        wire:model.live="dependents" placeholder="{{ __('messages.number_of_dependents') }}">
                                    @error('dependents')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Photo (File Upload) -->
                            <div class="md:col-span-3">
                                <label for="photo" class="block text-sm font-medium text-gray-700">{{ __('messages.photo') }}</label>
                                <div class="mt-1 flex items-center">
                                    @if($photo && !is_string($photo))
                                        <div class="mr-3">
                                            <img src="{{ $photo->temporaryUrl() }}" class="h-16 w-16 object-cover rounded-full">
                                        </div>
                                    @elseif($photo && is_string($photo))
                                        <div class="mr-3">
                                            <img src="{{ asset('storage/' . $photo) }}" class="h-16 w-16 object-cover rounded-full">
                                        </div>
                                    @endif
                                    <input type="file" id="photo" accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                        wire:model.live="photo">
                                </div>
                                @error('photo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <h4 class="text-md font-medium text-gray-700 mb-2 border-b pb-1">{{ __('messages.contact_information') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('messages.email') }} <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="email" id="email"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-300 text-red-900 @enderror"
                                        wire:model.live="email" placeholder="{{ __('messages.email_address') }}">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('messages.phone') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="text" id="phone"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-300 text-red-900 @enderror"
                                        wire:model.live="phone" placeholder="{{ __('messages.phone_number') }}">
                                    @error('phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-3">
                                <label for="address" class="block text-sm font-medium text-gray-700">{{ __('messages.address') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <textarea id="address" rows="2"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('address') border-red-300 text-red-900 @enderror"
                                        wire:model.live="address" placeholder="{{ __('messages.full_address') }}"></textarea>
                                    @error('address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <h4 class="text-md font-medium text-gray-700 mb-2 border-b pb-1">{{ __('messages.bank_information') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Bank Name -->
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700">{{ __('messages.bank_name') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <select id="bank_name"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('bank_name') border-red-300 text-red-900 @enderror"
                                        wire:model.live="bank_name">
                                        <option value="">{{ __('messages.select_bank') }}</option>
                                        <option value="Banco Nacional de Angola (BNA)">Banco Nacional de Angola (BNA)</option>
                                        <option value="Banco Angolano de Investimentos (BAI)">Banco Angolano de Investimentos (BAI)</option>
                                        <option value="Banco de Fomento Angola (BFA)">Banco de Fomento Angola (BFA)</option>
                                        <option value="Banco Económico">Banco Económico</option>
                                        <option value="Banco de Poupança e Crédito (BPC)">Banco de Poupança e Crédito (BPC)</option>
                                        <option value="Standard Bank Angola">Standard Bank Angola</option>
                                        <option value="Banco Millennium Atlântico">Banco Millennium Atlântico</option>
                                        <option value="Banco BIC">Banco BIC</option>
                                        <option value="Banco Sol">Banco Sol</option>
                                        <option value="Banco Keve">Banco Keve</option>
                                        <option value="Banco BAI Micro Finanças">Banco BAI Micro Finanças</option>
                                        <option value="Banco Comercial do Huambo">Banco Comercial do Huambo</option>
                                        <option value="Banco de Negócios Internacional (BNI)">Banco de Negócios Internacional (BNI)</option>
                                        <option value="Banco de Desenvolvimento de Angola (BDA)">Banco de Desenvolvimento de Angola (BDA)</option>
                                        <option value="Banco Prestígio">Banco Prestígio</option>
                                        <option value="Banco VTB África">Banco VTB África</option>
                                    </select>
                                    @error('bank_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Bank Account -->
                            <div>
                                <label for="bank_account" class="block text-sm font-medium text-gray-700">{{ __('messages.bank_account') }}</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="text" id="bank_account"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('bank_account') border-red-300 text-red-900 @enderror"
                                        wire:model.live="bank_account" placeholder="{{ __('messages.account_number') }}">
                                    @error('bank_account')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <h4 class="text-md font-medium text-gray-700 mb-2 border-b pb-1">{{ __('messages.employment_information') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Department -->
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700">{{ __('messages.department') }} <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <select id="department_id"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('department_id') border-red-300 text-red-900 @enderror"
                                        wire:model.live="department_id">
                                        <option value="">{{ __('messages.select_department') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Position -->
                            <div>
                                <label for="position_id" class="block text-sm font-medium text-gray-700">{{ __('messages.position') }} <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <select id="position_id"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('position_id') border-red-300 text-red-900 @enderror"
                                        wire:model.live="position_id">
                                        <option value="">{{ __('messages.select_position') }}</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}">{{ $position->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('position_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Hire Date -->
                            <div>
                                <label for="hire_date" class="block text-sm font-medium text-gray-700">{{ __('messages.hire_date') }} <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="date" id="hire_date"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('hire_date') border-red-300 text-red-900 @enderror"
                                        wire:model.live="hire_date">
                                    @error('hire_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Employment Status -->
                            <div>
                                <label for="employment_status" class="block text-sm font-medium text-gray-700">{{ __('messages.employment_status') }} <span class="text-red-500">*</span></label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <select id="employment_status"
                                        class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('employment_status') border-red-300 text-red-900 @enderror"
                                        wire:model.live="employment_status">
                                        <option value="">{{ __('messages.select_status_employment') }}</option>
                                        <option value="active">{{ __('messages.active') }}</option>
                                        <option value="on_leave">{{ __('messages.on_leave') }}</option>
                                        <option value="terminated">{{ __('messages.terminated') }}</option>
                                        <option value="suspended">{{ __('messages.suspended') }}</option>
                                        <option value="retired">{{ __('messages.retired') }}</option>
                                    </select>
                                    @error('employment_status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($isEditing)
                    <div class="px-6 py-4">
                        <h4 class="text-md font-medium text-gray-700 mb-2 border-b pb-1">{{ __('messages.documents') }}</h4>
                        <div class="mt-2 bg-gray-50 p-3 rounded">
                            <div class="flex flex-col">
                                <div class="flex justify-between items-center mb-3">
                                    <h5 class="text-sm font-medium text-gray-700">{{ __('messages.employee_documents') }}</h5>
                                    <button type="button" wire:click="showDocumentUploadModal"
                                        class="px-3 py-1 bg-blue-500 text-white rounded-md text-sm hover:bg-blue-600 transition">
                                        <i class="fas fa-plus mr-1"></i> {{ __('messages.add_document') }}
                                    </button>
                                </div>
                                
                                @if(count($employeeDocuments) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full bg-white border border-gray-200 text-sm">
                                            <thead>
                                                <tr class="bg-gray-100">
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.type') }}</th>
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.title') }}</th>
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.expiry_date') }}</th>
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.status') }}</th>
                                                    <th class="py-2 px-3 text-left border-b">{{ __('messages.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($employeeDocuments as $document)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-2 px-3 border-b">
                                                        @switch($document->document_type)
                                                            @case('id_card')
                                                                <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-id-card mr-1"></i> {{ __('messages.id_card_doc') }}
                                                                </span>
                                                                @break
                                                            @case('certificate')
                                                                <span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-certificate mr-1"></i> {{ __('messages.certificate') }}
                                                                </span>
                                                                @break
                                                            @case('professional_card')
                                                                <span class="inline-flex items-center bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-id-badge mr-1"></i> {{ __('messages.professional_card') }}
                                                                </span>
                                                                @break
                                                            @case('contract')
                                                                <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-file-contract mr-1"></i> {{ __('messages.contract') }}
                                                                </span>
                                                                @break
                                                            @default
                                                                <span class="inline-flex items-center bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">
                                                                    <i class="fas fa-file mr-1"></i> {{ __('messages.other_doc') }}
                                                                </span>
                                                        @endswitch
                                                    </td>
                                                    <td class="py-2 px-3 border-b">{{ $document->title }}</td>
                                                    <td class="py-2 px-3 border-b">
                                                        @if($document->expiry_date)
                                                            {{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}
                                                        @else
                                                            <span class="text-gray-400">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 border-b">
                                                        @if($document->is_verified)
                                                            <span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                                <i class="fas fa-check-circle mr-1"></i> {{ __('messages.verified') }}
                                                                @if($document->verification_date)
                                                                    <span class="mx-1 text-xs text-gray-500">{{ \Carbon\Carbon::parse($document->verification_date)->format('d/m/Y') }}</span>
                                                                @endif
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                                                <i class="fas fa-clock mr-1"></i> {{ __('messages.pending') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="py-2 px-3 border-b">
                                                        <div class="flex space-x-2">
                                                            <button type="button" wire:click="downloadDocument({{ $document->id }})"
                                                                class="text-blue-600 hover:text-blue-800">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                            <button type="button" wire:click="toggleDocumentVerification({{ $document->id }})"
                                                                class="{{ $document->is_verified ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }}">
                                                                <i class="fas {{ $document->is_verified ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                                            </button>
                                                            <button type="button" 
                                                                wire:click="confirmDeleteDocument({{ $document->id }})"
                                                                onclick="confirm('Are you sure you want to delete this document?') || event.stopImmediatePropagation()"
                                                                class="text-red-600 hover:text-red-800">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="bg-gray-100 p-4 rounded text-center text-gray-600">
                                        <p><i class="fas fa-file-alt text-xl mb-2"></i></p>
                                        <p>No documents found. Click "Add Document" to upload.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="px-6 py-4 bg-gray-50 border-t sticky bottom-0">
                        <div class="flex justify-end space-x-3">
                            <button type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                wire:click="closeModal">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ $isEditing ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-red-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Delete Employee
                    </h3>
                    <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeDeleteModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-gray-700">Are you sure you want to delete this employee? This action cannot be undone.</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        wire:click="closeDeleteModal">
                        Cancel
                    </button>
                    <button type="button"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        wire:click="delete">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- View Employee Modal -->
    @if($showViewModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto m-4">
            <div class="flex justify-between items-center p-6 pb-3 border-b sticky top-0 bg-white z-10">
                <h3 class="text-xl font-semibold flex items-center text-blue-700">
                    <i class="fas fa-user-circle text-blue-500 mr-2 text-2xl"></i>
                    Employee Details
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeViewModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                <!-- Employee Photo and Basic Info -->
                <div class="md:col-span-1 flex flex-col items-center bg-gray-50 p-6 rounded-lg shadow-sm">
                    <div class="w-32 h-32 bg-gray-200 rounded-full overflow-hidden mb-4 border-4 border-blue-100 shadow-md">
                        @if($photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="{{ $full_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-blue-50">
                                <i class="fas fa-user text-5xl text-blue-300"></i>
                            </div>
                        @endif
                    </div>
                    <h4 class="font-bold text-xl text-center text-gray-800">{{ $full_name }}</h4>
                    @if(isset($position_id) && $positions->contains('id', $position_id))
                        <p class="text-sm text-gray-600 text-center flex items-center mt-1">
                            <i class="fas fa-briefcase text-blue-500 mr-1"></i>
                            {{ $positions->firstWhere('id', $position_id)->title }}
                        </p>
                    @endif
                    @if(isset($department_id) && $departments->contains('id', $department_id))
                        <p class="text-sm text-gray-600 text-center flex items-center mt-1">
                            <i class="fas fa-building text-blue-500 mr-1"></i>
                            {{ $departments->firstWhere('id', $department_id)->name }} Department
                        </p>
                    @endif
                    
                    <div class="mt-5 w-full">
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100 shadow-sm">
                            <h5 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-id-badge mr-2"></i>Employment Information
                            </h5>
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="text-gray-600 flex items-center">
                                    <i class="fas fa-dot-circle text-blue-400 mr-1 text-xs"></i>Status:
                                </div>
                                <div class="font-medium">
                                    @switch($employment_status)
                                        @case('active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Active
                                            </span>
                                            @break
                                        @case('on_leave')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-hourglass-half mr-1"></i> On Leave
                                            </span>
                                            @break
                                        @case('terminated')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i> Terminated
                                            </span>
                                            @break
                                        @case('suspended')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                <i class="fas fa-pause-circle mr-1"></i> Suspended
                                            </span>
                                            @break
                                        @case('retired')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-user-clock mr-1"></i> Retired
                                            </span>
                                            @break
                                        @default
                                            {{ $employment_status }}
                                    @endswitch
                                </div>
                                <div class="text-gray-600 flex items-center">
                                    <i class="fas fa-dot-circle text-blue-400 mr-1 text-xs"></i>Hire Date:
                                </div>
                                <div class="font-medium">
                                    <span class="flex items-center">
                                        <i class="far fa-calendar-alt text-blue-500 mr-1"></i>
                                        {{ $hire_date ? date('d/m/Y', strtotime($hire_date)) : 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Personal and Contact Information -->
                <div class="md:col-span-2">
                    <div class="mb-6 bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                        <h4 class="text-md font-semibold text-blue-700 mb-3 border-b pb-2 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>Personal Information
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-birthday-cake text-blue-400 mr-2"></i>Date of Birth:
                                </p>
                                <p class="font-medium pl-6">{{ $date_of_birth ? date('d/m/Y', strtotime($date_of_birth)) : 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-venus-mars text-blue-400 mr-2"></i>Gender:
                                </p>
                                <p class="font-medium pl-6">{{ $gender ? ucfirst($gender) : 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-id-card text-blue-400 mr-2"></i>ID Card:
                                </p>
                                <p class="font-medium pl-6">{{ $id_card ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-receipt text-blue-400 mr-2"></i>Tax Number:
                                </p>
                                <p class="font-medium pl-6">{{ $tax_number ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-ring text-blue-400 mr-2"></i>Marital Status:
                                </p>
                                <p class="font-medium pl-6">{{ $marital_status ? ucfirst($marital_status) : 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-users text-blue-400 mr-2"></i>Dependents:
                                </p>
                                <p class="font-medium pl-6">{{ $dependents ?: '0' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6 bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                        <h4 class="text-md font-semibold text-blue-700 mb-3 border-b pb-2 flex items-center">
                            <i class="fas fa-address-card mr-2 text-blue-500"></i>Contact Information
                        </h4>
                        <div class="grid grid-cols-1 gap-4 text-sm">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-envelope text-blue-400 mr-2"></i>Email:
                                </p>
                                <p class="font-medium pl-6">{{ $email ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-phone text-blue-400 mr-2"></i>Phone:
                                </p>
                                <p class="font-medium pl-6">{{ $phone ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-map-marker-alt text-blue-400 mr-2"></i>Address:
                                </p>
                                <p class="font-medium pl-6">{{ $address ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4 bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                        <h4 class="text-md font-semibold text-blue-700 mb-3 border-b pb-2 flex items-center">
                            <i class="fas fa-university mr-2 text-blue-500"></i>Bank Information
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-landmark text-blue-400 mr-2"></i>Bank Name:
                                </p>
                                <p class="font-medium pl-6">{{ $bank_name ?: 'N/A' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg">
                                <p class="text-gray-600 flex items-center mb-1">
                                    <i class="fas fa-credit-card text-blue-400 mr-2"></i>Bank Account:
                                </p>
                                <p class="font-medium pl-6">{{ $bank_account ?: 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employee Documents -->
            <div class="px-6 pb-6">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <h4 class="text-md font-semibold text-blue-700 mb-4 border-b pb-2 flex items-center">
                        <i class="fas fa-file-alt text-blue-500 mr-2"></i>
                        Documents
                    </h4>
                    
                    @if(count($employeeDocuments ?? []) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200 text-sm rounded-lg overflow-hidden">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Type</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Title</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Upload Date</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Expiry Date</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Status</th>
                                        <th class="py-3 px-4 text-left border-b font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeeDocuments as $document)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-4 border-b">
                                            @switch($document->document_type)
                                                @case('id_card')
                                                    <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-id-card mr-1"></i> ID Card
                                                    </span>
                                                    @break
                                                @case('certificate')
                                                    <span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-certificate mr-1"></i> Certificate
                                                    </span>
                                                    @break
                                                @case('professional_card')
                                                    <span class="inline-flex items-center bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-id-badge mr-1"></i> Professional
                                                    </span>
                                                    @break
                                                @case('contract')
                                                    <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-file-contract mr-1"></i> Contract
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="inline-flex items-center bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">
                                                        <i class="fas fa-file mr-1"></i> Other
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td class="py-3 px-4 border-b">{{ $document->title }}</td>
                                        <td class="py-3 px-4 border-b">
                                            <span class="flex items-center">
                                                <i class="far fa-calendar-plus text-blue-500 mr-1"></i>
                                                {{ $document->created_at->format('d/m/Y') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 border-b">
                                            @if($document->expiry_date)
                                                <span class="flex items-center">
                                                    <i class="far fa-calendar-times text-red-500 mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 border-b">
                                            @if($document->is_verified)
                                                <span class="inline-flex items-center bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                                    <i class="fas fa-check-circle mr-1"></i> Verified
                                                </span>
                                            @else
                                                <span class="inline-flex items-center bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 border-b">
                                            <button type="button" wire:click="downloadDocument({{ $document->id }})"
                                                class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-2 py-1 rounded flex items-center transition-colors">
                                                <i class="fas fa-download mr-1"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 p-6 rounded-lg text-center text-gray-600 border border-gray-200">
                            <p><i class="fas fa-file-alt text-2xl mb-2 text-blue-300"></i></p>
                            <p>No documents available for this employee.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="bg-gray-50 p-6 border-t flex justify-end space-x-3">
                <button type="button" wire:click="closeViewModal" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Close
                </button>
                <button type="button" wire:click="edit({{ $employee_id }})" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-edit mr-2"></i> Edit
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Document Upload Modal -->
    @if($showDocumentModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-xl max-h-[90vh] overflow-y-auto m-4">
            <div class="flex justify-between items-center p-6 pb-3 border-b sticky top-0 bg-white z-10">
                <h3 class="text-lg font-medium flex items-center">
                    <i class="fas fa-file-upload text-blue-500 mr-2"></i>
                    Upload Document
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeDocumentModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6">
                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                        <p class="font-bold">Please correct the following errors:</p>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit.prevent="uploadDocument">
                    <div class="space-y-4">
                        <!-- Document Type -->
                        <div>
                            <label for="newDocumentType" class="block text-sm font-medium text-gray-700">Document Type</label>
                            <select id="newDocumentType" wire:model="newDocumentType" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Document Type</option>
                                <option value="id_card">ID Card</option>
                                <option value="certificate">Certificate</option>
                                <option value="professional_card">Professional Card</option>
                                <option value="contract">Contract</option>
                                <option value="other">Other</option>
                            </select>
                            @error('newDocumentType') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Document Title -->
                        <div>
                            <label for="newDocumentTitle" class="block text-sm font-medium text-gray-700">{{ __('messages.document_title') }}</label>
                            <input type="text" id="newDocumentTitle" wire:model="newDocumentTitle" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="E.g., National ID Card, Bachelor Degree, etc.">
                            @error('newDocumentTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Document File -->
                        <div>
                            <label for="newDocumentFile" class="block text-sm font-medium text-gray-700">{{ __('messages.upload_file') }}</label>
                            <input type="file" id="newDocumentFile" accept="image/*"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                wire:model.live="newDocumentFile">
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.file_size_restrictions') }}</p>
                            @error('newDocumentFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div>
                            <label for="newDocumentExpiryDate" class="block text-sm font-medium text-gray-700">{{ __('messages.expiry_date_optional') }}</label>
                            <input type="date" id="newDocumentExpiryDate" wire:model="newDocumentExpiryDate" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('newDocumentExpiryDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Remarks -->
                        <div>
                            <label for="newDocumentRemarks" class="block text-sm font-medium text-gray-700">{{ __('messages.remarks_optional') }}</label>
                            <textarea id="newDocumentRemarks" wire:model="newDocumentRemarks" rows="3" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Any additional information about this document"></textarea>
                            @error('newDocumentRemarks') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-6 bg-gray-50 px-4 py-3 border-t flex justify-end space-x-3 sticky bottom-0">
                        <button type="button" wire:click="closeDocumentModal"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-upload mr-1"></i> {{ __('messages.upload') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => show = false, 3000)"
             x-show="show"
             class="fixed bottom-4 right-4 bg-green-500 text-white py-2 px-4 rounded-md shadow-md">
            {{ session('message') }}
        </div>
    @endif
</div>
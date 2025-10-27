<!-- Create/Edit User Modal -->
@if($showModal)
    <div x-data="{ open: @entangle('showModal') }" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50" 
         role="dialog" 
         aria-modal="true"
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0">
        <div class="relative top-20 mx-auto p-1 w-full max-w-2xl">
            <div class="relative bg-white rounded-lg shadow-xl transform transition-all duration-300 ease-in-out" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="transform opacity-0 scale-95" 
                 x-transition:enter-end="transform opacity-100 scale-100" 
                 x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="transform opacity-100 scale-100" 
                 x-transition:leave-end="transform opacity-0 scale-95">
                <!-- Cabeçalho com gradiente -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas {{ $isEditing ? 'fa-user-edit' : 'fa-user-plus' }} mr-2 animate-pulse"></i>
                        {{ $isEditing ? 'Edit User' : 'Create New User' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="saveUser" class="p-6">
                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-md">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <p class="font-bold text-red-700">Please correct the following errors:</p>
                            </div>
                            <ul class="list-disc list-inside text-sm text-red-600 ml-6">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-user text-gray-400 mr-1"></i>
                                First Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="first_name"
                                class="block w-full rounded-md shadow-sm border-gray-300 focus:border-green-500 focus:ring-green-500 @error('user.first_name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="user.first_name">
                            @error('user.first_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-user text-gray-400 mr-1"></i>
                                Last Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="last_name"
                                class="block w-full rounded-md shadow-sm border-gray-300 focus:border-green-500 focus:ring-green-500 @error('user.last_name') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="user.last_name">
                            @error('user.last_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email"
                                class="block w-full rounded-md shadow-sm border-gray-300 focus:border-green-500 focus:ring-green-500 @error('user.email') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="user.email">
                            @error('user.email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-phone text-gray-400 mr-1"></i>
                                Phone <span class="text-gray-500 text-xs">(Optional)</span>
                            </label>
                            <input type="text" id="phone"
                                class="block w-full rounded-md shadow-sm border-gray-300 focus:border-green-500 focus:ring-green-500 @error('user.phone') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="user.phone">
                            @error('user.phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-user-shield text-gray-400 mr-1"></i>
                                Role <span class="text-red-500">*</span>
                            </label>
                            <select id="role"
                                class="block w-full rounded-md shadow-sm border-gray-300 focus:border-green-500 focus:ring-green-500 @error('user.role') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="user.role"
                                wire:key="role-select-{{ $isEditing ? 'edit' : 'create' }}">
                                <option value="">Select Role</option>
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('user.role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Department -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-building text-gray-400 mr-1"></i>
                                Department <span class="text-red-500">*</span>
                            </label>
                            <select id="department"
                                class="block w-full rounded-md shadow-sm border-gray-300 focus:border-green-500 focus:ring-green-500 @error('user.department') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="user.department">
                                <option value="">Select Department</option>
                                @foreach($departments as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('user.department')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-lock text-gray-400 mr-1"></i>
                                Password 
                                @if($isEditing)
                                    <span class="text-gray-500 text-xs">(Leave blank to keep current)</span>
                                @else
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <input type="password" id="password"
                                class="block w-full rounded-md shadow-sm border-gray-300 focus:border-green-500 focus:ring-green-500 @error('user.password') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="user.password">
                            @error('user.password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-lock text-gray-400 mr-1"></i>
                                Confirm Password
                                @if(!$isEditing)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>
                            <input type="password" id="password_confirmation"
                                class="block w-full rounded-md shadow-sm border-gray-300 focus:border-green-500 focus:ring-green-500 @error('user.password_confirmation') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                wire:model.live="user.password_confirmation">
                            @error('user.password_confirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Status -->
                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="user.is_active" class="rounded border-gray-300 text-green-600 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">
                                    <i class="fas fa-toggle-on text-green-600 mr-1"></i>
                                    Active User
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="mt-6 flex justify-end space-x-3 border-t pt-4">
                        <button type="button" wire:click="closeModal" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                            <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-2"></i>
                            {{ $isEditing ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if($showModal)
<div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }" x-show="show" style="display: none;">
    <!-- Background overlay -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="$wire.closeModal()">
        </div>

        <!-- Modal panel -->
        <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-purple-600 to-indigo-600 border-b border-purple-700">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 bg-white rounded-lg shadow-sm">
                        <i class="fas {{ $isEditing ? 'fa-edit text-purple-600' : 'fa-plus-circle text-indigo-600' }} text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">
                        {{ $isEditing ? __('hr.departments.edit_department') : __('hr.departments.add_department') }}
                    </h3>
                </div>
                <button @click="$wire.closeModal()" type="button" 
                        class="text-white hover:text-gray-200 transition-colors duration-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6 max-h-[calc(100vh-250px)] overflow-y-auto">
                <form wire:submit.prevent="save" class="space-y-5">
                    
                    <!-- Department Name -->
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl p-5 border border-purple-100">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-building text-purple-600 mr-2"></i>
                            <h4 class="text-sm font-semibold text-gray-700">{{ __('hr.departments.department_information') }}</h4>
                        </div>
                        
                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-tag text-gray-400 mr-1"></i>
                                    {{ __('hr.departments.department_name') }} *
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    wire:model="name" 
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm @error('name') border-red-300 @enderror"
                                    placeholder="{{ __('hr.departments.department_name_placeholder') }}">
                                @error('name') 
                                    <p class="mt-1 text-xs text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-align-left text-gray-400 mr-1"></i>
                                    {{ __('hr.departments.description') }}
                                </label>
                                <textarea 
                                    id="description" 
                                    wire:model="description" 
                                    rows="3"
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm @error('description') border-red-300 @enderror"
                                    placeholder="{{ __('hr.departments.description_placeholder') }}"></textarea>
                                @error('description') 
                                    <p class="mt-1 text-xs text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Manager Selection -->
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-5 border border-blue-100">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                            <h4 class="text-sm font-semibold text-gray-700">{{ __('hr.departments.management') }}</h4>
                        </div>
                        
                        <div>
                            <label for="manager_id" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user-shield text-gray-400 mr-1"></i>
                                {{ __('hr.departments.department_manager') }}
                            </label>
                            <div class="relative">
                                <select 
                                    id="manager_id" 
                                    wire:model="manager_id" 
                                    class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm @error('manager_id') border-red-300 @enderror">
                                    <option value="">{{ __('hr.departments.select_manager') }}</option>
                                    @foreach($employees ?? [] as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400 text-sm"></i>
                                </div>
                            </div>
                            @error('manager_id') 
                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-5 border border-green-100">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-toggle-on text-green-600 mr-2"></i>
                            <h4 class="text-sm font-semibold text-gray-700">{{ __('hr.departments.status') }}</h4>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input 
                                    id="is_active" 
                                    wire:model="is_active" 
                                    type="checkbox" 
                                    class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            </div>
                            <div class="ml-3">
                                <label for="is_active" class="text-sm font-medium text-gray-700">
                                    {{ __('hr.departments.is_active') }}
                                </label>
                                <p class="text-xs text-gray-500">{{ __('hr.departments.set_department_active') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Org Chart Upload -->
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-xl p-5 border border-amber-100">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-sitemap text-amber-600 mr-2"></i>
                            <h4 class="text-sm font-semibold text-gray-700">{{ __('hr.departments.org_chart') }}</h4>
                        </div>
                        
                        <div>
                            <label for="org_chart" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file-image text-gray-400 mr-1"></i>
                                {{ __('hr.departments.upload_org_chart') }}
                            </label>
                            
                            @if($existing_org_chart)
                                <div class="mb-3 p-3 bg-white rounded-lg border border-amber-200">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-alt text-amber-500 mr-2"></i>
                                            <span class="text-sm text-gray-700">{{ basename($existing_org_chart) }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ Storage::url($existing_org_chart) }}" target="_blank" 
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye"></i> {{ __('hr.departments.view') }}
                                            </a>
                                            <button type="button" wire:click="removeOrgChart" 
                                                    class="text-red-600 hover:text-red-800 text-sm">
                                                <i class="fas fa-trash"></i> {{ __('hr.departments.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="flex items-center justify-center w-full">
                                <label for="org_chart" class="flex flex-col items-center justify-center w-full h-32 border-2 border-amber-300 border-dashed rounded-lg cursor-pointer bg-amber-50 hover:bg-amber-100 transition-colors">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <i class="fas fa-cloud-upload-alt text-amber-500 text-3xl mb-2"></i>
                                        <p class="mb-2 text-sm text-gray-500">
                                            <span class="font-semibold">{{ __('hr.departments.click_to_upload') }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500">PNG, JPG, PDF (MAX. 5MB)</p>
                                    </div>
                                    <input id="org_chart" type="file" wire:model="org_chart" class="hidden" accept=".png,.jpg,.jpeg,.pdf" />
                                </label>
                            </div>
                            
                            @if($org_chart)
                                <div class="mt-2 p-2 bg-green-50 rounded-lg border border-green-200">
                                    <div class="flex items-center text-green-700 text-sm">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        {{ __('hr.departments.file_selected') }}: {{ $org_chart->getClientOriginalName() }}
                                    </div>
                                </div>
                            @endif
                            
                            <div wire:loading wire:target="org_chart" class="mt-2">
                                <div class="flex items-center text-amber-600 text-sm">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    {{ __('hr.departments.uploading') }}...
                                </div>
                            </div>
                            
                            @error('org_chart') 
                                <p class="mt-1 text-xs text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                </form>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl space-x-3">
                <button 
                    type="button"
                    @click="$wire.closeModal()"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('hr.departments.cancel') }}
                </button>
                <button 
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                    <i class="fas fa-save mr-2" wire:loading.remove></i>
                    <i class="fas fa-spinner fa-spin mr-2" wire:loading></i>
                    <span wire:loading.remove>{{ $isEditing ? __('hr.departments.update') : __('hr.departments.save') }}</span>
                    <span wire:loading>{{ __('hr.departments.saving') }}...</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

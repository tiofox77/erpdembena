<div>
    <!-- JavaScript for Notifications -->
    <script>
        function showNotification(message, type = 'success') {
            if (window.toastr) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000
                };

                toastr[type](message);
            } else {
                alert(message);
            }
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (params) => {
                console.log('Notification event received:', params);
                showNotification(params.message, params.type);
            });
        });
    </script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-cog mr-2 text-gray-600"></i> {{ __('messages.system_settings') }}
                    </h2>

                    <div class="mb-4">
                        <h2 class="text-lg font-medium text-gray-900">{{ __('messages.system_settings') }}</h2>
                        <p class="mt-1 text-sm text-gray-600">{{ __('messages.manage_system_settings') }}</p>
                    </div>

                    <!-- Tabs -->
                    <div class="mb-4 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'general' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('general')" type="button" role="tab">
                                    {{ __('messages.general') }}
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'updates' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('updates')" type="button" role="tab">
                                    {{ __('messages.updates') }}
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'maintenance' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('maintenance')" type="button" role="tab">
                                    {{ __('messages.maintenance') }}
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'requirements' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('requirements')" type="button" role="tab">
                                    System Requirements
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'database' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('database')" type="button" role="tab">
                                    Database Performance
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'backup' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('backup')" type="button" role="tab">
                                    {{ __('messages.database_backup') }}
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab contents -->
                    <div class="p-4 bg-white rounded-lg">
                        <!-- General Settings Tab -->
                        <div class="{{ $activeTab === 'general' ? 'block' : 'hidden' }}" role="tabpanel">
                            <form wire:submit.prevent="saveGeneralSettings">
                                @if($errors->any())
                                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                                        <p class="font-bold flex items-center">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            Please correct the following errors:
                                        </p>
                                        <ul class="mt-2 list-disc list-inside text-sm">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.company_name') }}</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="text"
                                                wire:model.live="company_name"
                                                id="company_name"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                                @error('company_name') border-red-300 text-red-900 placeholder-red-300 @enderror"
                                                placeholder="Company Name">
                                            @error('company_name')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                                </div>
                                            @enderror
                                        </div>
                                        @error('company_name')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="company_logo" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.company_logo') }}</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="file"
                                                wire:model.live="company_logo"
                                                id="company_logo"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                                @error('company_logo') border-red-300 text-red-900 @enderror">
                                            @error('company_logo')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                                </div>
                                            @enderror
                                        </div>
                                        @error('company_logo')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror

                                        <div class="mt-2">
                                            @if ($company_logo)
                                                <img src="{{ $company_logo->temporaryUrl() }}" alt="Preview" class="h-20 w-auto">
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-span-1 md:col-span-2 mt-6">
                                        <h3 class="text-lg font-medium text-gray-900">{{ __('messages.company_details') }}</h3>
                                        <p class="mt-1 text-sm text-gray-600">Informações que aparecerão nos relatórios e documentos.</p>
                                    </div>

                                    <div>
                                        <label for="company_address" class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="text"
                                                wire:model.live="company_address"
                                                id="company_address"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Ex: Rua Principal, 123 - Centro">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="company_phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="text"
                                                wire:model.live="company_phone"
                                                id="company_phone"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Ex: +55 11 1234-5678">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="company_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="email"
                                                wire:model.live="company_email"
                                                id="company_email"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                                @error('company_email') border-red-300 text-red-900 @enderror"
                                                placeholder="Ex: contato@empresa.com">
                                            @error('company_email')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                                </div>
                                            @enderror
                                        </div>
                                        @error('company_email')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="company_website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="text"
                                                wire:model.live="company_website"
                                                id="company_website"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Ex: www.empresa.com">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="company_tax_id" class="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="text"
                                                wire:model.live="company_tax_id"
                                                id="company_tax_id"
                                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Ex: 12.345.678/0001-90">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="app_timezone" class="block text-sm font-medium text-gray-700 mb-1">Time Zone</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <select
                                                wire:model.live="app_timezone"
                                                id="app_timezone"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                                @error('app_timezone') border-red-300 text-red-900 @enderror">
                                                @foreach ($timezones as $key => $name)
                                                    <option value="{{ $key }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('app_timezone')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                                </div>
                                            @enderror
                                        </div>
                                        @error('app_timezone')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="date_format" class="block text-sm font-medium text-gray-700 mb-1">Date Format</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <select
                                                wire:model.live="date_format"
                                                id="date_format"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                                @error('date_format') border-red-300 text-red-900 @enderror">
                                                @foreach ($date_formats as $format => $example)
                                                    <option value="{{ $format }}">{{ $example }} ({{ $format }})</option>
                                                @endforeach
                                            </select>
                                            @error('date_format')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                                </div>
                                            @enderror
                                        </div>
                                        @error('date_format')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <select
                                                wire:model.live="currency"
                                                id="currency"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                                @error('currency') border-red-300 text-red-900 @enderror">
                                                @foreach ($currencies as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('currency')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                                </div>
                                            @enderror
                                        </div>
                                        @error('currency')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="language" class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <select
                                                wire:model.live="language"
                                                id="language"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                                @error('language') border-red-300 text-red-900 @enderror">
                                                @foreach ($languages as $code => $name)
                                                    <option value="{{ $code }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('language')
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                                </div>
                                            @enderror
                                        </div>
                                        @error('language')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-save mr-2"></i> Save General Settings
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Updates Tab -->
                        <div class="{{ $activeTab === 'updates' ? 'block' : 'hidden' }}" role="tabpanel">
                            <div class="mb-6">
                                <div class="bg-gray-50 border border-gray-200 rounded-md p-4 mb-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900">System Version</h3>
                                            <p class="mt-1 text-sm text-gray-600">Current version: v{{ $current_version }}</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ $update_status }}</p>
                                        </div>
                                        <div>
                                            <button
                                                wire:click="checkForUpdates"
                                                wire:loading.attr="disabled"
                                                wire:target="checkForUpdates"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <i class="fas fa-sync-alt mr-2" wire:loading.class="animate-spin" wire:target="checkForUpdates"></i>
                                                <span wire:loading.remove wire:target="checkForUpdates">Check for Updates</span>
                                                <span wire:loading wire:target="checkForUpdates">Checking...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                @if($update_available)
                                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-check-circle text-green-400 h-5 w-5"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-green-800">Update Available: v{{ $latest_version }}</h3>
                                            <div class="mt-2 text-sm text-green-700">
                                                <p>{{ $update_notes['title'] ?? 'New Version Available' }}</p>
                                                <div class="mt-1 whitespace-pre-line">{{ $update_notes['body'] ?? '' }}</div>
                                            </div>
                                            <div class="mt-4">
                                                <div class="flex items-center mb-2">
                                                    <input
                                                        id="backup_before_update"
                                                        wire:model.live="backup_before_update"
                                                        type="checkbox"
                                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                    <label for="backup_before_update" class="ml-2 block text-sm text-gray-700">Create backup before updating</label>
                                                </div>
                                                <button
                                                    wire:click="confirmStartUpdate"
                                                    wire:loading.attr="disabled"
                                                    wire:target="startUpdate"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                    <i class="fas fa-download mr-2"></i>
                                                    <span wire:loading.remove wire:target="startUpdate">Install Update</span>
                                                    <span wire:loading wire:target="startUpdate">Installing...</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($isUpdating)
                                <div class="bg-gray-50 border border-gray-200 rounded-md p-4 mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Update Progress</h3>
                                    <p class="mb-2 text-sm text-gray-600">{{ $update_status }}</p>
                                    <div class="relative pt-1">
                                        <div class="overflow-hidden h-2 text-xs flex rounded bg-indigo-200">
                                            <div style="width: {{ $update_progress }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 transition-all duration-500"></div>
                                        </div>
                                        <div class="text-right mt-1">
                                            <span class="text-xs font-semibold inline-block text-indigo-600">
                                                {{ $update_progress }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <form wire:submit.prevent="saveUpdateSettings" class="bg-white rounded-md">
                                    @if($errors->any())
                                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                                            <p class="font-bold flex items-center">
                                                <i class="fas fa-exclamation-circle mr-2"></i>
                                                Please correct the following errors:
                                            </p>
                                            <ul class="mt-2 list-disc list-inside text-sm">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="mb-6">
                                        <h3 class="text-lg font-medium text-gray-900 mb-4">Update Settings</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label for="github_repository" class="block text-sm font-medium text-gray-700 mb-1">GitHub Repository</label>
                                                <div class="mt-1 relative rounded-md shadow-sm">
                                                    <input
                                                        type="text"
                                                        wire:model.live="github_repository"
                                                        id="github_repository"
                                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm
                                                        @error('github_repository') border-red-300 text-red-900 placeholder-red-300 @enderror"
                                                        placeholder="username/repository">
                                                    @error('github_repository')
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('github_repository')
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @else
                                                    <p class="mt-1 text-xs text-gray-500">Example: laravel/framework</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-6">
                                        <button
                                            type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <i class="fas fa-save mr-2"></i> Save Update Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div class="{{ $activeTab === 'maintenance' ? 'block' : 'hidden' }}" role="tabpanel">
                            <form wire:submit.prevent="saveMaintenanceSettings">
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance & Diagnostics</h3>

                                    <!-- Seeders Section -->
                                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-database text-blue-600"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-base font-medium text-blue-800">Gerenciamento de Seeders</h3>
                                                    <div class="mt-2 text-sm text-blue-700">
                                                        <p>
                                                            Execute seeders para popular o banco de dados com dados de exemplo ou restaurar configurações padrão.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <button 
                                                    type="button"
                                                    wire:click="openSeederModal"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                                    <i class="fas fa-seedling mr-2"></i> Executar Seeders
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-yellow-400 h-5 w-5"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>Enabling maintenance mode will make the application inaccessible to users. Only administrators will be able to access the site.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="flex items-center">
                                            <input
                                                id="maintenance_mode"
                                                wire:model.live="maintenance_mode"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <label for="maintenance_mode" class="ml-2 block text-sm text-gray-700">Enable Maintenance Mode</label>
                                        </div>

                                        <div class="flex items-center">
                                            <input
                                                id="debug_mode"
                                                wire:model.live="debug_mode"
                                                type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <label for="debug_mode" class="ml-2 block text-sm text-gray-700">Enable Debug Mode</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Tools</h3>
                                    
                                    <!-- Gerador de Dados para Teste -->
                                    <div class="bg-purple-50 border border-purple-200 rounded-md p-4 mb-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-database text-purple-600"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-base font-medium text-purple-800">Gerador de Dados para Teste</h3>
                                                    <div class="mt-2 text-sm text-purple-700">
                                                        <p>
                                                            Crie dados de teste para desenvolvimento e demonstração. Permite gerar registros para tabelas específicas com valores aleatórios ou personalizados.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <button 
                                                    type="button"
                                                    wire:click="$dispatch('openDataGenerator')"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                                    <i class="fas fa-table mr-2"></i> Gerar Dados
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <button
                                            type="button"
                                            wire:click="confirmRunArtisanCommand('optimize:clear')"
                                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <i class="fas fa-broom mr-2"></i> Clear Cache
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="confirmRunArtisanCommand('migrate')"
                                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <i class="fas fa-database mr-2"></i> Run Migrations
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="confirmRunArtisanCommand('storage:link')"
                                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <i class="fas fa-link mr-2"></i> Create Storage Link
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-save mr-2"></i> Save Maintenance Settings
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Database Performance Tab -->
                        <div class="{{ $activeTab === 'database' ? 'block' : 'hidden' }}" role="tabpanel">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Database Performance & Statistics</h3>
                                <p class="mt-1 text-sm text-gray-600">Analise o desempenho e tamanho do banco de dados para otimizar a performance do sistema.</p>
                            </div>
                            
                            <div class="flex justify-between items-center mb-4">
                                <button type="button" wire:click="analyzeDatabasePerformance" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 {{ $isLoadingDbStats ? 'opacity-75 cursor-not-allowed' : '' }}" {{ $isLoadingDbStats ? 'disabled' : '' }}>
                                    <div wire:loading wire:target="analyzeDatabasePerformance" class="mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <i class="fas fa-sync-alt mr-2" wire:loading.remove wire:target="analyzeDatabasePerformance"></i>
                                    Refresh Database Stats
                                </button>
                            </div>

                            <!-- Database Size Overview -->
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                                <h4 class="text-md font-medium text-blue-800 mb-3">Database Size Overview</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="bg-white p-4 rounded-md shadow-sm border border-gray-100">
                                        <p class="text-sm text-gray-500">Total Database Size</p>
                                        <p class="text-2xl font-bold text-indigo-600">{{ $this->formatBytes($databaseSize) }}</p>
                                    </div>
                                    <div class="bg-white p-4 rounded-md shadow-sm border border-gray-100">
                                        <p class="text-sm text-gray-500">Total Tables</p>
                                        <p class="text-2xl font-bold text-indigo-600">{{ count($databaseTables) }}</p>
                                    </div>
                                    <div class="bg-white p-4 rounded-md shadow-sm border border-gray-100">
                                        <p class="text-sm text-gray-500">Slow Queries</p>
                                        <p class="text-2xl font-bold text-indigo-600">
                                            @foreach($databaseStatus as $status)
                                                @if($status['name'] === 'Slow_queries')
                                                    {{ $status['value'] }}
                                                    @break
                                                @endif
                                            @endforeach
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Database Tables -->
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-800 mb-3">Top 10 Tables by Size</h4>
                                <div class="overflow-x-auto bg-white rounded-lg border">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table Name</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rows</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Size</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Index Size</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @php $counter = 0; @endphp
                                            @forelse($databaseTables as $table)
                                                @if($counter < 10)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $table->table }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $table->size_mb }} MB</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $table->rows }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $table->data_mb }} MB</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $table->index_mb }} MB</td>
                                                    </tr>
                                                    @php $counter++; @endphp
                                                @endif
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No table data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Database Metrics -->
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-800 mb-3">Database Performance Metrics</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @forelse($databaseStatus as $status)
                                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                                            <h5 class="text-sm font-semibold text-gray-800">{{ $status['name'] }}</h5>
                                            <p class="text-lg font-bold text-indigo-600 mt-1">{{ $status['value'] }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $status['description'] }}</p>
                                        </div>
                                    @empty
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 col-span-2">
                                            <p class="text-sm text-gray-500 text-center">No metrics available</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Slow Queries Log -->
                            @if(!empty($slowQueries))
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-800 mb-3">Slow Queries Log</h4>
                                <div class="bg-white p-4 rounded-lg border border-gray-200">
                                    @if(isset($slowQueries['enabled']) && $slowQueries['enabled'])
                                        <div class="flex items-center text-green-600 mb-2">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            <p class="text-sm">{{ $slowQueries['message'] }}</p>
                                        </div>
                                        <p class="text-sm text-gray-600">Log file: <code class="text-xs bg-gray-100 p-1 rounded">{{ $slowQueries['log_file'] }}</code></p>
                                    @else
                                        <div class="flex items-center text-amber-600 mb-2">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <p class="text-sm">{{ $slowQueries['message'] }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500">Ativar o log de queries lentas permite identificar problemas de desempenho no banco de dados.</p>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Optimization Tips -->
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-6">
                                <h4 class="text-md font-medium text-yellow-800 mb-3">Database Optimization Tips</h4>
                                <ul class="text-sm text-yellow-700 space-y-2 list-disc list-inside">
                                    <li>Tabelas grandes podem impactar a performance do sistema</li>
                                    <li>Considere otimizar consultas frequentes com índices adequados</li>
                                    <li>Realize backups regulares do banco de dados</li>
                                    <li>Execute limpeza periódica de dados temporários ou obsoletos</li>
                                    <li>Monitore o crescimento do banco de dados e planeje expansões conforme necessário</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Database Backup Tab -->
                        <div class="{{ $activeTab === 'backup' ? 'block' : 'hidden' }}" role="tabpanel">
                            <livewire:settings.database-backup />
                        </div>
                        
                        <!-- System Requirements Tab -->
                        <div class="{{ $activeTab === 'requirements' ? 'block' : 'hidden' }}" role="tabpanel">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900">System Requirements Check</h3>
                                <p class="mt-1 text-sm text-gray-600">Verify if your system meets all the requirements for optimal operation.</p>
                            </div>

                            <!-- Status summary -->
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 mr-3 text-green-500 bg-green-100 rounded-lg">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-green-700">Passed</p>
                                            <p class="text-2xl font-bold text-green-700">{{ $requirementsStatus['passed'] }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-yellow-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 mr-3 text-yellow-500 bg-yellow-100 rounded-lg">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-yellow-700">Warnings</p>
                                            <p class="text-2xl font-bold text-yellow-700">{{ $requirementsStatus['warnings'] }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-red-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 mr-3 text-red-500 bg-red-100 rounded-lg">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-red-700">Failed</p>
                                            <p class="text-2xl font-bold text-red-700">{{ $requirementsStatus['failed'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    wire:click="checkSystemRequirements" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="checkSystemRequirements">Refresh Requirements Check</span>
                                    <span wire:loading wire:target="checkSystemRequirements">Checking...</span>
                                </button>
                            </div>

                            <!-- Requirements List -->
                            <div class="overflow-x-auto rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirement</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($systemRequirements as $req)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 flex items-center">
                                                        @if($req['is_critical'])
                                                            <span class="w-2 h-2 bg-red-400 rounded-full mr-2" title="Critical requirement"></span>
                                                        @endif
                                                        {{ $req['name'] }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-500">{{ $req['description'] }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-500">{{ $req['result'] }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($req['status'] === 'passed')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Passed
                                                        </span>
                                                    @elseif($req['status'] === 'warning')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            Warning
                                                        </span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            Failed
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    <div wire:loading wire:target="checkSystemRequirements" class="flex justify-center items-center">
                                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                        <span>Checking system requirements...</span>
                                                    </div>
                                                    <div wire:loading.remove wire:target="checkSystemRequirements">
                                                        No requirement checks performed yet. Click the "Refresh Requirements Check" button to begin.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($requirementsStatus['failed'] > 0)
                                <div class="mt-6 p-4 bg-red-50 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 00-1.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">
                                                Your system does not meet all requirements
                                            </h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <p>
                                                    Your system does not meet all the requirements needed for the application to run properly. Please fix the issues marked as "Failed" to ensure optimal performance and functionality.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($requirementsStatus['warnings'] > 0)
                                <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800">
                                                Your system meets the minimum requirements but has warnings
                                            </h3>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <p>
                                                    Your system meets the critical requirements, but has some warnings that might affect performance or certain features. Consider addressing these for optimal operation.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($requirementsStatus['passed'] > 0)
                                <div class="mt-6 p-4 bg-green-50 rounded-lg">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-green-800">
                                                Your system meets all requirements
                                            </h3>
                                            <div class="mt-2 text-sm text-green-700">
                                                <p>
                                                    All system requirements are met. Your system is properly configured for optimal performance and functionality.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    @if($showConfirmModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                    Confirmation Required
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeConfirmModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="bg-yellow-50 p-4 rounded-md mb-4">
                <p class="text-sm text-yellow-700">{{ $confirmMessage }}</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    wire:click="closeConfirmModal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button
                    type="button"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                    wire:click="processConfirmedAction">
                    <i class="fas fa-check mr-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modal de Seeders -->
    <div x-data="{ open: @entangle('showSeederModal') }" 
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
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-seedling mr-2 animate-pulse"></i>
                        Executar Seeders
                    </h3>
                    <button type="button" wire:click="closeSeederModal" class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110 hover:rotate-90">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Corpo do modal -->
                <div class="p-6">
                    <div class="mb-4">
                        <label for="selectedSeeder" class="block text-sm font-medium text-gray-700 mb-1">Selecione o Seeder</label>
                        <select
                            wire:model.live="selectedSeeder"
                            id="selectedSeeder"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 sm:text-sm bg-white"
                            @if($runningSeeder) disabled @endif
                        >
                            <option value="">Selecione um seeder</option>
                            @foreach($availableSeeders as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Selecione 'Todos os Seeders' para executar todos os seeders configurados no sistema, ou um seeder específico.
                        </p>
                    </div>
                    
                    @if(!empty($seederOutput))
                        <div class="my-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Saída do Seeder</label>
                            <pre class="bg-gray-800 text-white p-4 rounded-md overflow-auto max-h-60 text-sm font-mono">{{ $seederOutput }}</pre>
                        </div>
                    @endif
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Atenção:</strong> Executar seeders pode modificar ou adicionar dados ao banco de dados.
                                    Recomenda-se fazer um backup antes de prosseguir.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé com botões de ação -->
                <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3 border-t border-gray-200">
                    <button type="button" wire:click="closeSeederModal" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button type="button" 
                        wire:click="runSeeder" 
                        wire:loading.attr="disabled" 
                        @if($runningSeeder || empty($selectedSeeder)) disabled @endif
                        class="inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 ease-in-out transform hover:scale-105 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="runSeeder">
                            <i class="fas fa-play-circle mr-2"></i>
                            Executar Seeder
                        </span>
                        <span wire:loading wire:target="runSeeder" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Executando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Integração com o Gerador de Dados -->
    <livewire:settings.data-generator />
</div>
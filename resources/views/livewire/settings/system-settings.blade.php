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
                        <i class="fas fa-cog mr-2 text-gray-600"></i> System Settings
                    </h2>

                    <!-- Tabs -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                wire:click="setActiveTab('general')"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-wrench mr-2"></i> General
                            </button>
                            <button
                                wire:click="setActiveTab('updates')"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'updates' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-sync-alt mr-2"></i> Updates
                            </button>
                            <button
                                wire:click="setActiveTab('maintenance')"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'maintenance' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                <i class="fas fa-tools mr-2"></i> Maintenance
                            </button>
                        </nav>
                    </div>

                    <!-- General Settings Tab -->
                    <div class="{{ $activeTab === 'general' ? '' : 'hidden' }}">
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
                                    <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">Company Name</label>
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
                                    <label for="company_logo" class="block text-sm font-medium text-gray-700 mb-1">Company Logo</label>
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
                    <div class="{{ $activeTab === 'updates' ? '' : 'hidden' }}">
                        <div class="mb-6">
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4 mb-4">
                                <h3 class="text-lg font-semibold">System Version</h3>
                                <p>Current version: <span class="font-semibold">v{{ $current_version }}</span></p>

                                @if($update_status)
                                    <div class="mt-2 text-sm text-gray-600">{{ $update_status }}</div>

                                    @if($isUpdating)
                                        <div class="mt-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $update_progress }}%"></div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="mt-4">
                                <button type="button" wire:click="checkForUpdates" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="checkForUpdates">
                                        <i class="fas fa-sync-alt mr-2"></i> Check for Updates
                                    </span>
                                    <span wire:loading wire:target="checkForUpdates">
                                        <i class="fas fa-spinner fa-spin mr-2"></i> Checking...
                                    </span>
                                </button>

                                @if($update_available)
                                    <button type="button" wire:click="confirmStartUpdate" wire:loading.attr="disabled" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                                        <i class="fas fa-download mr-2"></i> Update to v{{ $latest_version }}
                                    </button>
                                @endif

                                <!-- Botão para teste de migração -->
                                <button type="button" wire:click="testUpdateWithMigration" wire:loading.attr="disabled" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="testUpdateWithMigration">
                                        <i class="fas fa-flask mr-2"></i> Testar Migração
                                    </span>
                                    <span wire:loading wire:target="testUpdateWithMigration">
                                        <i class="fas fa-spinner fa-spin mr-2"></i> Testando...
                                    </span>
                                </button>
                            </div>
                        </div>

                        <form wire:submit.prevent="saveUpdateSettings">
                            @if($errors->any())
                                <div class="mb-4 p-4 rounded-md bg-red-50">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="bg-white shadow rounded-lg p-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-5">Update Settings</h3>

                                <div class="mb-4">
                                    <label for="github_repository" class="block text-sm font-medium text-gray-700 mb-1">GitHub Repository</label>
                                    <input type="text" id="github_repository" wire:model="github_repository" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="username/repository">
                                    <p class="mt-1 text-xs text-gray-500">Example: laravel/framework</p>
                                    @error('github_repository')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="backup_before_update" class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                                        <span class="ml-2 text-sm text-gray-600">Backup database and files before update</span>
                                    </label>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Save Update Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Maintenance Tab -->
                    <div class="{{ $activeTab === 'maintenance' ? '' : 'hidden' }}">
                        <form wire:submit.prevent="saveMaintenanceSettings">
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Maintenance & Diagnostics</h3>

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
</div>

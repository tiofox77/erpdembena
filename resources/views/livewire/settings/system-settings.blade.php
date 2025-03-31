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

                    <div class="mb-4">
                        <h2 class="text-lg font-medium text-gray-900">System Settings</h2>
                        <p class="mt-1 text-sm text-gray-600">Manage your system configuration and settings.</p>
                    </div>

                    <!-- Tabs -->
                    <div class="mb-4 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button 
                                    class="inline-flex items-center p-4 border-b-2 rounded-t-lg {{ $activeTab === 'general' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('general')" 
                                    type="button" 
                                    role="tab"
                                    x-data="{ tooltip: false }"
                                    @mouseenter="tooltip = true"
                                    @mouseleave="tooltip = false"
                                >
                                    <i class="fas fa-sliders-h mr-2"></i> General
                                    <div x-cloak x-show="tooltip" class="absolute bg-gray-800 text-white text-xs rounded py-1 px-2 mt-16 z-50 w-48">
                                        <div class="flex items-center mb-1 font-semibold">
                                            <i class="fas fa-info-circle mr-1.5"></i> General Settings
                                        </div>
                                        <p>Configure basic system properties like company name, timezone, date format and regional preferences.</p>
                                    </div>
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button 
                                    class="inline-flex items-center p-4 border-b-2 rounded-t-lg {{ $activeTab === 'updates' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('updates')" 
                                    type="button" 
                                    role="tab"
                                    x-data="{ tooltip: false }"
                                    @mouseenter="tooltip = true"
                                    @mouseleave="tooltip = false"
                                >
                                    <i class="fas fa-sync-alt mr-2"></i> Updates
                                    <div x-cloak x-show="tooltip" class="absolute bg-gray-800 text-white text-xs rounded py-1 px-2 mt-16 z-50 w-48">
                                        <div class="flex items-center mb-1 font-semibold">
                                            <i class="fas fa-info-circle mr-1.5"></i> System Updates
                                        </div>
                                        <p>Check for new versions, manage application updates and track version history of the system.</p>
                                    </div>
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button 
                                    class="inline-flex items-center p-4 border-b-2 rounded-t-lg {{ $activeTab === 'maintenance' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('maintenance')" 
                                    type="button" 
                                    role="tab"
                                    x-data="{ tooltip: false }"
                                    @mouseenter="tooltip = true"
                                    @mouseleave="tooltip = false"
                                >
                                    <i class="fas fa-tools mr-2"></i> Maintenance
                                    <div x-cloak x-show="tooltip" class="absolute bg-gray-800 text-white text-xs rounded py-1 px-2 mt-16 z-50 w-48">
                                        <div class="flex items-center mb-1 font-semibold">
                                            <i class="fas fa-info-circle mr-1.5"></i> System Maintenance
                                        </div>
                                        <p>Perform maintenance tasks like cache clearing, debugging, database optimizations and backups.</p>
                                    </div>
                                </button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button 
                                    class="inline-flex items-center p-4 border-b-2 rounded-t-lg {{ $activeTab === 'requirements' ? 'border-indigo-500 text-indigo-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}"
                                    wire:click="setActiveTab('requirements')" 
                                    type="button" 
                                    role="tab"
                                    x-data="{ tooltip: false }"
                                    @mouseenter="tooltip = true"
                                    @mouseleave="tooltip = false"
                                >
                                    <i class="fas fa-check-square mr-2"></i> Requirements
                                    <div x-cloak x-show="tooltip" class="absolute bg-gray-800 text-white text-xs rounded py-1 px-2 mt-16 z-50 w-48">
                                        <div class="flex items-center mb-1 font-semibold">
                                            <i class="fas fa-info-circle mr-1.5"></i> System Requirements
                                        </div>
                                        <p>Check if your server meets all necessary requirements for optimal system performance.</p>
                                    </div>
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

                                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                    <div x-data="{ tooltip: false }" class="sm:col-span-2 relative">
                                        <label for="company_name" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-building mr-2 text-indigo-500"></i> Company Name 
                                            <span class="text-red-500 ml-1">*</span>
                                            <span 
                                                @mouseenter="tooltip = true" 
                                                @mouseleave="tooltip = false"
                                                class="ml-1.5 text-gray-400 cursor-help">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-6 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                            <div class="font-semibold mb-1">Company Name</div>
                                            <p>The name of your organization that will appear throughout the application, including reports and emails.</p>
                                        </div>
                                        <div class="mt-1">
                                            <input
                                                type="text"
                                                id="company_name"
                                                wire:model.live="company_name"
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('company_name') border-red-300 text-red-900 @enderror"
                                                placeholder="Enter your company name"
                                            >
                                            @error('company_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div x-data="{ tooltip: false }" class="sm:col-span-2 relative">
                                        <label for="company_logo" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-image mr-2 text-indigo-500"></i> Company Logo
                                            <span 
                                                @mouseenter="tooltip = true" 
                                                @mouseleave="tooltip = false"
                                                class="ml-1.5 text-gray-400 cursor-help">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-6 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                            <div class="font-semibold mb-1">Company Logo</div>
                                            <p>Upload your organization's logo that will appear in reports, the application header, and exported documents.</p>
                                        </div>
                                        <div class="mt-1 flex items-center">
                                            <div
                                                class="flex-shrink-0 h-16 w-32 bg-gray-100 rounded-md overflow-hidden mr-4 border border-gray-200"
                                            >
                                                @if($company_logo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)
                                                    <img
                                                        src="{{ $company_logo->temporaryUrl() }}"
                                                        alt="Company Logo Preview"
                                                        class="h-16 w-auto object-contain"
                                                    >
                                                @else
                                                    <img
                                                        src="{{ Storage::disk('public')->url(\App\Models\Setting::get('company_logo', 'default-logo.png')) }}"
                                                        alt="Company Logo"
                                                        class="h-16 w-auto object-contain"
                                                    >
                                                @endif
                                            </div>
                                            <div>
                                                <label for="company_logo_upload" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 cursor-pointer">
                                                    <i class="fas fa-upload mr-2"></i> Browse...
                                                    <input
                                                        id="company_logo_upload"
                                                        type="file"
                                                        wire:model.live="company_logo"
                                                        class="sr-only"
                                                        accept="image/*"
                                                    >
                                                </label>
                                                <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF up to 1MB</p>
                                                @error('company_logo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div x-data="{ tooltip: false }" class="relative">
                                        <label for="app_timezone" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-globe-americas mr-2 text-indigo-500"></i> Time Zone 
                                            <span class="text-red-500 ml-1">*</span>
                                            <span 
                                                @mouseenter="tooltip = true" 
                                                @mouseleave="tooltip = false"
                                                class="ml-1.5 text-gray-400 cursor-help">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-6 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                            <div class="font-semibold mb-1">Time Zone</div>
                                            <p>Set the default time zone for the application. All dates and times will be displayed according to this setting.</p>
                                        </div>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <select
                                                wire:model.live="app_timezone"
                                                id="app_timezone"
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('app_timezone') border-red-300 text-red-900 @enderror"
                                            >
                                                @foreach(timezone_identifiers_list() as $timezone)
                                                    <option value="{{ $timezone }}">{{ $timezone }}</option>
                                                @endforeach
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        @error('app_timezone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div x-data="{ tooltip: false }" class="relative">
                                        <label for="date_format" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-calendar-alt mr-2 text-indigo-500"></i> Date Format
                                            <span class="text-red-500 ml-1">*</span>
                                            <span 
                                                @mouseenter="tooltip = true" 
                                                @mouseleave="tooltip = false"
                                                class="ml-1.5 text-gray-400 cursor-help">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-6 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                            <div class="font-semibold mb-1">Date Format</div>
                                            <p>Choose how dates should be displayed throughout the application. This affects reports, interfaces and exported data.</p>
                                        </div>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <select
                                                wire:model.live="date_format"
                                                id="date_format"
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('date_format') border-red-300 text-red-900 @enderror"
                                            >
                                                <option value="m/d/Y">MM/DD/YYYY (e.g., 03/25/2023)</option>
                                                <option value="d/m/Y">DD/MM/YYYY (e.g., 25/03/2023)</option>
                                                <option value="Y-m-d">YYYY-MM-DD (e.g., 2023-03-25)</option>
                                                <option value="d.m.Y">DD.MM.YYYY (e.g., 25.03.2023)</option>
                                                <option value="d-m-Y">DD-MM-YYYY (e.g., 25-03-2023)</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        @error('date_format') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div x-data="{ tooltip: false }" class="relative">
                                        <label for="currency" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-dollar-sign mr-2 text-indigo-500"></i> Currency
                                            <span class="text-red-500 ml-1">*</span>
                                            <span 
                                                @mouseenter="tooltip = true" 
                                                @mouseleave="tooltip = false"
                                                class="ml-1.5 text-gray-400 cursor-help">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-6 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                            <div class="font-semibold mb-1">Currency</div>
                                            <p>Set the default currency for financial data, costs, pricing and reports. Use the ISO 3-letter currency code.</p>
                                        </div>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <select
                                                wire:model.live="currency"
                                                id="currency"
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('currency') border-red-300 text-red-900 @enderror"
                                            >
                                                <option value="USD">USD - US Dollar</option>
                                                <option value="EUR">EUR - Euro</option>
                                                <option value="GBP">GBP - British Pound</option>
                                                <option value="BRL">BRL - Brazilian Real</option>
                                                <option value="CAD">CAD - Canadian Dollar</option>
                                                <option value="AUD">AUD - Australian Dollar</option>
                                                <option value="JPY">JPY - Japanese Yen</option>
                                                <option value="CNY">CNY - Chinese Yuan</option>
                                                <option value="INR">INR - Indian Rupee</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        @error('currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div x-data="{ tooltip: false }" class="relative">
                                        <label for="language" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                            <i class="fas fa-language mr-2 text-indigo-500"></i> Language
                                            <span class="text-red-500 ml-1">*</span>
                                            <span 
                                                @mouseenter="tooltip = true" 
                                                @mouseleave="tooltip = false"
                                                class="ml-1.5 text-gray-400 cursor-help">
                                                <i class="fas fa-info-circle"></i>
                                            </span>
                                        </label>
                                        <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-6 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                            <div class="font-semibold mb-1">Language</div>
                                            <p>Select the default language for the application interface, messages and system-generated content.</p>
                                        </div>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <select
                                                wire:model.live="language"
                                                id="language"
                                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('language') border-red-300 text-red-900 @enderror"
                                            >
                                                <option value="en">English</option>
                                                <option value="pt">Portuguese</option>
                                                <option value="es">Spanish</option>
                                                <option value="fr">French</option>
                                                <option value="de">German</option>
                                                <option value="it">Italian</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i class="fas fa-chevron-down text-gray-400"></i>
                                            </div>
                                        </div>
                                        @error('language') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        wire:loading.attr="disabled"
                                        wire:target="saveGeneralSettings"
                                    >
                                        <span wire:loading.remove wire:target="saveGeneralSettings">
                                            <i class="fas fa-save mr-2"></i> Save Settings
                                        </span>
                                        <span wire:loading wire:target="saveGeneralSettings">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Updates Tab -->
                        <div class="{{ $activeTab === 'updates' ? 'block' : 'hidden' }}" role="tabpanel">
                            <div class="mb-6">
                                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                                <i class="fas fa-code-branch mr-2 text-indigo-500"></i>
                                                System Version
                                            </h3>
                                            <div class="mt-2 flex items-start">
                                                <div class="mr-6">
                                                    <p class="text-sm text-gray-500 flex items-center">
                                                        <i class="fas fa-tag text-gray-400 mr-2"></i>
                                                        Current Version:
                                                    </p>
                                                    <p class="text-xl font-semibold text-gray-800">{{ $current_version }}</p>
                                                </div>
                                                
                                                @if($update_available)
                                                <div>
                                                    <p class="text-sm text-gray-500 flex items-center">
                                                        <i class="fas fa-arrow-circle-up text-green-500 mr-2"></i>
                                                        Available Version:
                                                    </p>
                                                    <p class="text-xl font-semibold text-green-600">{{ $latest_version }}</p>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="flex-shrink-0">
                                            @if($update_available)
                                                <button
                                                    wire:click="startUpdate"
                                                    wire:loading.attr="disabled"
                                                    wire:target="startUpdate"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    x-data="{ tooltip: false }"
                                                    @mouseenter="tooltip = true"
                                                    @mouseleave="tooltip = false"
                                                >
                                                    <span wire:loading.remove wire:target="startUpdate">
                                                        <i class="fas fa-arrow-circle-up mr-2"></i> Update Now
                                                    </span>
                                                    <span wire:loading wire:target="startUpdate">
                                                        <i class="fas fa-spinner fa-spin mr-2"></i> Updating...
                                                    </span>
                                                    
                                                    <div x-cloak x-show="tooltip" class="absolute mt-10 right-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-56">
                                                        <div class="font-semibold mb-1">System Update</div>
                                                        <p>Start the update process to version {{ $latest_version }}. A backup of your system will be made before updating.</p>
                                                    </div>
                                                </button>
                                            @else
                                                <button
                                                    wire:click="checkForUpdates(false)"
                                                    wire:loading.attr="disabled"
                                                    wire:target="checkForUpdates"
                                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    x-data="{ tooltip: false }"
                                                    @mouseenter="tooltip = true"
                                                    @mouseleave="tooltip = false"
                                                >
                                                    <span wire:loading.remove wire:target="checkForUpdates">
                                                        <i class="fas fa-sync-alt mr-2"></i> Check for Updates
                                                    </span>
                                                    <span wire:loading wire:target="checkForUpdates">
                                                        <i class="fas fa-spinner fa-spin mr-2"></i> Checking...
                                                    </span>
                                                    
                                                    <div x-cloak x-show="tooltip" class="absolute mt-10 right-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-56">
                                                        <div class="font-semibold mb-1">Check for Updates</div>
                                                        <p>Connect to the repository to check if a newer version of the system is available.</p>
                                                    </div>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 text-sm">
                                        <span class="text-gray-500">Last checked: </span>
                                        <span class="text-gray-700">{{ $lastChecked ?? 'Never' }}</span>
                                    </div>
                                </div>
                                
                                @if($update_available)
                                <div class="bg-white border border-gray-200 rounded-lg p-5 mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center mb-3">
                                        <i class="fas fa-clipboard-list mr-2 text-indigo-500"></i>
                                        Update Notes
                                    </h3>
                                    
                                    @if(count($update_notes) > 0)
                                        <div class="overflow-y-auto max-h-48 p-3 bg-gray-50 rounded-md">
                                            @foreach($update_notes as $note)
                                                <div class="mb-3 pb-3 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0">
                                                    <h4 class="font-medium text-gray-800">{{ $note['title'] ?? 'Update Note' }}</h4>
                                                    <p class="text-sm text-gray-600 mt-1">{{ $note['description'] ?? 'No description available.' }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-600 italic">
                                            No release notes available for this update.
                                        </div>
                                    @endif
                                </div>
                                @endif
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-5 mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center mb-3">
                                        <i class="fas fa-chart-line mr-2 text-indigo-500"></i>
                                        Update Progress
                                    </h3>
                                    
                                    <div class="text-sm text-gray-700 mb-2">{{ $update_status ?: 'No update in progress.' }}</div>
                                    
                                    <div class="relative pt-1">
                                        <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                            <div
                                                style="width: {{ $update_progress }}%"
                                                class="transition-all duration-300 shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"
                                            ></div>
                                        </div>
                                        <div class="text-right mt-1">
                                            <span class="text-xs font-semibold inline-block text-indigo-600">
                                                {{ $update_progress }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <form wire:submit.prevent="saveUpdateSettings" class="bg-white border border-gray-200 rounded-lg p-5">
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
                                    
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center mb-4">
                                        <i class="fas fa-cog mr-2 text-indigo-500"></i>
                                        Update Settings
                                    </h3>
                                    
                                    <div class="mb-4">
                                        <div x-data="{ tooltip: false }" class="relative">
                                            <label for="github_repository" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                                <i class="fab fa-github mr-2 text-gray-700"></i> 
                                                GitHub Repository 
                                                <span class="text-red-500 ml-1">*</span>
                                                <span 
                                                    @mouseenter="tooltip = true" 
                                                    @mouseleave="tooltip = false"
                                                    class="ml-1.5 text-gray-400 cursor-help">
                                                    <i class="fas fa-info-circle"></i>
                                                </span>
                                            </label>
                                            <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-6 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                <div class="font-semibold mb-1">GitHub Repository</div>
                                                <p>The GitHub repository where system updates are published. Format: username/repository</p>
                                            </div>
                                            <div class="mt-1 relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <i class="fab fa-github text-gray-400"></i>
                                                </div>
                                                <input
                                                    type="text"
                                                    id="github_repository"
                                                    wire:model.live="github_repository"
                                                    class="pl-10 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('github_repository') border-red-300 text-red-900 @enderror"
                                                    placeholder="username/repository"
                                                >
                                            </div>
                                            @error('github_repository') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                            <p class="mt-1 text-xs text-gray-500">Example: tiofox77/erpdembena</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div x-data="{ tooltip: false }" class="relative flex items-start">
                                            <div class="flex items-center h-5">
                                                <input
                                                    type="checkbox"
                                                    id="backup_before_update"
                                                    wire:model.live="backup_before_update"
                                                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                >
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="backup_before_update" class="font-medium text-gray-700 flex items-center">
                                                    Create backup before updating
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                </label>
                                                <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                    <div class="font-semibold mb-1">Backup Before Update</div>
                                                    <p>When enabled, a system backup will be created automatically before installing any updates.</p>
                                                </div>
                                                <p class="text-gray-500">
                                                    Recommended for safety. This will create a database and file backup.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-end">
                                        <button
                                            type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            wire:loading.attr="disabled"
                                            wire:target="saveUpdateSettings"
                                        >
                                            <span wire:loading.remove wire:target="saveUpdateSettings">
                                                <i class="fas fa-save mr-2"></i> Save Settings
                                            </span>
                                            <span wire:loading wire:target="saveUpdateSettings">
                                                <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div class="{{ $activeTab === 'maintenance' ? 'block' : 'hidden' }}" role="tabpanel">
                            <form wire:submit.prevent="saveMaintenanceSettings">
                                <div class="space-y-6">
                                    <div class="bg-white border border-gray-200 rounded-lg p-5">
                                        <h3 class="text-lg font-medium text-gray-900 flex items-center mb-4">
                                            <i class="fas fa-tools mr-2 text-indigo-500"></i>
                                            Maintenance & Diagnostics
                                        </h3>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div x-data="{ tooltip: false }" class="relative flex items-start col-span-full">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        id="maintenance_mode"
                                                        wire:model.live="maintenance_mode"
                                                        type="checkbox"
                                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="maintenance_mode" class="font-medium text-gray-700 flex items-center">
                                                        Maintenance Mode
                                                        <span 
                                                            @mouseenter="tooltip = true" 
                                                            @mouseleave="tooltip = false"
                                                            class="ml-1.5 text-gray-400 cursor-help">
                                                            <i class="fas fa-info-circle"></i>
                                                        </span>
                                                    </label>
                                                    <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                        <div class="font-semibold mb-1">Maintenance Mode</div>
                                                        <p>When enabled, the application will display a maintenance page to all users except administrators. Use during updates or maintenance periods.</p>
                                                    </div>
                                                    <p class="text-gray-500">
                                                        Put the system in maintenance mode. Only administrators can access the site.
                                                    </p>
                                                </div>
                                            </div>

                                            <div x-data="{ tooltip: false }" class="relative flex items-start col-span-full">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        id="debug_mode"
                                                        wire:model.live="debug_mode"
                                                        type="checkbox"
                                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="debug_mode" class="font-medium text-gray-700 flex items-center">
                                                        Debug Mode
                                                        <span 
                                                            @mouseenter="tooltip = true" 
                                                            @mouseleave="tooltip = false"
                                                            class="ml-1.5 text-gray-400 cursor-help">
                                                            <i class="fas fa-info-circle"></i>
                                                        </span>
                                                    </label>
                                                    <div x-cloak x-show="tooltip" class="absolute top-0 right-0 mt-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                        <div class="font-semibold mb-1">Debug Mode</div>
                                                        <p>Enables detailed error reporting. Only enable in development environments. When disabled, users see generic error messages.</p>
                                                    </div>
                                                    <p class="text-gray-500">
                                                        Enable detailed error reporting. Recommended only for development environments.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white border border-gray-200 rounded-lg p-5">
                                        <h3 class="text-lg font-medium text-gray-900 flex items-center mb-4">
                                            <i class="fas fa-broom mr-2 text-indigo-500"></i>
                                            System Cleanup
                                        </h3>
                                        
                                        <div class="grid grid-cols-1 gap-4">
                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between p-4 bg-gray-50 rounded-lg">
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900 flex items-center" x-data="{ tooltip: false }">
                                                        Clear Cache
                                                        <span 
                                                            @mouseenter="tooltip = true" 
                                                            @mouseleave="tooltip = false"
                                                            class="ml-1.5 text-gray-400 cursor-help">
                                                            <i class="fas fa-info-circle"></i>
                                                        </span>
                                                        <div x-cloak x-show="tooltip" class="absolute mt-8 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                            <div class="font-semibold mb-1">Clear Application Cache</div>
                                                            <p>Removes cached application data, views, and routes. This can help resolve display issues after updates.</p>
                                                        </div>
                                                    </h4>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Clear application cache, route cache, view cache and config cache.
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('clearCache')"
                                                    class="mt-3 md:mt-0 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand"
                                                >
                                                    <span wire:loading.remove wire:target="runArtisanCommand('clearCache')">
                                                        <i class="fas fa-sync-alt mr-2"></i> Clear Cache
                                                    </span>
                                                    <span wire:loading wire:target="runArtisanCommand('clearCache')">
                                                        <i class="fas fa-spinner fa-spin mr-2"></i> Clearing...
                                                    </span>
                                                </button>
                                            </div>
                                            
                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between p-4 bg-gray-50 rounded-lg">
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900 flex items-center" x-data="{ tooltip: false }">
                                                        Optimize Database
                                                        <span 
                                                            @mouseenter="tooltip = true" 
                                                            @mouseleave="tooltip = false"
                                                            class="ml-1.5 text-gray-400 cursor-help">
                                                            <i class="fas fa-info-circle"></i>
                                                        </span>
                                                        <div x-cloak x-show="tooltip" class="absolute mt-8 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                            <div class="font-semibold mb-1">Database Optimization</div>
                                                            <p>Performs database optimization operations like reindexing and running migrations. Can improve system performance.</p>
                                                        </div>
                                                    </h4>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Run database migrations and optimize tables.
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('optimizeDb')"
                                                    class="mt-3 md:mt-0 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand"
                                                >
                                                    <span wire:loading.remove wire:target="runArtisanCommand('optimizeDb')">
                                                        <i class="fas fa-database mr-2"></i> Optimize
                                                    </span>
                                                    <span wire:loading wire:target="runArtisanCommand('optimizeDb')">
                                                        <i class="fas fa-spinner fa-spin mr-2"></i> Optimizing...
                                                    </span>
                                                </button>
                                            </div>
                                            
                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between p-4 bg-gray-50 rounded-lg">
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900 flex items-center" x-data="{ tooltip: false }">
                                                        Clear Old Logs
                                                        <span 
                                                            @mouseenter="tooltip = true" 
                                                            @mouseleave="tooltip = false"
                                                            class="ml-1.5 text-gray-400 cursor-help">
                                                            <i class="fas fa-info-circle"></i>
                                                        </span>
                                                        <div x-cloak x-show="tooltip" class="absolute mt-8 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                            <div class="font-semibold mb-1">Clear Log Files</div>
                                                            <p>Removes old log files from the system to free up disk space. This will not affect current system operations.</p>
                                                        </div>
                                                    </h4>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Remove old log files to free up disk space.
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('clearLogs')"
                                                    class="mt-3 md:mt-0 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand"
                                                >
                                                    <span wire:loading.remove wire:target="runArtisanCommand('clearLogs')">
                                                        <i class="fas fa-trash-alt mr-2"></i> Clear Logs
                                                    </span>
                                                    <span wire:loading wire:target="runArtisanCommand('clearLogs')">
                                                        <i class="fas fa-spinner fa-spin mr-2"></i> Clearing...
                                                    </span>
                                                </button>
                                            </div>
                                            
                                            <div class="flex flex-col md:flex-row md:items-center md:justify-between p-4 bg-gray-50 rounded-lg">
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900 flex items-center" x-data="{ tooltip: false }">
                                                        Create System Backup
                                                        <span 
                                                            @mouseenter="tooltip = true" 
                                                            @mouseleave="tooltip = false"
                                                            class="ml-1.5 text-gray-400 cursor-help">
                                                            <i class="fas fa-info-circle"></i>
                                                        </span>
                                                        <div x-cloak x-show="tooltip" class="absolute mt-8 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                            <div class="font-semibold mb-1">System Backup</div>
                                                            <p>Creates a complete backup of the application including database and files. Use before major changes or upgrades.</p>
                                                        </div>
                                                    </h4>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Create a backup of the database and application files.
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('backup')"
                                                    class="mt-3 md:mt-0 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand"
                                                >
                                                    <span wire:loading.remove wire:target="runArtisanCommand('backup')">
                                                        <i class="fas fa-save mr-2"></i> Create Backup
                                                    </span>
                                                    <span wire:loading wire:target="runArtisanCommand('backup')">
                                                        <i class="fas fa-spinner fa-spin mr-2"></i> Backing up...
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        wire:loading.attr="disabled"
                                        wire:target="saveMaintenanceSettings"
                                    >
                                        <span wire:loading.remove wire:target="saveMaintenanceSettings">
                                            <i class="fas fa-save mr-2"></i> Save Settings
                                        </span>
                                        <span wire:loading wire:target="saveMaintenanceSettings">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- System Requirements Tab -->
                        <div class="{{ $activeTab === 'requirements' ? 'block' : 'hidden' }}" role="tabpanel">
                            <div class="space-y-6">
                                <div class="bg-white border border-gray-200 rounded-lg p-5">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                            <i class="fas fa-server mr-2 text-indigo-500"></i>
                                            System Requirements
                                        </h3>
                                        <button
                                            type="button"
                                            wire:click="checkRequirements"
                                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            wire:loading.attr="disabled"
                                            wire:target="checkRequirements"
                                            x-data="{ tooltip: false }"
                                            @mouseenter="tooltip = true"
                                            @mouseleave="tooltip = false"
                                        >
                                            <span wire:loading.remove wire:target="checkRequirements">
                                                <i class="fas fa-sync-alt mr-2"></i> Check Again
                                            </span>
                                            <span wire:loading wire:target="checkRequirements">
                                                <i class="fas fa-spinner fa-spin mr-2"></i> Checking...
                                            </span>
                                            <div x-cloak x-show="tooltip" class="absolute mt-8 right-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-56">
                                                <div class="font-semibold mb-1">Recheck Requirements</div>
                                                <p>Refresh the system requirements check to ensure all values are current.</p>
                                            </div>
                                        </button>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 mb-4">
                                        Verify if your server meets the necessary requirements to run the application properly.
                                    </p>
                                    
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <div class="grid grid-cols-5 bg-gray-50 text-xs font-medium text-gray-700 border-b border-gray-200">
                                            <div class="py-2 px-4 col-span-2">Requirement</div>
                                            <div class="py-2 px-4">Required</div>
                                            <div class="py-2 px-4">Current</div>
                                            <div class="py-2 px-4">Status</div>
                                        </div>
                                        
                                        <div class="divide-y divide-gray-200 text-sm">
                                            <!-- PHP Version -->
                                            <div class="grid grid-cols-5 hover:bg-gray-50" x-data="{ tooltip: false }">
                                                <div class="py-3 px-4 col-span-2 font-medium flex items-center">
                                                    <span>PHP Version</span>
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                    <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                        <div class="font-semibold mb-1">PHP Version</div>
                                                        <p>Minimum PHP version required to run all application features securely and efficiently.</p>
                                                    </div>
                                                </div>
                                                <div class="py-3 px-4">{{ $requirements['php']['required'] ?? '7.4.0' }}</div>
                                                <div class="py-3 px-4">{{ $requirements['php']['current'] ?? phpversion() }}</div>
                                                <div class="py-3 px-4">
                                                    @if(isset($requirements['php']['status']) && $requirements['php']['status'])
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check mr-1"></i> OK
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <i class="fas fa-times mr-1"></i> Failed
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- MySQL Version -->
                                            <div class="grid grid-cols-5 hover:bg-gray-50" x-data="{ tooltip: false }">
                                                <div class="py-3 px-4 col-span-2 font-medium flex items-center">
                                                    <span>MySQL Version</span>
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                    <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                        <div class="font-semibold mb-1">MySQL Version</div>
                                                        <p>Minimum MySQL database version required for proper data handling and storage.</p>
                                                    </div>
                                                </div>
                                                <div class="py-3 px-4">{{ $requirements['mysql']['required'] ?? '5.7.0' }}</div>
                                                <div class="py-3 px-4">{{ $requirements['mysql']['current'] ?? 'Unknown' }}</div>
                                                <div class="py-3 px-4">
                                                    @if(isset($requirements['mysql']['status']) && $requirements['mysql']['status'])
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check mr-1"></i> OK
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <i class="fas fa-times mr-1"></i> Failed
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- PHP Extensions -->
                                            @foreach($requirements['extensions'] ?? [] as $extension => $data)
                                                <div class="grid grid-cols-5 hover:bg-gray-50" x-data="{ tooltip: false }">
                                                    <div class="py-3 px-4 col-span-2 font-medium flex items-center">
                                                        <span>{{ ucfirst($extension) }} Extension</span>
                                                        <span 
                                                            @mouseenter="tooltip = true" 
                                                            @mouseleave="tooltip = false"
                                                            class="ml-1.5 text-gray-400 cursor-help">
                                                            <i class="fas fa-info-circle"></i>
                                                        </span>
                                                        <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                            <div class="font-semibold mb-1">{{ ucfirst($extension) }} Extension</div>
                                                            <p>{{ $data['description'] ?? 'Required PHP extension for the system to function properly.' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="py-3 px-4">Enabled</div>
                                                    <div class="py-3 px-4">{{ $data['current'] ? 'Enabled' : 'Disabled' }}</div>
                                                    <div class="py-3 px-4">
                                                        @if($data['status'])
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                <i class="fas fa-check mr-1"></i> OK
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                <i class="fas fa-times mr-1"></i> Failed
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <!-- Directory Permissions -->
                                            <div class="grid grid-cols-5 hover:bg-gray-50" x-data="{ tooltip: false }">
                                                <div class="py-3 px-4 col-span-2 font-medium flex items-center">
                                                    <span>Storage Directory</span>
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                    <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                        <div class="font-semibold mb-1">Storage Directory</div>
                                                        <p>This directory must be writable for file uploads, cache storage, and temporary files.</p>
                                                    </div>
                                                </div>
                                                <div class="py-3 px-4">Writable</div>
                                                <div class="py-3 px-4">{{ isset($requirements['directories']['storage']) && $requirements['directories']['storage']['current'] ? 'Writable' : 'Not Writable' }}</div>
                                                <div class="py-3 px-4">
                                                    @if(isset($requirements['directories']['storage']) && $requirements['directories']['storage']['status'])
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check mr-1"></i> OK
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <i class="fas fa-times mr-1"></i> Failed
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="grid grid-cols-5 hover:bg-gray-50" x-data="{ tooltip: false }">
                                                <div class="py-3 px-4 col-span-2 font-medium flex items-center">
                                                    <span>Bootstrap Directory</span>
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                    <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                        <div class="font-semibold mb-1">Bootstrap Directory</div>
                                                        <p>This directory must be writable for framework caching and configuration.</p>
                                                    </div>
                                                </div>
                                                <div class="py-3 px-4">Writable</div>
                                                <div class="py-3 px-4">{{ isset($requirements['directories']['bootstrap']) && $requirements['directories']['bootstrap']['current'] ? 'Writable' : 'Not Writable' }}</div>
                                                <div class="py-3 px-4">
                                                    @if(isset($requirements['directories']['bootstrap']) && $requirements['directories']['bootstrap']['status'])
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <i class="fas fa-check mr-1"></i> OK
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            <i class="fas fa-times mr-1"></i> Failed
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white border border-gray-200 rounded-lg p-5">
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center mb-4">
                                        <i class="fas fa-hdd mr-2 text-indigo-500"></i>
                                        System Resources
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Disk Space -->
                                        <div class="bg-gray-50 rounded-lg p-4" x-data="{ tooltip: false }">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-700 flex items-center">
                                                    <i class="fas fa-hdd mr-2 text-indigo-400"></i>
                                                    <span>Disk Space</span>
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                </h4>
                                                <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                    <div class="font-semibold mb-1">Available Disk Space</div>
                                                    <p>Shows how much free disk space is available for application storage. At least 500MB is recommended.</p>
                                                </div>
                                                <span class="text-xs font-semibold 
                                                    {{ isset($requirements['disk']) && $requirements['disk']['status'] ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ isset($requirements['disk']) && $requirements['disk']['current'] ? $requirements['disk']['current'] : 'Unknown' }}
                                                </span>
                                            </div>
                                            <div class="relative pt-1">
                                                <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200">
                                                    <div 
                                                        style="width: {{ isset($requirements['disk']) && $requirements['disk']['percentage'] ? $requirements['disk']['percentage'] : 0 }}%" 
                                                        class="{{ isset($requirements['disk']) && $requirements['disk']['status'] ? 'bg-green-500' : 'bg-red-500' }} shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Memory Limit -->
                                        <div class="bg-gray-50 rounded-lg p-4" x-data="{ tooltip: false }">
                                            <div class="flex items-center justify-between mb-1">
                                                <h4 class="font-medium text-gray-700 flex items-center">
                                                    <i class="fas fa-memory mr-2 text-indigo-400"></i>
                                                    <span>Memory Limit</span>
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                </h4>
                                                <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                    <div class="font-semibold mb-1">PHP Memory Limit</div>
                                                    <p>The maximum amount of memory a PHP script can consume. At least 128MB is recommended for this application.</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-500">Required: {{ isset($requirements['memory']) ? $requirements['memory']['required'] : '128M' }}</span>
                                                <span class="text-xs font-semibold 
                                                    {{ isset($requirements['memory']) && $requirements['memory']['status'] ? 'text-green-600' : 'text-red-600' }}">
                                                    Current: {{ isset($requirements['memory']) ? $requirements['memory']['current'] : 'Unknown' }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Upload Max Size -->
                                        <div class="bg-gray-50 rounded-lg p-4" x-data="{ tooltip: false }">
                                            <div class="flex items-center justify-between mb-1">
                                                <h4 class="font-medium text-gray-700 flex items-center">
                                                    <i class="fas fa-upload mr-2 text-indigo-400"></i>
                                                    <span>Upload Max Size</span>
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                </h4>
                                                <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                    <div class="font-semibold mb-1">Maximum Upload Size</div>
                                                    <p>The maximum file size that can be uploaded to the server. At least 8MB is recommended for document uploads.</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-500">Required: {{ isset($requirements['upload']) ? $requirements['upload']['required'] : '8M' }}</span>
                                                <span class="text-xs font-semibold 
                                                    {{ isset($requirements['upload']) && $requirements['upload']['status'] ? 'text-green-600' : 'text-red-600' }}">
                                                    Current: {{ isset($requirements['upload']) ? $requirements['upload']['current'] : 'Unknown' }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Max Execution Time -->
                                        <div class="bg-gray-50 rounded-lg p-4" x-data="{ tooltip: false }">
                                            <div class="flex items-center justify-between mb-1">
                                                <h4 class="font-medium text-gray-700 flex items-center">
                                                    <i class="fas fa-stopwatch mr-2 text-indigo-400"></i>
                                                    <span>Execution Time</span>
                                                    <span 
                                                        @mouseenter="tooltip = true" 
                                                        @mouseleave="tooltip = false"
                                                        class="ml-1.5 text-gray-400 cursor-help">
                                                        <i class="fas fa-info-circle"></i>
                                                    </span>
                                                </h4>
                                                <div x-cloak x-show="tooltip" class="absolute mt-5 ml-0 bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 w-64">
                                                    <div class="font-semibold mb-1">Maximum Execution Time</div>
                                                    <p>The maximum time in seconds a script is allowed to run before it is terminated. At least 30 seconds is recommended.</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-500">Required: {{ isset($requirements['execution']) ? $requirements['execution']['required'] : '30 sec' }}</span>
                                                <span class="text-xs font-semibold 
                                                    {{ isset($requirements['execution']) && $requirements['execution']['status'] ? 'text-green-600' : 'text-red-600' }}">
                                                    Current: {{ isset($requirements['execution']) ? $requirements['execution']['current'] : 'Unknown' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                @if(count(array_filter($requirements ?? [], function($item) { return isset($item['status']) && !$item['status']; })) > 0)
                                <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded-md">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-orange-400"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-orange-800">Warning: Some system requirements are not met</h3>
                                            <div class="mt-2 text-sm text-orange-700">
                                                <p>Your server doesn't meet all the requirements to run the application properly. Please fix the issues marked as "Failed" above.</p>
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

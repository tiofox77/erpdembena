<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- JavaScript for Notifications -->
    <script>
        function showNotification(message, type = 'success') {
            if (window.toastr) {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000,
                    extendedTimeOut: 2000
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

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="bg-white shadow-lg rounded-2xl border border-slate-200/60">
                    <div class="px-8 py-6 border-b border-slate-200/60">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg">
                                    <i class="fas fa-cog text-white text-xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold text-slate-800">{{ __('messages.system_settings') }}</h1>
                                    <p class="text-slate-600 mt-1">{{ __('messages.manage_system_settings') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                    <i class="fas fa-circle text-green-500 text-xs mr-1"></i>
                                    Sistema Activo
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Tabs -->
                    <div class="px-8">
                        <div class="border-b border-slate-200">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button wire:click="setActiveTab('general')" type="button" 
                                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 {{ $activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                                    <div class="w-8 h-8 mr-3 rounded-lg flex items-center justify-center {{ $activeTab === 'general' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200' }}">
                                        <i class="fas fa-building text-sm"></i>
                                    </div>
                                    {{ __('messages.general') }}
                                </button>
                                
                                <button wire:click="setActiveTab('updates')" type="button" 
                                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 {{ $activeTab === 'updates' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                                    <div class="w-8 h-8 mr-3 rounded-lg flex items-center justify-center {{ $activeTab === 'updates' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200' }}">
                                        <i class="fas fa-download text-sm"></i>
                                    </div>
                                    {{ __('messages.updates') }}
                                </button>
                                
                                <button wire:click="setActiveTab('maintenance')" type="button" 
                                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 {{ $activeTab === 'maintenance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                                    <div class="w-8 h-8 mr-3 rounded-lg flex items-center justify-center {{ $activeTab === 'maintenance' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200' }}">
                                        <i class="fas fa-tools text-sm"></i>
                                    </div>
                                    {{ __('messages.maintenance') }}
                                </button>
                                
                                <button wire:click="setActiveTab('requirements')" type="button" 
                                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 {{ $activeTab === 'requirements' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                                    <div class="w-8 h-8 mr-3 rounded-lg flex items-center justify-center {{ $activeTab === 'requirements' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200' }}">
                                        <i class="fas fa-shield-alt text-sm"></i>
                                    </div>
                                    {{ __('messages.system_requirements') }}
                                </button>
                                
                                <button wire:click="setActiveTab('opcache')" type="button" 
                                    class="group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 {{ $activeTab === 'opcache' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                                    <div class="w-8 h-8 mr-3 rounded-lg flex items-center justify-center {{ $activeTab === 'opcache' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400 group-hover:bg-slate-200' }}">
                                        <i class="fas fa-tachometer-alt text-sm"></i>
                                    </div>
                                    OPcache
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Tab contents -->
                    <div class="p-8">
                        <!-- General Settings Tab -->
                        <div class="{{ $activeTab === 'general' ? 'block' : 'hidden' }}" role="tabpanel">
                            <form wire:submit.prevent="saveGeneralSettings" class="space-y-8">
                                @if($errors->any())
                                    <div class="rounded-xl bg-red-50 border border-red-200 p-6 shadow-sm">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-exclamation-triangle text-red-500"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <h3 class="text-sm font-semibold text-red-800">Corrigir os seguintes erros:</h3>
                                                <ul class="mt-2 space-y-1">
                                                    @foreach($errors->all() as $error)
                                                        <li class="text-sm text-red-700 flex items-center">
                                                            <i class="fas fa-dot-circle text-red-400 text-xs mr-2"></i>
                                                            {{ $error }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Company Information Card -->
                                <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                                        <h3 class="text-lg font-semibold text-white flex items-center">
                                            <i class="fas fa-building mr-3"></i>
                                            Informações da Empresa
                                        </h3>
                                    </div>
                                    <div class="p-6 space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label for="company_name" class="block text-sm font-semibold text-slate-700">{{ __('messages.company_name') }}</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-building text-slate-400"></i>
                                                    </div>
                                                    <input type="text"
                                                        wire:model.live="company_name"
                                                        id="company_name"
                                                        class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200
                                                        @error('company_name') border-red-300 bg-red-50 @enderror"
                                                        placeholder="Nome da Empresa">
                                                    @error('company_name')
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('company_name')
                                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-info-circle mr-1"></i>{{ $message }}
                                                    </p>
                                                @enderror
                                            </div>

                                            <div class="space-y-2">
                                                <label for="company_logo" class="block text-sm font-semibold text-slate-700">{{ __('messages.company_logo') }}</label>
                                                <div class="relative">
                                                    <input type="file"
                                                        wire:model.live="company_logo"
                                                        id="company_logo"
                                                        accept="image/*"
                                                        class="block w-full text-sm text-slate-600 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200
                                                        @error('company_logo') border-red-300 bg-red-50 @enderror">
                                                    @error('company_logo')
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('company_logo')
                                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-info-circle mr-1"></i>{{ $message }}
                                                    </p>
                                                @enderror

                                                @if ($company_logo)
                                                    <div class="mt-4 flex items-center space-x-4">
                                                        <div class="w-16 h-16 bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
                                                            <img src="{{ $company_logo->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                                                        </div>
                                                        <div class="text-sm text-slate-600">
                                                            <p class="font-medium">Pré-visualização do logo</p>
                                                            <p class="text-xs text-slate-500">Máximo: 1MB</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Company Details Card -->
                                <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden">
                                    <div class="bg-gradient-to-r from-slate-500 to-slate-600 px-6 py-4">
                                        <h3 class="text-lg font-semibold text-white flex items-center">
                                            <i class="fas fa-address-card mr-3"></i>
                                            {{ __('messages.company_details') }}
                                        </h3>
                                        <p class="text-slate-200 text-sm mt-1">Informações que aparecerão nos relatórios e documentos</p>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                            <div class="space-y-2">
                                                <label for="company_address" class="block text-sm font-semibold text-slate-700">Endereço</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-map-marker-alt text-slate-400"></i>
                                                    </div>
                                                    <input type="text"
                                                        wire:model.live="company_address"
                                                        id="company_address"
                                                        class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                                        placeholder="Ex: Rua Principal, 123 - Luanda">
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <label for="company_phone" class="block text-sm font-semibold text-slate-700">Telefone</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-phone text-slate-400"></i>
                                                    </div>
                                                    <input type="text"
                                                        wire:model.live="company_phone"
                                                        id="company_phone"
                                                        class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                                        placeholder="Ex: +244 923 456 789">
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <label for="company_email" class="block text-sm font-semibold text-slate-700">Email</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-envelope text-slate-400"></i>
                                                    </div>
                                                    <input type="email"
                                                        wire:model.live="company_email"
                                                        id="company_email"
                                                        class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200
                                                        @error('company_email') border-red-300 bg-red-50 @enderror"
                                                        placeholder="Ex: contato@empresa.com">
                                                    @error('company_email')
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('company_email')
                                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-info-circle mr-1"></i>{{ $message }}
                                                    </p>
                                                @enderror
                                            </div>

                                            <div class="space-y-2">
                                                <label for="company_website" class="block text-sm font-semibold text-slate-700">Website</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-globe text-slate-400"></i>
                                                    </div>
                                                    <input type="text"
                                                        wire:model.live="company_website"
                                                        id="company_website"
                                                        class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                                        placeholder="Ex: www.empresa.com">
                                                </div>
                                            </div>

                                            <div class="space-y-2">
                                                <label for="company_tax_id" class="block text-sm font-semibold text-slate-700">NIF</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-id-card text-slate-400"></i>
                                                    </div>
                                                    <input type="text"
                                                        wire:model.live="company_tax_id"
                                                        id="company_tax_id"
                                                        class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                                        placeholder="Ex: 5417654321">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- System Configuration Card -->
                                <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                                        <h3 class="text-lg font-semibold text-white flex items-center">
                                            <i class="fas fa-cogs mr-3"></i>
                                            Configurações do Sistema
                                        </h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label for="app_timezone" class="block text-sm font-semibold text-slate-700">Fuso Horário</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                        <i class="fas fa-clock text-slate-400"></i>
                                                    </div>
                                                    <select
                                                        wire:model.live="app_timezone"
                                                        id="app_timezone"
                                                        class="block w-full pl-10 pr-8 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none bg-white
                                                        @error('app_timezone') border-red-300 bg-red-50 @enderror">
                                                        @foreach ($timezones as $key => $name)
                                                            <option value="{{ $key }}">{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-chevron-down text-slate-400"></i>
                                                    </div>
                                                    @error('app_timezone')
                                                        <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('app_timezone')
                                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-info-circle mr-1"></i>{{ $message }}
                                                    </p>
                                                @enderror
                                            </div>

                                            <div class="space-y-2">
                                                <label for="date_format" class="block text-sm font-semibold text-slate-700">Formato de Data</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                        <i class="fas fa-calendar text-slate-400"></i>
                                                    </div>
                                                    <select
                                                        wire:model.live="date_format"
                                                        id="date_format"
                                                        class="block w-full pl-10 pr-8 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none bg-white
                                                        @error('date_format') border-red-300 bg-red-50 @enderror">
                                                        @foreach ($date_formats as $format => $example)
                                                            <option value="{{ $format }}">{{ $example }} ({{ $format }})</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-chevron-down text-slate-400"></i>
                                                    </div>
                                                    @error('date_format')
                                                        <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('date_format')
                                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-info-circle mr-1"></i>{{ $message }}
                                                    </p>
                                                @enderror
                                            </div>

                                            <div class="space-y-2">
                                                <label for="currency" class="block text-sm font-semibold text-slate-700">Moeda</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                        <i class="fas fa-dollar-sign text-slate-400"></i>
                                                    </div>
                                                    <select
                                                        wire:model.live="currency"
                                                        id="currency"
                                                        class="block w-full pl-10 pr-8 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none bg-white
                                                        @error('currency') border-red-300 bg-red-50 @enderror">
                                                        @foreach ($currencies as $code => $name)
                                                            <option value="{{ $code }}">{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-chevron-down text-slate-400"></i>
                                                    </div>
                                                    @error('currency')
                                                        <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('currency')
                                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-info-circle mr-1"></i>{{ $message }}
                                                    </p>
                                                @enderror
                                            </div>

                                            <div class="space-y-2">
                                                <label for="language" class="block text-sm font-semibold text-slate-700">Idioma</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                                                        <i class="fas fa-language text-slate-400"></i>
                                                    </div>
                                                    <select
                                                        wire:model.live="language"
                                                        id="language"
                                                        class="block w-full pl-10 pr-8 py-3 border border-slate-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none bg-white
                                                        @error('language') border-red-300 bg-red-50 @enderror">
                                                        @foreach ($languages as $code => $name)
                                                            <option value="{{ $code }}">{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                        <i class="fas fa-chevron-down text-slate-400"></i>
                                                    </div>
                                                    @error('language')
                                                        <div class="absolute inset-y-0 right-8 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('language')
                                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-info-circle mr-1"></i>{{ $message }}
                                                    </p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Button -->
                                <div class="flex justify-end pt-6">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200">
                                        <i class="fas fa-save mr-2"></i> 
                                        Guardar Configurações Gerais
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Updates Tab -->
                        <div class="{{ $activeTab === 'updates' ? 'block' : 'hidden' }}" role="tabpanel">
                            <!-- System Version Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-code-branch mr-3"></i>
                                        Versão do Sistema
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-16 h-16 bg-purple-100 rounded-xl flex items-center justify-center">
                                                <i class="fas fa-download text-purple-600 text-2xl"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-slate-800">Versão Actual: v{{ $current_version }}</h4>
                                                <p class="text-sm text-slate-600 mt-1">{{ $update_status }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <button
                                                wire:click="checkForUpdates"
                                                wire:loading.attr="disabled"
                                                wire:target="checkForUpdates"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-xl shadow-lg hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <i class="fas fa-sync-alt mr-2" wire:loading.class="animate-spin" wire:target="checkForUpdates"></i>
                                                <span wire:loading.remove wire:target="checkForUpdates">Verificar Actualizações</span>
                                                <span wire:loading wire:target="checkForUpdates">Verificando...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($update_available)
                            <!-- Update Available Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-green-200 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-arrow-circle-up mr-3"></i>
                                        Actualização Disponível: v{{ $latest_version }}
                                    </h3>
                                </div>
                                <div class="p-6 space-y-6">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-gift text-green-600 text-xl"></i>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-slate-800 text-lg">{{ $update_notes['title'] ?? 'Nova Versão Disponível' }}</h4>
                                            <div class="mt-2 text-sm text-slate-600 whitespace-pre-line bg-slate-50 p-4 rounded-lg">
                                                {{ $update_notes['body'] ?? 'Actualização disponível para instalação.' }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="border-t border-slate-200 pt-6">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <input
                                                    id="backup_before_update"
                                                    wire:model.live="backup_before_update"
                                                    type="checkbox"
                                                    class="w-5 h-5 rounded border-slate-300 text-green-600 focus:ring-green-500 transition-all duration-200">
                                                <label for="backup_before_update" class="text-sm font-medium text-slate-700">
                                                    Criar backup antes da actualização
                                                </label>
                                            </div>
                                            <button
                                                wire:click="confirmStartUpdate"
                                                wire:loading.attr="disabled"
                                                wire:target="startUpdate"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:from-green-700 hover:to-emerald-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 disabled:opacity-50">
                                                <i class="fas fa-rocket mr-2"></i>
                                                <span wire:loading.remove wire:target="startUpdate">Instalar Actualização</span>
                                                <span wire:loading wire:target="startUpdate">Instalando...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($isUpdating)
                            <!-- Update Progress Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-blue-200 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-cog fa-spin mr-3"></i>
                                        Progresso da Actualização
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <p class="mb-4 text-sm text-slate-600">{{ $update_status }}</p>
                                    <div class="bg-slate-200 rounded-full h-3 overflow-hidden">
                                        <div 
                                            style="width: {{ $update_progress }}%" 
                                            class="bg-gradient-to-r from-blue-500 to-blue-600 h-full rounded-full transition-all duration-500 ease-out">
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center mt-2">
                                        <span class="text-xs text-slate-500">Progresso</span>
                                        <span class="text-sm font-semibold text-blue-600">{{ $update_progress }}%</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($update_logs)
                            <!-- Update Logs Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-slate-600 to-slate-700 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-terminal mr-3"></i>
                                        Logs da Actualização
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="bg-slate-900 rounded-lg p-4 max-h-64 overflow-y-auto">
                                        <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap">{{ $update_logs }}</pre>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Update Settings Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-cogs mr-3"></i>
                                        Configurações de Actualização
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <form wire:submit.prevent="saveUpdateSettings" class="space-y-6">
                                        @if($errors->any())
                                            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                                <div class="flex items-start">
                                                    <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                                        <i class="fas fa-exclamation text-red-600 text-sm"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <h4 class="font-medium text-red-800">Corrija os seguintes erros:</h4>
                                                        <ul class="mt-2 text-sm text-red-700 space-y-1">
                                                            @foreach($errors->all() as $error)
                                                                <li class="flex items-center">
                                                                    <i class="fas fa-circle text-xs mr-2"></i>
                                                                    {{ $error }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label for="github_repository" class="block text-sm font-semibold text-slate-700 mb-2">
                                                    <i class="fab fa-github mr-2"></i>
                                                    Repositório GitHub
                                                </label>
                                                <div class="relative">
                                                    <input
                                                        type="text"
                                                        wire:model.live="github_repository"
                                                        id="github_repository"
                                                        class="w-full pl-4 pr-10 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-slate-50 hover:bg-white
                                                        @error('github_repository') border-red-300 bg-red-50 text-red-900 placeholder-red-400 focus:ring-red-500 focus:border-red-500 @enderror"
                                                        placeholder="utilizador/repositorio">
                                                    @error('github_repository')
                                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                            <i class="fas fa-exclamation-circle text-red-500"></i>
                                                        </div>
                                                    @enderror
                                                </div>
                                                @error('github_repository')
                                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        {{ $message }}
                                                    </p>
                                                @else
                                                    <p class="mt-1 text-xs text-slate-500">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Exemplo: laravel/framework
                                                    </p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="flex justify-end pt-4 border-t border-slate-200">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200">
                                                <i class="fas fa-save mr-2"></i> 
                                                Guardar Configurações
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Backup Management Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-shield-alt mr-3"></i>
                                        Gestão de Backups
                                    </h3>
                                </div>
                                <div class="p-6">
                                    @if(count($available_backups) > 0)
                                        <div class="space-y-4">
                                            @foreach($available_backups as $backup)
                                                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 hover:border-emerald-300 transition-colors duration-200">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center space-x-4">
                                                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                                                                <i class="fas fa-archive text-emerald-600 text-xl"></i>
                                                            </div>
                                                            <div>
                                                                <h4 class="font-semibold text-slate-800">{{ $backup['date'] }}</h4>
                                                                <div class="flex items-center space-x-4 text-sm text-slate-600">
                                                                    <span class="flex items-center">
                                                                        <i class="fas fa-file-archive mr-1"></i>
                                                                        {{ $backup['size'] }}
                                                                    </span>
                                                                    @if($backup['database_file'] && file_exists($backup['database_file']))
                                                                        <span class="flex items-center text-green-600">
                                                                            <i class="fas fa-database mr-1"></i>
                                                                            BD incluída
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center space-x-2">
                                                            <button
                                                                wire:click="confirmRestoreBackup('{{ $backup['filename'] }}')"
                                                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-medium rounded-lg shadow hover:from-emerald-700 hover:to-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all duration-200">
                                                                <i class="fas fa-undo mr-2"></i>
                                                                Restaurar
                                                            </button>
                                                            <button
                                                                wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                                                wire:confirm="Tem a certeza que deseja eliminar este backup? Esta ação não pode ser desfeita."
                                                                class="inline-flex items-center px-3 py-2 text-red-600 hover:text-white hover:bg-red-600 border border-red-300 hover:border-red-600 text-sm font-medium rounded-lg transition-all duration-200">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-8">
                                            <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                                                <i class="fas fa-archive text-slate-400 text-2xl"></i>
                                            </div>
                                            <h4 class="text-lg font-medium text-slate-600 mb-2">Nenhum backup disponível</h4>
                                            <p class="text-sm text-slate-500">Os backups serão criados automaticamente antes das actualizações.</p>
                                        </div>
                                    @endif

                                    @if($isRestoringBackup)
                                        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                                            <div class="flex items-center mb-3">
                                                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                    <i class="fas fa-cog fa-spin text-blue-600 text-sm"></i>
                                                </div>
                                                <h4 class="font-medium text-blue-800">Restaurando sistema...</h4>
                                            </div>
                                            <p class="text-sm text-blue-700 mb-3">{{ $restore_status }}</p>
                                            <div class="bg-blue-200 rounded-full h-2 overflow-hidden">
                                                <div 
                                                    style="width: {{ $restore_progress }}%" 
                                                    class="bg-gradient-to-r from-blue-500 to-blue-600 h-full rounded-full transition-all duration-500">
                                                </div>
                                            </div>
                                            <div class="flex justify-between items-center mt-2">
                                                <span class="text-xs text-blue-600">Progresso</span>
                                                <span class="text-sm font-semibold text-blue-700">{{ $restore_progress }}%</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div class="{{ $activeTab === 'maintenance' ? 'block' : 'hidden' }}" role="tabpanel">
                            <!-- Warning Alert Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-amber-200 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-3"></i>
                                        Aviso Importante
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="flex items-start space-x-4">
                                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-shield-alt text-amber-600 text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-slate-800 text-lg">Modo de Manutenção</h4>
                                            <p class="text-sm text-slate-600 mt-2">
                                                Activar o modo de manutenção tornará a aplicação inacessível aos utilizadores. 
                                                Apenas os administradores poderão aceder ao site durante este período.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Maintenance Settings Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-tools mr-3"></i>
                                        Configurações de Manutenção
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <form wire:submit.prevent="saveMaintenanceSettings" class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                                            <i class="fas fa-pause text-red-600"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-slate-800">Modo de Manutenção</h4>
                                                            <p class="text-xs text-slate-600">Activar para manutenção do sistema</p>
                                                        </div>
                                                    </div>
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input
                                                            id="maintenance_mode"
                                                            wire:model.live="maintenance_mode"
                                                            type="checkbox"
                                                            class="sr-only peer">
                                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                            <i class="fas fa-bug text-blue-600"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-semibold text-slate-800">Modo Debug</h4>
                                                            <p class="text-xs text-slate-600">Activar para depuração</p>
                                                        </div>
                                                    </div>
                                                    <label class="relative inline-flex items-center cursor-pointer">
                                                        <input
                                                            id="debug_mode"
                                                            wire:model.live="debug_mode"
                                                            type="checkbox"
                                                            class="sr-only peer">
                                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex justify-end pt-4 border-t border-slate-200">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold rounded-xl shadow-lg hover:from-red-700 hover:to-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200">
                                                <i class="fas fa-save mr-2"></i> 
                                                Guardar Configurações
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- System Tools Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-slate-600 to-slate-700 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-cogs mr-3"></i>
                                        Ferramentas do Sistema
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <button
                                            type="button"
                                            wire:click="confirmRunArtisanCommand('optimize:clear')"
                                            class="group bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 rounded-xl p-4 transition-all duration-200 hover:shadow-md">
                                            <div class="flex flex-col items-center text-center space-y-2">
                                                <div class="w-12 h-12 bg-blue-100 group-hover:bg-blue-200 rounded-xl flex items-center justify-center transition-colors duration-200">
                                                    <i class="fas fa-broom text-blue-600 text-xl"></i>
                                                </div>
                                                <span class="font-semibold text-slate-700 group-hover:text-blue-700">Limpar Cache</span>
                                                <span class="text-xs text-slate-500">Optimizar desempenho</span>
                                            </div>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="confirmRunArtisanCommand('migrate')"
                                            class="group bg-slate-50 hover:bg-green-50 border border-slate-200 hover:border-green-200 rounded-xl p-4 transition-all duration-200 hover:shadow-md">
                                            <div class="flex flex-col items-center text-center space-y-2">
                                                <div class="w-12 h-12 bg-green-100 group-hover:bg-green-200 rounded-xl flex items-center justify-center transition-colors duration-200">
                                                    <i class="fas fa-database text-green-600 text-xl"></i>
                                                </div>
                                                <span class="font-semibold text-slate-700 group-hover:text-green-700">Executar Migrações</span>
                                                <span class="text-xs text-slate-500">Actualizar base de dados</span>
                                            </div>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="confirmRunArtisanCommand('storage:link')"
                                            class="group bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-200 rounded-xl p-4 transition-all duration-200 hover:shadow-md">
                                            <div class="flex flex-col items-center text-center space-y-2">
                                                <div class="w-12 h-12 bg-purple-100 group-hover:bg-purple-200 rounded-xl flex items-center justify-center transition-colors duration-200">
                                                    <i class="fas fa-link text-purple-600 text-xl"></i>
                                                </div>
                                                <span class="font-semibold text-slate-700 group-hover:text-purple-700">Criar Link Storage</span>
                                                <span class="text-xs text-slate-500">Configurar armazenamento</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Requirements Tab -->
                        <div class="{{ $activeTab === 'requirements' ? 'block' : 'hidden' }}" role="tabpanel">
                            <!-- Requirements Header Card -->
                            <div class="bg-white rounded-2xl shadow-lg border border-slate-200/60 overflow-hidden mb-8">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                                    <h3 class="text-lg font-semibold text-white flex items-center">
                                        <i class="fas fa-clipboard-check mr-3"></i>
                                        Verificação de Requisitos do Sistema
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <p class="text-sm text-slate-600">
                                        Verificar se o seu sistema atende todos os requisitos para funcionamento optimal.
                                    </p>
                                </div>
                            </div>

                            <!-- Status Summary Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div class="bg-white rounded-2xl shadow-lg border border-green-200 overflow-hidden">
                                    <div class="p-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-16 h-16 bg-green-100 rounded-xl flex items-center justify-center">
                                                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-slate-800">Aprovados</h4>
                                                <p class="text-3xl font-bold text-green-600">{{ $requirementsStatus['passed'] }}</p>
                                                <p class="text-sm text-slate-500">Requisitos cumpridos</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-2xl shadow-lg border border-amber-200 overflow-hidden">
                                    <div class="p-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-16 h-16 bg-amber-100 rounded-xl flex items-center justify-center">
                                                <i class="fas fa-exclamation-triangle text-amber-600 text-2xl"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-slate-800">Avisos</h4>
                                                <p class="text-3xl font-bold text-amber-600">{{ $requirementsStatus['warnings'] }}</p>
                                                <p class="text-sm text-slate-500">Requer atenção</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white rounded-2xl shadow-lg border border-red-200 overflow-hidden">
                                    <div class="p-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-16 h-16 bg-red-100 rounded-xl flex items-center justify-center">
                                                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-slate-800">Falharam</h4>
                                                <p class="text-3xl font-bold text-red-600">{{ $requirementsStatus['failed'] }}</p>
                                                <p class="text-sm text-slate-500">Requer correção</p>
                                            </div>
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
        
        <!-- OPcache Tab -->
        <div class="{{ $activeTab === 'opcache' ? 'block' : 'hidden' }}" role="tabpanel">
            <div class="space-y-6">
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-white flex items-center">
                                <i class="fas fa-tachometer-alt mr-3 animate-pulse"></i>
                                OPcache Status & Performance
                            </h2>
                            <p class="text-green-100 mt-1">Monitor e otimização do cache de bytecode PHP</p>
                        </div>
                        <button wire:click="loadOpcacheStatus" class="px-4 py-2 bg-white text-green-700 rounded-lg hover:bg-green-50 transition-all duration-200 font-medium">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Atualizar
                        </button>
                    </div>
                </div>

                @if(!empty($opcacheHealth))
                    <!-- Health Status Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-heartbeat text-red-500 mr-2"></i>
                            Estado de Saúde
                        </h3>
                        
                        @if($opcacheHealth['status'] === 'healthy')
                            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                                    <div>
                                        <p class="font-bold text-green-900">{{ $opcacheHealth['message'] }}</p>
                                        <p class="text-sm text-green-700 mt-1">OPcache está funcionando perfeitamente</p>
                                    </div>
                                </div>
                            </div>
                        @elseif($opcacheHealth['status'] === 'warning')
                            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mr-3 mt-1"></i>
                                    <div class="flex-1">
                                        <p class="font-bold text-yellow-900 mb-2">{{ $opcacheHealth['message'] }}</p>
                                        
                                        @if(!empty($opcacheHealth['issues']))
                                            <div class="mt-3">
                                                <p class="text-sm font-semibold text-yellow-800 mb-1">Problemas Detectados:</p>
                                                <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1">
                                                    @foreach($opcacheHealth['issues'] as $issue)
                                                        <li>{{ $issue }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        
                                        @if(!empty($opcacheHealth['recommendations']))
                                            <div class="mt-3">
                                                <p class="text-sm font-semibold text-yellow-800 mb-1">Recomendações:</p>
                                                <ul class="list-disc list-inside text-sm text-yellow-700 space-y-1">
                                                    @foreach($opcacheHealth['recommendations'] as $recommendation)
                                                        <li>{{ $recommendation }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @elseif($opcacheHealth['status'] === 'disabled')
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-times-circle text-red-600 text-2xl mr-3"></i>
                                    <div>
                                        <p class="font-bold text-red-900">{{ $opcacheHealth['message'] }}</p>
                                        <p class="text-sm text-red-700 mt-1">OPcache não está habilitado</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-gray-50 border-l-4 border-gray-500 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-info-circle text-gray-600 text-2xl mr-3"></i>
                                    <p class="text-gray-700">{{ $opcacheHealth['message'] ?? 'Status desconhecido' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if(!empty($opcacheStatus) && isset($opcacheStatus['enabled']) && $opcacheStatus['enabled'])
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Memory Usage -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <i class="fas fa-memory text-blue-600 text-xl"></i>
                                </div>
                                <span class="text-2xl font-bold text-blue-600">
                                    {{ $opcacheStatus['memory']['usage_percent'] ?? 0 }}%
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700">Uso de Memória</h4>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $opcacheStatus['memory']['used'] ?? 0 }}MB / {{ $opcacheStatus['memory']['total'] ?? 0 }}MB
                            </p>
                            <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" 
                                     style="width: {{ $opcacheStatus['memory']['usage_percent'] ?? 0 }}%"></div>
                            </div>
                        </div>

                        <!-- Hit Rate -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <i class="fas fa-bullseye text-green-600 text-xl"></i>
                                </div>
                                <span class="text-2xl font-bold text-green-600">
                                    {{ $opcacheStatus['statistics']['hit_rate'] ?? 0 }}%
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700">Hit Rate</h4>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ number_format($opcacheStatus['statistics']['hits'] ?? 0) }} hits
                            </p>
                            <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full transition-all duration-500" 
                                     style="width: {{ $opcacheStatus['statistics']['hit_rate'] ?? 0 }}%"></div>
                            </div>
                        </div>

                        <!-- Cached Files -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-3 bg-purple-100 rounded-lg">
                                    <i class="fas fa-file-code text-purple-600 text-xl"></i>
                                </div>
                                <span class="text-2xl font-bold text-purple-600">
                                    {{ number_format($opcacheStatus['statistics']['num_cached_scripts'] ?? 0) }}
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700">Arquivos Cached</h4>
                            <p class="text-xs text-gray-500 mt-1">
                                max: {{ number_format($opcacheStatus['configuration']['max_accelerated_files'] ?? 0) }}
                            </p>
                            <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $filePercent = ($opcacheStatus['configuration']['max_accelerated_files'] ?? 0) > 0 
                                        ? (($opcacheStatus['statistics']['num_cached_scripts'] ?? 0) / ($opcacheStatus['configuration']['max_accelerated_files'] ?? 1)) * 100 
                                        : 0;
                                @endphp
                                <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" 
                                     style="width: {{ min(100, $filePercent) }}%"></div>
                            </div>
                        </div>

                        <!-- JIT Status -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-3">
                                <div class="p-3 {{ ($opcacheStatus['jit']['enabled'] ?? false) ? 'bg-yellow-100' : 'bg-gray-100' }} rounded-lg">
                                    <i class="fas fa-bolt {{ ($opcacheStatus['jit']['enabled'] ?? false) ? 'text-yellow-600' : 'text-gray-400' }} text-xl"></i>
                                </div>
                                <span class="text-2xl font-bold {{ ($opcacheStatus['jit']['enabled'] ?? false) ? 'text-yellow-600' : 'text-gray-400' }}">
                                    {{ ($opcacheStatus['jit']['enabled'] ?? false) ? 'ON' : 'OFF' }}
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700">JIT Compiler</h4>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $opcacheStatus['jit']['buffer_size'] ?? '0' }}
                            </p>
                        </div>
                    </div>

                    <!-- Detailed Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Memory Details -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                                Detalhes de Memória
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Memória Usada</span>
                                    <span class="font-bold text-blue-600">{{ $opcacheStatus['memory']['used'] ?? 0 }}MB</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Memória Livre</span>
                                    <span class="font-bold text-green-600">{{ $opcacheStatus['memory']['free'] ?? 0 }}MB</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Memória Desperdiçada</span>
                                    <span class="font-bold text-yellow-600">{{ $opcacheStatus['memory']['wasted'] ?? 0 }}MB</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Total Alocado</span>
                                    <span class="font-bold text-gray-900">{{ $opcacheStatus['memory']['total'] ?? 0 }}MB</span>
                                </div>
                            </div>
                        </div>

                        <!-- Performance Stats -->
                        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-chart-line text-green-600 mr-2"></i>
                                Estatísticas de Performance
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Cache Hits</span>
                                    <span class="font-bold text-green-600">{{ number_format($opcacheStatus['statistics']['hits'] ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Cache Misses</span>
                                    <span class="font-bold text-red-600">{{ number_format($opcacheStatus['statistics']['misses'] ?? 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Hit Rate</span>
                                    <span class="font-bold text-blue-600">{{ $opcacheStatus['statistics']['hit_rate'] ?? 0 }}%</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Scripts Cached</span>
                                    <span class="font-bold text-purple-600">{{ number_format($opcacheStatus['statistics']['num_cached_scripts'] ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Details -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cogs text-gray-600 mr-2"></i>
                            Configuração Atual
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-500 mb-1">Max Accelerated Files</p>
                                <p class="text-lg font-bold text-gray-900">{{ number_format($opcacheStatus['configuration']['max_accelerated_files'] ?? 0) }}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-500 mb-1">Memory Consumption</p>
                                <p class="text-lg font-bold text-gray-900">{{ $opcacheStatus['configuration']['memory_consumption'] ?? 0 }}MB</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-500 mb-1">Interned Strings Buffer</p>
                                <p class="text-lg font-bold text-gray-900">{{ $opcacheStatus['configuration']['interned_strings_buffer'] ?? 0 }}MB</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-500 mb-1">Max Wasted %</p>
                                <p class="text-lg font-bold text-gray-900">{{ $opcacheStatus['configuration']['max_wasted_percentage'] ?? 0 }}%</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-500 mb-1">Validate Timestamps</p>
                                <p class="text-lg font-bold text-gray-900">{{ ($opcacheStatus['configuration']['validate_timestamps'] ?? false) ? 'Sim' : 'Não' }}</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-500 mb-1">Revalidate Freq</p>
                                <p class="text-lg font-bold text-gray-900">{{ $opcacheStatus['configuration']['revalidate_freq'] ?? 0 }}s</p>
                            </div>
                        </div>
                    </div>

                    <!-- JIT Details (if enabled) -->
                    @if($opcacheStatus['jit']['enabled'] ?? false)
                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl shadow-lg border border-yellow-200 p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-bolt text-yellow-600 mr-2 animate-pulse"></i>
                                JIT Compiler Ativo
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 bg-white rounded-lg border border-yellow-200">
                                    <p class="text-xs text-gray-500 mb-1">Buffer Size</p>
                                    <p class="text-lg font-bold text-yellow-700">{{ $opcacheStatus['jit']['buffer_size'] ?? '0' }}</p>
                                </div>
                                <div class="p-4 bg-white rounded-lg border border-yellow-200">
                                    <p class="text-xs text-gray-500 mb-1">JIT Mode</p>
                                    <p class="text-lg font-bold text-yellow-700">{{ $opcacheStatus['jit']['mode'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="mt-4 p-3 bg-yellow-100 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    JIT (Just-In-Time) está ativo e otimizando o código PHP em tempo real para melhor performance.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-lg border border-blue-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-tools text-blue-600 mr-2"></i>
                            Ações Rápidas
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <a href="{{ asset('opcache-status.php') }}" target="_blank" 
                               class="flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                Dashboard Completo
                            </a>
                            <button onclick="window.location.reload()" 
                                    class="flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Recarregar Página
                            </button>
                            <a href="{{ url('/maintenance/settings?activeTab=opcache') }}" 
                               class="flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-refresh mr-2"></i>
                                Atualizar Status
                            </a>
                        </div>
                    </div>
                @else
                    <!-- OPcache Not Enabled -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
                            <i class="fas fa-times-circle text-red-600 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">OPcache Não Está Ativo</h3>
                        <p class="text-gray-600 mb-6">
                            O OPcache não está habilitado ou configurado corretamente neste servidor.
                        </p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
                            <p class="text-sm font-semibold text-blue-900 mb-2">Como Habilitar:</p>
                            <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                                <li>Edite o arquivo php.ini</li>
                                <li>Adicione ou descomente: opcache.enable=1</li>
                                <li>Configure: opcache.memory_consumption=256</li>
                                <li>Configure: opcache.max_accelerated_files=20000</li>
                                <li>Reinicie o servidor web</li>
                            </ol>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modern Confirmation Modal with Animations -->
    @if($showConfirmModal)
    <div 
        class="fixed inset-0 z-50 overflow-y-auto"
        x-data="{ open: @entangle('showConfirmModal') }"
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gradient-to-br from-slate-900/80 to-slate-800/80 backdrop-blur-sm"></div>
        
        <!-- Modal container -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div 
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <!-- Header with gradient -->
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                                <i class="fas fa-download text-white text-xl animate-pulse"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-white">Confirmação de Actualização</h3>
                                <p class="text-amber-100 text-sm">Sistema v{{ $latest_version ?? '1.3.8.5' }}</p>
                            </div>
                        </div>
                        <button 
                            type="button" 
                            class="rounded-full bg-white/20 p-2 text-white hover:bg-white/30 transition-all duration-200 hover:scale-110" 
                            wire:click="closeConfirmModal">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-6">
                    <!-- Confirmation State -->
                    <div x-show="!$wire.isUpdating" x-transition:enter="ease-out duration-300" x-transition:leave="ease-in duration-200">
                        <!-- Warning message with modern styling -->
                        <div class="mb-6">
                            <div class="rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 p-5">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100">
                                            <i class="fas fa-exclamation-triangle text-amber-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-semibold text-amber-800 mb-2">Atenção - Actualização do Sistema</h4>
                                        <p class="text-sm text-amber-700 leading-relaxed">{{ $confirmMessage }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Progress preview (when confirming) -->
                        @if($confirmAction === 'startUpdate')
                        <div class="mb-6 rounded-2xl bg-slate-50 border border-slate-200 p-4">
                            <h5 class="text-sm font-semibold text-slate-700 mb-3 flex items-center">
                                <i class="fas fa-tasks text-blue-500 mr-2"></i>
                                Etapas da Actualização
                            </h5>
                            <div class="space-y-2">
                                <div class="flex items-center text-xs text-slate-600">
                                    <div class="w-2 h-2 rounded-full bg-blue-500 mr-3 animate-pulse"></div>
                                    1. Fazer backup antes da actualização
                                </div>
                                <div class="flex items-center text-xs text-slate-600">
                                    <div class="w-2 h-2 rounded-full bg-slate-300 mr-3"></div>
                                    2. Descarregar nova versão
                                </div>
                                <div class="flex items-center text-xs text-slate-600">
                                    <div class="w-2 h-2 rounded-full bg-slate-300 mr-3"></div>
                                    3. Aplicar actualização
                                </div>
                                <div class="flex items-center text-xs text-slate-600">
                                    <div class="w-2 h-2 rounded-full bg-slate-300 mr-3"></div>
                                    4. Verificar sistema
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Estimated time -->
                        <div class="mb-6 flex items-center justify-center">
                            <div class="bg-blue-50 rounded-xl px-4 py-2 border border-blue-200">
                                <div class="flex items-center text-sm text-blue-700">
                                    <i class="fas fa-clock mr-2 text-blue-500"></i>
                                    <span class="font-medium">Tempo estimado: 2-5 minutos</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Progress State -->
                    <div x-show="$wire.isUpdating" x-transition:enter="ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                        <!-- Animated header -->
                        <div class="text-center mb-8">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full shadow-lg mb-4">
                                <i class="fas fa-download text-white text-2xl animate-pulse"></i>
                            </div>
                            <h4 class="text-lg font-bold text-slate-800 mb-2">Actualização em Progresso</h4>
                            <p class="text-sm text-slate-600">Por favor aguarde, não feche esta janela...</p>
                        </div>

                        <!-- Progress bar -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-slate-700">{{ $update_status ?? 'Preparando...' }}</span>
                                <span class="text-sm text-slate-500">{{ $update_progress ?? 0 }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                                <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-3 rounded-full transition-all duration-700 ease-out relative"
                                     style="width: {{ $update_progress ?? 0 }}%">
                                    <div class="absolute inset-0 bg-white/30 animate-pulse"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Active steps visualization -->
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5">
                            <h5 class="text-sm font-semibold text-slate-700 mb-4 flex items-center">
                                <i class="fas fa-cogs text-blue-500 mr-2 animate-spin"></i>
                                Estado da Actualização
                            </h5>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-xl {{ ($update_progress ?? 0) >= 10 ? 'bg-green-50 border border-green-200' : 'bg-white border border-slate-200' }}">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full mr-3 flex items-center justify-center {{ ($update_progress ?? 0) >= 10 ? 'bg-green-100' : 'bg-slate-100' }}">
                                            @if(($update_progress ?? 0) >= 10)
                                                <i class="fas fa-check text-green-600 text-sm"></i>
                                            @else
                                                <i class="fas fa-circle text-slate-400 text-xs animate-pulse"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium {{ ($update_progress ?? 0) >= 10 ? 'text-green-700' : 'text-slate-600' }}">Backup do Sistema</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-xl {{ ($update_progress ?? 0) >= 30 ? 'bg-green-50 border border-green-200' : (($update_progress ?? 0) >= 20 ? 'bg-blue-50 border border-blue-200' : 'bg-white border border-slate-200') }}">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full mr-3 flex items-center justify-center {{ ($update_progress ?? 0) >= 30 ? 'bg-green-100' : (($update_progress ?? 0) >= 20 ? 'bg-blue-100' : 'bg-slate-100') }}">
                                            @if(($update_progress ?? 0) >= 30)
                                                <i class="fas fa-check text-green-600 text-sm"></i>
                                            @elseif(($update_progress ?? 0) >= 20)
                                                <i class="fas fa-download text-blue-600 text-sm animate-bounce"></i>
                                            @else
                                                <i class="fas fa-circle text-slate-400 text-xs"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium {{ ($update_progress ?? 0) >= 30 ? 'text-green-700' : (($update_progress ?? 0) >= 20 ? 'text-blue-700' : 'text-slate-600') }}">Descarregar Actualização</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-xl {{ ($update_progress ?? 0) >= 70 ? 'bg-green-50 border border-green-200' : (($update_progress ?? 0) >= 50 ? 'bg-blue-50 border border-blue-200' : 'bg-white border border-slate-200') }}">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full mr-3 flex items-center justify-center {{ ($update_progress ?? 0) >= 70 ? 'bg-green-100' : (($update_progress ?? 0) >= 50 ? 'bg-blue-100' : 'bg-slate-100') }}">
                                            @if(($update_progress ?? 0) >= 70)
                                                <i class="fas fa-check text-green-600 text-sm"></i>
                                            @elseif(($update_progress ?? 0) >= 50)
                                                <i class="fas fa-cogs text-blue-600 text-sm animate-spin"></i>
                                            @else
                                                <i class="fas fa-circle text-slate-400 text-xs"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium {{ ($update_progress ?? 0) >= 70 ? 'text-green-700' : (($update_progress ?? 0) >= 50 ? 'text-blue-700' : 'text-slate-600') }}">Aplicar Ficheiros</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-xl {{ ($update_progress ?? 0) >= 100 ? 'bg-green-50 border border-green-200' : (($update_progress ?? 0) >= 90 ? 'bg-blue-50 border border-blue-200' : 'bg-white border border-slate-200') }}">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full mr-3 flex items-center justify-center {{ ($update_progress ?? 0) >= 100 ? 'bg-green-100' : (($update_progress ?? 0) >= 90 ? 'bg-blue-100' : 'bg-slate-100') }}">
                                            @if(($update_progress ?? 0) >= 100)
                                                <i class="fas fa-check text-green-600 text-sm"></i>
                                            @elseif(($update_progress ?? 0) >= 90)
                                                <i class="fas fa-shield-check text-blue-600 text-sm animate-pulse"></i>
                                            @else
                                                <i class="fas fa-circle text-slate-400 text-xs"></i>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium {{ ($update_progress ?? 0) >= 100 ? 'text-green-700' : (($update_progress ?? 0) >= 90 ? 'text-blue-700' : 'text-slate-600') }}">Verificar Sistema</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Success animation when complete -->
                        @if(($update_progress ?? 0) >= 100)
                        <div class="mt-6 text-center" x-data="{ show: false }" x-init="setTimeout(() => show = true, 500)" x-show="show" x-transition:enter="ease-out duration-500" x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100">
                            <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-2xl shadow-lg">
                                <i class="fas fa-check-circle mr-3 text-xl animate-bounce"></i>
                                <span class="font-bold">Actualização Concluída!</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-slate-50 px-6 py-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        class="inline-flex w-full justify-center items-center rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all duration-200 sm:w-auto"
                        wire:click="closeConfirmModal"
                        :disabled="$wire.isUpdating">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="inline-flex w-full justify-center items-center rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg hover:from-amber-600 hover:to-orange-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                        wire:click="processConfirmedAction"
                        :disabled="$wire.isUpdating">
                        
                        <!-- Loading state -->
                        <div x-show="$wire.isUpdating" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Actualizando...</span>
                        </div>

                        <!-- Normal state -->
                        <div x-show="!$wire.isUpdating" class="flex items-center">
                            <i class="fas fa-rocket mr-2 animate-bounce"></i>
                            Confirmar Actualização
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
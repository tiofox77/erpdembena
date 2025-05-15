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

    <!-- Script para inicializar variáveis Alpine.js globais -->
    <script>
        document.addEventListener('alpine:init', () => {
            // Criar o store global para gerenciar estado
            Alpine.store('globals', {
                formState: 'idle',
                activeSection: 'company',
                commandHistory: []
            });

            // Para compatibilidade com o código existente, também definimos variáveis globais
            window.formState = 'idle';
            window.activeSection = 'company';
            window.commandHistory = [];

            // Funções para sincronizar store com variáveis globais
            Alpine.effect(() => {
                window.formState = Alpine.store('globals').formState;
                window.activeSection = Alpine.store('globals').activeSection;
                window.commandHistory = Alpine.store('globals').commandHistory;
            });
        });
    </script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header com fundo gradiente -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg mb-6 border border-blue-100 shadow-sm">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center mb-2">
                            <div class="p-2 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full text-white mr-3">
                                <i class="fas fa-cog text-xl"></i>
                            </div>
                            {{ __('messages.system_settings') }}
                        </h2>
                        <p class="text-gray-600 ml-12">{{ __('messages.manage_system_settings') }}</p>
                    </div>

                    <!-- Tabs com design moderno -->
                    <div class="mb-6 overflow-x-auto">
                        <ul class="flex whitespace-nowrap gap-1 md:gap-2 px-1" role="tablist">
                            <li role="presentation">
                                <button 
                                    class="group transition-all duration-200 flex items-center py-2.5 px-4 rounded-lg font-medium {{ $activeTab === 'general' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-indigo-600' }}"
                                    wire:click="setActiveTab('general')" 
                                    type="button" 
                                    role="tab"
                                    aria-selected="{{ $activeTab === 'general' ? 'true' : 'false' }}"
                                >
                                    <i class="fas fa-sliders-h mr-2 {{ $activeTab === 'general' ? 'text-white' : 'text-blue-500 group-hover:text-indigo-600' }}"></i>
                                    {{ __('messages.general') }}
                                </button>
                            </li>
                            <li role="presentation">
                                <button 
                                    class="group transition-all duration-200 flex items-center py-2.5 px-4 rounded-lg font-medium {{ $activeTab === 'updates' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-indigo-600' }}"
                                    wire:click="setActiveTab('updates')" 
                                    type="button" 
                                    role="tab"
                                    aria-selected="{{ $activeTab === 'updates' ? 'true' : 'false' }}"
                                >
                                    <i class="fas fa-sync-alt mr-2 {{ $activeTab === 'updates' ? 'text-white' : 'text-green-500 group-hover:text-indigo-600' }}"></i>
                                    {{ __('messages.updates') }}
                                </button>
                            </li>
                            <li role="presentation">
                                <button 
                                    class="group transition-all duration-200 flex items-center py-2.5 px-4 rounded-lg font-medium {{ $activeTab === 'maintenance' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-indigo-600' }}"
                                    wire:click="setActiveTab('maintenance')" 
                                    type="button" 
                                    role="tab"
                                    aria-selected="{{ $activeTab === 'maintenance' ? 'true' : 'false' }}"
                                >
                                    <i class="fas fa-tools mr-2 {{ $activeTab === 'maintenance' ? 'text-white' : 'text-yellow-500 group-hover:text-indigo-600' }}"></i>
                                    {{ __('messages.maintenance') }}
                                </button>
                            </li>
                            <li role="presentation">
                                <button 
                                    class="group transition-all duration-200 flex items-center py-2.5 px-4 rounded-lg font-medium {{ $activeTab === 'requirements' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-indigo-600' }}"
                                    wire:click="setActiveTab('requirements')" 
                                    type="button" 
                                    role="tab"
                                    aria-selected="{{ $activeTab === 'requirements' ? 'true' : 'false' }}"
                                >
                                    <i class="fas fa-clipboard-check mr-2 {{ $activeTab === 'requirements' ? 'text-white' : 'text-indigo-500 group-hover:text-indigo-600' }}"></i>
                                    {{ __('messages.system_requirements') }}
                                </button>
                            </li>
                            <li role="presentation">
                                <button 
                                    class="group transition-all duration-200 flex items-center py-2.5 px-4 rounded-lg font-medium {{ $activeTab === 'database' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-indigo-600' }}"
                                    wire:click="setActiveTab('database')" 
                                    type="button" 
                                    role="tab"
                                    aria-selected="{{ $activeTab === 'database' ? 'true' : 'false' }}"
                                >
                                    <i class="fas fa-database mr-2 {{ $activeTab === 'database' ? 'text-white' : 'text-purple-500 group-hover:text-indigo-600' }}"></i>
                                    Database SQL
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tab contents com transições animadas -->
                    <div class="p-4 bg-white rounded-lg shadow-sm">
                        <!-- General Settings Tab com nova UI -->
                        <div class="{{ $activeTab === 'general' ? 'block' : 'hidden' }}" 
                            role="tabpanel"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform translate-y-4"
                            x-transition:enter-end="opacity-100 transform translate-y-0">
                            
                            <!-- Sub navegação da aba Geral -->
                            <div class="flex flex-wrap items-center space-x-2 mb-6 border-b border-gray-200 pb-3">
                                <button 
                                    @click="$store.globals.activeSection = 'company'" 
                                    :class="$store.globals.activeSection === 'company' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center">
                                    <i class="fas fa-building mr-2"></i> Empresa
                                </button>
                                <button 
                                    @click="$store.globals.activeSection = 'appearance'" 
                                    :class="$store.globals.activeSection === 'appearance' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center">
                                    <i class="fas fa-palette mr-2"></i> Aparência
                                </button>
                                <button 
                                    @click="$store.globals.activeSection = 'language'" 
                                    :class="$store.globals.activeSection === 'language' ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center">
                                    <i class="fas fa-language mr-2"></i> Idioma
                                </button>
                            </div>
                            
                            <form wire:submit.prevent="saveGeneralSettings">
                                @if($errors->any())
                                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg shadow-sm">
                                        <p class="font-bold flex items-center text-red-700">
                                            <i class="fas fa-exclamation-circle mr-2 text-red-500 animate-pulse"></i>
                                            {{ __('messages.validation_error') }}
                                        </p>
                                        <ul class="mt-2 pl-4 list-disc text-red-600">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Seções com x-show para navegação interna da aba -->
                                <div x-show="$store.globals.activeSection === 'company'" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform translate-y-4"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-100 shadow-sm mb-6">
                                    
                                    <!-- Cabeçalho da seção -->
                                    <div class="bg-blue-50 p-4 rounded-lg shadow-sm mb-6 border border-blue-100">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-2 rounded-full mr-3 shadow-sm">
                                                <i class="fas fa-building text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800">{{ __('messages.company_information') }}</h3>
                                                <p class="text-sm text-blue-600">
                                                    <i class="fas fa-info-circle mr-1"></i> {{ __('messages.data_shown_in_reports') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Grid compacto de informações -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                        <!-- Nome da Empresa -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-blue-500">
                                                    <i class="fas fa-file-signature"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="company_name" class="block text-xs text-gray-500 mb-1">{{ __('messages.company_name') }} <span class="text-red-500">*</span></label>
                                                    <input type="text" 
                                                        wire:model.live="company_name"
                                                        id="company_name"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-900 focus:border-blue-400 focus:ring-blue-400"
                                                        placeholder="Dembena Industria e Comercio Lda">
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.company_name_help') }}</p>
                                                </div>
                                            </div>
                                            @error('company_name')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>

                                        <!-- CNPJ/CPF -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-blue-500">
                                                    <i class="fas fa-id-card"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="company_tax_id" class="block text-xs text-gray-500 mb-1">{{ __('messages.company_tax_id') }} <span class="text-red-500">*</span></label>
                                                    <input type="text" 
                                                        wire:model.live="company_tax_id"
                                                        id="company_tax_id"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-900 focus:border-blue-400 focus:ring-blue-400"
                                                        placeholder="5417601292">
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.company_tax_id_help') }}</p>
                                                </div>
                                            </div>
                                            @error('company_tax_id')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        
                                        <!-- Endereço -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-blue-500">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="company_address" class="block text-xs text-gray-500 mb-1">{{ __('messages.company_address') }}</label>
                                                    <input type="text" 
                                                        wire:model.live="company_address"
                                                        id="company_address"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-900 focus:border-blue-400 focus:ring-blue-400"
                                                        placeholder="Polo Industrial de Viana, Luanda, Angola">
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.company_address_help') }}</p>
                                                </div>
                                            </div>
                                            @error('company_address')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        
                                        <!-- Telefone -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-blue-500">
                                                    <i class="fas fa-phone-alt"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="company_phone" class="block text-xs text-gray-500 mb-1">{{ __('messages.company_phone') }}</label>
                                                    <input type="tel" 
                                                        wire:model.live="company_phone"
                                                        id="company_phone"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-900 focus:border-blue-400 focus:ring-blue-400"
                                                        placeholder="+244943268770">
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.company_phone_help') }}</p>
                                                </div>
                                            </div>
                                            @error('company_phone')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        
                                    <!-- Configurações Regionais -->
                                    <div class="bg-indigo-50 p-4 rounded-lg shadow-sm my-6 border border-indigo-100">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-2 rounded-full mr-3 shadow-sm">
                                                <i class="fas fa-globe-americas text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800">{{ __('messages.regional_settings') }}</h3>
                                                <p class="text-sm text-indigo-600">
                                                    <i class="fas fa-info-circle mr-1"></i> {{ __('messages.regional_settings_description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Grid de configurações regionais -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                        <!-- Time Zone -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-indigo-500">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="app_timezone" class="block text-xs text-gray-500 mb-1">{{ __('messages.timezone') }} <span class="text-red-500">*</span></label>
                                                    <select
                                                        wire:model.live="app_timezone"
                                                        id="app_timezone"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-700 focus:border-indigo-400 focus:ring-indigo-400">
                                                        <option value="UTC" selected>UTC (Tempo Universal Coordenado)</option>
                                                        <option value="Africa/Luanda">Africa/Luanda</option>
                                                        <option value="America/Sao_Paulo">America/Sao_Paulo</option>
                                                        <option value="Europe/Lisbon">Europe/Lisbon</option>
                                                    </select>
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.timezone_help') }}</p>
                                                </div>
                                            </div>
                                            @error('app_timezone')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        
                                        <!-- Date Format -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-indigo-500">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="date_format" class="block text-xs text-gray-500 mb-1">{{ __('messages.date_format') }} <span class="text-red-500">*</span></label>
                                                    <select
                                                        wire:model.live="date_format"
                                                        id="date_format"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-700 focus:border-indigo-400 focus:ring-indigo-400">
                                                        <option value="d/m/Y" selected>13/05/2025 (d/m/Y)</option>
                                                        <option value="m/d/Y">05/13/2025 (m/d/Y)</option>
                                                        <option value="Y-m-d">2025-05-13 (Y-m-d)</option>
                                                        <option value="d.m.Y">13.05.2025 (d.m.Y)</option>
                                                    </select>
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.date_format_help') }}</p>
                                                </div>
                                            </div>
                                            @error('date_format')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        
                                        <!-- Moeda -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-indigo-500">
                                                    <i class="fas fa-money-bill-wave"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="currency" class="block text-xs text-gray-500 mb-1">{{ __('messages.currency') }} <span class="text-red-500">*</span></label>
                                                    <select
                                                        wire:model.live="currency"
                                                        id="currency"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-700 focus:border-indigo-400 focus:ring-indigo-400">
                                                        <option value="BRL" selected>BRL - Brazilian Real</option>
                                                        <option value="USD">USD - US Dollar</option>
                                                        <option value="EUR">EUR - Euro</option>
                                                        <option value="AOA">AOA - Angolan Kwanza</option>
                                                    </select>
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.currency_help') }}</p>
                                                </div>
                                            </div>
                                            @error('currency')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        
                                        <!-- Idioma -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-indigo-500">
                                                    <i class="fas fa-language"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="language" class="block text-xs text-gray-500 mb-1">{{ __('messages.language') }} <span class="text-red-500">*</span></label>
                                                    <select
                                                        wire:model.live="language"
                                                        id="language"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-700 focus:border-indigo-400 focus:ring-indigo-400">
                                                        <option value="pt">Português</option>
                                                        <option value="en">English</option>
                                                    </select>
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.language_help') }}</p>
                                                </div>
                                            </div>
                                            @error('language')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Informações de Contato -->
                                    <div class="bg-green-50 p-4 rounded-lg shadow-sm my-6 border border-green-100">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-r from-green-600 to-teal-600 p-2 rounded-full mr-3 shadow-sm">
                                                <i class="fas fa-address-card text-white text-lg"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800">{{ __('messages.contact_information') }}</h3>
                                                <p class="text-sm text-green-600">
                                                    <i class="fas fa-info-circle mr-1"></i> {{ __('messages.contact_information_description') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Grid de informações de contato -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                        <!-- Email -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-green-500">
                                                    <i class="fas fa-envelope"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="company_email" class="block text-xs text-gray-500 mb-1">{{ __('messages.company_email') }}</label>
                                                    <input type="email" 
                                                        wire:model.live="company_email"
                                                        id="company_email"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-900 focus:border-green-400 focus:ring-green-400"
                                                        placeholder="Muler@dembenagroup.com">
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.company_email_help') }}</p>
                                                </div>
                                            </div>
                                            @error('company_email')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        
                                        <!-- Website -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-green-500">
                                                    <i class="fas fa-globe"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="company_website" class="block text-xs text-gray-500 mb-1">{{ __('messages.company_website') }}</label>
                                                    <input type="url" 
                                                        wire:model.live="company_website"
                                                        id="company_website"
                                                        class="block w-full rounded border-gray-300 shadow-sm text-gray-900 focus:border-green-400 focus:ring-green-400"
                                                        placeholder="www.dembenagroup.com">
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.company_website_help') }}</p>
                                                </div>
                                            </div>
                                            @error('company_website')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </div>
                                        
                                        <!-- Logo da Empresa -->
                                        <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm col-span-2">
                                            <div class="flex items-start mb-1">
                                                <div class="mr-2 mt-1 text-blue-500">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <label for="company_logo" class="block text-xs text-gray-500 mb-1">{{ __('messages.company_logo') }}</label>
                                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors duration-200">
                                                        <div class="space-y-1 text-center">
                                                            <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl"></i>
                                                            <div class="text-sm text-gray-600">
                                                                <label for="company_logo" class="relative cursor-pointer rounded-md font-medium text-blue-500 hover:text-blue-700 transition-colors duration-200">
                                                                    <span>{{ __('messages.upload_file') }}</span>
                                                                    <input 
                                                                        type="file"
                                                                        wire:model.live="company_logo"
                                                                        id="company_logo"
                                                                        class="sr-only" 
                                                                    />
                                                                </label>
                                                                <p>{{ __('messages.drag_drop') }}</p>
                                                            </div>
                                                            <p class="text-xs text-gray-500">SVG, PNG, JPG {{ __('messages.max_2mb') }}</p>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">{{ __('messages.company_logo_help') }}</p>
                                                </div>
                                            </div>
                                            
                                            @error('company_logo')
                                                <p class="mt-1 text-xs text-red-600 bg-red-50 p-1 rounded">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror

                                            @if ($company_logo)
                                                <div class="mt-3 flex items-center space-x-2 bg-gray-50 p-2 rounded border border-gray-200">
                                                    <div>
                                                        <p class="text-xs text-gray-500 font-medium">{{ __('messages.logo_preview') }}:</p>
                                                        <div class="mt-1">
                                                            <img src="{{ $company_logo->temporaryUrl() }}" alt="Logo preview" class="h-12 w-auto object-contain">
                                                        </div>
                                                    </div>
                                                    <button type="button" class="ml-auto bg-red-50 hover:bg-red-100 p-1.5 rounded-full text-red-500 hover:text-red-700 transition-colors duration-200" wire:click="removeTemporaryLogo" title="{{ __('messages.remove_image') }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    
                                    </div>
                                    
                                    <!-- Nota informativa -->
                                    <div class="col-span-1 md:col-span-2 mt-8 mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-lg p-4 shadow-sm">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 bg-blue-100 p-1.5 rounded-full">
                                                <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-blue-800">{{ __('messages.important_information') }}</h3>
                                                <div class="mt-2 text-sm text-blue-700">
                                                    <p>{{ __('messages.settings_saved_automatically') }}</p>
                                                    <ul class="mt-2 space-y-1 list-disc list-inside">
                                                        <li>{{ __('messages.company_settings_used_in_reports') }}</li>
                                                        <li>{{ __('messages.regional_settings_affect_display') }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                
                                <!-- Botão de salvar -->
                                <div class="flex justify-center md:justify-end space-x-4 mt-6 mb-8">
                                    <button
                                        type="button"
                                        wire:click="saveGeneralSettings"
                                        class="flex items-center justify-center px-8 py-3 rounded-lg text-white font-medium shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 space-x-2 min-w-[200px]">
                                        <i class="fas fa-save mr-2"></i>
                                        <span>{{ __('messages.save_changes') }}</span>
                                        <div wire:loading wire:target="saveGeneralSettings" class="ml-2">
                                            <i class="fas fa-spinner fa-spin text-white"></i>
                                        </div>
                                    </button>
                                    
                                    <button
                                        type="button"
                                        onclick="location.reload()"
                                        class="flex items-center justify-center px-4 py-3 rounded-lg text-gray-700 font-medium border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-all duration-200">
                                        <i class="fas fa-undo mr-2"></i>
                                        {{ __('messages.reset') }}
                                    </button>
                                </div>
                                
                                <!-- Indicador de salvamento com animação -->
                                <div 
                                    x-data="{ show: false }"
                                    x-init="
                                        Livewire.on('settingsSaved', () => {
                                            show = true;
                                            setTimeout(() => show = false, 3000);
                                        })
                                    "
                                    x-show="show"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0 transform translate-y-2"
                                    x-transition:enter-end="opacity-100 transform translate-y-0"
                                    x-transition:leave="transition ease-in duration-200"
                                    x-transition:leave-start="opacity-100 transform translate-y-0"
                                    x-transition:leave-end="opacity-0 transform translate-y-2"
                                    class="mt-3 text-sm text-green-600 flex items-center justify-end"
                                >
                                    <i class="fas fa-check-circle mr-1"></i> {{ __('messages.settings_saved_successfully') }}
                                </div>
                            </form>
                        </div>

                        <div class="{{ $activeTab === 'updates' ? 'block' : 'hidden' }}" role="tabpanel">
                            <!-- Header com navegação interna da aba -->
                            <div class="mb-6" x-data="{ activeSection: 'system' }">
                                <!-- Navegação com tabs animadas -->
                                <div class="flex border-b border-gray-200 mb-6 overflow-x-auto hide-scrollbar pb-1">
                                    <button 
                                        @click="$store.globals.activeSection = 'system'" 
                                        :class="{'border-b-2 border-blue-500 text-blue-600': $store.globals.activeSection === 'system', 'text-gray-500 hover:text-gray-700': $store.globals.activeSection !== 'system'}"
                                        class="px-4 py-2 font-medium text-sm flex items-center transition-all duration-200 hover:bg-gray-50 rounded-t-md mr-1"
                                    >
                                        <i class="fas fa-code-branch mr-2 transform transition-transform duration-300" :class="{'text-blue-600 rotate-12': $store.globals.activeSection === 'system'}"></i>
                                        {{ __('messages.system_version') }}
                                    </button>
                                    <button 
                                        @click="$store.globals.activeSection = 'maintenance'" 
                                        :class="{'border-b-2 border-purple-500 text-purple-600': $store.globals.activeSection === 'maintenance', 'text-gray-500 hover:text-gray-700': $store.globals.activeSection !== 'maintenance'}"
                                        class="px-4 py-2 font-medium text-sm flex items-center transition-all duration-200 hover:bg-gray-50 rounded-t-md mr-1"
                                    >
                                        <i class="fas fa-tools mr-2 transform transition-transform duration-300" :class="{'text-purple-600 rotate-12': $store.globals.activeSection === 'maintenance'}"></i>
                                        {{ __('messages.maintenance') }}
                                    </button>
                                    <button 
                                        @click="$store.globals.activeSection = 'disk'" 
                                        :class="{'border-b-2 border-green-500 text-green-600': $store.globals.activeSection === 'disk', 'text-gray-500 hover:text-gray-700': $store.globals.activeSection !== 'disk'}"
                                        class="px-4 py-2 font-medium text-sm flex items-center transition-all duration-200 hover:bg-gray-50 rounded-t-md"
                                    >
                                        <i class="fas fa-hdd mr-2 transform transition-transform duration-300" :class="{'text-green-600 rotate-12': $store.globals.activeSection === 'disk'}"></i>
                                        {{ __('messages.disk_usage') }}
                                    </button>
                                </div>

                                <!-- System Version Section - Com animações e design modernos -->
                                <div x-show="$store.globals.activeSection === 'system'" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform translate-y-4"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100 p-6 shadow-sm mb-6 overflow-hidden relative">
                                    
                                    <!-- Faixa decorativa superior -->
                                    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-blue-400 to-indigo-500"></div>
                                    
                                    <!-- Bolhas decorativas animadas -->
                                    <div class="absolute top-4 right-4 w-32 h-32 bg-blue-200 opacity-20 rounded-full blur-xl animate-pulse-slow"></div>
                                    <div class="absolute bottom-4 left-4 w-24 h-24 bg-indigo-200 opacity-20 rounded-full blur-xl animate-pulse-slow"></div>
                                    
                                    <!-- Conteúdo principal -->
                                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center relative z-10">
                                        <div class="mb-4 md:mb-0">
                                            <!-- Ícone e título com animação -->
                                            <div class="flex items-center mb-3 group">
                                                <div class="p-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg shadow-md mr-3 group-hover:shadow-lg transition-all duration-300 transform group-hover:scale-110">
                                                    <i class="fas fa-code-branch text-lg"></i>
                                                </div>
                                                <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-700 transition-colors duration-200">{{ __('messages.system_version') }}</h3>
                                            </div>
                                            
                                            <!-- Informações de versão com design moderno -->
                                            <div class="bg-white/80 backdrop-blur-sm rounded-lg p-4 border border-blue-100 shadow-sm space-y-3">
                                                <!-- Current version -->
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-md bg-blue-100 text-blue-500">
                                                        <i class="fas fa-tag"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-xs text-gray-500">{{ __('messages.current_version') }}</p>
                                                        <p class="font-semibold text-gray-800">
                                                            v{{ $current_version }}
                                                            <!-- Badge para versão atual -->
                                                            <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">{{ __('messages.stable') }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                
                                                <!-- Status da atualização -->
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-md"
                                                        :class="{
                                                            'bg-green-100 text-green-500': '{{ $update_status }}' === 'Up to date',
                                                            'bg-yellow-100 text-yellow-500': '{{ $update_status }}' === 'Update available',
                                                            'bg-gray-100 text-gray-500': '{{ $update_status }}' === 'Checking...'
                                                        }">
                                                        <i class="fas" 
                                                            :class="{
                                                                'fa-check-circle': '{{ $update_status }}' === 'Up to date',
                                                                'fa-exclamation-circle': '{{ $update_status }}' === 'Update available',
                                                                'fa-question-circle': '{{ $update_status }}' === 'Checking...'
                                                            }"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-xs text-gray-500">{{ __('messages.update_status') }}</p>
                                                        <p class="font-semibold" 
                                                            :class="{
                                                                'text-green-600': '{{ $update_status }}' === 'Up to date',
                                                                'text-yellow-600': '{{ $update_status }}' === 'Update available',
                                                                'text-gray-600': '{{ $update_status }}' === 'Checking...'
                                                            }">
                                                            {{ __('messages.'.str_replace(' ', '_', strtolower($update_status))) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Botão de verificar atualizações com design moderno e animação -->
                                        <div>
                                            <button
                                                wire:click="checkForUpdates"
                                                wire:loading.attr="disabled"
                                                wire:target="checkForUpdates"
                                                class="group relative inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105 overflow-hidden">
                                                <!-- Efeito de brilho no hover -->
                                                <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-white/0 via-white/30 to-white/0 -translate-x-full group-hover:translate-x-full transition-all duration-1000 ease-out"></span>
                                                
                                                <!-- Ícone animado -->
                                                <span class="relative flex items-center justify-center">
                                                    <i class="fas fa-sync-alt mr-2 transition-transform duration-300 group-hover:rotate-180" wire:loading.class="animate-spin" wire:target="checkForUpdates"></i>
                                                    <span wire:loading.remove wire:target="checkForUpdates">{{ __('messages.check_for_updates') }}</span>
                                                    <span wire:loading wire:target="checkForUpdates">{{ __('messages.checking') }}...</span>
                                                </span>
                                            </button>
                                            
                                            <!-- Timestamp da última verificação -->
                                            <p class="mt-2 text-xs text-gray-500 text-center">
                                                <i class="far fa-clock mr-1"></i> {{ __('messages.last_checked') }}: {{ now()->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
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
                                    </div>
                                    
                                    <!-- Conteúdo principal do formulário -->
                                    <div class="relative z-10">
                                        <!-- Mensagem informativa -->
                                        <div class="bg-white/80 backdrop-blur-sm rounded-lg p-4 border border-blue-100 shadow-sm mb-6">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 text-blue-500">
                                                    <i class="fas fa-info-circle text-lg"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-gray-600">{{ __('messages.update_settings_description') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Campo GitHub Repository -->
                                        <div class="bg-white/90 backdrop-blur-sm rounded-lg border border-blue-100 hover:border-blue-200 transition-all duration-200 p-5 mb-6 shadow-sm hover:shadow group">
                                            <div class="flex items-center justify-between mb-2">
                                                <label for="github_repository" class="font-semibold text-gray-800 flex items-center group-hover:text-blue-700 transition-colors duration-200">
                                                    <i class="fab fa-github mr-2 text-gray-600 group-hover:text-blue-600 transition-colors duration-200"></i>
                                                    {{ __('messages.github_repository') }}
                                                </label>
                                                <!-- Badge de serviço -->
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-code-branch mr-1"></i> Git
                                                </span>
                                            </div>
                                            
                                            <div class="relative mt-2">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 group-hover:text-blue-600 transition-colors duration-200">
                                                    <i class="fas fa-terminal"></i>
                                                </div>
                                                
                                                <input type="text" 
                                                       wire:model.live="github_repository" 
                                                       id="github_repository" 
                                                       class="pl-10 block w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-gray-700 hover:border-blue-300 transition-colors duration-200"
                                                       placeholder="username/repository">
                                                       
                                                <!-- Indicador de carregamento -->
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none" wire:loading wire:target="github_repository">
                                                    <i class="fas fa-spinner fa-spin text-blue-500"></i>
                                                </div>
                                            </div>
                                            
                                            <!-- Exemplo e ajuda -->
                                            <div class="mt-2 flex items-start text-xs text-gray-500">
                                                <i class="fas fa-info-circle mt-0.5 mr-1 text-blue-500"></i>
                                                <div>
                                                    <p>{{ __('messages.github_repository_help') }}</p>
                                                    <p class="mt-1"><span class="font-semibold">{{ __('messages.example') }}:</span> <code class="px-1.5 py-0.5 bg-gray-100 rounded text-blue-700">tiofox77/erpdembena</code></p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Campo para configurações adicionais (opcional) -->
                                        <div class="bg-white/90 backdrop-blur-sm rounded-lg border border-blue-100 hover:border-blue-200 transition-all duration-200 p-5 mb-6 shadow-sm hover:shadow group">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="font-semibold text-gray-800 flex items-center group-hover:text-blue-700 transition-colors duration-200">
                                                    <i class="fas fa-sliders-h mr-2 text-gray-600 group-hover:text-blue-600 transition-colors duration-200"></i>
                                                    {{ __('messages.update_preferences') }}
                                                </label>
                                            </div>
                                            
                                            <!-- Opções de configuração -->
                                            <div class="space-y-3 mt-3">
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.live="auto_check_updates" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4 transition duration-150 ease-in-out">
                                                    <span class="ml-2 text-sm text-gray-700">{{ __('messages.auto_check_updates') }}</span>
                                                </label>
                                                
                                                <label class="flex items-center">
                                                    <input type="checkbox" wire:model.live="backup_before_update" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4 transition duration-150 ease-in-out">
                                                    <span class="ml-2 text-sm text-gray-700">{{ __('messages.backup_before_update') }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Botão de salvar com feedback visual -->
                                    <div class="flex justify-end items-center mt-6 relative z-10">
                                        <!-- Feedback de sucesso -->
                                        <div x-show="$store.globals.formState === 'success'"
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 transform translate-x-4"
                                             x-transition:enter-end="opacity-100 transform translate-x-0"
                                             x-transition:leave="transition ease-in duration-200"
                                             x-transition:leave-start="opacity-100 transform translate-x-0"
                                             x-transition:leave-end="opacity-0 transform translate-x-4"
                                             class="mr-4 text-sm text-green-600 flex items-center">
                                            <i class="fas fa-check-circle mr-1"></i> {{ __('messages.settings_saved_successfully') }}
                                        </div>
                                        
                                        <!-- Botão de salvar -->
                                        <button type="submit"
                                                class="group relative inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105 overflow-hidden"
                                                :disabled="$store.globals.formState === 'submitting'">
                                            
                                            <!-- Efeito de brilho no hover -->
                                            <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-white/0 via-white/30 to-white/0 -translate-x-full group-hover:translate-x-full transition-all duration-1000 ease-out"></span>
                                            
                                            <!-- Ícone animado -->
                                            <span class="relative flex items-center justify-center">
                                                <i class="fas fa-save mr-2 group-hover:rotate-12 transition-transform duration-300" x-bind:class="{ 'fa-spinner fa-spin': $store.globals.formState === 'submitting' }"></i>
                                                <span>{{ __('messages.save_update_settings') }}</span>
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div class="{{ $activeTab === 'maintenance' ? 'block' : 'hidden' }}" role="tabpanel"
                             x-data="{ activeSection: 'settings' }"
                             x-init="setTimeout(() => { if ($wire.diskUsage.length === 0) $wire.checkDiskSpace(); if ($wire.systemLogs.length === 0) $wire.loadSystemLogs(); }, 500)">
                            
                            <!-- Quick Navigation -->
                            <div class="flex flex-wrap gap-2 mb-6 bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-sm">
                                <button 
                                    @click="$store.globals.activeSection = 'settings'"
                                    :class="$store.globals.activeSection === 'settings' ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                    class="flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 shadow-sm">
                                    <i class="fas fa-cogs mr-2"></i> Settings
                                </button>
                                <button 
                                    @click="$store.globals.activeSection = 'disk'"
                                    :class="$store.globals.activeSection === 'disk' ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                    class="flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 shadow-sm">
                                    <i class="fas fa-hdd mr-2"></i> Disk Usage
                                </button>
                                <button 
                                    @click="$store.globals.activeSection = 'tools'"
                                    :class="$store.globals.activeSection === 'tools' ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'" 
                                    class="flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 shadow-sm">
                                    <i class="fas fa-tools mr-2"></i> System Tools
                                </button>
                                <button 
                                    @click="$store.globals.activeSection = 'history'"
                                    :class="$store.globals.activeSection === 'history' ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                    class="flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 shadow-sm">
                                    <i class="fas fa-history mr-2"></i> Command History
                                </button>
                                <button 
                                    @click="$store.globals.activeSection = 'logs'"
                                    :class="$store.globals.activeSection === 'logs' ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                    class="flex items-center px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 shadow-sm">
                                    <i class="fas fa-clipboard-list mr-2"></i> System Logs
                                </button>
                            </div>
                            
                            <!-- Settings Section -->
                            <div x-show="$store.globals.activeSection === 'settings'" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform translate-y-4"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 class="mb-6 bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                <div class="flex items-center mb-4">
                                    <div class="p-2 rounded-full bg-gradient-to-r from-blue-600 to-blue-700 text-white mr-3">
                                        <i class="fas fa-cogs text-xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">Maintenance & Diagnostics</h3>
                                </div>

                                <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-md p-4 mb-6">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-400 h-5 w-5 animate-pulse"></i>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <p>Enabling maintenance mode will make the application inaccessible to users. Only administrators will be able to access the site.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form wire:submit.prevent="saveMaintenanceSettings" class="mb-4">
                                    <div class="space-y-4">
                                        <div class="flex items-center p-3 border border-gray-200 rounded-md hover:bg-gray-50 transition-colors duration-150">
                                            <span class="inline-flex items-center justify-center flex-shrink-0 h-10 w-10 rounded-md {{ $maintenance_mode ? 'bg-blue-500' : 'bg-gray-200' }} text-white transition-colors duration-150">
                                                <i class="fas {{ $maintenance_mode ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                            </span>
                                            <div class="ml-4 flex-grow">
                                                <label for="maintenance_mode" class="font-medium text-gray-700">Maintenance Mode</label>
                                                <p class="text-sm text-gray-500">When enabled, the site will show a maintenance page to regular users.</p>
                                            </div>
                                            <div class="ml-4">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input id="maintenance_mode" wire:model.live="maintenance_mode" type="checkbox" class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="flex items-center p-3 border border-gray-200 rounded-md hover:bg-gray-50 transition-colors duration-150">
                                            <span class="inline-flex items-center justify-center flex-shrink-0 h-10 w-10 rounded-md {{ $debug_mode ? 'bg-purple-500' : 'bg-gray-200' }} text-white transition-colors duration-150">
                                                <i class="fas {{ $debug_mode ? 'fa-bug' : 'fa-bug-slash' }}"></i>
                                            </span>
                                            <div class="ml-4 flex-grow">
                                                <label for="debug_mode" class="font-medium text-gray-700">Debug Mode</label>
                                                <p class="text-sm text-gray-500">Enables detailed error reporting and debugging information.</p>
                                            </div>
                                            <div class="ml-4">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input id="debug_mode" wire:model.live="debug_mode" type="checkbox" class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="pt-4">
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                                                <i class="fas fa-save mr-2"></i> Save Maintenance Settings
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                                
                                <!-- Disk Usage Section -->
                                <div x-show="$store.globals.activeSection === 'disk'" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform translate-y-4"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-100 p-6 shadow-sm mb-6 overflow-hidden relative">
                                    
                                    <!-- Faixa decorativa superior -->
                                    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-green-400 to-emerald-500"></div>
                                    
                                    <!-- Bolhas decorativas animadas -->
                                    <div class="absolute top-4 right-4 w-32 h-32 bg-green-200 opacity-20 rounded-full blur-xl animate-pulse-slow"></div>
                                    <div class="absolute bottom-4 left-4 w-24 h-24 bg-emerald-200 opacity-20 rounded-full blur-xl animate-pulse-slow"></div>
                                    
                                    <!-- Cabeçalho da seção -->
                                    <div class="flex items-center mb-6 group relative z-10">
                                        <div class="p-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg shadow-md mr-3 group-hover:shadow-lg transition-all duration-300 transform group-hover:scale-110">
                                            <i class="fas fa-hdd text-lg"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-green-700 transition-colors duration-200">{{ __('messages.disk_usage') }}</h3>
                                    </div>
                                    
                                    <!-- Botões de ação com design moderno e animações -->
                                    <div class="flex flex-wrap gap-3 mb-6 relative z-10">
                                        <button
                                            wire:click="checkDiskSpace"
                                            wire:loading.attr="disabled"
                                            wire:target="checkDiskSpace"
                                            class="group relative inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-md text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 transform hover:scale-105 overflow-hidden">
                                            <!-- Efeito de brilho no hover -->
                                            <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-white/0 via-white/30 to-white/0 -translate-x-full group-hover:translate-x-full transition-all duration-1000 ease-out"></span>
                                            
                                            <!-- Ícone animado -->
                                            <span class="relative flex items-center justify-center">
                                                <i class="fas fa-sync-alt mr-2 transition-transform duration-300 group-hover:rotate-180" wire:loading.class="animate-spin" wire:target="checkDiskSpace"></i>
                                                <span wire:loading.remove wire:target="checkDiskSpace">{{ __('messages.refresh_disk_info') }}</span>
                                                <span wire:loading wire:target="checkDiskSpace">{{ __('messages.refreshing') }}...</span>
                                            </span>
                                        </button>
                                        
                                        <button
                                            wire:click="clearDiskCache"
                                            wire:loading.attr="disabled"
                                            wire:target="clearDiskCache"
                                            class="group relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg shadow-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300 transform hover:scale-105 overflow-hidden">
                                            <!-- Efeito de brilho no hover -->
                                            <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-gray-50/0 via-gray-50/70 to-gray-50/0 -translate-x-full group-hover:translate-x-full transition-all duration-1000 ease-out"></span>
                                            
                                            <!-- Ícone animado -->
                                            <span class="relative flex items-center justify-center">
                                                <i class="fas fa-trash-alt mr-2 group-hover:text-red-500 transition-all duration-300" wire:loading.class="animate-spin" wire:target="clearDiskCache"></i>
                                                <span wire:loading.remove wire:target="clearDiskCache">{{ __('messages.clear_cache_files') }}</span>
                                                <span wire:loading wire:target="clearDiskCache">{{ __('messages.clearing') }}...</span>
                                            </span>
                                        </button>
                                    </div>
                                    
                                    <!-- Mensagem quando não há dados -->
                                    <div x-data="{ isLoading: @entangle('isLoading').defer }"
                                         x-show="!isLoading && {{ count($diskUsage ?? []) === 0 ? 'true' : 'false' }}"
                                         class="bg-white/80 backdrop-blur-sm rounded-lg p-8 border border-green-100 shadow-sm relative z-10 flex flex-col items-center justify-center text-center">
                                        <div class="bg-green-100 p-4 rounded-full mb-4">
                                            <i class="fas fa-info-circle text-green-600 text-2xl"></i>
                                        </div>
                                        <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ __('messages.no_disk_data_available') }}</h4>
                                        <p class="text-gray-600 max-w-md mb-4">{{ __('messages.click_refresh_to_check_disk') }}</p>
                                        <button
                                            wire:click="checkDiskSpace"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                            <i class="fas fa-sync-alt mr-2"></i>
                                            {{ __('messages.check_now') }}
                                        </button>
                                    </div>
                                    
                                    <!-- Indicador de carregamento -->
                                    <div x-data="{ isLoading: @entangle('isLoading').defer }"
                                         x-show="isLoading"
                                         class="bg-white/80 backdrop-blur-sm rounded-lg p-8 border border-green-100 shadow-sm relative z-10 flex flex-col items-center justify-center text-center">
                                        <div class="inline-block animate-spin mb-4">
                                            <i class="fas fa-circle-notch text-green-600 text-3xl"></i>
                                        </div>
                                        <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ __('messages.checking_disk_space') }}</h4>
                                        <p class="text-gray-600">{{ __('messages.please_wait') }}</p>
                                    </div>
                                    
                                    <!-- Conteúdo do uso de disco com animações e design moderno -->
                                    @if(isset($diskUsage) && count($diskUsage) > 0)
                                        <div class="space-y-4 relative z-10">
                                            <!-- Título da seção -->
                                            <div class="mb-4 text-center">
                                                <h4 class="text-sm uppercase tracking-wider text-gray-500 font-semibold">{{ __('messages.storage_overview') }}</h4>
                                            </div>
                                            <!-- Itens de disco -->
                                            @foreach($diskUsage as $item)
                                                <div class="bg-white/90 backdrop-blur-sm rounded-lg border border-green-100 hover:border-green-200 hover:shadow-md p-5 transition-all duration-300">
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4">
                                                        <div>
                                                            <div class="flex items-center">
                                                                <!-- Ícone dinâmico baseado no percentual de uso -->
                                                                @php 
                                                                    $percentage = isset($item['percentage']) ? (float) $item['percentage'] : 0;
                                                                    $textColorClass = $percentage > 85 ? 'text-red-500' : ($percentage > 70 ? 'text-yellow-500' : 'text-green-500');
                                                                    $iconClass = $percentage > 85 ? 'fa-exclamation-circle' : ($percentage > 70 ? 'fa-exclamation-triangle' : 'fa-check-circle');
                                                                @endphp
                                                                
                                                                <div class="p-2 rounded-md {{ $percentage > 85 ? 'bg-red-100' : ($percentage > 70 ? 'bg-yellow-100' : 'bg-green-100') }} mr-3">
                                                                    <i class="fas {{ $iconClass }} {{ $textColorClass }} text-lg"></i>
                                                                </div>
                                                                
                                                                <div>
                                                                    <h4 class="text-base font-semibold text-gray-900">{{ $item['label'] ?? __('messages.disk') }}</h4>
                                                                    <p class="text-xs text-gray-500 flex items-center mt-1">
                                                                        <i class="fas fa-folder mr-1"></i> {{ $item['path'] ?? 'N/A' }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mt-3 sm:mt-0 sm:text-right">
                                                            <div class="flex items-center justify-end">
                                                                <div class="text-right mr-2">
                                                                    <p class="text-sm font-semibold {{ $textColorClass }}">
                                                                        {{ $item['usage'] ?? '0 MB' }}
                                                                    </p>
                                                                    <p class="text-xs text-gray-500">{{ __('messages.of') }} {{ $item['total'] ?? '0 MB' }}</p>
                                                                </div>
                                                                
                                                                <!-- Badge com o percentual -->
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $percentage > 85 ? 'bg-red-100 text-red-800' : ($percentage > 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                                    {{ $percentage }}%
                                                                </span>
                                                            </div>
                                                            <p class="text-xs text-gray-500 mt-1">
                                                                {{ $item['free'] ?? '0 MB' }} {{ __('messages.available') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                   
                                                    <!-- Gráfico de progresso moderno com animação -->
                                                     <div class="mt-4">
                                                         <!-- Título e valor de porcentagem -->
                                                         <div class="flex items-center justify-between mb-2">
                                                             <span class="text-xs font-medium text-gray-600">{{ __('messages.storage_usage') }}</span>
                                                             <div class="flex items-center">
                                                                 <div class="w-2 h-2 rounded-full {{ $percentage > 85 ? 'bg-red-500' : ($percentage > 70 ? 'bg-yellow-500' : 'bg-green-500') }} mr-1"></div>
                                                                 <span class="text-xs font-bold {{ $percentage > 85 ? 'text-red-600' : ($percentage > 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                                                     {{ $percentage }}%
                                                                 </span>
                                                             </div>
                                                         </div>
                                                         
                                                         <!-- Barra de progresso com animação -->
                                                         <div class="h-2.5 w-full bg-gray-200 rounded-full overflow-hidden shadow-inner">
                                                             <div 
                                                                 class="h-full rounded-full {{ $percentage > 85 ? 'bg-gradient-to-r from-red-500 to-red-600' : ($percentage > 70 ? 'bg-gradient-to-r from-yellow-400 to-yellow-500' : 'bg-gradient-to-r from-green-400 to-green-600') }}" 
                                                                 style="width: 0%"
                                                                 x-data=""
                                                                 x-init="
                                                                     $nextTick(() => {
                                                                         setTimeout(() => {
                                                                             $el.style.transition = 'width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1)';
                                                                             $el.style.width = '{{ $percentage }}%';
                                                                         }, 300);
                                                                     })
                                                                 ">
                                                             </div>
                                                         </div>
                                                         
                                                         <!-- Marcadores na barra -->
                                                         <div class="flex justify-between mt-1 text-[10px] text-gray-400">
                                                             <span>0</span>
                                                             <span>25%</span>
                                                             <span>50%</span>
                                                             <span>75%</span>
                                                             <span>100%</span>
                                                         </div>
                                                     </div>
                                                     
                                                     <!-- Estatísticas detalhadas -->
                                                     <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 bg-gray-50 rounded-lg p-3 text-xs">
                                                         <div class="flex items-center">
                                                             <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5"></div>
                                                             <span class="text-gray-500">{{ __('messages.total') }}:</span>
                                                             <span class="ml-auto font-medium">{{ $item['total'] ?? 'N/A' }}</span>
                                                         </div>
                                                         <div class="flex items-center">
                                                             <div class="w-1.5 h-1.5 {{ $percentage > 85 ? 'bg-red-500' : ($percentage > 70 ? 'bg-yellow-500' : 'bg-green-500') }} rounded-full mr-1.5"></div>
                                                             <span class="text-gray-500">{{ __('messages.used') }}:</span>
                                                             <span class="ml-auto font-medium {{ $percentage > 85 ? 'text-red-600' : ($percentage > 70 ? 'text-yellow-600' : 'text-green-600') }}">{{ $item['usage'] ?? 'N/A' }}</span>
                                                         </div>
                                                         <div class="flex items-center">
                                                             <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5"></div>
                                                             <span class="text-gray-500">{{ __('messages.free') }}:</span>
                                                             <span class="ml-auto font-medium text-emerald-600">{{ $item['free'] ?? 'N/A' }}</span>
                                                         </div>
                                                         <div class="flex items-center">
                                                             <div class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></div>
                                                             <span class="text-gray-500">{{ __('messages.status') }}:</span>
                                                             <span class="ml-auto font-medium {{ $percentage > 85 ? 'text-red-600' : ($percentage > 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                                                 {{ $percentage > 85 ? __('messages.critical') : ($percentage > 70 ? __('messages.warning') : __('messages.good')) }}
                                                             </span>
                                                         </div>
                                                     </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <!-- Log File Section -->
                                            @if(isset($diskUsage['logs']))
                                                <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                                                    <h4 class="text-md font-semibold mb-2 flex items-center">
                                                        <i class="fas fa-file-alt text-indigo-500 mr-2"></i> Log File
                                                    </h4>
                                                    <div class="text-2xl font-bold text-gray-700">
                                                        {{ $diskUsage['logs']['main_log_size_formatted'] ?? 'N/A' }}
                                                    </div>
                                                    <div class="mt-2 text-sm">
                                                        <p class="text-gray-600">laravel.log</p>
                                                    </div>
                                                    <div class="mt-4">
                                                        <button 
                                                            type="button" 
                                                            wire:click="clearLogFile"
                                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                                            <i class="fas fa-trash-alt mr-1"></i> Clear log file
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Empty State -->
                                        <div class="bg-gray-50 p-6 rounded-lg text-center border border-gray-200">
                                            <div class="mb-4">
                                                <i class="fas fa-hdd text-gray-400 text-5xl"></i>
                                            </div>
                                            <p class="text-gray-600 mb-4">No disk usage information available yet.</p>
                                            <p class="text-sm text-gray-500">Click "Check Disk Space" to view disk usage information.</p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- System Tools Section -->
                                <div x-show="$store.globals.activeSection === 'tools'" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform translate-y-4"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     class="mb-6 bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="p-2 rounded-full bg-gradient-to-r from-purple-600 to-purple-700 text-white mr-3">
                                            <i class="fas fa-tools text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">System Commands</h3>
                                    </div>
                                    
                                    <div class="bg-blue-50 border-l-4 border-blue-400 rounded-md p-4 mb-6">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-info-circle text-blue-500 h-5 w-5"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-blue-800">Information</h3>
                                                <div class="mt-2 text-sm text-blue-700">
                                                    <p>These commands help maintain your application's performance and resolve various cache-related issues.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <!-- Cache Commands Group -->
                                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                                            <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                                                <h4 class="font-medium text-gray-800 flex items-center">
                                                    <i class="fas fa-broom text-blue-500 mr-2"></i> Cache Management
                                                </h4>
                                            </div>
                                            <div class="p-4 space-y-3">
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('optimize:clear')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('optimize:clear')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-broom mr-2 text-blue-500"></i>
                                                        Clear All Caches
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                                
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('config:clear')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('config:clear')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-cog mr-2 text-blue-500"></i>
                                                        Clear Config Cache
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                                
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('route:clear')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('route:clear')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-road mr-2 text-blue-500"></i>
                                                        Clear Route Cache
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- View and Application Commands Group -->
                                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                                            <div class="p-4 bg-gradient-to-r from-green-50 to-teal-50 border-b border-gray-200">
                                                <h4 class="font-medium text-gray-800 flex items-center">
                                                    <i class="fas fa-eye text-green-500 mr-2"></i> Application Tools
                                                </h4>
                                            </div>
                                            <div class="p-4 space-y-3">
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('view:clear')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('view:clear')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-eye mr-2 text-green-500"></i>
                                                        Clear View Cache
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                                
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('cache:clear')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('cache:clear')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-trash-alt mr-2 text-green-500"></i>
                                                        Clear App Cache
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                                
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('queue:restart')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('queue:restart')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-sync-alt mr-2 text-green-500"></i>
                                                        Restart Queue Workers
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Database Commands Group -->
                                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300">
                                            <div class="p-4 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-gray-200">
                                                <h4 class="font-medium text-gray-800 flex items-center">
                                                    <i class="fas fa-database text-purple-500 mr-2"></i> Database Tools
                                                </h4>
                                            </div>
                                            <div class="p-4 space-y-3">
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('migrate')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('migrate')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-arrow-circle-up mr-2 text-purple-500"></i>
                                                        Run Migrations
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                                
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('migrate:status')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('migrate:status')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-info-circle mr-2 text-purple-500"></i>
                                                        Migration Status
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                                
                                                <button
                                                    type="button"
                                                    wire:click="runArtisanCommand('db:seed')"
                                                    wire:loading.attr="disabled"
                                                    wire:target="runArtisanCommand('db:seed')"
                                                    class="w-full inline-flex items-center justify-between px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                                                    <span class="flex items-center">
                                                        <i class="fas fa-seedling mr-2 text-purple-500"></i>
                                                        Run Database Seeders
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            wire:click="showSeederModal = true"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 transform hover:scale-105">
                                            <i class="fas fa-database mr-2"></i>
                                            Advanced Database Tools
                                        </button>
                                        
                                        <button
                                            type="button"
                                            x-on:click="$store.globals.activeSection = 'history'"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200">
                                            <i class="fas fa-history mr-2"></i>
                                            View Command History
                                        </button>
                                    </div>
                                    
                                    <!-- Empty State (Only shown when no history is available) -->
                                    <div x-show="$store.globals.activeSection === 'history' && $store.globals.commandHistory.length === 0" class="bg-gray-50 p-6 rounded-lg text-center border border-gray-200">
                                        <div class="mb-4">
                                            <i class="fas fa-history text-gray-400 text-5xl"></i>
                                        </div>
                                        <p class="text-gray-600 mb-2">No command history available.</p>
                                        <p class="text-sm text-gray-500">Run system commands to build a history of your actions.</p>
                                    </div>
                                </div>
                                
                                <!-- Command History Section -->
                                <div x-show="$store.globals.activeSection === 'history'" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform translate-y-4"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     class="mb-6 bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="p-2 rounded-full bg-gradient-to-r from-amber-600 to-amber-700 text-white mr-3">
                                            <i class="fas fa-history text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">Command History</h3>
                                    </div>
                                    
                                    <div x-show="$store.globals.commandHistory && $store.globals.commandHistory.length > 0">
                                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead>
                                                    <tr class="bg-gradient-to-r from-amber-50 to-amber-100">
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-amber-800 uppercase tracking-wider">Command</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-amber-800 uppercase tracking-wider">Status</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-amber-800 uppercase tracking-wider">Time</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-amber-800 uppercase tracking-wider">Duration</th>
                                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-amber-800 uppercase tracking-wider">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($commandHistory as $cmd)
                                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            <div class="flex items-center">
                                                                <i class="fas fa-terminal text-gray-500 mr-2"></i>
                                                                {{ $cmd['command'] }}
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cmd['status'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                                                <i class="fas {{ $cmd['status'] === 'success' ? 'fa-check-circle mr-1' : 'fa-times-circle mr-1' }}"></i>
                                                                {{ ucfirst($cmd['status']) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <div class="flex items-center">
                                                                <i class="far fa-clock text-gray-400 mr-2"></i>
                                                                {{ $cmd['executed_at'] }}
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs">
                                                                {{ $cmd['execution_time'] }}
                                                            </span>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            <button
                                                                type="button"
                                                                wire:click="viewCommandOutput('{{ $cmd['id'] ?? '' }}')"
                                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200">
                                                                <i class="fas fa-eye mr-1"></i> View
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="mt-4 text-right">
                                            <button
                                                type="button"
                                                wire:click="clearCommandHistory"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200">
                                                <i class="fas fa-trash-alt mr-2"></i>
                                                Clear History
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div x-show="!$store.globals.commandHistory || $store.globals.commandHistory.length === 0" class="bg-gray-50 p-6 rounded-lg text-center border border-gray-200">
                                        <div class="mb-4">
                                            <i class="fas fa-history text-gray-400 text-5xl"></i>
                                        </div>
                                        <p class="text-gray-600 mb-2">No command history available.</p>
                                        <p class="text-sm text-gray-500">Run system commands to build a history of your actions.</p>
                                    </div>
                                </div>
                                
                                <!-- System Logs Section -->
                                <div x-show="$store.globals.activeSection === 'logs'" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 transform translate-y-4"
                                     x-transition:enter-end="opacity-100 transform translate-y-0"
                                     class="mb-6 bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
                                    <div class="flex items-center mb-4">
                                        <div class="p-2 rounded-full bg-gradient-to-r from-indigo-600 to-indigo-700 text-white mr-3">
                                            <i class="fas fa-clipboard-list text-xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900">System Logs</h3>
                                    </div>
                                    
                                    <div class="flex flex-wrap gap-2 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <div class="flex items-center">
                                            <span class="text-gray-700 text-sm mr-2">Filter by:</span>
                                        </div>
                                        <div class="flex-grow flex flex-wrap sm:flex-nowrap gap-2">
                                            <button 
                                                type="button" 
                                                wire:click="loadSystemLogs"
                                                wire:loading.attr="disabled"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105">
                                                <i class="fas fa-sync-alt mr-2" wire:loading.class="animate-spin" wire:target="loadSystemLogs"></i>
                                                <span wire:loading.remove wire:target="loadSystemLogs">Refresh Logs</span>
                                                <span wire:loading wire:target="loadSystemLogs">Loading...</span>
                                            </button>
                                            
                                            <div class="relative inline-block w-full sm:w-auto">
                                                <select 
                                                    wire:model.live="selectedLogType"
                                                    class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 bg-white">
                                                    @foreach($logTypes as $type)
                                                        <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                                    <i class="fas fa-filter text-indigo-500"></i>
                                                </div>
                                            </div>
                                            
                                            <div class="relative inline-block w-full sm:w-auto">
                                                <select 
                                                    wire:model.live="logLimit"
                                                    class="block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 bg-white">
                                                    <option value="25">25 entries</option>
                                                    <option value="50">50 entries</option>
                                                    <option value="100">100 entries</option>
                                                    <option value="200">200 entries</option>
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                                    <i class="fas fa-list-ol text-indigo-500"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if(count($systemLogs) > 0)
                                        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                            <div class="overflow-y-auto max-h-96 scrollbar-thin scrollbar-thumb-indigo-500 scrollbar-track-gray-100">
                                                @foreach($systemLogs as $log)
                                                    <div class="border-b border-gray-200 last:border-b-0">
                                                        <div class="px-4 py-3 cursor-pointer hover:bg-gray-50 transition-colors duration-150" 
                                                             x-data="{open: false}" 
                                                             @click="open = !open">
                                                            <div class="flex items-center">
                                                                <div class="mr-2">
                                                                    @if($log['level'] == 'error')
                                                                        <i class="fas fa-circle-exclamation text-red-500"></i>
                                                                    @elseif($log['level'] == 'warning')
                                                                        <i class="fas fa-triangle-exclamation text-yellow-500"></i>
                                                                    @elseif($log['level'] == 'info')
                                                                        <i class="fas fa-circle-info text-blue-500"></i>
                                                                    @elseif($log['level'] == 'debug')
                                                                        <i class="fas fa-bug text-gray-500"></i>
                                                                    @else
                                                                        <i class="fas fa-check-circle text-green-500"></i>
                                                                    @endif
                                                                </div>
                                                                <div class="w-32 text-xs text-gray-500 shrink-0 flex items-center">
                                                                    <i class="far fa-clock text-gray-400 mr-1"></i> 
                                                                    {{ $log['date'] }}
                                                                </div>
                                                                <div class="w-20 text-xs font-medium uppercase shrink-0">
                                                                    <span class="px-2 py-1 rounded-full {{ 
                                                                        $log['level'] == 'error' ? 'bg-red-100 text-red-800 border border-red-200' : 
                                                                        ($log['level'] == 'warning' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : 
                                                                        ($log['level'] == 'info' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 
                                                                        ($log['level'] == 'debug' ? 'bg-gray-100 text-gray-800 border border-gray-200' : 'bg-green-100 text-green-800 border border-green-200'))) 
                                                                    }}">
                                                                        {{ $log['level'] }}
                                                                    </span>
                                                                </div>
                                                                <div class="ml-4 text-sm text-gray-700 truncate flex-grow">{{ $log['message'] }}</div>
                                                                <div>
                                                                    <i class="fas fa-chevron-down text-gray-400 transform transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                                                </div>
                                                            </div>
                                                            <div x-show="open" 
                                                                 x-transition:enter="transition ease-out duration-200"
                                                                 x-transition:enter-start="opacity-0 transform -translate-y-1"
                                                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                                                 class="mt-2 whitespace-pre-wrap text-xs font-mono text-gray-600 bg-gray-50 p-4 rounded-md border border-gray-200">
                                                                {{ $log['full_text'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="bg-gray-50 py-2 px-4 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
                                                <span>{{ count($systemLogs) }} log entries displayed</span>
                                                <button 
                                                    type="button"
                                                    wire:click="clearSystemLogs"
                                                    class="text-indigo-600 hover:text-indigo-800 flex items-center">
                                                    <i class="fas fa-trash-alt mr-1"></i> Clear Logs
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 p-6 rounded-lg text-center border border-gray-200">
                                            <div class="mb-4">
                                                <i class="fas fa-clipboard-check text-gray-400 text-5xl"></i>
                                            </div>
                                            <p class="text-gray-600 mb-2">No log entries found.</p>
                                            <p class="text-sm text-gray-500">Your system is running smoothly with no logged issues.</p>
                                        </div>
                                    @endif
                                </div>
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
                        </div>

                        <!-- Database Analysis Tab -->
                        <div class="{{ $activeTab === 'database' ? 'block' : 'hidden' }}" role="tabpanel">
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Análise do Banco de Dados SQL</h3>
                                <p class="mt-1 text-sm text-gray-600">Verifique todas as tabelas do banco de dados, espaço ocupado e estado de funcionamento.</p>
                            </div>

                            <div class="mb-6">
                                <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    wire:click="analyzeDatabaseSQL" wire:loading.attr="disabled">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    <span wire:loading.remove wire:target="analyzeDatabaseSQL">Analisar Banco de Dados</span>
                                    <span wire:loading wire:target="analyzeDatabaseSQL">Analisando...</span>
                                </button>
                            </div>

                            @if($databaseInfo)
                            <!-- Database information cards -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <h4 class="text-md font-semibold mb-2">Informações Gerais</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Nome do Banco:</span>
                                            <span class="text-sm font-medium">{{ $databaseInfo['name'] }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Driver:</span>
                                            <span class="text-sm font-medium">{{ $databaseInfo['driver'] }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Versão:</span>
                                            <span class="text-sm font-medium">{{ $databaseInfo['version'] }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Charset:</span>
                                            <span class="text-sm font-medium">{{ $databaseInfo['charset'] }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Collation:</span>
                                            <span class="text-sm font-medium">{{ $databaseInfo['collation'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <h4 class="text-md font-semibold mb-2">Estatísticas</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Total de Tabelas:</span>
                                            <span class="text-sm font-medium">{{ count($databaseTables) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Tamanho Total:</span>
                                            <span class="text-sm font-medium">{{ number_format($databaseSize, 2) }} MB</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-600">Tabelas com Problemas:</span>
                                            <span class="text-sm font-medium">{{ count(array_filter($databaseTables, fn($table) => $table['status'] != 'healthy')) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min(100, $databaseSize / 10) }}%"></div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Uso de espaço: {{ number_format($databaseSize, 2) }} MB</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Database Tables List -->
                            <div class="overflow-x-auto rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tabela</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Engine</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registros</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamanho</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atualização</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($databaseTables as $table)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 flex items-center">
                                                        @if($table['has_primary_key'])
                                                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2" title="Possui chave primária"></span>
                                                        @else
                                                            <span class="w-2 h-2 bg-amber-400 rounded-full mr-2" title="Sem chave primária"></span>
                                                        @endif
                                                        {{ $table['name'] }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-500">{{ $table['engine'] }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-500">{{ number_format($table['rows']) }}</div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900">
                                                        {{ number_format($table['total_size'], 2) }} MB
                                                        <div class="text-xs text-gray-500">
                                                            Dados: {{ number_format($table['data_size'], 2) }} MB / 
                                                            Índices: {{ number_format($table['index_size'], 2) }} MB
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $table['status'] == 'healthy' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        {{ $table['status'] == 'healthy' ? 'Saudável' : 'Atenção' }}
                                                    </span>
                                                    @if(!empty($table['issues']))
                                                        <div class="text-xs text-red-600 mt-1">
                                                            {{ implode(', ', $table['issues']) }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-500">{{ $table['updated_at'] ?? 'N/A' }}</div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Nenhuma tabela encontrada
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="p-6 text-center text-gray-500">
                                <i class="fas fa-database text-4xl mb-3"></i>
                                <p>Clique no botão "Analisar Banco de Dados" para visualizar as informações.</p>
                            </div>
                            @endif
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
    @if(isset($showConfirmationModal) && $showConfirmationModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>
                    {{ __('messages.confirm_action') }}
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="cancelConfirmation">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600">{{ $confirmationMessage }}</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    wire:click="cancelConfirmation">
                    <i class="fas fa-times mr-1"></i> {{ __('messages.cancel') }}
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

    <!-- Command Output Modal -->
    @if(isset($showCommandOutputModal) && $showCommandOutputModal && isset($selectedCommand))
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-terminal mr-2 text-indigo-500"></i>
                    Command Output
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="$set('showCommandOutputModal', false)">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4 bg-gray-100 p-3 rounded-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-sm font-mono bg-gray-200 px-3 py-1 rounded-md">
                        <span>{{ $selectedCommand['command'] }}</span>
                    </div>
                    <div>
                        <span class="px-2 py-1 text-xs leading-5 font-semibold rounded-full {{ $selectedCommand['status'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                            <i class="fas {{ $selectedCommand['status'] === 'success' ? 'fa-check-circle mr-1' : 'fa-times-circle mr-1' }}"></i>
                            {{ ucfirst($selectedCommand['status']) }}
                        </span>
                    </div>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-600">
                    <div class="flex items-center">
                        <i class="far fa-clock text-gray-400 mr-1"></i> 
                        <span>Executed: {{ $selectedCommand['executed_at'] }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-stopwatch text-gray-400 mr-1"></i>
                        <span>Duration: {{ $selectedCommand['execution_time'] }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-900 rounded-md p-4 text-gray-100 font-mono text-sm overflow-y-auto flex-grow">
                <div class="whitespace-pre-wrap break-words max-h-96 overflow-y-auto">{{ $selectedCommand['output'] ?: 'No output available.' }}</div>
            </div>

            <div class="flex justify-end space-x-3 mt-4">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    wire:click="$set('showCommandOutputModal', false)">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button
                    type="button"
                    onclick="navigator.clipboard.writeText('{{ addslashes($selectedCommand['output'] ?: 'No output available.') }}').then(() => { Livewire.dispatch('notify', {type: 'success', message: 'Output copied to clipboard'}) })"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-copy mr-1"></i> Copy Output
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Seeder Modal -->
    @if(isset($showSeederModal) && $showSeederModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-database mr-2 text-indigo-500"></i>
                    {{ __('messages.run_database_seeder') }}
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeSeederModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <label for="selectedSeeder" class="block text-sm font-medium text-gray-700">{{ __('messages.select_seeder') }}</label>
                <select id="selectedSeeder" wire:model="selectedSeeder" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">{{ __('messages.select_option') }}</option>
                    @foreach($availableSeeders as $seederClass)
                        <option value="{{ $seederClass }}">{{ $seederClass }}</option>
                    @endforeach
                </select>
            </div>
            
            @if(isset($runningSeeder) && $runningSeeder)
                <div class="mt-4 p-2 bg-gray-100 rounded">
                    <p class="text-sm text-gray-700 mb-2">{{ __('messages.seeder_running') }}</p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-indigo-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
                    </div>
                </div>
            @endif
            
            @if(isset($seederOutput) && $seederOutput)
                <div class="mt-4 p-2 bg-gray-100 rounded max-h-40 overflow-y-auto">
                    <pre class="text-xs text-gray-700">{{ $seederOutput }}</pre>
                </div>
            @endif

            <div class="flex justify-end space-x-3 mt-6">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    wire:click="closeSeederModal"
                    @if(isset($runningSeeder) && $runningSeeder) disabled @endif>
                    <i class="fas fa-times mr-1"></i> {{ __('messages.close') }}
                </button>
                <button
                    type="button"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 @if((isset($runningSeeder) && $runningSeeder) || !isset($selectedSeeder) || empty($selectedSeeder)) opacity-50 cursor-not-allowed @endif"
                    wire:click="runSeeder"
                    @if((isset($runningSeeder) && $runningSeeder) || !isset($selectedSeeder) || empty($selectedSeeder)) disabled @endif>
                    <i class="fas fa-play mr-1"></i> {{ __('messages.run_seeder') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
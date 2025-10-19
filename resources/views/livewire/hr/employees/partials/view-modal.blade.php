<!-- View Employee Modal -->
<!-- NOTA: Este é um placeholder. O conteúdo completo deve ser copiado das linhas 2380-2960 do arquivo original employees.blade.php -->
<!-- A visualização inclui: Personal Info, Employment, Salary & Benefits, Documents, Emergency Contact -->

@if($showViewModal && $viewEmployee)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden m-4 transform transition-all duration-300 ease-out">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <div class="bg-white/20 p-2 rounded-lg mr-3">
                        <i class="fas fa-user text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-white">{{ __('messages.employee_details') }}</h3>
                        <p class="text-indigo-100 text-sm">{{ $viewEmployee->full_name }}</p>
                    </div>
                </div>
                <button type="button" 
                    class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-2 transition-all duration-200" 
                    wire:click="closeViewModal">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Expiring Documents Alert -->
        @if($expiringDocuments->count() > 0)
        <div class="mx-6 mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-amber-500 mr-3 animate-pulse"></i>
                <div>
                    <h4 class="font-semibold text-amber-800">{{ __('messages.documents_expiring_soon') }}</h4>
                    <p class="text-sm text-amber-700">{{ __('messages.documents_expire_within_30_days', ['count' => $expiringDocuments->count()]) }}</p>
                </div>
            </div>
            <div class="mt-3 space-y-2">
                @foreach($expiringDocuments as $document)
                <div class="flex items-center justify-between bg-white rounded-lg p-3 border border-amber-200">
                    <div class="flex items-center">
                        <i class="fas fa-file-alt text-amber-600 mr-2"></i>
                        <span class="font-medium text-gray-800">{{ $document->title }}</span>
                    </div>
                    <span class="text-sm font-medium text-amber-700">
                        {{ __('messages.expires_on') }}: {{ \Carbon\Carbon::parse($document->expiry_date)->format('d/m/Y') }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Modal Content -->
        <div class="max-h-[calc(90vh-150px)] overflow-y-auto p-4 sm:p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                <!-- Employee Information -->
                <div class="lg:col-span-2 space-y-6">
                    {{-- 
                        IMPORTANTE: Copiar aqui todo o conteúdo de visualização do arquivo original
                        Linhas 2434-2850 aproximadamente
                        
                        Seções incluídas:
                        1. Personal Information (Nome, Data de Nascimento, Gênero, ID, Telefone, Email)
                        2. Employment Information (Departamento, Posição, Data de Contratação, Status)
                        3. Salary & Benefits (Salário Base, Taxa Horária, Benefícios)
                        4. Emergency Contact (Nome, Relacionamento, Telefone, Endereço)
                        
                        Cada seção tem seu próprio card com gradiente e ícones.
                    --}}
                    
                    <!-- Placeholder: As seções de informação devem ser inseridas aqui -->
                    <div class="text-center py-12 bg-gray-50 rounded-xl">
                        <i class="fas fa-eye text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">
                            Este arquivo é um placeholder.<br>
                            Copie o conteúdo completo das seções de visualização das linhas 2434-2850 do arquivo original.
                        </p>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-4">
                    {{-- 
                        IMPORTANTE: Copiar aqui o sidebar com:
                        - Foto do funcionário
                        - Estatísticas rápidas
                        - Documentos
                        - Ações rápidas
                        
                        Linhas 2850-2960 aproximadamente
                    --}}
                    
                    <!-- Placeholder: Sidebar deve ser inserido aqui -->
                    <div class="text-center py-8 bg-gray-50 rounded-xl">
                        <i class="fas fa-image text-gray-300 text-4xl mb-2"></i>
                        <p class="text-sm text-gray-500">Sidebar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="border-t border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 rounded-b-xl">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <button type="button" 
                        wire:click="openDocumentsModal({{ $viewEmployee->id }})"
                        class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-lg shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-file-upload mr-2"></i>
                        {{ __('messages.upload_document') }}
                    </button>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" wire:click="closeViewModal"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('messages.close') }}
                    </button>
                    <button type="button" 
                        wire:click="edit({{ $viewEmployee->id }})"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition-all duration-200 hover:scale-105">
                        <i class="fas fa-edit mr-2"></i>
                        {{ __('messages.edit') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

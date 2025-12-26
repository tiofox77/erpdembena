<!-- Modal de Visualização de Overtime -->
<div x-data="{ open: @entangle('showViewModal') }" 
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
    
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" 
             class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-4xl sm:w-full"
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="transform opacity-0 scale-95" 
             x-transition:enter-end="transform opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="transform opacity-100 scale-100" 
             x-transition:leave-end="transform opacity-0 scale-95">
            
            <!-- Cabeçalho da Modal -->
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 rounded-t-lg px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-medium text-white flex items-center">
                    <i class="fas fa-eye mr-3"></i>
                    {{ __('messages.view_overtime') }}
                </h3>
                <button type="button" wire:click="closeViewModal" 
                        class="text-white hover:text-gray-200 focus:outline-none transition-all duration-200 ease-in-out transform hover:scale-110">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Corpo da Modal -->
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                
                <!-- Informações do Funcionário -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.employee_information') }}</h3>
                    </div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.employee') }}</h4>
                            <p class="text-sm font-medium text-gray-800">{{ $employee_name }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.date') }}</h4>
                            <p class="text-sm font-medium text-gray-800">{{ $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detalhes do Overtime -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-clock text-green-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.overtime_details') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        @if($input_type === 'time_range')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-xs font-medium text-gray-500">{{ __('messages.start_time') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ $start_time ?? '-' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-xs font-medium text-gray-500">{{ __('messages.end_time') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ $end_time ?? '-' }}</p>
                                </div>
                            </div>
                        @else
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.direct_hours') }} ({{ ucfirst($period_type) }})</h4>
                                <p class="text-sm font-medium text-gray-800">{{ number_format($direct_hours ?? 0, 2) }} {{ __('messages.hours') }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.hours') }}</h4>
                                <p class="text-sm font-medium text-gray-800">{{ number_format($hours ?? 0, 2) }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.hourly_rate') }}</h4>
                                <p class="text-sm font-medium text-gray-800">{{ number_format($rate ?? 0, 2) }} AOA</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.total_amount') }}</h4>
                                <p class="text-sm font-bold text-green-600">{{ number_format($amount ?? 0, 2) }} AOA</p>
                            </div>
                        </div>

                        @if($description)
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.description') }}</h4>
                                <p class="text-sm text-gray-800">{{ $description }}</p>
                            </div>
                        @endif

                        <div>
                            <h4 class="text-xs font-medium text-gray-500">{{ __('messages.status') }}</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($status === 'approved') bg-green-100 text-green-800
                                @elseif($status === 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ __(ucfirst($status)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Informações de Auditoria -->
                <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                    <div class="flex items-center bg-gradient-to-r from-purple-50 to-purple-100 px-4 py-3 border-b border-gray-200">
                        <i class="fas fa-info-circle text-purple-600 mr-2"></i>
                        <h3 class="text-base font-medium text-gray-700">{{ __('messages.audit_information') }}</h3>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.created_by') }}</h4>
                                <p class="text-sm font-medium text-gray-800">{{ $creator_name ?? '-' }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.created_at') }}</h4>
                                <p class="text-sm font-medium text-gray-800">{{ $created_at ? \Carbon\Carbon::parse($created_at)->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                            <div>
                                <h4 class="text-xs font-medium text-gray-500">{{ __('messages.updated_at') }}</h4>
                                <p class="text-sm font-medium text-gray-800">{{ $updated_at ? \Carbon\Carbon::parse($updated_at)->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                        </div>

                        @if($status !== 'pending' && $approver_name)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t">
                                <div>
                                    <h4 class="text-xs font-medium text-gray-500">{{ __('messages.approved_by') }}</h4>
                                    <p class="text-sm font-medium text-gray-800">{{ $approver_name }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Rodapé da Modal -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end border-t border-gray-200">
                <button type="button" wire:click="closeViewModal" 
                    class="inline-flex justify-center items-center px-6 py-2 bg-white border border-gray-300 rounded-md font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    {{ __('messages.close') }}
                </button>
            </div>

        </div>
    </div>
</div>

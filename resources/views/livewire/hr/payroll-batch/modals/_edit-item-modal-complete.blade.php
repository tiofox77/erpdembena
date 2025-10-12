{{-- Complete Edit Batch Item Modal - Similar to Individual Payroll Processing --}}
@if($showEditItemModal && $editingItem && !empty($calculatedData))
<div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50" wire:key="edit-item-modal-complete">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-[98vw] mx-4 max-h-[98vh] overflow-hidden flex flex-col">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-orange-600 to-orange-700 p-6 text-white flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-edit text-3xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-3">
                            <h2 class="text-3xl font-bold">{{ $editingItem->employee->full_name }}</h2>
                            @if($this->isEmployeeOnLeave($editingItem->employee_id, $editingItem->payrollBatch->payroll_period_id))
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/30 text-white border-2 border-white/50 backdrop-blur-sm">
                                    <i class="fas fa-umbrella-beach mr-2"></i>
                                    Em Férias
                                </span>
                            @endif
                        </div>
                        <p class="text-orange-100 text-lg">BI: {{ $editingItem->employee->id_card }} | {{ $editingItem->employee->department->name ?? 'N/A' }}</p>
                        <p class="text-orange-200 text-sm mt-1">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Período: {{ $editingItem->payrollBatch->payrollPeriod->name }}
                        </p>
                    </div>
                </div>
                <button wire:click="closeEditItemModal" class="text-white/80 hover:text-white p-2 hover:bg-white/10 rounded-lg transition">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        {{-- Editable Fields Section --}}
        <div class="bg-gradient-to-r from-teal-50 to-cyan-50 border-b border-teal-200 p-6 flex-shrink-0">
            <h3 class="text-lg font-bold text-teal-800 mb-4 flex items-center">
                <i class="fas fa-edit text-teal-600 mr-2"></i>
                Campos Editáveis - Ajuste o Pagamento
            </h3>
            
            {{-- Primeira Linha: Valores Monetários --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                
                {{-- Additional Bonus --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-plus-circle text-teal-600 mr-1"></i>
                        Bónus Adicional
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01"
                            min="0"
                            wire:model.live="edit_additional_bonus"
                            class="w-full px-4 py-3 text-lg border border-gray-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-white"
                            placeholder="0.00 AOA"
                        >
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">AOA</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Bónus extra para este pagamento</p>
                </div>

                {{-- Overtime Amount --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-clock text-orange-600 mr-1"></i>
                        Horas Extras
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01"
                            min="0"
                            wire:model.live="edit_overtime_amount"
                            class="w-full px-4 py-3 text-lg border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white"
                            placeholder="0.00 AOA"
                        >
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">AOA</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Valor adicional de horas extras</p>
                </div>

                {{-- Advance Deduction --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-hand-holding-usd text-red-600 mr-1"></i>
                        Adiantamento
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01"
                            min="0"
                            wire:model.live="edit_advance_deduction"
                            class="w-full px-4 py-3 text-lg border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white"
                            placeholder="0.00 AOA"
                        >
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">AOA</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Dedução de adiantamento salarial</p>
                </div>

            </div>

            {{-- Segunda Linha: Subsídios (Checkboxes) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Christmas Subsidy --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-gift text-emerald-600 mr-1"></i>
                        Subsídio de Natal (50%)
                    </label>
                    <div class="flex items-center h-[52px] bg-white border border-gray-300 rounded-xl px-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:model.live="edit_christmas_subsidy"
                                class="w-5 h-5 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2"
                            >
                            <span class="ml-3 text-sm font-medium text-gray-700">
                                Incluir Subsídio de Natal
                            </span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">50% do salário base</p>
                </div>

                {{-- Vacation Subsidy --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-umbrella-beach text-blue-600 mr-1"></i>
                        Subsídio de Férias (50%)
                    </label>
                    <div class="flex items-center h-[52px] bg-white border border-gray-300 rounded-xl px-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input 
                                type="checkbox" 
                                wire:model.live="edit_vacation_subsidy"
                                class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                            >
                            <span class="ml-3 text-sm font-medium text-gray-700">
                                Incluir Subsídio de Férias
                            </span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">50% do salário base</p>
                </div>

            </div>
            
            {{-- Recalculation Notice --}}
            <div class="mt-4 bg-blue-100 border border-blue-300 rounded-lg p-3 flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                <p class="text-sm text-blue-800">
                    <strong>Nota:</strong> Os valores serão recalculados automaticamente ao alterar os campos acima.
                </p>
            </div>
        </div>

        {{-- Information Cards Section --}}
        <div class="bg-gray-50 border-y border-gray-200 p-6 flex-shrink-0">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                {{-- Overtime Records Card --}}
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-white text-lg"></i>
                        </div>
                        <h4 class="ml-3 font-bold text-purple-900">Overtime Records</h4>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-purple-700 font-medium mb-1">Total Overtime Hours</p>
                            <p class="text-lg font-bold text-purple-900">{{ number_format($calculatedData['total_overtime_hours'] ?? 0, 2) }}h</p>
                        </div>
                        <div>
                            <p class="text-xs text-purple-700 font-medium mb-1">Overtime Amount</p>
                            <p class="text-lg font-bold text-purple-900">{{ number_format($calculatedData['total_overtime_amount'] ?? 0, 2) }} AOA</p>
                        </div>
                    </div>
                </div>

                {{-- Salary Advances Card --}}
                <div class="bg-gradient-to-br from-orange-50 to-amber-100 rounded-xl p-4 border border-orange-200">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-orange-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-hand-holding-usd text-white text-lg"></i>
                        </div>
                        <h4 class="ml-3 font-bold text-orange-900">Salary Advances</h4>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-orange-700 font-medium mb-1">Total Advances</p>
                            <p class="text-lg font-bold text-orange-900">{{ number_format($calculatedData['total_advances'] ?? 0, 2) }} AOA</p>
                        </div>
                        <div>
                            <p class="text-xs text-orange-700 font-medium mb-1">Deduction Amount</p>
                            <p class="text-lg font-bold text-orange-900">{{ number_format($calculatedData['advance_deduction'] ?? 0, 2) }} AOA</p>
                        </div>
                    </div>
                </div>

                {{-- Salary Discounts Card --}}
                <div class="bg-gradient-to-br from-red-50 to-pink-100 rounded-xl p-4 border border-red-200">
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-minus-circle text-white text-lg"></i>
                        </div>
                        <h4 class="ml-3 font-bold text-red-900">Salary Discounts</h4>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-red-700 font-medium mb-1">Total Discounts</p>
                            <p class="text-lg font-bold text-red-900">{{ number_format($calculatedData['total_salary_discounts'] ?? 0, 2) }} AOA</p>
                        </div>
                        <div>
                            <p class="text-xs text-red-700 font-medium mb-1">Active Discounts</p>
                            <p class="text-lg font-bold text-red-900">{{ count($calculatedData['salary_discount_records'] ?? []) }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Content - Two Column Layout (INCLUDE DO SUMMARY) --}}
        <div class="flex flex-1 overflow-hidden max-h-[calc(98vh-350px)]">
            @include('livewire.hr.payroll-batch.modals._edit-item-summary')
        </div>

        {{-- Footer with Action Buttons --}}
        <div class="flex-shrink-0 bg-gray-50 border-t border-gray-200 p-6">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    Os valores são calculados automaticamente pelo sistema
                </div>
                <div class="flex space-x-3">
                    <button
                        wire:click="closeEditItemModal"
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-colors"
                    >
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    <button
                        wire:click="saveEditedItem"
                        class="px-8 py-3 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105"
                    >
                        <i class="fas fa-save mr-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endif

{{-- Edit Batch Item Modal --}}
@if($showEditItemModal && $editingItem)
<div class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50" wire:key="edit-item-modal">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-orange-600 to-orange-700 p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg">
                        <i class="fas fa-edit text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Editar Item do Lote</h2>
                        <p class="text-orange-100">{{ $editingItem->employee->full_name }}</p>
                    </div>
                </div>
                <button wire:click="closeEditItemModal" class="text-white/80 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
            
            {{-- Employee Info --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-blue-600 font-medium">Funcionário:</span>
                        <span class="text-gray-900 ml-2">{{ $editingItem->employee->full_name }}</span>
                    </div>
                    <div>
                        <span class="text-blue-600 font-medium">BI:</span>
                        <span class="text-gray-900 ml-2">{{ $editingItem->employee->id_card }}</span>
                    </div>
                    <div>
                        <span class="text-blue-600 font-medium">Departamento:</span>
                        <span class="text-gray-900 ml-2">{{ $editingItem->employee->department->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-blue-600 font-medium">Status:</span>
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 ml-2">
                            {{ $editingItem->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Edit Form --}}
            <div class="space-y-5">
                
                {{-- Gross Salary --}}
                <div>
                    <label for="edit_gross_salary" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                        Salário Bruto (AOA)
                    </label>
                    <input
                        type="number"
                        id="edit_gross_salary"
                        wire:model.defer="edit_gross_salary"
                        step="0.01"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-lg font-semibold"
                        placeholder="0.00"
                    >
                    @error('edit_gross_salary') 
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Total Deductions --}}
                <div>
                    <label for="edit_total_deductions" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-minus-circle text-red-500 mr-2"></i>
                        Total de Deduções (AOA)
                    </label>
                    <input
                        type="number"
                        id="edit_total_deductions"
                        wire:model.defer="edit_total_deductions"
                        step="0.01"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-lg font-semibold"
                        placeholder="0.00"
                    >
                    @error('edit_total_deductions') 
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Net Salary --}}
                <div>
                    <label for="edit_net_salary" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-hand-holding-usd text-blue-500 mr-2"></i>
                        Salário Líquido (AOA)
                    </label>
                    <input
                        type="number"
                        id="edit_net_salary"
                        wire:model.defer="edit_net_salary"
                        step="0.01"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-lg font-semibold bg-blue-50"
                        placeholder="0.00"
                    >
                    @error('edit_net_salary') 
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label for="edit_notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                        Observações
                    </label>
                    <textarea
                        id="edit_notes"
                        wire:model.defer="edit_notes"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Adicione observações sobre as alterações (opcional)"
                    ></textarea>
                    @error('edit_notes') 
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Calculation Helper --}}
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <span class="text-green-700 font-medium">Cálculo Automático:</span>
                            <p class="text-xs text-green-600 mt-1">Salário Líquido = Salário Bruto - Deduções</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-green-700">
                                {{ number_format($edit_gross_salary - $edit_total_deductions, 2, ',', '.') }} AOA
                            </div>
                            <p class="text-xs text-green-600">Valor calculado</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-end space-x-3">
            <button
                wire:click="closeEditItemModal"
                wire:loading.attr="disabled"
                wire:target="saveEditedItem"
                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i class="fas fa-times mr-2" wire:loading.remove wire:target="saveEditedItem"></i>
                <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="saveEditedItem"></i>
                <span wire:loading.remove wire:target="saveEditedItem">Cancelar</span>
                <span wire:loading wire:target="saveEditedItem">Aguarde...</span>
            </button>
            <button
                wire:click="saveEditedItem"
                wire:loading.attr="disabled"
                wire:target="saveEditedItem"
                class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i class="fas fa-save mr-2" wire:loading.remove wire:target="saveEditedItem"></i>
                <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="saveEditedItem"></i>
                <span wire:loading.remove wire:target="saveEditedItem">Salvar Alterações</span>
                <span wire:loading wire:target="saveEditedItem">Salvando...</span>
            </button>
        </div>

    </div>
</div>
@endif

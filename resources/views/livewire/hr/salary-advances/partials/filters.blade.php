<!-- Filtros -->
<div class="p-4 bg-gray-50 border-b border-gray-200">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Campo de pesquisa -->
        <div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" wire:model.live.debounce.300ms="filters.search" 
                       placeholder="{{ __('messages.search_employee') }}" 
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
            </div>
        </div>
        
        <!-- Filtro de status -->
        <div>
            <select wire:model.live="filters.status" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
                <option value="">{{ __('messages.all_statuses') }}</option>
                <option value="pending">{{ __('messages.pending') }}</option>
                <option value="approved">{{ __('messages.approved') }}</option>
                <option value="rejected">{{ __('messages.rejected') }}</option>
                <option value="completed">{{ __('messages.completed') }}</option>
            </select>
        </div>
        
        <!-- Data de -->
        <div>
            <input type="date" wire:model.live="filters.date_from" 
                   placeholder="{{ __('messages.date_from') }}"
                   class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
        </div>
        
        <!-- Data até -->
        <div>
            <input type="date" wire:model.live="filters.date_to" 
                   placeholder="{{ __('messages.date_to') }}"
                   class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-green-500 focus:border-green-500 sm:text-sm transition-all duration-200">
        </div>
    </div>
</div>

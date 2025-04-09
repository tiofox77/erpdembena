<div>
    <label for="perPage" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
        <i class="fas fa-list-ol mr-1 text-gray-500"></i> Items Per Page
    </label>
    <div class="relative rounded-md shadow-sm">
        <select
            id="perPage"
            wire:model.live="perPage"
            class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
        >
            <option value="10">10 per page</option>
            <option value="25">25 per page</option>
            <option value="50">50 per page</option>
            <option value="100">100 per page</option>
        </select>
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if($showDeleteModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-red-600">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Delete Employee
                </h3>
                <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeDeleteModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-gray-700">Are you sure you want to delete this employee? This action cannot be undone.</p>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    wire:click="closeDeleteModal">
                    Cancel
                </button>
                <button type="button"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    wire:click="delete">
                    Delete
                </button>
            </div>
        </div>
    </div>
@endif

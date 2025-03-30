<div>
    <!-- JavaScript for Notifications -->
    <script>
        function showNotification(message, type = 'success') {
            // You can replace this with your preferred notification library
            // Example using Toastr or any other notification library you have in your project
            if (window.toastr) {
                toastr[type](message);
            } else {
                alert(message);
            }
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('showNotification', (data) => {
                showNotification(data.message, data.type);
            });
        });
    </script>

    <div class="container mx-auto py-6">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Maintenance Management</h1>
            <div class="space-x-2">
                <button type="button"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    wire:click="openCreateAreaModal">
                    <i class="fas fa-plus-circle mr-1"></i> Add Area
                </button>
                <button type="button"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                    wire:click="openCreateLineModal">
                    <i class="fas fa-plus-circle mr-1"></i> Add Line
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                <li class="mr-2">
                    <a href="#"
                        class="{{ $activeTab === 'areas' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700' }} inline-block p-4"
                        wire:click.prevent="$set('activeTab', 'areas')">
                        <i class="fas fa-map-marker-alt mr-2"></i> Areas
                    </a>
                </li>
                <li class="mr-2">
                    <a href="#"
                        class="{{ $activeTab === 'lines' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700' }} inline-block p-4"
                        wire:click.prevent="$set('activeTab', 'lines')">
                        <i class="fas fa-project-diagram mr-2"></i> Lines
                    </a>
                </li>
            </ul>
        </div>

        <!-- Search and Filters -->
        <div class="mb-4 flex">
            <div class="flex-1">
                <input type="text"
                    class="w-full px-4 py-2 border rounded"
                    placeholder="Search..."
                    wire:model.live.debounce.300ms="search">
            </div>
            <div class="ml-4">
                <select class="px-4 py-2 border rounded" wire:model.live="perPage">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
            </div>
        </div>

        <!-- Areas Tab -->
        <div class="{{ $activeTab === 'areas' ? 'block' : 'hidden' }}">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-700">Area Management</h2>
                </div>
                @if($this->canCreateArea())
                <button type="button" wire:click="openCreateAreaModal" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> New Area
                </button>
                @endif
            </div>

            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($this->areas as $area)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $area->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $area->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $area->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    @if($this->canEditArea())
                                    <button type="button" class="text-blue-600 hover:text-blue-900 mx-2 text-lg"
                                        wire:click="editArea({{ $area->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif

                                    @if($this->canDeleteArea())
                                    <button type="button" class="text-red-600 hover:text-red-900 mx-2 text-lg"
                                        wire:click="confirmDelete({{ $area->id }}, 'area')" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center py-4">
                                        <i class="fas fa-folder-open text-gray-400 text-4xl mb-3"></i>
                                        <p>No areas found</p>
                                        <button type="button"
                                            class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
                                            wire:click="openCreateAreaModal">
                                            <i class="fas fa-plus-circle mr-1"></i> Add your first area
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $this->areas->links() }}
            </div>
        </div>

        <!-- Lines Tab -->
        <div class="{{ $activeTab === 'lines' ? 'block' : 'hidden' }}">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-700">Line Management</h2>
                </div>
                @if($this->canCreateLine())
                <button type="button" wire:click="openCreateLineModal" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> New Line
                </button>
                @endif
            </div>

            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($this->lines as $line)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $line->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $line->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $line->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    @if($this->canEditLine())
                                    <button type="button" class="text-blue-600 hover:text-blue-900 mx-2 text-lg"
                                        wire:click="editLine({{ $line->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif

                                    @if($this->canDeleteLine())
                                    <button type="button" class="text-red-600 hover:text-red-900 mx-2 text-lg"
                                        wire:click="confirmDelete({{ $line->id }}, 'line')" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center justify-center py-4">
                                        <i class="fas fa-folder-open text-gray-400 text-4xl mb-3"></i>
                                        <p>No lines found</p>
                                        <button type="button"
                                            class="mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
                                            wire:click="openCreateLineModal">
                                            <i class="fas fa-plus-circle mr-1"></i> Add your first line
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $this->lines->links() }}
            </div>
        </div>

        <!-- Create/Edit Area Modal -->
        @if($showAreaModal)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $isEditing ? 'Edit' : 'Create' }} Area
                        </h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                            <p class="font-bold"><i class="fas fa-exclamation-circle mr-2"></i>Please correct the following errors:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit.prevent="saveArea">
                        <div class="mb-4">
                            <label for="area-name" class="block text-sm font-medium text-gray-700">Name</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="text" id="area-name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('area.name') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live="area.name">
                                @error('area.name')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('area.name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="area-description" class="block text-sm font-medium text-gray-700">Description</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea id="area-description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('area.description') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live="area.description"></textarea>
                                @error('area.description')
                                    <div class="absolute top-0 right-0 pr-3 pt-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('area.description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                wire:click="closeModal">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-1"></i>
                                {{ $isEditing ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Create/Edit Line Modal -->
        @if($showLineModal)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">
                            <i class="fas {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }} mr-2"></i>
                            {{ $isEditing ? 'Edit' : 'Create' }} Line
                        </h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700">
                            <p class="font-bold"><i class="fas fa-exclamation-circle mr-2"></i>Please correct the following errors:</p>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit.prevent="saveLine">
                        <div class="mb-4">
                            <label for="line-name" class="block text-sm font-medium text-gray-700">Name</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="text" id="line-name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('line.name') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live="line.name">
                                @error('line.name')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('line.name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="line-description" class="block text-sm font-medium text-gray-700">Description</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea id="line-description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('line.description') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror"
                                    wire:model.live="line.description"></textarea>
                                @error('line.description')
                                    <div class="absolute top-0 right-0 pr-3 pt-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('line.description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button type="button"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                wire:click="closeModal">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fas {{ $isEditing ? 'fa-save' : 'fa-plus' }} mr-1"></i>
                                {{ $isEditing ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Delete Confirmation Modal -->
        @if($showDeleteModal)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-red-600">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            Confirm Deletion
                        </h3>
                        <button type="button" class="text-gray-500 hover:text-gray-700 text-xl" wire:click="closeModal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <p class="mb-4 text-gray-700">
                        Are you sure you want to delete this {{ $deleteType }}? This action cannot be undone.
                    </p>
                    <div class="flex justify-end space-x-2">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            wire:click="closeModal">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </button>
                        <button type="button"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700"
                            wire:click="delete">
                            <i class="fas fa-trash-alt mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div>
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Header and Add Button -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold flex items-center">
                        <i class="fas fa-tasks mr-3 text-gray-700"></i> Task Management
                    </h1>
                    <button
                        wire:click="createTask"
                        type="button"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded flex items-center transition-colors duration-150"
                    >
                        <i class="fas fa-plus-circle mr-2"></i>
                        Add New Task
                    </button>
                </div>

                <!-- Filter Section -->
                <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-filter mr-2 text-blue-500"></i> Filters and Search
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="search" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-search mr-1 text-gray-500"></i> Search
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input
                                    wire:model.live.debounce.300ms="search"
                                    type="text"
                                    id="search"
                                    placeholder="Search tasks..."
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                >
                            </div>
                        </div>

                        <!-- Add more filters here if needed -->
                    </div>
                    <div class="flex justify-end mt-3">
                        <button
                            wire:click="clearFilters"
                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <i class="fas fa-times-circle mr-1"></i> Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Tasks Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg w-full border border-gray-200">
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full divide-y divide-gray-200 w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('title')">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-clipboard-list text-gray-400 mr-1"></i>
                                            <span>Task Name</span>
                                            @if($sortField === 'title')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-align-left text-gray-400 mr-1"></i>
                                            <span>Description</span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-calendar-plus text-gray-400 mr-1"></i>
                                            <span>Created</span>
                                            @if($sortField === 'created_at')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('updated_at')">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-calendar-check text-gray-400 mr-1"></i>
                                            <span>Updated</span>
                                            @if($sortField === 'updated_at')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                            @else
                                                <i class="fas fa-sort text-gray-300"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-cogs text-gray-400 mr-1"></i>
                                            <span>Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($tasks as $task)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">{{ $task->title }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500 truncate max-w-xs">{{ $task->description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 flex items-center">
                                                <i class="far fa-calendar-alt mr-1 text-gray-400"></i>
                                                {{ $task->created_at->format('M d, Y g:i A') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 flex items-center">
                                                <i class="far fa-clock mr-1 text-gray-400"></i>
                                                {{ $task->updated_at->format('M d, Y g:i A') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex justify-start space-x-2">
                                                <button
                                                    wire:click="viewTask({{ $task->id }})"
                                                    class="text-blue-600 hover:text-blue-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-blue-100"
                                                    title="View Details"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button
                                                    wire:click="editTask({{ $task->id }})"
                                                    class="text-indigo-600 hover:text-indigo-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-indigo-100"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    wire:click="delete({{ $task->id }})"
                                                    wire:confirm="Are you sure you want to delete this task?"
                                                    class="text-red-600 hover:text-red-900 w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-100"
                                                    title="Delete"
                                                >
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center py-8">
                                                <div class="bg-gray-100 rounded-full p-3 mb-4">
                                                    <i class="fas fa-clipboard-list text-gray-400 text-4xl"></i>
                                                </div>
                                                <p class="text-lg font-medium">No tasks found</p>
                                                <p class="text-sm text-gray-500 mt-1 flex items-center">
                                                    @if($search)
                                                        <i class="fas fa-filter mr-1"></i> Try adjusting your search filters or
                                                        <button
                                                            wire:click="clearFilters"
                                                            class="ml-1 text-blue-600 hover:text-blue-800 underline flex items-center"
                                                        >
                                                            <i class="fas fa-times-circle mr-1"></i> clear all filters
                                                        </button>
                                                    @else
                                                        <i class="fas fa-info-circle mr-1"></i> Click "Add New Task" to create your first task
                                                    @endif
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- View Task Modal -->
    @if($showViewModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl p-6 overflow-y-auto max-h-[90vh]">
            <!-- Enhanced Header with Icon -->
            <div class="flex justify-between items-center mb-6 border-b border-gray-200 pb-4">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <span class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                        <i class="fas fa-clipboard-check text-lg"></i>
                    </span>
                    Task Details
                </h3>
                <div class="flex items-center space-x-2">
                    <button
                        type="button"
                        class="bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors duration-150 p-2 rounded-full"
                        wire:click="editTask({{ $viewingTask->id }})"
                        title="Edit Task"
                    >
                        <i class="fas fa-edit"></i>
                    </button>
                    <button
                        type="button"
                        class="bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors duration-150 p-2 rounded-full"
                        wire:click="closeViewModal"
                        title="Close"
                    >
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Task Summary Card -->
            <div class="bg-gray-50 rounded-lg mb-6 shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                        <div class="flex items-center mb-3 md:mb-0">
                            <span class="ml-4 bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm flex items-center">
                                <i class="fas fa-hashtag mr-1"></i>
                                ID: {{ $viewingTask->id }}
                            </span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <span>Created: {{ $viewingTask->created_at->format('Y-m-d g:i A') }}</span>
                        </div>
                    </div>
                </div>
                <!-- Primary Task Fields Summary -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                    <div class="flex items-start">
                        <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                            <i class="fas fa-clipboard-list"></i>
                        </span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Task Name</p>
                            <p class="text-sm font-medium">{{ $viewingTask->title }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-blue-50 p-2 rounded-full text-blue-600 mr-3">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Last Updated</p>
                            <p class="text-sm font-medium">{{ $viewingTask->updated_at->format('Y-m-d g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Content Section -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
                <div class="bg-gray-50 px-4 py-2 rounded-t-lg flex items-center">
                    <i class="fas fa-align-left text-gray-600 mr-2"></i>
                    <h4 class="text-sm font-semibold text-gray-700">Description</h4>
                </div>
                <div class="p-4 h-40 overflow-y-auto">
                    @if($viewingTask->description)
                        <p class="text-sm leading-relaxed">{{ $viewingTask->description }}</p>
                    @else
                        <div class="flex items-center justify-center h-full text-gray-400">
                            <i class="fas fa-file-alt mr-2"></i>
                            <span>No description provided</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex justify-end space-x-3 border-t border-gray-200 pt-4">
                <button
                    type="button"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150 flex items-center"
                    wire:click="closeViewModal"
                >
                    <i class="fas fa-times mr-2"></i> Close
                </button>
                <button
                    type="button"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                    wire:click="editTask({{ $viewingTask->id }})"
                >
                    <i class="fas fa-edit mr-2"></i> Edit Task
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit/Create Modal -->
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <!-- Modal Header -->
            <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-4">
                <h3 class="text-lg font-medium flex items-center">
                    <span class="bg-indigo-100 text-indigo-600 p-2 rounded-full mr-3">
                        <i class="fas {{ $taskId ? 'fa-edit' : 'fa-plus-circle' }} text-lg"></i>
                    </span>
                    {{ $taskId ? 'Edit Task' : 'Create New Task' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Sumário de erros -->
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

            <!-- Form Section -->
            <form wire:submit.prevent="save">
                <div class="bg-gray-50 p-3 rounded-md mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i> Task Information
                    </h4>
                    <div class="space-y-4">
                        <!-- Task Name -->
                        <div>
                            <label for="title" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-clipboard-list mr-1 text-gray-500"></i>
                                Task Name <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input
                                    type="text"
                                    id="title"
                                    wire:model.live="title"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2 @error('title') border-red-300 text-red-900 @enderror"
                                    placeholder="Enter task name"
                                >
                                @error('title')
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('title')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-xs font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-align-left mr-1 text-gray-500"></i>
                                Description
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <textarea
                                    id="description"
                                    wire:model.live="description"
                                    rows="4"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs sm:text-sm py-1.5 px-2 @error('description') border-red-300 text-red-900 @enderror"
                                    placeholder="Enter detailed description of the task..."
                                ></textarea>
                                @error('description')
                                    <div class="absolute top-0 right-0 pr-3 pt-2 flex items-center pointer-events-none">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                @enderror
                            </div>
                            @error('description')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-2 mt-6">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                    >
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150 flex items-center"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="save">
                            <i class="fas {{ $taskId ? 'fa-save' : 'fa-plus' }} mr-2"></i>
                            {{ $taskId ? 'Update' : 'Create' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Notification System -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Success notification handler
            Livewire.on('notify', (params) => {
                console.log('Notification received:', params);
                const { type, message, title } = params;

                // Create notification element
                const notificationElement = document.createElement('div');

                // Choose color based on notification type
                let bgColor = 'bg-blue-500'; // default info color
                let icon = 'fa-info-circle';

                if (type === 'success') {
                    bgColor = 'bg-green-500';
                    icon = 'fa-check-circle';
                }
                else if (type === 'error') {
                    bgColor = 'bg-red-500';
                    icon = 'fa-exclamation-circle';
                }
                else if (type === 'warning') {
                    bgColor = 'bg-yellow-500';
                    icon = 'fa-exclamation-triangle';
                }

                notificationElement.className = `fixed top-4 right-4 z-50 p-4 rounded-md ${bgColor} text-white max-w-xs shadow-lg transition-opacity duration-500 flex items-start`;

                // Add icon
                const iconElement = document.createElement('i');
                iconElement.className = `fas ${icon} mr-3 mt-0.5`;
                notificationElement.appendChild(iconElement);

                const contentElement = document.createElement('div');

                // Add title if provided
                if (title) {
                    const titleElement = document.createElement('div');
                    titleElement.className = 'font-bold';
                    titleElement.textContent = title;
                    contentElement.appendChild(titleElement);
                }

                // Add message
                const messageElement = document.createElement('div');
                messageElement.textContent = message;
                contentElement.appendChild(messageElement);

                notificationElement.appendChild(contentElement);

                document.body.appendChild(notificationElement);

                // Remove notification after 3 seconds
                setTimeout(() => {
                    notificationElement.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(notificationElement);
                    }, 500);
                }, 3000);
            });

            // Fechar modal com tecla ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('escape-pressed');
                }
            });

            // Listen for filters-cleared event
            Livewire.on('filters-cleared', () => {
                // Optional: Add visual feedback
                const clearBtn = document.querySelector('button[wire\\:click="clearFilters"]');
                if (clearBtn) {
                    clearBtn.classList.add('bg-blue-50');
                    setTimeout(() => {
                        clearBtn.classList.remove('bg-blue-50');
                    }, 300);
                }
            });
        });
    </script>
</div>

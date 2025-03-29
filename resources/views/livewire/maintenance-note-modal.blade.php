<div>
@if($showModal)
<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50 flex items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all w-full max-w-4xl">
        <!-- Modal Header -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">
                @if($viewOnly)
                    Maintenance History: {{ $task['title'] }} - {{ $task['equipment'] }}
                @else
                    Maintenance Notes: {{ $task['title'] }} - {{ $task['equipment'] }}
                @endif
            </h3>
            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Column 1: Task Details -->
                <div class="col-span-1">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-700 mb-3">Task Details</h4>

                        <div class="mb-2">
                            <span class="text-sm font-medium text-gray-500">ID:</span>
                            <span class="text-sm text-gray-900 ml-1">{{ $task['id'] }}</span>
                        </div>

                        <div class="mb-2">
                            <span class="text-sm font-medium text-gray-500">Task:</span>
                            <span class="text-sm text-gray-900 ml-1">{{ $task['title'] }}</span>
                        </div>

                        <div class="mb-2">
                            <span class="text-sm font-medium text-gray-500">Equipment:</span>
                            <span class="text-sm text-gray-900 ml-1">{{ $task['equipment'] }}</span>
                        </div>

                        <div class="mb-4">
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            <span class="px-2 py-0.5 text-xs rounded-full ml-1
                                {{ $task['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $task['status'] === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($task['status']) }}
                            </span>
                        </div>

                        @if(!$viewOnly)
                        <h5 class="font-medium text-gray-700 text-sm mb-2">Update Status:</h5>
                        <div class="flex space-x-2">
                            <button wire:click="updateStatus('in_progress')" class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 hover:bg-blue-200">
                                In Progress
                            </button>
                            <button wire:click="updateStatus('completed')" class="px-2 py-1 text-xs rounded bg-green-100 text-green-800 hover:bg-green-200">
                                Completed
                            </button>
                            <button wire:click="updateStatus('cancelled')" class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 hover:bg-gray-200">
                                Cancelled
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Column 2: Note Form and History -->
                <div class="col-span-2">
                    @if(!$viewOnly)
                    <h4 class="font-medium text-gray-700 mb-3">Add New Note</h4>

                    <form wire:submit.prevent="saveNote" class="mb-4">
                        <div class="mb-3">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Activity Description</label>
                            <textarea
                                id="notes"
                                wire:model="notes"
                                rows="4"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Describe what was done during maintenance..."
                            ></textarea>
                            @error('notes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                                Add Note
                            </button>
                        </div>
                    </form>
                    @endif

                    <!-- Notes History -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3 {{ !$viewOnly ? 'border-t pt-3' : '' }}">Activity History</h4>

                        @if(empty($history))
                            <p class="text-sm text-gray-500 italic">No activity records found</p>
                        @else
                            <div class="space-y-3">
                                @foreach($history as $note)
                                    <div class="p-3 border rounded-md bg-gray-50">
                                        <div class="flex justify-between mb-1">
                                            <span class="text-xs font-medium text-gray-500">
                                                {{ $note['created_at'] }} by {{ $note['user'] }}
                                            </span>
                                            <span class="px-2 py-0.5 text-xs rounded-full
                                                {{ $note['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $note['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $note['status'] === 'cancelled' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst($note['status']) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-900 whitespace-pre-line">{{ $note['notes'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-3 flex justify-end">
            <button
                wire:click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50"
            >
                Close
            </button>
        </div>
    </div>
</div>
@endif
</div>

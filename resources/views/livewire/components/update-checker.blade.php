<div>
    @if($updateAvailable)
        <div
            class="relative cursor-pointer bg-yellow-100 text-gray-800 px-3 py-2 rounded-md flex items-center text-sm ml-2"
            x-data="{showTooltip: false}"
            x-on:mouseenter="showTooltip = true"
            x-on:mouseleave="showTooltip = false"
            wire:click="goToUpdatePage"
        >
            <i class="fas fa-arrow-circle-up text-yellow-600 mr-2"></i>
            <span>Update</span>

            <div
                x-cloak
                x-show="showTooltip"
                class="absolute top-full mt-2 left-0 bg-white shadow-lg rounded-md p-2 text-xs w-48 z-50"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
            >
                <div class="font-semibold border-b pb-1 mb-1">New version available!</div>
                <div class="flex justify-between mb-1">
                    <span>Current:</span>
                    <span class="font-medium">{{ $currentVersion }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span>New:</span>
                    <span class="font-semibold text-green-600">{{ $latestVersion }}</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Click to update
                </div>
            </div>
        </div>
    @endif

    <!-- Button to manually check for updates (shown only to admins) -->
    @if(auth()->check() && (auth()->user()->hasRole(['admin', 'super-admin']) || (isset(auth()->user()->role) && in_array(auth()->user()->role, ['admin', 'super-admin']))))
        <div class="flex items-center">
            @if($updateAvailable)
                <button
                    wire:click="goToUpdatePage"
                    class="relative inline-flex items-center gap-x-1.5 px-3 py-1.5 text-sm font-semibold rounded-md bg-blue-600 text-white hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600"
                >
                    <svg class="-ml-0.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                    </svg>
                    Update available
                </button>
            @else
                <button
                    wire:click="checkForUpdates"
                    class="relative inline-flex items-center gap-x-1.5 px-3 py-1.5 text-sm font-semibold rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
                    wire:loading.attr="disabled"
                    wire:target="checkForUpdates"
                >
                    <svg wire:loading.remove wire:target="checkForUpdates" class="-ml-0.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                    </svg>
                    <svg wire:loading wire:target="checkForUpdates" class="animate-spin -ml-0.5 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="checkForUpdates">Check for updates</span>
                    <span wire:loading wire:target="checkForUpdates">Checking...</span>
                </button>
            @endif
        </div>
    @endif
</div>

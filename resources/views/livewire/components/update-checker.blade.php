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
    @if(auth()->check() && (auth()->user()->roles->contains('name', 'admin') || auth()->user()->roles->contains('name', 'super-admin')))
        <button
            wire:click="checkForUpdates"
            wire:loading.attr="disabled"
            class="text-gray-600 p-2 rounded-full hover:bg-gray-200 focus:outline-none ml-1"
            title="Check for updates"
        >
            <div wire:loading wire:target="checkForUpdates">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
            <!-- <div wire:loading.remove wire:target="checkForUpdates">
                <i class="fas fa-sync-alt"></i>
            </div> -->
        </button>
    @endif
</div>

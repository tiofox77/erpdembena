@if (session()->has('message'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-md">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium text-green-800">
                    {{ session('message') }}
                </p>
            </div>
            <div class="ml-auto pl-3">
                <button @click="show = false" class="inline-flex text-green-600 hover:text-green-800 focus:outline-none transition-colors duration-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
@endif

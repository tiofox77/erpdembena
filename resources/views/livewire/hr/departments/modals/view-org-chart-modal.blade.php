@if($showOrgChartModal)
<div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showOrgChartModal') }" x-show="show" style="display: none;">
    <!-- Background overlay -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="$wire.closeOrgChartModal()">
        </div>

        <!-- Modal panel -->
        <div class="inline-block w-full max-w-5xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl"
             x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-5 bg-gradient-to-r from-amber-600 to-orange-600 border-b border-amber-700">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 bg-white rounded-lg shadow-sm">
                        <i class="fas fa-sitemap text-amber-600 text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">
                            {{ __('hr.departments.org_chart') }}
                        </h3>
                        @if($viewingDepartment)
                        <p class="text-amber-100 text-sm">{{ $viewingDepartment->name }}</p>
                        @endif
                    </div>
                </div>
                <button @click="$wire.closeOrgChartModal()" type="button" 
                        class="text-white hover:text-gray-200 transition-colors duration-200 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-6 bg-gray-50">
                @if($viewingDepartment && $viewingDepartment->org_chart)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        @php
                            $extension = strtolower(pathinfo($viewingDepartment->org_chart, PATHINFO_EXTENSION));
                        @endphp
                        
                        @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']))
                            <!-- Image Preview -->
                            <div class="flex justify-center">
                                <img src="{{ Storage::url($viewingDepartment->org_chart) }}" 
                                     alt="{{ __('hr.departments.org_chart') }}"
                                     class="max-w-full h-auto rounded-lg shadow-lg">
                            </div>
                        @elseif($extension === 'pdf')
                            <!-- PDF Preview -->
                            <div class="flex flex-col items-center space-y-4">
                                <div class="w-full h-[600px] border border-gray-300 rounded-lg overflow-hidden">
                                    <iframe src="{{ Storage::url($viewingDepartment->org_chart) }}" 
                                            class="w-full h-full"
                                            frameborder="0"></iframe>
                                </div>
                                <a href="{{ Storage::url($viewingDepartment->org_chart) }}" 
                                   target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    {{ __('hr.departments.open_in_new_tab') }}
                                </a>
                            </div>
                        @else
                            <!-- Generic File -->
                            <div class="flex flex-col items-center justify-center py-12">
                                <i class="fas fa-file-alt text-gray-400 text-6xl mb-4"></i>
                                <p class="text-gray-600 mb-4">{{ __('hr.departments.preview_not_available') }}</p>
                                <a href="{{ Storage::url($viewingDepartment->org_chart) }}" 
                                   download
                                   class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-download mr-2"></i>
                                    {{ __('hr.departments.download_file') }}
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-exclamation-circle text-gray-400 text-6xl mb-4"></i>
                        <p class="text-gray-600">{{ __('hr.departments.no_org_chart') }}</p>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-4 bg-gray-100 border-t border-gray-200 rounded-b-2xl">
                <div class="text-sm text-gray-600">
                    @if($viewingDepartment && $viewingDepartment->org_chart)
                        <i class="fas fa-file mr-1"></i>
                        {{ basename($viewingDepartment->org_chart) }}
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    @if($viewingDepartment && $viewingDepartment->org_chart)
                    <a href="{{ Storage::url($viewingDepartment->org_chart) }}" 
                       download
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200">
                        <i class="fas fa-download mr-2"></i>
                        {{ __('hr.departments.download') }}
                    </a>
                    @endif
                    <button type="button"
                        class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all duration-200"
                        @click="$wire.closeOrgChartModal()">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('hr.departments.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

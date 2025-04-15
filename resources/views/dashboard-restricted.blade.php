@extends('layouts.livewire')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-lg mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-4 py-5">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i>
                {{ __('messages.access_restricted') }}
            </h2>
        </div>
        <div class="p-6 space-y-6">
            <div class="flex flex-col items-center text-center space-y-4">
                <i class="fas fa-lock text-red-500 text-5xl"></i>
                <p class="text-gray-700">{{ __('messages.no_module_permissions') }}</p>
                <p class="text-sm text-gray-500">{{ __('messages.contact_administrator') }}</p>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <h3 class="font-medium text-gray-700">{{ __('messages.your_details') }}:</h3>
                <div class="mt-2 bg-gray-50 p-3 rounded-md">
                    <p><span class="font-medium">{{ __('messages.username') }}:</span> {{ auth()->user()->name }}</p>
                    <p><span class="font-medium">{{ __('messages.email') }}:</span> {{ auth()->user()->email }}</p>
                    <p><span class="font-medium">{{ __('messages.roles') }}:</span> 
                        {{ auth()->user()->roles->pluck('name')->implode(', ') ?: __('messages.no_roles_assigned') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 text-right">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    {{ __('messages.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

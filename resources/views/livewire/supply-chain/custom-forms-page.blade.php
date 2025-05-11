@extends('layouts.livewire')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 flex items-center">
            <i class="fas fa-wpforms text-blue-600 mr-3"></i>
            {{ __('Formulários Personalizados para Shipping Notes') }}
        </h1>
        <nav class="flex mt-4 md:mt-0" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-1"></i> Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                        <a href="{{ route('supply-chain.dashboard') }}" class="text-gray-700 hover:text-blue-600">
                            Supply Chain
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                        <span class="text-gray-500" aria-current="page">Formulários Personalizados</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        @livewire('supply-chain.custom-form-builder')
    </div>
</div>
@endsection

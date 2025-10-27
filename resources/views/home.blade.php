@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p class="mb-4">{{ __('You are logged in!') }}</p>
                    
                    <div class="mt-4">
                        <h5 class="mb-3">{{ __('Quick Links') }}</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ url('/hr-guide.html') }}" class="btn btn-primary" target="_blank">
                                <i class="fas fa-book-open mr-2"></i> {{ __('messages.hr_guide') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

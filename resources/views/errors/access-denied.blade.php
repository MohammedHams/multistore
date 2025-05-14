@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Access Denied') }}</div>

                <div class="card-body">
                    <div class="alert alert-danger">
                        {{ session('error') ?? __('You do not have permission to access this page.') }}
                    </div>
                    <p>{{ __('Please contact an administrator if you believe this is an error.') }}</p>
                    
                    @if(auth()->guard('store-owner')->check())
                        <a href="{{ route('store-owner.dashboard') }}" class="btn btn-primary">
                            {{ __('Back to Dashboard') }}
                        </a>
                    @elseif(auth()->guard('store-staff')->check())
                        <a href="{{ route('store-staff.dashboard') }}" class="btn btn-primary">
                            {{ __('Back to Dashboard') }}
                        </a>
                    @elseif(auth()->guard('admin')->check())
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                            {{ __('Back to Dashboard') }}
                        </a>
                    @else
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            {{ __('Back to Home') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

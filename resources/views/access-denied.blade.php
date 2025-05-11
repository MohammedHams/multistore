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
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        {{ __('Go to Profile') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

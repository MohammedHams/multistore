@extends('layouts.store-owner')

@php
$title = __('Add Staff Member');
$pageTitle = __('Add Staff Member');
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Add Staff Member') }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('store-owner.store.staff.index', $store->id) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to Staff') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('store-owner.store.staff.store', $store->id) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="user_id" class="form-label required">{{ __('Select User') }}</label>
                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                        <option value="">{{ __('Select an option') }}</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    @if($availableUsers->isEmpty())
                        <div class="alert alert-info mt-3">
                            {{ __('No users available to add as staff') }}
                        </div>
                    @endif
                </div>
                
                <div class="mb-4">
                    <label class="form-label required">{{ __('Permissions') }}</label>
                    <div class="row">
                        @foreach($availablePermissions as $permissionKey => $permissionData)
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permissionKey }}" id="permission_{{ $permissionKey }}" {{ in_array($permissionKey, old('permissions', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission_{{ $permissionKey }}">
                                        {{ $permissionData['label'] }}
                                    </label>
                                    <div class="text-muted small">{{ $permissionData['description'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" {{ $availableUsers->isEmpty() ? 'disabled' : '' }}>
                        <i class="fas fa-plus"></i> {{ __('Add Staff Member') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

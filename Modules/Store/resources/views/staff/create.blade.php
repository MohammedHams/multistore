@extends('layouts.app')

@php
$title = __('store.add_staff') . ' - ' . $store->name . ' - ' . config('app.name');
$pageTitle = __('store.add_staff_to_store') . ': ' . $store->name;
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('store.add_staff_to_store') }}: {{ $store->name }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.store.staff.index', $store) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('store.back_to_staff') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.store.staff.store', $store) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="user_id" class="form-label required">{{ __('store.select_user') }}</label>
                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                        <option value="">{{ __('dashboard.select_option') }}</option>
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
                            {{ __('store.no_users_available') }}
                        </div>
                    @endif
                </div>
                
                <div class="mb-4">
                    <label class="form-label required">{{ __('store.permissions') }}</label>
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
                        <i class="fas fa-plus"></i> {{ __('store.add_staff') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

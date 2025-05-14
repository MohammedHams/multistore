@extends('layouts.app')

@php
$title = __('store.edit_staff_permissions') . ' - ' . $staff->getUserData()['name'] . ' - ' . config('app.name');
$pageTitle = __('store.edit_staff_permissions') . ': ' . $staff->getUserData()['name'];
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('store.edit_staff_permissions') }}: {{ $staff->getUserData()['name'] }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('store.staff.index', $store) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('store.back_to_staff') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('store.staff.update', [$store, $staff->getId()]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="symbol symbol-50px me-3">
                            <div class="symbol-label fs-2 fw-bold bg-primary text-white">{{ substr($staff->getUserData()['name'], 0, 1) }}</div>
                        </div>
                        <div>
                            <h4 class="mb-0">{{ $staff->getUserData()['name'] }}</h4>
                            <span class="text-muted">{{ $staff->getUserData()['email'] }}</span>
                        </div>
                    </div>
                    
                    <label class="form-label required">{{ __('store.permissions') }}</label>
                    <div class="row">
                        @foreach($availablePermissions as $permissionKey => $permissionData)
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permissionKey }}" id="permission_{{ $permissionKey }}" {{ in_array($permissionKey, old('permissions', $currentPermissions)) ? 'checked' : '' }}>
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
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('store.update_permissions') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

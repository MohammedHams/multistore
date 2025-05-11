@extends('layouts.app')

@php
$title = 'Store Staff - ' . $store->name . ' - Multistore Admin';
$pageTitle = 'Store Staff: ' . $store->name;
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Store Staff') }}: {{ $store->name }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('store.show', $store->id) }}" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to Store') }}
                </a>
                <a href="{{ route('store.staff.create', $store->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> {{ __('Add Staff') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Permissions') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $staffMember)
                        <tr>
                            <td>{{ $staffMember->getUserData()['id'] }}</td>
                            <td>{{ $staffMember->getUserData()['name'] }}</td>
                            <td>{{ $staffMember->getUserData()['email'] }}</td>
                            <td>
                                @php
                                    $userId = $staffMember->getUserData()['id'];
                                    $staffPermissions = \App\Models\StoreStaff::where('store_id', $store->id)
                                        ->where('user_id', $userId)
                                        ->first()
                                        ->permissions ?? [];
                                @endphp
                                
                                @foreach($staffPermissions as $permission)
                                    <span class="badge badge-primary me-1">{{ __($permission) }}</span>
                                @endforeach
                                
                                @if(empty($staffPermissions))
                                    <span class="text-muted">{{ __('No permissions') }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $userId = $staffMember->getUserData()['id'];
                                    $user = \App\Models\User::find($userId);
                                @endphp
                                <a href="{{ route('store.staff.edit', [$store->id, $user]) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="{{ __('Edit Permissions') }}">
                                    <i class="fas fa-key"></i>
                                </a>
                                <form action="{{ route('store.staff.destroy', [$store->id, $user]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this staff member?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-bs-toggle="tooltip" title="{{ __('Remove Staff') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ __('No staff members found.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

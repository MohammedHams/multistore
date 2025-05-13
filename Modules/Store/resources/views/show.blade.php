@extends('layouts.app')

@php
$title = __('store.store_details') . ' - ' . $storeModel->name . ' - ' . config('app.name');
$pageTitle = __('store.store_details') . ': ' . $storeModel->name;
@endphp

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ __('store.store_details') }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('store.index') }}" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> {{ __('store.back_to_stores') }}
                </a>
                <a href="{{ route('store.edit', $storeModel->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> {{ __('store.edit_store') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    @if($storeModel->logo)
                        <img src="{{ asset('storage/' . $storeModel->logo) }}" alt="{{ $storeModel->name }}" class="img-fluid rounded" style="max-width: 200px;">
                    @else
                        <div class="symbol symbol-150px">
                            <div class="symbol-label fs-2 fw-bold bg-primary text-white">{{ substr($storeModel->name, 0, 1) }}</div>
                        </div>
                    @endif
                </div>
                <div class="col-md-8">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 150px;">{{ __('store.id') }}:</th>
                            <td>{{ $storeModel->id }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('store.name') }}:</th>
                            <td>{{ $storeModel->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('store.domain') }}:</th>
                            <td>{{ $storeModel->domain }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('store.email') }}:</th>
                            <td>{{ $storeModel->email }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('store.phone') }}:</th>
                            <td>{{ $storeModel->phone }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('store.status') }}:</th>
                            <td>
                                @if($storeModel->is_active)
                                    <span class="badge badge-success">{{ __('store.active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('store.inactive') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('store.created_at') }}:</th>
                            <td>{{ $storeModel->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('store.updated_at') }}:</th>
                            <td>{{ $storeModel->updated_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Owners Section -->
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">{{ __('store.store_owners') }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('store.owners.create', $storeModel->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> {{ __('store.add_owner') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>{{ __('store.id') }}</th>
                            <th>{{ __('store.name') }}</th>
                            <th>{{ __('store.email') }}</th>
                            <th>{{ __('store.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($owners as $owner)
                        <tr>
                            <td>{{ $owner->getUserData()['id'] ?? $owner->user_id }}</td>
                            <td>{{ $owner->getUserData()['name'] ?? ($owner->user->name ?? 'N/A') }}</td>
                            <td>{{ $owner->getUserData()['email'] ?? ($owner->user->email ?? 'N/A') }}</td>
                            <td>
                                @php
                                    $userId = $owner->getUserData()['id'] ?? $owner->user_id;
                                    $user = \App\Models\User::find($userId);
                                @endphp
                                <form action="{{ route('store.owners.destroy', ['store' => $storeModel->id, 'user' => $user]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('store.are_you_sure_remove_owner') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-bs-toggle="tooltip" title="{{ __('store.remove_owner') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('store.no_owners_found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Store Staff Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('store.store_staff') }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('store.staff.create', $storeModel->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> {{ __('store.add_staff') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>{{ __('store.id') }}</th>
                            <th>{{ __('store.name') }}</th>
                            <th>{{ __('store.email') }}</th>
                            <th>{{ __('store.permissions') }}</th>
                            <th>{{ __('store.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staff as $staffMember)
                        <tr>
                            <td>{{ $staffMember->user_id ?? ($staffMember->getUserData()['id'] ?? 'N/A') }}</td>
                            <td>{{ $staffMember->user->name ?? ($staffMember->getUserData()['name'] ?? 'N/A') }}</td>
                            <td>{{ $staffMember->user->email ?? ($staffMember->getUserData()['email'] ?? 'N/A') }}</td>
                            <td>
                                @php
                                    $userId = $staffMember->user_id ?? ($staffMember->getUserData()['id'] ?? 0);
                                    $staffPermissions = \App\Models\StoreStaff::where('store_id', $storeModel->id)
                                        ->where('user_id', $userId)
                                        ->first()
                                        ->permissions ?? [];
                                @endphp
                                
                                @foreach($staffPermissions as $permission)
                                    <span class="badge badge-primary me-1">{{ __($permission) }}</span>
                                @endforeach
                                
                                @if(empty($staffPermissions))
                                    <span class="text-muted">{{ __('store.no_permissions') }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $userId = $staffMember->getUserData()['id'];
                                    $user = \App\Models\User::find($userId);
                                @endphp
                                <a href="{{ route('store.staff.edit', [$storeModel->id, $user]) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="{{ __('store.edit_staff_permissions') }}">
                                    <i class="fas fa-key"></i>
                                </a>
                                <form action="{{ route('store.staff.destroy', [$storeModel->id, $user]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('store.are_you_sure_remove_staff') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-bs-toggle="tooltip" title="{{ __('store.remove_staff') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ __('store.no_staff_found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

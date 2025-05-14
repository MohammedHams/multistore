@extends('layouts.store-owner')

@php
$title = __('Staff Details') . ' - ' . $staff->getUserData()['name'];
$pageTitle = __('Staff Details') . ': ' . $staff->getUserData()['name'];
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('Staff Details') }}: {{ $staff->getUserData()['name'] }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('store-owner.store.staff.index', $store->id) }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> {{ __('Back to Staff') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-7">
                <div class="symbol symbol-60px me-4">
                    <div class="symbol-label fs-2 fw-bold bg-primary text-white">{{ substr($staff->getUserData()['name'], 0, 1) }}</div>
                </div>
                <div>
                    <h3 class="fs-2 text-gray-800 mb-0">{{ $staff->getUserData()['name'] }}</h3>
                    <div class="fs-6 fw-semibold text-gray-400">{{ $staff->getUserData()['email'] }}</div>
                </div>
            </div>

            <div class="separator separator-dashed my-5"></div>

            <div class="row mb-5">
                <div class="col-md-6">
                    <h4 class="fs-5 fw-bold mb-3">{{ __('Permissions') }}</h4>
                    @php
                        // Get staff permissions from the entity
                        $permissions = $staff->getPermissions() ?? [];
                        
                        // Map permissions to display categories
                        $permissionMap = [
                            'view_store' => 'view',
                            'edit_store' => 'edit',
                            'view_products' => 'view',
                            'manage_products' => 'edit',
                            'delete_products' => 'delete',
                            'view_orders' => 'view',
                            'manage_orders' => 'edit',
                            'delete_orders' => 'delete',
                            'manage_staff' => 'admin'
                        ];
                        
                        // Define permission groups and their labels
                        $permissionGroups = [
                            'view' => __('View'),
                            'edit' => __('Edit'),
                            'delete' => __('Delete'),
                            'admin' => __('Admin')
                        ];
                        
                        // Collect unique permission categories
                        $userPermissions = [];
                        foreach ($permissions as $permission) {
                            if (isset($permissionMap[$permission])) {
                                $userPermissions[$permissionMap[$permission]] = true;
                            }
                        }
                        $userPermissions = array_keys($userPermissions);
                    @endphp
                    
                    @if(!empty($userPermissions))
                        <div class="d-flex flex-wrap">
                            @foreach($userPermissions as $permission)
                                <span class="badge badge-primary me-2 mb-2 p-2">{{ $permissionGroups[$permission] }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">{{ __('No permissions assigned') }}</span>
                    @endif
                </div>
                
                <div class="col-md-6">
                    <h4 class="fs-5 fw-bold mb-3">{{ __('Access Details') }}</h4>
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <tbody>
                                <tr>
                                    <th class="fw-bold text-muted">{{ __('Staff ID') }}</th>
                                    <td>{{ $staff->getId() }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-bold text-muted">{{ __('Store') }}</th>
                                    <td>{{ $store->name }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-bold text-muted">{{ __('Last Login') }}</th>
                                    <td>{{ $staff->getUserData()['last_login'] ?? __('Never') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('store-owner.store.staff.edit', [$store->id, $staff->getId()]) }}" class="btn btn-primary me-2">
                    <i class="fas fa-edit"></i> {{ __('Edit Permissions') }}
                </a>
                <form action="{{ route('store-owner.store.staff.destroy', [$store->id, $staff->getId()]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this staff member?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> {{ __('Remove Staff') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

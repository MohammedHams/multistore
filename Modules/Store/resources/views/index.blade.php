@extends('layouts.app')

@php
$title = __('store.stores') . ' - ' . config('app.name');
$pageTitle = __('store.stores');
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('store.stores') }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('store.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> {{ __('store.add_store') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search & Filters -->
            <form action="{{ route('store.index') }}" method="GET" class="mb-7">
                <div class="row mb-4">
                    <div class="col-lg-4 mb-lg-0 mb-4">
                        <label class="form-label">{{ __('store.search') }}</label>
                        <input type="text" name="search" class="form-control" placeholder="{{ __('store.search_by_name_domain_email') }}" value="{{ request('search') }}">
                    </div>
                    <div class="col-lg-3 mb-lg-0 mb-4">
                        <label class="form-label">{{ __('store.status') }}</label>
                        <select name="status" class="form-select">
                            <option value="">{{ __('dashboard.all') }}</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('store.active') }}</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('store.inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-3 mb-lg-0 mb-4">
                        <label class="form-label">{{ __('store.sort_by') }}</label>
                        <select name="sort" class="form-select">
                            <option value="created_at_desc" {{ request('sort') === 'created_at_desc' ? 'selected' : '' }}>{{ __('store.newest_first') }}</option>
                            <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>{{ __('store.oldest_first') }}</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>{{ __('store.name_a_z') }}</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>{{ __('store.name_z_a') }}</option>
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">{{ __('store.filter') }}</button>
                        <a href="{{ route('store.index') }}" class="btn btn-secondary">{{ __('dashboard.reset') }}</a>
                    </div>
                </div>
            </form>

            <!-- Stores Table -->
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>{{ __('store.id') }}</th>
                            <th>{{ __('store.logo') }}</th>
                            <th>{{ __('store.name') }}</th>
                            <th>{{ __('store.domain') }}</th>
                            <th>{{ __('store.status') }}</th>
                            <th>{{ __('store.created_at') }}</th>
                            <th>{{ __('store.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stores as $store)
                        <tr>
                            <td>{{ $store->id }}</td>
                            <td>
                                @if($store->logo)
                                    <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }}" class="w-50px h-50px rounded">
                                @else
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label fs-2 fw-bold bg-primary text-white">{{ substr($store->name, 0, 1) }}</div>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $store->name }}</td>
                            <td>{{ $store->domain }}</td>
                            <td>
                                @if($store->is_active)
                                    <span class="badge badge-success">{{ __('store.active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ __('store.inactive') }}</span>
                                @endif
                            </td>
                            <td>{{ $store->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('store.show', $store) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="{{ __('store.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('store.edit', $store) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="{{ __('store.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('store.destroy', $store) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('store.are_you_sure_delete_store') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-bs-toggle="tooltip" title="{{ __('store.delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('store.no_stores_found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-end mt-5">
                {{ $stores->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection

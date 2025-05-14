@extends('layouts.app')

@php
$title = __('store.store_owners') . ' - ' . $store->name . ' - ' . config('app.name');
$pageTitle = __('store.store_owners') . ': ' . $store->name;
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('store.store_owners') }}: {{ $store->name }}</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.store.show', $store->id) }}" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> {{ __('store.back_to_store') }}
                </a>
                <a href="{{ route('admin.store.owners.create', $store->id) }}" class="btn btn-sm btn-primary">
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
                            <td>{{ $owner->getUserData()['id'] }}</td>
                            <td>{{ $owner->getUserData()['name'] }}</td>
                            <td>{{ $owner->getUserData()['email'] }}</td>
                            <td>
                                @php
                                    $userId = $owner->getUserData()['id'];
                                    $user = \App\Models\User::find($userId);
                                @endphp
                                <form action="{{ route('store.owners.destroy', ['store' => $store->id, 'user' => $user]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('store.are_you_sure_remove_owner') }}');">
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
@endsection

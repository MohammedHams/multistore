@extends('layouts.app')

@php
$title = 'تفاصيل المتجر' . ' - ' . $storeModel->name . ' - ' . config('app.name');
$pageTitle = 'تفاصيل المتجر' . ': ' . $storeModel->name;
@endphp

@section('content')
    <div class="card mb-5">
        <div class="card-header">
            <h3 class="card-title">تفاصيل المتجر</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.store.index') }}" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> العودة إلى المتاجر
                </a>
                <a href="{{ route('admin.store.edit', $storeModel->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i> تعديل المتجر
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
                            <th style="width: 150px;">الرقم التعريفي:</th>
                            <td>{{ $storeModel->id }}</td>
                        </tr>
                        <tr>
                            <th>الاسم:</th>
                            <td>{{ $storeModel->name }}</td>
                        </tr>
                        <tr>
                            <th>النطاق:</th>
                            <td>{{ $storeModel->domain }}</td>
                        </tr>
                        <tr>
                            <th>البريد الإلكتروني:</th>
                            <td>{{ $storeModel->email }}</td>
                        </tr>
                        <tr>
                            <th>رقم الهاتف:</th>
                            <td>{{ $storeModel->phone }}</td>
                        </tr>
                        <tr>
                            <th>الحالة:</th>
                            <td>
                                @if($storeModel->is_active)
                                    <span class="badge badge-success">نشط</span>
                                @else
                                    <span class="badge badge-danger">غير نشط</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>تاريخ الإنشاء:</th>
                            <td>{{ $storeModel->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>تاريخ التحديث:</th>
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
            <h3 class="card-title">مالكو المتجر</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.store.owners.create', $storeModel->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> إضافة مالك
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>الرقم التعريفي</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الإجراءات</th>
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
                            <td colspan="4" class="text-center">لم يتم العثور على مالكين</td>
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
            <h3 class="card-title">موظفو المتجر</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.store.staff.create', $storeModel->id) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> إضافة موظف
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>الرقم التعريفي</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الصلاحيات</th>
                            <th>الإجراءات</th>
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
                                    $staffModel = \App\Models\StoreStaff::where('store_id', $storeModel->id)
                                        ->where('user_id', $userId)
                                        ->first();

                                    // Ensure permissions is an array
                                    $staffPermissions = [];
                                    if ($staffModel && $staffModel->permissions) {
                                        if (is_string($staffModel->permissions)) {
                                            $staffPermissions = json_decode($staffModel->permissions, true) ?? [];
                                        } else {
                                            $staffPermissions = $staffModel->permissions;
                                        }
                                    }
                                @endphp

                                @foreach($staffPermissions as $permission)
                                    <span class="badge badge-primary me-1">{{ $permission }}</span>
                                @endforeach

                                @if(empty($staffPermissions))
                                    <span class="text-muted">لا توجد صلاحيات</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $userId = $staffMember->getUserData()['id'];
                                    $user = \App\Models\User::find($userId);
                                @endphp
                                <a href="{{ route('admin.store.staff.edit', [$storeModel->id, $user]) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="تعديل صلاحيات الموظف">
                                    <i class="fas fa-key"></i>
                                </a>
                                <form action="{{ route('admin.store.staff.destroy', [$storeModel->id, $user]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إزالة هذا الموظف؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-bs-toggle="tooltip" title="إزالة الموظف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">لم يتم العثور على موظفين</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

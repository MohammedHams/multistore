@extends('layouts.app')

@php
$title = 'المتاجر - ' . config('app.name');
$pageTitle = 'المتاجر';
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">المتاجر</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.store.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> إضافة متجر
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search & Filters -->
            <form action="{{ route('admin.store.index') }}" method="GET" class="mb-7">
                <div class="row mb-4">
                    <div class="col-lg-4 mb-lg-0 mb-4">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="البحث بالاسم، النطاق، أو البريد الإلكتروني" value="{{ request('search') }}">
                    </div>
                    <div class="col-lg-3 mb-lg-0 mb-4">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="">الكل</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                    </div>
                    <div class="col-lg-3 mb-lg-0 mb-4">
                        <label class="form-label">ترتيب حسب</label>
                        <select name="sort" class="form-select">
                            <option value="created_at_desc" {{ request('sort') === 'created_at_desc' ? 'selected' : '' }}>الأحدث أولاً</option>
                            <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>الأقدم أولاً</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>الاسم (أ-ي)</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>الاسم (ي-أ)</option>
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">تصفية</button>
                        <a href="{{ route('admin.store.index') }}" class="btn btn-secondary">إعادة تعيين</a>
                    </div>
                </div>
            </form>

            <!-- Stores Table -->
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>الرقم</th>
                            <th>الشعار</th>
                            <th>الاسم</th>
                            <th>النطاق</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
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
                                    <span class="badge badge-success">نشط</span>
                                @else
                                    <span class="badge badge-danger">غير نشط</span>
                                @endif
                            </td>
                            <td>{{ $store->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.store.show', $store->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.store.edit', $store->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" data-bs-toggle="tooltip" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.store.destroy', $store->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المتجر؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-bs-toggle="tooltip" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">لم يتم العثور على متاجر</td>
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

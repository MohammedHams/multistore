@extends('layouts.store-owner')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تفاصيل الموظف</h5>
                    <div>
                        <a href="{{ route('store-owner.staff.edit', $staff->id) }}" class="btn btn-primary btn-sm">تعديل</a>
                        <a href="{{ route('store-owner.staff.index') }}" class="btn btn-secondary btn-sm">العودة إلى القائمة</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">المعلومات الأساسية</h5>
                                <div class="row mb-2">
                                    <div class="col-md-4 fw-bold">الاسم:</div>
                                    <div class="col-md-8">{{ $staff->name }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 fw-bold">البريد الإلكتروني:</div>
                                    <div class="col-md-8">{{ $staff->email }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 fw-bold">رقم الهاتف:</div>
                                    <div class="col-md-8">{{ $staff->phone ?? 'غير متوفر' }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 fw-bold">الدور:</div>
                                    <div class="col-md-8">
                                        @switch($staff->role)
                                            @case('manager')
                                                <span class="badge bg-primary">مدير</span>
                                                @break
                                            @case('cashier')
                                                <span class="badge bg-success">أمين صندوق</span>
                                                @break
                                            @case('inventory')
                                                <span class="badge bg-info">مسؤول مخزون</span>
                                                @break
                                            @case('sales')
                                                <span class="badge bg-warning">مبيعات</span>
                                                @break
                                            @case('customer_service')
                                                <span class="badge bg-secondary">خدمة عملاء</span>
                                                @break
                                            @default
                                                <span class="badge bg-dark">{{ $staff->role }}</span>
                                        @endswitch
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4 fw-bold">تاريخ الإنضمام:</div>
                                    <div class="col-md-8">{{ $staff->created_at->format('Y-m-d') }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">الصلاحيات</h5>
                                @php
                                    $permissions = json_decode($staff->permissions, true) ?? [];
                                @endphp
                                
                                <div class="mb-2">
                                    <h6>إدارة المتجر:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge {{ in_array('view-store', $permissions) ? 'bg-success' : 'bg-secondary' }}">عرض المتجر</span>
                                        <span class="badge {{ in_array('edit-store', $permissions) ? 'bg-success' : 'bg-secondary' }}">تعديل المتجر</span>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <h6>إدارة المنتجات:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge {{ in_array('view-products', $permissions) ? 'bg-success' : 'bg-secondary' }}">عرض المنتجات</span>
                                        <span class="badge {{ in_array('manage-products', $permissions) ? 'bg-success' : 'bg-secondary' }}">إدارة المنتجات</span>
                                        <span class="badge {{ in_array('delete-products', $permissions) ? 'bg-success' : 'bg-secondary' }}">حذف المنتجات</span>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <h6>إدارة الطلبات:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge {{ in_array('view-orders', $permissions) ? 'bg-success' : 'bg-secondary' }}">عرض الطلبات</span>
                                        <span class="badge {{ in_array('manage-orders', $permissions) ? 'bg-success' : 'bg-secondary' }}">إدارة الطلبات</span>
                                        <span class="badge {{ in_array('delete-orders', $permissions) ? 'bg-success' : 'bg-secondary' }}">حذف الطلبات</span>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <h6>إدارة الموظفين:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge {{ in_array('manage-staff', $permissions) ? 'bg-success' : 'bg-secondary' }}">إدارة الموظفين</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between">
                        <form action="{{ route('store-owner.staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا الموظف؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">حذف الموظف</button>
                        </form>
                        <a href="{{ route('store-owner.staff.edit', $staff->id) }}" class="btn btn-primary">تعديل الموظف</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

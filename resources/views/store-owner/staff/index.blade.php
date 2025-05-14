@extends('layouts.store-owner')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">إدارة الموظفين</h5>
                    <a href="{{ route('store-owner.staff.create') }}" class="btn btn-primary btn-sm">إضافة موظف جديد</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الدور</th>
                                    <th>رقم الهاتف</th>
                                    <th>تاريخ الإنشاء</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($staff as $member)
                                    <tr>
                                        <td>{{ $member->id }}</td>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td>
                                            @switch($member->role)
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
                                                    <span class="badge bg-dark">{{ $member->role }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $member->phone ?? 'غير متوفر' }}</td>
                                        <td>{{ $member->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('store-owner.staff.show', $member->id) }}" class="btn btn-info btn-sm">عرض</a>
                                                <a href="{{ route('store-owner.staff.edit', $member->id) }}" class="btn btn-primary btn-sm">تعديل</a>
                                                <form action="{{ route('store-owner.staff.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا الموظف؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">لا يوجد موظفين.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $staff->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

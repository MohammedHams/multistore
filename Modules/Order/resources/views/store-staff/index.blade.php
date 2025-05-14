@extends('layouts.store-staff')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">الطلبات</h5>
                    <a href="{{ route('store-staff.order.create') }}" class="btn btn-primary btn-sm">إنشاء طلب</a>
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

                    <div class="mb-3">
                        <form action="{{ route('store-staff.order.index') }}" method="GET" class="row g-3">
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="payment_status" class="form-select">
                                    <option value="">جميع حالات الدفع</option>
                                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>فشل</option>
                                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>مسترد</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="start_date" class="form-control" placeholder="تاريخ البدء" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="end_date" class="form-control" placeholder="تاريخ الانتهاء" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">تصفية</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>العميل</th>
                                    <th>الإجمالي</th>
                                    <th>الحالة</th>
                                    <th>حالة الدفع</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>{{ $order->order_number }}</td>
                                        <td>{{ $order->name ?? 'غير متوفر' }}</td>
                                        <td>${{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : ($order->status == 'processing' ? 'info' : 'warning')) }}">
                                                @if($order->status == 'completed')
                                                    مكتمل
                                                @elseif($order->status == 'cancelled')
                                                    ملغي
                                                @elseif($order->status == 'processing')
                                                    قيد المعالجة
                                                @else
                                                    قيد الانتظار
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'failed' ? 'danger' : ($order->payment_status == 'refunded' ? 'info' : 'warning')) }}">
                                                @if($order->payment_status == 'paid')
                                                    مدفوع
                                                @elseif($order->payment_status == 'failed')
                                                    فشل
                                                @elseif($order->payment_status == 'refunded')
                                                    مسترد
                                                @else
                                                    قيد الانتظار
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('store-staff.order.show', $order->id) }}" class="btn btn-info btn-sm">عرض</a>
                                                <a href="{{ route('store-staff.order.edit', $order->id) }}" class="btn btn-primary btn-sm">تعديل</a>
                                                <form action="{{ route('store-staff.order.destroy', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا الطلب؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">لم يتم العثور على طلبات.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

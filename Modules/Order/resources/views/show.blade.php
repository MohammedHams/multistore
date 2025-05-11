@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">رقم الطلب: {{ $order->order_number }}</h5>
                    <div>
                        <a href="{{ route('order.index') }}" class="btn btn-secondary btn-sm">العودة إلى القائمة</a>
                        <a href="{{ route('order.edit', $order->id) }}" class="btn btn-primary btn-sm">تعديل</a>
                    </div>
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

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">معلومات الطلب</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th style="width: 150px;">رقم الطلب:</th>
                                                <td>{{ $order->order_number }}</td>
                                            </tr>
                                            <tr>
                                                <th>التاريخ:</th>
                                                <td>{{ $order->created_at->format('M d, Y H:i:s') }}</td>
                                            </tr>
                                            <tr>
                                                <th>الحالة:</th>
                                                <td>
                                                    <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : ($order->status == 'processing' ? 'info' : 'warning')) }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>حالة الدفع:</th>
                                                <td>
                                                    <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'failed' ? 'danger' : ($order->payment_status == 'refunded' ? 'info' : 'warning')) }}">
                                                        {{ ucfirst($order->payment_status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>طريقة الدفع:</th>
                                                <td>{{ $order->payment_method ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>المبلغ الإجمالي:</th>
                                                <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">معلومات العميل</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th style="width: 150px;">العميل:</th>
                                                <td>{{ $order->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>البريد الإلكتروني:</th>
                                                <td>{{ $order->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>عنوان الشحن:</th>
                                                <td>{{ $order->shipping_address ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>عنوان الفواتير:</th>
                                                <td>{{ $order->billing_address ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">عناصر الطلب</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>المنتج</th>
                                                    <th>رمز المنتج</th>
                                                    <th class="text-center">السعر</th>
                                                    <th class="text-center">الكمية</th>
                                                    <th class="text-end">المجموع الفرعي</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($order->getItems() as $item)
                                                    <tr>
                                                        <td>
                                                            {{ $item['product_name'] ?? 'Unknown Product' }}
                                                        </td>
                                                        <td>{{ $item['product_sku'] ?? 'N/A' }}</td>
                                                        <td class="text-center">${{ number_format($item['price'], 2) }}</td>
                                                        <td class="text-center">{{ $item['quantity'] }}</td>
                                                        <td class="text-end">${{ number_format($item['subtotal'], 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">لا توجد عناصر</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="4" class="text-end">المجموع:</th>
                                                    <th class="text-end">${{ number_format($order->total_amount, 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">تحديث حالة الطلب</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('order.update.status', $order->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label for="status" class="form-label">حالة الطلب</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>قيد المعالجة</option>
                                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">تحديث الحالة</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">تحديث حالة الدفع</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('order.update.payment-status', $order->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label for="payment_status" class="form-label">حالة الدفع</label>
                                            <select class="form-select" id="payment_status" name="payment_status" required>
                                                <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                                                <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>مدفوع</option>
                                                <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>فشل</option>
                                                <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>مسترجع</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">تحديث حالة الدفع</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($order->notes)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">ملاحظات</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $order->notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

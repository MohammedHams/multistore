@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تعديل الطلب #{{ $order->order_number }}</h5>
                    <div>
                        <a href="{{ route('order.index') }}" class="btn btn-secondary btn-sm">العودة إلى القائمة</a>
                        <a href="{{ route('order.show', $order->id) }}" class="btn btn-info btn-sm">عرض التفاصيل</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('order.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">عنوان الشحن</label>
                                    <textarea class="form-control @error('shipping_address') is-invalid @enderror" id="shipping_address" name="shipping_address" rows="3">{{ old('shipping_address', $order->shipping_address) }}</textarea>
                                    @error('shipping_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="billing_address" class="form-label">عنوان الفواتير</label>
                                    <textarea class="form-control @error('billing_address') is-invalid @enderror" id="billing_address" name="billing_address" rows="3">{{ old('billing_address', $order->billing_address) }}</textarea>
                                    @error('billing_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">طريقة الدفع</label>
                                    <input type="text" class="form-control @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" value="{{ old('payment_method', $order->payment_method) }}">
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">ملاحظات</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $order->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('order.show', $order->id) }}" class="btn btn-secondary">إلغاء</a>
                            <button type="submit" class="btn btn-primary">تحديث الطلب</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

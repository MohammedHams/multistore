@extends('layouts.store-owner')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تفاصيل المنتج</h5>
                    <div>
                        <a href="{{ route('store-owner.product.edit', $product->id) }}" class="btn btn-primary btn-sm">تعديل</a>
                        <a href="{{ route('store-owner.product.index') }}" class="btn btn-secondary btn-sm">العودة إلى القائمة</a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            @if ($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid rounded">
                            @else
                                <div class="text-center p-5 bg-light rounded">
                                    <span class="text-muted">لا توجد صورة</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <h3>{{ $product->name }}</h3>
                            <p class="text-muted">رمز المنتج: {{ $product->sku }}</p>
                            
                            <div class="mb-3">
                                <h5>السعر:</h5>
                                <p class="fs-4">${{ number_format($product->price, 2) }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <h5>المخزون:</h5>
                                <p>{{ $product->stock }} وحدة</p>
                                
                                <form action="{{ route('store-owner.product.update.stock', $product->id) }}" method="POST" class="row g-3 mt-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="number" name="stock" class="form-control" value="{{ $product->stock }}" min="0">
                                            <button type="submit" class="btn btn-primary">تحديث المخزون</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="mb-3">
                                <h5>الحالة:</h5>
                                <p>
                                    <span class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                        {{ $product->is_active ? 'نشط' : 'غير نشط' }}
                                    </span>
                                </p>
                            </div>
                            
                            <div class="mb-3">
                                <h5>الوصف:</h5>
                                <p>{{ $product->description ?? 'لا يوجد وصف' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

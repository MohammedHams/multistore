@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">تفاصيل المنتج</h5>
                    <div>
                        <a href="{{ route('product.index') }}" class="btn btn-secondary btn-sm">العودة إلى القائمة</a>
                        <a href="{{ route('product.edit', $product->id) }}" class="btn btn-primary btn-sm">تعديل</a>
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

                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center mb-4">
                                @if ($product->image)
                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-height: 300px;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 300px;">
                                        <span class="text-muted">لا توجد صورة متاحة</span>
                                    </div>
                                @endif
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">إدارة المخزون</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('product.update.stock', $product->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">الكمية</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="increase" id="increase" value="1" checked>
                                                <label class="form-check-label" for="increase">زيادة المخزون</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="increase" id="decrease" value="0">
                                                <label class="form-check-label" for="decrease">تقليل المخزون</label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">تحديث المخزون</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 200px;">رقم المنتج</th>
                                        <td>{{ $product->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>الاسم</th>
                                        <td>{{ $product->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>رمز المنتج</th>
                                        <td>{{ $product->sku }}</td>
                                    </tr>
                                    <tr>
                                        <th>السعر</th>
                                        <td>${{ number_format($product->price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>المخزون</th>
                                        <td>
                                            <span class="badge bg-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                                                {{ $product->stock }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>الحالة</th>
                                        <td>
                                            <span class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                                {{ $product->is_active ? 'نشط' : 'غير نشط' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>المتجر</th>
                                        <td>
                                            <a href="{{ route('store.show', $product->store_id) }}">
                                                {{ $product->store_id }}
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ الإنشاء</th>
                                        <td>{{ $product->created_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>تاريخ التحديث</th>
                                        <td>{{ $product->updated_at->format('M d, Y H:i:s') }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="mt-4">
                                <h6>{{ __('product::products.description') }}</h6>
                                <div class="p-3 bg-light rounded">
                                    {!! nl2br(e($product->description)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

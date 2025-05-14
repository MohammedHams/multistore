@extends('layouts.store-owner')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">المنتجات</h5>
                    <a href="{{ route('store-owner.product.create') }}" class="btn btn-primary btn-sm">إنشاء منتج</a>
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
                        <form action="{{ route('store-owner.product.index') }}" method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="بحث عن منتجات..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="min_price" class="form-control" placeholder="الحد الأدنى للسعر" value="{{ request('min_price') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="max_price" class="form-control" placeholder="الحد الأقصى للسعر" value="{{ request('max_price') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="is_active" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>نشط</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">تصفية</button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('store-owner.product.index') }}" class="btn btn-secondary w-100">إعادة تعيين</a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>الصورة</th>
                                    <th>الاسم</th>
                                    <th>رمز المنتج</th>
                                    <th>السعر</th>
                                    <th>المخزون</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td>
                                            @if ($product->image)
                                                <img src="{{ $product->image }}" alt="{{ $product->name }}" width="50">
                                            @else
                                                <span class="text-muted">لا توجد صورة</span>
                                            @endif
                                        </td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->sku }}</td>
                                        <td>${{ number_format($product->price, 2) }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td>
                                            <span class="badge bg-{{ $product->is_active ? 'success' : 'danger' }}">
                                                {{ $product->is_active ? 'نشط' : 'غير نشط' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('store-owner.product.show', $product->id) }}" class="btn btn-info btn-sm">عرض</a>
                                                <a href="{{ route('store-owner.product.edit', $product->id) }}" class="btn btn-primary btn-sm">تعديل</a>
                                                <form action="{{ route('store-owner.product.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد أنك تريد حذف هذا المنتج؟');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">لا توجد منتجات.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

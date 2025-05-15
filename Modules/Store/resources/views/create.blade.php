@extends('layouts.app')

@php
$title = 'إنشاء متجر - ' . config('app.name');
$pageTitle = 'إنشاء متجر';
@endphp

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">إنشاء متجر</h3>
            <div class="card-toolbar">
                <a href="{{ route('admin.store.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> العودة إلى المتاجر
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.store.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label for="name" class="form-label required">اسم المتجر</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="domain" class="form-label required">نطاق المتجر</label>
                    <input type="text" class="form-control @error('domain') is-invalid @enderror" id="domain" name="domain" value="{{ old('domain') }}" required>
                    @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">نطاق المتجر (مثال: mystore.com)</div>
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label required">البريد الإلكتروني</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="phone" class="form-label required">رقم الهاتف</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="logo" class="form-label">شعار المتجر</label>
                    <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">الحجم الموصى به: 200x200px. الحجم الأقصى: 2MB.</div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">نشط</label>
                    </div>
                    <div class="form-text">إذا تم تحديده، سيكون المتجر نشطًا</div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> إنشاء متجر
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

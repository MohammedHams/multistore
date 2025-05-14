@extends('layouts.auth')

@section('auth_title', 'تسجيل الدخول - مالك المتجر')
@section('auth_description', 'قم بتسجيل الدخول كمالك متجر للوصول إلى لوحة التحكم')

@section('content')
<!--begin::Form-->
<form class="form w-100" method="POST" action="{{ route('store-owner.login.submit') }}">
    @csrf
    <!--begin::Heading-->
    <div class="text-center mb-10">
        <!--begin::Title-->
        <h1 class="text-dark mb-3">{{ __('تسجيل دخول مالك المتجر') }}</h1>
        <!--end::Title-->
    </div>
    <!--begin::Heading-->

    <!--begin::Input group-->
    <div class="fv-row mb-10">
        <!--begin::Label-->
        <label class="form-label fs-6 fw-bolder text-dark">{{ __('البريد الإلكتروني') }}</label>
        <!--end::Label-->
        <!--begin::Input-->
        <input id="email" type="email" class="form-control form-control-lg form-control-solid @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus />
        <!--end::Input-->
        @error('email')
        <div class="fv-plugins-message-container invalid-feedback">
            <div>{{ $message }}</div>
        </div>
        @enderror
    </div>
    <!--end::Input group-->

    <!--begin::Input group-->
    <div class="fv-row mb-10">
        <!--begin::Wrapper-->
        <div class="d-flex flex-stack mb-2">
            <!--begin::Label-->
            <label class="form-label fw-bolder text-dark fs-6 mb-0">{{ __('كلمة المرور') }}</label>
            <!--end::Label-->
            <!--begin::Link-->
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="link-primary fs-6 fw-bolder">{{ __('نسيت كلمة المرور؟') }}</a>
            @endif
            <!--end::Link-->
        </div>
        <!--end::Wrapper-->
        <!--begin::Input-->
        <input id="password" type="password" class="form-control form-control-lg form-control-solid @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" />
        <!--end::Input-->
        @error('password')
        <div class="fv-plugins-message-container invalid-feedback">
            <div>{{ $message }}</div>
        </div>
        @enderror
    </div>
    <!--end::Input group-->

    <!--begin::Actions-->
    <div class="text-center">
        <!--begin::Submit button-->
        <button type="submit" id="kt_sign_in_submit" class="btn btn-lg btn-primary w-100 mb-5">
            <span class="indicator-label">{{ __('تسجيل الدخول') }}</span>
        </button>
        <!--end::Submit button-->
        <!--begin::Separator-->
        <div class="text-center text-muted text-uppercase fw-bolder mb-5">أو</div>
        <!--end::Separator-->
        <!--begin::Remember-->
        <div class="fv-row mb-10">
            <label class="form-check form-check-custom form-check-solid mb-5">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                <span class="form-check-label fw-bold text-gray-700">{{ __('تذكرني') }}</span>
            </label>
        </div>
        <!--end::Remember-->
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->
@endsection

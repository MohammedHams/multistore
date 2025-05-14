@extends('layouts.auth')

@section('title', 'المصادقة الثنائية - Multistore')
@section('auth_title', 'المصادقة الثنائية')
@section('auth_description', 'أدخل رمز المصادقة للمتابعة')

@section('content')
<!--begin::Form-->
<form class="form w-100" action="{{ route('two-factor.challenge.submit') }}" method="POST" id="kt_two_factor_form">
    @csrf
    
    <!--begin::Heading-->
    <div class="text-center mb-10">
        <!--begin::Title-->
        <h1 class="text-dark mb-3">المصادقة الثنائية</h1>
        <!--end::Title-->
        <!--begin::Link-->
        <div class="text-gray-400 fw-bold fs-4">أدخل رمز المصادقة من تطبيق المصادقة الخاص بك أو استخدم رمز الاسترداد.</div>
        <!--end::Link-->
        
        @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
        
        @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
        @endif
        
        @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    <!--begin::Heading-->

    @if ($errors->any())
    <!--begin::Alert-->
    <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
        <!--begin::Icon-->
        <span class="svg-icon svg-icon-2hx svg-icon-danger me-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="black"></path>
                <path d="M11.1731 9.58675L11.1731 9.58675C11.4507 9.32551 11.4507 8.90852 11.1731 8.64728C10.9094 8.3938 10.4579 8.39168 10.1942 8.64516L6.93359 11.8358L5.54822 10.4783C5.27249 10.2081 4.82503 10.2081 4.5493 10.4783C4.28203 10.7396 4.28203 11.1665 4.5493 11.4278L6.31255 13.1632C6.31623 13.167 6.32 13.1708 6.32386 13.1745C6.46515 13.3128 6.65799 13.3871 6.85186 13.3871C7.04572 13.3871 7.23857 13.3128 7.37985 13.1745C7.38371 13.1708 7.38748 13.167 7.39116 13.1632L11.1731 9.58675Z" fill="black"></path>
            </svg>
        </span>
        <!--end::Icon-->
        
        <!--begin::Wrapper-->
        <div class="d-flex flex-column">
            <!--begin::Content-->
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <!--end::Content-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Alert-->
    @endif

    <div class="mb-10">
        <!--begin::Input group-->
        <div class="fv-row mb-10">
            <label class="form-label fw-bolder text-gray-900 fs-6">رمز التحقق</label>
            <input class="form-control form-control-lg form-control-solid" type="text" name="code" autocomplete="off" />
            <div class="form-text">
                <a href="{{ route('two-factor.resend') }}" class="link-primary">إرسال رمز جديد</a>
            </div>
        </div>
        <!--end::Input group-->
        
        <!--begin::Input group-->
        <div class="fv-row mb-10">
            <label class="form-label fw-bolder text-gray-900 fs-6">أو أدخل رمز الاسترداد</label>
            <input class="form-control form-control-lg form-control-solid" type="text" name="recovery_code" autocomplete="off" />
        </div>
        <!--end::Input group-->
    </div>
    
    <!--begin::Actions-->
    <div class="text-center">
        <button type="submit" id="kt_two_factor_submit" class="btn btn-lg btn-primary w-100 mb-5">
            <span class="indicator-label">متابعة</span>
            <span class="indicator-progress">يرجى الانتظار...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->
@endsection

@push('scripts')
<script>
    // Simple form handling without validation
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.querySelector('#kt_two_factor_form');
        var submitButton = document.querySelector('#kt_two_factor_submit');
        
        // Handle tab changes
        const tabLinks = document.querySelectorAll('.nav-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Reset any error messages
                document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            });
        });
        
        // Handle submit button click
        submitButton.addEventListener('click', function(e) {
            // Show loading indication
            submitButton.setAttribute('data-kt-indicator', 'on');
            
            // Disable button to avoid multiple clicks
            submitButton.disabled = true;
            
            // Submit the form
            form.submit();
        });
    });
</script>
@endpush

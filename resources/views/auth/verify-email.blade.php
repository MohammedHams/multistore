@extends('layouts.auth')

@section('title', 'تحقق من البريد الإلكتروني - Multistore')
@section('auth_title', 'التحقق من البريد الإلكتروني')
@section('auth_description', 'تحقق من عنوان بريدك الإلكتروني للمتابعة')

@section('content')
<!--begin::Form-->
<div class="form w-100">
    <!--begin::Heading-->
    <div class="text-center mb-10">
        <!--begin::Title-->
        <h1 class="text-dark mb-3">تحقق من عنوان بريدك الإلكتروني</h1>
        <!--end::Title-->
        <!--begin::Link-->
        <div class="text-gray-400 fw-bold fs-4">
            شكراً للتسجيل! قبل البدء، هل يمكنك التحقق من عنوان بريدك الإلكتروني بالنقر على الرابط الذي أرسلناه لتونا إليك؟ إذا لم تستلم البريد الإلكتروني، فسنرسل لك رسالة أخرى بكل سرور.
        </div>
        <!--end::Link-->
    </div>
    <!--begin::Heading-->

    @if (session('status') == 'verification-link-sent')
    <!--begin::Alert-->
    <div class="alert alert-success d-flex align-items-center p-5 mb-10">
        <!--begin::Icon-->
        <span class="svg-icon svg-icon-2hx svg-icon-success me-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="black"></path>
                <path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="black"></path>
            </svg>
        </span>
        <!--end::Icon-->
        
        <!--begin::Wrapper-->
        <div class="d-flex flex-column">
            <!--begin::Content-->
            <span>تم إرسال رابط تحقق جديد إلى عنوان البريد الإلكتروني الذي قدمته أثناء التسجيل.</span>
            <!--end::Content-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Alert-->
    @endif

    <!--begin::Actions-->
    <div class="d-flex flex-wrap justify-content-center pb-lg-0">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" id="kt_resend_verification_submit" class="btn btn-lg btn-primary fw-bolder me-4">
                <span class="indicator-label">إعادة إرسال بريد التحقق</span>
                <span class="indicator-progress">يرجى الانتظار...
                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </form>
        
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-lg btn-light-primary fw-bolder">تسجيل الخروج</button>
        </form>
    </div>
    <!--end::Actions-->
</div>
<!--end::Form-->
@endsection

@push('scripts')
<script>
    // Handle resend button
    var KTVerifyEmail = function() {
        // Elements
        var form;
        var submitButton;

        // Handle form
        var handleForm = function(e) {
            // Handle form submit
            submitButton.addEventListener('click', function (e) {
                // Show loading indication
                submitButton.setAttribute('data-kt-indicator', 'on');

                // Disable button to avoid multiple click 
                submitButton.disabled = true;
            });
        }

        // Public functions
        return {
            // Initialization
            init: function() {
                form = document.querySelector('#kt_resend_verification_submit').closest('form');
                submitButton = document.querySelector('#kt_resend_verification_submit');
                
                handleForm();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        KTVerifyEmail.init();
    });
</script>
@endpush

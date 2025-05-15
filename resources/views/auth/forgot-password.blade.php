@extends('layouts.auth')

@section('title', 'نسيت كلمة المرور - Multistore')
@section('auth_title', 'نسيت كلمة المرور؟')
@section('auth_description', 'أدخل بريدك الإلكتروني لإعادة تعيين كلمة المرور')

@section('content')
<!--begin::Form-->
<form class="form w-100" action="{{ route('password.email') }}" method="POST" novalidate="novalidate" id="kt_password_reset_form">
    @csrf
    <!--begin::Heading-->
    <div class="text-center mb-10">
        <!--begin::Title-->
        <h1 class="text-dark mb-3">نسيت كلمة المرور؟</h1>
        <!--end::Title-->
        <!--begin::Link-->
        <div class="text-gray-400 fw-bold fs-4">أدخل بريدك الإلكتروني لإعادة تعيين كلمة المرور.</div>
        <!--end::Link-->
    </div>
    <!--begin::Heading-->

    @if (session('status'))
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
            <span>{{ session('status') }}</span>
            <!--end::Content-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Alert-->
    @endif

    @error('email')
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
            <span>{{ $message }}</span>
            <!--end::Content-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Alert-->
    @enderror

    <!--begin::Input group-->
    <div class="fv-row mb-10">
        <label class="form-label fw-bolder text-gray-900 fs-6">البريد الإلكتروني</label>
        <input class="form-control form-control-solid @error('email') is-invalid @enderror" type="email" placeholder="" name="email" value="{{ old('email') }}" autocomplete="off" />
    </div>
    <!--end::Input group-->
    
    <!--begin::Actions-->
    <div class="d-flex flex-wrap justify-content-center pb-lg-0">
        <button type="submit" id="kt_password_reset_submit" class="btn btn-lg btn-primary fw-bolder me-4">
            <span class="indicator-label">إرسال</span>
            <span class="indicator-progress">يرجى الانتظار...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
        <a href="{{ route('/') }}" class="btn btn-lg btn-light-primary fw-bolder">إلغاء</a>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->
@endsection

@push('scripts')
<script>
    // Form validation
    var KTPasswordResetGeneral = function() {
        // Elements
        var form;
        var submitButton;
        var validator;

        // Handle form
        var handleForm = function(e) {
            // Init form validation rules
            validator = FormValidation.formValidation(
                form,
                {
                    fields: {					
                        'email': {
                            validators: {
                                notEmpty: {
                                    message: 'البريد الإلكتروني مطلوب'
                                },
                                emailAddress: {
                                    message: 'القيمة ليست عنوان بريد إلكتروني صالح'
                                }
                            }
                        } 
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger(),
                        bootstrap: new FormValidation.plugins.Bootstrap5({
                            rowSelector: '.fv-row',
                            eleInvalidClass: '',
                            eleValidClass: ''
                        })
                    }
                }
            );		

            // Handle form submit
            submitButton.addEventListener('click', function (e) {
                // Prevent button default action
                e.preventDefault();

                // Validate form
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple clicks
                        submitButton.disabled = true;
                        
                        // Submit form
                        form.submit();
                    } else {
                        // Show error popup
                        Swal.fire({
                            text: "عذراً، يبدو أنه تم اكتشاف بعض الأخطاء، يرجى المحاولة مرة أخرى.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "حسناً، فهمت!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                });
            });
        }

        // Public functions
        return {
            // Initialization
            init: function() {
                form = document.querySelector('#kt_password_reset_form');
                submitButton = document.querySelector('#kt_password_reset_submit');
                
                handleForm();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        KTPasswordResetGeneral.init();
    });
</script>
@endpush

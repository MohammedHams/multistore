@extends('layouts.auth')

@section('title', 'المصادقة الثنائية - Multistore')
@section('auth_title', 'المصادقة الثنائية')
@section('auth_description', 'أدخل رمز المصادقة للمتابعة')

@section('content')
<!--begin::Form-->
<form class="form w-100" action="{{ route('two-factor.login') }}" method="POST" novalidate="novalidate" id="kt_two_factor_form">
    @csrf
    
    <!--begin::Heading-->
    <div class="text-center mb-10">
        <!--begin::Title-->
        <h1 class="text-dark mb-3">المصادقة الثنائية</h1>
        <!--end::Title-->
        <!--begin::Link-->
        <div class="text-gray-400 fw-bold fs-4">أدخل رمز المصادقة من تطبيق المصادقة الخاص بك أو استخدم رمز الاسترداد.</div>
        <!--end::Link-->
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
        <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_code">رمز المصادقة</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_email">رمز البريد الإلكتروني</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_sms">رمز الرسائل القصيرة</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_recovery">رمز الاسترداد</a>
            </li>
        </ul>
        
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="kt_tab_pane_code" role="tabpanel">
                <!--begin::Input group-->
                <div class="fv-row mb-10">
                    <label class="form-label fw-bolder text-gray-900 fs-6">رمز المصادقة</label>
                    <input class="form-control form-control-lg form-control-solid" type="text" name="code" autocomplete="off" />
                </div>
                <!--end::Input group-->
            </div>
            <div class="tab-pane fade" id="kt_tab_pane_email" role="tabpanel">
                <!--begin::Input group-->
                <div class="fv-row mb-10">
                    <label class="form-label fw-bolder text-gray-900 fs-6">رمز البريد الإلكتروني</label>
                    <input class="form-control form-control-lg form-control-solid" type="text" name="code" autocomplete="off" />
                    <input type="hidden" name="method" value="email" />
                    <div class="form-text">
                        <a href="{{ route('two-factor.resend') }}?method=email" class="link-primary">إرسال رمز جديد</a>
                    </div>
                </div>
                <!--end::Input group-->
            </div>
            <div class="tab-pane fade" id="kt_tab_pane_sms" role="tabpanel">
                <!--begin::Input group-->
                <div class="fv-row mb-10">
                    <label class="form-label fw-bolder text-gray-900 fs-6">رمز الرسائل القصيرة</label>
                    <input class="form-control form-control-lg form-control-solid" type="text" name="code" autocomplete="off" />
                    <input type="hidden" name="method" value="sms" />
                    <div class="form-text">
                        <a href="{{ route('two-factor.resend') }}?method=sms" class="link-primary">إرسال رمز جديد</a>
                    </div>
                </div>
                <!--end::Input group-->
            </div>
            <div class="tab-pane fade" id="kt_tab_pane_recovery" role="tabpanel">
                <!--begin::Input group-->
                <div class="fv-row mb-10">
                    <label class="form-label fw-bolder text-gray-900 fs-6">رمز الاسترداد</label>
                    <input class="form-control form-control-lg form-control-solid" type="text" name="recovery_code" autocomplete="off" />
                </div>
                <!--end::Input group-->
            </div>
        </div>
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
    // Form validation
    var KTTwoFactorGeneral = function() {
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
                        'code': {
                            validators: {
                                callback: {
                                    message: 'الرجاء إدخال رمز مصادقة صالح',
                                    callback: function(input) {
                                        const value = input.value;
                                        if (!value) {
                                            return false;
                                        }
                                        
                                        // Check if the code tab is active
                                        if (document.querySelector('#kt_tab_pane_code').classList.contains('active')) {
                                            return value.length === 6 && /^[0-9]+$/.test(value);
                                        }
                                        
                                        // Check if the email OTP tab is active
                                        if (document.querySelector('#kt_tab_pane_email').classList.contains('active')) {
                                            return value.length === 6 && /^[0-9]+$/.test(value);
                                        }
                                        
                                        return true;
                                    }
                                }
                            }
                        },
                        'recovery_code': {
                            validators: {
                                callback: {
                                    message: 'الرجاء إدخال رمز استرداد صالح',
                                    callback: function(input) {
                                        const value = input.value;
                                        
                                        // Check if the recovery tab is active
                                        if (document.querySelector('#kt_tab_pane_recovery').classList.contains('active')) {
                                            return value.length > 0;
                                        }
                                        
                                        return true;
                                    }
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

            // Handle tab changes
            const tabLinks = document.querySelectorAll('.nav-link');
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    validator.resetForm();
                });
            });

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
                        // Add error class to the form controls instead of showing a popup
                        const activeTab = document.querySelector('.tab-pane.active');
                        const inputField = activeTab.querySelector('input');
                        if (inputField) {
                            inputField.classList.add('is-invalid');
                            
                            // Add error message below the input
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback d-block';
                            errorDiv.textContent = 'الرجاء إدخال رمز مصادقة أو رمز استرداد صالح.';
                            
                            // Remove any existing error message
                            const existingError = inputField.parentNode.querySelector('.invalid-feedback');
                            if (existingError) {
                                existingError.remove();
                            }
                            
                            // Add the new error message
                            inputField.parentNode.appendChild(errorDiv);
                        }
                    }
                });
            });
        }

        // Public functions
        return {
            // Initialization
            init: function() {
                form = document.querySelector('#kt_two_factor_form');
                submitButton = document.querySelector('#kt_two_factor_submit');
                
                handleForm();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        KTTwoFactorGeneral.init();
    });
</script>
@endpush

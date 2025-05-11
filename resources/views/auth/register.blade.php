@extends('layouts.auth')

@section('title', 'Register - Multistore')
@section('auth_title', 'Join Our Community')
@section('auth_description', 'Create an account to get started with Multistore')

@section('content')
<!--begin::Form-->
<form class="form w-100" action="{{ route('register') }}" method="POST" novalidate="novalidate" id="kt_sign_up_form">
    @csrf
    <!--begin::Heading-->
    <div class="text-center mb-10">
        <!--begin::Title-->
        <h1 class="text-dark mb-3">Create an Account</h1>
        <!--end::Title-->
        <!--begin::Link-->
        <div class="text-gray-400 fw-bold fs-4">Already have an account?
        <a href="{{ route('login') }}" class="link-primary fw-bolder">Sign in here</a></div>
        <!--end::Link-->
    </div>
    <!--end::Heading-->

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

    <!--begin::Input group-->
    <div class="fv-row mb-7">
        <label class="form-label fw-bolder text-dark fs-6">Name</label>
        <input class="form-control form-control-lg form-control-solid @error('name') is-invalid @enderror" type="text" placeholder="" name="name" value="{{ old('name') }}" autocomplete="off" />
    </div>
    <!--end::Input group-->

    <!--begin::Input group-->
    <div class="fv-row mb-7">
        <label class="form-label fw-bolder text-dark fs-6">Email</label>
        <input class="form-control form-control-lg form-control-solid @error('email') is-invalid @enderror" type="email" placeholder="" name="email" value="{{ old('email') }}" autocomplete="off" />
    </div>
    <!--end::Input group-->

    <!--begin::Input group-->
    <div class="mb-10 fv-row" data-kt-password-meter="true">
        <!--begin::Wrapper-->
        <div class="mb-1">
            <!--begin::Label-->
            <label class="form-label fw-bolder text-dark fs-6">Password</label>
            <!--end::Label-->
            <!--begin::Input wrapper-->
            <div class="position-relative mb-3">
                <input class="form-control form-control-lg form-control-solid @error('password') is-invalid @enderror" type="password" placeholder="" name="password" autocomplete="off" />
                <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2" data-kt-password-meter-control="visibility">
                    <i class="bi bi-eye-slash fs-2"></i>
                    <i class="bi bi-eye fs-2 d-none"></i>
                </span>
            </div>
            <!--end::Input wrapper-->
            <!--begin::Meter-->
            <div class="d-flex align-items-center mb-3" data-kt-password-meter-control="highlight">
                <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
            </div>
            <!--end::Meter-->
        </div>
        <!--end::Wrapper-->
        <!--begin::Hint-->
        <div class="text-muted">Use 8 or more characters with a mix of letters, numbers &amp; symbols.</div>
        <!--end::Hint-->
    </div>
    <!--end::Input group-->

    <!--begin::Input group-->
    <div class="fv-row mb-5">
        <label class="form-label fw-bolder text-dark fs-6">Confirm Password</label>
        <input class="form-control form-control-lg form-control-solid" type="password" placeholder="" name="password_confirmation" autocomplete="off" />
    </div>
    <!--end::Input group-->

    <!--begin::Input group-->
    <div class="fv-row mb-10">
        <label class="form-check form-check-custom form-check-solid form-check-inline">
            <input class="form-check-input" type="checkbox" name="terms" value="1" />
            <span class="form-check-label fw-bold text-gray-700 fs-6">I Agree
            <a href="#" class="ms-1 link-primary">Terms and conditions</a>.</span>
        </label>
    </div>
    <!--end::Input group-->

    <!--begin::Actions-->
    <div class="text-center">
        <button type="submit" id="kt_sign_up_submit" class="btn btn-lg btn-primary">
            <span class="indicator-label">Submit</span>
            <span class="indicator-progress">Please wait...
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
    var KTSignupGeneral = function() {
        // Elements
        var form;
        var submitButton;
        var validator;
        var passwordMeter;

        // Handle form
        var handleForm  = function(e) {
            // Init form validation rules
            validator = FormValidation.formValidation(
                form,
                {
                    fields: {
                        'name': {
                            validators: {
                                notEmpty: {
                                    message: 'Name is required'
                                }
                            }
                        },
                        'email': {
                            validators: {
                                notEmpty: {
                                    message: 'Email address is required'
                                },
                                emailAddress: {
                                    message: 'The value is not a valid email address'
                                }
                            }
                        },
                        'password': {
                            validators: {
                                notEmpty: {
                                    message: 'The password is required'
                                },
                                callback: {
                                    message: 'Please enter valid password',
                                    callback: function(input) {
                                        if (input.value.length > 0) {
                                            return validatePassword();
                                        }
                                    }
                                }
                            }
                        },
                        'password_confirmation': {
                            validators: {
                                notEmpty: {
                                    message: 'The password confirmation is required'
                                },
                                identical: {
                                    compare: function() {
                                        return form.querySelector('[name="password"]').value;
                                    },
                                    message: 'The password and its confirm are not the same'
                                }
                            }
                        },
                        'terms': {
                            validators: {
                                notEmpty: {
                                    message: 'You must accept the terms and conditions'
                                }
                            }
                        }
                    },
                    plugins: {
                        trigger: new FormValidation.plugins.Trigger({
                            event: {
                                password: false
                            }  
                        }),
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
                e.preventDefault();

                validator.revalidateField('password');

                validator.validate().then(function(status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // Submit form
                        form.submit();
                    } else {
                        // Show error popup
                        Swal.fire({
                            text: "Sorry, looks like there are some errors detected, please try again.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                });
            });

            // Handle password input
            form.querySelector('input[name="password"]').addEventListener('input', function() {
                if (this.value.length > 0) {
                    validator.updateFieldStatus('password', 'NotValidated');
                }
            });
        }

        // Password input validation
        var validatePassword = function() {
            // Only check if password is at least 8 characters long
            return form.querySelector('[name="password"]').value.length >= 8;
        }

        // Public functions
        return {
            // Initialization
            init: function() {
                // Elements
                form = document.querySelector('#kt_sign_up_form');
                submitButton = document.querySelector('#kt_sign_up_submit');
                passwordMeter = KTPasswordMeter.getInstance(form.querySelector('[data-kt-password-meter="true"]'));

                handleForm();
            }
        };
    }();

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        KTSignupGeneral.init();
    });
</script>
@endpush

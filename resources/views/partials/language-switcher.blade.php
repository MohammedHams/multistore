<!--begin::Language Switcher-->
<div class="d-flex align-items-center ms-1 ms-lg-3">
    <!--begin::Menu wrapper-->
    <div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
        @if(app()->getLocale() == 'ar')
            <span class="symbol-label bg-light">
                <img class="w-20px h-20px rounded-1" src="{{ asset('assets/media/flags/saudi-arabia.svg') }}" alt="Arabic"/>
            </span>
        @else
            <span class="symbol-label bg-light">
                <img class="w-20px h-20px rounded-1" src="{{ asset('assets/media/flags/united-states.svg') }}" alt="English"/>
            </span>
        @endif
    </div>
    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-bold w-200px py-3" data-kt-menu="true">
        <!--begin::Heading-->
        <div class="menu-item px-3">
            <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">
                {{ __('Languages') }}
            </div>
        </div>
        <!--end::Heading-->
        
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <a href="{{ route('language.switch', 'en') }}" class="menu-link d-flex px-5 @if(app()->getLocale() != 'ar') active @endif">
                <span class="symbol symbol-20px me-4">
                    <img class="rounded-1" src="{{ asset('assets/media/flags/united-states.svg') }}" alt="English"/>
                </span>
                English (LTR)
            </a>
        </div>
        <!--end::Menu item-->
        
        <!--begin::Menu item-->
        <div class="menu-item px-3">
            <a href="{{ route('language.switch', 'ar') }}" class="menu-link d-flex px-5 @if(app()->getLocale() == 'ar') active @endif">
                <span class="symbol symbol-20px me-4">
                    <img class="rounded-1" src="{{ asset('assets/media/flags/saudi-arabia.svg') }}" alt="Arabic"/>
                </span>
                العربية (RTL)
            </a>
        </div>
        <!--end::Menu item-->
    </div>
    <!--end::Menu-->
    <!--end::Menu wrapper-->
</div>
<!--end::Language Switcher-->



<!--begin::Aside-->
<div id="kt_aside" class="aside bg-success" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="auto" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_toggle">
    <!--begin::Logo-->
    <div class="aside-logo d-none d-lg-flex flex-column align-items-center flex-column-auto py-8" id="kt_aside_logo">
        <a href="{{ route('store-staff.dashboard') }}">
            <img alt="Logo" src="{{ asset('assets/media/logos/logo-demo-6.svg') }}" class="h-55px" />
        </a>
    </div>
    <!--end::Logo-->
    <!--begin::Nav-->
    <div class="aside-nav d-flex flex-column align-lg-center flex-column-fluid w-100 pt-5 pt-lg-0" id="kt_aside_nav">
        <!--begin::Primary menu-->
        <div id="kt_aside_menu" class="menu menu-column menu-title-gray-600 menu-state-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500 fw-bold fs-6" data-kt-menu="true">
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-staff.dashboard') ? 'active' : '' }}" href="{{ route('store-staff.dashboard') }}">
                    <span class="menu-icon">
                        <i class="bi bi-house fs-3"></i>
                    </span>
                    <span class="menu-title">Dashboard</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-staff.store.*') ? 'active' : '' }}" href="{{ route('store-staff.dashboard') }}">
                    <span class="menu-icon">
                        <i class="bi bi-shop fs-3"></i>
                    </span>
                    <span class="menu-title">Store Info</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-staff.product.*') ? 'active' : '' }}" href="{{ route('store-staff.product.index') }}">
                    <span class="menu-icon">
                        <i class="bi bi-box fs-3"></i>
                    </span>
                    <span class="menu-title">Products</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-staff.order.*') ? 'active' : '' }}" href="{{ route('store-staff.order.index') }}">
                    <span class="menu-icon">
                        <i class="bi bi-cart fs-3"></i>
                    </span>
                    <span class="menu-title">Orders</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link" href="#">
                    <span class="menu-icon">
                        <i class="bi bi-person fs-3"></i>
                    </span>
                    <span class="menu-title">Customers</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link" href="#">
                    <span class="menu-icon">
                        <i class="bi bi-person-badge fs-3"></i>
                    </span>
                    <span class="menu-title">My Profile</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link" href="#">
                    <span class="menu-icon">
                        <i class="bi bi-shield-check fs-3"></i>
                    </span>
                    <span class="menu-title">My Permissions</span>
                </a>
            </div>
        </div>
        <!--end::Primary menu-->
    </div>
    <!--end::Nav-->
</div>
<!--end::Aside-->

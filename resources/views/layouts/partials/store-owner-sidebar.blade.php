<!--begin::Aside-->
<div id="kt_aside" class="aside bg-info" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="auto" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_toggle">
    <!--begin::Logo-->
    <div class="aside-logo d-none d-lg-flex flex-column align-items-center flex-column-auto py-8" id="kt_aside_logo">
        <a href="{{ route('store-owner.dashboard') }}">
            <img alt="Logo" src="{{ asset('assets/media/logos/logo-demo-6.svg') }}" class="h-55px" />
        </a>
    </div>
    <!--end::Logo-->
    <!--begin::Nav-->
    <div class="aside-nav d-flex flex-column align-lg-center flex-column-fluid w-100 pt-5 pt-lg-0" id="kt_aside_nav">
        <!--begin::Primary menu-->
        <div id="kt_aside_menu" class="menu menu-column menu-title-gray-600 menu-state-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500 fw-bold fs-6" data-kt-menu="true">
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-owner.dashboard') ? 'active' : '' }}" href="{{ route('store-owner.dashboard') }}">
                    <span class="menu-icon">
                        <i class="bi bi-house fs-3"></i>
                    </span>
                    <span class="menu-title">Dashboard</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-owner.store.*') ? 'active' : '' }}" href="{{ route('store-owner.dashboard') }}">
                    <span class="menu-icon">
                        <i class="bi bi-shop fs-3"></i>
                    </span>
                    <span class="menu-title">My Store</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-owner.product.*') ? 'active' : '' }}" href="{{ route('store-owner.product.index') }}">
                    <span class="menu-icon">
                        <i class="bi bi-box fs-3"></i>
                    </span>
                    <span class="menu-title">Products</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-owner.order.*') ? 'active' : '' }}" href="{{ route('store-owner.order.index') }}">
                    <span class="menu-icon">
                        <i class="bi bi-cart fs-3"></i>
                    </span>
                    <span class="menu-title">Orders</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link {{ request()->routeIs('store-owner.store.staff.*') ? 'active' : '' }}" href="{{ route('store-owner.store.staff.index', auth('store-owner')->user()->store_id) }}">
                    <span class="menu-icon">
                        <i class="bi bi-people fs-3"></i>
                    </span>
                    <span class="menu-title">Staff</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link" href="#">
                    <span class="menu-icon">
                        <i class="bi bi-graph-up fs-3"></i>
                    </span>
                    <span class="menu-title">Reports</span>
                </a>
            </div>
            <div class="menu-item py-3">
                <a class="menu-link" href="#">
                    <span class="menu-icon">
                        <i class="bi bi-gear fs-3"></i>
                    </span>
                    <span class="menu-title">Settings</span>
                </a>
            </div>
        </div>
        <!--end::Primary menu-->
    </div>
    <!--end::Nav-->
</div>
<!--end::Aside-->

<!--begin::Toolbar-->
<div class="toolbar" id="kt_toolbar">
    <!--begin::Container-->
    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
        <!--begin::Page title-->
        <div data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}" class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
            <!--begin::Title-->
            <h1 class="d-flex align-items-center text-dark fw-bolder fs-3 my-1">
                @yield('page_title', 'Dashboard')
                <!--begin::Separator-->
                @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                <span class="h-20px border-gray-200 border-start ms-3 mx-2"></span>
                @endif
                <!--end::Separator-->
            </h1>
            <!--end::Title-->
            <!--begin::Breadcrumb-->
            @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
            <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                @foreach($breadcrumbs as $breadcrumb)
                    @if(!$loop->last)
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ $breadcrumb['url'] }}" class="text-muted text-hover-primary">{{ $breadcrumb['title'] }}</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-200 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                    @else
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-dark">{{ $breadcrumb['title'] }}</li>
                        <!--end::Item-->
                    @endif
                @endforeach
            </ul>
            @endif
            <!--end::Breadcrumb-->
        </div>
        <!--end::Page title-->
        
        <!--begin::Actions-->
        <div class="d-flex align-items-center py-1">
            @yield('toolbar_buttons')
        </div>
        <!--end::Actions-->
    </div>
    <!--end::Container-->
</div>
<!--end::Toolbar-->

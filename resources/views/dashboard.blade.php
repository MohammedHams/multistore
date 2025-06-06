@extends('layouts.app')

@section('title', __('dashboard.dashboard') . ' - ' . config('app.name'))

@section('page_title', __('dashboard.dashboard'))


@section('content')
<!--begin::Sign Out-->
<div class="card mb-5">
    <div class="card-body pt-3 pb-3">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1">
                <h3 class="fw-bolder">{{ __('dashboard.welcome') }}, {{ Auth::user()->name ?? __('dashboard.user') }}!</h3>
                <span class="text-muted fw-bold d-block">{{ now()->format('l, F j, Y') }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right fs-4 me-2"></i>{{ __('auth.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
<!--end::Sign Out-->

<!--begin::Row-->
<div class="row gy-5 g-xl-8">
    <!--begin::Col-->
    <div class="col-xxl-4">
        <!--begin::Mixed Widget 2-->
        <div class="card card-xxl-stretch">
            <!--begin::Header-->
            <div class="card-header border-0 bg-danger py-5">
                <h3 class="card-title fw-bolder text-white">Sales Statistics</h3>
                <div class="card-toolbar">
                    <!--begin::Menu-->
                    <button type="button" class="btn btn-sm btn-icon btn-color-white btn-active-white btn-active-color- border-0 me-n3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <!--begin::Svg Icon | path: icons/duotune/general/gen024.svg-->
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="5" y="5" width="5" height="5" rx="1" fill="#000000" />
                                    <rect x="14" y="5" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                    <rect x="5" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                    <rect x="14" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                </g>
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </button>
                    <!--begin::Menu 3-->
                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-bold w-200px py-3" data-kt-menu="true">
                        <!--begin::Heading-->
                        <div class="menu-item px-3">
                            <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Timeframe</div>
                        </div>
                        <!--end::Heading-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3">Today</a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3">This Week</a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3">This Month</a>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::Menu 3-->
                    <!--end::Menu-->
                </div>
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body p-0">
                <!--begin::Chart-->
                <div class="mixed-widget-2-chart card-rounded-bottom bg-danger" data-kt-color="danger" style="height: 200px"></div>
                <!--end::Chart-->
                <!--begin::Stats-->
                <div class="card-p mt-n20 position-relative">
                    <!--begin::Row-->
                    <div class="row g-0">
                        <!--begin::Col-->
                        <div class="col bg-light-warning px-6 py-8 rounded-2 me-7 mb-7">
                            <!--begin::Svg Icon | path: icons/duotune/general/gen032.svg-->
                            <span class="svg-icon svg-icon-3x svg-icon-warning d-block my-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect x="8" y="9" width="3" height="10" rx="1.5" fill="black" />
                                    <rect opacity="0.5" x="13" y="5" width="3" height="14" rx="1.5" fill="black" />
                                    <rect x="18" y="11" width="3" height="8" rx="1.5" fill="black" />
                                    <rect x="3" y="13" width="3" height="6" rx="1.5" fill="black" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                            <a href="#" class="text-warning fw-bold fs-6">Weekly Sales</a>
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col bg-light-primary px-6 py-8 rounded-2 mb-7">
                            <!--begin::Svg Icon | path: icons/duotune/finance/fin006.svg-->
                            <span class="svg-icon svg-icon-3x svg-icon-primary d-block my-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path opacity="0.3" d="M20 15H4C2.9 15 2 14.1 2 13V7C2 6.4 2.4 6 3 6H21C21.6 6 22 6.4 22 7V13C22 14.1 21.1 15 20 15ZM13 12H11C10.5 12 10 12.4 10 13V16C10 16.5 10.4 17 11 17H13C13.6 17 14 16.6 14 16V13C14 12.4 13.6 12 13 12Z" fill="black" />
                                    <path d="M14 6V5H10V6H8V5C8 3.9 8.9 3 10 3H14C15.1 3 16 3.9 16 5V6H14ZM20 15H14V16C14 16.6 13.5 17 13 17H11C10.5 17 10 16.6 10 16V15H4C3.6 15 3.3 14.9 3 14.7V18C3 19.1 3.9 20 5 20H19C20.1 20 21 19.1 21 18V14.7C20.7 14.9 20.4 15 20 15Z" fill="black" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                            <a href="#" class="text-primary fw-bold fs-6">New Orders</a>
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                    <!--begin::Row-->
                    <div class="row g-0">
                        <!--begin::Col-->
                        <div class="col bg-light-danger px-6 py-8 rounded-2 me-7">
                            <!--begin::Svg Icon | path: icons/duotune/abstract/abs027.svg-->
                            <span class="svg-icon svg-icon-3x svg-icon-danger d-block my-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path opacity="0.3" d="M21.25 18.525L13.05 21.825C12.35 22.125 11.65 22.125 10.95 21.825L2.75 18.525C1.75 18.125 1.75 16.725 2.75 16.325L4.04999 15.825L10.25 18.325C10.85 18.525 11.45 18.625 12.05 18.625C12.65 18.625 13.25 18.525 13.85 18.325L20.05 15.825L21.35 16.325C22.35 16.725 22.35 18.125 21.25 18.525ZM13.05 16.425L21.25 13.125C22.25 12.725 22.25 11.325 21.25 10.925L13.05 7.62502C12.35 7.32502 11.65 7.32502 10.95 7.62502L2.75 10.925C1.75 11.325 1.75 12.725 2.75 13.125L10.95 16.425C11.65 16.725 12.45 16.725 13.05 16.425Z" fill="black" />
                                    <path d="M11.05 11.025L2.84998 7.725C1.84998 7.325 1.84998 5.925 2.84998 5.525L11.05 2.225C11.75 1.925 12.45 1.925 13.15 2.225L21.35 5.525C22.35 5.925 22.35 7.325 21.35 7.725L13.05 11.025C12.45 11.325 11.65 11.325 11.05 11.025Z" fill="black" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                            <a href="#" class="text-danger fw-bold fs-6 mt-2">Item Orders</a>
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col bg-light-success px-6 py-8 rounded-2">
                            <!--begin::Svg Icon | path: icons/duotune/communication/com010.svg-->
                            <span class="svg-icon svg-icon-3x svg-icon-success d-block my-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M6 8.725C6 8.125 6.4 7.725 7 7.725H14L18 11.725V12.925L22 9.725L12.6 2.225C12.2 1.925 11.7 1.925 11.4 2.225L2 9.725L6 12.925V8.725Z" fill="black" />
                                    <path opacity="0.3" d="M22 9.72498V20.725C22 21.325 21.6 21.725 21 21.725H3C2.4 21.725 2 21.325 2 20.725V9.72498L11.4 17.225C11.8 17.525 12.3 17.525 12.6 17.225L22 9.72498ZM15 11.725H18L14 7.72498V10.725C14 11.325 14.4 11.725 15 11.725Z" fill="black" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                            <a href="#" class="text-success fw-bold fs-6 mt-2">Bug Reports</a>
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Stats-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Mixed Widget 2-->
    </div>
    <!--end::Col-->
    <!--begin::Col-->
    <div class="col-xxl-4">
        <!--begin::List Widget 5-->
        <div class="card card-xxl-stretch">
            <!--begin::Header-->
            <div class="card-header align-items-center border-0 mt-4">
                <h3 class="card-title align-items-start flex-column">
                    <span class="fw-bolder mb-2 text-dark">Recent Activities</span>
                    <span class="text-muted fw-bold fs-7">Last 30 days</span>
                </h3>
                <div class="card-toolbar">
                    <!--begin::Menu-->
                    <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <!--begin::Svg Icon | path: icons/duotune/general/gen024.svg-->
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="5" y="5" width="5" height="5" rx="1" fill="#000000" />
                                    <rect x="14" y="5" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                    <rect x="5" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                    <rect x="14" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                </g>
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </button>
                    <!--end::Menu-->
                </div>
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body pt-5">
                <!--begin::Timeline-->
                <div class="timeline-label">
                    <!--begin::Item-->
                    <div class="timeline-item">
                        <!--begin::Label-->
                        <div class="timeline-label fw-bolder text-gray-800 fs-6">08:42</div>
                        <!--end::Label-->
                        <!--begin::Badge-->
                        <div class="timeline-badge">
                            <i class="fa fa-genderless text-warning fs-1"></i>
                        </div>
                        <!--end::Badge-->
                        <!--begin::Text-->
                        <div class="fw-mormal timeline-content text-muted ps-3">New order received #XF-2356</div>
                        <!--end::Text-->
                    </div>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <div class="timeline-item">
                        <!--begin::Label-->
                        <div class="timeline-label fw-bolder text-gray-800 fs-6">10:00</div>
                        <!--end::Label-->
                        <!--begin::Badge-->
                        <div class="timeline-badge">
                            <i class="fa fa-genderless text-success fs-1"></i>
                        </div>
                        <!--end::Badge-->
                        <!--begin::Content-->
                        <div class="timeline-content d-flex">
                            <span class="fw-bolder text-gray-800 ps-3">AEOL meeting</span>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <div class="timeline-item">
                        <!--begin::Label-->
                        <div class="timeline-label fw-bolder text-gray-800 fs-6">14:37</div>
                        <!--end::Label-->
                        <!--begin::Badge-->
                        <div class="timeline-badge">
                            <i class="fa fa-genderless text-danger fs-1"></i>
                        </div>
                        <!--end::Badge-->
                        <!--begin::Desc-->
                        <div class="timeline-content fw-bolder text-gray-800 ps-3">New order placed #XF-2356</div>
                        <!--end::Desc-->
                    </div>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <div class="timeline-item">
                        <!--begin::Label-->
                        <div class="timeline-label fw-bolder text-gray-800 fs-6">16:50</div>
                        <!--end::Label-->
                        <!--begin::Badge-->
                        <div class="timeline-badge">
                            <i class="fa fa-genderless text-primary fs-1"></i>
                        </div>
                        <!--end::Badge-->
                        <!--begin::Text-->
                        <div class="timeline-content fw-mormal text-muted ps-3">Inventory updated #XF-2356</div>
                        <!--end::Text-->
                    </div>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <div class="timeline-item">
                        <!--begin::Label-->
                        <div class="timeline-label fw-bolder text-gray-800 fs-6">21:03</div>
                        <!--end::Label-->
                        <!--begin::Badge-->
                        <div class="timeline-badge">
                            <i class="fa fa-genderless text-danger fs-1"></i>
                        </div>
                        <!--end::Badge-->
                        <!--begin::Desc-->
                        <div class="timeline-content fw-bold text-gray-800 ps-3">New order placed #XF-2357</div>
                        <!--end::Desc-->
                    </div>
                    <!--end::Item-->
                </div>
                <!--end::Timeline-->
            </div>
            <!--end: Card Body-->
        </div>
        <!--end: List Widget 5-->
    </div>
    <!--end::Col-->
    <!--begin::Col-->
    <div class="col-xxl-4">
        <!--begin::List Widget 3-->
        <div class="card card-xxl-stretch mb-5 mb-xl-8">
            <!--begin::Header-->
            <div class="card-header border-0">
                <h3 class="card-title fw-bolder text-dark">Latest Products</h3>
                <div class="card-toolbar">
                    <!--begin::Menu-->
                    <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <!--begin::Svg Icon | path: icons/duotune/general/gen024.svg-->
                        <span class="svg-icon svg-icon-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="5" y="5" width="5" height="5" rx="1" fill="#000000" />
                                    <rect x="14" y="5" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                    <rect x="5" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                    <rect x="14" y="14" width="5" height="5" rx="1" fill="#000000" opacity="0.3" />
                                </g>
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                    </button>
                    <!--end::Menu-->
                </div>
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body pt-2">
                <!--begin::Item-->
                <div class="d-flex align-items-center mb-8">
                    <!--begin::Bullet-->
                    <span class="bullet bullet-vertical h-40px bg-success"></span>
                    <!--end::Bullet-->
                    <!--begin::Checkbox-->
                    <div class="form-check form-check-custom form-check-solid mx-5">
                        <input class="form-check-input" type="checkbox" value="" />
                    </div>
                    <!--end::Checkbox-->
                    <!--begin::Description-->
                    <div class="flex-grow-1">
                        <a href="#" class="text-gray-800 text-hover-primary fw-bolder fs-6">Product One</a>
                        <span class="text-muted fw-bold d-block">$99.00</span>
                    </div>
                    <!--end::Description-->
                    <span class="badge badge-light-success fs-8 fw-bolder">In Stock</span>
                </div>
                <!--end:Item-->
                <!--begin::Item-->
                <div class="d-flex align-items-center mb-8">
                    <!--begin::Bullet-->
                    <span class="bullet bullet-vertical h-40px bg-primary"></span>
                    <!--end::Bullet-->
                    <!--begin::Checkbox-->
                    <div class="form-check form-check-custom form-check-solid mx-5">
                        <input class="form-check-input" type="checkbox" value="" />
                    </div>
                    <!--end::Checkbox-->
                    <!--begin::Description-->
                    <div class="flex-grow-1">
                        <a href="#" class="text-gray-800 text-hover-primary fw-bolder fs-6">Product Two</a>
                        <span class="text-muted fw-bold d-block">$149.00</span>
                    </div>
                    <!--end::Description-->
                    <span class="badge badge-light-primary fs-8 fw-bolder">In Stock</span>
                </div>
                <!--end:Item-->
                <!--begin::Item-->
                <div class="d-flex align-items-center mb-8">
                    <!--begin::Bullet-->
                    <span class="bullet bullet-vertical h-40px bg-warning"></span>
                    <!--end::Bullet-->
                    <!--begin::Checkbox-->
                    <div class="form-check form-check-custom form-check-solid mx-5">
                        <input class="form-check-input" type="checkbox" value="" />
                    </div>
                    <!--end::Checkbox-->
                    <!--begin::Description-->
                    <div class="flex-grow-1">
                        <a href="#" class="text-gray-800 text-hover-primary fw-bolder fs-6">Product Three</a>
                        <span class="text-muted fw-bold d-block">$249.00</span>
                    </div>
                    <!--end::Description-->
                    <span class="badge badge-light-warning fs-8 fw-bolder">Low Stock</span>
                </div>
                <!--end:Item-->
                <!--begin::Item-->
                <div class="d-flex align-items-center mb-8">
                    <!--begin::Bullet-->
                    <span class="bullet bullet-vertical h-40px bg-danger"></span>
                    <!--end::Bullet-->
                    <!--begin::Checkbox-->
                    <div class="form-check form-check-custom form-check-solid mx-5">
                        <input class="form-check-input" type="checkbox" value="" />
                    </div>
                    <!--end::Checkbox-->
                    <!--begin::Description-->
                    <div class="flex-grow-1">
                        <a href="#" class="text-gray-800 text-hover-primary fw-bolder fs-6">Product Four</a>
                        <span class="text-muted fw-bold d-block">$79.00</span>
                    </div>
                    <!--end::Description-->
                    <span class="badge badge-light-danger fs-8 fw-bolder">Out of Stock</span>
                </div>
                <!--end:Item-->
            </div>
            <!--end::Body-->
        </div>
        <!--end:List Widget 3-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->
@endsection

@push('scripts')
<script>
    // Initialize charts and widgets
    var initMixedWidget2 = function() {
        var charts = document.querySelectorAll('.mixed-widget-2-chart');

        var options;
        var chart;
        
        // Initialize chart
        if (charts) {
            [].slice.call(charts).map(function(element) {
                var height = parseInt(KTUtil.css(element, 'height'));

                if (!element) {
                    return;
                }

                options = {
                    series: [{
                        name: 'Sales',
                        data: [30, 45, 32, 70, 40, 40, 40]
                    }],
                    chart: {
                        fontFamily: 'inherit',
                        type: 'bar',
                        height: height,
                        toolbar: {
                            show: false
                        },
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: ['30%'],
                            borderRadius: 4
                        }
                    },
                    legend: {
                        show: false
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false
                        },
                        labels: {
                            style: {
                                colors: KTUtil.getCssVariableValue('--bs-gray-400'),
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        min: 0,
                        max: 100,
                        labels: {
                            style: {
                                colors: KTUtil.getCssVariableValue('--bs-gray-400'),
                                fontSize: '12px'
                            }
                        }
                    },
                    fill: {
                        opacity: 1
                    },
                    states: {
                        normal: {
                            filter: {
                                type: 'none',
                                value: 0
                            }
                        },
                        hover: {
                            filter: {
                                type: 'none',
                                value: 0
                            }
                        },
                        active: {
                            allowMultipleDataPointsSelection: false,
                            filter: {
                                type: 'none',
                                value: 0
                            }
                        }
                    },
                    tooltip: {
                        style: {
                            fontSize: '12px'
                        },
                        y: {
                            formatter: function (val) {
                                return "$" + val + "k"
                            }
                        }
                    },
                    colors: [KTUtil.getCssVariableValue('--bs-light-danger')],
                    grid: {
                        borderColor: KTUtil.getCssVariableValue('--bs-gray-200'),
                        strokeDashArray: 4,
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            left: 20,
                            right: 20
                        }
                    }
                };

                chart = new ApexCharts(element, options);
                chart.render();
            });
        }
    }

    // On document ready
    KTUtil.onDOMContentLoaded(function() {
        initMixedWidget2();
    });
</script>
@endpush

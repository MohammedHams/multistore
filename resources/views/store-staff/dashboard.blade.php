@extends('layouts.store-staff')

@section('title', 'Store Staff Dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            @php
                // Get the staff member and their permissions
                $staffMember = Auth::guard('store-staff')->user();
                $permissions = $staffMember->permissions ?? [];
                $store = \App\Models\Store::find($staffMember->store_id);
                
                // Check permissions
                $canViewStore = in_array('view_store', $permissions);
                $canEditStore = in_array('edit_store', $permissions);
                $canViewProducts = in_array('view_products', $permissions);
                $canManageProducts = in_array('manage_products', $permissions);
                $canDeleteProducts = in_array('delete_products', $permissions);
                $canViewOrders = in_array('view_orders', $permissions);
                $canManageOrders = in_array('manage_orders', $permissions);
                $canDeleteOrders = in_array('delete_orders', $permissions);
                $canManageStaff = in_array('manage_staff', $permissions);
            @endphp

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">{{ __('Store Staff Dashboard') }}</h5>
                </div>

                <div class="card-body">
                    <h2>Welcome, {{ $staffMember->name }}</h2>
                    <p>You are logged in as a staff member of {{ $store->name }}.</p>
                    
                    @if(count($permissions) > 0)
                        <div class="mt-3">
                            <h6>Your Permissions:</h6>
                            <div>
                                @foreach($permissions as $permission)
                                    <span class="badge bg-info me-2 mb-2">{{ str_replace('_', ' ', ucfirst($permission)) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Quick Access Modules -->
            <div class="row mb-4">
                @if($canViewStore || $canEditStore)
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">Store</h5>
                                    <div class="small">Store Information</div>
                                </div>
                                <div>
                                    <i class="fas fa-store fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('store-staff.store.show', $store->id) }}">View Store</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($canViewProducts || $canManageProducts)
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">Products</h5>
                                    <div class="small">{{ $canManageProducts ? 'Manage Products' : 'View Products' }}</div>
                                </div>
                                <div>
                                    <i class="fas fa-box fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('store-staff.product.index') }}">{{ $canManageProducts ? 'Manage Products' : 'View Products' }}</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($canViewOrders || $canManageOrders)
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">Orders</h5>
                                    <div class="small">{{ $canManageOrders ? 'Process Orders' : 'View Orders' }}</div>
                                </div>
                                <div>
                                    <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('store-staff.order.index') }}">{{ $canManageOrders ? 'Process Orders' : 'View Orders' }}</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                @endif
                
                @if($canManageStaff)
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">Staff</h5>
                                    <div class="small">Manage Staff</div>
                                </div>
                                <div>
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('store-staff.store.staff.index', $store->id) }}">Manage Staff</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($canManageProducts)
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('store-staff.product.create') }}" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-2"></i> Add New Product
                                    </a>
                                </div>
                                @endif
                                
                                @if($canManageOrders)
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('store-staff.order.create') }}" class="btn btn-success w-100">
                                        <i class="fas fa-plus me-2"></i> Create New Order
                                    </a>
                                </div>
                                @endif
                                
                                @if($canViewOrders)
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('store-staff.order.index') }}" class="btn btn-warning w-100">
                                        <i class="fas fa-list me-2"></i> View Orders
                                    </a>
                                </div>
                                @endif
                                
                                @if($canViewProducts)
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('store-staff.product.index') }}" class="btn btn-info w-100">
                                        <i class="fas fa-list me-2"></i> View Products
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            @if($canViewOrders)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Recent Orders</h5>
                            <a href="{{ route('store-staff.order.index') }}" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $recentOrders = \App\Models\Order::where('store_id', $store->id)
                                                ->orderBy('created_at', 'desc')
                                                ->limit(5)
                                                ->get();
                                        @endphp
                                        
                                        @forelse($recentOrders as $order)
                                            <tr>
                                                <td>{{ $order->id }}</td>
                                                <td>{{ $order->customer_name }}</td>
                                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                                <td>
                                                    <a href="{{ route('store-staff.order.show', $order->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($canManageOrders)
                                                    <a href="{{ route('store-staff.order.edit', $order->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No recent orders found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

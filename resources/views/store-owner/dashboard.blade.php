@extends('layouts.store-owner')

@section('title', 'Store Owner Dashboard')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0">{{ __('Store Owner Dashboard') }}</h5>
                </div>

                <div class="card-body">
                    <h2>Welcome, {{ Auth::guard('store-owner')->user()->name }}</h2>
                    <p>Manage your store, products, orders, and staff from this dashboard.</p>
                </div>
            </div>

            @php
                // Get the store owner's store
                $storeOwner = Auth::guard('store-owner')->user();
                $store = \App\Models\Store::where('id', $storeOwner->store_id)->first();
                
                // Count products
                $productCount = \App\Models\Product::where('store_id', $store->id)->count();
                
                // Count orders
                $orderCount = \App\Models\Order::where('store_id', $store->id)->count();
                
                // Count staff
                $staffCount = \App\Models\StoreStaff::where('store_id', $store->id)->count();
                
                // Calculate revenue
                $revenue = \App\Models\Order::where('store_id', $store->id)
                    ->where('status', 'completed')
                    ->sum('total_amount');
            @endphp
            
            <!-- Store Overview -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="m-0">Store Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>{{ $store->name }}</h4>
                                    <p>{{ $store->email }}</p>
                                    <p>{{ $store->phone }}</p>
                                    <p>Status: <span class="badge bg-{{ $store->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($store->status) }}</span></p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <a href="{{ route('store-owner.store.edit', $store->id) }}" class="btn btn-primary">Edit Store</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $productCount }}</h5>
                                    <div class="small">Products</div>
                                </div>
                                <div>
                                    <i class="fas fa-box fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('store-owner.product.index') }}">View Products</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $orderCount }}</h5>
                                    <div class="small">Orders</div>
                                </div>
                                <div>
                                    <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('store-owner.order.index') }}">View Orders</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">{{ $staffCount }}</h5>
                                    <div class="small">Staff</div>
                                </div>
                                <div>
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('store-owner.store.staff.index', $store->id) }}">Manage Staff</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-0">${{ number_format($revenue, 2) }}</h5>
                                    <div class="small">Revenue</div>
                                </div>
                                <div>
                                    <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="{{ route('store-owner.order.index') }}?status=completed">View Completed Orders</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
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
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('store-owner.product.create') }}" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-2"></i> Add New Product
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('store-owner.order.create') }}" class="btn btn-success w-100">
                                        <i class="fas fa-plus me-2"></i> Create New Order
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('store-owner.store.staff.create', $store->id) }}" class="btn btn-info w-100">
                                        <i class="fas fa-user-plus me-2"></i> Add Staff Member
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('store-owner.store.edit', $store->id) }}" class="btn btn-warning w-100">
                                        <i class="fas fa-cog me-2"></i> Store Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="m-0">Recent Orders</h5>
                            <a href="{{ route('store-owner.order.index') }}" class="btn btn-sm btn-primary">View All</a>
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
                                                    <a href="{{ route('store-owner.order.show', $order->id) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
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
        </div>
    </div>
</div>
@endsection

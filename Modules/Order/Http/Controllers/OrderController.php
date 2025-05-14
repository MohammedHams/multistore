<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Order\Repositories\Interfaces\OrderRepositoryInterface;
use Modules\Order\Repositories\Interfaces\OrderItemRepositoryInterface;
use Modules\Product\Repositories\Interfaces\ProductRepositoryInterface;
use Modules\Store\Repositories\Interfaces\StoreRepositoryInterface;

class OrderController extends Controller
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * OrderController constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Display a listing of the orders.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }
        
        // Get orders based on user role
        $orders = [];
        
        if ($guard === 'admin') {
            // Admin can see all orders
            $orders = $this->orderRepository->getAllOrders();
        } elseif ($guard === 'store-owner') {
            // Store owner can only see orders for their store
            $storeOwner = auth('store-owner')->user();
            $storeId = $storeOwner->store_id;
            $orders = $this->orderRepository->getOrdersByStoreId($storeId);
        } elseif ($guard === 'store-staff') {
            // Store staff can only see orders for their store
            $storeStaff = auth('store-staff')->user();
            $storeId = $storeStaff->store_id;
            $orders = $this->orderRepository->getOrdersByStoreId($storeId);
        }
        
        // If it's an AJAX request, return JSON response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }
        
        // Determine which view to use based on the guard
        if ($guard === 'admin') {
            return view('order::index', compact('orders'));
        } elseif ($guard === 'store-owner') {
            return view('order::store-owner.index', compact('orders'));
        } elseif ($guard === 'store-staff') {
            return view('order::store-staff.index', compact('orders'));
        } else {
            return view('order::index', compact('orders'));
        }
    }

    /**
     * Show the form for creating a new order.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }
        
        // Get products based on user role
        $products = [];
        $stores = [];
        
        if ($guard === 'admin') {
            // Admin can see all products and stores
            $products = $this->productRepository->all();
            $stores = $this->storeRepository->all();
        } elseif ($guard === 'store-owner') {
            // Store owner can only see products for their store
            $storeOwner = auth('store-owner')->user();
            $storeId = $storeOwner->store_id;
            $products = $this->productRepository->getProductsByStoreId($storeId);
            $stores = $this->storeRepository->findById($storeId) ? [$this->storeRepository->findById($storeId)] : [];
        } elseif ($guard === 'store-staff') {
            // Store staff can only see products for their store
            $storeStaff = auth('store-staff')->user();
            $storeId = $storeStaff->store_id;
            $products = $this->productRepository->getProductsByStoreId($storeId);
            $stores = $this->storeRepository->findById($storeId) ? [$this->storeRepository->findById($storeId)] : [];
        }
        
        // Determine which view to use based on the guard
        if ($guard === 'admin') {
            return view('order::create', compact('products', 'stores'));
        } elseif ($guard === 'store-owner') {
            return view('order::store-owner.create', compact('products', 'stores'));
        } elseif ($guard === 'store-staff') {
            return view('order::store-staff.create', compact('products', 'stores'));
        } else {
            return view('order::create', compact('products', 'stores'));
        }
    }

    /**
     * Store a newly created order in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'store_id' => 'required|exists:stores,id',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Get user data
            $user = \App\Models\User::find($request->user_id);
            if (!$user) {
                return redirect()->back()->with('error', 'المستخدم غير موجود')->withInput();
            }
            
            // Prepare order data
            $orderData = [
                'user_id' => $user->id,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'store_id' => $request->store_id,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => 'pending',
                'payment_status' => 'pending',
                'order_number' => 'ORD-' . time() . rand(1000, 9999),
            ];
            
            // Calculate total amount
            $totalAmount = 0;
            $processedItems = [];
            
            foreach ($request->items as $item) {
                $product = $this->productRepository->findById($item['product_id']);
                if (!$product) {
                    continue;
                }
                
                $price = $product->price;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;
                $totalAmount += $subtotal;
                
                $processedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }
            
            $orderData['total_amount'] = $totalAmount;
            
            // Create the order
            try {
                $order = $this->orderRepository->createOrder($orderData);
                
                // Create order items
                foreach ($processedItems as $item) {
                    $this->orderItemRepository->createOrderItem([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }
            } catch (\Exception $e) {
                // Log the actual error for debugging
                Log::error('Error creating order: ' . $e->getMessage());
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'فشل إنشاء الطلب. يرجى التحقق من البيانات والمحاولة مرة أخرى.'
                    ], 500);
                }
                
                return redirect()->back()->with('error', 'فشل إنشاء الطلب. يرجى التحقق من البيانات والمحاولة مرة أخرى.')->withInput();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => $order
                ]);
            }

            return redirect()->route('admin.order.index')->with('success', 'Order created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to create order')->withInput();
        }
    }

    /**
     * Display the specified order.
     *
     * Show the form for editing the specified order.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    /**
     * Display the specified order.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }
        
        // Find the order
        $order = $this->orderRepository->findById($id);
        
        if (!$order) {
            // Determine which route to redirect to based on the guard
            $routePrefix = '';
            if ($guard === 'admin') {
                $routePrefix = 'admin.';
            } elseif ($guard === 'store-owner') {
                $routePrefix = 'store-owner.';
            } elseif ($guard === 'store-staff') {
                $routePrefix = 'store-staff.';
            }
            
            return redirect()->route($routePrefix . 'order.index')->with('error', 'Order not found');
        }
        
        // Check if the user has permission to view this order
        if ($guard !== 'admin') {
            $userId = null;
            $userStoreId = null;
            
            if ($guard === 'store-owner') {
                $user = auth('store-owner')->user();
                $userStoreId = $user->store_id;
            } elseif ($guard === 'store-staff') {
                $user = auth('store-staff')->user();
                $userStoreId = $user->store_id;
            }
            
            // If the order's store_id doesn't match the user's store_id, redirect
            if ($userStoreId && $order->store_id != $userStoreId) {
                $routePrefix = ($guard === 'store-owner') ? 'store-owner.' : 'store-staff.';
                return redirect()->route($routePrefix . 'order.index')
                    ->with('error', 'You do not have permission to view this order');
            }
        }
        
        // Get order items
        $orderItems = $this->orderItemRepository->getOrderItems($id);
        
        if ($guard === 'admin') {
            return view('order::show', compact('order', 'orderItems'));
        } elseif ($guard === 'store-owner') {
            return view('order::store-owner.show', compact('order', 'orderItems'));
        } elseif ($guard === 'store-staff') {
            return view('order::store-staff.show', compact('order', 'orderItems'));
        } else {
            return view('order::show', compact('order', 'orderItems'));
        }
    }
    
    public function edit($id)
    {
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }
        
        // Find the order
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            // Determine which route to redirect to based on the guard
            $routePrefix = '';
            if ($guard === 'admin') {
                $routePrefix = 'admin.';
            } elseif ($guard === 'store-owner') {
                $routePrefix = 'store-owner.';
            } elseif ($guard === 'store-staff') {
                $routePrefix = 'store-staff.';
            }
            
            return redirect()->route($routePrefix . 'order.index')->with('error', 'Order not found');
        }
        
        // Check if the user has permission to edit this order
        if ($guard !== 'admin') {
            $userId = null;
            $userStoreId = null;
            
            if ($guard === 'store-owner') {
                $user = auth('store-owner')->user();
                $userStoreId = $user->store_id;
            } elseif ($guard === 'store-staff') {
                $user = auth('store-staff')->user();
                $userStoreId = $user->store_id;
            }
            
            // If the order's store_id doesn't match the user's store_id, redirect
            if ($userStoreId && $order->store_id != $userStoreId) {
                $routePrefix = ($guard === 'store-owner') ? 'store-owner.' : 'store-staff.';
                return redirect()->route($routePrefix . 'order.index')
                    ->with('error', 'You do not have permission to edit this order');
            }
        }

        // Get order items
        $orderItems = $this->orderItemRepository->getOrderItems($id);
        
        // Get products for order editing based on user role
        $products = [];
        $stores = [];
        
        if ($guard === 'admin') {
            // Admin can see all products and stores
            $products = $this->productRepository->all();
            $stores = $this->storeRepository->all();
        } elseif ($guard === 'store-owner' || $guard === 'store-staff') {
            // Store owner/staff can only see products for their store
            $storeId = ($guard === 'store-owner') ? auth('store-owner')->user()->store_id : auth('store-staff')->user()->store_id;
            $products = $this->productRepository->getProductsByStoreId($storeId);
            $stores = $this->storeRepository->findById($storeId) ? [$this->storeRepository->findById($storeId)] : [];
        }
        
        // Determine which view to use based on the guard
        if ($guard === 'admin') {
            return view('order::edit', compact('order', 'orderItems', 'products', 'stores'));
        } elseif ($guard === 'store-owner') {
            return view('order::store-owner.edit', compact('order', 'orderItems', 'products', 'stores'));
        } elseif ($guard === 'store-staff') {
            return view('order::store-staff.edit', compact('order', 'orderItems', 'products', 'stores'));
        } else {
            return view('order::edit', compact('order', 'orderItems', 'products', 'stores'));
        }
    }

    /**
     * Update the specified order in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }
        
        // Validate the request based on the fields in the edit form
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Find the order
            $order = $this->orderRepository->findById($id);

            if (!$order) {
                // Determine which route to redirect to based on the guard
                $routePrefix = '';
                if ($guard === 'admin') {
                    $routePrefix = 'admin.';
                } elseif ($guard === 'store-owner') {
                    $routePrefix = 'store-owner.';
                } elseif ($guard === 'store-staff') {
                    $routePrefix = 'store-staff.';
                }
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }
                
                return redirect()->route($routePrefix . 'order.index')->with('error', 'Order not found');
            }
            
            // Check if the user has permission to update this order
            if ($guard !== 'admin') {
                $userId = null;
                $userStoreId = null;
                
                if ($guard === 'store-owner') {
                    $user = auth('store-owner')->user();
                    $userStoreId = $user->store_id;
                } elseif ($guard === 'store-staff') {
                    $user = auth('store-staff')->user();
                    $userStoreId = $user->store_id;
                }
                
                // If the order's store_id doesn't match the user's store_id, redirect
                if ($userStoreId && $order->store_id != $userStoreId) {
                    $routePrefix = ($guard === 'store-owner') ? 'store-owner.' : 'store-staff.';
                    
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to update this order'
                        ], 403);
                    }
                    
                    return redirect()->route($routePrefix . 'order.index')
                        ->with('error', 'You do not have permission to update this order');
                }
            }

            // Update the order with only the fields from the edit form
            $order = $this->orderRepository->updateOrder($id, $request->only([
                'shipping_address',
                'billing_address',
                'payment_method',
                'notes'
            ]));

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order updated successfully',
                    'data' => $order
                ]);
            }
            
            // Determine which route to redirect to based on the guard
            $routePrefix = '';
            if ($guard === 'admin') {
                $routePrefix = 'admin.';
            } elseif ($guard === 'store-owner') {
                $routePrefix = 'store-owner.';
            } elseif ($guard === 'store-staff') {
                $routePrefix = 'store-staff.';
            }

            return redirect()->route($routePrefix . 'order.index')->with('success', 'Order updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update order')->withInput();
        }
    }

    /**
     * Remove the specified order from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }
        
        try {
            // Find the order first to check permissions
            $order = $this->orderRepository->findById($id);
            
            if (!$order) {
                // Determine which route to redirect to based on the guard
                $routePrefix = '';
                if ($guard === 'admin') {
                    $routePrefix = 'admin.';
                } elseif ($guard === 'store-owner') {
                    $routePrefix = 'store-owner.';
                } elseif ($guard === 'store-staff') {
                    $routePrefix = 'store-staff.';
                }
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }
                
                return redirect()->route($routePrefix . 'order.index')->with('error', 'Order not found');
            }
            
            // Check if the user has permission to delete this order
            if ($guard !== 'admin') {
                $userId = null;
                $userStoreId = null;
                
                if ($guard === 'store-owner') {
                    $user = auth('store-owner')->user();
                    $userStoreId = $user->store_id;
                } elseif ($guard === 'store-staff') {
                    $user = auth('store-staff')->user();
                    $userStoreId = $user->store_id;
                }
                
                // If the order's store_id doesn't match the user's store_id, redirect
                if ($userStoreId && $order->store_id != $userStoreId) {
                    $routePrefix = ($guard === 'store-owner') ? 'store-owner.' : 'store-staff.';
                    
                    if (request()->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to delete this order'
                        ], 403);
                    }
                    
                    return redirect()->route($routePrefix . 'order.index')
                        ->with('error', 'You do not have permission to delete this order');
                }
            }
            
            // Delete the order
            $result = $this->orderRepository->deleteOrder($id);

            if (!$result) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to delete order'
                    ], 500);
                }
                
                return redirect()->back()->with('error', 'Failed to delete order');
            }

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order deleted successfully'
                ]);
            }
            
            // Determine which route to redirect to based on the guard
            $routePrefix = '';
            if ($guard === 'admin') {
                $routePrefix = 'admin.';
            } elseif ($guard === 'store-owner') {
                $routePrefix = 'store-owner.';
            } elseif ($guard === 'store-staff') {
                $routePrefix = 'store-staff.';
            }

            return redirect()->route($routePrefix . 'order.index')->with('success', 'Order deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete order'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete order');
        }
    }

    /**
     * Update the order status.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator);
        }

        try {
            // Find the order first to check permissions
            $order = $this->orderRepository->findById($id);
            
            if (!$order) {
                // Determine which route to redirect to based on the guard
                $routePrefix = '';
                if ($guard === 'admin') {
                    $routePrefix = 'admin.';
                } elseif ($guard === 'store-owner') {
                    $routePrefix = 'store-owner.';
                } elseif ($guard === 'store-staff') {
                    $routePrefix = 'store-staff.';
                }
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }
                
                return redirect()->route($routePrefix . 'order.index')->with('error', 'Order not found');
            }
            
            // Check if the user has permission to update this order
            if ($guard !== 'admin') {
                $userId = null;
                $userStoreId = null;
                
                if ($guard === 'store-owner') {
                    $user = auth('store-owner')->user();
                    $userStoreId = $user->store_id;
                } elseif ($guard === 'store-staff') {
                    $user = auth('store-staff')->user();
                    $userStoreId = $user->store_id;
                }
                
                // If the order's store_id doesn't match the user's store_id, redirect
                if ($userStoreId && $order->store_id != $userStoreId) {
                    $routePrefix = ($guard === 'store-owner') ? 'store-owner.' : 'store-staff.';
                    
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to update this order'
                        ], 403);
                    }
                    
                    return redirect()->route($routePrefix . 'order.index')
                        ->with('error', 'You do not have permission to update this order');
                }
            }
            
            // Update the order status
            $order = $this->orderRepository->updateOrderStatus($id, $request->status);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully',
                    'data' => $order
                ]);
            }

            // Determine which route to redirect to based on the guard
            $routePrefix = '';
            if ($guard === 'admin') {
                $routePrefix = 'admin.';
            } elseif ($guard === 'store-owner') {
                $routePrefix = 'store-owner.';
            } elseif ($guard === 'store-staff') {
                $routePrefix = 'store-staff.';
            }

            return redirect()->route($routePrefix . 'order.show', $id)->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating order status: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order status'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update order status');
        }
    }

    /**
     * Update the order payment status.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|string|in:pending,paid,failed,refunded',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($validator);
        }

        try {
            // Find the order first to check permissions
            $order = $this->orderRepository->findById($id);
            
            if (!$order) {
                // Determine which route to redirect to based on the guard
                $routePrefix = '';
                if ($guard === 'admin') {
                    $routePrefix = 'admin.';
                } elseif ($guard === 'store-owner') {
                    $routePrefix = 'store-owner.';
                } elseif ($guard === 'store-staff') {
                    $routePrefix = 'store-staff.';
                }
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }
                
                return redirect()->route($routePrefix . 'order.index')->with('error', 'Order not found');
            }
            
            // Check if the user has permission to update this order
            if ($guard !== 'admin') {
                $userId = null;
                $userStoreId = null;
                
                if ($guard === 'store-owner') {
                    $user = auth('store-owner')->user();
                    $userStoreId = $user->store_id;
                } elseif ($guard === 'store-staff') {
                    $user = auth('store-staff')->user();
                    $userStoreId = $user->store_id;
                }
                
                // If the order's store_id doesn't match the user's store_id, redirect
                if ($userStoreId && $order->store_id != $userStoreId) {
                    $routePrefix = ($guard === 'store-owner') ? 'store-owner.' : 'store-staff.';
                    
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to update this order'
                        ], 403);
                    }
                    
                    return redirect()->route($routePrefix . 'order.index')
                        ->with('error', 'You do not have permission to update this order');
                }
            }
            
            // Update the order payment status
            $order = $this->orderRepository->updateOrderPaymentStatus($id, $request->payment_status);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order payment status updated successfully',
                    'data' => $order
                ]);
            }

            // Determine which route to redirect to based on the guard
            $routePrefix = '';
            if ($guard === 'admin') {
                $routePrefix = 'admin.';
            } elseif ($guard === 'store-owner') {
                $routePrefix = 'store-owner.';
            } elseif ($guard === 'store-staff') {
                $routePrefix = 'store-staff.';
            }

            return redirect()->route($routePrefix . 'order.show', $id)->with('success', 'Order payment status updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating order payment status: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order payment status'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update order payment status');
        }
    }
}

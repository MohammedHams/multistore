<?php

namespace Modules\Order\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Order\app\Repositories\Interfaces\OrderRepositoryInterface;
use Modules\Order\app\Repositories\Interfaces\OrderItemRepositoryInterface;
use Modules\Product\app\Repositories\Interfaces\ProductRepositoryInterface;
use Modules\Store\app\Repositories\Interfaces\StoreRepositoryInterface;

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
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only([
                'status', 'payment_status', 'store_id', 'user_id', 
                'start_date', 'end_date', 'sort_by', 'sort_direction'
            ]);
            
            $orders = $this->orderRepository->paginate($perPage, $filters);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $orders,
                ]);
            }
            
            return view('order::index', compact('orders'));
        } catch (\Exception $e) {
            Log::error('Error in OrderController@index: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while retrieving orders.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while retrieving orders.');
        }
    }

    /**
     * Display a listing of the orders by store.
     *
     * @param Request $request
     * @param int $storeId
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function indexByStore(Request $request, int $storeId)
    {
        try {
            $store = $this->storeRepository->find($storeId);
            
            if (!$store) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Store not found.',
                    ], 404);
                }
                
                return redirect()->route('store.index')->with('error', 'Store not found.');
            }
            
            $perPage = $request->get('per_page', 15);
            $filters = $request->only([
                'status', 'payment_status', 'user_id', 
                'start_date', 'end_date', 'sort_by', 'sort_direction'
            ]);
            
            $orders = $this->orderRepository->paginateByStoreId($storeId, $perPage, $filters);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $orders,
                ]);
            }
            
            return view('order::store.index', compact('orders', 'store'));
        } catch (\Exception $e) {
            Log::error('Error in OrderController@indexByStore: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while retrieving orders.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while retrieving orders.');
        }
    }

    /**
     * Show the form for creating a new order.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $stores = $this->storeRepository->all();
        $products = $this->productRepository->getActive();
        
        return view('order::create', compact('stores', 'products'));
    }

    /**
     * Show the form for creating a new order for a specific store.
     *
     * @param Request $request
     * @param int $storeId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function createForStore(Request $request, int $storeId)
    {
        $store = $this->storeRepository->find($storeId);
        
        if (!$store) {
            return redirect()->route('store.index')->with('error', 'Store not found.');
        }
        
        $products = $this->productRepository->getActive($storeId);
        
        return view('order::store.create', compact('store', 'products'));
    }

    /**
     * Store a newly created order in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'store_id' => 'required|integer|exists:stores,id',
                'user_id' => 'required|integer|exists:users,id',
                'shipping_address' => 'nullable|string',
                'billing_address' => 'nullable|string',
                'payment_method' => 'nullable|string',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);
            
            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation error.',
                        'errors' => $validator->errors(),
                    ], 422);
                }
                
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Calculate total amount and prepare items
            $totalAmount = 0;
            $orderItems = [];
            
            foreach ($request->input('items') as $item) {
                $product = $this->productRepository->find($item['product_id']);
                
                if (!$product) {
                    continue;
                }
                
                // Check if there's enough stock
                if ($product->getStock() < $item['quantity']) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Not enough stock for product: ' . $product->getName(),
                        ], 422);
                    }
                    
                    return redirect()->back()->with('error', 'Not enough stock for product: ' . $product->getName())->withInput();
                }
                
                $price = $product->getPrice();
                $subtotal = $price * $item['quantity'];
                $totalAmount += $subtotal;
                
                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
                
                // Update product stock
                $this->productRepository->updateStock($item['product_id'], $item['quantity'], false);
            }
            
            // Create order
            $orderData = $request->except('items');
            $orderData['total_amount'] = $totalAmount;
            $orderData['status'] = 'pending';
            $orderData['payment_status'] = 'pending';
            
            $order = $this->orderRepository->create($orderData);
            
            // Create order items
            foreach ($orderItems as &$item) {
                $item['order_id'] = $order->getId();
                $this->orderItemRepository->create($item);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully.',
                    'data' => $order,
                ], 201);
            }
            
            return redirect()->route('order.show', $order->getId())
                ->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            Log::error('Error in OrderController@store: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the order.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while creating the order.')->withInput();
        }
    }

    /**
     * Display the specified order.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, int $id)
    {
        try {
            $order = $this->orderRepository->find($id);
            
            if (!$order) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found.',
                    ], 404);
                }
                
                return redirect()->route('order.index')->with('error', 'Order not found.');
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $order,
                ]);
            }
            
            return view('order::show', compact('order'));
        } catch (\Exception $e) {
            Log::error('Error in OrderController@show: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while retrieving the order.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while retrieving the order.');
        }
    }

    /**
     * Show the form for editing the specified order.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(int $id)
    {
        try {
            $order = $this->orderRepository->find($id);
            
            if (!$order) {
                return redirect()->route('order.index')->with('error', 'Order not found.');
            }
            
            $stores = $this->storeRepository->all();
            
            return view('order::edit', compact('order', 'stores'));
        } catch (\Exception $e) {
            Log::error('Error in OrderController@edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while retrieving the order.');
        }
    }

    /**
     * Update the specified order in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        try {
            $order = $this->orderRepository->find($id);
            
            if (!$order) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found.',
                    ], 404);
                }
                
                return redirect()->route('order.index')->with('error', 'Order not found.');
            }
            
            $validator = Validator::make($request->all(), [
                'shipping_address' => 'nullable|string',
                'billing_address' => 'nullable|string',
                'payment_method' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation error.',
                        'errors' => $validator->errors(),
                    ], 422);
                }
                
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            $updatedOrder = $this->orderRepository->update($id, $request->all());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order updated successfully.',
                    'data' => $updatedOrder,
                ]);
            }
            
            return redirect()->route('order.show', $updatedOrder->getId())
                ->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in OrderController@update: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the order.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating the order.')->withInput();
        }
    }

    /**
     * Update the order status.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, int $id)
    {
        try {
            $order = $this->orderRepository->find($id);
            
            if (!$order) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found.',
                    ], 404);
                }
                
                return redirect()->route('order.index')->with('error', 'Order not found.');
            }
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,processing,completed,cancelled',
            ]);
            
            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation error.',
                        'errors' => $validator->errors(),
                    ], 422);
                }
                
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            $updatedOrder = $this->orderRepository->updateStatus($id, $request->input('status'));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully.',
                    'data' => $updatedOrder,
                ]);
            }
            
            return redirect()->route('order.show', $updatedOrder->getId())
                ->with('success', 'Order status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in OrderController@updateStatus: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the order status.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating the order status.')->withInput();
        }
    }

    /**
     * Update the payment status.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updatePaymentStatus(Request $request, int $id)
    {
        try {
            $order = $this->orderRepository->find($id);
            
            if (!$order) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found.',
                    ], 404);
                }
                
                return redirect()->route('order.index')->with('error', 'Order not found.');
            }
            
            $validator = Validator::make($request->all(), [
                'payment_status' => 'required|string|in:pending,paid,failed,refunded',
            ]);
            
            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation error.',
                        'errors' => $validator->errors(),
                    ], 422);
                }
                
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            $updatedOrder = $this->orderRepository->updatePaymentStatus($id, $request->input('payment_status'));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment status updated successfully.',
                    'data' => $updatedOrder,
                ]);
            }
            
            return redirect()->route('order.show', $updatedOrder->getId())
                ->with('success', 'Payment status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in OrderController@updatePaymentStatus: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the payment status.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating the payment status.')->withInput();
        }
    }

    /**
     * Remove the specified order from storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $order = $this->orderRepository->find($id);
            
            if (!$order) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found.',
                    ], 404);
                }
                
                return redirect()->route('order.index')->with('error', 'Order not found.');
            }
            
            // Return items to inventory if order is not completed
            if ($order->getStatus() !== 'completed') {
                foreach ($order->getItems() as $item) {
                    $this->productRepository->updateStock(
                        $item->getProductId(),
                        $item->getQuantity(),
                        true
                    );
                }
            }
            
            $this->orderRepository->delete($id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order deleted successfully.',
                ]);
            }
            
            return redirect()->route('order.index')
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error in OrderController@destroy: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the order.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while deleting the order.');
        }
    }

    /**
     * Search for orders.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $filters = $request->only([
                'status', 'payment_status', 'store_id', 'user_id', 
                'start_date', 'end_date', 'sort_by', 'sort_direction'
            ]);
            
            $orders = $this->orderRepository->paginate($perPage, $filters);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $orders,
                ]);
            }
            
            return view('order::search', compact('orders'));
        } catch (\Exception $e) {
            Log::error('Error in OrderController@search: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while searching for orders.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while searching for orders.');
        }
    }
}

<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Product\Repositories\Interfaces\ProductRepositoryInterface;
use Modules\Store\Repositories\Interfaces\StoreRepositoryInterface;

class ProductController extends Controller
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * ProductController constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Display a listing of the products.
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
        
        // Get products based on user role
        $products = null;
        if ($guard === 'admin') {
            $products = $this->productRepository->getAllProducts();
        } elseif ($guard === 'store-owner' || $guard === 'store-staff') {
            $user = auth($guard)->user();
            $storeId = $user->store_id;
            $products = $this->productRepository->getProductsByStoreId($storeId);
        }
        
        // If it's an AJAX request, return JSON response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        }
        
        // Set the view prefix based on the guard
        $viewPrefix = '';
        if ($guard === 'store-owner') {
            $viewPrefix = 'store-owner.';
        } elseif ($guard === 'store-staff') {
            $viewPrefix = 'store-staff.';
        }
        
        // Otherwise, return view with products
        return view('product::' . $viewPrefix . 'index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     *
     * @param Request $request
     * @param int|null $storeId
     * @return \Illuminate\View\View
     */
    public function create(Request $request, ?int $storeId = null)
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
        
        // Get stores based on user role
        $stores = null;
        if ($guard === 'admin') {
            $stores = $this->storeRepository->all();
        } elseif ($guard === 'store-owner' || $guard === 'store-staff') {
            $user = auth($guard)->user();
            $storeId = $user->store_id;
            $stores = [$this->storeRepository->findById($storeId)];
        }
        
        // Set the view prefix based on the guard
        $viewPrefix = '';
        if ($guard === 'store-owner') {
            $viewPrefix = 'store-owner.';
        } elseif ($guard === 'store-staff') {
            $viewPrefix = 'store-staff.';
        }
        
        // Return the view with stores data
        return view('product::' . $viewPrefix . 'create', compact('stores', 'storeId'));
    }

    /**
     * Store a newly created product in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            // Add other validation rules as needed
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

        // Create the product
        $product = $this->productRepository->createProduct($request->all());

        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }

        // Determine the correct route name based on the guard
        $routeName = 'product.index';
        if ($guard === 'store-owner') {
            $routeName = 'store-owner.product.index';
        } elseif ($guard === 'store-staff') {
            $routeName = 'store-staff.product.index';
        } elseif ($guard === 'admin') {
            $routeName = 'admin.product.index';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ]);
        }

        return redirect()->route($routeName)->with('success', 'Product created successfully');
    }

    /**
     * Display the specified product.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, int $id)
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

        // Find the product
        $product = $this->productRepository->findById($id);

        if (!$product) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            // Determine the correct route name based on the guard
            $routeName = 'product.index';
            if ($guard === 'store-owner') {
                $routeName = 'store-owner.product.index';
            } elseif ($guard === 'store-staff') {
                $routeName = 'store-staff.product.index';
            } elseif ($guard === 'admin') {
                $routeName = 'admin.product.index';
            }
            
            return redirect()->route($routeName)->with('error', 'Product not found');
        }

        // Check if the user has permission to view this product
        if (($guard === 'store-owner' || $guard === 'store-staff') && $product->store_id !== auth($guard)->user()->store_id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this product'
                ], 403);
            }
            
            // Determine the correct route name based on the guard
            $routeName = 'product.index';
            if ($guard === 'store-owner') {
                $routeName = 'store-owner.product.index';
            } elseif ($guard === 'store-staff') {
                $routeName = 'store-staff.product.index';
            } elseif ($guard === 'admin') {
                $routeName = 'admin.product.index';
            }
            
            return redirect()->route($routeName)->with('error', 'You do not have permission to view this product');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        }
        
        // Set the view prefix based on the guard
        $viewPrefix = '';
        if ($guard === 'store-owner') {
            $viewPrefix = 'store-owner.';
        } elseif ($guard === 'store-staff') {
            $viewPrefix = 'store-staff.';
        }
        
        return view('product::' . $viewPrefix . 'show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(int $id)
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

        // Find the product
        $product = $this->productRepository->findById($id);

        if (!$product) {
            // Determine the correct route name based on the guard
            $routeName = 'product.index';
            if ($guard === 'store-owner') {
                $routeName = 'store-owner.product.index';
            } elseif ($guard === 'store-staff') {
                $routeName = 'store-staff.product.index';
            } elseif ($guard === 'admin') {
                $routeName = 'admin.product.index';
            }
            
            return redirect()->route($routeName)->with('error', 'Product not found');
        }

        // Check if the user has permission to edit this product
        if (($guard === 'store-owner' || $guard === 'store-staff') && $product->store_id !== auth($guard)->user()->store_id) {
            // Determine the correct route name based on the guard
            $routeName = 'product.index';
            if ($guard === 'store-owner') {
                $routeName = 'store-owner.product.index';
            } elseif ($guard === 'store-staff') {
                $routeName = 'store-staff.product.index';
            } elseif ($guard === 'admin') {
                $routeName = 'admin.product.index';
            }
            
            return redirect()->route($routeName)->with('error', 'You do not have permission to edit this product');
        }

        // Get stores based on user role
        $stores = null;
        if ($guard === 'admin') {
            $stores = $this->storeRepository->all();
        } elseif ($guard === 'store-owner' || $guard === 'store-staff') {
            $user = auth($guard)->user();
            $storeId = $user->store_id;
            $stores = [$this->storeRepository->findById($storeId)];
        }
        
        // Set the view prefix based on the guard
        $viewPrefix = '';
        if ($guard === 'store-owner') {
            $viewPrefix = 'store-owner.';
        } elseif ($guard === 'store-staff') {
            $viewPrefix = 'store-staff.';
        }
        
        return view('product::' . $viewPrefix . 'edit', compact('product', 'stores'));
    }

    /**
     * Update the specified product in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            // Add other validation rules as needed
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

        // Update the product
        $product = $this->productRepository->updateProduct($id, $request->all());

        if (!$product) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            return redirect()->route('product.index')->with('error', 'Product not found');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        }

        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }

        // Determine the correct route name based on the guard
        $routeName = 'product.index';
        if ($guard === 'store-owner') {
            $routeName = 'store-owner.product.index';
        } elseif ($guard === 'store-staff') {
            $routeName = 'store-staff.product.index';
        } elseif ($guard === 'admin') {
            $routeName = 'admin.product.index';
        }
        
        return redirect()->route($routeName)->with('success', 'Product updated successfully');
    }

    /**
     * Update the product stock.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateStock(Request $request, int $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer|min:0',
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

        // Update the product stock
        $product = $this->productRepository->updateProductStock($id, $request->stock);

        if (!$product) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            return redirect()->route('product.index')->with('error', 'Product not found');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product stock updated successfully',
                'data' => $product
            ]);
        }

        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }

        // Determine the correct route name based on the guard
        $routeName = 'product.index';
        if ($guard === 'store-owner') {
            $routeName = 'store-owner.product.index';
        } elseif ($guard === 'store-staff') {
            $routeName = 'store-staff.product.index';
        } elseif ($guard === 'admin') {
            $routeName = 'admin.product.index';
        }
        
        return redirect()->route($routeName)->with('success', 'Product stock updated successfully');
    }

    /**
     * Remove the specified product from storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
    {
        // Delete the product
        $result = $this->productRepository->deleteProduct($id);

        if (!$result) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }
            
            return redirect()->route('product.index')->with('error', 'Product not found');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        }

        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }

        // Determine the correct route name based on the guard
        $routeName = 'product.index';
        if ($guard === 'store-owner') {
            $routeName = 'store-owner.product.index';
        } elseif ($guard === 'store-staff') {
            $routeName = 'store-staff.product.index';
        } elseif ($guard === 'admin') {
            $routeName = 'admin.product.index';
        }
        
        return redirect()->route($routeName)->with('success', 'Product deleted successfully');
    }

    /**
     * Search for products.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        // Get search parameters
        $query = $request->input('query');
        
        // Search for products
        $products = $this->productRepository->searchProducts($query);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        }
        
        // Determine which guard is being used
        $guard = null;
        if (auth('admin')->check()) {
            $guard = 'admin';
        } elseif (auth('store-owner')->check()) {
            $guard = 'store-owner';
        } elseif (auth('store-staff')->check()) {
            $guard = 'store-staff';
        }

        // Set the view prefix based on the guard
        $viewPrefix = '';
        if ($guard === 'store-owner') {
            $viewPrefix = 'store-owner.';
        } elseif ($guard === 'store-staff') {
            $viewPrefix = 'store-staff.';
        }
        
        return view('product::' . $viewPrefix . 'search', compact('products', 'query'));
    }
}

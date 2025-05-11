<?php

namespace Modules\Product\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Product\app\Repositories\Interfaces\ProductRepositoryInterface;
use Modules\Store\app\Repositories\Interfaces\StoreRepositoryInterface;

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
        try {
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['search', 'store_id', 'min_price', 'max_price', 'is_active', 'sort_by', 'sort_direction']);
            
            $products = $this->productRepository->paginate($perPage, $filters);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $products,
                ]);
            }
            
            return view('product::index', compact('products'));
        } catch (\Exception $e) {
            Log::error('Error in ProductController@index: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while retrieving products.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while retrieving products.');
        }
    }

    /**
     * Display a listing of the products by store.
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
            $filters = $request->only(['search', 'min_price', 'max_price', 'is_active', 'sort_by', 'sort_direction']);
            
            $products = $this->productRepository->paginateByStoreId($storeId, $perPage, $filters);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $products,
                ]);
            }
            
            return view('product::store.index', compact('products', 'store'));
        } catch (\Exception $e) {
            Log::error('Error in ProductController@indexByStore: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while retrieving products.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while retrieving products.');
        }
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
        $stores = [];
        
        if ($storeId) {
            $store = $this->storeRepository->find($storeId);
            
            if (!$store) {
                return redirect()->route('store.index')->with('error', 'Store not found.');
            }
        } else {
            $stores = $this->storeRepository->all();
        }
        
        return view('product::create', compact('stores', 'storeId'));
    }

    /**
     * Store a newly created product in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'store_id' => 'required|integer|exists:stores,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'sku' => 'required|string|max:100|unique:products,sku',
                'image' => 'nullable|string|max:255',
                'is_active' => 'boolean',
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
            
            // Prepare the data, ensuring empty strings are converted to null
            $data = $request->all();
            if (empty($data['image'])) {
                $data['image'] = null;
            }
            
            $product = $this->productRepository->create($data);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product created successfully.',
                    'data' => $product,
                ], 201);
            }
            
            return redirect()->route('product.show', $product->getId())
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProductController@store: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            Log::error('Request data: ' . json_encode($request->all()));
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the product.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while creating the product.')->withInput();
        }
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
        try {
            $product = $this->productRepository->find($id);
            
            if (!$product) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found.',
                    ], 404);
                }
                
                return redirect()->route('product.index')->with('error', 'Product not found.');
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $product,
                ]);
            }
            
            return view('product::show', compact('product'));
        } catch (\Exception $e) {
            Log::error('Error in ProductController@show: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while retrieving the product.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while retrieving the product.');
        }
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(int $id)
    {
        try {
            $product = $this->productRepository->find($id);
            
            if (!$product) {
                return redirect()->route('product.index')->with('error', 'Product not found.');
            }
            
            $stores = $this->storeRepository->all();
            
            return view('product::edit', compact('product', 'stores'));
        } catch (\Exception $e) {
            Log::error('Error in ProductController@edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while retrieving the product.');
        }
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
        try {
            $product = $this->productRepository->find($id);
            
            if (!$product) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found.',
                    ], 404);
                }
                
                return redirect()->route('product.index')->with('error', 'Product not found.');
            }
            
            $validator = Validator::make($request->all(), [
                'store_id' => 'required|integer|exists:stores,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'sku' => 'required|string|max:100|unique:products,sku,' . $id,
                'image' => 'nullable|string|max:255',
                'is_active' => 'boolean',
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
            
            $updatedProduct = $this->productRepository->update($id, $request->all());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product updated successfully.',
                    'data' => $updatedProduct,
                ]);
            }
            
            return redirect()->route('product.show', $updatedProduct->getId())
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProductController@update: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the product.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating the product.')->withInput();
        }
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
        try {
            $product = $this->productRepository->find($id);
            
            if (!$product) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found.',
                    ], 404);
                }
                
                return redirect()->route('product.index')->with('error', 'Product not found.');
            }
            
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
                'increase' => 'required|boolean',
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
            
            $updatedProduct = $this->productRepository->updateStock(
                $id,
                $request->input('quantity'),
                $request->input('increase')
            );
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product stock updated successfully.',
                    'data' => $updatedProduct,
                ]);
            }
            
            return redirect()->route('product.show', $updatedProduct->getId())
                ->with('success', 'Product stock updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProductController@updateStock: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the product stock.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating the product stock.')->withInput();
        }
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
        try {
            $product = $this->productRepository->find($id);
            
            if (!$product) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found.',
                    ], 404);
                }
                
                return redirect()->route('product.index')->with('error', 'Product not found.');
            }
            
            $this->productRepository->delete($id);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product deleted successfully.',
                ]);
            }
            
            return redirect()->route('product.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error in ProductController@destroy: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the product.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while deleting the product.');
        }
    }

    /**
     * Search for products.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            $storeId = $request->input('store_id');
            $perPage = $request->input('per_page', 15);
            
            $products = $this->productRepository->search($query, $storeId, $perPage);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $products,
                ]);
            }
            
            return view('product::search', compact('products', 'query'));
        } catch (\Exception $e) {
            Log::error('Error in ProductController@search: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while searching for products.',
                    'error' => $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while searching for products.');
        }
    }
}

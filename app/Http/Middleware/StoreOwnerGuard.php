<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StoreOwnerGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول للوصول إلى هذه الصفحة.');
        }
        
        // Check if user has the store-owner role or admin role (admins can access all routes)
        if (!Auth::user()->hasAnyRole(['admin', 'store-owner'])) {
            return redirect()->route('access.denied')->with('error', 'يجب أن تكون مالك متجر للوصول إلى هذه الصفحة.');
        }
        
        // Admin can access all stores, so we only need to check permissions for store owners
        if (Auth::user()->hasRole('admin')) {
            return $next($request);
        }
        
        // If store_id is in the route parameters, check if the store owner has permission to access this store
        if ($request->route('store_id') || $request->input('store_id')) {
            $storeId = $request->route('store_id') ?? $request->input('store_id');
            $store = Store::find($storeId);
            
            // Check if the store exists and if the user has permission to manage this store
            if (!$store || !Auth::user()->hasPermissionTo('manage-store-' . $storeId)) {
                return redirect()->back()->with('error', 'ليس لديك صلاحية الوصول إلى هذا المتجر.');
            }
            
            // Check for specific action permissions based on the route
            $action = $request->route()->getActionMethod();
            $this->checkActionPermissions($action, $storeId);
        }
        
        return $next($request);
    }
    
    /**
     * Check if the user has permission to perform the requested action on the store
     *
     * @param string $action The controller action method
     * @param int $storeId The store ID
     * @return void
     */
    private function checkActionPermissions(string $action, int $storeId): void
    {
        $user = Auth::user();
        
        // Map controller methods to permission keys
        $permissionMap = [
            // Product related actions
            'index' => 'view-products-store-' . $storeId,
            'show' => 'view-products-store-' . $storeId,
            'create' => 'create-products-store-' . $storeId,
            'store' => 'create-products-store-' . $storeId,
            'edit' => 'edit-products-store-' . $storeId,
            'update' => 'edit-products-store-' . $storeId,
            'destroy' => 'delete-products-store-' . $storeId,
            'updateStock' => 'edit-products-store-' . $storeId,
            
            // Order related actions
            'updateStatus' => 'update-order-status-store-' . $storeId,
            'updatePaymentStatus' => 'update-payment-status-store-' . $storeId,
        ];
        
        // If the action requires a specific permission, check if the user has it
        if (isset($permissionMap[$action]) && !$user->hasPermissionTo($permissionMap[$action])) {
            abort(403, 'ليس لديك الصلاحية للقيام بهذا الإجراء.');
        }
    }
}

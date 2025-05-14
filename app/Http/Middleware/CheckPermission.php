<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check which guard is currently authenticated
        if (Auth::guard('admin')->check()) {
            // Admins have all permissions
            return $next($request);
        } elseif (Auth::guard('store-owner')->check()) {
            // Store owners have access to their own store data
            $storeOwner = Auth::guard('store-owner')->user();
            
            // If the route has a store parameter, check if the store owner owns this store
            if ($request->route('store') && !$storeOwner->ownsStore($request->route('store'))) {
                return redirect()->route('access.denied');
            }
            
            // Check if the store owner has the required permission
            if (!$storeOwner->hasPermission($permission)) {
                return redirect()->route('access.denied');
            }
            
            return $next($request);
        } elseif (Auth::guard('store-staff')->check()) {
            // Store staff have permissions based on their role
            $storeStaff = Auth::guard('store-staff')->user();
            
            // If the route has a store parameter, check if the staff belongs to this store
            if ($request->route('store') && $storeStaff->store_id != $request->route('store')) {
                return redirect()->route('access.denied');
            }
            
            // Check if the staff has the required permission
            if (!$storeStaff->hasPermission($permission)) {
                return redirect()->route('access.denied');
            }
            
            return $next($request);
        }
        
        // If no guard is authenticated or permission check fails
        return redirect()->route('access.denied');
    }
}

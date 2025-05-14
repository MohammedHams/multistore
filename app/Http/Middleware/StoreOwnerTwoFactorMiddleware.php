<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreOwnerTwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $storeOwner = Auth::guard('store-owner')->user();

        if ($storeOwner && 
            $storeOwner->two_factor_secret && 
            $storeOwner->two_factor_confirmed_at && 
            !$request->session()->get('auth.two_factor_confirmed')) {
            
            return redirect()->route('store-owner.two-factor.challenge');
        }

        return $next($request);
    }
}

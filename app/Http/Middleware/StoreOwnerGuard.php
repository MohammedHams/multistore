<?php

namespace App\Http\Middleware;

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
        if (!Auth::guard('store-owner')->check()) {
            // Store the intended URL for redirection after login
            if ($request->url() != route('store-owner.login')) {
                session()->put('url.intended', $request->url());
            }
            return redirect()->route('store-owner.login');
        }

        return $next($request);
    }
}

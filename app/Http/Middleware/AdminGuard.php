<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminGuard
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
        
        // Check if user has the admin role
        if (!Auth::user()->hasRole('admin')) {
            return redirect()->route('access.denied')->with('error', 'يجب أن تكون مسؤولاً للوصول إلى هذه الصفحة.');
        }
        
        // Admins have all permissions, so we can proceed
        return $next($request);
    }
}

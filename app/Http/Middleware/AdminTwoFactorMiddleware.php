<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTwoFactorMiddleware
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
        $admin = Auth::guard('admin')->user();

        if ($admin && 
            $admin->two_factor_secret && 
            $admin->two_factor_confirmed_at && 
            !$request->session()->get('auth.two_factor_confirmed')) {
            
            return redirect()->route('admin.two-factor.challenge');
        }

        return $next($request);
    }
}

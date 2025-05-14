<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\TwoFactorAuthenticatable;

class RequireTwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $user = Auth::guard($guard)->user();

        // If no user is authenticated or the user doesn't use the TwoFactorAuthenticatable trait, proceed
        if (!$user || !in_array(TwoFactorAuthenticatable::class, class_uses_recursive(get_class($user)))) {
            return $next($request);
        }

        // Check if 2FA is enabled for this user
        if ($user->two_factor_secret && !$request->session()->get('auth.two_factor_confirmed')) {
            // Store the intended URL to redirect back after 2FA confirmation
            $request->session()->put('auth.two_factor_intended_url', $request->fullUrl());
            
            // Determine the redirect route based on the guard
            $redirectRoute = 'two-factor.login';
            if ($guard === 'admin') {
                $redirectRoute = 'admin.two-factor.login';
            } elseif ($guard === 'store-owner') {
                $redirectRoute = 'store-owner.two-factor.login';
            }
            
            return redirect()->route($redirectRoute);
        }

        return $next($request);
    }
}

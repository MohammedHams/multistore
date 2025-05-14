<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireTwoFactorSetup
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

        // If the user has not set up 2FA (has secret but not confirmed)
        if ($user && $user->two_factor_secret && !$user->two_factor_confirmed_at) {
            // Determine the appropriate redirect based on the guard
            if ($guard === 'admin') {
                return redirect('/admin/two-factor-auth');
            } elseif ($guard === 'store-owner') {
                return redirect('/store-owner/two-factor-auth');
            } elseif ($guard === 'store-staff') {
                return redirect('/store-staff/two-factor-auth');
            } else {
                // Default redirect for web guard
                return redirect('/user/two-factor-auth');
            }
        }

        return $next($request);
    }
}

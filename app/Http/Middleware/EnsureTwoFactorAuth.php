<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureTwoFactorAuth
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
        // Check if the user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if the user has two-factor authentication enabled
        $user = auth()->user();
        if ($user->two_factor_secret) {
            // Check if the user has completed the two-factor authentication
            if (Session::has('login.id') && !Session::has('two_factor_confirmed')) {
                return redirect()->route('two-factor.challenge');
            }
        }

        return $next($request);
    }
}

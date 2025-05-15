<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Determine which guard is being used and redirect accordingly
            if ($request->is('admin/*')) {
                return route('admin.login');
            } elseif ($request->is('store-owner/*')) {
                return route('store-owner.login');
            } elseif ($request->is('store-staff/*')) {
                return route('store-staff.login');
            } else {
                return route('/');
            }
        }
        
        return null;
    }
}

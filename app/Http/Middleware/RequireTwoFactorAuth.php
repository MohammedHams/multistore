<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Mail\TwoFactorCode;
use Illuminate\Support\Facades\Mail;

class RequireTwoFactorAuth
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
        // Determine which guard is being used
        $guard = null;
        
        if (Auth::guard('admin')->check()) {
            $guard = 'admin';
            $user = Auth::guard('admin')->user();
        } elseif (Auth::guard('store-owner')->check()) {
            $guard = 'store-owner';
            $user = Auth::guard('store-owner')->user();
        } elseif (Auth::guard('store-staff')->check()) {
            $guard = 'store-staff';
            $user = Auth::guard('store-staff')->user();
        } elseif (Auth::check()) {
            $guard = 'web';
            $user = Auth::user();
        } else {
            // No authenticated user, proceed with the request
            return $next($request);
        }
        
        // Check if the user has two-factor authentication enabled
        if ($user->user && $user->user->two_factor_secret && !Session::has('two_factor_authenticated')) {
            // Create a database OTP record
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Try to store the OTP in the database if the table exists
            try {
                \App\Models\VerifyOtp::create([
                    'user_id' => $user->user->id,
                    'code' => $code,
                    'expires_at' => now()->addMinutes(10),
                    'attempts' => 0,
                    'used' => false,
                ]);
            } catch (\Exception $e) {
                // If there's an error with the database, just continue with session-based OTP
                // No need to do anything here as we'll fall back to session-based OTP
            }
            
            // Also store the code in the session as a backup
            Session::put('two_factor_code', [
                'code' => $code,
                'expires_at' => now()->addMinutes(10)->timestamp,
            ]);
            
            // Store the intended URL and guard information
            Session::put('url.intended', $request->url());
            Session::put('auth.guard', $guard);
            
            // Send the code via email
            Mail::to($user->user->email)->send(new TwoFactorCode($code));
            
            // Log the user out but keep their ID in the session for re-authentication
            Session::put('login.id', $user->user->id);
            Session::put('login.remember', $request->cookie('remember_' . $guard));
            Auth::guard($guard)->logout();
            
            // Redirect to the two-factor challenge page
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}

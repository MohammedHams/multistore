<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StoreOwner;
use App\Providers\RouteServiceProvider;
use App\Traits\CustomAuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class StoreOwnerAuthController extends Controller
{
    use CustomAuthenticatesUsers;
    
    /**
     * The email two-factor provider instance.
     *
     * @var \App\Auth\TwoFactor\EmailTwoFactorProvider
     */
    protected $emailProvider;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/store-owner/dashboard';

    /**
     * Create a new controller instance.
     *
     * @param  \App\Auth\TwoFactor\EmailTwoFactorProvider  $emailProvider
     * @return void
     */
    public function __construct(\App\Auth\TwoFactor\EmailTwoFactorProvider $emailProvider)
    {
        $this->middleware('guest:store-owner')->except(['logout', 'showTwoFactorChallenge', 'twoFactorChallenge', 'resendTwoFactorCode']);
        $this->emailProvider = $emailProvider;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.store-owner.login');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('store-owner');
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        // First find the store owner by email
        $storeOwner = StoreOwner::whereHas('user', function ($query) use ($request) {
            $query->where('email', $request->email);
        })->first();

        if (!$storeOwner) {
            \Illuminate\Support\Facades\Log::info('Store owner not found for email: ' . $request->email);
            return false;
        }
        
        \Illuminate\Support\Facades\Log::info('Store owner found:', [
            'id' => $storeOwner->id,
            'has_two_factor' => !empty($storeOwner->two_factor_secret),
            'two_factor_secret' => $storeOwner->two_factor_secret ? 'exists' : 'null',
            'user_id' => $storeOwner->user_id ?? 'null'
        ]);

        // Attempt to authenticate using the store owner guard
        $result = $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
        
        \Illuminate\Support\Facades\Log::info('Authentication result:', ['success' => $result]);
        
        // If authentication was successful and the user has two-factor auth enabled
        if ($result && !empty($storeOwner->two_factor_secret)) {
            \Illuminate\Support\Facades\Log::info('Two-factor auth is enabled, redirecting to challenge');
            $this->guard()->logout();
            
            // Store the user ID and remember flag in the session
            $request->session()->put([
                'login.id' => $storeOwner->user_id,
                'login.remember' => $request->filled('remember'),
                'auth.guard' => 'store-owner',
            ]);
            
            // Redirect to the common two-factor challenge page
            return redirect()->route('two-factor.challenge');
        } else if ($result) {
            \Illuminate\Support\Facades\Log::info('Two-factor auth is NOT enabled, proceeding with normal login');
        }
        
        return $result;
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        // Get the store owner associated with the user
        $storeOwner = StoreOwner::whereHas('user', function ($query) use ($request) {
            $query->where('email', $request->email);
        })->first();

        if ($storeOwner) {
            // Return credentials for the store owner guard
            return [
                'id' => $storeOwner->id,
                'password' => $request->password,
            ];
        }

        return $request->only($this->username(), 'password');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return $this->loggedOut($request) ?: redirect('/store-owner/login');
    }
    
    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            // Get the authenticated store owner user
            $storeOwner = $this->guard()->user();
            
            if ($storeOwner) {
                $user = $storeOwner->user;
                
                // Check if 2FA is enabled for this user
                if ($user && !empty($user->two_factor_secret)) {
                    // Store store owner ID in session for the two-factor challenge
                    $request->session()->put([
                        'login.id' => $storeOwner->id,
                        'login.remember' => $request->filled('remember'),
                        'auth.guard' => 'store-owner',
                    ]);

                    // Log the store owner out as they need to complete 2FA
                    $this->guard()->logout();
                    
                    // Redirect to the common two-factor challenge page
                    return redirect()->route('two-factor.challenge')
                        ->with('status', 'Please enter your two-factor authentication code.');
                }
            }
            
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }
}

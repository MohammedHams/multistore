<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StoreStaff;
use App\Providers\RouteServiceProvider;
use App\Traits\CustomAuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreStaffAuthController extends Controller
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
    protected $redirectTo = '/store-staff/dashboard';

    /**
     * Create a new controller instance.
     *
     * @param  \App\Auth\TwoFactor\EmailTwoFactorProvider  $emailProvider
     * @return void
     */
    public function __construct(\App\Auth\TwoFactor\EmailTwoFactorProvider $emailProvider)
    {
        $this->middleware('guest:store-staff')->except(['logout', 'showTwoFactorChallenge', 'twoFactorChallenge', 'resendTwoFactorCode']);
        $this->emailProvider = $emailProvider;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.store-staff.login');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('store-staff');
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
        // First find the store staff by email
        $storeStaff = StoreStaff::whereHas('user', function ($query) use ($request) {
            $query->where('email', $request->email);
        })->first();

        if (!$storeStaff) {
            return false;
        }

        // Attempt to authenticate using the store staff guard
        $result = $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
        
        // If authentication was successful and the user has two-factor auth enabled
        if ($result && $storeStaff->two_factor_secret) {
            $this->guard()->logout();
            
            // Store the user ID and remember flag in the session
            $request->session()->put([
                'login.id' => $storeStaff->id,
                'login.remember' => $request->filled('remember'),
                'auth.guard' => 'store-staff',
            ]);
            
            // Redirect to the common two-factor challenge page
            return redirect()->route('two-factor.challenge');
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
        // Get the store staff associated with the user
        $storeStaff = StoreStaff::whereHas('user', function ($query) use ($request) {
            $query->where('email', $request->email);
        })->first();

        if ($storeStaff) {
            // Return credentials for the store staff guard
            return [
                'id' => $storeStaff->id,
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

        return $this->loggedOut($request) ?: redirect('/store-staff/login');
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
            // Get the authenticated store staff user
            $storeStaff = $this->guard()->user();
            
            if ($storeStaff) {
                $user = $storeStaff->user;
                
                // Check if 2FA is enabled for this user
                if ($user && !empty($user->two_factor_secret)) {
                    // Store store staff ID in session for the two-factor challenge
                    $request->session()->put([
                        'login.id' => $storeStaff->id,
                        'login.remember' => $request->filled('remember'),
                        'auth.guard' => 'store-staff',
                    ]);

                    // Log the store staff out as they need to complete 2FA
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

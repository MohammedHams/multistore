<?php

namespace App\Http\Controllers\Auth;

use App\Auth\TwoFactor\EmailTwoFactorProvider;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Providers\RouteServiceProvider;
use App\Responses\AdminLoginResponse;
use App\Traits\CustomAuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
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
    protected $redirectTo = '/admin/dashboard';

    /**
     * Create a new controller instance.
     *
     * @param  \App\Auth\TwoFactor\EmailTwoFactorProvider  $emailProvider
     * @return void
     */
    public function __construct(EmailTwoFactorProvider $emailProvider)
    {
        $this->middleware('guest:admin')->except(['logout', 'showTwoFactorChallenge', 'twoFactorChallenge', 'resendTwoFactorCode']);
        $this->emailProvider = $emailProvider;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.admin.login');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
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
        // First find the admin by email
        $admin = Admin::whereHas('user', function ($query) use ($request) {
            $query->where('email', $request->email);
        })->first();

        if (!$admin) {
            return false;
        }

        // Attempt to authenticate using the admin guard
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->filled('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        // Get the user associated with the admin
        $admin = Admin::whereHas('user', function ($query) use ($request) {
            $query->where('email', $request->email);
        })->first();

        if ($admin) {
            // Return credentials for the admin guard
            return [
                'id' => $admin->id,
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

        return $this->loggedOut($request) ?: redirect('/admin/login');
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
            // Get the authenticated admin user
            $admin = $this->guard()->user();

            if ($admin) {
                $user = $admin->user;

                // Check if 2FA is enabled for this user
                if ($user && !empty($user->two_factor_secret)) {
                    // Store admin ID in session for the two-factor challenge
                    $request->session()->put([
                        'login.id' => $admin->id,
                        'login.remember' => $request->filled('remember'),
                        'auth.guard' => 'admin',
                    ]);

                    // Log the admin out as they need to complete 2FA
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

    /**
     * Show the two-factor authentication challenge form.
     *
     * @return \Illuminate\View\View
     */
    public function showTwoFactorChallenge()
    {
        return view('auth.admin.two-factor-challenge');
    }

    /**
     * Validate the two-factor authentication challenge.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function twoFactorChallenge(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        // Get the admin ID from the session
        $adminId = $request->session()->get('login.id');
        if (!$adminId) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find($adminId);
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $user = $admin->user;
        if (!$user) {
            return redirect()->route('admin.login');
        }

        if ($request->code) {
            try {
                // Create the providers for verification
                $google2fa = new \PragmaRX\Google2FA\Google2FA();
                $fortifyProvider = new \Laravel\Fortify\TwoFactorAuthenticationProvider($google2fa);
                $emailProvider = new \App\Auth\TwoFactor\EmailTwoFactorProvider($fortifyProvider);

                // Validate the OTP code
                $valid = $emailProvider->verify($user, $request->code);

                if (!$valid) {
                    return back()->withErrors(['code' => 'The provided two-factor authentication code was invalid.']);
                }

                // Complete the authentication
                $this->completeAuthentication($request, $admin);

                return redirect()->intended(route('admin.dashboard'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error validating 2FA code: ' . $e->getMessage());
                return back()->withErrors(['code' => 'An error occurred while validating your code. Please try again.']);
            }
        }

        if ($request->recovery_code) {
            // Validate the recovery code
            if ($user->two_factor_recovery_codes) {
                try {
                    $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

                    if (in_array($request->recovery_code, $recoveryCodes)) {
                        // Remove the used recovery code
                        $recoveryCodes = array_diff($recoveryCodes, [$request->recovery_code]);
                        $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
                        $user->save();

                        // Complete the authentication
                        $this->completeAuthentication($request, $admin);

                        return redirect()->intended(route('admin.dashboard'));
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error validating recovery code: ' . $e->getMessage());
                }
            }

            return back()->withErrors(['recovery_code' => 'The provided two-factor recovery code was invalid.']);
        }

        return back()->withErrors(['code' => 'You must provide either a two-factor authentication code or a recovery code.']);
    }

    /**
     * Complete the authentication process after successful verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return void
     */
    protected function completeAuthentication(Request $request, $admin)
    {
        // Set session flag to indicate 2FA is completed
        Session::put('two_factor_confirmed', true);

        // Clear the login session data
        $request->session()->forget('login.id');
        $request->session()->forget('auth.guard');

        // Log the admin in
        $this->guard()->login($admin, $request->session()->pull('login.remember', false));
    }

    /**
     * Resend the two-factor authentication code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendTwoFactorCode(Request $request)
    {
        // Get the admin ID from the session
        $adminId = $request->session()->get('login.id');
        if (!$adminId) {
            return redirect()->route('admin.login');
        }

        $admin = Admin::find($adminId);
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        $user = $admin->user;
        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Send a new OTP code
        $this->emailProvider->sendOtpCode($user);

        return redirect()->route('admin.two-factor.challenge')
            ->with('status', 'A new verification code has been sent to your email.');
    }
}

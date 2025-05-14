<?php

namespace App\Responses;

use App\Auth\TwoFactor\EmailTwoFactorProvider;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class AdminLoginResponse implements LoginResponseContract
{
    /**
     * @var EmailTwoFactorProvider
     */
    protected $emailProvider;

    /**
     * Create a new response instance.
     *
     * @param EmailTwoFactorProvider $emailProvider
     */
    public function __construct(EmailTwoFactorProvider $emailProvider)
    {
        $this->emailProvider = $emailProvider;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $admin = Auth::guard('admin')->user();

        // Check if the admin has two-factor authentication enabled
        if ($admin && $admin->two_factor_secret && $admin->two_factor_confirmed_at) {
            // Store admin ID in session for the two-factor challenge
            $request->session()->put([
                'login.id' => $admin->id,
                'login.remember' => $request->filled('remember'),
                'auth.guard' => 'admin',
            ]);

            // Log the admin out as they need to complete 2FA
            Auth::guard('admin')->logout();

            // Send an OTP code to the admin's email
            $user = $admin->user;
            if ($user) {
                $this->emailProvider->sendOtpCode($user);
            }

            // Redirect to the two-factor challenge page
            return redirect()->route('admin.two-factor.challenge');
        }

        // If no 2FA, proceed with normal login
        return redirect()->intended(route('admin.dashboard'));
    }
}

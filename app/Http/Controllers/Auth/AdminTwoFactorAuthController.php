<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Laravel\Fortify\RecoveryCode;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use PragmaRX\Google2FA\Google2FA;

class AdminTwoFactorAuthController extends Controller
{
    /**
     * Show the two-factor authentication setup page.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $admin = Auth::guard('admin')->user();
        
        return view('auth.admin.two-factor-auth', [
            'enabled' => $admin->two_factor_secret && $admin->two_factor_confirmed_at,
            'confirming' => $admin->two_factor_secret && !$admin->two_factor_confirmed_at,
            'recoveryCodes' => $admin->two_factor_recovery_codes ? json_decode(decrypt($admin->two_factor_recovery_codes), true) : [],
        ]);
    }

    /**
     * Enable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enable(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication($admin);
        
        return redirect()->route('admin.two-factor.show')
            ->with('status', 'Two-factor authentication has been enabled. Scan the QR code with your authenticator app.');
    }

    /**
     * Confirm two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        
        $admin = Auth::guard('admin')->user();
        
        $confirmTwoFactorAuthentication = app(ConfirmTwoFactorAuthentication::class);
        
        if ($confirmTwoFactorAuthentication($admin, $request->code)) {
            return redirect()->route('admin.two-factor.show')
                ->with('status', 'Two-factor authentication has been confirmed and enabled.');
        }
        
        return back()->withErrors(['code' => 'The provided two-factor authentication code was invalid.']);
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $disableTwoFactorAuthentication = app(DisableTwoFactorAuthentication::class);
        $disableTwoFactorAuthentication($admin);
        
        return redirect()->route('admin.two-factor.show')
            ->with('status', 'Two-factor authentication has been disabled.');
    }

    /**
     * Generate a QR code for two-factor authentication.
     *
     * @return \Illuminate\Http\Response
     */
    public function qrCode()
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->two_factor_secret) {
            return abort(404);
        }
        
        return response()->json([
            'svg' => $admin->twoFactorQrCodeSvg(),
        ]);
    }

    /**
     * Get the recovery codes for the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function recoveryCodes()
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->two_factor_recovery_codes) {
            return abort(404);
        }
        
        return response()->json([
            'recoveryCodes' => json_decode(decrypt($admin->two_factor_recovery_codes), true),
        ]);
    }

    /**
     * Regenerate the recovery codes for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        $generateNewRecoveryCodes = app(GenerateNewRecoveryCodes::class);
        $generateNewRecoveryCodes($admin);
        
        return redirect()->route('admin.two-factor.show')
            ->with('status', 'Two-factor authentication recovery codes have been regenerated.');
    }

    /**
     * Show the two-factor authentication challenge form.
     *
     * @return \Illuminate\View\View
     */
    public function showChallenge()
    {
        return view('auth.admin.two-factor-challenge');
    }

    /**
     * Validate the two-factor authentication challenge.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function challenge(Request $request)
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
                // Create the provider for verification
                $provider = new TwoFactorAuthenticationProvider(new Google2FA());
                
                // Validate the OTP code
                if (!$provider->verify(
                    decrypt($user->two_factor_secret),
                    $request->code
                )) {
                    return back()->withErrors(['code' => 'The provided two-factor authentication code was invalid.']);
                }
                
                // Complete the authentication
                $this->completeAuthentication($request, $admin);
                
                return redirect()->intended(route('admin.dashboard'));
            } catch (\Exception $e) {
                Log::error('Error validating 2FA code: ' . $e->getMessage());
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
                    Log::error('Error validating recovery code: ' . $e->getMessage());
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
        $request->session()->put('auth.two_factor_confirmed', true);
        $request->session()->put('two_factor_authenticated', true);
        
        // Clear the login session data
        $request->session()->forget('login.id');
        
        // Log the admin in
        Auth::guard('admin')->login($admin, $request->session()->pull('login.remember', false));
    }
    
    /**
     * Resend the two-factor authentication code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendTwoFactorCode(Request $request)
    {
        // With Fortify's 2FA, we don't need to resend codes as it uses TOTP
        // Just redirect back to the challenge page with a message
        return redirect()->route('admin.two-factor.challenge')
            ->with('status', 'Please use your authenticator app to get a fresh code.');
    }
}

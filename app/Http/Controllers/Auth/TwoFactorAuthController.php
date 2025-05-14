<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Auth\TwoFactor\EmailTwoFactorProvider;
use App\Auth\TwoFactor\SmsTwoFactorProvider;
use App\Mail\TwoFactorCode;
use App\Models\VerifyOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\TwoFactorAuthenticationProvider as FortifyTwoFactorProvider;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends Controller
{
    /**
     * Send a new two-factor authentication code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendCode(Request $request)
    {
        Log::info('Resend code request:', $request->all());
        
        $user = Auth::user();

        if (!$user) {
            // If no authenticated user, try to get the user from the session
            $userId = $request->session()->get('login.id');
            $guard = $request->session()->get('auth.guard', 'web');
            
            Log::info('No authenticated user, checking session:', [
                'userId' => $userId,
                'guard' => $guard
            ]);
            
            // Find the appropriate user based on the guard
            $guardUser = null;
            
            if ($guard === 'admin') {
                $guardUser = \App\Models\Admin::find($userId);
                if ($guardUser) {
                    $user = $guardUser->user;
                }
            } elseif ($guard === 'store-owner') {
                $guardUser = \App\Models\StoreOwner::find($userId);
                if ($guardUser) {
                    $user = $guardUser->user;
                }
            } elseif ($guard === 'store-staff') {
                $guardUser = \App\Models\StoreStaff::find($userId);
                if ($guardUser) {
                    $user = $guardUser->user;
                }
            } else {
                $user = \App\Models\User::find($userId);
            }
            
            if (!$user) {
                Log::warning('User not found for resend code');
                return redirect()->route('login');
            }
        }

        Log::info('Found user for resend code:', ['email' => $user->email]);
        
        // Determine the verification method (default to email if not specified)
        $method = $request->input('method', 'email');
        
        try {
            if ($method === 'sms') {
                // Check if user has a phone number
                if (empty($user->phone_number)) {
                    return back()->withErrors([
                        'phone' => 'لا يوجد رقم هاتف مسجل. الرجاء استخدام البريد الإلكتروني أو تحديث رقم هاتفك.'
                    ]);
                }
                
                // Use SMS provider
                $provider = new SmsTwoFactorProvider();
                $success = $provider->generateAndSendOtp($user);
                
                if ($success) {
                    return back()->with('status', 'تم إرسال رمز التحقق إلى رقم هاتفك.');
                } else {
                    // Fallback to email if SMS fails
                    Log::warning("SMS OTP failed for user {$user->id}, falling back to email");
                    $method = 'email';
                }
            }
            
            if ($method === 'email') {
                // Generate a 6-digit code
                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                
                Log::info('Generated new resend 2FA code: ' . $code);
                
                // Store the code in the session
                Session::put('two_factor_code', [
                    'code' => $code,
                    'expires_at' => now()->addMinutes(10)->timestamp,
                ]);
                
                // Send the code via email using queue
                Mail::to($user->email)->queue(new TwoFactorCode($code));
                
                return back()->with('status', 'We sent you a new code via email. Check your inbox shortly!');
            }
            
            // If we get here, neither method worked
            return back()->withErrors([
                'general' => 'Failed to send verification code. Please try again.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error sending 2FA code: " . $e->getMessage());
            return back()->withErrors([
                'email' => 'Failed to send verification code. Please try again.'
            ]);
        }
    }

    /**
     * Attempt to verify a two-factor authentication code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required_without:recovery_code|string|nullable',
            'recovery_code' => 'required_without:code|string|nullable',
        ]);

        $user = Auth::user();

        if (!$user) {
            // If no authenticated user, try to get the user from the session
            $userId = $request->session()->get('login.id');
            if ($userId) {
                $user = \App\Models\User::find($userId);
            }
            
            if (!$user) {
                return redirect()->route('login');
            }
        }

        if ($request->code) {
            // First check if we have a session-based OTP code
            $otpData = Session::get('two_factor_code');
            
            if ($otpData && 
                $otpData['code'] === $request->code && 
                $otpData['expires_at'] > now()->timestamp) {
                
                // Clear the code from the session
                Session::forget('two_factor_code');
                
                // Complete the authentication
                $this->completeAuthentication($request, $user);
                
                return redirect()->intended(config('fortify.home'));
            }
            
            // If no session code or it didn't match, try TOTP if user has it set up
            if ($user->two_factor_secret) {
                try {
                    // Create the provider for verification
                    $provider = new FortifyTwoFactorProvider(new Google2FA());
                    
                    // Validate the OTP code
                    if ($provider->verify(
                        decrypt($user->two_factor_secret),
                        $request->code
                    )) {
                        // Complete the authentication
                        $this->completeAuthentication($request, $user);
                        
                        return redirect()->intended(config('fortify.home'));
                    }
                } catch (\Exception $e) {
                    Log::error('Error validating 2FA code: ' . $e->getMessage());
                }
            }
            
            // Check the database for a matching OTP
            $otp = VerifyOtp::where('user_id', $user->id)
                ->where('otp', $request->code)
                ->where('expire_at', '>=', now())
                ->first();
                
            if ($otp) {
                // Delete the OTP
                $otp->delete();
                
                // Complete the authentication
                $this->completeAuthentication($request, $user);
                
                return redirect()->intended(config('fortify.home'));
            }
            
            return back()->withErrors([
                'code' => 'الرمز الذي أدخلته غير صحيح. الرجاء التحقق والمحاولة مرة أخرى.'
            ]);
        } else {
            // Handle recovery code verification
            // This is just a placeholder - actual recovery code verification would depend on your implementation
            return back()->withErrors([
                'recovery_code' => 'رمز الاسترداد غير صحيح. الرجاء التحقق والمحاولة مرة أخرى.'
            ]);
        }
    }
    
    /**
     * Show the two-factor authentication challenge view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showChallenge(Request $request)
    {
        // Get the user ID and guard from the session
        $userId = $request->session()->get('login.id');
        $guard = $request->session()->get('auth.guard', 'web');
        
        Log::info('showChallenge session data:', [
            'userId' => $userId,
            'guard' => $guard,
            'has_two_factor_code' => Session::has('two_factor_code'),
        ]);
        
        if (!$userId) {
            return redirect()->route('login');
        }
        
        // Find the appropriate user based on the guard
        $guardUser = null;
        $user = null;
        
        if ($guard === 'admin') {
            $guardUser = \App\Models\Admin::find($userId);
            if ($guardUser) {
                $user = $guardUser->user;
            }
        } elseif ($guard === 'store-owner') {
            $guardUser = \App\Models\StoreOwner::find($userId);
            if ($guardUser) {
                $user = $guardUser->user;
            }
        } elseif ($guard === 'store-staff') {
            $guardUser = \App\Models\StoreStaff::find($userId);
            if ($guardUser) {
                $user = $guardUser->user;
            }
        } else {
            $user = \App\Models\User::find($userId);
        }
        
        if (!$user) {
            Log::warning('User not found for ID: ' . $userId);
            return redirect()->route('login');
        }
        
        Log::info('User found:', ['email' => $user->email]);
        
        // Send an email code if one doesn't exist in the session
        if (!Session::has('two_factor_code')) {
            try {
                // Generate a random 6-digit code
                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                
                Log::info('Generated new 2FA code: ' . $code);
                
                // Store the code in the session with an expiration time (10 minutes)
                Session::put('two_factor_code', [
                    'code' => $code,
                    'expires_at' => now()->addMinutes(10)->timestamp,
                ]);
                
                // Send the code via email using queue
                Mail::to($user->email)->queue(new TwoFactorCode($code));
                
                // Flash a success message
                Session::flash('status', 'We sent you a code via email. Check your inbox shortly!');
            } catch (\Exception $e) {
                Log::error('Failed to send two-factor code: ' . $e->getMessage());
                Session::flash('warning', 'Failed to send verification code. Please try again.');
            }
        } else {
            $otpData = Session::get('two_factor_code');
            Log::info('Using existing 2FA code from session:', [
                'code' => $otpData['code'],
                'expires_at' => $otpData['expires_at'],
                'now' => now()->timestamp,
                'is_valid' => ($otpData['expires_at'] > now()->timestamp)
            ]);
            
            // If the code is expired, generate a new one
            if ($otpData['expires_at'] <= now()->timestamp) {
                Log::info('Code expired, generating new one');
                
                // Generate a random 6-digit code
                $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                
                // Store the code in the session with an expiration time (10 minutes)
                Session::put('two_factor_code', [
                    'code' => $code,
                    'expires_at' => now()->addMinutes(10)->timestamp,
                ]);
                
                // Send the code via email using queue
                Mail::to($user->email)->queue(new TwoFactorCode($code));
                
                // Flash a success message
                Session::flash('status', 'We sent you a new code via email. Check your inbox shortly!');
            }
        }
        
        return view('auth.two-factor-challenge');
    }
    
    /**
     * Handle the two-factor authentication challenge.
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

        // Log the request data for debugging
        Log::info('Two-factor challenge request data:', $request->all());
        
        // Get the user ID and guard from the session
        $userId = $request->session()->get('login.id');
        $guard = $request->session()->get('auth.guard', 'web');
        
        Log::info('Session data:', [
            'userId' => $userId,
            'guard' => $guard,
            'has_two_factor_code' => Session::has('two_factor_code'),
        ]);
        
        if (!$userId) {
            return redirect()->route('login');
        }
        
        // Find the appropriate user based on the guard
        $guardUser = null;
        $user = null;
        
        if ($guard === 'admin') {
            $guardUser = \App\Models\Admin::find($userId);
            if ($guardUser) {
                $user = $guardUser->user;
            }
        } elseif ($guard === 'store-owner') {
            $guardUser = \App\Models\StoreOwner::find($userId);
            if ($guardUser) {
                $user = $guardUser->user;
            }
        } elseif ($guard === 'store-staff') {
            $guardUser = \App\Models\StoreStaff::find($userId);
            if ($guardUser) {
                $user = $guardUser->user;
            }
        } else {
            $user = \App\Models\User::find($userId);
        }
        
        if (!$user) {
            Log::warning('User not found for ID: ' . $userId);
            return redirect()->route('login');
        }
        
        // Check if code is provided in the request
        if ($request->filled('code')) {
            // First check if we have a session-based OTP code
            $otpData = Session::get('two_factor_code');
            
            Log::info('Comparing codes:', [
                'submitted_code' => $request->code,
                'session_code' => $otpData ? $otpData['code'] : null,
                'expires_at' => $otpData ? $otpData['expires_at'] : null,
                'now_timestamp' => now()->timestamp,
            ]);
            
            if ($otpData && 
                $otpData['code'] === $request->code && 
                $otpData['expires_at'] > now()->timestamp) {
                
                // Clear the code from the session
                Session::forget('two_factor_code');
                
                // Complete the authentication
                $this->completeAuthentication($request, $user);
                
                // Redirect to the appropriate dashboard based on the guard
                if ($guard === 'admin') {
                    return redirect()->intended(route('admin.dashboard'));
                } elseif ($guard === 'store-owner') {
                    return redirect()->intended(route('store-owner.dashboard'));
                } elseif ($guard === 'store-staff') {
                    return redirect()->intended(route('store-staff.dashboard'));
                } else {
                    return redirect()->intended(config('fortify.home'));
                }
            }
            
            // If no session code or it didn't match, try TOTP if user has it set up
            if ($user->two_factor_secret) {
                try {
                    // Create the provider for verification
                    $provider = new FortifyTwoFactorProvider(new Google2FA());
                    
                    // Validate the OTP code
                    if ($provider->verify(
                        decrypt($user->two_factor_secret),
                        $request->code
                    )) {
                        // Complete the authentication
                        $this->completeAuthentication($request, $user);
                        
                        // Redirect to the appropriate dashboard based on the guard
                        if ($guard === 'admin') {
                            return redirect()->intended(route('admin.dashboard'));
                        } elseif ($guard === 'store-owner') {
                            return redirect()->intended(route('store-owner.dashboard'));
                        } elseif ($guard === 'store-staff') {
                            return redirect()->intended(route('store-staff.dashboard'));
                        } else {
                            return redirect()->intended(config('fortify.home'));
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error validating 2FA code: ' . $e->getMessage());
                }
            }
            
            // If we get here, neither method worked
            return back()->withErrors(['code' => 'The provided two-factor authentication code was invalid.']);
        }
        
        // Check if recovery code is provided in the request
        if ($request->filled('recovery_code') && $user->two_factor_recovery_codes) {
            try {
                $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
                
                if (in_array($request->recovery_code, $recoveryCodes)) {
                    // Remove the used recovery code
                    $recoveryCodes = array_diff($recoveryCodes, [$request->recovery_code]);
                    $user->two_factor_recovery_codes = encrypt(json_encode($recoveryCodes));
                    $user->save();
                    
                    // Complete the authentication
                    $this->completeAuthentication($request, $user);
                    
                    // Redirect to the appropriate dashboard based on the guard
                    if ($guard === 'admin') {
                        return redirect()->intended(route('admin.dashboard'));
                    } elseif ($guard === 'store-owner') {
                        return redirect()->intended(route('store-owner.dashboard'));
                    } elseif ($guard === 'store-staff') {
                        return redirect()->intended(route('store-staff.dashboard'));
                    } else {
                        return redirect()->intended(config('fortify.home'));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error validating recovery code: ' . $e->getMessage());
            }
            
            return back()->withErrors(['recovery_code' => 'The provided two-factor recovery code was invalid.']);
        }
        
        // If we get here, no code or recovery code was provided
        if (!$request->filled('code') && !$request->filled('recovery_code')) {
            return back()->withErrors(['code' => 'You must provide either a two-factor authentication code or a recovery code.']);
        }
    }
    
    /**
     * Complete the authentication process after successful verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function completeAuthentication(Request $request, $user)
    {
        Log::info('Completing authentication for user:', ['id' => $user->id, 'email' => $user->email]);
        
        // Set session flag to indicate 2FA is completed
        Session::put('two_factor_confirmed', true);
        Session::put('two_factor_authenticated', true);
        
        // If we're in the challenge flow, we need to complete the login
        if ($request->session()->has('login.id')) {
            $request->session()->forget('login.id');
            
            // Get the guard that was used for authentication
            $guard = Session::get('auth.guard', 'web');
            Log::info('Using guard for authentication:', ['guard' => $guard]);
            
            // Find the appropriate user model based on the guard
            if ($guard === 'admin') {
                $adminUser = \App\Models\Admin::whereHas('user', function($query) use ($user) {
                    $query->where('id', $user->id);
                })->first();
                
                if ($adminUser) {
                    Log::info('Logging in admin user:', ['admin_id' => $adminUser->id]);
                    Auth::guard('admin')->login($adminUser, $request->session()->pull('login.remember', false));
                } else {
                    Log::warning('Admin user not found for user ID: ' . $user->id);
                }
            } elseif ($guard === 'store-owner') {
                $storeOwnerUser = \App\Models\StoreOwner::whereHas('user', function($query) use ($user) {
                    $query->where('id', $user->id);
                })->first();
                
                if ($storeOwnerUser) {
                    Log::info('Logging in store owner user:', ['store_owner_id' => $storeOwnerUser->id]);
                    Auth::guard('store-owner')->login($storeOwnerUser, $request->session()->pull('login.remember', false));
                } else {
                    Log::warning('Store owner user not found for user ID: ' . $user->id);
                }
            } elseif ($guard === 'store-staff') {
                $storeStaffUser = \App\Models\StoreStaff::whereHas('user', function($query) use ($user) {
                    $query->where('id', $user->id);
                })->first();
                
                if ($storeStaffUser) {
                    Log::info('Logging in store staff user:', ['store_staff_id' => $storeStaffUser->id]);
                    Auth::guard('store-staff')->login($storeStaffUser, $request->session()->pull('login.remember', false));
                } else {
                    Log::warning('Store staff user not found for user ID: ' . $user->id);
                }
            } else {
                // Default to web guard
                Log::info('Logging in web user');
                Auth::login($user, $request->session()->pull('login.remember', false));
            }
            
            // Clear the guard from the session
            Session::forget('auth.guard');
        } else {
            Log::info('No login.id in session, skipping login');
        }
    }
}

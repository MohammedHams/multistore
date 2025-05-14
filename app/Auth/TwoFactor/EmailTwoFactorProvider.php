<?php

namespace App\Auth\TwoFactor;

use App\Models\VerifyOtp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\TwoFactorAuthenticationProvider as FortifyTwoFactorProvider;

class EmailTwoFactorProvider implements TwoFactorAuthenticationProvider
{
    /**
     * The underlying two-factor provider implementation.
     *
     * @var \Laravel\Fortify\TwoFactorAuthenticationProvider
     */
    protected $provider;

    /**
     * Create a new two-factor authentication provider instance.
     *
     * @param  \Laravel\Fortify\TwoFactorAuthenticationProvider  $provider
     * @return void
     */
    public function __construct(FortifyTwoFactorProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Generate a new secret key.
     *
     * @return string
     */
    public function generateSecretKey()
    {
        return $this->provider->generateSecretKey();
    }

    /**
     * Get the validation rules used to validate an incoming two factor authentication code.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required|string|min:6',
        ];
    }

    /**
     * Validate the given code.
     *
     * @param  \App\Models\User  $user
     * @param  string  $code
     * @param  string|null  $secret
     * @return bool
     */
    public function verify($user, $code, $secret = null)
    {
        try {
            // First check if there's a valid email OTP in the database
            $otp = VerifyOtp::where('user_id', $user->id)
                            ->where('otp', $code)
                            ->where('type', 'email')
                            ->where('used', false)
                            ->where('expires_at', '>', now())
                            ->first();
            
            if ($otp) {
                // Mark the OTP as used
                $otp->update(['used' => true]);
                return true;
            }
            
            // Check for any valid OTP regardless of type (for backward compatibility)
            $legacyOtp = VerifyOtp::where('user_id', $user->id)
                                ->where('otp', $code)
                                ->whereNull('type')
                                ->where('used', false)
                                ->where('expires_at', '>', now())
                                ->first();
            
            if ($legacyOtp) {
                // Mark the OTP as used
                $legacyOtp->update(['used' => true]);
                return true;
            }
            
            // Fallback to the regular TOTP verification
            // Only if the user has set up a TOTP authenticator and a secret is provided
            if ($secret) {
                return $this->provider->verify($user, $code, $secret);
            }
            
            return false;
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error("Error verifying email OTP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate and send an OTP code to the user's email.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return bool
     */
    public function generateAndSendOtp($user)
    {
        try {
            // Generate a 6-digit code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Delete any existing OTPs for this user with type=email
            VerifyOtp::where('user_id', $user->id)
                    ->where('type', 'email')
                    ->delete();
            
            // Store the code in the database
            VerifyOtp::create([
                'user_id' => $user->id,
                'otp' => $code,
                'type' => 'email',
                'expires_at' => now()->addMinutes(10), // OTP expires after 10 minutes
                'used' => false,
            ]);
            
            // Send the code via email
            Mail::to($user->email)->send(new \App\Mail\TwoFactorCode($code));
            
            return true;
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error("Error generating/sending email OTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Legacy method for backward compatibility
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function sendOtpCode($user)
    {
        return $this->generateAndSendOtp($user);
    }
    
    /**
     * Get the QR code URL for the user's two factor authentication QR code.
     *
     * @param  string  $companyName
     * @param  string  $companyEmail
     * @param  string  $secret
     * @return string
     */
    public function qrCodeUrl($companyName, $companyEmail, $secret)
    {
        // Since we're using email-based 2FA, we don't need a QR code
        // But we need to implement this method to satisfy the interface
        // Delegate to the underlying provider for TOTP-based 2FA if needed
        return $this->provider->qrCodeUrl($companyName, $companyEmail, $secret);
    }
}

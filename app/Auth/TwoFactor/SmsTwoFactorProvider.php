<?php

namespace App\Auth\TwoFactor;

use App\Models\VerifyOtp;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsTwoFactorProvider
{
    /**
     * The length of the OTP code.
     *
     * @var int
     */
    protected $codeLength = 6;

    /**
     * The expiry time of the OTP code in minutes.
     *
     * @var int
     */
    protected $expiryTime = 10;

    /**
     * Generate and send an OTP code to the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function generateAndSendOtp(User $user)
    {
        // Generate a random OTP code
        $code = $this->generateOtpCode();

        // Save the OTP code to the database
        $this->storeOtp($user, $code);

        // Send the OTP code via SMS
        return $this->sendSms($user->phone_number, $code);
    }

    /**
     * Generate a random OTP code.
     *
     * @return string
     */
    protected function generateOtpCode()
    {
        return str_pad(random_int(0, pow(10, $this->codeLength) - 1), $this->codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Store the OTP code in the database.
     *
     * @param  \App\Models\User  $user
     * @param  string  $code
     * @return void
     */
    protected function storeOtp(User $user, $code)
    {
        // Delete any existing OTP codes for this user
        VerifyOtp::where('user_id', $user->id)
            ->where('type', 'sms')
            ->delete();

        // Create a new OTP record
        VerifyOtp::create([
            'user_id' => $user->id,
            'otp' => $code,
            'type' => 'sms',
            'expires_at' => now()->addMinutes($this->expiryTime),
        ]);
    }

    /**
     * Send an SMS with the OTP code.
     *
     * @param  string  $phoneNumber
     * @param  string  $code
     * @return bool
     */
    protected function sendSms($phoneNumber, $code)
    {
        try {
            // Check if phone number is valid
            if (empty($phoneNumber)) {
                Log::error('Cannot send OTP via SMS: Phone number is empty');
                return false;
            }

            // Format the phone number (ensure it has country code)
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Prepare the message
            $message = "Your verification code is: {$code}. It will expire in {$this->expiryTime} minutes.";

            // Get SMS API configuration from config
            $apiUrl = config('services.sms.api_url');
            $apiKey = config('services.sms.api_key');
            $sender = config('services.sms.sender_id', 'MultiStore');

            // Check if SMS service is configured
            if (empty($apiUrl) || empty($apiKey)) {
                Log::warning('SMS service not configured properly. OTP code: ' . $code);
                return false;
            }

            // Send the SMS using the configured API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'to' => $phoneNumber,
                'from' => $sender,
                'text' => $message,
            ]);

            // Log the response for debugging
            if ($response->successful()) {
                Log::info("OTP SMS sent successfully to {$phoneNumber}");
                return true;
            } else {
                Log::error("Failed to send OTP SMS: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error sending OTP SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format the phone number to ensure it has a country code.
     *
     * @param  string  $phoneNumber
     * @return string
     */
    protected function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If the number doesn't start with a +, add the default country code (Saudi Arabia: +966)
        if (!Str::startsWith($phoneNumber, '+')) {
            // If the number starts with 0, remove it before adding the country code
            if (Str::startsWith($phoneNumber, '0')) {
                $phoneNumber = substr($phoneNumber, 1);
            }
            
            // Add the country code
            $phoneNumber = '+966' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Verify the OTP code.
     *
     * @param  \App\Models\User  $user
     * @param  string  $code
     * @return bool
     */
    public function verify(User $user, $code)
    {
        $otp = VerifyOtp::where('user_id', $user->id)
            ->where('otp', $code)
            ->where('type', 'sms')
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return false;
        }

        // Mark the OTP as used
        $otp->delete();

        return true;
    }
}

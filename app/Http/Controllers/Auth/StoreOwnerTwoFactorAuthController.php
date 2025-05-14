<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Features;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class StoreOwnerTwoFactorAuthController extends Controller
{
    /**
     * Show the two-factor authentication setup page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        return view('auth.store-owner.two-factor-auth', [
            'enabled' => $request->user('store-owner')->two_factor_secret !== null,
            'confirmed' => $request->user('store-owner')->two_factor_confirmed_at !== null,
            'showingQrCode' => $request->user('store-owner')->two_factor_secret !== null && $request->user('store-owner')->two_factor_confirmed_at === null,
            'showingRecoveryCodes' => $request->user('store-owner')->two_factor_secret !== null && $request->user('store-owner')->two_factor_confirmed_at !== null,
        ]);
    }

    /**
     * Enable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\EnableTwoFactorAuthentication  $enable
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $enable($request->user('store-owner'));

        return back()->with('status', 'Two-factor authentication has been enabled.');
    }

    /**
     * Disable two-factor authentication for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\DisableTwoFactorAuthentication  $disable
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $disable($request->user('store-owner'));

        return back()->with('status', 'Two-factor authentication has been disabled.');
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

        $user = $request->user('store-owner');

        if (! $this->hasValidCode($request, $user)) {
            return back()->withErrors(['code' => 'The provided two-factor authentication code was invalid.']);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->put('auth.two_factor_confirmed', true);

        return back()->with('status', 'Two-factor authentication has been confirmed.');
    }

    /**
     * Determine if the provided code is valid.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return bool
     */
    protected function hasValidCode(Request $request, $user)
    {
        return $request->code && app(TwoFactorAuthenticationProvider::class)->verify(
            $user->two_factor_secret, $request->code
        );
    }

    /**
     * Generate a new set of recovery codes for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Actions\GenerateNewRecoveryCodes  $generate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerateRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate)
    {
        $generate($request->user('store-owner'));

        return back()->with('status', 'Recovery codes have been regenerated.');
    }

    /**
     * Display the user's recovery codes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recoveryCodes(Request $request)
    {
        if (! $request->user('store-owner')->two_factor_confirmed_at) {
            return back()->with('error', 'Please confirm your two-factor authentication before viewing recovery codes.');
        }

        return response()->json([
            'recoveryCodes' => json_decode(decrypt($request->user('store-owner')->two_factor_recovery_codes)),
        ]);
    }

    /**
     * Display the QR code for the user's two-factor authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function qrCode(Request $request)
    {
        if (! $request->user('store-owner')->two_factor_secret) {
            return back()->with('error', 'Two-factor authentication is not enabled.');
        }

        return response()->json([
            'svg' => (new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            ))->render(
                $request->user('store-owner')->twoFactorQrCodeUrl()
            ),
        ]);
    }
}

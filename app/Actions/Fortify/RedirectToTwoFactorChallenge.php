<?php

namespace App\Actions\Fortify;

use App\Auth\TwoFactor\EmailTwoFactorProvider;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as FortifyRedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\LoginRateLimiter;
use Laravel\Fortify\TwoFactorAuthenticatable;

class RedirectToTwoFactorChallenge extends FortifyRedirectIfTwoFactorAuthenticatable
{
    /**
     * The email two-factor provider instance.
     *
     * @var \App\Auth\TwoFactor\EmailTwoFactorProvider
     */
    protected $emailProvider;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @param  \Laravel\Fortify\LoginRateLimiter  $limiter
     * @param  \App\Auth\TwoFactor\EmailTwoFactorProvider  $emailProvider
     * @return void
     */
    public function __construct(StatefulGuard $guard, LoginRateLimiter $limiter, EmailTwoFactorProvider $emailProvider)
    {
        parent::__construct($guard, $limiter);
        $this->emailProvider = $emailProvider;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        $user = $this->validateCredentials($request);

        if (optional($user)->two_factor_secret &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->filled('remember'),
            ]);

            // Send an OTP code to the user's email
            $this->emailProvider->sendOtpCode($user);

            return $this->twoFactorChallengeResponse($request, $user);
        }

        return $next($request);
    }
}

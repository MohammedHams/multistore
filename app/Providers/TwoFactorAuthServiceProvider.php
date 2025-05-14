<?php

namespace App\Providers;

use App\Auth\TwoFactor\EmailTwoFactorProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\TwoFactorAuthenticationProvider as FortifyTwoFactorProvider;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Ensure the Filesystem service is available
        if (!$this->app->bound('files')) {
            $this->app->singleton('files', function () {
                return new \Illuminate\Filesystem\Filesystem;
            });
        }

        // Register the default Fortify two-factor provider
        $this->app->singleton(FortifyTwoFactorProvider::class, function ($app) {
            return new FortifyTwoFactorProvider(
                new Google2FA(),
                config('fortify.second_factor_app')
            );
        });

        // Register our custom email two-factor provider
        $this->app->singleton(TwoFactorAuthenticationProvider::class, function ($app) {
            return new EmailTwoFactorProvider(
                $app->make(FortifyTwoFactorProvider::class)
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Auto-enable two-factor authentication for all new users
        \App\Models\User::created(function ($user) {
            $enableTwoFactorAuthentication = app(\Laravel\Fortify\Actions\EnableTwoFactorAuthentication::class);
            $enableTwoFactorAuthentication($user);
        });
    }
}

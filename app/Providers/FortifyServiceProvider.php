<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Responses\LoginResponse;
use App\Responses\LoginViewResponse;
use App\Responses\PasswordResetResponse;
use App\Responses\PasswordResetViewResponse;
use App\Responses\RegisterResponse;
use App\Responses\RegisterViewResponse;
use App\Responses\RequestPasswordResetLinkViewResponse;
use App\Responses\VerifyEmailViewResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register response bindings
        $this->app->singleton(Contracts\LoginViewResponse::class, LoginViewResponse::class);
        $this->app->singleton(Contracts\LoginResponse::class, LoginResponse::class);
        
        $this->app->singleton(Contracts\RegisterViewResponse::class, RegisterViewResponse::class);
        $this->app->singleton(Contracts\RegisterResponse::class, RegisterResponse::class);
        
        $this->app->singleton(Contracts\RequestPasswordResetLinkViewResponse::class, RequestPasswordResetLinkViewResponse::class);
        $this->app->singleton(Contracts\PasswordResetViewResponse::class, PasswordResetViewResponse::class);
        $this->app->singleton(Contracts\PasswordResetResponse::class, PasswordResetResponse::class);
        
        $this->app->singleton(Contracts\VerifyEmailViewResponse::class, VerifyEmailViewResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}

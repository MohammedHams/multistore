<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Http\Kernel;

class LocaleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(SetLocale::class);
    }
}

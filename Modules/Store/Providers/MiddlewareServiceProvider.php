<?php

namespace Modules\Store\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use App\Http\Middleware\CheckPermission;

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Register middleware aliases
    }

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);
        
        // Register the permission middleware
        $router->aliasMiddleware('permission', CheckPermission::class);
    }
}

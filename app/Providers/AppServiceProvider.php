<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\App;
use App\Http\Middleware\SetLocale;
use Illuminate\Contracts\Http\Kernel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No custom bindings needed
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the SetLocale middleware
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(SetLocale::class);
        
        // Add a blade directive for translation
        Blade::directive('t', function ($expression) {
            return "<?php echo __(${expression}); ?>";
        });
    }
}
